<?php
include_once(APP_DIR_CONNECT . 'connect.base.php');

class ConnectOauth2 extends ConnectBase
{
	public function __construct($config)
	{
		parent::__construct($config);
	}
}
