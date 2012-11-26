<?php
class ModelTaskMulti extends ModelCommon
{
	public $_table = 'task_multi';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		!empty($params['account']) && $condition[] = array('u.`account`' => array('like', $params['account']));
		!empty($params['name']) && $condition[] = array('tm.`name`' => array('like', $params['name']));
		!empty($params['channel']) && $condition[] = array('tm.`channel_id`' => array('eq', $params['channel']));
		!empty($params['send_state']) && $condition[] = array('tm.`send_state`' => array('eq', $params['send_state']));
		!empty($params['start_time']) && $condition[] = array('tm.`plan_send_time`' => array('gte', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('tm.`plan_send_time`' => array('lte', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT tm.*, u.account, c.name AS channel_name FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = tm.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY tm.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchRows($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['send_state_format'] = $GLOBALS['CONFIG']['TASKMULTI']['STATE'][$list[$k]['send_state']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$userObj = Factory::getModel('user');
		$targetObj = Factory::getModel('target');
		$channelObj = Factory::getModel('channel');

		$data['user'] = $userObj->getObject(array(array('id' => array('eq', $data['user_id']))));
		$data['checkuser'] = $userObj->getObject(array(array('id' => array('eq', $data['check_user_id']))));
		$data['channel'] = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		$data['channel']['type_format'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$data['channel']['type']];
		$data['send_state_format'] = $GLOBALS['CONFIG']['TASKMULTI']['STATE'][$data['send_state']];
		return $data;
	}

	public function add($data)
	{
		$userObj = Factory::getModel('user');

		// 任务名称检查
		$data['name'] = trim($data['name']);
		$result = $this->validName($data['name']);
		if(!$result['state'])
		{
			return $result;
		}

		// 通道检查
		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		if(!$channel)
		{
			return array('state' => false, 'message' => '请选择通道。');
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
		if($channel['type'] == CHANNEL_TYPE_EMAIL && empty($data['title']))
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
		$data['plan_send_time'] = strtotime($data['plan_send_time']);
		if($data['plan_send_time'] < time())
		{
			$data['plan_send_time'] = time();
		}

		// 默认参数
		$data['create_time'] = time();
		$data['check_user_id'] = 0;
		$data['check_time'] = 0;
		$data['send_count'] = 0;
		$data['accept_rate'] = 0;
		$data['send_rate'] = 0;
		$data['send_state'] = 5;
		$data['start_time'] = 0;
		$data['end_time'] = 0;
		$data['remarks'] = '';

		$state = $this->insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_TASKMULTI, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function addPv($id)
	{
		$sql = 'UPDATE ' . $this->table('send_list') . ' SET `page_view` = `page_view` + 1, `last_read_time` = ' . time() . ', `first_read_time` = IF(`first_read_time` > 0, `first_read_time`, ' . time() . ') WHERE `id` = ' . $id;
		$this->query($sql);
		
		$condition = array();
		$condition[] = array('id' => $id);
		$task_id = $this->getOne($condition, 'task_id', 'send_list');

		$condition = array();
		$condition[] = array('task_id' => array('eq', $task_id));
		$condition[] = array('page_view' => array('gt', 0));
		$accept_rate = $this->getOne($condition, 'COUNT(*)', 'send_list');

		$condition = array();
		$condition[] = array('id' => array('eq', $task_id));
		$this->update($condition, array('accept_rate' => $accept_rate));
	}

	public function edit($id, $data)
	{
		$userObj = Factory::getModel('user');
		$targetObj = Factory::getModel('target');
		$taskmulti = $this->getObject(array(array('id' => array('eq', $id))));
		$channel = $this->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))), array(), 'channel');

		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'name':
					$data[$k] = trim($v);
					$result = $this->validName($data[$k], array($id));
					if(!$result['state'])
					{
						return $result;
					}
					break;
				case 'channel_id':
					$data[$k] = intval($v);
					$new_channel = $this->getObject(array(array('id' => array('eq', $data[$k]))), array(), 'channel');
					if(empty($new_channel))
					{
						return array('state' => false, 'message' => '通道不存在。');
					}
					else if($channel['type'] != $new_channel['type'] && $taskmulti['send_count'] > 0)
					{
						return array('state' => false, 'message' => '改变通道类型前需先清空发送列表。');
					}
					else if(!$userObj->hasChannel($new_channel['id']))
					{
						return array('state' => false, 'message' => '没有该通道的权限。');
					}
					else if($new_channel['is_disable'])
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
		$taskmulti = array_merge($taskmulti, $data);
		$channel_type = $this->getOne(array(array('id' => array('eq', $taskmulti['channel_id']))), 'type', 'channel');
		if($channel_type == CHANNEL_TYPE_EMAIL && empty($taskmulti['title']))
		{
			return array('state' => false, 'message' => '邮件标题不能为空。');
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = $this->update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_TASKMULTI, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function validName($name, $not_in_id = array())
	{
		// 空
		if(empty($name))
		{
			return array('state' => false, 'message' => '任务名称不能为空。');
		}

		// 是否存在
		$condition = array();
		$condition[] = array('name' => array('eq', $name));
		(count($not_in_id) > 0) && $condition[] = array('id' => array('not in', $not_in_id));
		$exists = $this->getOne($condition, 'COUNT(*)');
		if($exists > 0)
		{
			return array('state' => false, 'message' => '任务名称已存在。');
		}
		return array('state' => true, 'message' => '');
	}

	public function cancel($id)
	{
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('send_state' => array('in', array(1, 2, 5, 6)));
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = 4, `remarks` = CONCAT("' . date('Y-m-d H:i:s') . '\\t' . $user['id'] . '\\t' . $user['account'] . '\\tcanceled\\n", `remarks`)' . $this->getWhere($condition);
		$state = $this->query($sql);
		$state && $this->record($id, LOG_DATA_TABLE_TASKMULTI, LOG_OPERATION_TYPE_UPDATE);

		return array('state' => $state, 'message' => ($state ? '取消成功。' : '取消失败。'));
	}

	public function check($id, $pass)
	{
		if(!in_array($pass, array(1, 5, 6)))
		{
			return array('state' => false, 'message' => '状态参数错误。');
		}
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		if(in_array($pass, array(1, 5)))
		{
			$condition[] = array('send_state' => array('eq', 6));
		}
		else
		{
			$condition[] = array('send_state' => array('eq', 5));
		}
		switch($pass)
		{
			case 1:
				$pass_message = '审核通过';
				break;
			case 5:
				$pass_message = '审核不通过';
				break;
			case 6:
				$pass_message = '提交审核';
				break;
		}
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = ' . intval($pass) . ', `remarks` = CONCAT("' . date('Y-m-d H:i:s') . '\\t' . $user['id'] . '\\t' . $user['account'] . '\\t' . $pass_message . '\\n", `remarks`)' . $this->getWhere($condition);
		$state = $this->query($sql);
		if($state && $pass == 1)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $id));
			$data = array();
			$data['check_user_id'] = $user['id'];
			$data['check_time'] = time();
			$this->update($condition, $data);
		}

		if($state)
		{
			// 日志记录
			$this->record($id, LOG_DATA_TABLE_TASKMULTI, LOG_OPERATION_TYPE_UPDATE);

			// 邮件类型则替换内容中的uri
			if($pass == 1)
			{
				$taskmulti = $this->getObject(array(array('id' => array('eq', $id))));
				$channel = $this->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))), array(), 'channel');
				if($channel['type'] == CHANNEL_TYPE_EMAIL)
				{
					$shorturiObj = Factory::getModel('shorturi');
					$shorturiObj->monitor($taskmulti['content'], $id);

					$condition = array();
					$condition[] = array('id' => $id);
					$this->update($condition, array('content' => $taskmulti['content']));
				}
			}
		}

		return array('state' => $state, 'message' => ($state ? '审核成功。' : '审核失败。'));
	}

	public function render($content, $data)
	{
		preg_match_all("/{\\$(.*?)}/i", $content, $tags);
		if(count($tags) == 2)
		{
			foreach($tags[1] as $k => $v)
			{
				$tag = $v;
				$tag = explode(',', $tag);

				$key = $tag[0];
				$len = empty($tag[1]) ? 0 : intval($tag[1]);

				$value = $len > 0 ? mb_substr($data[$key], 0, $len, 'UTF-8') : $data[$key];
				$content = str_replace($tags[0][$k], $value, $content);
			}
		}

		return $content;
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
		$condition[] = array('send_state' => array('eq', 2));
		$taskmultis = $this->getObjects($condition);
		$task_ids = array();
		foreach($taskmultis as $v)
		{
			$task_ids[] = $v['id'];
		}

		// 获取发送队列
		$condition = array();
		$condition[] = array('task_id' => array('in', $task_ids));
		$condition[] = array('send_state' => array('eq', 1));
		$condition[] = array('send_time' => array('eq', 0));
		$sql = 'SELECT id FROM ' . $this->table('send_list') . $this->getWhere($condition) . ' ORDER BY `id` ASC ' . $this->getLimit(1, $count);
		$ids = $this->fetchCol($sql);

		// 给任务打上标记
		$condition = array();
		$condition[] = array('id' => array('in', $ids));
		$condition[] = array('send_state' => array('eq', 1));
		$condition[] = array('send_time' => array('eq', 0));
		$data = array();
		$data['send_state'] = 2;
		$data['send_time'] = $nowstamp;
		$this->update($condition, $data, 'send_list');

		// 获取最终要发送的任务
		$condition = array();
		$condition[] = array('s.`id`' => array('in', $ids));
		$condition[] = array('s.`send_state`' => array('eq', 2));
		$condition[] = array('s.`send_time`' => array('eq', $nowstamp));
		$sql = 'SELECT s.`id`, t.`contact`, s.`data`, tm.`title`, tm.`content` FROM ' . $this->table('send_list') . ' AS s JOIN ' . $this->table('task_multi') . ' AS tm ON s.`task_id` = tm.`id` JOIN ' . $this->table('target') . ' AS t ON t.`id` = s.`target_id` ' . $this->getWhere($condition);
		$list = $this->fetchRows($sql);
		foreach($list as $k => $v)
		{
			$data = json_decode($list[$k]['data'], true);
			$list[$k]['title'] = $this->render($list[$k]['title'], $data);
			$list[$k]['content'] = $this->render($list[$k]['content'], $data);
		}

		// 添加PV图标
		if($channel['type'] == CHANNEL_TYPE_EMAIL)
		{
			foreach($list as $k => $v)
			{
				$list[$k]['content'] .= '<img src="' . DOMAIN_OUTSIDE . 'index.php?a=read&m=pv&id=' . $v['id'] . '&ms=multi" />';
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
			$this->update($condition, $data, 'send_list');
		}
	}

	public function taskReflash($channel)
	{
		// 通道是否被禁用
		$condition = array();
		$condition[] = array('id' => array('eq', $channel));
		$channel = $this->getObject($condition, array(), 'channel');
		if(!$channel || $channel['is_disable'])
		{
			return;
		}

		// 获取通道任务
		$condition = array();
		$condition[] = array('channel_id' => array('eq', $channel['id']));
		$condition[] = array('send_state' => array('eq', 2));
		$condition[] = array(
			'start_time' => array('gt', time() - TASK_SCAN_RANGE),
			'plan_send_time' => array('gt', time() - TASK_SCAN_RANGE),
			'create_time' => array('gt', time() - TASK_SCAN_RANGE),
		);
		$task_ids = $this->getCol($condition, 'id');

		// 超时发送任务标记失败
		$condition = array();
		$condition[] = array('task_id' => array('in', $task_ids));
		$condition[] = array('send_state' => array('eq', 2));
		$condition[] = array('send_time' => array('lt', time() - TASK_TIMEOUT));
		$data = array();
		$data['send_state'] = 5;
		$this->update($condition, $data, 'send_list');

		// 更新进度
		$sql = 'SELECT task_id, COUNT(*) FROM ' . $this->table('send_list') . ' WHERE `task_id` IN (' . implode(', ', $task_ids) . ') AND `send_state` != 1 GROUP BY `task_id`';
		$task = $this->fetchPairs($sql);
		foreach($task as $k => $v)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $k));
			$data = array();
			$data['send_rate'] = $v;
			$this->update($condition, $data);

		}

		// 发送完成
		// 更新状态，结束时间
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = 3, end_time = ' . time() . ' WHERE `send_state` = 2 AND `send_count` = `send_rate`';
		$this->query($sql);

		// 到预订发送时间
		$condition = array();
		$condition[] = array('channel_id' => array('eq', $channel['id']));
		$condition[] = array('check_user_id' => array('gt', 0));
		$condition[] = array('plan_send_time' => array('lte', time()));
		$condition[] = array('send_state' => array('eq', 1));
		$data = array();
		$data['start_time'] = time();
		$data['send_state'] = 2;
		$this->update($condition, $data);

		// 更新通道最后运行时间
		$condition = array();
		$condition[] = array('id' => array('eq', $channel['id']));
		$data = array();
		$data['last_run'] = time();
		$this->update($condition, $data, 'channel');
	}
}
