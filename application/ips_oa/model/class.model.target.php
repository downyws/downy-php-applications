<?php
class ModelTarget extends ModelCommon
{
	public $_table = 'target';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($p, $params, $ps = APP_PAGER_SIZE)
	{
		$condition = array();
		!empty($params['contact']) && $condition[] = array('`contact`' => array('like', $params['contact']));
		!empty($params['type']) && $condition[] = array('`type`' => array('eq', $params['type']));
		in_array($params['is_disable'], array(0, 1)) && $condition[] = array('`is_disable`' => array('eq', $params['is_disable']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT * FROM ' . $this->table() . $this->getWhere($condition) . ' ORDER BY `contact` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchRows($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$list[$k]['is_disable']];
			$list[$k]['type_format'] = $GLOBALS['CONFIG']['TARGET']['TYPE'][$list[$k]['type']];
		}
		return $list;
	}

	public function formatObject($data)
	{
		$data['type_format'] = $GLOBALS['CONFIG']['TARGET']['TYPE'][$data['type']];
		$data['is_disable_format'] = $GLOBALS['CONFIG']['IS_DISABLE'][$data['is_disable']];
		return $data;
	}

	public function add($data)
	{
		// 目标地址
		$data['contact'] = trim($data['contact']);
		$data['type'] = $this->contactType($data['contact']);
		if(!in_array($data['type'], array(CHANNEL_TYPE_EMAIL, CHANNEL_TYPE_SMS)))
		{
			return array('state' => false, 'message' => '目标地址错误。');
		}

		// 是否已经存在
		$condition = array();
		$condition[] = array('contact' => array('eq', $data['contact']));
		$count = $this->getOne($condition, 'COUNT(*)');
		if($count > 0)
		{
			return array('state' => false, 'message' => '目标地址已经存在。');
		}

		// 状态
		$data['is_disable'] = intval($data['is_disable']) > 0 ? 1 : 0;

		$state = $this->insert($data);
		$state && $this->record($state, LOG_DATA_TABLE_TARGET, LOG_OPERATION_TYPE_INSERT);

		$message = $state ? $state : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function addBatch($contacts, $type)
	{
		$data = array();
		foreach($contacts as $v)
		{
			if($this->contactType($v) == $type)
			{
				$data[] = array('contact' => $v, 'type' => $type);
			}
		}
		$this->insertBatch(array('contact', 'type'), $data);
		$this->record(0, LOG_DATA_TABLE_TARGET, LOG_OPERATION_TYPE_INSERT);

		$condition = array();
		$condition[] = array('contact' => array('in', $contacts));
		$condition[] = array('is_disable' => array('eq', 0));
		$condition[] = array('type' => array('eq', $type));
		return $this->getPairs($condition, array('contact', 'id'));
	}

	public function edit($id, $data)
	{
		foreach($data as $k => $v)
		{
			switch($k)
			{
				case 'is_disable':
					$data[$k] = intval($data[$k]) > 0 ? 1 : 0;
					break;
				default:
					unset($data[$k]);
			}
		}

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = $this->update($condition, $data);
		$state = ($state !== false);
		$state && $this->record($id, LOG_DATA_TABLE_TARGET, LOG_OPERATION_TYPE_UPDATE);

		$message = $state ? '保存成功。' : '保存失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function contactToId($contact)
	{
		$condition = array();
		$condition[] = array('contact' => array('eq', $contact));
		$id = $this->getOne($condition, 'id');
		if($id < 1)
		{
			$type = $this->contactType($contact);
			if($type > 0)
			{
				$id = $this->insert(array('contact' => $contact, 'type' => $type, 'is_disable' => 0));
			}
		}
		return $id;
	}

	public function contactType($contact)
	{
		if(preg_match('/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/', $contact))
		{
			return 1;
		}
		else if(preg_match('/^1[358]\d{9}$/', $contact))
		{
			return 2;
		}
		return 0;
	}

	public function disableById($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		return $this->getOne($condition, 'is_disable');
	}
}
