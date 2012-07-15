<?php

class Db
{
	static $dbs = array();
	private $db = null;
	protected $_config = array();

	public function __construct($config)
	{
		$this->_config = $config;
	}

	protected function _connect()
	{
		$ping = $this->db ? mysql_ping($this->db) : false;

		if(!$this->db || !$ping)
		{
			$key = md5(serialize($this->_config));
			if(empty(db::$dbs[$key]) || !$ping)
			{
				$this->db = mysql_connect($this->_config['HOST'], $this->_config['USERNAME'], $this->_config['PASSWORD'], true);
				if($this->_config['CHARSET'])
				{
					mysql_query('SET NAMES utf8;', $this->db);
				}
				mysql_select_db($this->_config['DBNAME'], $this->db);

				db::$dbs[$key] = $this->db;

				return;
			}
			else
			{
				$this->db = db::$dbs[$key];
			}
		}
	}

	public function query($sql)
	{
		$this->_connect();
		$res = mysql_unbuffered_query($sql, $this->db);
		return $res;
	}

	public function &fetchRow($sql)
	{
		$data = false;
		$res = $this->query($sql);
		if($res !== false)
		{
			$data = mysql_fetch_row($res);
		}
		return $data;
	}

	public function fetchOne($sql)
	{
		$data = false;
		$row = $this->fetchRow($sql);
		if(is_array($row))
		{
			$data = $row[0];
		}
		return $data;
	}

	public function &fetchArray($sql)
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			$data = mysql_fetch_assoc($res);
		}
		return $data;
	}

	public function &fetchAll($sql, $id = '', $cached = false)
	{
		$data = array();
		$res = $this->query($sql, $cached);
		if($res !== false)
		{
			if(empty($id))
			{
				while($row = mysql_fetch_assoc($res))
				{
					$data []= $row;
				}
			}
			else
			{
				while($row = mysql_fetch_assoc($res))
				{
					$data[$row[$id]] = $row;
				}
			}
		}

		return $data;
	}

	public function &fetchPairs($sql)
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			while($row = mysql_fetch_row($res))
			{
				$data[$row[0]]= $row[1];
			}
		}
		return $data;
	}

	public function &fetchCol($sql)
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			while($row = mysql_fetch_row($res))
			{
				$data []= $row[0];
			}
		}
		return $data;
	}

	public function affectedRows()
	{
		$n = mysql_affected_rows($this->db);
		if($n >= 0)
		{
			return $n;
		}
		return false;
	}

	public function insertId()
	{
		$id = mysql_insert_id($this->db);
		if($id > 0)
		{
			return $id;
		}
		return false;
	}

	public function mysqlError()
	{
		return mysql_errno() . ": " . mysql_error();
	}

	public function escape($str)
	{
		$this->_connect();
		if(is_array($str))
		{
			foreach($str as $k => $v)
			{
				$str[$k] = mysql_real_escape_string($v, $this->db);
			}
		}
		else
		{
			$str = mysql_real_escape_string($str, $this->db);
		}
		return $str;
	}
}
