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
