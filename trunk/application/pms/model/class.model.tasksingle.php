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

	public function cancel($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('send_state' => array('eq', 1));
		$state = $this->update($condition, array('send_state' => 4));
		if($state)
		{
			$userObj = Factory::getModel('user');
			$user = $userObj->getUser();
			$this->record($user['id'], $id, LOG_DATA_TABLE_TASKSINGLE, LOG_OPERATION_TYPE_UPDATE);
		}
		return array('state' => $state, 'message' => ($state ? '取消成功。' : '取消失败。'));
	}
}
