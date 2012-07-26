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
			'name' => array(array('format', 'trim')),
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

		$list['pager']['params'] = 'name=' . urlencode($params['name']) . '&account=' . urlencode($params['account']) . '&channel=' . $params['channel'] . '&send_state=' . $params['send_state'];
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
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0))
		));

		$userObj = Factory::getModel('user');
		if($params['id'])
		{
			$taskmultiObj = Factory::getModel('taskmulti');

			$object = $taskmultiObj->getObject(array(array('id' => array('eq', $params['id']))));
			$user = $userObj->getUser();

			// 非法过滤
			if($object['send_state'] != 5)
			{
				$this->message('该任务已处于无法编辑状态。');
			}
			else if(!in_array('TASKMULTI:EDITALL', $userObj->getUserPower()) && $object['user_id'] != $user['id'])
			{
				$this->message('您不能编辑他人的任务。');
			}
			
			$object = $taskmultiObj->formatObject($object);
			$this->assign('object', $object);
		}

		$channelObj = Factory::getModel('channel');

		$channels = $channelObj->getAll();
		$this->assign('user_channel_list', $userObj->getUserChannel());
		$this->assign('channel_list', $channels['data']);
		$this->assign('userpower', $userObj->getUserPower());
		$this->assign('id', $params['id']);
	}

	public function methodEditAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', '编号错误', null, 0)),
			'name' => array(array('format', 'trim'), array('valid', 'empty', '任务名称不能为空', null, null)),
			'type' => array(array('format', 'int'), array('valid', 'in', '类型错误', null, array_keys($GLOBALS['CONFIG']['CHANNEL']['TYPE']))),
			'channel_id' => array(array('format', 'int'), array('valid', 'gt', '通道错误', null, 0)),
			'title' => array(array('format', 'trim')),
			'content1' => array(array('format', 'trim')),
			'content2' => array(array('format', 'trim')),
			'plan_send_time' => array(array('format', 'trim'))
		));

		if(count($this->_submit->errors) > 0)
		{
			$message = implode('，', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			$channelObj = Factory::getModel('channel');
			$channel_type = $channelObj->getOne(array(array('id' => array('eq', $params['channel_id']))), 'type');
			if($params['type'] != $channel_type)
			{
				$result = array('state' => false, 'message' => '通道类型与任务类型不匹配。');
			}
			else
			{
				$taskmultiObj = Factory::getModel('taskmulti');
				$userObj = Factory::getModel('user');
				$user = $userObj->getUser();

				$params['content'] = $params['content' . $params['type']];
				unset($params['type']);
				unset($params['content1']);
				unset($params['content2']);

				if($params['id'] == 0)
				{
					$params['user_id'] = $user['id'];
					$result = $taskmultiObj->add($params);
					if($result['state'])
					{
						$result['script'] = 'alert("保存成功。");window.location="/index.php?a=taskmulti&m=edit&id=' . $result['message'] . '";';
						$result['message'] = '保存成功。';
					}
				}
				else
				{
					$object = $taskmultiObj->getObject(array(array('id' => array('eq', $params['id']))));
					// 非法过滤
					if($object['send_state'] != 5)
					{
						$result = array('state' => false, 'message' => '该任务已处于无法编辑状态。');
					}
					else if(!in_array('TASKMULTI:EDITALL', $userObj->getUserPower()) && $object['user_id'] != $user['id'])
					{
						$result = array('state' => false, 'message' => '您不能编辑他人的任务。');
					}
					else
					{
						$result = $taskmultiObj->edit($params['id'], $params);
					}
				}
			}
		}
		// 返回
		echo json_encode($result);
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
			$taskmultiObj = Factory::getModel('taskmulti');
			$result = $taskmultiObj->cancel($params['id']);
			if($result['state'])
			{
				$result['script'] = 'alert("取消成功。");window.location.href="/index.php?a=taskmulti&m=detail&id=' . $params['id'] . '";';
			}
		}

		echo json_encode($result);
	}

	public function methodCheckAjax()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '任务不存在', null, 0)),
			'pass' => array(array('format', 'int'))
		));

		if(count($this->_submit->errors) > 0)
		{
			$result = array('state' => false, 'message' => implode('，', $this->_submit->errors) . '。');
		}
		else
		{
			$taskmultiObj = Factory::getModel('taskmulti');
			$result = $taskmultiObj->check($params['id'], $params['pass']);
			if($result['state'])
			{
				$result['script'] = 'alert("审核成功。");window.location.href="/index.php?a=taskmulti&m=detail&id=' . $params['id'] . '";';
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
			$taskmultiObj = Factory::getModel('taskmulti');
			$object = $taskmultiObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $taskmultiObj->formatObject($object);

			$this->assign('user', $userObj->getUser());
			$this->assign('userpower', $userObj->getUserPower());
			$this->assign('object', $object);
		}
	}
}
