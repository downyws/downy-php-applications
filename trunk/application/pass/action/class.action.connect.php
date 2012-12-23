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
			'name' => array(array('format', 'trim'), array('valid', 'in', '', null, $connects)),
			'callback' => array(array('valid', 'url', '', APP_URL, 0))
		));

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			$this->redirect('', 404);
		}
		else
		{
		// 	include_once(APP_DIR_CONNECT . $params['name']
		//	$params['callback']
		//	
		}
		exit;
	}
}
