<?php
class ActionIndex extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodIndex()
	{

	}

	public function methodHome()
	{
		$noticeObj = Factory::getModel('notice');
		$notices = $noticeObj->show();
		$related_desc = $noticeObj->getById(0);

		$this->assign('notices', $notices);
		$this->assign('related_desc', $related_desc);
	}

	public function methodNavg()
	{
	}

	public function methodLogin()
	{
		$commonObj = Factory::getModel('common');

		// 已登录
		if($commonObj->getSessionUser())
		{
			$this->redirect('/index.php?a=index&m=index');
		}
	}

	public function methodLoginAjax()
	{
		$commonObj = Factory::getModel('common');

		// 已登录
		if($commonObj->getSessionUser())
		{
			$this->jsonout(array('state' => false, 'message' => '已经登录。', 'url' => '/index.php?a=index&m=index'));
		}

		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'email' => array(array('format', 'trim')),
			'password' => array(array('format', 'trim'))
		));

		// 登录
		$userObj = Factory::getModel('user');
		$result = $userObj->login($params['email'], $params['password']);
		if($result['state'])
		{
			$result['url'] = '/index.php?a=index&m=index';
		}
		$this->jsonout($result);
	}

	public function methodLogout()
	{
		$commonObj = Factory::getModel('common');
		$commonObj->delSessionUser();
		$this->redirect('/');
	}
}
