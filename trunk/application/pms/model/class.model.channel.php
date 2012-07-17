<?php
class ModelChannel extends ModelCommon
{
	public $_table = 'channel';

	public function __construct()
	{
		parent::__construct();
	}

	public function getAllPairs()
	{
		$sql = 'SELECT id, name FROM ' . $this->table();
		return $this->fetchPairs($sql);
	}

	public function getAll()
	{
		$data = $this->getObjects(array());
		$count = $this->getOne(array(), 'COUNT(*)');
		return array('count' => $count, 'data' => $data, 'pager' => array());
	}

	public function formatData($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['type'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$list[$k]['type']];
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
		}
		return $list;
	}
}
