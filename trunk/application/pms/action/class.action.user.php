<?php
class ActionUser extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array('profile');
	public $RUN_LONG_TIME = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->obtain(array(
			'p' => array(array('format', 'int'), array('valid', 'gt', null, 1, 0)),
			'account' => array(array('format', 'trim')),
			'is_disable' => array(array('valid', 'empty', null, -1, null), array('format', 'int'), array('valid', 'in', null, -1, array(-1, 0, 1)))
		));

		$userObj = Factory::getModel('user');

		$p = $params['p'];
		unset($params['p']);
		
		$list = $userObj->getList($p, $params);
		$list['data'] = $userObj->formatList($list['data']);
		$list['pager']['params'] = 'account=' . urlencode($params['account']) . '&is_disable=' . $params['is_disable'];

		$this->assign('userpower', $userObj->getUserPower());
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
			$user = $userObj->getUser();
			if($params['id'] == ADMIN_ID && $user['id'] != ADMIN_ID)
			{
				$this->message('您没有权限。');
			}
			$object = $userObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $userObj->formatObject($object);

			$this->assign('object', $object);
		}

		$channelObj = Factory::getModel('channel');

		$this->assign('id', $params['id']);
		$this->assign('power_list', $GLOBALS['CONFIG']['POWER']);
		$this->assign('channel_list', $channelObj->getAllPairs());
		$this->assign('is_disable_list', $GLOBALS['CONFIG']['IS_DISABLE']);
	}

	public function methodEditAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', '编号错误', null, 0)),
			'account' => array(array('format', 'trim'), array('valid', 'empty', '账号不能为空', null, null)),
			'password' => array(array('format', 'trim')),
			'channel' => array(),
			'power' => array(),
			'is_disable' => array(array('format', 'int'), array('valid', 'in', '状态错误', null, array_keys($GLOBALS['CONFIG']['IS_DISABLE']))),
			'tasksingle_limit_day' => array(array('format', 'int'), array('valid', 'egt', '单任务上线必须大于等于0', null, 0))
		));

		// 保存
		if(count($this->_submit->errors) > 0)
		{
			$message = implode('，', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			$userObj = Factory::getModel('user');
			if($params['id'] == 0)
			{
				$result = $userObj->add($params);
				if($result['state'])
				{
					$result['script'] = '$.fn.dialogScript("提示信息", "保存成功。", "window.location.href=\"/index.php?a=user&m=edit&id=' . $result['message'] . '\"");';
					$result['message'] = '保存成功。';
				}
			}
			else
			{
				$user = $userObj->getUser();
				if($params['id'] == ADMIN_ID && $user['id'] != ADMIN_ID)
				{
					$result = array('state' => false, 'message' => '您没有权限。');
				}
				else
				{
					$result = $userObj->edit($params['id'], $params);
				}
			}
		}

		// 返回
		echo json_encode($result);
	}

	public function methodDetail()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '用户不存在', null, 0))
		));

		if(count($this->_submit->errors) > 0)
		{
			$this->message(implode('，', $this->_submit->errors) . '。');
		}
		else
		{
			$userObj = Factory::getModel('user');
			$object = $userObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $userObj->formatObject($object);

			$this->assign('userpower', $userObj->getUserPower());
			$this->assign('object', $object);
		}
	}

	public function methodProfile()
	{
		$userObj = Factory::getModel('user');
		$object = $userObj->getUser();
		$object = $userObj->formatObject($object);
		$this->assign('object', $object);
	}

	public function methodProfileAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'key' => array(array('format', 'trim')),
			'val' => array(array('format', 'trim'))
		));

		$result = array('state' => true, 'message' => '未知错误');

		// 保存
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();
		switch($params['key'])
		{
			case 'password':
				$result = $userObj->editPassword($user['id'], $params['val']);
				break;
		}

		// 返回
		echo json_encode($result);
	}
}
