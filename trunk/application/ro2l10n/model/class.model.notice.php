<?php
class ModelNotice extends ModelCommon
{
	public $_table = 'notice';

	public function __construct()
	{
		parent::__construct();
	}

	public function getById($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$object = $this->getObject($condition);

		$object['style_u'] = strpos($object['style'], 'text-decoration:underline') !== false;
		$object['style_b'] = strpos($object['style'], 'falsefont-weight:bold') !== false;
		$object['style_i'] = strpos($object['style'], 'falsefont-style:italic') !== false;
		$object['style_s'] = strpos($object['style'], 'text-decoration:line-through') !== false;

		$object['style_color'] = '';
		$color = strpos($object['style'], 'color:#');
		if($color !== false)
		{
			$object['style_color'] = substr($object['style'], $color + 7, 6);
		}

		return $object;
	}

	public function show()
	{
		$now = time();
		$condition = array();
		$condition[] = array('n.id' => array('gt', 0));
		$condition[] = array('n.start_time' => array('lte', $now));
		$condition[] = array('n.end_time' => array('gte', $now));
		$sql =  ' SELECT n.* FROM ' . $this->table() . ' AS n ' . $this->getWhere($condition) . 
				' ORDER BY n.sort DESC, n.end_time DESC, n.id DESC ';
		return $this->fetchRows($sql);
	}

	public function getList($page_now, $page_size)
	{
		$now = time();
		$sql =  ' SELECT n.*, IF(' . $now . ' BETWEEN n.start_time AND n.end_time, 1, 0) AS is_show, IF(n.id = 0, 1, 0) AS sortk_1, IF(n.end_time >= ' . $now . ', n.sort, 0) AS sortk_2, IF(n.end_time >= ' . $now. ', n.end_time, 0) AS sortk_3 FROM ' . $this->table() . ' AS n ' . 
				' ORDER BY sortk_1 DESC, sortk_2 DESC, sortk_3 DESC, n.id DESC ' . $this->getLimit($page_now, $page_size);
		$data = $this->fetchRows($sql);

		$count = $this->getOne(array(), 'COUNT(*)');
		$pager = $this->getPager($page_now, $count, $page_size);

		$result = array('count' => $count, 'data' => $data, 'pager' => $pager);
		return $result;
	}

	public function remove($id)
	{
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$condition[] = array('id' => array('gt', 0));
		return $this->delete($condition);
	}

	public function save($object)
	{
		$object['start_time'] = intval(strtotime($object['start_time']));
		$object['end_time'] = intval(strtotime($object['end_time']));

		$object['style'] = '';
		$object['style'] .= $object['style_u'] ? 'text-decoration:underline;' : '';
		$object['style'] .= $object['style_b'] ? 'font-weight:bold;' : '';
		$object['style'] .= $object['style_i'] ? 'font-style:italic;' : '';
		$object['style'] .= $object['style_s'] ? 'text-decoration:line-through;' : '';
		$object['style'] .= $object['style_color'] ? ('color:#' . $object['style_color']) : '';

		if($object['id'] >= 0)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $object['id']));
			unset($object['id']);
			$result['state'] = $this->update($condition, $object);
		}
		else
		{
			unset($object['id']);
			$result['state'] = $this->insert($object);
		}

		$result['message'] = $result['state'] ? 'save success.' : 'save error.';

		return $result;
	}
}
