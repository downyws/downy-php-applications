<?php
class ActionSend extends ActionCommon
{
	public $NOT_LOGIN = array('crontab');
	public $NOT_POWER = array();
	public $RUN_LONG_TIME = array('crontab');

	public function __construct()
	{
		parent::__construct();
	}

	public function methodCrontabApi()
	{
		$params = $this->_submit->obtain(array(
			'salt' => array(array('format', 'trim'), array('valid', 'eq', '安全码错误', null, CRONTAB_SALT)),
			'channel_id' => array(array('format', 'int'), array('valid', 'gt', '通道编号错误', null, 0))
		));

		// 保存
		if(count($this->_submit->errors) > 0)
		{
			$message = implode('，', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			// 定时脚本
		}
		
		// 返回
		$this->jsonout($result);
	}
}
