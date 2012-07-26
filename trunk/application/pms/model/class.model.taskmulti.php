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
		$userObj = Factory::getModel('user');
		$user = $userObj->getUser();

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('send_state' => array('eq', 6));
		$sql = 'UPDATE ' . $this->table('') . ' SET `send_state` = ' . ($pass ? 1 : 5) . ', `remarks` = CONCAT(\'' . date('Y-m-d H:i:s') . '\\t' . $user['id'] . '\\t' . $user['account'] . '\\t' . ($pass ? 'check passed' : 'check not passed') . '\\n\', `remarks`)' . $this->getWhere($condition);
		$state = $this->query($sql);
		$state && $this->record($id, LOG_DATA_TABLE_TASKMULTI, LOG_OPERATION_TYPE_UPDATE);

		return array('state' => $state, 'message' => ($state ? '审核成功。' : '审核失败。'));
	}
}
