<?php
class ModelMember extends ModelCommon
{
	public $_table = 'member';

	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Model', 'define.model.', __CLASS__) . '.php');
	}

	public function getMemberInfo($member_id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $member_id));
		return $this->getObject($condition, null, 'member_info');
	}

	public function getMemberPrivacy($member_id)
	{
		$condition = array();
		$condition[] = array('member_id' => array('eq', $member_id));
		$privacys = $this->getPairs($condition, array('field', 'type'), 'member_privacy');
		foreach($GLOBALS['PRIVACY']['DEFAULT'] as $k => $v)
		{
			if($v < 0) $privacys[$k] = $v * -1;
			else if(empty($privacys[$k])) $privacys[$k] = $v;
		}
		return $privacys;
	}

	public function getMemberApps($member_id)
	{
		$filecache = new Filecache();
		$cache = array('key' => strtolower(__CLASS__ . '_' . __FUNCTION__ . '/' . $member_id), 'time' => 1800);
		$apps = $filecache->get($cache['key']);
		if(!$apps)
		{
			$condition = array();
			$condition[] = array('ma.member_id' => array('eq', $member_id));
			$condition[] = array('a.status' => array('eq', STATUS_DEFAULT));
			$condition[] = array('ma.status' => array('neq', STATUS_DISCARD));
			$sql = 'SELECT ma.*, a.name, a.image, a.home_page FROM ' . $this->table('member_application') . ' AS ma JOIN ' . $this->table('appliction') . ' AS a ON a.id = ma.application_id ' . 
				$this->getWhere($condition) . ' ORDER BY update_time DESC ';
			$apps = $this->fetchRows($sql);
			foreach($apps as $k => $v)
			{
				$today = mktime(1, 0, 0, date('m'), date('d'), date('Y'));
				$addday = mktime(0, 0, 0, date('m', $apps[$k]['create_time']), date('d', $apps[$k]['create_time']), date('Y', $apps[$k]['create_time']));
				$apps[$k]['exp'] = ceil(($today - $addday) / 86400);
				$apps[$k]['data'] = json_decode($apps[$k]['data'], true);
			}
			$filecache->set($cache['key'], $apps, $cache['time']);
		}
		return $apps;
	}

	public function login($params, $type = '', $is_md5 = false, $log_inout = true)
	{
		$err = array();
		$condition = array();
		if($type == 'id')
		{
			$condition[] = array('id' => array('eq', $params));
		}
		else
		{
			empty($params['account']) && $err[] = MCGetC('MMER_PLZ_IN_ACCOUNT');
			empty($params['password']) && $err[] = MCGetC('MMER_PLZ_IN_PASSWORD');

			if(empty($err))
			{
				// 判断账号类型
				Factory::loadLibrary('stringhelper');
				$stringhelper = new StringHelper();
				if($stringhelper->dataTypeTrue($params['account'], 'email'))
				{
					$condition[] = array('email' => array('eq', $params['account']));
					$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_EMAIL));
				}
				else if($stringhelper->dataTypeTrue($params['account'], 'mobile'))
				{
					$condition[] = array('mobile' => array('eq', $params['account']));
					$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_MOBILE));
				}
				else
				{
					$condition[] = array('account' => array('eq', $params['account']));
				}
				$condition[] = array('password' => array('eq', $is_md5 ? $params['password'] : md5($params['password'])));
			}
		}

		// 查询账号
		if(empty($err))
		{
			$member = $this->getObject($condition);
			if($member)
			{
				switch($member['status'])
				{
					case STATUS_DEFAULT: 
						// 上次登录信息
						if($member['last_inout_id'])
						{
							$condition = array();
							$condition[] = array('id' => array('eq', $member['last_inout_id']));
							$member['last'] = $this->getObject($condition, null, 'inout');
						}
						else
						{
							$member['last'] = null;
						}

						$_SESSION['MEMBER'] = $member; 

						// 记录本次登录
						$log_inout && $inout_id = $this->logInOut(INOUT_TPYE_IN);

						// 首次登录
						if(!$member['first_inout_id'])
						{
							$member['first_inout_id'] = $inout_id;
							$condition = array();
							$condition[] = array('id' => array('eq', $member['id']));
							$data = array('first_inout_id' => $inout_id);
							$this->update($condition, $data);
						}
						return true; 
					case STATUS_UNACTIVE: $err[] = MCGetC('MMER_ACCOUNT_UNACTIVE'); break;
					case STATUS_DISABEL: $err[] = MCGetC('MMER_ACCOUNT_DISABLE'); break;
					default: $err[] = MCGetC('MMER_ACCOUNT_ERRSTA_TELA'); break;
				}
			}
			else
			{
				$err[] = MCGetC(($type == 'id') ? 'MMER_ACCOUNT_NOEXIST' : 'MMER_ACTORPAD_MAYBEERROR');
			}
		}
		$this->_error[] = $err;
		return false;
	}

	public function autoLogin()
	{
		if(!empty($_COOKIE['AUTO_LOGIN']))
		{
			$key = explode('|', $_COOKIE['AUTO_LOGIN']);
			if(count($key) == 3 && $key[2] >= time())
			{
				$condition = array();
				$condition[] = array('id' => array('eq', $key[0]));
				$object = $this->getObject($condition, array(), 'member');
				if($object && $object['auto_login_key'] == md5($key[0] . $key[1] . $key[2]) && $object['auto_login_ip'] == ip2long(REMOTE_IP_ADDRESS))
				{
					if($this->login($key[0], 'id'))
					{
						$this->setAutoLogin();
						return true;
					}
				}
			}
		}
		return false;
	}

	public function isLogin()
	{
		return !empty($_SESSION['MEMBER']);
	}

	public function setAutoLogin($member_id = 0)
	{
		$member_id = empty($member_id) ? $_SESSION['MEMBER']['id'] : $member_id;
		$expire = time() + 86400 * 7;
		$client_key = md5($member_id . mt_rand() . time());
		$server_key = md5($member_id . $client_key . $expire);

		// 保存密钥		
		$condition = array();
		$condition[] = array('id' => array('eq', $member_id));
		$data = array('auto_login_key' => $server_key, 'auto_login_ip' => ip2long(REMOTE_IP_ADDRESS));
		if($this->update($condition, $data))
		{
			setcookie('AUTO_LOGIN', $member_id . '|' . $client_key . '|' . $expire, $expire);
			return true;
		}
		return false;
	}

	public function logout($log_inout = true)
	{
		if($this->isLogin())
		{
			$log_inout && $this->logInOut(INOUT_TPYE_OUT);
			unset($_SESSION['MEMBER']);
			setcookie('AUTO_LOGIN', '', -1);
		}
	}

	public function register($member, $type = '')
	{
		$err = array();
		$res = $this->checkErrName($member, false);
		$res && $err[] = $res;
		$res = $this->checkErrAccount($member, false);
		$res && $err[] = $res;
		$res = $this->checkErrPassword($member, false);
		$res && $err[] = $res;
		$res = $this->checkErrEmail($member, true);
		$res && $err[] = $res;
		$res = $this->checkErrMobile($member, true);
		$res && $err[] = $res;

		if(empty($err) && $type != 'check')
		{			
			$this->transStart();

			// member
			$member['password'] = md5($member['password']);
			$data = array('image' => '', 'status' => STATUS_DEFAULT, 'point_coin' => 0, 'point_level' => 0, 'online_long' => 0, 'verify_info' => 0, 'auto_login_ip' => 0, 'auto_login_key' => '', 'first_inout_id' => 0, 'last_inout_id' => 0, 'create_time' => time(), 'update_time' => time());
			$member_id = $this->insert(array_merge($data, $member));

			// member_info
			$data = array('id' => $member_id, 'birthday' => 0, 'blood' => $GLOBALS['BLOOD']['OTHER'], 'sign' => '');
			!empty($member['ext_info']) && $data = array_merge($data, $member['ext_info']);
			$this->insert($data, 'member_info');

			// logs && check
			$this->operateLog($member_id);
			$this->operateCheck($member_id, CHECK_MEMBER_REGISTER, $member_id);

			$res = $this->transCommit();

			if($res)
			{
				return $member_id;
			}
			else
			{
				$err[] = 'MCON_SYSERR_TELA';
			}
		}
		$this->_error[] = $err;
		return false;
	}

	public function logInOut($type, $member_id = 0)
	{
		$member_id = empty($member_id) ? $_SESSION['MEMBER']['id'] : $member_id;

		$this->transStart();

		$data = array('member_id' => $member_id, 'ip' => ip2long(REMOTE_IP_ADDRESS), 'type' => $type, 'session_key' => session_id(), 'inout_time' => time());
		$inout_id = $this->insert($data, 'inout');
		if($type == INOUT_TPYE_IN)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $member_id));
			$data = array('last_inout_id' => $inout_id);
			$this->update($condition, $data);
		}

		$res = $this->transCommit();

		return $inout_id;
	}

	public function modifyPortrait($params)
	{
	}

	public function modifyName($params)
	{
	}

	public function modifySex($params)
	{
	}

	public function modifyBirthday($params)
	{
	}

	public function modifyBlood($params)
	{
	}

	public function modifySign($params)
	{
	}

	public function modifyMobile($params)
	{
	}

	public function modifyEmail($params)
	{
	}

	public function checkErrName($vals, $can_empty = false)
	{
		if(empty($vals['first_name'])) return $can_empty ? false : MCGetC('MMER_FNAME_CNT_EMPTY');
		if(empty($vals['last_name'])) return $can_empty ? false : MCGetC('MMER_LNAME_CNT_EMPTY');
		if(strlen($vals['first_name']) > 50) return MCGetC('MMER_FNAME_SO_LONG');
		if(strlen($vals['last_name']) > 50) return MCGetC('MMER_LNAME_SO_LONG');
		if(!preg_match('/^[\x{4e00}-\x{9fa5}\w]+$/u', $vals['first_name'])) return MCGetC('MMER_FNAME_MAYBE_ERR');
		if(!preg_match('/^[\x{4e00}-\x{9fa5}\w]+$/u', $vals['last_name'])) return MCGetC('MMER_LNAME_MAYBE_ERR');

		return false;
	}

	public function checkErrAccount($vals, $can_empty = false)
	{
		if(empty($vals['account'])) return $can_empty ? false : MCGetC('MMER_ACCOUNT_CNT_EMPTY');
		$len = strlen($vals['account']);
		if($len < 6 || $len > 20) return MCGetC('MMER_ACCOUNT_LENBET_6_20');
		if($len > 8 && !preg_match('/[^\d+.]/', $vals['account'])) return MCGetC('MMER_ACCOUNT_LENGT8_NEED_LER');
		if(!preg_match('/^[a-z0-9.]+$/i', $vals['account'])) return MCGetC('MMER_ACCOUNT_ONLY_LER_NUM_DOT');
		$condition = array();
		$condition[] = array('account' => array('eq', $vals['account']));
		if($this->getOne($condition, 'id')) return MCGetC('MMER_ACCOUNT_EXIST_USE_OTHER');

		return false;
	}

	public function checkErrPassword($vals, $can_empty = false)
	{
		if(empty($vals['password'])) return $can_empty ? false : MCGetC('MMER_PWD_CNT_EMPTY');
		$len = strlen($vals['password']);
		if($len < 6) return MCGetC('MMER_PWD_SO_SHORT');
		if($len > 30) return MCGetC('MMER_PWD_SO_LONG');
		if(empty($vals['passwordcfm'])) return MCGetC('MMER_PWDC_CNT_EMPTY');
		if($vals['password'] != $vals['passwordcfm']) return MCGetC('MMER_PWDC_NOEQ_PWD');

		return false;
	}

	public function checkErrEmail($vals, $can_empty = false)
	{
		if(empty($vals['email'])) return $can_empty ? false : MCGetC('MMER_EMAIL_CNT_EMPTY');
		$at_count = count(explode('@', $vals['email']));
		if($at_count > 2) return MCGetC('MMER_EMAIL_MORE_AT');
		Factory::loadLibrary('stringhelper');
		$stringhelper = new StringHelper();
		if(!$stringhelper->dataTypeTrue($vals['email'], 'email')) return MCGetC('MMER_EMAIL_ERROR');
		$condition = array();
		$condition[] = array('email' => array('eq', $vals['email']));
		$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_EMAIL));
		if($this->getOne($condition, 'id')) return MCGetC('MMER_EMAIL_EXIVEY_USE_OTHER');

		return false;
	}

	public function checkErrMobile($vals, $can_empty = false)
	{
		if(empty($vals['mobile'])) return $can_empty ? false : MCGetC('MMER_MOBILE_CNT_EMPTY');
		Factory::loadLibrary('stringhelper');
		$stringhelper = new StringHelper();
		if(!$stringhelper->dataTypeTrue($vals['mobile'], 'mobile')) return MCGetC('MMER_MOBILE_ERROR');
		$condition = array();
		$condition[] = array('mobile' => array('eq', $vals['mobile']));
		$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_MOBILE));
		if($this->getOne($condition, 'id')) return MCGetC('MMER_MOBILE_EXIVEY_USE_OTHER');

		return false;
	}
}
