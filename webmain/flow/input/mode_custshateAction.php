<?php
/**
*	此文件是流程模块【custshate.分享单据】对应控制器接口文件。
*/ 
class mode_custshateClassAction extends inputAction{
	
	public function selectcust()
	{
		$rows = m('crm')->getmycust($this->adminid, $this->rock->arrvalue($this->rs, 'custid'));
		$rows['rows'][] = array(
			'name' => '所有客户',
			'value'=> '0'
		);
		return $rows;
	}
}	
			