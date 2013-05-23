<?php
class ModelLocalization extends ModelCommon
{
	public $_table = 'mapping';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($page_now, $page_size, $params)
	{
		$condition = array();
		if(!empty($params['key']))
		{
			$condition[] = array('`key`' => array('eq', $params['key']));
		}
		if(!empty($params['content_new_en']))
		{
			$condition[] = array('content_new_en' => array('like', $params['content_new_en']));
		}
		if(!empty($params['state']))
		{
			$condition[] = array('state' => array('in', $params['state']));
		}
		if(!empty($params['file']))
		{
			$condition[] = array('file_id' => array('in', $params['file']));
		}

		$sql = ' SELECT m.*, f.file_name, f.file_name_short FROM ' . $this->table() . ' AS m JOIN ' . $this->table('file') . ' AS f ON f.id = m.file_id ' . 
				$this->getWhere($condition) . ' ORDER BY m.`key` ASC, f.file_name ASC ' . $this->getLimit($page_now, $page_size);
		$data = $this->fetchRows($sql);

		$count = $this->getOne($condition, 'COUNT(*)');
		$pager = $this->getPager($page_now, $count, $page_size);

		$result = array('count' => $count, 'data' => $data, 'pager' => $pager);
		return $result;
	}

	public function search($content)
	{
		$result = array();

		// 转换
		if(preg_match('/^[0-9]+$/', $content))
		{
			$condition = array();
			$condition[] = array('`key`' => array('eq', $content));
			$content = $this->getOne($condition, 'GROUP_CONCAT(content_new_en SEPARATOR " ")', 'mapping');
		}

		// 读取缓存
		$filecache = new Filecache();
		$key = 'dict/' . md5($content);
		$result = $filecache->get($key);
		if($result)
		{
			return $result;
		}

		// 搜索
		$sql = 'SELECT * FROM ' . $this->table('dict') . ' WHERE "' . $this->escape($content) . '" LIKE CONCAT("%", en, "%") LIMIT 0, ' . (DICT_SHOW_COUNT + 1);
		$result['data'] = $this->fetchRows($sql);
		if(count($result['data']) > DICT_SHOW_COUNT)
		{
			array_pop($result['data']);
			$result['data'][] = array('en' => 'More then ' . DICT_SHOW_COUNT . ', can not show all.', 'cn' => '');
		}
		$result['state'] = !!$result['data'];
		$result['message'] = $result['state'] ? '' : 'Did not match any dictionaries.';

		// 保存缓存
		$filecache->set($key, $result, 3600);

		return $result;
	}

	public function save($object)
	{
		$result = array();
		if($object['id'] > 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			$data = array('cn' => $object['cn']);
			$result['state'] = $this->update($condition, $data);
			$result['message'] = $result['state'] ? '' : 'save error.';
		}
		else
		{
			$condition = array();
			$condition[] = array('en' => array('eq', $object['en']));
			$exists = $this->getOne($condition, 'COUNT(*)');
			if($exists)
			{
				$result = array('state' => false, 'message' => 'dict en is exists.');
			}
			else
			{
				$data = array('cn' => $object['cn'], 'en' => $object['en']);
				$result['state'] = $this->insert($data);
				$result['message'] = $result['state'] ? '' : 'save error.';
			}
		}
		return $result;
	}

	public function remove($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		return $this->delete($condition);
	}

	public function import($path)
	{
		$dict = array();
		$content = file_get_contents($path);
		$content = trim($content);
		$content = explode("\n", $content);
		foreach($content as $v)
		{
			$v = explode("\t", $v);
			if(count($v) == 2)
			{
				$dict[md5($v[0])] = array('en' => trim($v[0]), 'cn' => trim($v[1]));
			}
		}

		$this->transStart();
		
		// 清空字典
		$this->delete(array());
		// 重置编号
		$sql = 'ALTER TABLE ' . $this->table() . ' AUTO_INCREMENT = 1';
		$this->query($sql);
		// 插入字典
		$this->insertBatch(array('en', 'cn'), $dict);

		$result = array();
		$result['state'] = $this->transCommit();
		$result['message'] = $result['state'] ? 'Import success.' : 'Import error.';
		return $result;
	}

	public function export()
	{
		$sql =  ' SELECT GROUP_CONCAT(CONCAT(en, "\t", cn) SEPARATOR "\r\n") FROM( ' .
				'	SELECT en, cn FROM ' . $this->table() . ' ORDER BY en ASC ' .
				' ) AS tb ';
		return $this->fetchOne($sql);
	}

