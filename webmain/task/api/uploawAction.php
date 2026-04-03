<?php 
class uploawClassAction extends apiAction
{
	public function initAction()
	{
		$this->display= false;
	}
	
	/**
	*	上传文件(外部信息收集使用的)，弃用
	*/
	public function upfileAction()
	{
		return 'error0';
		/*
		$mid = (int)$this->get('mid','0');
		$to	 = m('planm')->rows('`id`='.$mid.' and `type`=2 and `fenlei`=1 and `status`=1');
		if($to==0 || !$_FILES)exit('sorry!');
		$upimg	= c('upfile');
		$maxsize= (int)$this->get('maxsize', $upimg->getmaxzhao());//上传最大M
		$uptypes= 'jpg|png|docx|doc|pdf|xlsx|xls|zip|rar';
		$upimg->initupfile($uptypes, ''.UPDIR.'|'.date('Y-m').'', $maxsize);
		$upses	= $upimg->up('file');
		if(!is_array($upses))exit($upses);
		$arr 	= c('down')->uploadback($upses);
		$arr['autoup'] = (getconfig('qcloudCos_autoup') || getconfig('alioss_autoup')) ? 1 : 0; //是否上传其他平台
		return $arr;
		*/
	}
}