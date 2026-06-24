<?php
class flow_finorderClassModel extends flowModel
{
	
	public function orderstate()
	{
		//'未付款|red,已付款|green,待确认收款|#ff6600,没付款成功|red'
		
		$arr[] = array('value'=>'0','name'=>'待付款','color'=>'red');
		$arr[] = array('value'=>'1','name'=>'已付款','color'=>'green');
		$arr[] = array('value'=>'2','name'=>'待确认收款','color'=>'#ff6600');
		$arr[] = array('value'=>'3','name'=>'没付款成功','color'=>'gray');
		$arr[] = array('value'=>'4','name'=>'付款中','color'=>'blue');
		return $arr;
	}
	
	public function getorderzt($zt)
	{
		$arr = array();
		foreach($this->orderstate() as $k=>$rs){
			if($rs['value']==$zt)$arr = $rs;
		}
		return $arr;
	}
	
	public function flowrsreplace($rs, $lx=0)
	{
		if($rs['ispay']==3)$rs['ishui']=1;
		if($rs['isplat']=='0')$rs['isplat']='';
		if($rs['isplat']=='1'){
			$rs['isplat'] = '<font color=green>是</font>';
		}
		
		if(in_array($rs['ispay'], array('0','2','4')) && $this->openmode==4 && $lx==1 && c('rockpay')->isConfig()){
			$rs['isplat'] .= '&nbsp;<button onclick="js.loading(\'处理中...\');location.href=location.href+\'&gtype=pay\'" class="btn">在去支付</button>'; 
			$gtype = $this->rock->get('gtype');
			if($gtype=='pay'){
				$barr = c('rockpay')->createOrder($rs['ordernum'], $rs['money'], $rs['subject'], $rs['body'], array(
					'platid' => $rs['platid']
				));
				if(!$barr['success']){
					echo $barr['msg'];
				}else{
					$this->update('`isplat`=1,`ispay`=4,`paytype`=null', $rs['id']);
					$this->rock->location($barr['data']);
				}
				exit;
			}
		}
		
		$jzid	= arrvalue($rs,'jzid');
		if($lx==1 && $jzid>0){
			$to = m('finjibook')->rows($jzid);
			if($to==0){
				$jzid = 0;
				$this->update('`jzid`=0', $rs['id']);
			}
		}
		if($jzid>0){
			$url  = $this->getxiangurl('finjishou', $jzid, 'auto');
			$rs['jzid'] = '<a href="javascript:;" onclick="js.open(\''.$url.'\')">已生成</a>';
		}else if($jzid=='-1'){
			$rs['jzid'] = '<font color=#aaaaaa>不需要</font>';
		}else{
			$rs['jzid'] = '';
		}
		
		return $rs;
	}
	
	public function flowatypearr($atypea)
	{
		foreach($atypea as $k=>$rs){
			if($rs['num']=='alldaisk'){
				$atypea[$k]['stotal'] = $this->rows('`ispay`=2');
			}
		}
		return $atypea;
	}
	
	//付款成功
	public function paysuccess($ids)
	{
		$ida = explode(',', $ids);
		foreach($ida as $id){
			$this->loaddata($id, false);
			$this->changeOrder(1);
		}
	}
	
	/**
	*	付款状态更新
	*/
	public function changeOrder($zt)
	{
		$ispay = $this->rs['ispay'];
		if($ispay==$zt)return;
		$orderbill = $this->rs['orderbill'];
		$this->update(array(
			'ispay' => $zt,
		), $this->id);
		
		
		if(isempt($orderbill))return;
		$arr = explode(',', $orderbill);
		foreach($arr as $arrs){
			$numa = explode('|', $arrs);
			$flow = m('flow:'.$numa[0].'');
			if(method_exists($flow, 'flowordersuccess')){
				$flow->flowordersuccess($numa[1], array(
					'ispay' 	=> $zt,
					'paytype' 	=> $this->rs['paytype'],
					'paydt' 	=> $this->rs['paydt'],
				));
			}
		}
	}
	