	public function clear()
	{
		$filecache = new Filecache();
		$path = APP_DIR_CACHE . 'filecache/dict';
		$handle = opendir($path);
		while($file = readdir($handle))
		{
			if(!is_dir($file))
			{
				unlink($path . '/' . $file);
			}
		}
		return @rmdir($path);
	}

	public function occupied($params)
	{
		$result = array('state' => false);

		$condition = array();
		foreach($params['opr_file_key'] as $v)
		{
			$v = explode('_', $v);
			if(count($v) == 2 && preg_match('/^[0-9]+$/', $v[0]) && preg_match('/^[0-9]+$/', $v[1]))
			{
				$condition[] = '("' . $v[0] . '", ' . $v[1] . ')';
			}
		}

		$userObj = Factory::getModel('user');
		$user_id = $this->getSessionUser('id');
		$user = $userObj->getById($user_id);
		if($params['opr'] == 'back')
		{
			$opr_lv = ($user['audit'] >= 2);
		}
		else
		{
			$opr_lv = $user[$params['opr']];
		}

		if(empty($condition))
		{
			$result['message'] = 'Have not item.';
		}
		else if(!$opr_lv)
		{
			$result['message'] = 'No power.';
		}
		else
		{
			$count = count($condition);
			$condition = '(' . implode(',', $condition) . ')';
			switch($params['opr'])
			{
				case 'translation': 
					$sql = 'UPDATE ' . $this->table() . ' SET state = ' . MAPPING_STATE_TD . ', occ_time = ' . time() . ', occ_user_id = ' . $user['id'] . ' WHERE state = ' . MAPPING_STATE_TW . ' AND (file_id, `key`) IN ' . $condition;
					break;
				case 'proof': 
					$sql = 'UPDATE ' . $this->table() . ' SET state = ' . MAPPING_STATE_PD . ', occ_time = ' . time() . ', occ_user_id = ' . $user['id'] . ' WHERE state = ' . MAPPING_STATE_PW . ' AND (file_id, `key`) IN ' . $condition;
					if($opr_lv == 1)
					{
						$sql .= ' AND t_user_id != ' . $user['id'];
					}
					break;
				case 'audit': 
					$sql = 'UPDATE ' . $this->table() . ' SET state = ' . MAPPING_STATE_AD . ', occ_time = ' . time() . ', occ_user_id = ' . $user['id'] . ' WHERE state = ' . MAPPING_STATE_AW . ' AND (file_id, `key`) IN ' . $condition;
					if($opr_lv == 1)
					{
						$sql .= ' AND p_user_id != ' . $user['id'];
					}
					break;
				case 'back':
					$sql = 'UPDATE ' . $this->table() . ' SET state = ' . MAPPING_STATE_AW . ', a_user_id = 0 WHERE state = ' . MAPPING_STATE_CP . ' AND (file_id, `key`) IN ' . $condition;
					break;
			}

			$this->transStart();
			$exec = $this->query($sql);
			if($exec !== false)
			{
				$exec = $this->affectedRows();
				if($user['task_max_count'] < $user['task_occ_count'] + $exec)
				{
					$this->transRollback();
					$result = array('state' => false, 'message' => 'You only can occ ' . ($user['task_max_count'] - $user['task_occ_count']) . ' task.');
				}
				else
				{
					$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count + ' . $exec . ' WHERE id = ' . $user['id'];
					$this->query($sql);
					$result['state'] = $this->transCommit();
					if($result['state'])
					{
						$this->setSessionUser(array('task_occ_count' => $user['task_occ_count'] + $exec));
						$result = array('state' => true, 'message' => 'Choose ' . $count . ' rows, Exec ' . $exec . ' rows.');
					}
					else
					{
						$result = array('state' => false, 'message' => 'Occ error.');
					}
				}
			}
			else
			{
				$this->transRollback();
				$result = array('state' => false, 'message' => 'Occ error.');
			}
		}
		return $result;
	}

	public function getMyTaskListKey($params)
	{
		$user_id = $this->getSessionUser('id');

		$condition = array();
		$condition[] = array('occ_user_id' => array('eq', $user_id));
		if(!empty($params['key']))
		{
			$condition[] = array('`key`' => array('eq', $params['key']));
		}
		if(!empty($params['content_new_en']))
		{
			$condition[] = array('content_new_en' => array('like', $params['content_new_en']));
		}
		if(!empty($params['state']))
		{
			$condition[] = array('state' => array('in', $params['state']));
		}
		if(!empty($params['file']))
		{
			$condition[] = array('file_id' => array('in', $params['file']));
		}

		$sql = ' SELECT CONCAT(m.file_id, "_", m.`key`) FROM ' . $this->table() . ' AS m JOIN ' . $this->table('file') . ' AS f ON f.id = m.file_id ' . 
				$this->getWhere($condition) . ' ORDER BY m.`key` ASC, f.file_name ASC ';
		$data = $this->fetchCol($sql);

		$key = md5($user_id . time() . mt_rand());
		$count = count($data);

		$filecache = new Filecache();
		$filecache->set('tasklist/' . $key, $data, 86400);

		return array('key' => $key, 'count' => $count);
	}

