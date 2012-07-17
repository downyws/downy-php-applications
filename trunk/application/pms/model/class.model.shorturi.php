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

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . 'AS su LEFT JOIN ' . $this->table('task_multi') . ' AS tm ON (tm.`id` = su.`task_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT su.*, IF(tm.title IS NULL, \'\', tm.title) AS task_title FROM ' . $this->table() . ' AS su LEFT JOIN ' . $this->table('task_multi') . ' AS tm ON (tm.`id` = su.`task_id`) ' . $this->getWhere($condition) . ' ORDER BY su.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatData($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['suri'] = $GLOBALS['CONFIG']['SHORT_URI']['DOMAIN'] . $list[$k]['key'];
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
		}
		return $list;
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
