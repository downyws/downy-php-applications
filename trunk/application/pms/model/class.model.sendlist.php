<?php
class ModelSendList extends ModelCommon
{
	public $_table = 'send_list';

	public function __construct()
	{
		parent::__construct();
	}

	public function getList($id, $p, $params, $ps = APP_PAGEE_SIZE)
	{
		$condition = array();
		$condition[] = array('s.`task_id`' => array('eq', $id));
		!empty($params['contact']) && $condition[] = array('t.`contact`' => array('like', $params['contact']));

		$sql = 'SELECT COUNT(*) FROM ' . $this->table() . ' AS s JOIN ' . $this->table('target') . ' AS t ON (t.`id` = s.`target_id`) ' . $this->getWhere($condition);
		$count = $this->fetchOne($sql);
		$sql = 'SELECT s.*, t.contact FROM ' . $this->table() . ' AS s JOIN ' . $this->table('target') . ' AS t ON (t.`id` = s.`target_id`) ' . $this->getWhere($condition) . ' ORDER BY s.`id` DESC ' . $this->getLimit($p, $ps);
		$data = $this->fetchAll($sql);
		$pager = $this->getPager($p, $count, $ps);

		return array('count' => $count, 'data' => $data, 'pager' => $pager);
	}

	public function formatList($list)
	{
		foreach($list as $k => $v)
		{
			$list[$k]['is_send_format'] = $list[$k]['is_send'] ? '已发送' : '未发送';
		}
		return $list;
	}

	public function remove($id)
	{
		$task_id = $this->getOne(array(array('id' => array('eq', $id))), 'task_id');

		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$state = $this->delete($condition);
		$state = ($state !== false);

		if($state)
		{
			$count = $this->getOne(array(array('task_id' => array('eq', $task_id))), 'COUNT(*)');
			$condition = array();
			$condition[] = array('id' => array('eq', $task_id));
			$data = array('send_count' => $count);
			$this->update($condition, $data, 'task_multi');
		}

		$message = $state ? '删除成功。' : '删除失败。';
		return array('state' => $state, 'message' => $message);
	}

	public function clear($id)
	{
		$condition = array();
		$condition[] = array('task_id' => array('eq', $id));
		$state = $this->delete($condition);
		$state = ($state !== false);
		
		// 更新多任务
		if($state)
		{
			$condition = array();
			$condition[] = array('id' => array('eq', $id));
			$data = array('send_count' => 0);
			$this->update($condition, $data, 'task_multi');
		}

		$message = $state ? '清空列表成功。' : '清空列表失败。';
		return array('state' => $state, 'message' => $message);
	}
}
