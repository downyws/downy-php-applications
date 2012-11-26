<?php

class Db
{
	static $dbs = array();
	private $db = null;
	protected $_config = array();
	protected $_logs = null;
	protected $_error = '';
	protected $_trigger = array(
		'before' => array(),
		'after' => array(),
		'error' => array()
	);

	public function __construct($config)
	{
		$this->_config = $config;
		$this->_config['POST'] = empty($this->_config['POST']) ? ini_get("mysqli.default_port") : $this->_config['PORT'];
		$this->_logs = new Logs();
	}

	protected function _connect()
	{
		$ping = $this->db ? mysqli_ping($this->db) : false;

		if(!$this->db || !$ping)
		{
			$key = md5(serialize($this->_config));
			if(empty(db::$dbs[$key]) || !$ping)
			{
				$this->db = mysqli_connect($this->_config['HOST'], $this->_config['USERNAME'], $this->_config['PASSWORD'], $this->_config['DBNAME'], $this->_config['POST']);
				if($this->_config['CHARSET'])
				{
					mysqli_set_charset($this->db, $this->_config['CHARSET']);
				}

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

		foreach($this->_trigger['before'] as $v)
		{
			call_user_func($v, $sql);
		}

		if(mysqli_real_query($this->db, $sql))
		{
			$res = mysqli_use_result($this->db);
			$res = ($res === false) ? true : $res;
		}
		else
		{
			$res = false;
		}

		foreach($this->_trigger['after'] as $v)
		{
			call_user_func($v, $sql, $res);
		}

		$errno = mysqli_errno($this->db);
		if($errno)
		{
			$error = mysqli_error($this->db);
			$this->_msg_error = $errno . ': ' . $error;
			foreach($this->_trigger['error'] as $v)
			{
				call_user_func($v, $sql, $errno, $error);
			}
			$logs_file = $this->_logs->attachment('query', array('sql' => $sql, 'errno' => $errno, 'error' => $error));
			$this->_logs->message('query', 'Error: Query Failed, see ' . $logs_file);
		}
		else
		{
			$this->_msg_error = '';
		}

		return $res;
	}

	public function fetchOne($sql)
	{
		$data = false;
		$res = $this->query($sql);
		if($res !== false)
		{
			$data = mysqli_fetch_row($res);
		}
		if(is_array($data) && count($data) > 0)
		{
			$data = $data[0];
		}
		return $data;
	}

	public function &fetchPairs($sql)
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			while($row = mysqli_fetch_row($res))
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
			while($row = mysqli_fetch_row($res))
			{
				$data []= $row[0];
			}
		}
		return $data;
	}

	public function &fetchRow($sql)
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			$data = mysqli_fetch_assoc($res);
		}
		return $data;
	}

	public function &fetchRows($sql, $id = '')
	{
		$data = array();
		$res = $this->query($sql);
		if($res !== false)
		{
			if(empty($id))
			{
				while($row = mysqli_fetch_assoc($res))
				{
					$data []= $row;
				}
			}
			else
			{
				while($row = mysqli_fetch_assoc($res))
				{
					$data[$row[$id]] = $row;
				}
			}
		}

		return $data;
	}

	public function fetchMySqlVersion()
	{
		$sql = 'SELECT VERSION()';
		return $this->fetchOne($sql);
	}

	public function fetchDbSize()
	{
		$sql = 'SELECT SUM(DATA_LENGTH) + SUM(INDEX_LENGTH) FROM information_schema.`TABLES` WHERE TABLE_SCHEMA = "' . $this->_config['DBNAME'] . '"';
		return $this->fetchOne($sql);
	}

	public function affectedRows()
	{
		$n = mysqli_affected_rows($this->db);
		if($n >= 0)
		{
			return $n;
		}
		return false;
	}

	public function insertId()
	{
		$id = mysqli_insert_id($this->db);
		if($id > 0)
		{
			return $id;
		}
		return false;
	}

	public function mysqlError()
	{
		return $this->_error;
	}

	public function escape($str)
	{
		$this->_connect();
		return mysqli_real_escape_string($this->db, $str);
	}

	public function addTrigger($event, $handler)
	{
		foreach($this->_trigger[$event] as $v)
		{
			if($v == $handler)
			{
				return false;
			}
		}

		$this->_trigger[$event][] = $handler;
		return true;
	}

	public function removeTrigger($event, $handler)
	{
		foreach($this->_trigger[$event] as $k => $v)
		{
			if($v == $handler)
			{
				unset($this->_trigger[$event][$k]);
				return true;
			}
		}

		return false;
	}
}
