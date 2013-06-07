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

	public function getSetting()
	{
		$sql = ' SELECT * FROM ' . $this->table() . ' WHERE id < 0 ORDER BY path ASC ';
		$data = $this->fetchRows($sql);

		$result = array('count' => count($data), 'data' => $data, 'pager' => null);
		return $result;
	}

	public function getList()
	{
		$sql = ' SELECT * FROM ' . $this->table() . ' WHERE id > 0 ORDER BY keyword ASC, path ASC ';
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
			$result['message'] = '地址不能为空。';
			return $result;
		}
		else if($object['id'] == 0)
		{
			if(empty($object['keyword']))
			{
				$result['message'] = '关键字不能为空。';
				return $result;
			}

			// 唯一检查
			$condition = array();
			$condition[] = array('path' => array('eq', $object['path']));
			if($this->getOne($condition, 'id'))
			{
			//	$result['message'] = '路径已经存在。';
			//	return $result;
			}
		}
		else if($object['id'] < 0)
		{
			$result['message'] = '对象编号错误。';
			return $result;
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

		$result['message'] = $result['state'] ? '保存成功。' : '保存出错。';

		return $result;
	}

	public function saveSetting($object)
	{
		$result = array('state' => false);

		if($object['id'] == 0)
		{
			// 唯一检查
			$condition = array();
			$condition[] = array('path' => array('eq', $object['path']));
			if($this->getOne($condition, 'id'))
			{
			//	$result['message'] = '键存在。';
			//	return $result;
			}
		}

		if($object['id'] < 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			$object = array('url' => $object['url']);
			$result['state'] = $this->update($condition, $object);
		}
		else
		{
			$sql = 'SELECT MIN(id) FROM ' . $this->table();
			$id = $this->fetchOne($sql) - 1;
			$id = ($id >= 0) ? -1 : $id;
			$object = array
			(
				'id' => $id,
				'keyword' => '',
				'path' => $object['path'],
				'url' => $object['url'],
				'update_time' => 0
			);
			$result['state'] = $this->insert($object);
			$result['state'] = $result['state'] ? $id : $result['state'];
		}

		$result['message'] = $result['state'] ? '保存成功。' : '保存失败。';

		return $result;
	}
}
