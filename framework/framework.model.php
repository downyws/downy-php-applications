<?php

class Model extends Db
{
	protected $_prefix = '';
	protected $_table = null;

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
		return '`' . $this->_prefix . $table . '`';
	}

	public function insert($data, $table = '')
	{
		$sql = 'DESC ' . $this->table($table);
		$desc = array_flip($this->fetchCol($sql));
		foreach($data as $k => $v)
		{
			if(!isset($desc[$k]))
			{
				unset($data[$k]);
			}
			else
			{
				$data[$k] = '"' . $this->escape($v) . '"';
			}
		}

		$sql = 'INSERT INTO ' . $this->table($table) . ' (`' . join('`, `', array_keys($data)) . '`) VALUES (' . join(', ', $data) . ')';
		$res = $this->query($sql);

		if($res !== false)
		{
			$insert_id = $this->insertId();
			return $insert_id;
		}
		return false;
	}

	public function insertBatch($fields, $data, $table = '')
	{
		foreach($data as $k => $v)
		{
			$data[$k] = array();
			foreach($fields as $v1)
			{
				if(isset($v[$v1]))
				{
					$data[$k][$v1] = '"' . $this->escape($v[$v1]) . '"';
				}
				else
				{
					$data[$k][$v1] = '""';
				}
			}
			$data[$k] = '(' . join(', ', $data[$k]) . ')';
		}

		$sql = 'INSERT IGNORE INTO ' . $this->table($table) . ' (' . join(', ', $fields) . ') VALUES ' . join(', ', $data);
		$res = $this->query($sql);

		if($res !== false)
		{
			return $this->affectedRows();
		}
		return false;
	}

	public function update($condition, $data, $table = '')
	{
		$sql = 'DESC ' . $this->table($table);
		$desc = array_flip($this->fetchCol($sql));
		foreach($data as $k => $v)
		{
			if(!isset($desc[$k]))
			{
				unset($data[$k]);
			}
			else
			{
				$data[$k] = '`' . $k . '` = "' . $this->escape($v) . '"';
			}
		}

		$sql = 'UPDATE ' . $this->table($table) . ' SET ' . join(', ', $data) . $this->getWhere($condition);
		$res = $this->query($sql);

		if($res !== false)
		{
			return $this->affectedRows();
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
		$sql = 'SHOW TABLE STATUS LIKE \'' . $this->_prefix . $table . '\'';
		$result = $this->fetchArray($sql);
		return $result['Auto_increment'];
	}

	public function getOne($condition, $field, $table = '')
	{
		$sql = 'SELECT ' . $field . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchOne($sql);
	}

	public function getObject($condition, $fields = array(), $table = '')
	{
		$sql = 'SELECT ' . (empty($fields) ? '*' : join(', ', $fields)) . ' FROM ' . $this->table($table) . $this->getWhere($condition) . ' LIMIT 1 ';
		return $this->fetchArray($sql);
	}

	public function getObjects($condition, $fields = array(), $table = '')
	{
		$sql = 'SELECT ' . (empty($fields) ? '*' : join(', ', $fields)) . ' FROM ' . $this->table($table) . $this->getWhere($condition);
		return $this->fetchAll($sql);
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
					case 'eq': $_item[] = '(' . $_k . ' = \'' . $this->escape($_v[1]) . '\')'; break;
					case 'neq': $_item[] = '(' . $_k . ' != \'' . $this->escape($_v[1]) . '\')'; break;
					case 'gt': $_item[] = '(' . $_k . ' > \'' . $this->escape($_v[1]) . '\')'; break;
					case 'egt': $_item[] = '(' . $_k . ' >= \'' . $this->escape($_v[1]) . '\')'; break;
					case 'lt': $_item[] = '(' . $_k . ' < \'' . $this->escape($_v[1]) . '\')'; break;
					case 'elt': $_item[] = '(' . $_k . ' <= \'' . $this->escape($_v[1]) . '\')'; break;
					case 'like': $_item[] = '(' . $_k . ' LIKE \'%' . $this->escape($_v[1]) . '%\')'; break;
					case 'between': $_item[] = '(' . $_k . ' BETWEEN \'' . $this->escape($_v[1][0]) . '\' AND \'' . $this->escape($_v[1][1]) . '\')'; break;
					case 'not between': $_item[] = '(' . $_k . ' NOT BETWEEN \'' . $this->escape($_v[1][0]) . '\' AND \'' . $this->escape($_v[1][1]) . '\')'; break;
					case 'in': 
						$temp = array();
						foreach($_v[1] as $__v) $temp[] = $this->escape($__v);
						$_item[] = '(' . $_k . ' IN (\'' . implode('\',\'', $temp) . '\'))'; break;
					case 'not in': 
						$temp = array();
						foreach($_v[1] as $__v) $temp[] = $this->escape($__v);
						$_item[] = '(' . $_k . ' NOT IN (\'' . implode('\',\'', $temp) . '\'))'; break;
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
}
