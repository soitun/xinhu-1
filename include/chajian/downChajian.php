<?php
/**
	下载文件类插件
*/

class downChajian extends Chajian{
	
	private $upobj;
	private $messign;
	
	protected function initChajian()
	{
		$this->messign = '';
		$this->upobj = c('upfile');
	}
	
	/**
	*	获取随机文件名
	*/
	public function getallfilename($ext)
	{
		if(!is_dir(UPDIR))mkdir(UPDIR);
		$mkdir 	= ''.UPDIR.'/'.date('Y-m').'';
		if(!is_dir($mkdir))mkdir($mkdir);
		$allfilename			= ''.$mkdir.'/'.date('d_His').''.rand(10,99).'.'.$ext.'';
		return $allfilename;
	}
	
	/**
	*	根据扩展名保存文件(一般邮件附件下载)
	*/
	public function savefilecont($ext, $cont)
	{
		$bo  = $this->upobj->issavefile($ext);
		if(isempt($cont))return;
		$file= '';
		if(!$bo){
			$file	= $this->getallfilename('uptemp');
			$bo 	= @file_put_contents($file, base64_encode($cont));
		}else{
			$file 	= $this->getallfilename($ext);
			$bo 	= @file_put_contents($file, $cont);
		}
		if(!$bo){
			$file = '';
		}else{
			if($this->upobj->isimg($ext)){
				$bo = $this->upobj->isimgsave($ext, $file);
				if(!$bo)$file = '';
			}
		}
		return $file;
	}
	
	private function reutnmsg($msg)
	{
		$this->messign = $msg;
		return false;
	}
	
	//获取提示内容
	public function gettishi($msg1='')
	{
		$msg = $this->messign;
		if(isempt($msg))$msg = $msg1;
		return $msg;
	}
	
	/**
	*	根据内容创建文件
	*/
	public function createimage($cont, $ext, $filename, $thumbnail='')
	{
		if(isempt($cont))return $this->reutnmsg('创建内容为空');
		$allfilename			= $this->getallfilename($ext);
		$upses['oldfilename'] 	= $filename.'.'.$ext;
		$upses['fileext'] 	  	= $ext;
		@file_put_contents($allfilename, $cont);
		if(!file_exists($allfilename))return $this->reutnmsg('无法写入:'.$allfilename.'');
		
		$fileobj				= getimagesize($allfilename);
		$mime					= strtolower($fileobj['mime']);
		$next 					= 'jpg';
		if(contain($mime,'bmp'))$next = 'bmp';
		if($mime=='image/gif')$next = 'gif';
		if($mime=='image/png')$next = 'png';
		if($ext != $next){
			@unlink($allfilename);
			$ext = $next;
			$allfilename			= $this->getallfilename($ext);
			$upses['oldfilename'] 	= $filename.'.'.$ext;
			$upses['fileext'] 	  	= $ext;
			@file_put_contents($allfilename, $cont);
			if(!file_exists($allfilename))return $this->reutnmsg('无法写入:'.$allfilename.'');
		}
		
		$filesize 			  	= filesize($allfilename);
		$filesizecn 		  	= $this->upobj->formatsize($filesize);
		$picw					= $fileobj[0];				
		$pich					= $fileobj[1];
		if($picw==0||$pich==0){
			@unlink($allfilename);
			return $this->reutnmsg('无效的图片');;
		}
		$upses['filesize']	 	= $filesize;
		$upses['filesizecn']	= $filesizecn;
		$upses['allfilename']	= $allfilename;
		$upses['picw']	 		= $picw;
		$upses['pich']	 		= $pich;
		$arr 					= $this->uploadback($upses, $thumbnail);
		return $arr;
	}
	
