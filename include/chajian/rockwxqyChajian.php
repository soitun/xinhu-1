<?php
/**
*	信呼企业微信平台
*	2024-11-09
*/

class rockwxqyChajian extends Chajian
{
	private $platurl 	= '',$cnum = '';
	private $optionobj;
	
	
	protected function initChajian()
	{
		$this->platurl = getconfig('rockwxqy_url');
		if(!$this->platurl){
			$this->platurl = $this->rock->jm->base64decode('aHR0cHM6Ly9maWxlLnJvY2tvYS5jb20vd3hxeS5waHA:');
		}
		$this->optionobj = m('option');
		$this->cnum 	 = $this->optionobj->getval('wxqyplat_cnum');
	}
	
	public function isconfig()
	{
		if($this->cnum)return true;
		return false;
	}
	
	public function setCnum($cnum)
	{
		$this->cnum = $cnum;
		return $this;
	}
	
	public function geturlstr($act, $can=array(),$mact='')
	{
		$url = $this->platurl;
		$mode= 'wxqyopen';
		if($mact)$mode = $mact;
		$url.= '?a='.$act.'&m='.$mode.'';
		$url.= '&cnum='.$this->cnum.'&xinhukey='.getconfig('xinhukey').'';
		if(is_array($can))foreach($can as $k=>$v)$url.='&'.$k.'='.$v.'';
		return $url;
	}
	
	public function getdata($act, $can=array(), $data=array())
	{
		if(!$this->cnum)return returnerror('未设置单位编号');
		$url 	= $this->geturlstr($act, $can);
		if($data){
			$cont 	= c('curl')->postcurl($url, $data);
		}else{
			$cont 	= c('curl')->getcurl($url);
		}
		$data  	= array('code'=>201,'success'=>false,'msg'=>'出错了返回:'.htmlspecialchars($cont).'');
		if($cont!='' && substr($cont,0,1)=='{'){
			$data  	= json_decode($cont, true);
		}
		return $data;
	}
	
	public function postdata($act, $data=array(), $can=array())
	{
		return $this->getdata($act, $can, $data);
	}
	
	/**
	*	发消息
	*/
	public function sendmess($id, $title, $mess, $url='',$picurl='')
	{
		$where 		= '`uid` in('.$id.')';
		if($id=='all')$where = '1=1';
		$rows 		= m('zwxqy_user')->getall(''.$where.' AND `state`=1','userid,agentid,cnum');
		if(!$rows)return returnerror('nouser');
		$sdata = array(
			'touser' => $rows,
			'title'  => $this->rock->jm->base64encode($title),
			'mess'   => $this->rock->jm->base64encode($mess),
			'url'    => $this->rock->jm->base64encode($url),
			'picurl' => $this->rock->jm->base64encode($picurl),
		);
		return $this->postdata('sendmess', json_encode($sdata));
	}
	
	/**
	*	快捷登录
	*/
	public function authlogin()
	{
		$backurl = $this->rock->get('backurl');
		$rs 	 = m('zwxqy_user')->getone('`state`=1');
		if(!$rs){
			$url = '?d=we&m=login&errmsg='.$this->rock->jm->base64encode('没有激活的用户').'';
		}else{
			$url 	 = $this->platurl.'?m=main&yyid='.$rs['agentid'].'';
		}
		if($backurl)$url.='&backurl='.$backurl.'';
		$this->rock->location($url);
	}
	
	/**
	*	获取打卡记录(需要用异步)
	*/
	public function getcheckindata($uids='', $startdt='', $enddt='', $page=1)
	{
		$obj 		= m('weixinqy:daka');
		if(!method_exists($obj, 'savecheckindata'))return returnerror('未安装企业微信插件');
		
		$where 		= '`uid` in('.$uids.') AND ';
		if($uids=='')$where = '';
		$rows 		= m('zwxqy_user')->getall(''.$where.'`state`=1','userid,uid');
		if(!$rows)return returnerror('没有激活的用户');
		$userids	= $uids = $uarrs = array();
		foreach($rows as $k=>$rs){
			$userids[] = $rs['userid'];
			$uids[] = $rs['uid'];
			$uarrs[$rs['userid']] = $rs['uid'];
		}
		$sdata = array(
			'userids' 	=> $userids,
			'uids' 		=> $uids,
			'startdt' 	=> $startdt,
			'enddt' 	=> $enddt,
		);
		$barr 		= $this->postdata('checkindata', json_encode($sdata));
		if(!$barr['success'])return $barr;
		$this->rock->debugs($barr['data'],'djcheckdata');
		$data 		= $barr['data'];
		if(isset($data['checkindata'])){
			$obj->savecheckindata($data, $uarrs);
		}
		if(isset($data['hardwaredata'])){
			$obj->hardwaredata($data['hardwaredata'], $uarrs);
		}
		return $barr;
	}
}