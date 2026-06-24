<?php 
class uploadClassAction extends apiAction
{
	/**
	*	上传文件
	*/
	public function upfileAction()
	{
		if(!$_FILES)exit('sorry!');
		$upimg	= c('upfile');
		$maxsize= (int)$this->get('maxsize', $upimg->getmaxzhao());//上传最大M
		$uptypes= '*';
		$updir		= $this->get('updir');
		if(isempt($updir)){
			$updir=date('Y-m');
		}else{
			$updir=str_replace(array(' ','.'),'', trim($updir));
			$updir=str_replace('{month}',date('Y-m'), $updir);
			$updir=str_replace('{Year}',date('Y'), $updir);
			$updir=str_replace(array('{','}'),'', $updir);
			$updir=str_replace(',','|', $updir);
			
			$bobg = preg_replace("/[a-zA-Z0-9_]/",'', $updir);
			$bobg = str_replace(array('-','|'),'', $bobg);
			if($bobg)exit('stop:'.$bobg.'');
		}
		$upimg->initupfile($uptypes, ''.UPDIR.'|'.$updir.'', $maxsize);
		$upses	= $upimg->up('file');
		if(!is_array($upses))exit($upses);
		$arr 	= c('down')->uploadback($upses);
		$arr['autoup'] = (getconfig('qcloudCos_autoup') || getconfig('alioss_autoup')) ? 1 : 0; //是否上传其他平台
		$this->returnjson($arr);
	}
	
	/**
	*	从编辑文件中心获取最新编辑的文件20250610
	*/
	public function editfilebAction()
	{
		//用那个异步去获取(待开发)
		
		$fileid = (int)$this->get('fileid','0');
		$frs 	= m('file')->getone($fileid);
		if(!$frs)return 'error';
		$editobj= c('rockedit');
		$barr   = $editobj->getdata('file','history', array(
			'filekey' 	=> getconfig('xinhukey'),
			'filenum' 	=> $frs['onlynum'],
		));
		if(!$barr['success'])return $barr['msg'];
		$arr = $barr['data'];
		
		$data	= file_get_contents($arr['url']);
		$result = $this->upfilevb_Query($fileid, $frs['fileext'], $data);
		if(substr($result,0,2)=='ok'){
			$editobj->getdata('file','upresult', array(
				'isup' 	=> 3,
				'upid' 	=> $arr['upid'],
			));
			return '“'.$frs['filename'].'”文件编辑已完成';
		}else{
			return '编辑失败;';
		}
	}
	
	//判断是否编辑完成
	public function editfilecAction()
	{
		$fileid = (int)$this->get('fileid','0');
		$erand	= $this->get('erand');
		$frs 	= m('file')->getone($fileid);
		if(!$erand || !$frs)return 'error';
		$barr   = c('rockedit')->getdata('file','editfilec', array(
			'filekey' 	=> getconfig('xinhukey'),
			'filenum' 	=> $frs['onlynum'],
			'erand'		=> $erand
		));
		if($barr['success'])return $barr['data'];
		return 'wait';
	}
	
	
	/**
	*	这个是用来在线编辑文档上传的
	*/
	public function upfilevbAction()
	{
		$fileid = (int)$this->get('fileid','0');
		$fileext= $this->get('fileext');
		$data 	= $this->getpostdata();
		if(isempt($data))return '没有数据';
		return $this->upfilevb_Query($fileid, $fileext, base64_decode($data));
	}
	
