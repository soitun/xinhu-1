<?php
class orderClassAction extends apiAction
{
	
	public function initAction()
	{
		$this->display= false;
	}
	
	
	/**
	*	支付订单回调处理完成后才处理
	*/
	public function bcallAction()
	{
		$cobj 	 	 = c('check');
		$ordernum	 = $this->get('ordernum');
		if(!$ordernum || $cobj->onlynoen($ordernum))return 'err';
		$flow 		 = m('flow')->initflow('finorder');
		$rs 		 = $flow->getone("`ordernum`='$ordernum'");
		if(!$rs)return 'notfound';
		$ispay		 = (int)$rs['ispay'];
		$id 		 = $rs['id'];
		if($ispay == 1)return '订单已支付完成1';
		if($ispay == 3)return '订单已标失败1';
		
		$barr		 = c('rockpay')->orderQuery($ordernum);
		if(!$barr['success'])return $barr['msg'];
		$data 		 = $barr['data'];
		$order_no	 = $data['order_no'];
		$statuscn	 = $data['statuscn'];//付款状态
		if($statuscn != 'success')return 'zt:'.$statuscn.''; //不是成功的
		
		
		$uarr['orderno'] = $order_no;
		$uarr['paytype'] = $data['paytype'];
		$uarr['paydt']   = $data['findt'];
		$flow->update($uarr, $id);
		
		$flow->loaddata($id, false);
		$flow->changeOrder(1);
		
		return 'ok';
	}
	
	/**
	*	回调处理，状态变的时候会推送到这个地址
	*/
	public function bcstateAction()
	{
		$cobj 	 	 = c('check');
		$ordernum	 = $this->get('ordernum');
		$statuscn	 = $this->get('statuscn');
		$paytype	 = $this->get('paytype');
		if(!$ordernum || !$statuscn || $cobj->onlynoen($ordernum) || $cobj->onlynoen($statuscn) || $cobj->onlynoen($paytype))return 'err';
		
		$flow 		 = m('flow')->initflow('finorder');
		$rs 		 = $flow->getone("`ordernum`='$ordernum'");
		if(!$rs)return 'notfound';
		$ispay		 = (int)$rs['ispay'];
		$id 		 = $rs['id'];
		if($ispay == 1)return '订单已支付完成2';
		if($ispay == 3)return '订单已标失败2';
		
		$ispay 		 = -1;
		if($statuscn == 'ping')$ispay = 4; //付款中
		if($statuscn == 'wait')$ispay = 2; //已付待确认
		if($statuscn == 'perr')$ispay = 3; //失败
		if($paytype)$flow->update(array(
			'paytype' => $paytype
		), $id);
		
		if($ispay > -1){
			$flow->loaddata($id, false);
			$flow->changeOrder($ispay);
		}
		
		return 'ok,'.$ispay.'';
	}
}