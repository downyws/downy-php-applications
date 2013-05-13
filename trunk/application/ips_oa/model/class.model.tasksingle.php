<?php
class ModelTaskSingle extends ModelCommon
{
	public $_table = 'task_single';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGER_SIZE)
	{
		$condition = array();
		!empty($params['account']) && $condition[] = array('u.`account`' => array('like', $params['account']));
		!empty($params['contact']) && $condition[] = array('t.`contact`' => array('like', $params['contact']));
		!empty($params['channel']) && $condition[] = array('ts.`channel_id`' => array('eq', $params['channel']));
		!empty($params['send_state']) && $condition[] = array('ts.`send_state`' => array('eq', $params['send_state']));
		!empty($params['start_time']) && $condition[] = array('ts.`plan_send_time`' => array('gte', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('ts.`plan_send_time`' => array('lte', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS ts JOIN ' . $this->table('user') . ' AS u ON (u.`id` = ts.`user_id`) JOIN ' . $this->table('target') . ' AS t ON (t.`id` = ts.`target_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT ts.*, u.account, t.contact, c.name AS channel_name, c.type AS channel_type FROM ' . $this->table() . ' AS ts JOIN ' . $this->table('user') . ' AS u ON (u.`id` = ts.`user_id`) JOIN ' . $this->table('target') . ' AS t ON (t.`id` = ts.`target_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = ts.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY ts.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchRows($sql);
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
		$userObj = Factory::getModel('user');

		// 目标地址检查
		$targetObj = Factory::getModel('target');
		$data['target_id'] = $targetObj->contactToId($data['target_contact']);
		if($data['target_id'] < 1)
		{
			return array('state' => false, 'message' => '目标地址错误。');
		}
		else if($targetObj->disableById($data['target_id']))
		{
			return array('state' => false, 'message' => '不允许向目标地址发送信息。');
		}
		$target_type = $targetObj->contactType($data['target_contact']);
		unset($data['target_contact']);

		// 通道检查
		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		if(!$channel)
		{
			return array('state' => false, 'message' => '请选择通道。');
		}
		else if($channel['type'] != $target_type)
		{
			return array('state' => false, 'message' => '通道类型与目标地址类型不符。');
		}
		else if(!$userObj->hasChannel($channel['id']))
		{
			return array('state' => false, 'message' => '没有该通道的权限。');
		}
		else if($channel['is_disable'])
		{
			return array('state' => false, 'message' => '通道已被禁用。');
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
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser($data['user_id'], 'id');
		if(!$user)
		{
			return array('state' => false, 'message' => '创建人不存在。');
		}

		// 计划发送时间
		if(empty($data['plan_send_time']))
		{
			$data['plan_send_time'] = time();
		}
		else
		{
			$data['plan_send_time'] = strtotime($data['plan_send_time']);
			if($data['plan_send_time'] < time())
			{
				$data['plan_send_time'] = time();
			}
		}

		// 发送上线检查
		$today = strtotime(date('Y-m-d', time()));
		$condition = array();
		$condition[] = array('user_id' => array('eq', $data['user_id']));
		$condition[] = array('create_time' => array('between', array($today, $today + 86399)));
		$condition[] = array('send_state' => array('not in', array(4, 5)));
		$had_send = $this->getOne($condition, 'COUNT(*)');
		if($user['tasksingle_limit_day'] != 0 && $user['tasksingle_limit_day'] <= $had_send)
		{
			return array('state' => false, 'message' => '已达到当日单任务发送上线。');
		}

		// 默认参数
		$data['create_time'] = time();
		$data['send_state'] = 1;
		$data['send_time'] = 0;
		$data['page_view'] = 0;
		$data['first_read_time'] = 0;
		$data['last_read_time'] = 0;

		$state = $this->insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_TASKSINGLE, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function addPv($id)
	{
		$sql = 'UPDATE ' . $this->table('') . ' SET `page_view` = `page_view` + 1, `last_read_time` = ' . time() . ', `first_read_time` = IF(`first_read_time` > 0, `first_read_time`, ' . time() . ') WHERE `id` = ' . $id;
		$this->query($sql);
	}

	public function edit($id, $data)
	{
		$userObj = Factory::getModel('user');

		$targetObj = Factory::getModel('target');
		$tasksingle = $this->getObject(array(array('id' => array('eq', $id))));

		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'target_contact':
					$data['target_id'] = $targetObj->contactToId($v);
					if($data['target_id'] < 1)
					{
						return array('state' => false, 'message' => '目标地址错误。');
					}
					else if($targetObj->disableById($data['target_id']))
					{
						return array('state' => false, 'message' => '不允许向目标地址发送信息。');
					}
					unset($data['target_contact']);
					break;
				case 'channel_id':
					$data[$k] = intval($v);
					$channel = $this->getObject(array(array('id' => array('eq', $data[$k]))), array(), 'channel');
					if(empty($channel))
					{
						return array('state' => false, 'message' => '通道不存在。');
					}
					else if(!$userObj->hasChannel($data[$k]))
					{
						return array('state' => false, 'message' => '没有该通道的权限。');
					}
					else if($channel['is_disable'])
					{
						return array('state' => false, 'message' => '通道已被禁用。');
					}
					break;
				case 'title':
					$data[$k] = trim($v);
					break;
				case 'content':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						return array('state' => false, 'message' => '发送内容不能为空。');
					}
					break;
				case 'plan_send_time':
					$data[$k] = strtotime($v);
					if($data[$k] < time())
					{
						$data[$k] = time();
					}
					break;
				default:
					unset($data[$k]);
			}
		}

		// 检查通道标题是否正确
		$tasksingle = array_merge($tasksingle, $data);
		$target_type = $this->getOne(array(array('id' => array('eq', $tasksingle['target_id']))), 'type', 'target');
		$channel_type = $this->getOne(array(array('id' => array('eq', $tasksingle['channel_id']))), 'type', 'channel');
		if($target_type != $channel_type)
		{
			return array('state' => false, 'message' => '通道类型与目标地址类型不符。');
		}
		else if($channel_type == CHANNEL_TYPE_EMAIL && empty($tasksingle['title']))
		{
			return array('state' => false, 'message' => '邮件标题不能为空。');
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = $this->update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_TASKSINGLE, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
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

	public function taskReceive($channel, $count)
	{
		$nowstamp = time();

		// 通道是否被禁用
		$condition = array();
		$condition[] = array('id' => array('eq', $channel));
		$channel = $this->getObject($condition, array(), 'channel');
		if(!$channel || $channel['is_disable'])
		{
			return array('state' => false, 'message' => '通道被禁用。');
		}

		// 获取任务
		$condition = array();
		$condition[] = array('channel_id' => array('eq', $channel['id']));
		$condition[] = array('send_state' => array('eq', 1));
		$condition[] = array('send_time' => array('eq', 0));
		$condition[] = array('plan_send_time' => array('lte', $nowstamp));
		$sql = 'SELECT id FROM ' . $this->table('task_single') . $this->getWhere($condition) . ' ORDER BY `plan_send_time` ASC ' . $this->getLimit(1, $count);
		$ids = $this->fetchCol($sql);
		
		// 给任务打上标记
		$condition = array();
		$condition[] = array('id' => array('in', $ids));
		$condition[] = array('send_state' => array('eq', 1));
		$condition[] = array('send_time' => array('eq', 0));
		$data = array();
		$data['send_state'] = 2;
		$data['send_time'] = $nowstamp;
		$this->update($condition, $data);

		// 获取最终要发送的任务
		$condition = array();
		$condition[] = array('ts.`id`' => array('in', $ids));
		$condition[] = array('ts.`send_state`' => array('eq', 2));
		$condition[] = array('ts.`send_time`' => array('eq', $nowstamp));
		$sql = 'SELECT ts.`id`, t.`contact`, ts.`title`, ts.`content` FROM ' . $this->table('') . ' AS ts JOIN ' . $this->table('target') . ' AS t ON t.`id` = ts.`target_id` ' . $this->getWhere($condition);
		$list = $this->fetchRows($sql);

		// 添加PV图标
		if($channel['type'] == CHANNEL_TYPE_EMAIL)
		{
			foreach($list as $k => $v)
			{
				$list[$k]['content'] .= '<img src="' . DOMAIN_OUTSIDE . 'index.php?a=read&m=pv&id=' . $v['id'] . '&ms=single" />';
			}
		}

		return array('state' => true, 'message' => '', 'data' => $list);
	}

	public function taskSubmit($list)
	{
		// 结果分类
		$finish = array();
		foreach($list as $k => $v)
		{
			$finish[($v ? '3' : '5')][] = $k;
		}
		unset($list);

		// 更新
		foreach($finish as $k => $v)
		{
			$condition = array();
			$condition[] = array('id' => array('in', $v));
			$condition[] = array('send_state' => array('eq', 2));
			$data = array();
			$data['send_state'] = $k;
			$this->update($condition, $data);
		}
	}

	public function taskReflash($channel)
	{
		// 超时发送任务标记失败
		$condition = array();
		$condition[] = array('channel_id' => array('eq', $channel));
		$condition[] = array('create_time' => array('gt', time() - TASK_SCAN_RANGE));
		$condition[] = array('send_state' => array('eq', 2));
		$condition[] = array('send_time' => array('lt', time() - TASK_TIMEOUT));
		$data = array();
		$data['send_state'] = 5;
		$this->update($condition, $data);

		// 更新通道最后运行时间
		$condition = array();
		$condition[] = array('id' => array('eq', $channel));
		$data = array();
		$data['last_run'] = time();
		$this->update($condition, $data, 'channel');
	}
}
