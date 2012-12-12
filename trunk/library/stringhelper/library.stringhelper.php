<?php
class StringHelper
{
	public $_data_type = array(
		'email' => '/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/',
		'mobile' => '/^1[358]\d{9}$/',
		'telephone' => '/^\+?\d+(?:-\d+)$/',
		'url' => '/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//',
		'zip' => '/^\d{6}$/'
	);

	public function dataTypeTrue($value, $type)
	{
		return empty($this->_data_type[$type]) ? false : !!preg_match($this->_data_type[$type], $value);
	}
}

