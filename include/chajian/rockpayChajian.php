<?php 
/**
*	统一支付平台
*/
class rockpayChajian extends Chajian{
	
	private $apiurl 	= '';
	private $agentkey 	= '';
	
	protected function initChajian()
	{
		$this->apiurl 	= getconfig('rockpay_url');
		$this->agentkey = getconfig('rockpay_agentkey');
	}
	
	public function isConfig()
	{
		if($this->apiurl && $this->agentkey)return true;
		return false;
	}
	
	public function setConfig($url, $key)
	{
		if($url)$this->apiurl 	= $url;
		if($key)$this->agentkey = $key;
		return $this;
	}
	
	private function geturl($act,$can=array())
	{
		$url = $this->apiurl.'?m=openorder&a='.$act.'&agentkey='.$this->agentkey.'';
		foreach($can as $k=>$v)$url.='&'.$k.'='.$v.'';
		return $url;
	}
	
	private function resultrun($result)
	{
		if(!$result || !contain($result, 'success') || substr($result,0,1)!='{')return returnerror('接口返回错误：'.$result.'');
		$barr = json_decode($result, true);
		return $barr;
	}
	
	
	/*
	*	创建订单返回支付地址
	**/
	public function createOrder($ordernum, $money, $subject, $body='', $data=array())
	{
		if(!$this->apiurl || !$this->agentkey)return returnerror('未配置支付平台地址');
		$data['ordernum'] 	= $ordernum;
		$data['money'] 	 	= $money;
		$data['subject'] 	= $subject;
		$data['body'] 		= $body;
		$callurl			= getconfig('outurl',URL).'api.php?m=order&a=bcall&ordernum='.$ordernum.'';
		$data['callurl'] 	= urlencode($callurl);
		$url 				= $this->geturl('createOrder', array());
		$result 			= c('curl')->postcurl($url, $data);
		return $this->resultrun($result);
	}
	
	/**
	*	查询订单新版2026
	*/
	public function orderQuery($ordernum)
	{
		$url 	= $this->geturl('orderQuery', array(
			'ordernum' => $ordernum
		));
		$result = c('curl')->getcurl($url);
		return $this->resultrun($result);
	}
}                               