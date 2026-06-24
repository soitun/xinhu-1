<?php 
/**
*	天地图的使用
*/
class tiandituChajian extends Chajian{
	
	private $tdtmapkey = '';
	
	protected function initChajian()
	{
		$this->tdtmapkey  = getconfig('tdtmapkey');
		if($this->tdtmapkey == ''){
			$this->tdtmapkey = $this->rock->jm->base64decode('YzM2N2I2MGNjNGIzZmYxOTUwMGNlY2EzYTU3MGFmZDM:');
		}
	}
	
	public function getKey()
	{
		return $this->tdtmapkey;
	}
	
	/**
	*	根据IP获取坐标信息
	*/
	public function ipinfo($ip)
	{
		$url 	= c('xinhu')->geturlstr('query', array(
			'm' => 'ip',
			'ip'=> $ip
		));
		$result = c('curl')->getcurl($url);
		if($result && contain($result, 'country')){
			return returnsuccess(json_decode($result, true));
		}else{
			return returnerror('iperr'.$result);
		}
	}
}                                                                                                                                                            