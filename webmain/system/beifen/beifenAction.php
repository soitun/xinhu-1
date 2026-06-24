<?php
@set_time_limit(3600);
class beifenClassAction extends Action
{
	
	public function chushuaAjax()
	{
		$myext		= $this->getsession('adminallmenuid');
		if(getconfig('systype')=='demo')return '演示请勿操作';
		if($myext!='-1')return '只有管理员才可以用';
		
		$tabstr 	= 'daily,file,files,flow_log,flow_todos,flow_checks,im_history,im_mess,im_messzt,infor,infors,log,logintoken,meet,reads,sjoin,work,todo,flow_chao,flow_bill,flow_remind,goodm,goodn,goodss,goods,kqanay,kqdkjl,kqerr,kqout,kqinfo,location,official,officialfa,officialhong,schedule,scheduld,project,userinfo,userinfos,userract,hrpositive,word,hrredund,hrsalary,customer,custsale,custract,custfina,custappy,assetm,book,bookborrow,carm,carms,carmang,carmrese,email_cont,emailm,emails,sealapl,vcard,tovoid,editrecord,wouser,dailyfx,knowtraim,knowtrais,fininfom,fininfos,hrtrsalary,hrtransfer,hrdemint,reward,offyuebd,repair,knowtiku,kqdisv,knowledge,kqjcmd,kqjuser,kqjsn,hrcheck,receipt,hrcheckn,hrchecks,hrkaohem,hrkaohes,hrkaohen,demo,finpiao,wordxie,wordeil,subscribe,subscribeinfo,news,finzhang,finkemu,finount,finjibook,custplan,wenjuan,wenjuat,wenjuau,dangan,danganjy,wotpl,seal,godepot,im_tonghua,bianjian';
		$mrows		= m('mode')->getall("`id`>=108 and `type`<>'系统'");
		foreach($mrows as $k1=>$rs1){
			if(!isempt($rs1['table']))$tabstr.=','.$rs1['table'].'';
			if(!isempt($rs1['tables']))$tabstr.=','.$rs1['tables'].'';
		}
		$tables		= explode(',', $tabstr);
		$alltabls 	= $this->db->getalltable();
		foreach($tables as $tabs){
			$_tabs 	= ''.PREFIX.''.$tabs.'';
			$yunbo  = false;
			if(in_array($_tabs, $alltabls) || !$alltabls)$yunbo = true;
			if($yunbo){
				$sql1 = "delete from `$_tabs`";
				$sql2 = "alter table `$_tabs` AUTO_INCREMENT=1";
				$this->db->query($sql1, false);
				$this->db->query($sql2, false);
			}
		}
		$this->option->delpid('-2,-102'); //收信的清空
		if(!getconfig('platdwnum')){
			m('company')->delete('id>1');
			$sql2 = "alter table `[Q]company` AUTO_INCREMENT=1";
			$this->db->query($sql2, false);
		}
		echo 'ok';
	}
	
	public function beifenAjax()
	{
		m('beifen')->start();
		echo 'ok';
	}
	
	public function getdataAjax()
	{
		if(getconfig('systype')=='demo')exit('演示请勿操作');
		$carr = c('file')->getfolderrows(''.UPDIR.'/data');
		$rows = array();
		$len  = count($carr);
		$oux  = 0;
		for($k=$len-1;$k>=0;$k--){
			if($oux>100)break;
			$fils = $carr[$k];
			$fils['xu'] 	  = $k;
			$rows[] = $fils;
			$oux++;
		}
		if($rows)$rows = c('array')->order($rows, 'filename');
		$arr['rows'] = $rows;
		$this->returnjson($arr);
	}
	
	public function getdatssssAjax()
	{
		if(getconfig('systype')=='demo')exit('演示请勿操作');
		$rows = array();
		$folder = $this->post('folder');
		$folder	= str_replace(array('..','/'),'', $folder);
		$path 	= ''.UPDIR.'/data/'.$folder.'';
		$carr 	= c('file')->getfilerows($path);
		foreach($carr as $k=>$rs){
			$id 	= $rs['filename'];
			$ids 	= substr($id,0,-5);
			$ida 	= explode('_', $ids);
			$len 	= count($ida);
			$fieldshu = $ida[$len-2];
			$total 	= $ida[$len-1];
			$fields = str_replace('_'.$fieldshu.'_'.$total.'.json','', $id);
			$filepath = $path.'/'.$id.'';
			if(file_exists($filepath)){
				$filesize = filesize($filepath);
				$rows[] = array(
					'fields' 	=> $fields,
					'fieldshu' 	=> $fieldshu,
					'total' 	=> $total,
					'id'		=> $id,
					'filesizecn'=> $this->rock->formatsize($filesize)
				);
			}
		}

		$arr['rows'] = $rows;
		$this->returnjson($arr);
	}
	
