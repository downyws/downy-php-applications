<?php
class ActionIndex extends ActionCommon
{
	public $NOT_LOGIN = array('login');
	public $NOT_POWER = array('frame', 'head', 'index', 'menu', 'logout');

	public function __construct()
	{
		parent::__construct();
	}

	public function methodFrame()
	{

	}

	public function methodHead()
	{

	}

	public function methodIndex()
	{
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();
		$this->assign('account', $user['account']);
	}

	public function methodMenu()
	{
		$userObj = Factory::getModel('user');
		$power = $userObj->getUserPower();
		$this->assign('power', $power);
	}

	public function methodLogin()
	{
		// 获取参数
		$params = $this->_submit->filter(array(
			'submit' => array('complete' => array(array('int'))),
			'account' => array('complete' => array(array('trim'))),
			'password' => array('complete' => array(array('trim'))),
		));

		// 是否已经登录
		$userObj = Factory::getModel('user');
		if($userObj->isLogin())
		{
			$this->redirect('/index.php?a=index&m=frame');
		}

		// 表单提交
		if($params['submit'])
		{
			$result = $userObj->login($params['account'], $params['password']);
			if($result['state'])
			{
				$this->redirect('/index.php?a=index&m=frame');
			}
		}

		// 输出
		$this->assign('account', $params['account']);
		$this->assign('message', (empty($result['message']) ? '' : $result['message']));
	}

	public function methodLogout()
	{
		$userObj = Factory::getModel('user');
		$userObj->logout();
		$this->message('退出成功。', array(array('title' => '重新登录', 'href' => '/index.php?a=index&m=login')));
	}
}
