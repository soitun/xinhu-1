<?php
class qywxplatClassAction extends Action
{

	public function setsaveAjax()
	{
		$cnum = $this->post('cnum');
		$this->option->setval('wxqyplat_cnum@-10', $cnum);
		$this->option->setval('wxqyplat_devnum@-10', $this->post('devnum'));
		$this->option->setval('wxqyplat_huitoken@-10', $this->post('huitoken'));
		$this->option->setval('wxqyplat_tixi@-10', $this->post('tixi'));
		$this->option->setval('wxqyplat_daka@-10', $this->post('daka'));
		$this->option->setval('wxqyplat_kjdl@-10', $this->post('kjdl'));
		m('zwxqy_user')->delete("`cnum`<>'$cnum'");
		return 'ok';
	}
	
	public function getsetAjax()
	{
		$arr= array();
		$arr['purl']		= $this->option->getval('wxqyplat_purl');
		$arr['cnum']		= $this->option->getval('wxqyplat_cnum');
		$arr['devnum']		= $this->option->getval('wxqyplat_devnum');
		$arr['tixi']		= $this->option->getval('wxqyplat_tixi');
		$arr['daka']		= $this->option->getval('wxqyplat_daka');
		$arr['kjdl']		= $this->option->getval('wxqyplat_kjdl');
		$arr['huitoken']	= $this->option->getval('wxqyplat_huitoken');
		$arr['huiurl']		= ''.$this->rock->getouturl().'api.php?m=wxqyplat';
		if(COMPANYNUM)$arr['huiurl'].='&dwnum='.COMPANYNUM.'';
		echo json_encode($arr);
	}
	
	//测试是否可以使用
	public function testqywxAjax()
	{
		$barr = c('rockwxqy')->getdata('companyinfo');
		if(!$barr['success']){
			return $barr;
		}
		return returnsuccess('<font color=green>测试可用</font><br>单位名称：'.$barr['data']['name'].'<br>单位全称：'.$barr['data']['shortname'].'');
	}
	
	public function sethuidiaoAjax()
	{
		$data['huiurl'] 	= $this->jm->base64encode($this->rock->getouturl());
		$data['huitoken'] 	= $this->option->getval('wxqyplat_huitoken');
		return c('rockwxqy')->postdata('sethuiurl', $data);
	}
	
	//获取信呼系统上部门
	public function deptdataAjax()
	{
		$this->rows	= array();
		$this->getdept(0, 1);
		
		$this->returnjson(array(
			'totalCount'=> 0,
			'rows'		=> $this->rows
		));
	}
	private function getdept($pid, $oi)
	{
		$db		= m('dept');
		$menu	= $db->getall("`pid`='$pid' order by `sort`",'*');
		foreach($menu as $k=>$rs){
			$sid			= $rs['id'];
			
			$rs['level']	= $oi;
			$rs['stotal']	= $db->rows("`pid`='$sid'");
		
			$rs['zt']		= 1;
			$this->rows[] = $rs;
			$this->getdept($rs['id'], $oi+1);
		}
	}
	
	
	public function deptwxdataAjax()
	{
		$barr = c('rockqywx')->getdata('deptlist');
		if(!$barr['success'])return $barr;
		$rows = $barr['data'];
		
		$this->returnjson(array(
			'totalCount'=> 0,
			'rows'		=> $rows
		));
	}
	public function deptreloadAjax()
	{
		return c('rockqywx')->getdata('deptreload');
	}
	
	public function anaytodeptAjax()
	{
		$barr = c('rockqywx')->getdata('deptlist');
		if(!$barr['success'])return $barr;
		$rows = $barr['data'];
		
		
		return returnsuccess();
	}
	
	
	
