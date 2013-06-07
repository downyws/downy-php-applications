<?php
class ModelDict extends ModelCommon
{
	public $_table = 'dict';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($page_now, $page_size, $params)
	{
		$condition = array();
		if(!empty($params['cn']))
		{
			$condition[] = array('cn' => array('like', $params['cn']));
		}
		if(!empty($params['en']))
		{
			$condition[] = array('en' => array('like', $params['en']));
		}

		$sql = ' SELECT * FROM ' . $this->table() . $this->getWhere($condition) . ' ORDER BY en ASC ' . $this->getLimit($page_now, $page_size);
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
		$result['message'] = $result['state'] ? '' : '没有匹配到字典。';

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
			$result['message'] = $result['state'] ? '' : '保存错误。';
		}
		else
		{
			$condition = array();
			$condition[] = array('en' => array('eq', $object['en']));
			$exists = $this->getOne($condition, 'COUNT(*)');
			if($exists)
			{
				$result = array('state' => false, 'message' => '字典已经存在。');
			}
			else
			{
				$data = array('cn' => $object['cn'], 'en' => $object['en']);
				$result['state'] = $this->insert($data);
				$result['message'] = $result['state'] ? '' : '保存错误。';
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
		$result['message'] = $result['state'] ? '导入成功。' : '导入失败。';
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
}
