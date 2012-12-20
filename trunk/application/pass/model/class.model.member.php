<?php
class ModelMember extends ModelCommon
{
	public $_table = 'member';

	public function __construct()
	{
		parent::__construct();
	}

	public function isLogin()
	{
		return !empty($_SESSION['MEMBER']);
	}

	public function login($member_id, $account, $password, $is_md5 = false)
	{
		$condition = array();
		if($member_id)
		{
			$condition[] = array('id' => array('eq', $member_id));
		}
		else
		{
			// 判断账号类型
			Factory::loadLibrary('stringhelper');
			$stringhelper = new StringHelper();
			if($stringhelper->dataTypeTrue($account, 'email'))
			{
				$condition[] = array('email' => array('eq', $account));
				$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_EMAIL));
			}
			else if($stringhelper->dataTypeTrue($account, 'mobile'))
			{
				$condition[] = array('mobile' => array('eq', $account));
				$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_MOBILE));
			}
			else
			{
				$condition[] = array('account' => array('eq', $account));
			}
			$condition[] = array('password' => array('eq', $is_md5 ? $password : md5($password)));
		}

		// 查询账号
		$member = $this->getObject($condition);

		// 返回
		$result = false;
		if($member)
		{
			switch($member['status'])
			{
				case MEMBER_STATUS_DEFAULT: $_SESSION['MEMBER'] = $member; $result = true; break;
				case MEMBER_STATUS_UNACTIVE: $this->_error[] = MEMBER_LOGIN_UNACTIVE; break;
				case MEMBER_STATUS_DISABEL: $this->_error[] = MEMBER_LOGIN_DISABEL; break;
				case MEMBER_STATUS_DELETE: $this->_error[] = MEMBER_LOGIN_DELETE; break;
				default: $this->_error[] = MEMBER_LOGIN_UNKNOWSTATUS; break;
			}
		}
		else
		{
			$this->_error[] = MEMBER_LOGIN_NOTEXIST;
		}
		return $result;
	}

	public function logout()
	{
		unset($_SESSION['MEMBER']);
		setcookie('AUTO_LOGIN', '', -1);
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
				$object = $this->getObject($condition, array(), 'member_login');
				if($object && $object['auto_login_key'] == md5($key[0] . $key[1] . $key[2]) && $object['auto_login_ip'] == ip2long(REMOTE_IP_ADDRESS))
				{
					if($this->login($key[0], null, null))
					{
						$this->setAutoLogin();
						return true;
					}
				}
			}
		}
		return false;
	}

	public function setAutoLogin()
	{
		$member_id = $_SESSION['MEMBER']['id'];
		$expire = time() + 86400 * 7;
		$client_key = md5($member_id . mt_rand() . time());
		$server_key = md5($member_id . $client_key . $expire);

		// 保存密钥		
		$condition = array();
		$condition[] = array('id' => array('eq', $member_id));
		$data = array('auto_login_key' => $server_key, 'auto_login_ip' => ip2long(REMOTE_IP_ADDRESS));
		if($this->update($condition, $data, 'member_login'))
		{
			setcookie('AUTO_LOGIN', $member_id . '|' . $client_key . '|' . $expire, $expire);
			return true;
		}
		return false;
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
			$member['password'] = md5($member['password']);
			$data = array
			(
				'image' => '',
				'status' => MEMBER_STATUS_DEFAULT,
				'point_coin' => 0,
				'point_level' => 0,
				'verify_info' => 0,
				'create_time' => time(),
				'update_time' => time()
			);
			return $this->insert(array_merge($data, $member));
		}
		$this->_error[] = $err;
		return false;
	}

	public function autoRegister()
	{
		$this->register($member, 'auto');
	}

	public function getMemberPower()
	{
		$result = explode(';', $_SESSION['MEMBER']['POWER']);
		foreach($result as $k => $v)
		{
			if(empty($v))
			{
				unset($result[$k]);
			}
		}
		return $result;
	}

	public function hasPower($a, $m)
	{
		$am = $a . ':' . $m;
		foreach($GLOBALS['CONFIG']['POWER'] as $k => $v)
		{
			if(in_array($am, $v['ACTIONMETHOD']))
			{
				$am = $k;
				break;
			}
		}
		return in_array($am, $this->getMemberPower());
	}

	public function checkErrName($vals, $can_empty = false)
	{
		if(empty($vals['first_name'])) return $can_empty ? false : MEMBER_REGISTER_FNAMEEMPTY;
		if(empty($vals['last_name'])) return $can_empty ? false : MEMBER_REGISTER_LNAMEEMPTY;
		if(strlen($vals['first_name']) > 50) return MEMBER_REGISTER_FNAMELENMAX;
		if(strlen($vals['last_name']) > 50) return MEMBER_REGISTER_LNAMELENMAX;
		if(!preg_match('/^[\x{4e00}-\x{9fa5}\w]+$/u', $vals['first_name'])) return MEMBER_REGISTER_FNAMEERROR;
		if(!preg_match('/^[\x{4e00}-\x{9fa5}\w]+$/u', $vals['last_name'])) return MEMBER_REGISTER_LNAMEERROR;

		return false;
	}

	public function checkErrAccount($vals, $can_empty = false)
	{
		if(empty($vals['account'])) return $can_empty ? false : MEMBER_REGISTER_ACCOUNTEMPTY;
		$len = strlen($vals['account']);
		if($len < 6 || $len > 20) return MEMBER_REGISTER_ACCOUNTLENERROR;
		if($len > 8 && !preg_match('/[^\d+.]/', $vals['account'])) return MEMBER_REGISTER_ACCOUNTLENGTE8ERROR;
		if(!preg_match('/^[a-z0-9.]+$/i', $vals['account'])) return MEMBER_REGISTER_ACCOUNTCHRERROR;
		$condition = array();
		$condition[] = array('account' => array('eq', $vals['account']));
		if($this->getOne($condition, 'id')) return MEMBER_REGISTER_ACCOUNTEXITS;

		return false;
	}

	public function checkErrPassword($vals, $can_empty = false)
	{
		if(empty($vals['password'])) return $can_empty ? false : MEMBER_REGISTER_PASSWORDEMPTY;
		$len = strlen($vals['password']);
		if($len < 6) return MEMBER_REGISTER_PASSWORDLENERROR;
		if($len > 30) return MEMBER_REGISTER_PASSWORDLENMAX;
		if(empty($vals['passwordcfm'])) return MEMBER_REGISTER_PASSWORDCFMEMPTY;
		if($vals['password'] != $vals['passwordcfm']) return MEMBER_REGISTER_PASSWORDCFMNEQ;

		return false;
	}

	public function checkErrEmail($vals, $can_empty = false)
	{
		if(empty($vals['email'])) return $can_empty ? false : MEMBER_REGISTER_EMAILEMPTY;
		$at_count = count(explode('@', $vals['email']));
		if($at_count > 2) return MEMBER_REGISTER_EMAILMOREAT;
		Factory::loadLibrary('stringhelper');
		$stringhelper = new StringHelper();
		if(!$stringhelper->dataTypeTrue($vals['email'], 'email')) return MEMBER_REGISTER_EMAILERROR;
		$condition = array();
		$condition[] = array('email' => array('eq', $vals['email']));
		$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_EMAIL));
		if($this->getOne($condition, 'id')) return MEMBER_REGISTER_EMAILEXITSVERIFY;

		return false;
	}

	public function checkErrMobile($vals, $can_empty = false)
	{
		if(empty($vals['mobile'])) return $can_empty ? false : MEMBER_REGISTER_MOBILEEMPTY;
		Factory::loadLibrary('stringhelper');
		$stringhelper = new StringHelper();
		if(!$stringhelper->dataTypeTrue($vals['mobile'], 'mobile')) return MEMBER_REGISTER_MOBILERROR;
		$condition = array();
		$condition[] = array('mobile' => array('eq', $vals['mobile']));
		$condition[] = array('verify_info' => array('and', MEMBER_VERIFY_MOBILE));
		if($this->getOne($condition, 'id')) return MEMBER_REGISTER_MOBILEEXITSVERIFY;

		return false;
	}
}
