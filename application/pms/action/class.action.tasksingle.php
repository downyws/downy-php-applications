<?php
class ActionTaskSingle extends ActionCommon
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
			'contact' => array(array('format', 'trim')),
			'account' => array(array('format', 'trim')),
			'channel' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'send_state' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'start_time' => array(array('format', 'timestamp')),
			'end_time' => array(array('format', 'timestamp'))
		));

		$channelObj = Factory::getModel('channel');
		$tasksinglelObj = Factory::getModel('tasksingle');
		$userObj = Factory::getModel('user');
		$p = $params['p'];
		unset($params['p']);

		$channel_list = $channelObj->getAllPairs();
		$list = $tasksinglelObj->getList($p, $params);
		$list['data'] = $tasksinglelObj->formatList($list['data']);

		$list['pager']['params'] = 'contact=' . urlencode($params['contact']) . '&account=' . urlencode($params['account']) . '&channel=' . $params['channel'] . '&send_state=' . $params['send_state'];
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
		$this->assign('send_state_list', $GLOBALS['CONFIG']['TASKSINGLE']['STATE']);
		$this->assign('list', $list);
		$this->assign('params', $params);
	}

	public function methodEdit()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0))
		));

		if($params['id'])
		{
			$userObj = Factory::getModel('user');
			$tasksingleObj = Factory::getModel('tasksingle');

			$object = $tasksingleObj->getObject(array(array('id' => array('eq', $params['id']))));
			$user = $userObj->getUser();

			// 非法过滤
			if($object['send_state'] != 1)
			{
				$this->message('该单任务已处于无法编辑状态。');
			}
			else if(!in_array('TASKSINGLE:EDITALL', $userObj->getUserPower()) && $object['user_id'] != $user['id'])
			{
				$this->message('您不能编辑他人的单任务。');
			}
			
			$object = $tasksingleObj->formatObject($object);
			$this->assign('object', $object);
		}

		$channelObj = Factory::getModel('channel');

		$channels = $channelObj->getAll();
		$this->assign('user_channel_list', $userObj->getUserChannel());
		$this->assign('channel_list', $channels['data']);
		$this->assign('id', $params['id']);
	}

	public function methodEditAjax()
	{
		// 非法过滤
		// 通道过滤
		var_dump($_POST);
	}

	public function methodCancelAjax()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '任务不存在', null, 0))
		));

		if(count($this->_submit->errors) > 0)
		{
			$result = array('state' => false, 'message' => implode('，', $this->_submit->errors) . '。');
		}
		else
		{
			$tasksinglelObj = Factory::getModel('tasksingle');
			$result = $tasksinglelObj->cancel($params['id']);
			if($result['state'])
			{
				$result['script'] = 'alert("取消成功。");window.location.href="/index.php?a=tasksingle&m=detail&id=' . $params['id'] . '";';
			}
		}

		echo json_encode($result);
	}

	public function methodDetail()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '任务不存在', null, 0))
		));

		if(count($this->_submit->errors) > 0)
		{
			$this->message(implode('，', $this->_submit->errors) . '。');
		}
		else
		{
			$userObj = Factory::getModel('user');
			$tasksingleObj = Factory::getModel('tasksingle');
			$object = $tasksingleObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $tasksingleObj->formatObject($object);

			$this->assign('user', $userObj->getUser());
			$this->assign('userpower', $userObj->getUserPower());
			$this->assign('object', $object);
		}
	}

	public function methodSendApi()
	{
		echo 'ActionSuri';
	}
}
