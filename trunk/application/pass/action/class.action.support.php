<?php
class ActionSupport extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Action', 'define.action.', __CLASS__) . '.php');
	}

	public function methodResetpassword()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'key' => array(array('format', 'trim'))
		));

		$config = $GLOBALS['CONFIG']['LOST_PASSWORD_OPTIONS'];
		$filecache = new Filecache();
		$member_id = $filecache->get($config['RESET_KEY'] . $params['key']);
		if($member_id)
		{
			$this->assign('key', $params['key']);
		}
		else
		{
			$this->message(MCGetC('ASUP_KEY_ERROR'));
		}
	}

	public function methodResetpasswordAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'key' => array(array('format', 'trim')),
			'passwordnew' => array(array('format', 'trim')),
			'passwordcfm' => array(array('format', 'trim'))
		));

		$config = $GLOBALS['CONFIG']['LOST_PASSWORD_OPTIONS'];
		$filecache = new Filecache();
		$member_id = $filecache->get($config['RESET_KEY'] . $params['key']);
		if($member_id)
		{
			$memberObj = Factory::getModel('member');
			$params['member_id'] = $member_id;
			$params['password'] = $params['passwordnew'];

			// 修改密码，修改前保存原始session
			$exists = empty($_SESSION['MEMBER']) ? true : $_SESSION['MEMBER']['id'];
			$_SESSION['MEMBER']['id'] = $member_id;
			$result = $memberObj->modifyPassword($params);
			if($exists !== true)
			{
				$_SESSION['MEMBER']['id'] = $exists;
			}
			else
			{
				unset($_SESSION['MEMBER']);
			}
			if($result)
			{
				$filecache->delete($config['RESET_KEY'] . $params['key']);
				$this->jsonout(array('state' => true, 'url' => '/index.php?a=common&m=message&code=' . MCGetC('ASUP_RESET_PWD_SUCCESS')));
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
			}
			$this->jsonout(array('state' => false, 'message' => $message));
		}
		else
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('ASUP_KEY_ERROR'))));
		}
	}

	public function methodLogin()
	{

	}

	public function methodRecoverAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'step' => array(array('valid', 'in', MCGetM('ASUP_RECOVER_STEP_ERROR'), null, array('111', '112', '113')))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => end($this->_submit->errors))));
		}

		// 提交次数检查
		$filecache = new Filecache();
		$config = $GLOBALS['CONFIG']['LOST_PASSWORD_OPTIONS'];
		$try_count = $filecache->get($config['KEY'] . 'try' . REMOTE_IP_ADDRESS);
		if($try_count > $config['TRY_COUNT'])
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('ASUP_ERROR_MAX_COUNT'))));
		}
		$try_count = $filecache->set($config['KEY'] . 'try' . REMOTE_IP_ADDRESS, $try_count + 1, $config['TRY_TIME']);

		switch($params['step'])
		{
			case '111':
				// 获取参数
				$params = $this->_submit->obtain($_REQUEST, array(
					'account' => array(array('format', 'trim'), array('valid', 'isset', MCGetM('ASUP_RECOVER_ACCOUNT_CNT_EMPTY'), null, null)),
					'member_id' => array(array('format', 'int'))
				));

				// 错误提示
				if(count($this->_submit->errors) > 0)
				{
					$this->jsonout(array('state' => false, 'message' => array('account' => end($this->_submit->errors))));
				}

				// 提交问题
				$memberObj = Factory::getModel('member');
				if($params['member_id'])
				{
					$member_id = $params['member_id'];
					$qanda = $memberObj->getMemberQandA($member_id);
					if(!$qanda)
					{
						$this->jsonout(array('state' => false, 'message' => array('account' => MCGetM('ASUP_RECOVER_ACCOUNT_ERROR'))));
					}
					$fields = array();
					for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
					{
						$fields['answer' . $i] = array(array('format', 'trim'));
					}
					$params = $this->_submit->obtain($_REQUEST, $fields);
					$message = array();
					for($i = 0; $i < $GLOBALS['QANDA']['COUNT']; $i++)
					{
						if($qanda[$i]['answer'] != $params['answer' . $i])
						{
							$message['other'] = MCGetM('ASUP_RECOVER_ANSWER_ERROR');
							break;
						}
					}
					if(empty($message))
					{
						$key = md5(time() . mt_rand());
						$filecache->set($config['RESET_KEY'] . $key, $member_id, $config['RESET_TIME']);
						$url = '/index.php?a=support&m=resetpassword&key=' . $key;
						$this->jsonout(array('state' => true, 'url' => $url));
					}
					else
					{
						$this->jsonout(array('state' => false, 'message' => $message));
					}
				}
				// 获取问题
				else
				{
					$member_id = $memberObj->getMemberId($params['account'], 'account');
					if(!$member_id)
					{
						$this->jsonout(array('state' => false, 'message' => array('account' => MCGetM('ASUP_RECOVER_ACCOUNT_ERROR'))));
					}
					$qanda = $memberObj->getMemberQandA($member_id);
					Factory::loadLibrary('stringhelper');
					$stringhelper = new StringHelper();
					$index = 1;
					foreach($qanda as $k => $v)
					{
						$qanda[$k]['index'] = $stringhelper->intToCnWord($index++);
						unset($qanda[$k]['answer']);
						unset($qanda[$k]['member_id']);
					}
					$this->jsonout(array('state' => true, 'data' => array('member_id' => $member_id, 'qanda' => $qanda)));
				}
				break;
			case '112':
				break;
			case '113':
				break;
		}
	}

	public function methodAskAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'step' => array(array('valid', 'in', MCGetM('ASUP_ADD_STEP_ERROR'), null, array('12', '13')))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => end($this->_submit->errors))));
		}

		// 提交次数检查
		$filecache = new Filecache();
		$config = $GLOBALS['CONFIG']['SUPPORT_ASK_OPTIONS'];
		$cache = array('key' => $config['KEY'] . REMOTE_IP_ADDRESS, 'time' => $config['SUBMIT_INTERVAL']);
		$submit_count = $filecache->get($config['KEY'] . REMOTE_IP_ADDRESS);
		if($submit_count > $config['SUBMIT_COUNT'])
		{
			$this->jsonout(array('state' => false, 'message' => array('other' => MCGetM('ASUP_TRY_MAX_COUNT'))));
		}

		$supportObj = Factory::getModel('support');
		switch($params['step'])
		{
			case '12':
				// 获取参数
				$params = $this->_submit->obtain($_REQUEST, array(
					'first_name' => array(array('format', 'trim')),
					'last_name' => array(array('format', 'trim')),
					'contact' => array(array('format', 'trim')),
					'content' => array(array('format', 'trim'))
				));

				// 处理
				$type_cate = 'name';
				$params['cate_id'] = SUPPORT_LOST_ACCOUNT;
				$result = $supportObj->add($params);
				if($result)
				{
					$filecache->set($config['KEY'] . REMOTE_IP_ADDRESS, $submit_count + 1, $config['SUBMIT_INTERVAL']);
					$this->jsonout(array('state' => true, 'url' => '/index.php?a=common&m=message&code=' . MCGetC('ASUP_ADD_SUCCESS')));
				}
				break;
			case '13':
				// 获取参数
				$params = $this->_submit->obtain($_REQUEST, array(
					'account' => array(array('format', 'trim')),
					'contact' => array(array('format', 'trim')),
					'content' => array(array('format', 'trim'))
				));

				// 处理
				$type_cate = 'account';
				$params['cate_id'] = SUPPORT_LOGIN_QUESTION;
				$result = $supportObj->add($params);
				if($result)
				{
					$filecache->set($config['KEY'] . REMOTE_IP_ADDRESS, $submit_count + 1, $config['SUBMIT_INTERVAL']);
					$this->jsonout(array('state' => true, 'url' => '/index.php?a=common&m=message&code=' . MCGetC('ASUP_ADD_SUCCESS')));
				}
				break;
		}

		// 错误提示
		$errors = $supportObj->getError();
		$message = array();
		if(!empty($errors) && is_array($errors))
		{
			$type = array
			(
				substr(MCGetC('MSUP_TITLE_EMPTY'), 0, 5) => (empty($type_cate) ? 'title' : $type_cate),
				substr(MCGetC('MSUP_CONTACT_EMPTY'), 0, 5) => 'contact',
				substr(MCGetC('MSUP_CONTENT_EMPTY'), 0, 5) => 'content',
				substr(MCGetC('MCON_SYSERR_TELA'), 0, 5) => 'other',
				substr(MCGetC('MSUP_CATE_ERROR'), 0, 5) => 'other',
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
}