	//回调的，$ors当前单据操作的信息，$crs提交过来的一些信息
	protected function flowoptmenu($ors, $crs)
	{
		if($ors['num']=='bssunoup'){
			$this->changeOrder(1);
		}
		if($ors['num']=='bssenoup'){
			$this->changeOrder(3);//没付款成功
		}
	}
	
	//这个方法写搜索
	public function flowbillwhere($uid, $lx)
	{
		$where = '';
		
		
		if($lx=='yzallworder' || $lx=='yzallorder'){
			$where = 'and 1=2';
			$yezhuid = m('wuye')->getyezhuid(); //此方法我们封装好的获取当前业主id
			$where = "and `mknum`='wyfee' and `platid`=$yezhuid";
		}
		
		
		return $where;//返回条件
	}
	
	
	/**
	*	创建内部的订单
	*/
	public function createOrder($subject, $money, $orderbill, $cans=array())
	{
		$ordernum 	= 'ro'.date('Ym').''.$this->db->ranknum('[Q]finorder', 'ordernum', 8);
		
		$uarr['ordernum'] 	= $ordernum;
		$uarr['subject'] 	= $subject;
		$uarr['moneym']   	= $money;
		$uarr['money']   	= $money;
		$uarr['orderbill']  = $orderbill;
		$uarr['createdt']   = $this->rock->now;
		$uarr['optdt']   	= $this->rock->now;
		$uarr['ispay']   	= 2;
		$uarr['body']   	= '';
		foreach($cans as $k=>$v)$uarr[$k] = $v;
		$uarr['id']			= $this->insert($uarr);
		
		return $uarr;
	}
	
	/**
	*	生成记账单
	*/
	public function createJizhang($ids)
	{
		$zhangid = (int)$this->option->getval('finorderzhangid','0');
		if($zhangid <= 0)return returnerror('未设置收款帐号');
		$frs = m('finount')->getone($zhangid);
		if(!$frs)return returnerror('收款帐号不存在');
		$this->accountrs = $frs;
		$oi 	= 0;
		$ida 	= explode(',', $ids);
		foreach($ida as $id){
			if($this->id != $id)$this->loaddata($id, false);
			$bo = $this->createJizhangs();
			if($bo)$oi++;
		}
		return returnsuccess('成功生成'.$oi.'个记账单');
	}
	private $accountrs;
	private function createJizhangs()
	{
		$rs = $this->rs;
		if($rs['ispay'] != '1' && (int)$rs['jzid'] != 0)return false;
		$applydt = $rs['paydt'];
		if(isempt($applydt))$applydt = $rs['createdt'];
		
		$uarr['comid'] 		= $rs['comid'];
		$uarr['type'] 		= 0;
		$uarr['money'] 		= $rs['money'];
		$uarr['mknum'] 		= $this->modenum.'|'.$this->id;
		$uarr['custname']   = $rs['createname'];
		$uarr['applydt'] 	= $applydt;
		$uarr['optid'] 		= $this->adminid;
		$uarr['optname'] 	= $this->adminname;
		$uarr['optdt'] 		= $this->rock->now;
		$uarr['uid'] 		= $this->adminid;
		$uarr['xguid'] 		= '0';
		$uarr['xgname'] 	= '';
		$uarr['xgdeptid'] 	= '';
		$uarr['xgdeptname'] = '';
		$uarr['explain'] 	= '订单('.$rs['ordernum'].'.'.$rs['subject'].')';
		$uarr['accountid'] 	= $this->accountrs['id'];
		$uarr['zhangid'] 	= $this->accountrs['zhangid'];
		$uarr['jtype'] 	= '销售收入';
		$newid = m('finjibook')->insert($uarr);
		
		$this->update('jzid='.$newid.'', $this->id);
		return $newid;
	}
}