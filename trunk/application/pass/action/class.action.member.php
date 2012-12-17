<?php
class ActionMember extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodLogin()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'submit' => array(array('format', 'int')),
			'account' => array(array('format', 'trim')),
			'password' => array(array('format', 'trim')),
			'captcha' => array(array('format', 'trim')),
			'remember' => array(array('format', 'int')),
			'callback' => array(array('valid', 'url', '', APP_URL, 0))
		));

		// 是否已经登录
		$memberObj = Factory::getModel('member');
		if($memberObj->isLogin())
		{
			$this->redirect($params['callback']);
		}

		// Cookie自动登录
		if($memberObj->autoLogin())
		{
			$this->redirect($params['callback']);
		}

		// 防止攻击检测
		$filecache = new Filecache();
		$cache_key = 'try_login_ip/' . md5(REMOTE_IP_ADDRESS);
		$count = intval($filecache->get($cache_key));
		$captcha = $count >= $GLOBALS['OPTIONS']['LOGIN_FAILED_CAPTCHA']['COUNT'];

		// 表单提交
		if($params['submit'])
		{
			if($captcha && !$this->captchaCheck($params['captcha'], 'once'))
			{
				$error = array('message' => $GLOBALS['MESSAGE'][COMMON_CAPTCHA_ERROR], 'code' => COMMON_CAPTCHA_ERROR);
			}
			else if($memberObj->login(0, $params['account'], $params['password']))
			{
				$filecache->set($cache_key, 0);
				// 自动登录
				$params['remember'] && $memberObj->setAutoLogin();
				// 跳转
				$this->redirect($params['callback']);
			}
			else
			{
				$error = $memberObj->getError();
				$error = array('message' => $GLOBALS['MESSAGE'][$error], 'code' => $error);
			}

			$filecache->set($cache_key, ++$count, $GLOBALS['OPTIONS']['LOGIN_FAILED_CAPTCHA']['TIME']);
			$captcha = $count >= $GLOBALS['OPTIONS']['LOGIN_FAILED_CAPTCHA']['COUNT'];
		}
		$this->assign('error', empty($error) ? null : $error);
		$this->assign('captcha', $captcha);

		// 可用的网站接入
		$connectObj = Factory::getModel('connect');
		$connects = $connectObj->getAllPairs('key', 'name', MEMBER_STATUS_DEFAULT, true);

		$this->assign('connects', $connects);
		$this->assign('callback', urlencode($params['callback']));
		$this->assign('params', $params);
	}

	public function methodLogout()
	{
		$memberObj = Factory::getModel('member');
		$memberObj->logout();
		$this->redirect(APP_URL);
	}

	public function methodRegister()
	{
	}

	public function methodRecover()
	{
	}

	public function methodActive()
	{
	}

	public function methodHome()
	{
		var_dump($_SESSION);
		echo '<a href="/index.php?a=member&m=logout">logout</a>';
	}

	public function methodSetProfile()
	{
	}
}
