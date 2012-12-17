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
}
