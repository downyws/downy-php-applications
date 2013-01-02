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
		$connects = $connectObj->getAllPairs('id', 'key', STATUS_DEFAULT, true);

		$params = $this->_submit->obtain($_REQUEST, array(
			'name' => array(array('format', 'trim'), array('valid', 'in', '', null, $connects)),
			'callback' => array(array('valid', 'url', '', APP_URL, 0)),
			'stamp' => array(array('format', 'trim'))
		));

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			$this->redirect('', 404);
		}
		else
		{
			// 保存回调地址
			!$params['stamp'] && setcookie('CONNECT_CALLBACK', $params['callback'], time() + 900);
			// 打开网站接入
			include_once(APP_DIR_CONNECT . 'connect.' . $params['name'] . '.php');
			$class = 'connect' . $params['name'];
			$connect = new $class(array('action' => $this));
			$connect->redirect();
		}
	}
}