	public function upfilevb_Query($fileid, $fileext, $data)
	{
		if($fileid==0)return 'error';
		
		$fileobj  = m('file');
		$frs 	  = $fileobj->getone($fileid); //记录
		if(!$frs) return '文件记录不存在了';
		if(!$fileext)$fileext = $frs['fileext'];
		
		$uptype = '|doc|docx|xls|xlsx|ppt|pptx|';
		if(!contain($uptype,'|'.$fileext.'|'))$fileext='doc';
		
		$frs['oldfilepath'] = $frs['filepath'];
		$filename 			= $frs['filename'];
		if(!contain($filename, '.'.$fileext.'')){
			$filename = str_replace(array('.doc','.xls','.ppt'), '.'.$fileext.'', $filename);
		}
		
		$filepath = ''.UPDIR.'/'.date('Y-m').'/'.date('d_His').''.rand(10,99).'.'.$fileext.'';

		$this->rock->createtxt($filepath, $data);
		
		$filesize 			  	= filesize($filepath);
		$filesizecn 		  	= $this->rock->formatsize($filesize);
		
		//更新文件
		$fileobj->update(array(
			'filename' 		=> $filename,
			'filepath' 		=> $filepath,
			'filenum' 		=> '',
			'filesize' 		=> $filesize,
			'filesizecn' 	=> $filesizecn,
			'fileext' 		=> $fileext,
			'filepathout' 	=> '',
			'pdfpath' 		=> '',
		),$fileid);
		c('cache')->del('filetopdf'.$fileid.'');
		
		//【弃用】发队列自动上传到信呼文件平台
		if(getconfig('autoup_toxinhudoc')){
			//c('rockqueue')->sendfile($fileid);
		}
		//上传到腾讯存储
		if(getconfig('qcloudCos_autoup')){
			c('rockqueue')->sendfile($fileid);
		}
		
		//告诉上传人说编辑了他的附件
		$mknums = arrvalue($frs,'mknum');
		if(!isempt($mknums) && $frs['mid']>0){
			
			$mid = $frs['mid'];
			$mknumsa = explode('|', $mknums);
			$modenum = $mknumsa[0];
			if(isset($mknumsa[1]))$mid = $mknumsa[1];
			$flow = m('flow')->initflow($modenum, $mid, false);
			
			$ssid = $flow->addlog(array(
				'name' => '在线编辑'
			));
			
			$ufrs = $frs;
			$ufrs['filepath'] = $frs['oldfilepath'];
			unset($ufrs['oldfilepath']);
			unset($ufrs['id']);
			unset($ufrs['onlynum']);
			$ufrs['mtype']  = 'flow_log';
			$ufrs['mid'] 	= $ssid;
			$ufrs['mknum'] 		= ''.$modenum.'|'.$mid.'';
			$ufrs['filename'] 	= str_replace('.'.$ufrs['fileext'].'','(备份).'.$ufrs['fileext'].'', $ufrs['filename']);
			$fileobj->insert($ufrs); //记录原来的文件
			
			//不是我创建就告诉创建人
			if($this->adminid<>$frs['optid'])
				$flow->push($frs['optid'],'', ''.$this->adminname.'在线编辑文件“'.$frs['filename'].'”', '文件在线编辑');
			
			
			$flow->floweditoffice($frs, $ufrs);
			
		}else if($this->adminid<>$frs['optid']){ //不知道关联哪个模块
			$flow = m('flow')->initflow('word');
			$flow->push($frs['optid'],'文档', ''.$this->adminname.'在线编辑文件“'.$frs['filename'].'”', '文件在线编辑',0, array(
				'wxurl' => ''
			));
		}
		
		$frs['filesize'] = $filesize;
		$fkey = $this->createtempurl($frs);
		return 'ok,'.$fkey.'';
	}
	
	/**
	*	上传时初始化看是不是存在文件
	*/
	public function initfileAction()
	{
		$cobj 		= c('check');
		$filesize	= $cobj->onlynumber($this->post('filesize'));
		$fileext	= $cobj->repotr($this->post('fileext'));
		$filename	= $cobj->repotr($this->getvals('filename'));
		$where 		= "`fileext`='$fileext' and `filesize`='$filesize'";
		if(!isempt($filename))$where.=" and `filename`='$filename'";
		$frs 		= m('file')->getone($where,'*','`id` desc');
		$bo 		= false;
		if($frs){
			$filepath = $frs['filepath'];
			if(!isempt($filepath) && file_exists($filepath))$bo=true;
		}
		if($bo){
			$this->showreturn(json_encode($frs));
		}else{
			$this->showreturn('','not found', 201);
		}
	}
	
