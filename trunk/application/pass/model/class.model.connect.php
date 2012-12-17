<?php
class ModelConnect extends ModelCommon
{
	public $_table = 'connect';

	public function __construct()
	{
		parent::__construct();
	}

	public function getAllPairs($field_key, $field_value, $status, $cache = false)
	{
		// »ñÈ¡»º´æ
		if($cache)
		{
			$cache_key = 'default/' . md5('connect_get_all_pairs' . $field_key . $field_value . $status);
			$filecache = new Filecache();
			$result = $filecache->get($cache_key);
			if($result)
			{
				return $result;
			}
		}

		// ²éÑ¯
		$condition = array();
		if(isset($status))
		{
			$condition[] = array('`status`' => array('eq', $status));
		}
		$sql = 'SELECT `' . $field_key . '`, `' . $field_value . '` FROM ' . $this->table() . $this->getWhere($condition) . ' ORDER BY `sort_order` DESC';
		$result = $this->fetchPairs($sql);

		// ±£´æ»º´æ
		if($cache && $result)
		{
			$filecache->set($cache_key, $result);
		}

		return $result;
	}
}
