<?php
class ActionTaskMulti extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->obtain(array(
			'p' => array(array('format', 'int'), array('valid', 'gt', null, 1, 0)),
			'title' => array(array('format', 'trim')),
			'account' => array(array('format', 'trim')),
			'channel' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'send_state' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'start_time' => array(array('format', 'timestamp')),
			'end_time' => array(array('format', 'timestamp'))
		));

		$channelObj = Factory::getModel('channel');
		$taskmultiObj = Factory::getModel('taskmulti');
		$userObj = Factory::getModel('user');
		$p = $params['p'];
		unset($params['p']);

		$channel_list = $channelObj->getAllPairs();
		$list = $taskmultiObj->getList($p, $params);
		$list['data'] = $taskmultiObj->formatList($list['data']);

		$list['pager']['params'] = 'title=' . urlencode($params['title']) . '&account=' . urlencode($params['account']) . '&channel=' . $params['channel'] . '&send_state=' . $params['send_state'];
		if($params['start_time'] !== false)
		{
			$params['start_time'] = date('Y-m-d', $params['start_time']);
			$list['pager']['params'] .= '&start_time=' . $params['start_time'];
		}
		if($params['end_time'] !== false)
		{
			$params['end_time'] = date('Y-m-d', $params['end_time']);
			$list['pager']['params'] .= '&end_time=' . $params['end_time'];
		}

		$this->assign('userpower', $userObj->getUserPower());
		$this->assign('user', $userObj->getUser());
		$this->assign('channel_list', $channel_list);
		$this->assign('send_state_list', $GLOBALS['CONFIG']['TASKMULTI']['STATE']);
		$this->assign('list', $list);
		$this->assign('params', $params);
	}

	public function methodEdit()
	{
		echo 'ActionSuri';
	}

	public function methodRead()
	{
		echo 'ActionSuri';
	}

	public function methodCheck()
	{
		echo 'ActionSuri';
	}

	public function methodShortUri()
	{

	}

	public function methodSendList()
	{

	}

	public function methodSendView()
	{

	}
}
