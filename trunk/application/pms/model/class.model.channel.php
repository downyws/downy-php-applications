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

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['type_format'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$list[$k]['type']];
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$data['type_format'] = $GLOBALS['CONFIG']['CHANNEL']['TYPE'][$data['type']];
		$data['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$data['is_disable']];
		return $data;
	}

	public function add($data)
	{
		// 通道名称
		$data['name'] = trim($data['name']);
		if(empty($data['name']))
		{
			return array('state' => false, 'message' => '通道名称不能为空。');
		}

		// 通道类型
		if(!in_array($data['type'], array_keys($GLOBALS['CONFIG']['CHANNEL']['TYPE'])))
		{
			return array('state' => false, 'message' => '类型错误。');
		}

		// 是否禁用
		$data['is_disable'] = intval($data['is_disable']) > 0 ? 1 : 0;

		$state = parent::insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_CHANNEL, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function edit($id, $data)
	{
		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'name':
					$data[$k] = trim($v);
					if(empty($data[$k]))
					{
						return array('state' => false, 'message' => '通道名称不能为空。');
					}
					break;
				case 'is_disable':
					$data[$k] = intval($data[$k]) > 0 ? 1 : 0;
					break;
				case 'type':
					if(!in_array($data[$k], array_keys($GLOBALS['CONFIG']['CHANNEL']['TYPE'])))
					{
						return array('state' => false, 'message' => '类型错误。');
					}
					break;
				default:
					unset($data[$k]);
			}
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = parent::update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_CHANNEL, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}
}
