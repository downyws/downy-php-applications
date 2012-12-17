<?php
class ActionConnect extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodLogin()
	{
		// 获取网站接入列表
		$connectObj = Factory::getModel('connect');
		$connects = $connectObj->getAllPairs('id', 'key', CONNECT_STATUS_DEFAULT, true);

		$params = $this->_submit->obtain($_REQUEST, array(
			'name' => array(array('format', 'trim'), array('valid', 'in', '所选的网站登录不存在', null, $connects)),
			'callback' => array(array('valid', 'url', '', APP_URL, 0))
		));

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			var_Dump($this->_submit->errors);
		}
		else
		{
echo		$params['name'];
		}
		exit;
	}
}
