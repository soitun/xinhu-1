<?php
//服务端设置
class xinhuClassAction extends Action
{
	public $xinhuobj;
	public function initAction()
	{
		$this->xinhuobj = c('xinhuapi');
	}
	
	public function setsaveAjax()
	{
		if(!COMPANYNUM){
			$this->option->setval('reimhostsystem@-8', $this->post('host'));
			$this->option->setval('reimpushurlsystem@-8', $this->post('push'));
			$this->option->setval('reimservertype@-8', $this->post('servertype'));
			$this->option->setval('reimappwxsystem@-8', $this->post('reimappwx'));
		}
		$this->option->setval('reimrecidsystem@-8', $this->post('receid'));
		$this->option->setval('reimchehuisystem@-8', $this->post('chehui'));
		$this->backmsg();
	}
	
	public function tongbudwAjax()
	{
		$rows = m('company')->getall('iscreate=1');
		foreach($rows as $k=>$rs){
			$base = ''.DB_BASE.'_company_'.$rs['num'].'';
			$this->sevessee($base, 'reimhostsystem');
			$this->sevessee($base, 'reimpushurlsystem');
			$this->sevessee($base, 'reimrecidsystem', $rs['num']);
			$this->sevessee($base, 'reimchehuisystem');
			$this->sevessee($base, 'reimservertype');
			$this->sevessee($base, 'reimappwxsystem');
		}
		return '同步成功';
	}
	private function sevessee($base, $key, $bh='')
	{
		$val = $this->option->getval($key);
		if($key=='reimrecidsystem')$val.='_'.$bh.'';
		$table = "".$base.".`[Q]option`";
		$where = "`num`='$key'";
		$ors 	= $this->db->getone($table, $where);
		if($ors){
			$sql 	= "update $table set `value`='$val',`optdt`='{$this->now}' where $where";
		}else{
			$sql 	= "insert into $table set `value`='$val',`optdt`='{$this->now}',`num`='$key'";
		}
		$this->db->query($sql, false);
	}
	
	public function getsetAjax()
	{
		$arr= array();
		$arr['reimhost']= $this->option->getval('reimhostsystem');
		$arr['reimrecid']= $this->option->getval('reimrecidsystem');
		$arr['reimpushurl']= $this->option->getval('reimpushurlsystem');
		$arr['reimchehui']= $this->option->getval('reimchehuisystem');
		$arr['servertype']= $this->rock->repempt($this->option->getval('reimservertype'),'1');
		if(isempt($arr['reimhost']))$arr['servertype']='1';
		$arr['reimappwx']= $this->rock->repempt($this->option->getval('reimappwxsystem'),'0');
		echo json_encode($arr);
	}
	
	//测试地址
	public function yibutestAjax()
	{
		$rand 	= time();
		$arr['krand'] = $rand;
		$runurl	= m('base')->getasynurl('asynrun','asyntest', $arr);
		m('reim')->asynurl('asynrun','asyntest', $arr);
		$msg 	= '<font color="green">测试成功可以使用</font>';
		sleep(10);
		$mkey 	= $this->option->getval('asyntest');
		if($mkey!=$rand)$msg 	= '<font color="red">测试失败不能使用，说明你服务端上是不能访问这地址的</font>';
		echo '异步地址【'.$runurl.'】'.$msg.'';
	}
	
	//测试队列
	public function testqueueAjax()
	{
		$rand 	= 'queue'.time();
		$barr 	= c('rockqueue')->push('cli,test', array(
			'rand' => $rand
		));
		if(!$barr['success'])return '队列测试失败：<font color="red">'.$barr['msg'].'</font>';
		sleep(3);
		$mkey 	= $this->option->getval('asyntest');
		$msg 	= '<font color="green">队列测试成功，可以使用</font>';
		if($mkey!=$rand)$msg 	= '<font color="red">无法运行“'.$barr['cmdurl'].'”测试失败不能使用，去<a href="'.URLY.'view_rockservice.html" target="_blank">看看帮助</a>。</font>';
		echo $msg;
	}
	
	public function testsendAjax()
	{
		$barr  = m('reim')->sendpush($this->adminid, $this->adminid,array(
			'cont' 	=> $this->jm->base64encode('测试内容:'.$this->now.''),
			'type' 	=> 'user',
			'optdt' => $this->now,
			'messid' => 0
		));
		$msg 	= '';
		if($barr['success']){
			$msg='服务端推送地址可以使用';
		}else{
			$msg='<font color=red>服务端推送地址不能使用：'.$barr['msg'].'</font>';
		}
		echo $msg;
	}
	
	
	
	
	/**
	*	获取列表
	*/
	public function getoainfoAjax()
	{
		$barr = $this->xinhuobj->getdata('dengji','getdata');
		$rows = array();
		if($barr['success']){
			$rows = $barr['data'];
		}
		return array(
			'rows' => $rows
		);
	}
	public function deldengjiAjax()
	{
		$id   = (int)$this->post('id');
		$barr = $this->xinhuobj->getdata('dengji','deldengji', array('id'=>$id));
		return $barr;
	}
	public function savedengjiAjax()
	{
		$id   	= (int)$this->post('id');
		$name   = str_replace(' ','',$this->post('name'));
		$url    = str_replace(' ','',$this->post('url'));
		if(substr($url,0,4)!='http')return returnerror('地址必须http开头');
		if(contain($url,'127.0.0.1') || contain($url,'localhost'))return returnerror('本地地址是不能登记到官网的');
		if(substr($url,-1)!='/')$url.='/';
		$carr 	= m('task')->pdlocal($url);
		if(!$carr['success'])return returnerror('系统地址无法打开');
		$barr 	= $this->xinhuobj->postdata('dengji','savedata', array(
			'id'	=> $id,
			'name'	=> $name,
			'url'	=> urlencode($url),
		));
		return $barr;
	}
	
	public function getonlineAjax()
	{
		
		$barr = m('reim')->pushserver('getonline');
		if(!$barr['success'])return $barr;
		$data = $barr['data'];
		if(!$data)return returnerror('无人员在线');
		$ondats = json_decode($data, true);
		$pc  = $ondats['pc'];
		$app = $ondats['app'];
		$uar1= explode(',', $pc);
		$uar2= explode(',', $app);
		$str = $pc;
		$on1 = count($uar1);
		$on2 = count($uar2);
		if($app){
			if($str)$str.=',';
			$str.=$app;
		}
		if(!$str)return returnerror('无人员在线');
		if(!$app)$on2 = 0;
		if(!$pc)$on1 = 0;
		$rows = m('admin')->getall('id in('.$str.') and `status`=1','id,name,face','sort asc');
		foreach($rows as $k=>$rs){
			$rows[$k]['pconline']  = in_array($rs['id'], $uar1);
			$rows[$k]['apponline'] = in_array($rs['id'], $uar2);
		}
		return returnsuccess(array(
			'rows' => $rows,
			'msg'	=> 'PC在线'.$on1.'人，APP在线'.$on2.'人',
		));
	}
}