	public function upfileappAction()
	{
		if(!$_FILES)$this->showreturn('', '禁止访问', 201);
		$upimg	= c('upfile');
		$maxsize= (int)$this->get('maxsize', $upimg->getmaxzhao());//上传最大M
		$uptypes= '*';
		$upimg->initupfile($uptypes, ''.UPDIR.'|reimchat|'.date('Y-m').'', $maxsize);
		$upses	= $upimg->up('file');
		if(!is_array($upses))$this->showreturn('', $upses, 202);
		$arr 	= c('down')->uploadback($upses);
		$this->showreturn($arr);
	}
	
	public function upcontAction()
	{
		$cont = $this->post('content');
		if(isempt($cont))exit('sorry not cont');
		$cont 	= str_replace(' ','', $cont);
		$cont	= $this->rock->jm->base64decode($cont);
		$arr 	= c('down')->createimage($cont,'png','截图');
		$this->returnjson($arr);
	}
	
	
	public function getfileAjax()
	{
		$cont = '';
		$path = ''.UPDIR.'/uptxt'.$this->adminid.'.txt';
		if(file_exists($path)){
			@$cont = file_get_contents($path);
		}
		$data = array();
		if($cont!=''){
			$arr = json_decode($cont, true);
			$msg 	= $arr['msg'];
			$data 	= $arr['data'];
			@unlink($path);
		}else{
			$msg = 'sorry,not infor!';
		}
		$this->showreturn($data, $msg);
	}
	
	public function getfileAction()
	{
		$fileid = (int)$this->post('fileid',0);
		$rs 	= m('file')->getone($fileid);
		$this->showreturn($rs);
	}
	
	public function downAction()
	{
		$id  = (int)$this->jm->gettoken('id');
		m('file')->show($id);
	}
	
	//记录预览记录
	public function logsAction()
	{
		$fileid = (int)$this->post('fileid',0);
		$type 	= (int)$this->post('type',0);
		m('file')->addlogs($fileid, $type);
	}
	
	
	/**
	*	发送编辑权限
	*/
	public function rockofficeeditAction()
	{
		$fileid = (int)$this->get('id');
		$lx 	= (int)$this->get('lx');
		$frs 	= m('file')->getone($fileid);
		if(!$frs)return returnerror('文件不存在了');
		$filepath = $frs['filepath'];
		$filepathout = $frs['filepathout'];
		
		if(substr($filepath,0,4)!='http' && !file_exists($filepath)){
			if(isempt($filepathout)){
				return returnerror('文件不存在了1');
			}else{
				$filepath = $filepathout;
			}
		}
		
		$uptype = '|doc|docx|xls|xlsx|ppt|pptx|';
		if(!contain($uptype,'|'.$frs['fileext'].'|'))return returnerror('不是文档类型无法在线编辑');
		$filename 	= $frs['filename'];
		$utes		= 'edit';
		if($lx==1){
			$filename = '(只读)'.$filename.'';
			$utes     = 'yulan';
		}
	
		$arr	 = array();
		$arr[0]  = URL; 
		$arr[1]  = $filename;
		$arr[2]  = $this->createtempurl($frs);
		$arr[3]  = $this->rock->gethttppath($filepath); //下载地址
		$arr[4]  = $fileid;
		$arr[5]  = $this->adminid;
		$arr[6]  = $this->token;
		$arr[7]  = $utes;
		$arr[8]  = $frs['fileext'];
		
		$str 	= '';
		foreach($arr as $s1)$str.=','.$s1.'';
		
		return returnsuccess(substr($str,1));
	}
	
	
	
	
	
	
	

	/**
	*	获取预览和下载地址
	*/
	public function fileinfoAction()
	{
		$fileid = (int)$this->get('id');
		$type 	= (int)$this->get('type'); //0预览,1下载,2编辑
		$ismobile = (int)$this->get('ismobile'); //是否手机端
		return $this->fileinfoShow($fileid, $type, $ismobile);
	}
	
