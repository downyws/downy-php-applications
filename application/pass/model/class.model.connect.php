<?php
class ModelConnect extends ModelCommon
{
	public $_table = 'connect';

	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Model', 'define.model.', __CLASS__) . '.php');
	}

	public function getAllPairs($field_key, $field_value, $status, $cache = false)
	{
		// 获取缓存
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

		// 查询
		$condition = array();
		if(isset($status))
		{
			$condition[] = array('`status`' => array('eq', $status));
		}
		$sql = 'SELECT `' . $field_key . '`, `' . $field_value . '` FROM ' . $this->table() . $this->getWhere($condition) . ' ORDER BY `sort_order` DESC';
		$result = $this->fetchPairs($sql);

		// 保存缓存
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
				case STATUS_DISABEL: $err[] = MCGetC('MCOT_ACCOUNT_DISABLE'); break;
				default: $err[] = MCGetC('MCOT_ACCOUNT_ERRSTA_TELA'); break;
			}
		}
		else
		{
			$err[] = MCGetC('MCOT_ACCOUNT_NOEXIST');
		}
		$this->_error[] = $err;
		return false;
	}
}
