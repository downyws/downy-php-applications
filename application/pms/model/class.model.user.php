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

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$data['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$data['is_disable']];

		$data['power'] = explode(';', $data['power']);
		$data['power_format'] = array();
		foreach($data['power'] as $k => $v)
		{
			if(empty($v))
			{
				unset($data[$k]);
			}
			else
			{
				$data['power_format'][] = $GLOBALS['CONFIG']['POWER'][$v]['NAME'];
			}
		}

		$channelObj = Factory::getModel('channel');
		$channels = $channelObj->getAllPairs();
		$data['channel'] = explode(';', $data['channel']);
		$data['channel_format'] = array();
		foreach($data['channel'] as $k => $v)
		{
			if(empty($v))
			{
				unset($data[$k]);
			}
			else
			{
				$data['channel_format'][] = $channels[$v];
			}
		}

		return $data;
	}

	public function add($data)
	{
		// 账号检查
		$data['account'] = trim($data['account']);
		$data['account'] = strtolower($data['account']);
		$result = $this->validAccount($data['account']);
		if(!$result['state'])
		{
			return $result;
		}

		// 密码检查
		$data['password'] = trim('password');
		if(empty($data['password']))
		{
			return array('state' => false, 'message' => '密码不能为空。');
		}
		$data['password'] = md5($data['password']);

		// 权限设置
		$power_list = array_keys($GLOBALS['CONFIG']['POWER']);
		if(is_array($data['power']) && !empty($data['power']))
		{
			foreach($data['power'] as $k => $v)
			{
				if(!in_array($v, $power_list))
				{
					unset($data['power'][$k]);
				}
			}
		}
		$data['power'] = (is_array($data['power']) && count($data['power']) > 0) ? implode(';', $data['power']) : '';

		// 通道设置
		if(is_array($data['channel']) && !empty($data['channel']))
		{
			$channelObj = Factory::getModel('channel');
			$channels = array_keys($channelObj->getAllPairs());
			foreach($data['channel'] as $k => $v)
			{
				if(!in_array($v, $channels))
				{
					unset($data['channel'][$k]);
				}
			}
		}
		$data['channel'] = (is_array($data['channel']) && count($data['channel']) > 0) ? implode(';', $data['channel']) : '';

		// 发送上线
		$data['tasksingle_limit_day'] = intval($data['tasksingle_limit_day']) >= 0 ? $data['tasksingle_limit_day'] : 1;

		// 是否禁用
		$data['is_disable'] = intval($data['is_disable']) > 0 ? 1 : 0;

		$state = $this->insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_USER, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function edit($id, $data)
	{
		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'account':
					$data[$k] = trim($v);
					$data[$k] = strtolower($data[$k]);
					$result = $this->validAccount($data[$k], array($id));
					if(!$result['state'])
					{
						return $result;
					}
					break;
				case 'password':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						unset($data[$k]);
					}
					else
					{
						$data[$k] = md5($data[$k]);
					}
					break;
				case 'power':
					$power_list = array_keys($GLOBALS['CONFIG']['POWER']);
					if(is_array($data[$k]) && !empty($data[$k]))
					{
						foreach($data[$k] as $_k => $_v)
						{
							if(!in_array($_v, $power_list))
							{
								unset($data[$k][$_k]);
							}
						}
					}
					$data[$k] = (is_array($data[$k]) && count($data[$k]) > 0) ? implode(';', $data[$k]) : '';
					break;
				case 'channel':
					if(is_array($data[$k]) && !empty($data[$k]))
					{
						$channelObj = Factory::getModel('channel');
						$channels = array_keys($channelObj->getAllPairs());
						foreach($data[$k] as $_k => $_v)
						{
							if(!in_array($_v, $channels))
							{
								unset($data[$k][$_k]);
							}
						}
					}
					$data[$k] = (is_array($data[$k]) && count($data[$k]) > 0) ? implode(';', $data[$k]) : '';
					break;
				case 'is_disable':
					$data[$k] = intval($data[$k]) > 0 ? 1 : 0;
					break;
				case 'tasksingle_limit_day':
					$data[$k] = intval($data[$k]) >= 0 ? $data[$k] : 1;
					break;
				default:
					unset($data[$k]);
			}
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = $this->update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_USER, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function validAccount($account, $not_in_id = array())
	{
		// 空
		if(empty($account))
		{
			return array('state' => false, 'message' => '账号不能为空。');
		}

		// 长度
		if(strlen($account) < 3)
		{
			return array('state' => false, 'message' => '账号长度必须大于3位。');
		}

		// 开头
		if(!preg_match('/^[a-z]/i', $account))
		{
			return array('state' => false, 'message' => '账号必须以字母开头。');
		}

		// 特殊字符过滤
		if(!preg_match('/^([0-9a-z\_\.]+)$/i', $account))
		{
			return array('state' => false, 'message' => '账号只允许包含数字、字母、下划线和点。');
		}

		// 是否存在
		$condition = array();
		$condition[] = array('account' => array('eq', $account));
		(count($not_in_id) > 0) && $condition[] = array('id' => array('not in', $not_in_id));
		$exists = $this->getOne($condition, 'COUNT(*)');
		if($exists > 0)
		{
			return array('state' => false, 'message' => '账号已经存在。');
		}
		return array('state' => true, 'message' => '');
	}

	public function isLogin()
	{
		return !empty($_SESSION['user']);
	}

	public function login($account, $password, $is_md5 = false)
	{
		$condition = array();
		$condition[] = array('account' => array('eq', $account));
		$condition[] = array('password' => array('eq', $is_md5 ? $password : md5($password)));
		$condition[] = array('is_disable' => array('eq', 0));
		$user = $this->getObject($condition);
		if($user)
		{
			$this->update($condition, array('last_login' => time()));
			$_SESSION['user'] = $user;
			$message = '';
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

			$this->record($user_id, LOG_DATA_TABLE_USER, LOG_OPERATION_TYPE_UPDATE);
		}
		return $result;
	}

	public function getUser($value = 0, $type = '')
	{
		if($value == 0 && empty($type))
		{
			return empty($_SESSION['user']) ? null : $_SESSION['user'];
		}
		else if(in_array($type, array('id', 'account')))
		{
			return $this->getObject(array(array($type => array('eq', $value))));
		}
		return false;
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

	public function existsField($field, $value)
	{
		$condition = array();
		$condition[] = array($field => array('eq', $value));
		$exists = $this->getOne($condition, 'COUNT(*)');
		return $exists > 0;
	}
}
