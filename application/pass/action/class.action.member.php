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

		// 是否展示验证码
		$filecache = new Filecache();
		$captcha = $GLOBALS['OPTIONS']['LOGIN']['CAPTCHA'];
		$captcha['KEY'] .= md5(REMOTE_IP_ADDRESS);
		$captcha['NOW_COUNT'] = intval($filecache->get($captcha['KEY']));
		$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);

		// 表单提交
		if($params['submit'])
		{
			if($captcha['SHOW'] && !$this->captchaCheck($params['captcha'], 'once'))
			{
				$error = array('message' => $GLOBALS['MESSAGE'][COMMON_CAPTCHA_ERROR], 'code' => COMMON_CAPTCHA_ERROR);
			}
			else if($memberObj->login(0, $params['account'], $params['password']))
			{
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

			($captcha['COUNT'] > 0) && $filecache->set($captcha['KEY'], ++$captcha['NOW_COUNT'], $captcha['TIME']);
			$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		}
		$this->assign('error', empty($error) ? null : $error);
		$this->assign('show_captcha', $captcha['SHOW']);

		// 可用的网站接入
		$connectObj = Factory::getModel('connect');
		$connects = $connectObj->getAllPairs('key', 'name', MEMBER_STATUS_DEFAULT, true);

		$this->assign('connects', $connects);
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
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'submit' => array(array('format', 'int')),
			'first_name' => array(array('format', 'trim')),
			'last_name' => array(array('format', 'trim')),
			'account' => array(array('format', 'trim')),
			'password' => array(array('format', 'trim')),
			'passwordcfm' => array(array('format', 'trim')),
			'sex' => array(array('valid', 'in', '', '', array('FEMALE', 'MALE', 'OTHER'))),
			'email' => array(array('format', 'trim')),
			'mobile' => array(array('format', 'trim')),
			'captcha' => array(array('format', 'trim')),
			'agree' => array(array('format', 'int')),
			'callback' => array(array('valid', 'url', '', APP_URL, 0))
		));

		// 是否展示验证码
		$filecache = new Filecache();
		$captcha = $GLOBALS['OPTIONS']['REGISTER']['CAPTCHA'];
		$captcha['KEY'] .= md5(REMOTE_IP_ADDRESS);
		$captcha['NOW_COUNT'] = intval($filecache->get($captcha['KEY']));
		$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		// 表单提交
		$error = array('captcha' => null, 'agree' => null, 'fname' => null, 'lname' => null, 'account' => null, 'password' => null, 'passwordcfm' => null, 'email' => null, 'mobile' => null);
		if($params['submit'])
		{
			$memberObj = Factory::getModel('member');
			$type = '';

			// 验证码检查
			if($captcha['SHOW'] && !$this->captchaCheck($params['captcha'], 'once'))
			{
				$error['captcha'] = array('message' => $GLOBALS['MESSAGE'][COMMON_CAPTCHA_ERROR], 'code' => COMMON_CAPTCHA_ERROR);
				$type = 'check';
			}
			// 同意服务条款
			if(!$params['agree'])
			{
				$error['agree'] = array('message' => $GLOBALS['MESSAGE'][COMMON_AGREE_TERMSOFSERVICE], 'code' => COMMON_AGREE_TERMSOFSERVICE);
				$type = 'check';
			}
			// 注册
			$params['sex'] = empty($params['sex']) || in_array($params['sex'], array('FEMALE', 'MALE', 'OTHER')) ? $GLOBALS['SEX']['OTHER'] : $GLOBALS['SEX'][$params['sex']];
			$member_id = $memberObj->register($params, $type);
			if($member_id)
			{
				// 跳转
				$this->redirect($params['callback']);
			}
			else
			{
				$errors = $memberObj->getError();
				if($errors && count($errors) > 0)
				{
					$type = array
					(
						substr(MEMBER_REGISTER_FNAMEEMPTY, 0, 6) => 'fname',
						substr(MEMBER_REGISTER_LNAMEEMPTY, 0, 6) => 'lname',
						substr(MEMBER_REGISTER_ACCOUNTEMPTY, 0, 6) => 'account',
						substr(MEMBER_REGISTER_PASSWORDEMPTY, 0, 6) => 'password',
						substr(MEMBER_REGISTER_PASSWORDCFMEMPTY, 0, 6) => 'passwordcfm',
						substr(MEMBER_REGISTER_EMAILEMPTY, 0, 6) => 'email',
						substr(MEMBER_REGISTER_MOBILEEMPTY, 0, 6) => 'mobile'
					);
					foreach($errors as $v)
					{
						$error[$type[substr($v, 0, 6)]] = array('message' => $GLOBALS['MESSAGE'][$v], 'code' => $v);
					}
				}
			}

			// 验证码累加
			($captcha['COUNT'] > 0) && $filecache->set($captcha['KEY'], ++$captcha['NOW_COUNT'], $captcha['TIME']);
			$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		}
		$this->assign('error', $error);
		$this->assign('show_captcha', $captcha['SHOW']);

		$this->assign('params', $params);
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