	public function huifdatanewAjax()
	{
		if(getconfig('systype')=='demo')exit();
		if($this->adminid!=1)return '只有ID=1的管理员才可以用';
		$folder = $this->post('folder');
		$sida 	= explode(',', $this->post('sid'));
		$alltabls 	= $this->db->getalltable();
		$shul 	= 0;
		$tablss	= '';
		foreach($sida as $id){
			$ids 	= substr($id,0,-5);
			$ida 	= explode('_', $ids);
			$len 	= count($ida);
			$fieldshu = $ida[$len-2];
			$total 	= $ida[$len-1];
			$tab 	= str_replace('_'.$fieldshu.'_'.$total.'.json','', $id); //表
			
			$filepath = ''.UPDIR.'/data/'.$folder.'/'.$id.'';
			if(!file_exists($filepath))continue;
			
			$data 	  = m('beifen')->getbfdata('',$filepath);
			if(!$data)continue;
			
			$dataarr	= $data[$tab];

			//表不存在
			if(!in_array($tab, $alltabls)){
				$createsql = arrvalue($dataarr, 'createsql');
				if($createsql){
					$this->db->query($createsql, false);
				}else{
					continue;
				}
			}
			
			$dataall 	= $dataarr['data'];
			if(count($dataall)<=0)continue; //没有数据
			
			$allfields 	= $this->db->getallfields($tab);
			$fistdata	= $dataall[0];
			$xufarr		= array();
			foreach($fistdata as $f=>$v){
				if(in_array($f, $allfields)){
					$xufarr[] = $f;
				}
			}
			$uparr	= array();
			foreach($dataall as $k=>$rs){
				$str1 	= '';
				$upa	= array();
				foreach($xufarr as $f){
					$upa[$f] = $rs[$f];
				}
				$uparr[] = $upa;
			}
			
			$sql1 	= "delete from `$tab`";
			//$sql2 	= "alter table `$tab` AUTO_INCREMENT=1";
			$bo 	= $this->db->query($sql1, false);
			//$bo 	= $this->db->query($sql2, false);
			foreach($uparr as $k=>$upas){
				$bo = $this->db->record($tab, $upas);
			}
			$shul++;
			$tablss.=','.$tab.'';
		}
		return ''.$tablss.'表已恢复';
	}
	
	/**
	*	还原数据操作（2017-08-27弃用）
	*/
	public function huifdataAjax()
	{
		if(getconfig('systype')=='demo')exit('演示请勿操作');
		$xu   = (int)$this->post('xu');
		$carr = c('file')->getfilerows(''.UPDIR.'/data');
		$sida = explode(',', $this->post('sid'));
		$rows = array();
		if(isset($carr[$xu])){
			$file = $carr[$xu]['filename'];
			$data = m('beifen')->getbfdata($file);
			if($data){
				$alltabls 	= $this->db->getalltable();
				foreach($sida as $tab){
					if(!isset($data[$tab]))continue;
					if(!in_array($tab, $alltabls))continue; //表不存在
					$dataall 	= $data[$tab]['data'];
					if(count($dataall)<=0)continue;
					
					$allfields 	= $this->db->getallfields($tab);
					$fistdata	= $dataall[0];
					$xufarr		= array();
					foreach($fistdata as $f=>$v){
						if(in_array($f, $allfields)){
							$xufarr[] = $f;
						}
					}
					$uparr	= array();
					foreach($dataall as $k=>$rs){
						$str1 	= '';
						$upa	= array();
						foreach($xufarr as $f){
							$upa[$f] = $rs[$f];
						}
						$uparr[] = $upa;
					}
					
					$sql1 	= "delete from `$tab`";
					$sql2 	= "alter table `$tab` AUTO_INCREMENT=1";
					$bo 	= $this->db->query($sql1, false);
					$bo 	= $this->db->query($sql2, false);
					foreach($uparr as $k=>$upas){
						$bo = $this->db->record($tab, $upas);
					}
				}
			}
		}
		echo 'ok';
	}
	
