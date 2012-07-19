<?php
class ActionUser extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array('profile');

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->filter(array(
			'p' => array('complete' => array(array('gt', 0), array('int'))),
			'account' => array('complete' => array(array('trim'))),
			'is_disable' => array('complete' => array(array('in', array("-1", "0", "1"))))
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

	}

	public function methodDelete()
	{

	}

	public function methodProfile()
	{
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();
		$user['power'] = $userObj->getUserPower();
		foreach($user['power'] as $k => $v)
		{
			$user['power'][$k] = $GLOBALS['CONFIG']['POWER'][$v]['NAME'];
		}

		$channelObj = Factory::getModel('channel');
		$channels = $channelObj->getAllPairs();

		$user['channel'] = $userObj->getUserChannel();
		foreach($user['channel'] as $k => $v)
		{
			$user['channel'][$k] = $channels[$v];
		}
		$this->assign('user', $user);
	}

	public function methodProfileAjax()
	{
		// 获取参数
		$params = $this->_submit->filter(array(
			'key' => array('complete' => array(array('trim'))),
			'val' => array('complete' => array(array('trim')))
		));

		$user = $_SESSION['user'];
		$result = array('state' => true, 'message' => '未知错误');

		// 保存
		$userObj = Factory::getModel('user');
		switch($params['key'])
		{
			case 'password':
				$result = $userObj->editPassword($user['id'], $params['val']);
				if($result['state'])
				{
					$userObj->record(array('user_id' => $user['id'], 'data_id' => $user['id'], 'data_table' => LOG_DATA_TABLE_USER, 'operation_type' => LOG_OPERATION_TYPE_UPDATE));
				}
				break;
		}

		// 返回
		echo json_encode($result);
	}
}
