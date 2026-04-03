<?php
class flow_finccbxClassModel extends flowModel
{
	
	public function flowrsreplace($rs, $lx=0)
	{
		if($rs['id']==0)return $rs;

		$jzid	= arrvalue($rs,'jzid');
		if($jzid>0 && $lx==1){
			$to = m('finjibook')->rows($jzid);//判断记录存在嘛
			if($to==0){
				$jzid = 0;
				$this->update('`jzid`=0', $rs['id']);
			}
		}
		if($jzid>0){
			$url  = $this->getxiangurl('finjizhi', $jzid, 'auto');
			$rs['jzid'] = '<a href="javascript:;" onclick="js.open(\''.$url.'\')">已生成</a>';
		}else if($jzid=='-1'){
			$rs['jzid'] = '<font color=#aaaaaa>不需要</font>';
		}else{
			$rs['jzid'] = '';
		}
		
		$cxid = arrvalue($rs,'cxid');
		if($cxid>0){
			$url  = $this->getxiangurl('waichu', $cxid, 'auto');
			$rs['cxid'] = '<a href="javascript:;" onclick="js.open(\''.$url.'\')">有关联</a>';
		}else{
			$rs['cxid'] = '';
		}
		
		return $rs;
	}
	
	public function flowatypearr($atypea)
	{
		foreach($atypea as $k=>$rs){
			if($rs['num']=='alldjz'){
				$atypea[$k]['stotal'] = $this->rows('`type`=1 and `status`=1 and `jzid`=0');
			}
		}
		return $atypea;
	}
	
	public function ccddata()
	{
		$arr[] = array('name'=>'不关联','value'=>'0');
		
		$rows  = m('kqout')->getall('`uid`='.$this->adminid.' and `status`=1','*','`optdt` desc');
		if($rows)foreach($rows as $k=>$rs){
			$arr[] = array('name'=>'['.$rs['atype'].']'.$rs['address'].'('.$rs['applydt'].')','value'=>$rs['id']);
		}
		return $arr;
	}
}