	public function getListByKey($start, $end, $key)
	{
		$filecache = new Filecache();
		$stamp = array();
		$data = $filecache->get('tasklist/' . $key);
		for($i = $start; $i <= $end; $i++)
		{
			$temp = explode('_', $data[$i]);
			$stamp[] = '(' . $temp[0] . ',' . $temp[1] . ')';
		}
		if(empty($stamp))
		{
			$result = array('state' => false, 'message' => 'No data.');
		}
		else
		{
			$user_id = $this->getSessionUser('id');

			$sql =  ' SELECT m.*, f.file_name FROM ' . $this->table() . ' AS m JOIN ' . $this->table('file') . ' AS f ON f.id = m.file_id' . 
					' WHERE m.occ_user_id = ' . $user_id . ' AND (m.file_id, m.`key`) IN (' . implode(',', $stamp) . ') ORDER BY m.`key` ASC, f.file_name ASC';
			$data = $this->fetchRows($sql);
			$data = empty($data) ? array() : $data;
			$result = array('state' => true, 'message' => '', 'data' => $data);
		}

		return $result;
	}

	public function taskDo($params)
	{
		$result = array('state' => false);

		$user_id = $this->getSessionUser('id');
		list($file_id, $key) = explode('_', $params['stamp']);

		$condition = array();
		$condition[] = array('occ_user_id' => array('eq', $user_id));
		$condition[] = array('`key`' => array('eq', $key));
		$condition[] = array('file_id' => array('eq', $file_id));
		$object = $this->getObject($condition);
		if($object)
		{
			switch($params['state'])
			{
				case 'finish':
					if(empty($object['content_new_cn']))
					{
						$result['message'] = 'Translation content can not empty.';
					}
					else if($object['state'] != MAPPING_STATE_TD)
					{
						$result['message'] = 'Task state error.';
					}
					else
					{
						$this->transStart();
						$data = array('t_user_id' => $user_id, 'occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_PW);
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Finish success.' : 'Finish error.';
					}
					break;
				case 'pass':
					if($object['state'] == MAPPING_STATE_PD)
					{
						$this->transStart();
						$data = array('p_user_id' => $user_id, 'occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_AW);
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Pass success.' : 'Pass error.';
					}
					else if($object['state'] == MAPPING_STATE_AD)
					{
						$this->transStart();
						$data = array('a_user_id' => $user_id, 'occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_CP);
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Pass success.' : 'Pass error.';
					}
					else
					{
						$result['message'] = 'Task state error.';
					}
					break;
				case 'back':
					if($object['state'] == MAPPING_STATE_PD)
					{
						$this->transStart();
						$data = array('t_user_id' => 0, 'occ_user_id' => $object['t_user_id'], 'occ_time' => time(), 'state' => MAPPING_STATE_TB, 'reason' => $params['reason']);
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count + 1 WHERE id = ' . $object['t_user_id'];
						$this->query($sql);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Back success.' : 'Back error.';
					}
					else if($object['state'] == MAPPING_STATE_AD)
					{
						$this->transStart();
						$data = array('p_user_id' => 0, 'occ_user_id' => $object['p_user_id'], 'occ_time' => time(), 'state' => MAPPING_STATE_PB, 'reason' => $params['reason']);
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count + 1 WHERE id = ' . $object['p_user_id'];
						$this->query($sql);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Back success.' : 'Back error.';
					}
					else
					{
						$result['message'] = 'Task state error.';
					}
					break;
				case 'forgo':
					if(in_array($object['state'], array(MAPPING_STATE_TD, MAPPING_STATE_TB)))
					{
						$this->transStart();
						$data = array('occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_TW, 'reason' => '');
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Forgo success.' : 'Forgo error.';
					}
					else if(in_array($object['state'], array(MAPPING_STATE_PD, MAPPING_STATE_PB)))
					{
						$this->transStart();
						$data = array('occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_PW, 'reason' => '');
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Forgo success.' : 'Forgo error.';
					}
					else if(in_array($object['state'], array(MAPPING_STATE_AD, MAPPING_STATE_AB)))
					{
						$this->transStart();
						$data = array('occ_user_id' => 0, 'occ_time' => 0, 'state' => MAPPING_STATE_AW, 'reason' => '');
						$this->update($condition, $data);
						$sql = 'UPDATE ' . $this->table('user') . ' SET task_occ_count = task_occ_count - 1 WHERE id = ' . $user_id;
						$this->query($sql);
						$result['state'] = $this->transCommit();
						$result['message'] = $result['state'] ? 'Forgo success.' : 'Forgo error.';
					}
					else
					{
						$result['message'] = 'Task state error.';
					}
					break;
				case 'confirm':
					if($object['state'] == MAPPING_STATE_TB)
					{
						$data = array('state' => MAPPING_STATE_TD, 'reason' => '');
						$result['state'] = $this->update($condition, $data);
						$result['message'] = $result['state'] ? 'Confirm success.' : 'Confirm error.';
					}
					else if($object['state'] == MAPPING_STATE_PB)
					{
						$data = array('state' => MAPPING_STATE_PD, 'reason' => '');
						$result['state'] = $this->update($condition, $data);
						$result['message'] = $result['state'] ? 'Confirm success.' : 'Confirm error.';
					}
					else if($object['state'] == MAPPING_STATE_AB)
					{
						$data = array('state' => MAPPING_STATE_AD, 'reason' => '');
						$result['state'] = $this->update($condition, $data);
						$result['message'] = $result['state'] ? 'Confirm success.' : 'Confirm error.';
					}
					else
					{
						$result['message'] = 'Task state error.';
					}
					break;
			}
		}
		else
		{
			$result['message'] = 'You have not this task.';
		}

		return $result;
	}

	public function importTask($path)
	{
		$mapping = array();
		$content = file_get_contents($path);
		$content = trim($content);
		$content = explode("\n", $content);
		$key_pointer = '';
		foreach($content as $v)
		{
			$v = trim($v);
			if($v)
			{
				$v = explode("\t", $v);
				switch($v[0])
				{
					case '[key]': $key_pointer = $v[1]; break;
					case '[content_new_cn]': 
						if($key_pointer)
						{
							unset($v[0]);
							list($file_id, $key) = explode('_', $key_pointer);
							$mapping[$key_pointer] = array('file_id' => $file_id, 'key' => $key, 'content_new_cn' => implode("\t", $v));
						}
						break;
				}
			}
		}

		$this->transStart();
		
		$user_id = $this->getSessionUser('id');
		$condition = array();
		$condition[] = array('occ_user_id' => array('eq', $user_id));
		$condition[] = array('state' => array('eq', MAPPING_STATE_TD));

		foreach($mapping as $v)
		{
			$c = $condition;
			$c[] = array('`key`' => array('eq', $v['key']));
			$c[] = array('file_id' => array('eq', $v['file_id']));
			$data = array('content_new_cn' => $v['content_new_cn']);
			$this->update($c, $data);
		}

		$result = array();
		$result['state'] = $this->transCommit();
		$result['message'] = $result['state'] ? 'Import task success.' : 'Import task error.';
		return $result;
	}

	public function exportTask($user_id, $params)
	{
		$condition = array();
		$condition[] = array('m.occ_user_id' => array('eq', $user_id));
		if(!empty($params['key']))
		{
			$condition[] = array('m.`key`' => array('eq', $params['key']));
		}
		if(!empty($params['content_new_en']))
		{
			$condition[] = array('m.content_new_en' => array('like', $params['content_new_en']));
		}
		if(!empty($params['state']))
		{
			$condition[] = array('m.state' => array('in', $params['state']));
		}
		if(!empty($params['file']))
		{
			$condition[] = array('m.file_id' => array('in', $params['file']));
		}

		$sql = ' SELECT m.*, f.file_name FROM ' . $this->table() . ' AS m JOIN ' . $this->table('file') . ' AS f ON f.id = m.file_id ' . 
				$this->getWhere($condition) . ' ORDER BY m.`key` ASC, f.file_name ASC ';
		$data = $this->fetchRows($sql);

		$result = "[翻译]\r\n\r\n";

		foreach($data as $v)
		{
			$result .= "[file]\t" . $v['file_name'] . "\r\n";
			$result .= "[key]\t" . $v['file_id'] . '_' . $v['key'] . "\r\n";
			$result .= "[content_old_en]\t" . $v['content_old_en'] . "\r\n";
			$result .= "[content_old_cn]\t" . $v['content_old_cn'] . "\r\n";
			$result .= "[content_new_en]\t" . $v['content_new_en'] . "\r\n";
			$result .= "[content_new_cn]\t" . $v['content_new_cn'] . "\r\n\r\n\r\n";
		}

		return $result;
	}
}
