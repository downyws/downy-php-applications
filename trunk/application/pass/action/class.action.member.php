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
		$this->assign('params', $params);

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
		$captcha = $GLOBALS['CONFIG']['LOGIN_CAPTCHA_OPTIONS'];
		$captcha['KEY'] .= md5(REMOTE_IP_ADDRESS);
		$captcha['NOW_COUNT'] = intval($filecache->get($captcha['KEY']));
		$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);

		// 表单提交
		$error = array('captcha' => null, 'account' => null, 'password' => null);
		if($params['submit'])
		{
			if($captcha['SHOW'] && !$this->captchaCheck($params['captcha'], 'once'))
			{
				$error['captcha'] = array('message' => $GLOBALS['MESSAGE'][COMMON_CAPTCHA_ERROR], 'code' => COMMON_CAPTCHA_ERROR);
				empty($params['account']) && $error['account'] = array('message' => $GLOBALS['MESSAGE'][COMMON_ACCOUNTEMPTY], 'code' => COMMON_ACCOUNTEMPTY);
				empty($params['password']) && $error['password'] = array('message' => $GLOBALS['MESSAGE'][COMMON_PASSWORDEMPTY], 'code' => COMMON_PASSWORDEMPTY);
			}
			else if($memberObj->login($params))
			{
				// 自动登录
				$params['remember'] && $memberObj->setAutoLogin();
				// 跳转
				$this->redirect($params['callback']);
			}
			else
			{
				$errors = $memberObj->getError();
				if(!empty($errors) && is_array($errors))
				{
					$type = array
					(
						substr(MEMBER_LOGIN_ACCOUNTEMPTY, 0, 6) => 'account',
						substr(MEMBER_LOGIN_PASSWORDEMPTY, 0, 6) => 'password'
					);
					foreach($errors as $v)
					{
						$error[$type[substr($v, 0, 6)]] = array('message' => $GLOBALS['MESSAGE'][$v], 'code' => $v);
					}
				}
			}

			($captcha['COUNT'] > 0) && $filecache->set($captcha['KEY'], ++$captcha['NOW_COUNT'], $captcha['TIME']);
			$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		}
		$this->assign('error', $error);
		$this->assign('show_captcha', $captcha['SHOW']);

		// 可用的网站接入
		$connectObj = Factory::getModel('connect');
		$connects = $connectObj->getAllPairs('key', 'name', STATUS_DEFAULT, true);

		// 产品介绍
		$product = $connectObj->productDomain($params['callback']);
		$product = empty($product) ? 'default' : str_replace('.', '_', $product);
		if(!file_exists(APP_DIR_TEMPLATE . 'member_login/' . $product . '.html'))
		{
			$product = 'default';
		}

		$this->assign('product', $product);
		$this->assign('connects', $connects);
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
			'sex' => array(array('valid', 'in', '', '', array_keys($GLOBALS['SEX']))),
			'email' => array(array('format', 'trim')),
			'mobile' => array(array('format', 'trim')),
			'captcha' => array(array('format', 'trim')),
			'agree' => array(array('format', 'int')),
			'callback' => array(array('valid', 'url', '', APP_URL, 0))
		));
		$this->assign('params', $params);

		// 是否展示验证码
		$filecache = new Filecache();
		$captcha = $GLOBALS['CONFIG']['REGISTER_CAPTCHA_OPTIONS'];
		$captcha['KEY'] .= md5(REMOTE_IP_ADDRESS);
		$captcha['NOW_COUNT'] = intval($filecache->get($captcha['KEY']));
		$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		// 表单提交
		$error = array('captcha' => null, 'agree' => null, 'fname' => null, 'lname' => null, 'account' => null, 'password' => null, 'passwordcfm' => null, 'email' => null, 'mobile' => null, 'system' => null);
		if($params['submit'])
		{
			$memberObj = Factory::getModel('member');

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
			// 性别转义
			if(!empty($params['sex']) && in_array($params['sex'], array('FEMALE', 'MALE', 'OTHER')))
			{
				$params['sex'] = $GLOBALS['SEX'][$params['sex']];
			}
			else
			{
				$params['sex'] = $GLOBALS['SEX']['OTHER'];
			}

			// 注册
			$member_id = $memberObj->register($params, empty($type) ? '' : $type);
			if($member_id)
			{
				$data = $params['callback'];
				$this->message(MEMBER_REGISTER_SUCCESS, $data);
			}
			else
			{
				$errors = $memberObj->getError();
				if(!empty($errors) && is_array($errors))
				{
					$type = array
					(
						substr(MEMBER_REGISTER_FNAMEEMPTY, 0, 6) => 'fname',
						substr(MEMBER_REGISTER_LNAMEEMPTY, 0, 6) => 'lname',
						substr(MEMBER_REGISTER_ACCOUNTEMPTY, 0, 6) => 'account',
						substr(MEMBER_REGISTER_PASSWORDEMPTY, 0, 6) => 'password',
						substr(MEMBER_REGISTER_PASSWORDCFMEMPTY, 0, 6) => 'passwordcfm',
						substr(MEMBER_REGISTER_EMAILEMPTY, 0, 6) => 'email',
						substr(MEMBER_REGISTER_MOBILEEMPTY, 0, 6) => 'mobile',
						substr(COMMON_SYSTEMERROR, 0, 6) => 'system'
					);
					foreach($errors as $v)
					{
						$error[$type[substr($v, 0, 6)]] = array('message' => $GLOBALS['MESSAGE'][$v], 'code' => $v);
					}
				}
			}

			($captcha['COUNT'] > 0) && $filecache->set($captcha['KEY'], ++$captcha['NOW_COUNT'], $captcha['TIME']);
			$captcha['SHOW'] = ($captcha['COUNT'] < 1) ? !!($captcha['COUNT'] + 1) : ($captcha['NOW_COUNT'] >= $captcha['COUNT']);
		}
		$this->assign('error', $error);
		$this->assign('show_captcha', $captcha['SHOW']);
	}

	public function methodHome()
	{
		$memberObj = Factory::getModel('member');
		$member = $memberObj->getSessionMember();
		$product = $memberObj->getMemberApps($member['id']);
		foreach($product as $k => $v)
		{
			if($product[$k]['status'] != STATUS_DEFAULT)
			{
				switch($product[$k]['status'])
				{
					case STATUS_DISABEL:
						$product[$k]['status_msg'] = $GLOBALS['MESSAGE'][MEMBER_APPS_STATUS_DISABEL];
						break;
				}
			}
		}
		$this->assign('product', $product);
		$this->assign('member', $member);
	}

	public function methodBase()
	{
		$memberObj = Factory::getModel('member');
		$member = $memberObj->getSessionMember();
		$member['info'] = $memberObj->getMemberInfo($member['id']);
		$member['privacy'] = $memberObj->getMemberPrivacy($member['id']);
		$this->assign('member', $member);

		$this->assign('bloods', $GLOBALS['BLOOD']);
		$this->assign('privacys', $GLOBALS['PRIVACY']['TYPE']);
		$this->assign('sexs', $GLOBALS['SEX']);
	}

	public function methodBaseModify()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'field' => array(array('valid', 'in', '不存在您所要保存的字段。', null, array_keys($GLOBALS['PRIVACY']['DEFAULT'])))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => end($this->_submit->errors)));
		}

		// 获取参数
		$fields = array(
			'portrait' => array(),
			'name' => array(
				'first_name' => array(array('format', 'trim')),
				'last_name' => array(array('format', 'trim'))
			),
			'sex' => array(
				'sex' => array(array('valid', 'in', '您所选的性别选项不存在。', null, array_keys($GLOBALS['SEX']))),
				'sex_privacy' => array(array('valid', 'in', '您所选的隐私选项不存在。', null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
			),
			'birthday' => array(
				'birthday_year' => array(array('format', 'trim')),
				'birthday_month' => array(array('format', 'trim')),
				'birthday_day' => array(array('format', 'trim')),
				'birthday_privacy' => array(array('valid', 'in', '您所选的隐私选项不存在。', null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
			),
			'blood' => array(
				'blood' => array(array('valid', 'in', '您所选的血型选项不存在。', null, array_keys($GLOBALS['BLOOD']))),
				'blood_privacy' => array(array('valid', 'in', '您所选的隐私选项不存在。', null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
			),
			'sign' => array(
				'sign' => array(array('format', 'trim'))
			),
			'mobile' => array(
				'mobile_privacy' => array(array('valid', 'in', '您所选的隐私选项不存在。', null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
			),
			'email' => array(
				'email_privacy' => array(array('valid', 'in', '您所选的隐私选项不存在。', null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
			),
			'_error_' => array(
				'_error_' => array(array('valid', 'empty', '缺少编辑项，请联系管理员。', null, null))
			)
		);
		$params = array_merge($params, $this->_submit->obtain($_REQUEST, $fields[$params['field']]));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => end($this->_submit->errors)));
		}

		// 处理
		$memberObj = Factory::getModel('member');
		$fun = 'modify' . $params['field'];
		if($memberObj->$fun($params))
		{
			$member = $memberObj->getSessionMember();
			switch($params['field'])
			{
				case 'portrait': break;
				case 'name': $member = array('first_name' => $params['first_name'], 'last_name' => $params['last_name']); break;
				case 'sex': $member = array('sex' => $GLOBALS['SEX'][$params['sex']], 'privacy' => array('sex' => $GLOBALS['PRIVACY']['TYPE'][$params['sex_privacy']])); break;
				case 'birthday': $member = array('info' => array('birthday' => strtotime($params['birthday_year'] . '-' . $params['birthday_month'] . '-' . $params['birthday_day'])), 'privacy' => array('birthday' => $GLOBALS['PRIVACY']['TYPE'][$params['birthday_privacy']])); break;
				case 'blood': $member = array('info' => array('blood' => $GLOBALS['BLOOD'][$params['blood']]), 'privacy' => array('blood' => $GLOBALS['PRIVACY']['TYPE'][$params['blood_privacy']])); break;
				case 'sign': $member = array('info' => array('sign' => $params['sign'])); break;
				case 'mobile': $member = array('mobile' => $member['mobile'], 'privacy' => array('mobile' => $GLOBALS['PRIVACY']['TYPE'][$params['mobile_privacy']])); break;
				case 'email': $member = array('email' => $member['email'], 'privacy' => array('email' => $GLOBALS['PRIVACY']['TYPE'][$params['email_privacy']])); break;
			}
			$this->initTemplate();
			$this->assign('field', $params['field']);
			$this->assign('member', $member);
			$html = $this->_tpl->fetch(APP_DIR_TEMPLATE  . 'member_base_item.html');
			$this->jsonout(array('state' => true, 'message' => $html));
		}
		$code = $memberObj->getError();
		$this->jsonout(array('state' => false, 'message' => $GLOBALS['MESSAGE'][$code]));
	}

	public function methodConnect()
	{
	}

	public function methodSafe()
	{
	}

	public function methodPassword()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'submit' => array(array('format', 'int'))
		));
		$this->assign('params', $params);
	}

	public function methodQandA()
	{
	}

	public function methodBindMobile()
	{
	}

	public function methodBindEmail()
	{
	}
}
