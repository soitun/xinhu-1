<?php
/**
*	添加方法日志，和IP限制判断
*/
function ipwhiteshow($ip, $rock){
	$iplist = ''.ROOT_PATH.'/config/iplist.php';
	$bool 	= 0;
	if(file_exists($iplist)){
		$iparr 	= require($iplist);
	}else{
		$iparr 	= array(
			'blackip' 	=> '',
			'whiteip'	=> '',
			'gaptime'	=> 0,		
			'gapnums'	=> 0		
		);
	}
	
	//白名单判断
	$whiteip = $iparr['whiteip'];
	if($whiteip!=''){
		$whiteipa = explode(',', $whiteip);
		foreach($whiteipa as $ips){
			$bo = strpos($ip, $ips);
			if($bo===0 || $ips=='*'){
				$bool = 1; //可以访问
				break;
			}
		}
	}
	
	//黑名单判断
	if($bool==0){
		$blackip = $iparr['blackip'];
		if($blackip!=''){
			$blackipa = explode(',', $blackip);
			foreach($blackipa as $ips){
				$bo = strpos($ip, $ips);
				if($bo===0 || $ips=='*'){
					$bool = 2;//不能访问
					break;
				}
			}
		}
	}

	//创建访问日志
	if(getconfig('accesslogs')){
		$str = '';
		foreach($_SERVER as $k=>$v)$str.='['.$k.']:'.$v.chr(10).'';
		
		$str1 = '';
		foreach($_GET as $k=>$v)$str1.='['.$k.']:'.$v.chr(10).'';
		
		$str2 = '';
		foreach($_POST as $k=>$v)$str2.='['.$k.']:'.$v.chr(10).'';
		$act  = arrvalue($_SERVER,'REQUEST_METHOD');
		if($act=='POST' && $str2==''){
			$str2 = arrvalue($GLOBALS, 'HTTP_RAW_POST_DATA');
		}

		$logs = ''.UPDIR.'/logs/'.date('Y-m-d').'/'.date('H').'/'.date('H.i.s').'_'.$act.'_'.$ip.'_'.rand(100,999).'.log';
$logstr = '[datetime]:'.$rock->now.'
[URL]:'.$rock->nowurl().'	
[ACTION]:'.$act.'
[IP]:'.$ip.'
[GET]
'.$str1.'
[POST]
'.$str2.'
[SERVER]
'.$str.'	
';
		$rock->createtxt($logs, $logstr);
	}
	$msg 	= '';
	
	if($bool==2){
		$msg 	= '您IP['.$ip.']禁止访问我们站点';
	}
	
	$gaptime = (int)arrvalue($iparr, 'gaptime', '0');
	$gapnums = (int)arrvalue($iparr, 'gapnums', '0');
	$adminid = (int)$rock->session('adminid',0);
	if($bool==0 && !$msg && $gapnums > 0 && $adminid==0){
		$key  = 'accessstate';
		$cish = (int)$rock->session($key.'cishu','0');
		$ltime= floatval($rock->session($key, '0'));
		$ntime= floatval(time());
		$jtime= $ntime - $ltime;
		if($jtime > $gaptime)$cish= 0;
		$cish ++;
		if($jtime <= $gaptime && $cish > $gapnums){
			$msg = '您访问速度太快了的'.$cish.'';
			$cish= 0;
		}
		$rock->setsession($key, $ntime);
		$rock->setsession($key.'cishu', $cish);
	}
	
	//区域限制的
	$whitecity = arrvalue($iparr, 'whitecity');
	if(!$msg && $bool==0 && $whitecity && !c('check')->isneiurl('http://'.$ip)){
		$key   = 'ip_'.$ip.'';$cache = c('cache');
		$result= $cache->get($key);
		if(!$result){
			$result = c('curl')->getcurl(''.base64_decode('aHR0cDovL3d3dy5yb2Nrb2EuY29tLz9tPWlwJmE9cXVlcnkmaXA9').''.$ip.'&xinhukey='.getconfig('xinhukey').'');//查询IP归属地
			if($result && contain($result, 'country')){
				$cache->set($key, $result);
			}else{
				$msg = $result;
				$result = '';
			}					
		}
		if(!$result){
			$msg 	 = '接口失效无法识别访问区域'.$msg.'';
		}else{
			$json	 = json_decode($result, true);
			$country = arrvalue($json, 'country');
			$region  = arrvalue($json, 'region');
			$city 	 = arrvalue($json, 'city');
			$xian 	 = arrvalue($json, 'xian');
			$whitea	 = explode(',', $whitecity);
			$inbool	 = false;
			foreach($whitea as $ctys){
				if($country && stripos($country, $ctys)===0)$inbool = true;
				if(!$inbool && $region && stripos($region, $ctys)===0)$inbool = true;
				if(!$inbool && $city && stripos($city, $ctys)===0)$inbool = true;
				if(!$inbool && $xian && stripos($xian, $ctys)===0)$inbool = true;
				if($inbool)break;
			}
			if(!$inbool)$msg = '您的IP['.$ip.''.$country.$region.$city.$xian.']区域禁止访问我们站点';
		}
	}
	
	if($msg){
		@file_put_contents(''.ROOT_PATH.'/'.UPDIR.'/phperrors.log','['.$rock->now.']'.$ip.''.$msg.''.chr(10).'',FILE_APPEND);
		$cfrom  = $rock->get('cfrom');
		$msg .= '，有问题请联系我们';
		if($cfrom == 'nppandroid' || $cfrom == 'nppios')$msg = json_encode(returnerror($msg));
		exit($msg);
	}
}

function ipwhiteshows($ips, $rock){
	$ipa = explode(',', $ips); 
	foreach($ipa as $ip)ipwhiteshow($ip, $rock);
}
ipwhiteshows($rock->ip, $rock);