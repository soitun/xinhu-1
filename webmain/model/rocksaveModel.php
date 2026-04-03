<?php
/**
*	20260318公共保存
*/
class rocksaveClassModel extends Model
{
	public $now_uid  = 0;
	public $now_name = '';
	public $flow;
	
	/**
	*	设置当前用户
	*/
	public function setNowuser($id, $name)
	{
		$this->now_uid  		= $id;
		$this->now_name 		= $name;
		$this->rock->adminid  	= $this->now_uid;
		$this->rock->adminname  = $this->now_name;
		return $this;
	}

	
	/**
	*	提交保存，简单的保存
	*/
	public function submit($num,$mid=0,$openlx=0)
	{
		$this->flow 			= m('flow')->initflow($num);
		$this->flow->adminname  = $this->now_name;
		$this->flow->adminid  	= $this->now_uid;
		$this->flow->openmode 	= $openlx;
		if($openlx == 4 && arrvalue($this->flow->moders,'isluo') != '1')return returnerror('not out open');
		
		$uaarr= array();
		$addbo= ($mid===0);
		foreach($this->flow->fieldsarra as $k=>$rs){
			$fid = $rs['fields'];
			$fi1 = substr($fid, 0, 5);
			if($fi1=='temp_' || $fi1=='base_' || $rs['islu']=='0')continue;
			$val = $this->rock->post($fid);
			
			if($rs['isbt']==1 && isempt($val))return returnerror(''.$rs['name'].'不能为空');
			
			$dev = $rs['dev'];
			if(!isempt($dev) && isempt($val) && $dev=='0')$val = $dev; //默认值是0
			
			$uaarr[$fid] = $val;
	
		}
		
		$table		= $this->flow->moders['table'];
		$allfields 	= $this->db->getallfields('[Q]'.$table.'');
		
		if(in_array('optdt', $allfields))$uaarr['optdt'] 	 = $this->rock->now;
		if(in_array('optid', $allfields))$uaarr['optid'] 	 = $this->now_uid;
		if(in_array('optname', $allfields))$uaarr['optname'] = $this->now_name;
		
		if(in_array('uid', $allfields) && $addbo)
			$uaarr['uid'] = $this->rock->post('uid', $this->now_uid);
		
		if(in_array('applydt', $allfields) && $addbo)
			$uaarr['applydt'] = $this->rock->post('applydt', date('Y-m-d'));
		
		
		
		$notsave = array();//不保存的字段
		if(method_exists($this->flow, 'flowsavebefore')){
			$befa = $this->flow->flowsavebefore($table, $uaarr, $mid, $addbo);
			if($befa){
				if(is_string($befa))return returnerror($befa);
				if(is_array($befa)){
					if(isset($befa['msg']))return returnerror($befa['msg']);
					if(isset($befa['rows'])){
						if(is_array($befa['rows']))foreach($befa['rows'] as $bk=>$bv)$uaarr[$bk]=$bv;
					}
					if(isset($befa['notsave'])){
						$notsave = $befa['notsave'];
						if(is_string($notsave))$notsave = explode(',', $notsave);
					}
				}
			}
		}
		
		//不保存字段过滤掉
		if(is_array($notsave))foreach($notsave as $nofild)if(isset($uaarr[$nofild]))unset($uaarr[$nofild]);
		
		
		if($addbo){
			$mid = $this->flow->insert($uaarr);
			if(!$mid)return returnerror($this->db->lasterror());
		}
		
		$this->flow->loaddata($mid, false);
		$this->flow->submit('提交');
		//print_r($uaarr);
		
		return returnsuccess();
	}
	
	
	/**
	*	文件上传的
	*/
	public function upload($uptypes='*')
	{
		if(!$_FILES)return 'sorry!';
		$upimg		= c('upfile');
		$maxsize	= (int)$this->rock->get('maxsize', $upimg->getmaxzhao());//上传最大M
		$updir		= $this->rock->get('updir');
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
			if($bobg)return 'stop:'.$bobg.'';
		}
		$uptypes = str_replace(',','|', $uptypes);
		$upimg->initupfile($uptypes, ''.UPDIR.'|'.$updir.'', $maxsize);
		$upses	= $upimg->up('file');
		if(!is_array($upses))return $upses;
		$arr 	= c('down')->uploadback($upses);
		$arr['autoup'] = (getconfig('qcloudCos_autoup') || getconfig('alioss_autoup')) ? 1 : 0; //是否上传其他平台
		return $arr;
	}
}