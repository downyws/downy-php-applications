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
		$params = $this->_submit->filter(array(
			'p' => array('complete' => array(array('gt', 0), array('int'))),
			'title' => array('complete' => array(array('trim'))),
			'account' => array('complete' => array(array('trim'))),
			'channel' => array('complete' => array(array('gt', -1))),
			'send_state' => array('complete' => array(array('gt', -1))),
			'start_time' => array('complete' => array(array('timestamp', false))),
			'end_time' => array('complete' => array(array('timestamp', false)))
		));

		$channelObj = Factory::getModel('channel');
		$taskmultiObj = Factory::getModel('taskmulti');
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

		$this->assign('channel_list', $channel_list);
		$this->assign('send_state_list', $GLOBALS['CONFIG']['TASKMULTI']['STATE']);
		$this->assign('list', $list);
		$this->assign('params', $params);
	}

	public function methodEdit()
	{
		echo 'ActionSuri';
	}

	public function methodDelete()
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