	private $frs,$loadyuan;
	public function fileinfoShow($fileid, $type, $ismobile)
	{
		$fobj 	= m('file');
		$frs 	= $fobj->getone($fileid);
		$this->frs = $frs;
		if(!$frs)return returnerror('文件不存在了');
		$filenum= $frs['filenum'];
		
		$fileext		= $frs['fileext'];
		$filename		= $frs['filename'];
		$filepath		= $frs['filepath'];
		$filepathout	= arrvalue($frs, 'filepathout');
		
		$data			= array();
		$loadyuan		= false;
		$data['url']	= '';
		$data['fileext']= $fileext;
		
		//预览
		if($type==0){
			if(!$fobj->isview($fileext))
				return returnerror('此'.$fileext.'类型文件不支持在线预览');
		}
		
		//从文件上传中心最新
		if(!isempt($filenum)){
			$dbs  = m('admin');
			$barr = c('rockfile')->getdata('upload','fileinfo', array(
				'fnum' 		=> $filenum,
				'lx' 		=> $type,
				'sysuid' 	=> $this->adminid,
				'ismobile' 	=> $ismobile,
				'sysname' 	=> $this->rock->jm->base64encode($this->adminname),
				'sysface' 	=> $this->rock->jm->base64encode($dbs->getface($dbs->getmou('face',$this->adminid))),
			));
			if(!$barr['success']){
				return $barr;
			}else{
				$loadyuan 	 = true;
				$da 		 = $barr['data'];
				$data['url'] = $da['url'];
				if($da['isimg'] && $type==0)$data['url'] = $da['imgurl'];
			}
		}
		
		$this->loadyuan = $loadyuan;
		//存自己服务器的
		if(!$loadyuan){
			if(substr($filepath,0,4)!='http' && isempt($filepathout) && !file_exists($filepath))return returnerror('文件不存在了1');
			if(c('upfile')->isimg($fileext)){
				$data['url'] = m('admin')->getface($filepath);
				if(!isempt($filepathout))$data['url'] = $filepathout;
			}
			//下载
			if($type==1){
				$url = 'api.php?m=upload&id='.$fileid.'&a=down';
				$url.= '&adminid='.$this->adminid.'&token='.$this->admintoken.'';
				$url.= '&filename='.$this->jm->base64encode($frs['filename']).''; 
				$data['url'] = $url;
			}
			
			//编辑
			if($type==2){
				if(getconfig('officebj')=='1'){
					$erand			= rand(1000000,9999999);
					$data['fileext']= 'rockedit';
					$data['url'] 	= 'index.php?m=public&a=fileedit&id='.$fileid.'&erand='.$erand.'';
					$data['editwsinfo'] = c('rockedit')->getwsinfo(array(
						'erand' => $erand,
						'fileid'=> $fileid
					));
					/*
					c('rockqueue')->push('rockoffice,gedit', array(
						'erand'  => $erand,
						'cishu'	 =>1, 
						'fileid' =>$fileid,
						'optid'	=> $this->adminid
					), time() + 10);*/
				}else{
					if($ismobile==1)return returnerror('移动端不支持在线编辑');
					$data['fileext']='rockoffice';
					$data['url'] = $this->rock->gethttppath($filepath);
				}
			}
		}
		
		
		$data['filename'] = $filename;
		$url 			  = arrvalue($data, 'url');
		
		if($url==''){
			$url = 'index.php?m=public&a=fileviewer&id='.$fileid.'';
		}

		//用本地插件编辑和预览
		if($data['fileext']=='rockoffice'){
			$utes		= 'edit';
			if($type==0){
				$filename = '(只读)'.$filename.'';
				$utes     = 'yulan';
			}
			$arr	 = array();
			$arr[0]  = URL; 
			$arr[1]  = $filename;
			$arr[2]  = $this->createtempurl($frs);
			$arr[3]  = $data['url']; //下载地址
			$arr[4]  = $fileid;
			$arr[5]  = $this->adminid;
			$arr[6]  = $this->token;
			$arr[7]  = $utes;
			$arr[8]  = $fileext;
			$str 	 = '';
			foreach($arr as $s1)$str.=','.$s1.'';
			$url 	 = substr($str, 1);
		}
		
		$data['url']	  = $url;
		$data['type']	  = $type;
		$data['id']	  	  = $fileid;
		$data['isview']	  = $fobj->isview($fileext); //是否可直接预览

		return returnsuccess($data);
	}
	
