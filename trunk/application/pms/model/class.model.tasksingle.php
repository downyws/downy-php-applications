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
		$sql = 'SELECT ts.*, u.account, t.contact, c.name AS channel_name FROM ' . $this->table() . ' AS ts JOIN ' . $this->table('user') . ' AS u ON (u.`id` = ts.`user_id`) JOIN ' . $this->table('target') . ' AS t ON (t.`id` = ts.`target_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = ts.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY ts.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatData($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['send_state_format'] = $GLOBALS['CONFIG']['TASKSINGLE']['STATE'][$list[$k]['send_state']];
		}
		return $list;
	}
}
