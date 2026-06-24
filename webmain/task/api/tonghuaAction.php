<?php 
class tonghuaClassAction extends apiAction
{
	/**
	*	通话初始化
	*/
	public function thinitAction()
	{
		if(!getconfig('video_bool'))return returnerror('系统未开启音视频');
		$id 		= (int)$this->get('id');
		$type 		= (int)$this->get('type');
		$rtctype 	= 1; //0，1腾讯
		if($id==$this->adminid)return returnerror('不能和自己通话');
		$nowtime 	= strtotime($this->now);
		
		$allfields	= $this->db->getallfields('[Q]im_tonghua');
		if(!in_array('toid', $allfields)){
			$this->db->addFields('[Q]im_tonghua','toid','int(11)','0','对于人id可能是组');
		}
		
		
		//判断是不是在通话中
		$thrs 		= m('im_tonghua')->getone('(`faid`='.$id.' or `toid`='.$id.') and (`state` in(0,4) or (`state`=1 and enddt is null))', '*', 'id desc');
		if($thrs){
			$time  	= time() - strtotime($thrs['adddt']);
			$state	= (int)$thrs['state'];
			$stime  = 60;
			if($state == 1)$stime = 30* 60;
			if($time < $stime)return returnerror('对方忙线');//60秒内
		}
		
		//判断用户有没有在线
		$gbarr = m('reim')->pushserver('getonline', array(
			'onlineid' => $id
		));
		if(!$gbarr)return returnerror('没有服务端');

		
		if(!$gbarr['success'])return $gbarr;
		$online = false;
		
		$sdata  = arrvalue($gbarr,'data');
		if($sdata){
			if(c('check')->isjson($sdata)){
				$ondats = json_decode($sdata, true);
				if($ondats && isset($ondats['pc'])){
					if($ondats['pc']==$id)$online = true;
					if($ondats['app']==$id)$online = true;
				}
			}else{
				if(contain(','.$sdata.',',','.$id.','))$online = true;
			}
		}
		
		if(!$online){
			$trows 	= m('login')->getall('`uid`='.$id.' and `online`=1 and `ispush`=1');
			if(!$trows)return returnerror('对方不在线，无法通话');
			
			$appfw  = $this->option->getval('reimappwxsystem');
			if($appfw != '1')return returnerror('服务端没开启APP可用');
			
			$isbo 	= true;
			foreach($trows as $k=>$rs){
				$web = $rs['web'];
				if(!contain($web, 'iphone'))$isbo = false;
			}
			//if($isbo)return returnerror('对方使用iphone，暂不支持通话');
		}
		
		$barr	= c('xinhuapi')->getdata('tonghua','thinit', array('faid'=>$this->adminid,'rtctype'=>$rtctype,'nowtime'=>$nowtime,'toid'=>$id,'type'=>$type));
		if(!$barr['success'])return $barr;
		$data 	= $barr['data'];
		$key 	= $data['channel'];
		c('cache')->set($key, $data, 60);
		
		//保存自己通话里面
		m('im_tonghua')->insert(array(
			'uid' 	=> $this->adminid,
			'faid' 	=> $this->adminid,
			'channel' =>$data['channel'],
			'type' 	  =>$data['type'],
			'plat' 	  =>$rtctype,
			'joinids' =>$id,
			'toid' 	  =>$id,
			'adddt' 	=>$this->now,
		));
		
		//异步发送
		c('rockqueue')->push('tonghua,call', array('key' => $key,'cishu'=>1));

		return $barr;	
	}
	
	
	/**
	*	取消呼叫
	*/
	public function cancelAction()
	{
		$channel = $this->get('channel');
		$state 	 = (int)$this->get('state','3');
		m('im_tonghua')->update('`state`='.$state.'',"`channel`='$channel'");
		$barr = c('rockqueue')->push('tonghua,cancel', array('key' => $channel));
		if(!$barr['success'])return $barr;
		return returnsuccess();
	}
	
	/**
	*	接电话了(0呼叫中,1同意，2拒绝,3取消，4接受者已打开页面，5呼叫超过30秒无人接听)
	*/
	public function jieAction()
	{
		$channel = $this->get('channel');
		$state 	 = (int)$this->get('state','2');
		$dbs 	 = m('im_tonghua');
		$onrs	 = $dbs->getone("`channel`='$channel'");
		$satype	 = '';
		if(!$onrs)return returnerror('通话不存在');
		$zt 	 = $onrs['state'];
		if($zt == '3' || $zt=='5')return returnerror('对方已取消');
		
		if($zt=='1')return returnerror('已在另端接通');
		if($zt=='2')return returnerror('已在另端拒绝');
		
		$nowtime 	= strtotime($this->now);
		$upstsr		= '`state`='.$state.'';
		if($state==1)$upstsr.=",`jiedt`='$this->now'";
		$dbs->update($upstsr,"`channel`='$channel'");
		$barr = c('rockqueue')->push('tonghua,jie', array('key'=>$channel,'nowtime'=>$nowtime,'uid'=>$this->adminid,'state'=>$state));
		if(!$barr['success'])return $barr;
		
		return returnsuccess(array(
			'satype' => ''
		));
	}
	
	/**
	*	接通
	*/
	public function jietongAction()
	{
		$channel 	= $this->get('channel');
		$barr		= c('xinhuapi')->getdata('tonghua','jietong', array('uid'=>$this->adminid,'channel'=>$channel));
		if($barr['success']){
			$bars = $this->jieAction();
			if(!$bars['success'])return $bars;
			$datas= $bars['data'];
			foreach($datas as $k=>$v)$barr['data'][$k] = $v;
		}
		return $barr;
	}
	
	/**
	*	结束通话
	*/
	public function jiesuAction()
	{
		$nowtime 	= strtotime($this->now);
		$channel 	= $this->get('channel');
		$toid 		= (int)$this->get('toid');
		c('rockqueue')->push('tonghua,jiesu', array('uid'=>$this->adminid,'toid'=>$toid,'nowtime'=>$nowtime,'channel'=>$channel));
		m('im_tonghua')->update("`enddt`='$this->now',`jieid`='$this->adminid'","`channel`='$channel'");
		return returnsuccess();
	}
	
	/**
	*	接受者打开了界面
	*/
	public function receopenAction()
	{
		$channel 	= $this->get('channel');
		$where 		= "`channel`='$channel'";
		$dbs = m('im_tonghua');
		$dbs->update('`state`=4', $where);
		$thrs		= $dbs->getone($where);
		$sytime = time()-strtotime($thrs['adddt']);
		return returnsuccess(array(
			'sytime' => $sytime
		));
	}
	
	/**
	*	时时读取状态
	*/
	public function stateAction()
	{
		$channel 	= $this->get('channel');
		$onrs 		= m('im_tonghua')->getone("`channel`='$channel'");
		$tayar 		= array('call','tongyi','jujue','cancel','wait','cancel','end');
		return returnsuccess(array(
			'state'  	=> arrvalue($tayar, $onrs['state']),
			'th_channel'=> $channel
		));
	}
	
	/**
	*	判断通话是不是结束
	*/
	public function statethAction()
	{
		$channel 	= $this->get('channel');
		$onrs 		= m('im_tonghua')->getone("`channel`='$channel'");
		$state		= 'wu';
		if($onrs && !isempt($onrs['enddt']))$state = 'jiesu';
		
		return returnsuccess(array(
			'state' => $state
		));
	}
}