	//生成唯一文件名键值
	private function createtempurl($frs)
	{
		$str = ''.md5(URL).'_'.$frs['filesize'].'_'.$frs['id'].'.'.$frs['fileext'].'';
		return $str;
	}
	
	/**
	*	app上获取下载地址
	*/
	public function appgetfileAction()
	{
		$id 	= (int)$this->post('id',0);
		$barr	= $this->fileinfoShow($id, 1, 1);
		if(!$barr['success'])return $barr;
		$frs 	= $this->frs;
		$frs['filetype']	= m('file')->getmime($frs['fileext']);
		$frs['downurl']		= $barr['data']['url'].'&cfrom=app';
		if(!$this->loadyuan){
			$frs['downurl']= '';
			if(substr($frs['filepath'],0,4)=='http'){
				$frs['downurl'] = $frs['filepath'];
			}else{
				if(!file_exists($frs['filepath']) && arrvalue($frs,'filepathout'))$frs['downurl'] = $frs['filepathout'];
			}
		}
		return returnsuccess($frs);
	}
	
	/**
	*	编辑时验证
	*/
	public function sendeditAction()
	{
		$id 		= (int)$this->get('id',0);
		$otype 		= (int)$this->get('otype',0);
		return c('rockedit')->sendedit($id, $this->admintoken, $otype);
	}
	
	/**
	*	获取文件信息
	*/
	public function afileinfoAction()
	{
		$allfid = c('check')->onlynumber($this->get('allfid'));
		$filearr= array();
		if($allfid){
			$fobj 	= m('file');
			$frows 	 = $fobj->getall('`id` in('.$allfid.')','filename,id,filesizecn,fileext,optname,thumbpath,thumbplat');
			foreach($frows as $k1=>$rs1){
				$rs1['thumbpath'] = $fobj->getthumbpath($rs1);
				$filearr['f'.$rs1['id'].'']	= $rs1;
			}
		}
		return $filearr;
	}
	
	/**
	*	获取文件(写入到内容里)
	*/
	public function filedaoAction()
	{
		$allfid = c('check')->onlynumber($this->get('fileid'));
		$filearr= array();
		$str = '';
		if($allfid){
			$fobj 	= m('file');
			$frows 	 = $fobj->getall('`id` in('.$allfid.')');
			$urla   = getconfig('outurl', URL);
			foreach($frows as $k1=>$rs1){
				$str.='<br>';
				$url = ''.$urla.''.$rs1['filepath'].'';
				if($sst = arrvalue($rs1,'filepathout'))$url = $sst;
				
				$flx   = $rs1['fileext'];
				if(!contain($fobj->fileall,','.$flx.','))$flx='wz';
				$str1  = '';
				$imurl = ''.URL.'web/images/fileicons/'.$flx.'.gif';
				$thumbpath = $fobj->getthumbpath($rs1);
				if($fobj->isimg($flx) && !isempt($thumbpath))$imurl = $thumbpath;
				
				$str.='<img src="'.$imurl.'" align="absmiddle" height=20 width=20> <a target="_blank" href="'.$url.'">'.$rs1['filename'].'</a>('.$rs1['filesizecn'].')';
			}
		}
		return $str;
	}
	
