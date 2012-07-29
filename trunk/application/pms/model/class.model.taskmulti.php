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
		!empty($params['start_time']) && $condition[] = array('tm.`plan_send_time`' => array('egt', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('tm.`plan_send_time`' => array('elt', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT tm.*, u.account, c.name AS channel_name FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = tm.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY tm.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
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
		// 任务名称检查
		$data['name'] = trim($data['name']);
		if(empty($data['name']))
		{
			return array('state' => false, 'message' => '任务名称不能为空。');
		}

		// 通道检查
		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $data['channel_id']))));
		if(!$channel)
		{
			return array('state' => false, 'message' => '请选择通道。');
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
		$targetObj = Factory::getModel('target');
		$taskmulti = $this->getObject(array(array('id' => array('eq', $id))));
		$channel = $this->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))), array(), 'channel');

		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'name':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						return array('state' => false, 'message' => '任务名称不能为空。');
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

	public function cancel($id)
	{
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('send_state' => array('in', array(1, 2, 5, 6)));
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = 4, `remarks` = CONCAT(\'' . date('Y-m-d H:i:s') . '\\t' . $user['id'] . '\\t' . $user['account'] . '\\tcanceled\\n\', `remarks`)' . $this->getWhere($condition);
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
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = ' . intval($pass) . ', `remarks` = CONCAT(\'' . date('Y-m-d H:i:s') . '\\t' . $user['id'] . '\\t' . $user['account'] . '\\t' . $pass_message . '\\n\', `remarks`)' . $this->getWhere($condition);
		$state = $this->query($sql);

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
}
