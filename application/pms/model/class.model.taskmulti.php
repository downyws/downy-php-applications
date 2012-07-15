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
		!empty($params['title']) && $condition[] = array('tm.`title`' => array('like', $params['title']));
		!empty($params['channel']) && $condition[] = array('tm.`channel_id`' => array('eq', $params['channel']));
		!empty($params['send_state']) && $condition[] = array('tm.`send_state`' => array('eq', $params['send_state']));
		!empty($params['start_time']) && $condition[] = array('tm.`send_time`' => array('egt', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('tm.`send_time`' => array('elt', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT tm.*, u.account, c.name AS channel_name FROM ' . $this->table() . ' AS tm JOIN ' . $this->table('user') . ' AS u ON (u.`id` = tm.`user_id`) JOIN ' . $this->table('channel') . ' AS c ON (c.`id` = tm.`channel_id`) ' . $this->getWhere($condition) . ' ORDER BY tm.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatData($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['send_state_format'] = $GLOBALS['CONFIG']['TASKMULTI']['STATE'][$list[$k]['send_state']];
		}
		return $list;
	}
}
