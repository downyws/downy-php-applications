<?php
class ModelUser extends ModelCommon
{
	public $_table = 'user';

	public function __construct()
	{
		parent::__construct();
	}

	public function getById($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$object = $this->getObject($condition, array());

		return $object;
	}

	public function getList($page_now, $page_size)
	{
		$sql = ' SELECT * FROM ' . $this->table() . ' ORDER BY nick ASC ' . $this->getLimit($page_now, $page_size);
		$data = $this->fetchRows($sql);

		$count = $this->getOne(array(), 'COUNT(*)');
		$pager = $this->getPager($page_now, $count, $page_size);

		$result = array('count' => $count, 'data' => $data, 'pager' => $pager);
		return $result;
	}

	public function login($email, $password)
	{
		// 攻击检测
		$filecache = new Filecache();
		$cache = array('key' => 'login_atk/' . md5(REMOTE_IP_ADDRESS), 'time' => 300);
		$count = intval($filecache->get($cache['key']));
		if($count > 3)
		{
			return array('state' => false, 'message' => 'Failed more then 3, wait login.');
		}

		// 获取账号信息
		$condition = array();
		$condition[] = array('email' => array('eq', $email));
		$condition[] = array('password' => array('eq', md5($password)));
		$user = $this->getObject($condition);

		// 登录错误
		if(!$user)
		{
			$filecache->set($cache['key'], $count + 1, $cache['time']);
			return array('state' => false, 'message' => 'Email or password error.');
		}

		// 是否锁定
		if($user['state'] == USER_STATE_LOCK)
		{
			return array('state' => false, 'message' => 'Email is lock.');
		}

		// 保存登录信息
		$individuation_detail = array('line_dict' => APP_PAGER_SIZE, 'line_ln' => APP_PAGER_SIZE, 'line_task' => APP_PAGER_SIZE, 'task_exco' => 0);
		if($user['individuation'] != '' && is_array(@json_decode($user['individuation'], true)))
		{
			$individuation_detail = array_merge($individuation_detail, json_decode($user['individuation'], true));
		}
		$user['individuation_detail'] = $individuation_detail;
		$this->setSessionUser($user);

		return array('state' => true, 'message' => 'Login success.');
	}

	public function editPassword($op, $np, $cp)
	{
		if(md5($op) != $this->getSessionUser('password'))
		{
			$result = array('state' => false, 'message' => 'Old password error.');
		}
		else if($np != $cp)
		{
			$result = array('state' => false, 'message' => 'New password neq confirm password.');
		}
		else
		{
			$id = $this->getSessionUser('id');
			$condition = array();
			$condition[] = array('id' => array('eq', $id));
			$result['state'] = $this->update($condition, array('password' => md5($np)));
			if($result['state'])
			{
				$this->setSessionUser(array('password' => md5($np)));
				$result['message'] = 'Password update success.';
			}
			else
			{
				$result['message'] = 'Password update error.';
			}
		}
		return $result;
	}

	public function saveIndividuation($params)
	{
		$options = array('line_dict', 'line_ln', 'line_task', 'task_exco');
		$data = array();
		foreach($options as $v)
		{
			$data[$v] = $params[$v];
		}

		$id = $this->getSessionUser('id');
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$result['state'] = $this->update($condition, array('individuation' => json_encode($data)));
		if($result['state'])
		{
			$this->setSessionUser(array('individuation_detail' => $data));
			$result['message'] = 'Individuation update success.';
		}
		else
		{
			$result['message'] = 'Individuation update error.';
		}
		return $result;
	}

