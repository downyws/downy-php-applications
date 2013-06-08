<?php
class ActionMember extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Action', 'define.action.', __CLASS__) . '.php');
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
				$error['captcha'] = array('message' => MCGetM('AMER_CAPTCHA_ERROR'), 'code' => MCGetC('AMER_CAPTCHA_ERROR'));
				empty($params['account']) && $error['account'] = array('message' => MCGetM('AMER_PLZ_IN_ACCOUNT'), 'code' => MCGetC('AMER_PLZ_IN_ACCOUNT'));
				empty($params['password']) && $error['password'] = array('message' => MCGetM('AMER_PLZ_IN_PASSWORD'), 'code' => MCGetC('AMER_PLZ_IN_PASSWORD'));
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
						substr(MCGetC('MMER_PLZ_IN_ACCOUNT'), 0, 5) => 'account',
						substr(MCGetC('MMER_PLZ_IN_PASSWORD'), 0, 5) => 'password'
					);
					foreach($errors as $v)
					{
						$error[$type[substr($v, 0, 5)]] = array('message' => MCGetM($v), 'code' => $v);
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
		$connects = $connectObj->getConnects('key', 'name', STATUS_DEFAULT, true);

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
		$params['email'] = '';
		$params['mobile'] = '';
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
				$error['captcha'] = array('message' => MCGetM('AMER_CAPTCHA_ERROR'), 'code' => MCGetC('AMER_CAPTCHA_ERROR'));
				$type = 'check';
			}
			// 同意服务条款
			if(!$params['agree'])
			{
				$error['agree'] = array('message' => MCGetM('AMER_AGREE_TERMSOFSERVICE'), 'code' => MCGetC('AMER_AGREE_TERMSOFSERVICE'));
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
				$this->message(MCGetC('AMER_REGISTER_SUCCESS'), $data);
			}
			else
			{
				$errors = $memberObj->getError();
				if(!empty($errors) && is_array($errors))
				{
					$type = array
					(
						substr(MCGetC('MMER_FNAME_CNT_EMPTY'), 0, 5) => 'fname',
						substr(MCGetC('MMER_LNAME_CNT_EMPTY'), 0, 5) => 'lname',
						substr(MCGetC('MMER_ACCOUNT_CNT_EMPTY'), 0, 5) => 'account',
						substr(MCGetC('MMER_PWD_CNT_EMPTY'), 0, 5) => 'password',
						substr(MCGetC('MMER_PWDC_CNT_EMPTY'), 0, 5) => 'passwordcfm',
						substr(MCGetC('MMER_EMAIL_CNT_EMPTY'), 0, 5) => 'email',
						substr(MCGetC('MMER_MOBILE_CNT_EMPTY'), 0, 5) => 'mobile',
						substr(MCGetC('MCON_SYSERR_TELA'), 0, 5) => 'system'
					);
					foreach($errors as $v)
					{
						$error[$type[substr($v, 0, 5)]] = array('message' => MCGetM($v), 'code' => $v);
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
						$product[$k]['status_msg'] = MCGetM('AMER_APPS_STATUS_DISABEL');
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
			'field' => array(array('valid', 'in', MCGetM('AMER_ITEM_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['DEFAULT'])))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => end($this->_submit->errors)));
		}

		// 获取参数
		switch($params['field'])
		{
			case 'portrait':
				$fields = array(
					'step' => array(array('valid', 'in', MCGetM('AMER_PORTRAIT_STEP_ERROR'), null, array('upload', 'scope'))),
					'width' => array(array('format', 'int')),
					'height' => array(array('format', 'int')),
					'top' => array(array('format', 'int')),
					'left' => array(array('format', 'int')),
					'length' => array(array('format', 'int')),
					'url' => array(array('format', 'trim'))
				);
				break;
			case 'name':
				$fields = array(
					'first_name' => array(array('format', 'trim')),
					'last_name' => array(array('format', 'trim'))
				);
				break;
			case 'sex':
				$fields = array(
					'sex' => array(array('valid', 'in', MCGetM('AMER_SEX_ITEM_NOEXIST'), null, array_keys($GLOBALS['SEX']))),
					'sex_privacy' => array(array('valid', 'in', MCGetM('AMER_SEX_PRI_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
				);
				break;
			case 'birthday':
				$fields = array(
					'birthday_year' => array(array('format', 'trim')),
					'birthday_month' => array(array('format', 'trim')),
					'birthday_day' => array(array('format', 'trim')),
					'birthday_privacy' => array(array('valid', 'in', MCGetM('AMER_BIRTH_PRI_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
				);
				break;
			case 'blood':
				$fields = array(
					'blood' => array(array('valid', 'in', MCGetM('AMER_BLOOD_ITEM_NOEXIST'), null, array_keys($GLOBALS['BLOOD']))),
					'blood_privacy' => array(array('valid', 'in', MCGetM('AMER_BLOOD_PRI_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['TYPE'])))
				);
				break;
			case 'sign':
				$fields = array('sign' => array('format', 'trim'));
				break;
			case 'mobile':
				$fields = array('mobile_privacy' => array('valid', 'in', MCGetM('AMER_MOBILE_PRI_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['TYPE'])));
				break;
			case 'email':
				$fields = array('email_privacy' => array('valid', 'in', MCGetM('AMER_EMAIL_PRI_NOEXIST'), null, array_keys($GLOBALS['PRIVACY']['TYPE'])));
				break;
			default:
				$this->jsonout(array('state' => false, 'message' => MCGetM('AMER_LOSTITEM_TELA')));
				break;
		}
		$params = array_merge($params, $this->_submit->obtain($_REQUEST, $fields));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => end($this->_submit->errors)));
		}

		// 参数转义
		switch($params['field'])
		{
			case 'portrait': 
				$member = $params;
				$member['portrait'] = ($params['step'] == 'upload') ? $_FILES['portrait'] : null;
				break;
			case 'name': $member = array('first_name' => $params['first_name'], 'last_name' => $params['last_name']); break;
			case 'sex': $member = array('sex' => $GLOBALS['SEX'][$params['sex']], 'sex_privacy' => $GLOBALS['PRIVACY']['TYPE'][$params['sex_privacy']]); break;
			case 'birthday': $member = array('birthday_year' => $params['birthday_year'], 'birthday_month' => $params['birthday_month'], 'birthday_day' => $params['birthday_day'], 'birthday_privacy' => $GLOBALS['PRIVACY']['TYPE'][$params['birthday_privacy']]); break;
			case 'blood': $member = array('blood' => $GLOBALS['BLOOD'][$params['blood']], 'blood_privacy' => $GLOBALS['PRIVACY']['TYPE'][$params['blood_privacy']]); break;
			case 'sign': $member = array('sign' => $params['sign']); break;
			case 'mobile': $member = array('mobile_privacy' => $GLOBALS['PRIVACY']['TYPE'][$params['mobile_privacy']]); break;
			case 'email': $member = array('email_privacy' => $GLOBALS['PRIVACY']['TYPE'][$params['email_privacy']]); break;
		}

		// 处理
		$memberObj = Factory::getModel('member');
		$member['member_id'] = $memberObj->getSessionMember('id');
		$fun = 'modify' . $params['field'];
		$result = $memberObj->$fun($member);
		if($result)
		{
			$member = $memberObj->getSessionMember();
			switch($params['field'])
			{
				case 'portrait': 
					if($params['step'] == 'upload')
					{
						$params['field'] = 'portrait-upload';
						$member['image'] = $result;
					}
					break;
				case 'name': break;
				case 'sex':
					$member['privacy'] = array('sex' => $GLOBALS['PRIVACY']['TYPE'][$params['sex_privacy']]);
					break;
				case 'birthday':
					$member['info'] = array('birthday' => mktime(0, 0, 0, $params['birthday_month'], $params['birthday_day'], $params['birthday_year']));
					$member['privacy'] = array('birthday' => $GLOBALS['PRIVACY']['TYPE'][$params['birthday_privacy']]);
					break;
				case 'blood':
					$member['info'] = array('blood' => $GLOBALS['BLOOD'][$params['blood']]);
					$member['privacy'] = array('blood' => $GLOBALS['PRIVACY']['TYPE'][$params['blood_privacy']]);
					break;
				case 'sign': $member['info'] = array('sign' => $params['sign']); break;
				case 'mobile': $member['privacy'] = array('mobile' => $GLOBALS['PRIVACY']['TYPE'][$params['mobile_privacy']]); break;
				case 'email': $member['privacy'] = array('email' => $GLOBALS['PRIVACY']['TYPE'][$params['email_privacy']]); break;
			}
			$this->initTemplate();
			$this->assign('field', $params['field']);
			$this->assign('member', $member);
			$html = $this->_tpl->fetch(APP_DIR_TEMPLATE  . 'member_base_item.html');
			$this->jsonout(array('state' => true, 'message' => $html));
		}

		// 错误提示
		$errors = $memberObj->getError();
		$message = array();
		if(!empty($errors) && is_array($errors))
		{
			switch($params['field'])
			{
				case 'birthday':
					foreach($errors as $c)
					{
						$data = array('year' => $params['birthday_year'], 'month' => $params['birthday_month'], 'day' => $params['birthday_day']);
						$message[] = $this->message($c, $data, 'string');
					}
					break;
				default:
					foreach($errors as $c)
					{
							$message[] = MCGetM($c);
					}
					break;
			}
		}
		$message = implode("<br />", $message);
		$this->jsonout(array('state' => false, 'message' => $message));
	}

	public function methodConnect()
	{
		$connectObj = Factory::getModel('connect');
		$memberObj = Factory::getModel('member');

		$connects = $connectObj->getConnects(array(), array(), STATUS_DEFAULT, true);
		$temp = $connectObj->getMemberConnects($memberObj->getSessionMember('id'));
		$member_connects = array();
		foreach($temp as $v)
		{
			$member_connects[$v['connect_id']] = $v;
			$member_connects[$v['connect_id']]['data'] = unserialize($v['data']);
		}
		foreach($connects as $k => $v)
		{
			if(isset($member_connects[$v['id']]))
			{
				$connects[$k]['data'] = $member_connects[$v['id']];
				$connects[$k]['config'] = unserialize($connects[$k]['config']);
			}
		}

		$this->assign('connects', $connects);
	}

	public function methodSafe()
	{

	}

	public function methodMyPassword()
	{

	}

	public function methodMyPasswordAjax()
	{
		$memberObj = Factory::getModel('member');

		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'passwordold' => array(array('format', 'trim')),
			'passwordnew' => array(array('format', 'trim')),
			'passwordcfm' => array(array('format', 'trim'))
		));

		// 错误提示
		if(md5($params['passwordold']) != $memberObj->getSessionMember('password'))
		{
			$this->jsonout(array('state' => false, 'message' => array('passwordold' => MCGetM('AMER_PASSWORD_OLD_ERROR'))));
		}

		// 处理
		$member['member_id'] = $memberObj->getSessionMember('id');
		$member['password'] = $params['passwordnew'];
		$member['passwordcfm'] = $params['passwordcfm'];
		$result = $memberObj->modifyPassword($member);
		if($result)
		{
			$this->jsonout(array('state' => true));
		}

		// 错误
		$errors = $memberObj->getError();
		$message = array();
		if(!empty($errors) && is_array($errors))
		{
			$type = array
			(
				substr(MCGetC('MMER_PWD_CNT_EMPTY'), 0, 5) => 'passwordnew',
				substr(MCGetC('MMER_PWDC_CNT_EMPTY'), 0, 5) => 'passwordcfm'
			);
			foreach($errors as $v)
			{
				$t = empty($type[substr($v, 0, 5)]) ? 'other' : $type[substr($v, 0, 5)];
				$message[$t][] = MCGetM($v);
			}
			foreach($message as $k => $v)
			{
				$message[$k] = implode("\t", $message[$k]);					
			}
		}
		$this->jsonout(array('state' => false, 'message' => $message));
	}

	public function methodMyQandA()
	{
		$memberObj = Factory::getModel('member');
		$member_id = $memberObj->getSessionMember('id');
		$qanda = $memberObj->getMemberQandA($member_id);
		$this->assign('qanda', $qanda);
		$this->assign('count', $GLOBALS['QANDA']['COUNT']);
	}

	public function methodMyQandAAjax()
	{
		// 获取参数
		$fields = array(
			'step' => array(array('valid', 'in', MCGetM('AMER_QANDA_STEP_ERROR'), null, array('1', '2'))),
			'key' => array(array('format', 'trim'))
		);
		for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
		{
			$fields['question' . $i] = array(array('format', 'trim'));
			$fields['answer' . $i] = array(array('format', 'trim'));
		}
		$params = $this->_submit->obtain($_REQUEST, $fields);

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => end($this->_submit->errors)));
		}

		// 处理
		$memberObj = Factory::getModel('member');
		$member = array('qanda' => $params, 'member_id' => $memberObj->getSessionMember('id'));
		$qanda = $memberObj->getMemberQandA($member['member_id']);

		// 处理一
		if($params['step'] == '1')
		{
			$qanda_old = array();
			$key = '';
			for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
			{
				if($qanda[$i]['answer'] != $member['qanda']['answer' . $i])
				{
					$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('AMER_QANDA_ANSWER_ERROR'))));
				}
				$qanda_old[$i] = array('question' => $qanda[$i]['question'], 'answer' => $qanda[$i]['answer']);
				$key .= $qanda[$i]['question'] . $qanda[$i]['answer'];
			}
			$key = md5($key);
			$this->jsonout(array('state' => true, 'message' => array('key' => $key, 'qanda' => $qanda_old)));
		}
		// 处理二
		else if($params['step'] == '2')
		{
			// 校验是否知道答案
			$key = '';
			for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
			{
				$key .= $qanda[$i]['question'] . $qanda[$i]['answer'];
			}
			if($params['key'] != md5($key))
			{
				$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('AMER_QANDA_ANSWER_HACK'))));
			}

			$result = $memberObj->modifyQandA($member);
			if($result)
			{
				$this->jsonout(array('state' => true));
			}

			// 错误
			$errors = $memberObj->getError();
			$message = array();
			if(!empty($errors) && is_array($errors))
			{
				$type = array();
				for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
				{
					$type[substr(MCGetC('MMER_QANDA' . $i . '_Q_LEN_ERROR'), 0, 5)] = 'question_' . $i;
					$type[substr(MCGetC('MMER_QANDA' . $i . '_A_LEN_ERROR'), 0, 5)] = 'answer_' . $i;
				}
				foreach($errors as $v)
				{
					$t = empty($type[substr($v, 0, 5)]) ? 'other' : $type[substr($v, 0, 5)];
					$message[$t][] = MCGetM($v);
				}
				foreach($message as $k => $v)
				{
					$message[$k] = implode("\t", $message[$k]);					
				}
			}
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		else
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('AMER_QANDA_STEP_ERROR'))));
		}
	}

	public function methodMyEmail()
	{
		$memberObj = Factory::getModel('member');
		$email = $memberObj->getSessionMember('email');
		$minute_expiry = intval($GLOBALS['CONFIG']['BIND_EMAIL_OPTIONS']['EXPIRY'] / 60);
		$minute_interval = intval($GLOBALS['CONFIG']['BIND_EMAIL_OPTIONS']['INTERVAL'] / 60);
		$this->assign('email', $email);
		$this->assign('minute_interval', $minute_interval);
		$this->assign('minute_expiry', $minute_expiry);
	}

	public function methodMyEmailAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'step' => array(array('valid', 'in', MCGetM('AMER_EMAIL_STEP_ERROR'), null, array('1', '2', '3'))),
			'email' => array(array('format', 'trim')),
			'key' => array(array('format', 'trim'))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => end($this->_submit->errors))));
		}

		$memberObj = Factory::getModel('member');
		$config = $GLOBALS['CONFIG']['BIND_EMAIL_OPTIONS'];
		$filecache = new Filecache();

		// 处理一
		if($params['step'] == '1')
		{
			$try_count = $filecache->get($config['KEY'] . 'try' . REMOTE_IP_ADDRESS);
			if($try_count > $config['TRY_COUNT'])
			{
				$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('AMER_BIND_EMAIL_KEY_ATK'))));
			}

			$member = array('member_id' => $memberObj->getSessionMember('id'), 'email' => $params['email']);
			$result = $memberObj->emailBind($member, $params['key']);
			if($result)
			{
				$filecache->delete($config['KEY'] . 'try' . REMOTE_IP_ADDRESS);
				setcookie('SEND_EMAIL_INTERVAL', '', -1);
				setcookie('BIND_EMAIL', '', -1);
				$this->jsonout(array('state' => true));
			}

			// 错误
			$filecache->set($config['KEY'] . 'try' . REMOTE_IP_ADDRESS, $try_count + 1, $config['TRY_TIME']);
			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$message = array();
			$message['other'] = implode("\t", $errors);
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		// 处理二
		else if($params['step'] == '2')
		{
			$member = array('member_id' => $memberObj->getSessionMember('id'), 'email' => '', 'unbind' => true);
			$result = $memberObj->modifyEmail($member);
			if($result)
			{
				$this->jsonout(array('state' => true));
			}

			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$message = array();
			$message['other'] = implode("\t", $errors);
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		// 处理三
		else if($params['step'] == '3')
		{
			$result = $filecache->get($config['KEY'] . REMOTE_IP_ADDRESS);
			if($result)
			{
				$this->jsonout(array('state' => false, 'message' => MCGetM('AMER_BIND_EMAIL_IP_LIMIT')));
			}

			$result = $memberObj->sendEmailKey($params['email']);
			if($result)
			{
				$filecache->set($config['KEY'] . REMOTE_IP_ADDRESS, $params['email'], $config['INTERVAL']);

				// Cookie记录
				$now = time();
				setcookie('SEND_EMAIL_INTERVAL', $now + $config['INTERVAL'], $now + $config['INTERVAL']);
				setcookie('BIND_EMAIL', $params['email'], $now + $config['EXPIRY']);

				$this->jsonout(array('state' => true));
			}

			// 错误
			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$this->jsonout(array('state' => false, 'message' => implode("\t", $errors)));
		}
	}

	public function methodMyMobile()
	{
		$memberObj = Factory::getModel('member');
		$mobile = $memberObj->getSessionMember('mobile');
		$minute_expiry = intval($GLOBALS['CONFIG']['BIND_MOBILE_OPTIONS']['EXPIRY'] / 60);
		$minute_interval = intval($GLOBALS['CONFIG']['BIND_MOBILE_OPTIONS']['INTERVAL'] / 60);
		$this->assign('mobile', $mobile);
		$this->assign('minute_interval', $minute_interval);
		$this->assign('minute_expiry', $minute_expiry);
	}

	public function methodMyMobileAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'step' => array(array('valid', 'in', MCGetM('AMER_MOBILE_STEP_ERROR'), null, array('1', '2', '3'))),
			'mobile' => array(array('format', 'trim')),
			'key' => array(array('format', 'trim'))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => end($this->_submit->errors))));
		}

		$memberObj = Factory::getModel('member');
		$config = $GLOBALS['CONFIG']['BIND_MOBILE_OPTIONS'];
		$filecache = new Filecache();

		// 处理一
		if($params['step'] == '1')
		{
			$try_count = $filecache->get($config['KEY'] . 'try' . REMOTE_IP_ADDRESS);
			if($try_count > $config['TRY_COUNT'])
			{
				$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('AMER_BIND_MOBILE_KEY_ATK'))));
			}

			$member = array('member_id' => $memberObj->getSessionMember('id'), 'mobile' => $params['mobile']);
			$result = $memberObj->mobileBind($member, $params['key']);
			if($result)
			{
				$filecache->delete($config['KEY'] . 'try' . REMOTE_IP_ADDRESS);
				setcookie('SEND_MOBILE_INTERVAL', '', -1);
				setcookie('BIND_MOBILE', '', -1);
				$this->jsonout(array('state' => true));
			}

			// 错误
			$filecache->set($config['KEY'] . 'try' . REMOTE_IP_ADDRESS, $try_count + 1, $config['TRY_TIME']);
			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$message = array();
			$message['other'] = implode("\t", $errors);
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		// 处理二
		else if($params['step'] == '2')
		{
			$member = array('member_id' => $memberObj->getSessionMember('id'), 'mobile' => '', 'unbind' => true);
			$result = $memberObj->modifyMobile($member);
			if($result)
			{
				$this->jsonout(array('state' => true));
			}

			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$message = array();
			$message['other'] = implode("\t", $errors);
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		// 处理三
		else if($params['step'] == '3')
		{
			$result = $filecache->get($config['KEY'] . REMOTE_IP_ADDRESS);
			if($result)
			{
				$this->jsonout(array('state' => false, 'message' => MCGetM('AMER_BIND_MOBILE_IP_LIMIT')));
			}

			$result = $memberObj->sendMobileKey($params['mobile']);
			if($result)
			{
				$filecache->set($config['KEY'] . REMOTE_IP_ADDRESS, $params['mobile'], $config['INTERVAL']);

				// Cookie记录
				$now = time();
				setcookie('SEND_MOBILE_INTERVAL', $now + $config['INTERVAL'], $now + $config['INTERVAL']);
				setcookie('BIND_MOBILE', $params['mobile'], $now + $config['EXPIRY']);

				$this->jsonout(array('state' => true));
			}

			// 错误
			$errors = $memberObj->getError();
			if(!empty($errors) && is_array($errors))
			{
				foreach($errors as $k => $v)
				{
					$errors[$k] = MCGetM($v);
				}
			}
			$this->jsonout(array('state' => false, 'message' => implode("\t", $errors)));
		}
	}

	public function methodMyActivity()
	{
		$memberObj = Factory::getModel('member');
		$member_id = $memberObj->getSessionMember('id');
		$report = $memberObj->getMemberReport($member_id, 1, $GLOBALS['CONFIG']['ACTION_OPTIONS']['member']['MYACTIVITY']['PAGE_SIZE']);
		$this->assign('report', $report);
		$rule = $GLOBALS['CONFIG']['MODEL_OPTIONS']['member']['REPORT'];
		$this->assign('rule', array('day' => floor($rule['SCOPE'] / 86400), 'limit' => $rule['LIMIT']));
	}

	public function methodMyActivityAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'page' => array(array('vaild', 'gte', '', 1, 1))
		));

		$memberObj = Factory::getModel('member');
		$member_id = $memberObj->getSessionMember('id');
		$report = $memberObj->getMemberReport($member_id, $params['page'], $GLOBALS['CONFIG']['ACTION_OPTIONS']['member']['MYACTIVITY']['PAGE_SIZE']);

		$this->initTemplate();
		$this->assign('report', $report);
		$rule = $GLOBALS['CONFIG']['MODEL_OPTIONS']['member']['REPORT'];
		$this->assign('rule', array('day' => floor($rule['SCOPE'] / 86400), 'limit' => $rule['LIMIT']));
		$html = $this->_tpl->fetch(APP_DIR_TEMPLATE  . 'member_myactivity_report.html');
		$this->jsonout(array('state' => true, 'message' => $html));
	}
}