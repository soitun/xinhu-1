<?php 
class userClassAction extends apiAction
{
	public function editpassAction()
	{
		if(getconfig('systype')=='demo')$this->showreturn('演示上不要修改');
		$id			= $this->adminid;
		$oldpass	= $this->post('passoldPost');
		$pasword	= $this->post('passwordPost');
		$msg 		= m('login')->editpass($id, $oldpass, $pasword);
		if($msg=='success'){
			$this->showreturn('success');
		}else{
			$this->showreturn('',$msg, 201);
		}
	}
	
	//修改头像
	public function editfaceAction()
	{
		$fid = (int)$this->post('fid');
		$dbs = m('admin');
		$face= $dbs->changeface($this->adminid, $fid);
		if($face)$face = $dbs->getface($face);
		
		$this->showreturn($face);
	}
	
	//设置极光推送的regid
	public function setjpushidAction()
	{
		$id = $this->get('id');
		m('login')->update("`ip`='$id'", "`token`='$this->token' and `uid`='$this->adminid'");
		$this->showreturn('ok');
	}
}