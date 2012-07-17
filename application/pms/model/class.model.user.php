<?php
class ModelUser extends ModelCommon
{
	public $_table = 'user';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		!empty($params['account']) && $condition[] = array('`account`' => array('like', $params['account']));
		in_array($params['is_disable'], array(0, 1)) && $condition[] = array('`is_disable`' => array('eq', $params['is_disable']));

		$count = $this->getOne($condition, 'COUNT(*)');
		$sql = 'SELECT * FROM ' . $this->table() . $this->getWhere($condition) . ' ORDER BY `account` ASC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatData($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
		}
		return $list;
	}

	public function isLogin()
	{
		return !empty($_SESSION['user']);
	}

	public function login($account, $password)
	{
		$condition = array();
		$condition[] = array('account' => array('eq', $account));
		$condition[] = array('password' => array('eq', md5($password)));
		$condition[] = array('is_disable' => array('eq', 0));
		$user = $this->getObject($condition);
		if($user)
		{
			$this->update($condition, array('last_login' => time()));
			$_SESSION['user'] = $user;
		}
		else
		{
			$message = '很遗憾，您无法登陆，请确认是否正确地输入了用户名和密码；如果仍无法登录，请确认您账号的权限。';
		}
		return array('state' => !empty($user), 'message' => $message);
	}

	public function logout()
	{
		unset($_SESSION['user']);
	}

	public function editPassword($user_id, $password)
	{
		$result = array('state' => false, 'message' => '未知错误。');
		if(empty($password))
		{
			$result['message'] = '密码不能为空。';
		}
		else
		{
			$condition = array(array('id' => array('eq', $user_id)));
			$data = array('password' => md5($password));
			$this->update($condition, $data);
			$result['state'] = true;
		}
		return $result;
	}

	public function getUser($id = 0)
	{
		if($id == 0)
		{
			return $_SESSION['user'];
		}
	}

	public function getUserPower()
	{
		$result = explode(';', $_SESSION['user']['power']);
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
		return in_array($am, $this->getUserPower());
	}

	public function getUserChannel()
	{
		$result = explode(';', $_SESSION['user']['channel']);
		foreach($result as $k => $v)
		{
			if(empty($v))
			{
				unset($result[$k]);
			}
		}
		return $result;
	}

	public function hasChannel($channel_id)
	{
		return in_array($channel_id, $this->hasChannel());
	}
}
