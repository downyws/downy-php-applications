<?php
class ModelAutoUpdate extends ModelCommon
{
	public $_table = 'auto_update';

	public function __construct()
	{
		parent::__construct();
	}
	
	public function getById($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$object = $this->getObject($condition, array());

		return $object;
	}

	public function getList()
	{
		$sql = ' SELECT * FROM ' . $this->table() . ' ORDER BY keyword ASC, path ASC ';
		$data = $this->fetchRows($sql);

		$result = array('count' => count($data), 'data' => $data, 'pager' => null);
		return $result;
	}

	public function remove($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		return $this->delete($condition);
	}

	public function remoteUpd($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		return $this->update($condition, array('update_time' => time()));
	}

	public function save($object)
	{
		$result = array('state' => false);

		if(empty($object['url']))
		{
			$result['message'] = 'Url can not empty.';
			return $result;
		}
		else if($object['id'] < 1)
		{
			if(empty($object['keyword']))
			{
				$result['message'] = 'Keyword can not empty.';
				return $result;
			}

			// Î¨Ò»¼ì²é
			$condition = array();
			$condition[] = array('path' => array('eq', $object['path']));
			if($this->getOne($condition, 'id'))
			{
				$result['message'] = 'Path exists.';
				return $result;
			}
		}

		if($object['id'] > 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			$object = array('url' => $object['url'], 'update_time' => time());
			$result['state'] = $this->update($condition, $object);
		}
		else
		{
			unset($object['id']);
			$object['update_time'] = time();
			$result['state'] = $this->insert($object);
		}

		$result['message'] = $result['state'] ? 'Save success.' : 'Save error.';

		return $result;
	}
}