	/**
	*	获取模版文件
	*/
	public function getmfileAction()
	{
		$data = array();
		$fenlei = $this->jm->base64decode($this->get('fenlei'));
		$fenlei = $this->rock->xssrepstr($this->rock->iconvsql($fenlei));
		$where 	= m('admin')->getjoinstr('a.`receid`', $this->adminid, 1);
		$sql 	= 'select a.`name`,a.`wtype`,b.`filepath`,b.`id` from `[Q]wordxie` a left join `[Q]file` b on a.`fileid`=b.`id` where a.`fenlei`=\''.$fenlei.'\' and a.`isgk`=1 and ('.$where.')';
		$rows 	= $this->db->getall($sql);
		foreach($rows as $k=>$rs){
			$data[] = array(
				'value' => $rs['id'],
				'name' => $rs['name'],
				'subname' => $rs['wtype'],
			);
		}
		return $data;
	}
	public function getmfilvAction()
	{
		$fileid = (int)$this->get('fileid','0');
		$frs 	= m('file')->getone($fileid);
		if(!$frs)return returnerror('不存在');
		
		$lujing	= $frs['filepathout'];
		if(isempt($lujing)){
			$lujing = $frs['filepath'];
			if(substr($lujing,0,4)!='http' && !file_exists($lujing))return returnerror('文件不存在了');
		}
		$fileext = $frs['fileext'];
		
		$fname = $this->jm->base64decode($this->get('fname'));
		$fname = (isempt($fname)) ? $frs['filename'] : ''.$fname.'.'.$fileext.'';
		
		$filepath = ''.UPDIR.'/'.date('Y-m').'/'.date('d').'_rocktpl'.rand(1000,9999).'_'.$fileid.'.'.$fileext.'';
		$this->rock->createtxt($filepath, file_get_contents($lujing));
		
		$uarr = array(
			'filename' => $this->rock->xssrepstr($fname),
			'fileext' => $fileext,
			'filepath' => $filepath,
			'filesize' => filesize($filepath),
			'filesizecn' => $this->rock->formatsize(filesize($filepath)),
			'optid' 	=> $this->adminid,
			'optname' 	=> $this->adminname,
			'adddt' 	=> $this->rock->now,
			'ip' 		=> $this->rock->ip,
			'web' 		=> $this->rock->web,
		);
		$uarr['id'] = m('file')->insert($uarr);
	
		return returnsuccess($uarr);
	}
	