	//微信上用户操作
	public function beforeusershow($table)
	{
		$fields = 'id,`name`,`user`,deptname,`mobile`,deptallname,status,ranking,deptid,sex,sort,face';
		$fields.=',deptids,deptnames';
		$s 		= '';
		$key 	= $this->post('key');
		if($key!=''){
			$s = " and (`name` like '%$key%' or `user` like '%$key%' or `ranking` like '%$key%' or `deptallname` like '%$key%' ";
			$s.=" or `deptnames` like '%$key%'";
			$s.= ')';
		}
		
		return array(
			'fields'=> $fields,
			'where'	=> $s
		);
	}
	public function afterusershow($table, $rows)
	{
		$tab  = 'zwxqy_user';
		$farr = $this->db->gettablefields('[Q]'.$tab.'');
		if(!$farr){
			$sql = "CREATE TABLE `[Q]".$tab."` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) DEFAULT '',
  `state` tinyint(1) DEFAULT '0' COMMENT '状态',
  `agentid` int(11) DEFAULT '0' COMMENT '对应应用id',
  `mobile` varchar(50) DEFAULT NULL COMMENT '关联手机号',
  `uid` int(11) DEFAULT '0' COMMENT '对应OA用户id',
  `cnum` varchar(30) DEFAULT NULL COMMENT '关联单位编号',
  PRIMARY KEY (`id`),
  KEY `mobile` (`mobile`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='关联信呼企业微信平台用户';";
			$this->db->query($sql);
		}
		$db  = m($tab);
		foreach($rows as $k=>$rs){
			$zt 	= '0';
			$toid 	= '0';
			$ors	= $db->getone("`mobile`='{$rs['mobile']}'");
			if($ors){
				$zt 	= $ors['state'];
				$toid 	= $ors['id'];
			}
			$rows[$k]['zt'] = $zt;
			$rows[$k]['toid'] = $toid;
			$rows[$k]['mobile']		= substr($rs['mobile'],0,3).'****'.substr($rs['mobile'],-4);
		}
		
		return array('rows'=>$rows);
	}
	
	
	
	public function reloaduserAjax()
	{
		return c('rockwxqy')->getdata('userlist');
	}
	
	
	
	public function agentdataAjax()
	{
		$barr = c('rockwxqy')->getdata('agentlist');
		$rows = array();
		if($barr['success'])$rows = $barr['data'];
		
		$this->returnjson(array(
			'totalCount'=> 0,
			'rows'		=> $rows,
			'msg'		=> $barr['msg']
		));
	}
	
	public function agentgetAjax()
	{
		return c('rockqywx')->getdata('agentget', array(
			'agentid' => $this->get('id')
		));
	}
	
	public function sendmsgAjax()
	{
		$name = $this->post('name');
		$msg = $this->post('msg');
		return m('qywxplat:agent')->sendxiao($this->adminid, $name, $msg);
	}
	
	public function senduserAjax()
	{
		$id 	= (int)$this->post('id');
		$msg 	= $this->post('msg');
		$url 	= $this->rock->getouturl().'?d=we';
		$urs 	= m('admin')->getone($id);
		return c('rockwxqy')->sendmess($id, '测试发给:'.$urs['name'].'', $msg, $url,'', true);
	}
	
	public function restateAjax()
	{
		$id 	= (int)$this->get('id');
		$urs 	= m('admin')->getone($id);
		$mobile = $urs['mobile'];
		if(!$mobile)return returnerror('未设置手机号');
		$uid 	= $urs['id'];
		$dbs 	= m('zwxqy_user');
		$barr = c('rockwxqy')->getdata('userstate', array(
			'mobile' => $mobile,
			'user' 	 => $urs['user'],
		));
		if(!$barr['success']){
			$dbs->delete('uid='.$uid.'');
			return $barr;
		}
		$data 	 = $barr['data'];
		$userid  = $data['userid'];
		$agentid = $data['agentid'];
		$cnum 	 = $data['cnum'];
		
		$uarr['uid'] 	 = $uid;
		$uarr['mobile']  = $mobile;
		$uarr['userid']  = $userid;
		$uarr['agentid'] = $agentid;
		$uarr['cnum'] 	 = $cnum;
		$uarr['state']   = $data['state'];
		$ors	= $dbs->getone("`uid`='$uid'");
		if($ors){
			$dbs->update($uarr, $ors['id']);
		}else{
			$dbs->insert($uarr);
		}
		return returnsuccess($data);
	}
	
	public function uqingkongAjax()
	{
		m('zwxqy_user')->delete('1=1');
		return returnsuccess();
	}
}