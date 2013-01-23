<?php

class Action
{
	public $_tpl;
	public $_submit;
	
	public function __construct()
	{
		$this->_submit = new Submit();
	}

	public function initTemplate($is_must = false)
	{
		if($is_must || empty($this->_tpl))
		{
			require_once LIBRARY_DIR . 'smarty/library.smarty.php';
			$this->_tpl = new Smarty();
			$this->_tpl->template_dir = APP_DIR_TEMPLATE;
			$this->_tpl->cache_dir = APP_DIR_CACHE . 'smarty/page/';
			$this->_tpl->compile_dir = APP_DIR_CACHE . 'smarty/compile/';
			$this->_tpl->left_delimiter = '{';
			$this->_tpl->right_delimiter = '}';
			$this->_tpl->error_reporting = E_ALL;
		}
	}

	public function assign($var, $val)
	{
		$this->_tpl->assign($var, $val);
	}
	
	public function render($template)
	{
		$this->_tpl->display($template);
	}

	public function fetch($string)
	{
		return $this->_tpl->fetch('string:' . $string);
	}

	public function redirect($url, $code = 302)
	{
		switch($code)
		{
			case 301:
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $url);
				exit;
			case 302:
				header('HTTP/1.1 302 Moved Temporarily');
				header('Location: ' . $url);
				exit;
			case 404:
				header('HTTP/1.1 404 Not Found');
				exit;
		}
	}

	public function params($fields)
	{
		$result = array();
		foreach($fields as $k => $v)
		{
			$result[$k] = empty($_REQUEST[$k]) ? '' : $_REQUEST[$k];
		}
		return $result;
	}
}
