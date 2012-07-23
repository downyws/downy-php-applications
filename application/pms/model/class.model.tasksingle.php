<?php
class ModelTaskSingle extends ModelCommon
{
	public $_table = 'task_single';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		!empty($params['account']) && $condition[] = array('u.`account`' => array('like', $params['account']));
		!empty($params['contact']) && $condition[] = array('t.`contact`' => array('like', $params['contact']));
		!empty($params['channel']) && $condition[] = array('ts.`channel_id`' => array('eq', $params['channel']));
		!empty($params['send_state']) && $condition[] = array('ts.`send_state`' => array('eq', $params['send_state']));
		!empty($params['start_time']) && $condition[] = array('ts.`send_time`' => array('egt', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('ts.`send_time`' => array('elt', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS ts JOIN ' . $this->table('user') . ' AS u ON (u.`id` = ts.`user_id`) JOIN ' . $this->table('target') . ' AS t ON (t.`id` = ts.`target_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT ts.*, u.account, t.contact, c.name AS channel_name, c.type AS channel_type FROM ' . $this->table() . ' AS ts JOIN ' . $this->table('user') . ' AS u ON (u.`id` = ts.`user_id`) JOIN ' . $this->table('target') . ' AS t ON (t.`id` = ts.`target_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = ts.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY ts.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['channel_type_format'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$list[$k]['channel_type']];
			$list[$k]['send_state_format'] = $GLOBALS['CONFIG']['TASKSINGLE']['STATE'][$list[$k]['send_state']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$userObj = Factory::getModel('user');
		$targetObj = Factory::getModel('target');
		$channelObj = Factory::getModel('channel');

		$data['user'] = $userObj->getObject(array(array('id' => array('eq', $data['user_id']))));
		$data['target'] = $targetObj->getObject(array(array('id' => array('eq', $data['target_id']))));
		$data['channel'] = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		$data['channel']['type_format'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$data['channel']['type']];
		$data['send_state_format'] = $GLOBALS['CONFIG']['TASKSINGLE']['STATE'][$data['send_state']];
		return $data;
	}

	public function add($data)
	{
		// 目标地址检查
		$targetObj = Factory::getModel('target');
		$data['target_id'] = $targetObj->contactToId($data['target_contact']);
		if($data['target_id'] < 1)
		{
			return array('state' => false, 'message' => '目标地址错误。');
		}
		$target_type = $targetObj->contactType($data['target_contact']);
		unset($data['target_contact']);

		// 通道检查
		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		if($channel['type'] != $target_type)
		{
			return array('state' => false, 'message' => '通道类型与目标地址类型不符。');
		}

		// 标题检查
		$data['title'] = trim($data['title']);
		if($target_type == CHANNEL_TYPE_EMAIL && empty($data['title']))
		{
			return array('state' => false, 'message' => '邮件标题不能为空。');
		}

		// 内容检查
		$data['content'] = trim($data['content']);
		if(empty($data['content']))
		{
			return array('state' => false, 'message' => '发送内容不能为空。');
		}

		// 创建人检查
		if(!$this->getOne(array(array('id' => array('eq', $data['user_id']))), 'COUNT(*)', 'user'))
		{
			return array('state' => false, 'message' => '创建人不存在。');
		}

		// 计划发送时间
		$data['plan_send_time'] = strtotime($data['plan_send_time']);
		if($data['plan_send_time'] < time())
		{
			$data['plan_send_time'] = time();
		}

		// 默认参数
		$data['create_time'] = time();
		$data['send_state'] = 1;
		$data['send_time'] = 0;
		$data['page_view'] = 0;
		$data['first_read_time'] = 0;
		$data['last_read_time'] = 0;

		$state = parent::insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_TASKSINGLE, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function edit($id, $data)
	{// 通道过滤
	/*	foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'account':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						return array('state' => false, 'message' => '账号不能为空。');
					}
					else if($this->getOne(array(array('account' => array('eq', $data[$k]))), 'COUNT(*)') === false)
					{
						return array('state' => false, 'message' => '账号已经存在。');
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
		$state = parent::update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_USER, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);*/
	}

	public function cancel($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('send_state' => array('eq', 1));
		$state = $this->update($condition, array('send_state' => 4));
		$state && $this->record($id, LOG_DATA_TABLE_TASKSINGLE, LOG_OPERATION_TYPE_UPDATE);

		return array('state' => $state, 'message' => ($state ? '取消成功。' : '取消失败。'));
	}
}