	/**
	*	文库选择文件
	*/
	public function changedataAction()
	{
		$rows  	= array();
		$uptp 	= $this->get('uptp');
		$tsye 	= $this->get('tsye');
		$key 	= $this->get('key');
		$selv 	= (int)$this->get('selvalue','0');
		$where 	= '`optid`='.$this->adminid.'';
		if($selv==1){
			$str   =  m('admin')->getjoinstrs('`shateid`', $this->adminid, 1);
			$where = '`id` in(select `fileid` from `[Q]word` where `type`=0 '.$str.' )';
		}
		if($key){
			$key = $this->rock->xssrepstr($this->jm->base64decode($key));
			$where.=" and `filename` like '%".$key."%'";
		}
		if($uptp && $uptp!='*'){
			if($uptp=='image')$uptp='jpg,png,gif,jpeg';
			$uptp  = str_replace(',', "','", $uptp);
			$where.=" and `fileext` in('".$uptp."')";
		}
		$db 	= m('file');
		$rows	= $db->getall($where,'id,filename,filesizecn,fileext,thumbpath,thumbplat,filepath,filepathout','`id` desc limit 10');
		foreach($rows as $k=>$rs){
			$rows[$k]['value'] 	= $rs['id'];
			$rows[$k]['name'] 	= $rs['filename'];
			$rows[$k]['subname']= $rs['filesizecn'];
			$rows[$k]['xuanbool']= true;
			if($tsye=='img'){
				if(!isempt($rs['filepathout']))$rows[$k]['filepath'] = $rs['filepathout'];
			}else{
				unset($rows[$k]['filepath']);
			}
			unset($rows[$k]['filepathout']);
			if(!isempt($rs['thumbpath'])){
				$rows[$k]['iconsimg'] = $rs['thumbpath'];
				if(!isempt($rs['thumbplat']))$rows[$k]['iconsimg'] = $rs['thumbplat'];
			}else{
				$flx = $rs['fileext'];
				if(!contain($db->fileall,','.$flx.','))$flx='wz';
				$rows[$k]['iconsimg'] = 'web/images/fileicons/'.$flx.'.gif';
			}
		}
		$count 	= $db->rows($where);
		$selectdata[] = array('value'=>'','name'=>'选自己上传');
		$selectdata[] = array('value'=>'1','name'=>'共享给我的');
		return array(
			'rows' 		 => $rows,
			'totalCount' => $count,
			'selectdata' => $selectdata
		);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	*	20250522更新修改
	*/
	private $upsize = 154857;
	public function officeexistsAction()
	{
		$id 	= (int)$this->get('id');
		$otype 	= (int)$this->get('otype'); //0编辑1预览
		$frs 		= m('file')->getone($id);
		if(!$frs)return returnerror('文件不存在0');
		
		$onlynum	= $frs['onlynum'];
		if(isempt($onlynum)){
			$onlynum	= md5(''.$this->rock->jm->getRandkey().date('YmdHis').'file'.$id.'');
			m('file')->update("`onlynum`='$onlynum'", $id);
		}
		
		$path 	= $this->getfurls($frs);
		if(isempt($path))return returnerror('文件不存在1');
		$obj 		= c('rockedit');
		$urs 		= m('admin')->getone($this->adminid);
		$barr 		= $obj->getdata('file','change', array(
			'filenum' 	=> $onlynum,
			'optid'		=> $this->adminid,
			'otype'		=> $otype,
			'fileext'	=> $frs['fileext'],
			'erand'		=> $this->get('erand'),
			'filename'	=> $this->rock->jm->base64encode($frs['filename']),
			'optname'	=> $this->rock->jm->base64encode($this->adminname),
			'face'		=> $this->rock->jm->base64encode(m('admin')->getface($urs['face'])),
		));
		if(!$barr['success'])return $barr;
		
		$da 	= $barr['data'];
		if($da['type']==0){
			$barr['data']['filesizecn'] = $frs['filesizecn'];
			$barr['data']['fileext'] 	= $frs['fileext'];
			$barr['data']['fileid'] 	= $id;
			if(substr($path,0,4)=='http'){
				$zong = 1;
			}else{
				$filesize 	= filesize($path);
				$zong	 	= ceil($filesize/$this->upsize);
				if($zong<=0)$zong = 1;
			}
			$barr['data']['zong'] = $zong;
		}else{
			$barr['data']['url']  = $obj->gotourl($da['gourl'],$da['gokey'],$onlynum, $otype, $this->admintoken, $id);
		}
		return $barr;
	}
	
	public function getfurls($frs)
	{
		$filepath 	 = $frs['filepath'];
		if(file_exists($filepath))return $filepath;
		$path 		 = $frs['filepathout'];
		if(isempt($path))$path = $filepath;
		if(substr($path,0,4)=='http')return $path;
		if(!file_exists($path))return '';
		return $path;
	}
	
	//开始上传
	public function officefstartAction()
	{
		$id 	= (int)$this->get('id');
		$zong 	= (int)$this->get('zong');
		$ci 	= (int)$this->get('ci');
		$otype 	= (int)$this->get('otype');
		$filemid 	= (int)$this->get('filemid');
		$frs 	= m('file')->getone($id);
		
		$path 	 = $this->getfurls($frs);
		$conts	 = '';
		$datype  = 'base';

		
		if(substr($path,0,4)=='http'){
			$datype  = 'http';
			$conts	 = $path; //http远程地址的
		}else{
			$fp 	 = @fopen($path,'rb');
			if(!$fp)return returnerror('无法读取文件');
			$oi 	 = 0;
			while(!feof($fp)){
				$cont 	= fread($fp, $this->upsize);
				if($oi==$ci){
					$conts 	= $cont;
					break;
				}
				$oi++;
			}
			fclose ($fp);
		}
		
		
		$obj = c('rockedit');
		$barr 		= $obj->postdata('file','fstart', array(
			'filenum' 	=> $frs['onlynum'],
			'data'		=> $this->rock->jm->base64encode($conts),
			'zong'		=> $zong,
			'datype'	=> $datype,
			'ci'		=> $ci,
			'fileid'		=> $id,
			'filemid'		=> $filemid,
			'filesize'		=> $frs['filesize'],
			'fileext'		=> $frs['fileext'],
			'filename'		=> $this->rock->jm->base64encode($frs['filename']),
		));
		if(!$barr['success'])return $barr;
		$bda = $barr['data'];
		if($bda['result']=='ok'){
			$gokey = $this->get('gokey');
			$gourl = $this->rock->jm->base64decode($this->get('gourl'));
			$barr['data']['url'] = $obj->gotourl($gourl,$gokey,$frs['onlynum'], $otype, $this->admintoken, $id);;
		}
		
		return $barr;
	}
}