<?php
class ActionSend extends ActionCommon
{
	public $NOT_LOGIN = array('crontab');
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodCrontab()
	{

	}
}
