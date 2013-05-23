<?php
class ModelFile extends ModelCommon
{
	public $_table = 'file';

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

	public function getByFileName($file_name)
	{
		$condition = array();
		$condition[] = array('file_name' => array('eq', $file_name));
		$object = $this->getObject($condition, array());

		return $object;
	}

	public function getList()
	{
		$sql = 'SELECT * FROM ' . $this->table() . ' ORDER BY file_name ASC ';
		$data = $this->fetchRows($sql);

		$result = array('count' => count($data), 'data' => $data, 'pager' => null);
		return $result;
	}

	public function getListMyHasTask()
	{
		$user_id = $this->getSessionUser('id');
		$sql = ' SELECT f.* FROM ' . $this->table('mapping') . ' AS m JOIN ' . $this->table() . ' AS f ON f.id = m.file_id WHERE m.occ_user_id = ' . $user_id . ' GROUP BY f.id ORDER BY f.file_name ASC';
		$data = $this->fetchRows($sql);
		return array('count' => count($data), 'data' => $data, 'pager' => null);
	}

	public function save($object)
	{
		$result = array('state' => false);

		// 文件名唯一检查
		$condition = array();
		$condition[] = array('id' => array('neq', $object['id']));
		$condition[] = array('file_name' => array('eq', $object['file_name']));
		if($this->getOne($condition, 'id'))
		{
			$result['message'] = 'file name exists.';
			return $result;
		}

		if($object['id'] > 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			unset($object['id']);
			$result['state'] = $this->update($condition, $object);
		}
		else
		{
			unset($object['id']);
			$default = array(
				'wait_t_count' => 0,
				'wait_p_count' => 0,
				'wait_a_count' => 0,
				'data_count' => 0,
				'update_time' => time()
			);
			$object = array_merge($default, $object);
			$result['state'] = $this->insert($object);
		}

		$result['message'] = $result['state'] ? 'save success.' : 'save error.';

		return $result;
	}

	public function remove($id)
	{
		// 开启事务
		$this->transStart();

		$users = array();

		// 删除映射
		$condition = array();
		$condition[] = array('file_id' => array('eq', $id));
		$this->delete($condition, array(), 'mapping');

		// 删除文件
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$this->delete($condition);

		// 提交事务
		return $this->transCommit();
	}

	public function import($file)
	{
		$object = $this->getByFileName($file['name']);
		if(!$object)
		{
			return array('state' => false, 'message' => 'File not exists.');
		}

		// 读取更新文件
		$content = file_get_contents($file['tmp_name']);
		$content = iconv('UCS-2', 'UTF-8', $content);
		$content = explode("\n", $content);
		$mapping = array();
		foreach($content as $v)
		{
			$v = explode("\t", $v);
			if(count($v) >= 2)
			{
				$key = trim($v[0]);
				unset($v[0]);
				$content = implode("\t", $v);
				if(preg_match('/^[0-9]+$/', $key))
				{
					$mapping[$key] = array('key' => $key, 'content_new_en' => trim($content), 'is_new' => 0);
				}
			}
		}
		if(empty($mapping))
		{
			return array('state' => true, 'message' => 'nothing import.');
		}

		// 开启事务
		$this->transStart();

		// 记录映射临时表
		$this->delete(array(), array(), 'mapping_temp');
		$this->insertBatch(array('key', 'content_new_en'), $mapping, 'mapping_temp');

		// 添加新增映射
		$sql =  ' INSERT INTO ' . $this->table('mapping') . ' ( ' .
				'	SELECT ' .
				'		`key`, ' . $object['id'] . ' AS file_id, ' .
				'		0 AS t_user_id, 0 AS p_user_id, 0 AS a_user_id, 0 AS occ_user_id, 0 AS occ_time, ' . MAPPING_STATE_TW . ' AS state, ' .
				'		content_new_en AS content_old_en, content_new_en AS content_old_cn, content_new_en AS content_new_en, "" AS content_new_cn, "" AS reason ' .
				'	FROM ' . $this->table('mapping_temp') . ' WHERE `key` NOT IN (SELECT `key` FROM ' . $this->table('mapping') . ' WHERE file_id = ' . $object['id'] . ') ' .
				' ) ';
		$this->query($sql);

		// 更新映射
		$sql =  ' REPLACE INTO ' . $this->table('mapping') . ' ( ' .
				'	SELECT ' .
				'		m.`key`, m.file_id, ' .
				'		0 AS t_user_id, 0 AS p_user_id, 0 AS a_user_id, 0 AS occ_user_id, 0 AS occ_time, ' . MAPPING_STATE_TW . ' AS state, ' .
				'		IF(m.state = ' . MAPPING_STATE_CP . ', m.content_new_en, m.content_old_en) AS content_old_en, ' .
				'		IF(m.state = ' . MAPPING_STATE_CP . ', m.content_new_cn, m.content_old_cn) AS content_old_cn, ' .
				'		mt.content_new_en AS content_new_en, "" AS content_new_cn, "" AS reason ' .
				'	FROM ' . $this->table('mapping_temp') . ' AS mt ' .
				'	JOIN ' . $this->table('mapping') . ' AS m ON m.`key` = mt.`key` AND m.file_id = ' . $object['id'] . ' AND m.content_new_en != mt.content_new_en ' .
				' ) ';
		$this->query($sql);

		// 提交事务
		$result = array('state' => $this->transCommit());
		$result['message'] = $result['state'] ? 'Import data ' . count($mapping) . ', run success.' : 'Import run error.';
		return $result;
	}

	public function export($id)
	{
		// 获取内容
		$sql =  ' SELECT GROUP_CONCAT(CONCAT(`key`, "\t", c) SEPARATOR "\r\n") FROM( ' .
				'	SELECT `key`, IF(state = ' . MAPPING_STATE_CP . ', content_new_cn, content_new_en) AS c FROM ' . $this->table('mapping') . ' WHERE file_id = ' . $id . ' ORDER BY `key` ASC ' .
				' ) AS tb ';
		$content = $this->fetchOne($sql);

		// 获取标题
		$object = $this->getById($id);
		$title = $object['name_key'] . "\t" . $object['name_val'] . "\r\n";

		$result = $title . $content;
		// $result = iconv('UTF-8', 'UCS-2', $result);
		return $result;
	}

	public function refresh($id)
	{
		// 更新文件信息
		$sql = ' SELECT ROUND(state / 10, 0) AS state, COUNT(*) FROM ' . $this->table('mapping') . ' WHERE file_id = ' . $id . ' GROUP BY state ';
		$counts = $this->fetchPairs($sql);
		$convert = array
		(
			intval(MAPPING_STATE_TW / 10) => 'wait_t_count',
			intval(MAPPING_STATE_PW / 10) => 'wait_p_count',
			intval(MAPPING_STATE_AW / 10) => 'wait_a_count'
		);
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$data = array
		(
			'wait_t_count' => 0,
			'wait_p_count' => 0,
			'wait_a_count' => 0,
			'data_count' => 0,
			'update_time' => time()
		);
		foreach($counts as $k => $v)
		{
			$data['data_count'] += $v;
			if(!empty($convert[$k]))
			{
				$data[$convert[$k]] = $v;
			}
		}
		return $this->update($condition, $data);
	}
}
