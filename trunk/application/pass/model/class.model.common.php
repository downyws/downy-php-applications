<?php
class ModelCommon extends Model
{
	// 错误队列
	public $_error = array();

	public function __construct()
	{
		parent::__construct($GLOBALS['CONFIG']['DB']);
		if(!empty($this->_table) && file_exists(APP_DIR_MESSAGE . 'package.message.' . $this->_table . '.php'))
		{
			include_once(APP_DIR_MESSAGE . 'package.message.' . $this->_table . '.php');
		}
	}

	public function getError($type = '')
	{
		switch($type)
		{
			case 'all':
				return $this->_error;
			case 'first':
				return reset($this->_error);
			case 'last':
			default:
				return end($this->_error);
		}
	}
}
