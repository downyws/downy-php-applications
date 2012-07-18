<?php
class ModelLog extends ModelCommon
{
	public $_table = 'log';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		!empty($params['account']) && $condition[] = array('u.`account`' => array('like', $params['account']));
		!empty($params['data_table']) && $condition[] = array('l.`data_table`' => array('eq', $params['data_table']));
		!empty($params['operation_type']) && $condition[] = array('l.`operation_type`' => array('eq', $params['operation_type']));
		!empty($params['start_time']) && $condition[] = array('l.`create_time`' => array('egt', $params['start_time']));
		!empty($params['end_time']) && $condition[] = array('l.`create_time`' => array('elt', $params['end_time']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . 'AS l JOIN ' . $this->table('user') . ' AS u ON (u.`id` = l.`user_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT l.*, u.account FROM ' . $this->table() . ' AS l JOIN ' . $this->table('user') . ' AS u ON (u.`id` = l.`user_id`) ' . $this->getWhere($condition) . ' ORDER BY l.`create_time` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);
		
		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['data_table_format'] = $GLOBALS['CONFIG']['LOG']['DATA_TABLE'][$list[$k]['data_table']];
			$list[$k]['operation_type_format'] = $GLOBALS['CONFIG']['LOG']['OPERATION_TYPE'][$list[$k]['operation_type']];
		}
		return $list;
	}
}