	public function chushuserAjax()
	{

		if(getconfig('systype')=='demo')return '演示请勿操作';
		if($this->adminid!=1)return '只有ID=1的管理员才可以用';
		
		$users	= "'diaochan','zhangfei','daqiao','xiaoqiao','zhaozl','rock','xinhu'";
		$dbs	= m('admin');
		$dba	= m('dept');
		
		//默认可以多次初始化
		if($this->option->getval('sysuserinit', '是') != '是')
			if($dbs->rows("`user` in($users)")==0)return '可能你已初始化过用户和部门了，要开启可到【流程模块→数据选项】系统选项下开启';
		
		$dbs->delete('id>1');
		$this->db->query('alter table `[Q]admin` AUTO_INCREMENT=2', false);
		
		$dba->delete('id>1');
		$this->db->query('alter table `[Q]dept` AUTO_INCREMENT=2', false);
		
		$this->db->query('delete from `[Q]userinfo`', false);
		$this->db->query('delete from `[Q]userinfos`', false);
		$this->db->query('alter table `[Q]userinfos` AUTO_INCREMENT=1', false);
		
		return 'ok';
	}
	
	
	public function fenbiaoAjax()
	{
		$allfields = $this->db->getallfields('[Q]flow_set');
		if(!in_array('logstr', $allfields)){
			$bool = $this->db->query("alter table `[Q]flow_set` add `logstr` varchar(500) DEFAULT NULL COMMENT '操作记录分表';", false);
			if(!$bool)return '无法操作:'.$this->db->error().'';
		}
		
		$obj 	= m('mode');
		$rows 	= $obj->getall('1=1');
		$atable = array();
		$count 	= m('flow_log')->rows('1=1');
		//if($count < 10000)return '操作记录少于1W条，不需要分表';
		
		$alltabls 	= $this->db->getalltable();
		$barr		= $this->createbiao(1, $alltabls);
		if(!$barr['success'])return $barr['msg'];
		$biao 		= $barr['data'];
		if(1==1)foreach($rows as $k=>$rs){
			$tab = $rs['table'];
			if(!isset($atable[$tab])){
				$max = m($tab)->getmou('max(id) as ids', 'id>0');
				if(!$max)$max = 0;
				$max++;
				$atable[$tab] = $max;
			}
			$max 	= $atable[$tab];
			
			$logstr = $rs['logstr'];
			if(isempt($logstr)){
				$logarr = array();
			}else{
				$logarr = json_decode($logstr, true);
			}
			if(!isset($logarr[$max]))$logarr[$max] = $biao;
			$logstr = json_encode($logarr);
			
			$obj->update(array(
				'logstr' => $logstr
			), $rs['id']);
		}
		
		//更新
		$sql = "update `[Q]file` set `mtype`='flow_log".$biao."' where `mtype`='flow_log'";
		$bool = $this->db->query($sql, false);
		
		return 'ok';
	}
	
	public function createbiao($xu, $alltabls)
	{
		$biao 	= 'a'.$xu.'';
		$lognab = 'flow_log'.$biao.'';
		if(in_array(''.PREFIX.''.$lognab.'',$alltabls))return $this->createbiao($xu+1, $alltabls);
		
		$maxlog	= (int)m('flow_log')->getmou('max(id) as ids', 'id>0') + 1;
		
		$sql 	= 'ALTER TABLE `[Q]flow_log` RENAME TO `[Q]'.$lognab.'`;';
		$bool	= $this->db->query($sql, false);
		if(!$bool)return returnerror('无法操作:'.$this->db->error().'');
		
		$sql = "CREATE TABLE `[Q]flow_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(50) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '1通过',
  `statusname` varchar(20) DEFAULT NULL COMMENT '状态名称',
  `name` varchar(50) DEFAULT NULL COMMENT '进程名称',
  `courseid` int(11) DEFAULT NULL,
  `optdt` datetime DEFAULT NULL COMMENT '操作时间',
  `explain` varchar(500) DEFAULT NULL COMMENT '说明',
  `ip` varchar(100) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL COMMENT '浏览器',
  `checkname` varchar(50) DEFAULT NULL COMMENT '审核人',
  `checkid` int(11) DEFAULT '0' COMMENT '审核人id',
  `modeid` smallint(6) DEFAULT NULL COMMENT '@模块Id',
  `color` varchar(10) DEFAULT NULL,
  `valid` tinyint(1) DEFAULT '1',
  `step` smallint(6) DEFAULT '0' COMMENT '步骤号',
  `qmimg` text COMMENT '签名的图片base64',
  `iszb` tinyint(1) DEFAULT '0' COMMENT '是否转办记录',
  PRIMARY KEY (`id`),
  KEY `table` (`table`,`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=".$maxlog." DEFAULT CHARSET=utf8 COMMENT='单据操作记录';
";
		$bool	= $this->db->query($sql, false);
		if(!$bool)return returnerror('无法创建表:'.$this->db->error().'');
		
		return returnsuccess($biao);
	}
}