	public function uploadback($upses, $thumbnail='', $subo=true)
	{
		if($thumbnail=='')$thumbnail='150x150';
		$msg 		= '';
		$data 		= array();
		if(is_array($upses)){
			$noasyn = $this->rock->get('noasyn');$noasyn = ''; //=yes就不同步到文件平台
			$noyaso = $this->rock->get('noyaso'); //=yes就不压缩
			$fileext= substr($upses['fileext'],0,10);
			$arrs	= array(
				'adddt'	=> $this->rock->now,
				'valid'	=> 1,
				'filename'	=> $this->replacefile($upses['oldfilename']),
				'web'		=> $this->rock->web,
				'ip'		=> $this->rock->ip,
				'mknum'		=> $this->rock->get('sysmodenum'),
				//'mid'		=> $this->rock->get('sysmid','0'),
				'fileext'	=> $fileext,
				'filesize'	=> (int)$this->rock->get('filesize', $upses['filesize']),
				'filesizecn'=> $upses['filesizecn'],
				'filepath'	=> str_replace('../','',$upses['allfilename']),
				'optid'		=> $this->adminid,
				'optname'	=> $this->adminname,
				'comid'		=> m('admin')->getcompanyid(),
			);
			$arrs['filetype'] = m('file')->getmime($fileext);
			$thumbpath	= $arrs['filepath'];
			$sttua		= explode('x', $thumbnail);
			$lw 		= (int)$sttua[0];
			$lh 		= (int)$sttua[1];
			
			//判断是不是需要压缩jpg和jpeg
			$compress	= getconfig('imgcompress');
			if($compress && $noyaso!='yes' && ($fileext=='jpg' || $fileext=='jpeg') && $upses['picw']>0 && $upses['pich']>0){
				$sttuc	= explode('x', $compress);
				$yw 	= (int)$sttuc[0];
				$yh 	= (int)arrvalue($sttuc, 1, 0);
				if($upses['picw'] > $yw || $upses['pich'] > $yh){
					$imgac	= c('image', true);
					$imgac->createimg($thumbpath);
					$yspaht = $imgac->compress($yw, $yh);
					if($yspaht){
						if($thumbpath != $yspaht)unlink($thumbpath);
						$thumbpath = $yspaht;
						$arrs['filepath'] = $yspaht;
						$arrs['filesize'] = filesize($yspaht);
						$arrs['filesizecn'] = $this->upobj->formatsize($arrs['filesize']);
					}
				}
			}
			
			$shuiyin = $this->rock->get('shuiyin');
			if($upses['picw']>$lw || $upses['pich']>$lh){
				$imgaa	= c('image', true);
				$imgaa->createimg($thumbpath);
				$thumbpath 	= $imgaa->thumbnail($lw, $lh, 1);
				if(contain($thumbpath, 'reimchat'))$shuiyin = 'size';
				if($shuiyin=='size'){
					$imgaa->createimg($thumbpath);
					$imgaa->addwater($arrs['filesizecn']);
				}
			}
			
			
			
			if($upses['picw'] == 0 && $upses['pich']==0)$thumbpath = '';
			$arrs['thumbpath'] = $thumbpath;
			
			//有缩略图先上传到云里 && $this->rock->get('sysuptype')=='img'
			if($thumbpath){
				$tarr = $this->uploadBase($thumbpath);
				if($tarr['success'] && isset($tarr['url']))$arrs['thumbplat'] = $tarr['url'];
			}
			
			$bo = $this->db->record('[Q]file',$arrs);
			if(!$bo)$this->reutnmsg($this->db->error());
			
			$id	= $this->db->insert_id();
			$arrs['id']   = $id;
			$arrs['picw'] = $upses['picw'];
			$arrs['pich'] = $upses['pich'];
			$data= $arrs;
			
			//上传到上传的文件管理2021-08-09
			if(getconfig('rockfile_autoup') && $noasyn != 'yes'){
				$stime = time()+rand(3,6);
				if($subo)$stime=0;
				c('rockqueue')->push('flow,uptofile', array('fileid'=>$id), $stime);
			}
			
			//自动上传到腾讯云存储/阿里云oss存储
			if((getconfig('qcloudCos_autoup') || getconfig('alioss_autoup'))  && $noasyn != 'yes'){
				$stime = time()+rand(3,6);
				if($subo)$stime=0;
				c('rockqueue')->sendfile($id, $stime);
			}
			
			if(arrvalue($arrs, 'thumbplat')){
				$data['filepath']  = $arrs['thumbplat'];
				$data['thumbpath'] = $arrs['thumbplat'];
			}
		}else{
			$data['msg'] = $upses;
		}
		return $data;
	}
	
	/**
	*	简单上传要调用
	*/
	private function uploadBase($path)
	{
		if(getconfig('qcloudCos_autoup')){
			return c('qcloudCos')->uploadFile($path);
		}else{
			if(getconfig('alioss_autoup')){
				$obj = c('alioss');
				if(method_exists($obj, 'uploadFile'))return $obj->uploadFile($path);
			}
		}
		return returnerror();
	}
	
	//过滤特殊文件名
	private function replacefile($str)
	{
		$s 			= strtolower($str);
		$s2			= $s.'';
		$lvlaraa  	= explode(',',' ,user(),found_rows,(),\',",select*from,select*,%20,<,>,\,');
		$s = str_replace($lvlaraa, '', $s);
		$s = str_replace(array('(',')'), array('）','）'), $s);
		if($s!=$s2)$str = $s;
		return $str;
	}
	
	//获取扩展名
	public function getext($file)
	{
		return strtolower(substr($file,strrpos($file,'.')+1));
	}
}
