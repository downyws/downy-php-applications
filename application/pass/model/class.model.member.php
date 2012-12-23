<?php
class ModelMember extends ModelCommon
{
	public $_table = 'member';

	public function __construct()
	{
		parent::__construct();
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
			empty($params['account']) && $err[] = MEMBER_LOGIN_ACCOUNTEMPTY;
			empty($params['password']) && $err[] = MEMBER_LOGIN_PASSWORDEMPTY;

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
					case MEMBER_STATUS_DEFAULT: 
							$_SESSION['MEMBER'] = $member; 
							$log_inout && $this->logInOut(INOUT_TPYE_IN);
							return true; 
						break;
					case MEMBER_STATUS_UNACTIVE: $err[] = MEMBER_LOGIN_UNACTIVE; break;
					case MEMBER_STATUS_DISABEL: $err[] = MEMBER_LOGIN_DISABEL; break;
					case MEMBER_STATUS_DELETE: $err[] = MEMBER_LOGIN_DELETE; break;
					default: $err[] = MEMBER_LOGIN_UNKNOWSTATUS; break;
				}
			}
			else
			{
				$err[] = MEMBER_LOGIN_NOTEXIST;
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
			$data = array('image' => '', 'status' => MEMBER_STATUS_DEFAULT, 'point_coin' => 0, 'point_level' => 0, 'online_long' => 0, 'verify_info' => 0, 'auto_login_ip' => 0, 'auto_login_key' => '', 'first_inout_id' => 0, 'last_inout_id' => 0, 'create_time' => time(), 'update_time' => time());
			$member_id = $this->insert(array_merge($data, $member));

			// member_info
			$data = array('id' => $member_id, 'birthday' => 0, 'blood' => $GLOBALS['BLOOD']['OTHER'], 'sign' => '');
			$this->insert($data, 'member_info');

			// inout
			$inout_id = $this->logInOut(INOUT_TPYE_IN, $member_id);
			$condition = array();
			$condition[] = array('id' => array('eq', $member_id));
			$data = array('first_inout_id' => $inout_id, 'last_inout_id' => $inout_id);
			$this->update($condition, $data);

			// logs && check
			$this->operateLog($member_id);
			$this->operateCheck($member_id, CHECK_MEMBER_REGISTER, $member_id);

			$res = $this->transCommit();

			// login
			if($res)
			{
				$this->login($member_id, 'id', false, false);
				return true;
			}
			else
			{
				$err[] = COMMON_SYSTEMERROR;
			}
		}
		$this->_error[] = $err;
		return false;
	}

	public function autoRegister()
	{
		// $this->register($member, 'auto');
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
