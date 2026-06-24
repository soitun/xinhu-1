<?php 
class minute5ClassAction extends runtAction
{
	public $startdt,$enddt,$enddtss;
	public function runAction()
	{
		$time 	= time();
		$time1 	= $time;
		$time2 	= $time1+5*60;
		$time3 	= $time1-5*60;
		$this->startdt	= date('Y-m-d H:i:s', $time1);	
		$this->enddt	= date('Y-m-d H:i:s', $time2);
		$this->enddtss	= date('Y-m-d H:i:s', $time3);
		$this->scheduletodo();
	
		m('flowbill')->autocheck(); //自动审批作废
		m('reim')->chatpushtowx($this->enddtss); //REIM消息
		m('log')->readPHPerr();//读取错误日志记录
		
		return 'success';
	}
	
	private function scheduletodo()
	{
		if($this->moderock('schedule'))m('schedule')->gettododata();//日程
		if($this->moderock('remind'))m('remind')->todorun();//单据
		if($this->moderock('meet'))m('flow')->initflow('meet')->meettodo(); //会议提醒的
	}
	
}