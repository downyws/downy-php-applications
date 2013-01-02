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

	public function getId($name)
	{
		$list = $this->getAllPairs('key', 'id', null, true);
		if(array_key_exists($name, $list))
		{
			return $list[$name];
		}
		return false;
	}

	public function login($connect_id, $outer_id)
	{
		$err = array();
		$condition = array();
		$condition[] = array('outer_id' => array('eq', $outer_id));
		$condition[] = array('connect_id' => array('eq', $connect_id));
		$object = $this->getObject($condition, null, 'member_connect');
		if($object)
		{
			$memberObj = Factory::getModel('member');
			switch($object['status'])
			{
				case STATUS_DEFAULT:
					if($memberObj->login($object['member_id'], 'id'))
					{
						return true;
					}
					$err = $memberObj->getError();
					break;
				case STATUS_DISABEL: $err[] = CONNECT_LOGIN_DISABEL; break;
				default: $err[] = CONNECT_LOGIN_UNKNOWSTATUS; break;
			}
		}
		else
		{
			$err[] = CONNECT_LOGIN_NOTEXISTS;
		}
		$this->_error[] = $err;
		return false;
	}
}
