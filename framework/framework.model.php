<?php

class Model extends Db
{
	protected $_prefix = '';
	protected $_table = null;
	protected $_fields = array();
	protected $_trans = array('sqls' => array(), 'errors' => array());

	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->_prefix = $this->_config['PREFIX'];
	}

	public function table($table = '')
	{
		if($table == '')
		{
			$table = $this->_table;
		}
		return (strpos($table, '`') === false) ? ('`' . $this->_prefix . $table . '`') : $table;
	}

	public function fieldFilter($table, $datas, $fields = array())
	{
		if(empty($this->_fields[$table]))
		{
			$sql = 'DESC ' . $table;
			$this->_fields[$table] = array_flip($this->fetchCol($sql));
		}

		foreach($datas as $k => $v)
		{
			foreach($v as $_k => $_v)
			{
				if(isset($this->_fields[$table][$_k]))
				{
					$datas[$k][$_k] = is_numeric($_v) ? $_v : $this->escape($_v);
				}
				else
				{
					unset($datas[$k][$_k]);
				}
			}
		}
		return $datas;
	}

	public function insert($data, $table = '', $ignore = false)
	{
		$table = $this->table($table);

		$data = $this->fieldFilter($table, array($data));
		$data = $data[0];

		$sql = ' INSERT ' . ($ignore ? ' IGNORE ' : '') . ' INTO ' . $table . ' (`' . implode('`, `', array_keys($data)) . '`) VALUES ("' . implode('", "', $data) . '")';
		$res = $this->query($sql);

		if($res !== false)
		{
			$insert_id = $this->insertId();
			return $insert_id;
		}
		return false;
	}

	public function insertBatch($fields, $datas, $table = '', $ignore = false)
	{
		$table = $this->table($table);

		$datas = $this->fieldFilter($table, $datas, $fields);

		$batch = array(); $i = 0; $item; $b = 0; $byte = 0;
		foreach($datas as $k => $v)
		{
			$item = '("' . implode('", "', $datas[$k]) . '")';
			$byte += $b = strlen($item);
			if($byte > $this->_config['QUERY_LIMIT_BYTE'])
			{
				$byte = $b;
				$i++;
			}
			$batch[$i] = $item;
		}

		$sql = 'INSERT ' . ($ignore ? ' IGNORE ' : '') . ' INTO ' . $table . ' (`' . implode('`, `', $fields) . '`) VALUES ';
		foreach($batch as $v)
		{
			$res = $this->query($sql . implode(', ', $v));
			if($res === false)
			{
				return false;
			}
		}
		return $this->affectedRows();
	}

	public function update($condition, $data, $table = '')
	{
		$table = $this->table($table);

		$data = $this->fieldFilter($table, array($data));
		$data = $data[0];
		foreach($data as $k => $v)
		{
			$data[$k] = '`' . $k . '` = "' . $this->escape($v) . '"';
		}

		$sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data) . $this->getWhere($condition);
		$res = $this->query($sql);

		if($res !== false)
		{
			return $this->affectedRows();
		}
		return false;
	}

	public function insertOrUpdate($condition, $data, $table = '')
	{
		$table = $this->table($table);

		$data = $this->fieldFilter($table, array($data));
		$data = $data[0];

		$object = $this->getObject($condition, array(), $table);
		if(!!$object)
		{
			$sql = 'UPDATE ' . $table .  ' SET ' . implode(', ', $data) . $this->getWhere($condition);
			if($this->query($sql) !== false)
			{
				return isset($object['id']) ? $object['id'] : !!$object;
			}
		}
		else
		{
			$sql = 'INSERT INTO ' . $table .  ' (`' . implode('`, `', array_keys($data)) . '`) VALUES ("' . implode('", "', $data) . '")';
			if($this->query($sql) !== false)
			{
				return $this->insertId();
			}
		}
		return false;
	}

	public function delete($condition, $field = '', $table = '')
	{
		$sql = 'DELETE FROM ' . $this->table($table) . $this->getWhere($condition);
		$res = $this->query($sql);

		if($res !== false)
		{
			return $this->affectedRows();
		}
		return false;
	}

	public function getNextId($table = '')
	{
		$table = ($table == '') ? $this->_table : $table;
		$sql = 'SHOW TABLE STATUS LIKE "' . $this->_prefix . $table . '"';
		$result = $this->fetchRow($sql);
		return $result['Auto_increment'];
	}

	public function getOne($condition, $field, $table = '')
	{
		$sql = 'SELECT ' . $field . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchOne($sql);
	}

	public function getObject($condition, $fields = array(), $table = '')
	{
		$sql = 'SELECT ' . (empty($fields) ? '*' : implode(', ', $fields)) . ' FROM ' . $this->table($table) . $this->getWhere($condition) . ' LIMIT 1 ';
		return $this->fetchRow($sql);
	}

	public function getObjects($condition, $fields = array(), $table = '')
	{
		$sql = 'SELECT ' . (empty($fields) ? '*' : implode(', ', $fields)) . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchRows($sql);
	}

	public function getCol($condition, $field, $table ='')
	{
		$sql = 'SELECT ' . $field . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchCol($sql);
	}

	public function getPairs($condition, $fields, $table ='')
	{
		$sql = 'SELECT ' . $fields[0] . ', ' . $fields[1] . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchPairs($sql);
	}

	public function getWhere($condition)
	{
		$result = array();
		if(!is_array($condition))
		{
			return '';
		}
		foreach($condition as $v)
		{
			$_item = array();
			foreach($v as $_k => $_v)
			{
				switch($_v[0])
				{
					case 'and': $_item[] = '(' . $_k . ' & "' . $this->escape($_v[1]) . '")'; break;
					case 'eq': $_item[] = '(' . $_k . ' = "' . $this->escape($_v[1]) . '")'; break;
					case 'neq': $_item[] = '(' . $_k . ' != "' . $this->escape($_v[1]) . '")'; break;
					case 'gt': $_item[] = '(' . $_k . ' > "' . $this->escape($_v[1]) . '")'; break;
					case 'gte': $_item[] = '(' . $_k . ' >= "' . $this->escape($_v[1]) . '")'; break;
					case 'lt': $_item[] = '(' . $_k . ' < "' . $this->escape($_v[1]) . '")'; break;
					case 'lte': $_item[] = '(' . $_k . ' <= "' . $this->escape($_v[1]) . '")'; break;
					case 'like': $_item[] = '(' . $_k . ' LIKE "%' . $this->escape($_v[1]) . '%")'; break;
					case 'between': $_item[] = '(' . $_k . ' BETWEEN "' . $this->escape($_v[1][0]) . '" AND "' . $this->escape($_v[1][1]) . '")'; break;
					case 'not between': $_item[] = '(' . $_k . ' NOT BETWEEN "' . $this->escape($_v[1][0]) . '" AND "' . $this->escape($_v[1][1]) . '")'; break;
					case 'in': 
						$temp = array();
						foreach($_v[1] as $__v) $temp[] = $this->escape($__v);
						$_item[] = '(' . $_k . ' IN ("' . implode('","', $temp) . '"))'; break;
					case 'not in': 
						$temp = array();
						foreach($_v[1] as $__v) $temp[] = $this->escape($__v);
						$_item[] = '(' . $_k . ' NOT IN ("' . implode('","', $temp) . '"))'; break;
					case 'exp': $_item[] = '(' . $_v[1] . ')'; break;
				}
			}
			$result[] = implode(' OR ', $_item);
		}
		$result = implode(' ) AND ( ', $result);
		return empty($result) ? '' : ' WHERE ( ' . $result . ' )';
	}

	public function getLimit($p, $ps)
	{
		return ' LIMIT ' . ($p - 1) * $ps . ', ' . $ps;
	}

	public function getPager($p, $count, $ps = APP_PAGEE_SIZE, $pc = APP_PAGER_COUNT)
	{
		$result = array();
		$result['total'] = $count;
		$result['first'] = 1;
		$result['last'] = intval(ceil($count / $ps));
		$result['current'] = ($p > $result['last'] ? $result['last'] : $p);
		$result['current'] = $result['current'] < 1 ? 1 : $result['current'];
		$result['prev'] = ($result['current'] > 1 ? $result['current'] - 1 : 1);
		$result['next'] = ($result['current'] >= $result['last'] ? $result['last'] : $result['current'] + 1);
		if($result['current'] <= intval($pc / 2))
		{
			$result['start'] = 1;
		}
		else
		{
			$result['start'] = $result['current'] - intval($pc / 2);
		}
		if($result['start'] + $pc - 1 > $result['last'])
		{
			$result['end'] = $result['last'];
			if($result['start'] > 1)
			{
				$result['start'] = $result['end'] - $pc + 1 > 1 ? $result['end'] - $pc + 1 : 1;
			}
		}
		else
		{
			$result['end'] = $result['start'] + $pc - 1;
		}
		return $result;
	}

	public function keysDisable($table = '')
	{
		$sql = ' ALTER TABLE ' . $this->table($table) . ' DISABLE KEYS ';
		$this->query($sql);
	}

	public function keysEnable($table = '')
	{
		$sql = ' ALTER TABLE ' . $this->table($table) . ' ENABLE KEYS ';
		$this->query($sql);
	}

	public function transAfterTrigger($sql)
	{
		$this->_trans['sqls'][] = $sql;
	}

	public function transErrorTrigger()
	{
		$this->_trans['errors'][] = func_get_args();
	}

	public function transStart($isolation = '')
	{
		$this->_trans['sqls'] = array();
		$this->_trans['errors'] = array();

		$this->addTrigger('after', array($this, 'transAfterTrigger'));
		$this->addTrigger('error', array($this, 'transErrorTrigger'));

		if(!empty($isolation))
		{
			$sql = ' SET TRANSACTION ISOLATION LEVEL ' . $isolation;
			$this->query($sql);
		}

		$sql = ' START TRANSACTION ';
		$this->query($sql);
	}

	public function transCommit()
	{
		if($this->_trans['errors'])
		{
			$sql = ' ROLLBACK ';
			$logs_file = $this->_logs->attachment('query', array('sqls' => $this->_trans['sqls'], 'errors' => $this->_trans['errors']));
			$this->_logs->message('query', 'Error: Transaction rollbacked, see ' . $logs_file);
			$res = false;
		}
		else
		{
			$sql = ' COMMIT ';
			$res = true;
		}

		$this->query($sql);

		$this->removeTrigger('after', array($this, 'transAfterTrigger'));
		$this->removeTrigger('error', array($this, 'transErrorTrigger'));

		return $res;
	}

	public function transRollback()
	{
		$sql = ' ROLLBACK ';
		$this->query($sql);

		$this->removeTrigger('after', array($this, 'transAfterTrigger'));
		$this->removeTrigger('error', array($this, 'transErrorTrigger'));
	}
}
