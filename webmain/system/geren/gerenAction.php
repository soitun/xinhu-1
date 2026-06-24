<?php
class gerenClassAction extends Action
{
	public function getinitAjax()
	{
		$uid = $this->adminid;
		$carr= m('admin')->getcompanyinfo($uid);
		
		return array(
			'gerentodo' => $this->option->getval('gerennotodo_'.$uid.''),
			'qmimgstr' 	=> $this->option->getval('qmimgstr_'.$uid.''),
			'carr' 		=> $carr,
		);
	}
	
	public function cogsaveAjax()
	{
		$uid = $this->adminid;
		$this->option->setval('gerennotodo_'.$uid.'', $this->get('gerentodo','0'));
	}
	
	//保存图片
	public function qmimgsaveAjax()
	{
		$uid = $this->adminid;
		$str = '';
		$qmimgstr = $this->post('qmimgstr');
		if(!isempt($qmimgstr)){
			if(contain($qmimgstr,'.')){
				$str = $qmimgstr;
			}else{
				$qma = explode(',', $qmimgstr);
				$str = ''.UPDIR.'/'.date('Y-m').'/'.$uid.'qming_'.rand(1000,9999).'.png';
				$this->rock->createtxt($str, base64_decode($qma[1]));
			}
		}
		$this->option->setval('qmimgstr_'.$uid.'', $str);
	}
	
	public function filebefore($table)
	{
		$key	= $this->post('key');
		$atype	= $this->post('atype');
		$dt1	= $this->post('dt1');
		$dt2	= $this->post('dt2');
		$where	 = 'and optid='.$this->adminid.'';
		if($atype=='all' && $this->adminid == 1){
			$where='';
			if($this->adminid>1)$where=m('admin')->getcompanywhere(3);
			
		}
		if($key!=''){
			$where.=" and (`optname` like '%$key%' or `filename` like '%$key%' or `mtype`='$key')";
		}
		if($dt1!='')$where.=" and `adddt`>='".$dt1." 00:00:00'";
		if($dt2!='')$where.=" and `adddt`<='".$dt2." 23:59:59'";
		return array(
			'where' => $where,
			
		);
	}
	public function fileafter($table, $rows)
	{
		$fobj = m('file');
		foreach($rows as $k=>&$rs){
			$rs['thumbpath'] = $fobj->getthumbpath($rs);
			$fpath = $rs['filepath'];
			
			$status= 1;
			if(substr($fpath,0,4)=='http'){
				$status = 2;
			}else{
				if(isempt($rs['filenum']) && !file_exists($fpath))$status=0;
				$filepathout = arrvalue($rs, 'filepathout');
				if(!file_exists($fpath) && !isempt($filepathout)){
					if($fobj->isimg($rs['fileext']))$rs['filepath'] = $filepathout;
					$status=2;
				}
			}
			$rs['status'] = $status;
		}
		return array(
			'rows' => $rows
		);
	}
	
	public function delfileAjax()
	{
		$id = c('check')->onlynumber($this->post('id','0'));
		m('file')->delfile($id);
		backmsg();
	}
	
	public function defaultAction()
	{
		$this->title	= '修改头像';
		$face			= $this->db->getmou($this->T('admin'),'face',"`id`='$this->adminid'");
		$imgurl = '';	
		if(!$this->rock->isempt($face)){
			$imgurl='../../'.preg_replace("/_crop\d{4}/",'',$face);
		}
		//$face			= $this->rock->repempt($face,'images/white.gif');
		$this->smartydata['face']		= $face;
		$this->smartydata['imgurl']		= $imgurl;
	}
	
	public function changestyleAjax()
	{
		$style = (int)$this->post('style','0');
		m('admin')->update('`style`='.$style.'', 'id='.$this->adminid.'');
	}


	public function editpassAjax()
	{
		$id			= $this->adminid;
		if(getconfig('systype')=='demo')exit('演示上不要修改');
		$oldpass	= $this->rock->post('passoldPost');
		$pasword	= $this->rock->post('passwordPost');
		echo m('login')->editpass($id, $oldpass, $pasword);
	}
	
	
	/**
		保存头像
	*/
	public function savefaceAjax()
	{
		$id			= $this->adminid;
		$arr		= array('face'=>$this->rock->post('facePost'));
		$msg		= '';
		if(!$this->db->record($this->T('admin'),$arr, "`id`='$id'"))$msg= $this->db->error();
		if($msg=='')$msg='success';
		echo $msg;
	}
	
	public function todoydAjax()
	{
		$sid = c('check')->onlynumber($this->post('s'));
		m('todo')->update("status=1,`readdt`='$this->now'", "`id` in(".$sid.") and `status`=0");
	}
	
	public function totaldaetods($table, $rows)
	{
		$wdtotal	= m('todo')->rows("`uid`='$this->adminid' and `status`=0 and `tododt`<='$this->now'");
		return array('wdtotal'=>$wdtotal);
	}
	
	public function beforetotaldaetods($table)
	{
		$s = " and `uid`='$this->adminid' and `tododt`<='$this->now'";
		$key = $this->post('key');
		$dt  = $this->post('dt');
		if($key)$s.=" and (`title` like '%$key%' or `mess` like '%$key%')";
		if($dt)$s.=" and `optdt` like '$dt%'";
		return $s;
	}
	
	public function getlinksAjax()
	{
		$rows = m('links')->getrows('1=1','*','`type`,`sort`');
		echo json_encode($rows);
	}
	
	
	//导入个人通讯录
	public function piliangaddAjax()
	{
		$rows  	= c('html')->importdata('name,unitname,tel,mobile,email,gname,address','name');
		$oi 	= 0;
		$db 	= m('vcard');
		foreach($rows as $k=>$rs){
			$rs['optdt']	= $this->now;
			$rs['uid']		= $this->adminid;
			$rs['optname']	= $this->adminname;
			$db->insert($rs);
			$oi++;
		}
		backmsg('','成功导入'.$oi.'条数据');
	}
	
	public function filelogs_before($table)
	{
		$where = '';
		$fileid = (int)$this->post('fileid','0');
		$where = "and `fileid`='$fileid'";
		return $where;
	}
	
	public function delfilelogsAjax()
	{
		$id = (int)$this->post('id','0');
		m('files')->delete($id);
		backmsg();
	}
	
	
	//获取背景图片
	public function getbeijingAjax()
	{
		$dev  = $this->option->getval('beijing_'.$this->adminid.'','images/beijing/bj0.jpg');
		$path = ''.ROOT_PATH.'/images/beijing';
		if(!is_dir($path))return returnsuccess(array(
			'dev' => $dev,
			'rows'=> array()
		));
		$barr = array();
		$bar  = glob(''.$path.'/*.jpg');
		$isno = false;
		if($bar)foreach($bar as $k=>$fil1){
			$faiar  = explode('images', $fil1);
			$lujg   = 'images'.$faiar[1].'';
			if($lujg==$dev)$isno = true;
			$barr[] = $lujg;
		}
		if(!$isno)$barr[] = $dev;
		return returnsuccess(array(
			'dev'=> $dev,
			'rows'=>$barr
		));
	}
	
	public function savebeijingAjax()
	{
		$val = $this->post('value');
		$this->option->setval('beijing_'.$this->adminid.'',$val);
	}
}