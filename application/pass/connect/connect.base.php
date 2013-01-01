<?php
class ConnectBase
{
	public $_base_url = '';
	public $_config;
	public $_connectObj;
	public $_curl;

	public function __construct()
	{
		$this->_base_url = APP_URL . 'index.php?a=connect&m=login&name=' . $this->_name;

		$this->_config = $GLOBALS['CONFIG']['CONNECT'][strtoupper($this->_name)];

		$this->_connectObj = Factory::getModel('connect');

		Factory::loadLibrary('curlhelper');
		$this->_curl = new CurlHelper($GLOBALS['CONFIG']['CURL']);
	}

	public function redirect()
	{
		$route = !empty($_REQUEST['r']) ? strtolower($_REQUEST['r']) : '';
		$routeName = 'route' . $route;
		if(is_callable(array($this, $routeName)))
		{
			$this->$routeName();
		}
		$this->message(PAGE_NOT_EXISTS);
	}

	public function login($connect_id, $outer_id, $data)
	{
		if($this->_connectObj->login($connect_id, $outer_id))
		{
			header('location: ' . $_COOKIE['CONNECT_CALLBACK']);
		}
		else
		{
			$errors = $this->_connectObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				$this->message(end($errors));
			}
		}
		exit;
	}

	public function message($code)
	{
		var_dump('coding...');
		// array('message' => $GLOBALS['MESSAGE'][$code], 'code' => $code);
		var_dump($code);
		if(!empty($GLOBALS['MESSAGE'][$code]))
		{
			var_dump($GLOBALS['MESSAGE'][$code]);
		}
		exit;
	}

	public function formatData($data, $type = '', $ext = '')
	{
		if($type == 'callback' || strpos($data, 'callback(') !== false)
		{
			$lpos = strpos($data, '(');
			$rpos = strrpos($data, ')');
			$data = substr($data, $lpos + 1, $rpos - $lpos -1);
			$result = json_decode($data, true);
		}
		else if($type == 'url')
		{
			parse_str($data, $result);
		}
		else if($type == 'json')
		{
			$result = json_decode($data, true);
		}
		return $result;
	}
}
