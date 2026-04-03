<?php
//单据提醒设置
class remindClassModel extends Model
{
	public function initModel()
	{
		$this->settable('flow_remind');
	}
	
	public function todorun()
	{
		return m('flow')->initflow('remind')->getremindtodo();
	}
	
	/**
	*	获取主ID
	*/
	public function getremindrs($table, $mid)
	{
		$rs = $this->getone("`table`='$table' and `mid`='$mid'");
		return $rs;
	}
	
		
	/**
	*	2026-03-06新增
	*	相关单据显示$lx0详情，1编辑，2列表
	*/
	public function relevantshow($xgstr, $lx=0, $fobj=null)
	{
		if(isempt($xgstr))return '';
		$farr = explode(',', $xgstr);
		$str  = '';if($lx==1)$str = array();
		foreach($farr as $fars){
			$farra  = explode('|', $fars);
			$num 	= $farra[0];
			$mid 	= $farra[1];
			
			$flow    = m('flow')->initflow($num, $mid, false);
			$flow->ismobile = $fobj->ismobile;
			$summary = $flow->moders['summary'];
			$vstrs 	 = $this->rock->reparr($summary, $flow->rs);
			if($lx==1){
				$str[]= array(
					'name' => $vstrs,
					'num' => $num,
					'mid' => $mid,
				);
			}else{
				$url  	 = $flow->getxiangurl($num, $mid, 'auto');
				$str .= '<div class="list-items">'.$vstrs.'';
				if(!$flow->daochubo)$str .= ' <a temp="clo" href="javascript:;" onclick="js.open(\''.$url.'\')">详</a>';
				$str .= '</div>';
			}
		}
		if($lx != 1){
			if($str)$str = '<div class="list-items-group">'.$str.'</div>';
		}
		return $str;
	}
}