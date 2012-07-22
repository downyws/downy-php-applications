<?php
class ModelShortUri extends ModelCommon
{
	public $_table = 'short_uri';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		!empty($params['task_title']) && $condition[] = array('tm.`title`' => array('like', $params['task_title']));
		!empty($params['uri']) && $condition[] = array('su.`uri`' => array('like', $params['uri']));
		!empty($params['type']) && $condition[] = array('su.`type`' => array('eq', $params['type']));
		in_array($params['is_disable'], array(0, 1)) && $condition[] = array('su.`is_disable`' => array('eq', $params['is_disable']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . 'AS su LEFT JOIN ' . $this->table('task_multi') . ' AS tm ON (tm.`id` = su.`task_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT su.*, IF(tm.title IS NULL, \'\', tm.title) AS task_title FROM ' . $this->table() . ' AS su LEFT JOIN ' . $this->table('task_multi') . ' AS tm ON (tm.`id` = su.`task_id`) ' . $this->getWhere($condition) . ' ORDER BY su.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['key_format'] = $GLOBALS['CONFIG']['SHORT_URI']['DOMAIN'] . $list[$k]['key'];
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
			$list[$k]['type_format'] = $GLOBALS['CONFIG']['SHORT_URI']['TYPE'][$list[$k]['type']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$taskmultiObj = Factory::getModel('taskmulti');

		$data['taskmulti'] = $taskmultiObj->getObject(array(array('id' => array('eq', $data['task_id']))));
		$data['key_format'] = $GLOBALS['CONFIG']['SHORT_URI']['DOMAIN'] . $data['key'];
		$data['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$data['is_disable']];
		$data['type_format'] = $GLOBALS['CONFIG']['SHORT_URI']['TYPE'][$data['type']];
		return $data;
	}

	public function add($data)
	{
		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'task_id':
					$data[$k] = empty($data[$k]) ? 0 : intval($data[$k]);
					break;
				case 'key':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						$data[$k] = $this->createKey();
						if(!$data[$k])
						{
							return array('state' => false, 'message' => '生成短网址失败。');
						}
					}
					else if($this->existsKey($data[$k], array($id)))
					{
						return array('state' => false, 'message' => '短网址已存在。');
					}
					break;
				case 'uri':
					$data[$k] = trim($v);
					if(!preg_match('/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//', $data[$k]))
					{
						return array('state' => false, 'message' => '重定向网址错误。');
					}
					break;
				case 'is_disable':
					$data[$k] = intval($data[$k]) > 0 ? 1 : 0;
					break;
				case 'type':
					if(!in_array($data[$k], array_keys($GLOBALS['CONFIG']['SHORT_URI']['TYPE'])))
					{
						return array('state' => false, 'message' => '类型错误。');
					}
					break;
				default:
					unset($data[$k]);
			}
		}

		$state = parent::insert($data);
		if($state)
		{
			$userObj = Factory::getModel('user');
			$user = $userObj->getUser();
			$this->record($user['id'], $state, LOG_DATA_TABLE_SHORTURI, LOG_OPERATION_TYPE_INSERT);
		}
		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function edit($id, $data)
	{
		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'task_id':
					$data[$k] = empty($data[$k]) ? 0 : intval($data[$k]);
					break;
				case 'key':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						$data[$k] = $this->createKey($id);
						if(!$data[$k])
						{
							return array('state' => false, 'message' => '生成短网址失败。');
						}
					}
					else if($this->existsKey($data[$k], array($id)))
					{
						return array('state' => false, 'message' => '短网址已存在。');
					}
					break;
				case 'uri':
					$data[$k] = trim($v);
					if(!preg_match('/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//', $data[$k]))
					{
						return array('state' => false, 'message' => '重定向网址错误。');
					}
					break;
				case 'is_disable':
					$data[$k] = intval($data[$k]) > 0 ? 1 : 0;
					break;
				case 'type':
					if(!in_array($data[$k], array_keys($GLOBALS['CONFIG']['SHORT_URI']['TYPE'])))
					{
						return array('state' => false, 'message' => '类型错误。');
					}
					break;
				default:
					unset($data[$k]);
			}
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = parent::update($condition, $data);
		$state = ($state !== false);
		if($state)
		{
			$userObj = Factory::getModel('user');
			$user = $userObj->getUser();
			$this->record($user['id'], $id, LOG_DATA_TABLE_SHORTURI, LOG_OPERATION_TYPE_UPDATE);
		}
		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function createKey($seed = 0)
	{
		Factory::loadLibrary('mathhelper');
		$mathhelper = new MathHelper();

		if($seed < 1)
		{
			$seed = $this->getNextId();
		}
		$key_prefix = $mathhelper->from10toN(62, $seed);
		$key_prefix = str_pad($key_prefix, 6, '0', STR_PAD_LEFT);

		$try = 100;
		while(--$try)
		{
			$key_suffix = $mathhelper->from10toN(62, mt_rand(0, pow(62, 2) - 1));
			$key_suffix = str_pad($key_suffix, 2, '0', STR_PAD_LEFT);
			if(!$this->existsKey($key_prefix . $key_suffix, array($seed)))
			{
				break;
			}
		}

		return $try ? $key_prefix . $key_suffix : false;
	}

	public function existsKey($key, $not_in_id = array())
	{
		$condition = array();
		$condition[] = array('key' => array('eq', $key));
		$condition[] = array('id' => array('not in', $not_in_id));
		$exists = $this->getOne($condition, 'COUNT(*)');
		return $exists > 0;
	}

	public function getUri($key)
	{
		// 判断合法性
		if(!preg_match('/^[0-9A-za-z]+$/', $key))
		{
			return $GLOBALS['CONFIG']['SHORT_URI']['ERROR_PAGE'];
		}
		
		// 获取URI
		$condition = array(array('`key`' => array('eq', $key)));
		$uri = $this->getObject($condition);

		return $uri;
	}

	public function updateCount($key, $add_count)
	{
		$sql = 'UPDATE ' . $this->table() . ' SET `count` = (`count` + ' . $add_count . ') WHERE `key` = \'' . $key . '\'';
		$this->query($sql);
	}
}