	public function save($object)
	{
		$result = array('state' => false);

		// 昵称唯一检查
		$condition = array();
		$condition[] = array('id' => array('neq', $object['id']));
		$condition[] = array('nick' => array('eq', $object['nick']));
		if($this->getOne($condition, 'id'))
		{
			$result['message'] = 'nick exists.';
			return $result;
		}

		// 邮箱唯一检查
		$condition = array();
		$condition[] = array('id' => array('neq', $object['id']));
		$condition[] = array('email' => array('eq', $object['email']));
		if($this->getOne($condition, 'id'))
		{
			$result['message'] = 'email exists.';
			return $result;
		}

		if($object['id'] > 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			unset($object['id']);
			if(!empty($object['password']))
			{
				$object['password'] = md5($object['password']);
			}
			else
			{
				unset($object['password']);
			}
			$result['state'] = $this->update($condition, $object);
		}
		else
		{
			unset($object['id']);
			$default = array(
				'individuation' => '',
				'task_occ_count' => 0,
				'dedicate_t_count' => 0,
				'dedicate_p_count' => 0,
				'dedicate_a_count' => 0
			);
			$object['password'] = md5($object['password']);
			$object = array_merge($default, $object);
			$result['state'] = $this->insert($object);
		}

		$result['message'] = $result['state'] ? 'save success.' : 'save error.';

		return $result;
	}

	public function remove($id)
	{
		// 开启事务
		$this->transStart();

		// 删除用户
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$this->delete($condition);

		// 重置和该用户有关的mapping信息
		$condition = array();
		$condition[] = array
		(
			't_user_id' => array('eq', $id),
			'p_user_id' => array('eq', $id),
			'a_user_id' => array('eq', $id),
			'occ_user_id' => array('eq', $id)
		);
		$data = array
		(
			't_user_id' => 0,
			'p_user_id' => 0,
			'a_user_id' => 0,
			'occ_user_id' => 0,
			'occ_time' => 0,
			'state' => MAPPING_STATE_TW
		);
		$this->update($condition, $data, 'mapping');

		// 提交事务
		return $this->transCommit();
	}

	public function refreshMapping()
	{
		// 开启事务
		$this->transStart();

		// 清空数据
		$data = array
		(
			'dedicate_t_count' => 0,
			'dedicate_p_count' => 0,
			'dedicate_a_count' => 0,
			'task_occ_count' => 0
		);
		$this->update(array(), $data);

		// 重新计算
		$sql =  ' REPLACE INTO ' . $this->table() . ' ( ' .
				'	SELECT ' .
				'		u.id, u.nick, u.email, u.`password`, u.individuation, u.state, u.task_max_count, SUM(tb.o) AS task_occ_count, ' .
				'		SUM(tb.t) AS dedicate_t_count, SUM(tb.p) AS dedicate_p_count, SUM(tb.a) AS dedicate_a_count, ' .
				'		u.super, u.dict, u.translation, u.proof, u.audit ' .
				'	FROM( ' .
				'		SELECT t_user_id AS user_id, COUNT(*) AS t, 0 AS p, 0 AS a, 0 AS o FROM ' . $this->table('mapping') . ' WHERE state = ' . MAPPING_STATE_CP . ' GROUP BY user_id ' .
				'		UNION ALL ' .
				'		SELECT p_user_id AS user_id, 0 AS t, COUNT(*) AS p, 0 AS a, 0 AS o FROM ' . $this->table('mapping') . ' WHERE state = ' . MAPPING_STATE_CP . ' GROUP BY user_id ' .
				'		UNION ALL ' .
				'		SELECT a_user_id AS user_id, 0 AS t, 0 AS p, COUNT(*) AS a, 0 AS o FROM ' . $this->table('mapping') . ' WHERE state = ' . MAPPING_STATE_CP . ' GROUP BY user_id ' .
				'		UNION ALL ' .
				'		SELECT occ_user_id AS user_id, 0 AS t, 0 AS p, 0 AS a, COUNT(*) AS o FROM ' . $this->table('mapping') . ' GROUP BY user_id ' .
				'	) AS tb JOIN ' . $this->table() . ' AS u ON tb.user_id = u.id WHERE tb.user_id > 0 GROUP BY tb.user_id ' .
				' ) ';
		$this->query($sql);

		// 提交事务
		return $this->transCommit();
	}
}
