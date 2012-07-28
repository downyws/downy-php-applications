<?php
class ActionSend extends ActionCommon
{
	public $NOT_LOGIN = array('crontab');
	public $NOT_POWER = array();
	public $RUN_LONG_TIME = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodCrontab()
	{

	}
}
