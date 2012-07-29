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
			$list[$k]['send_state_format'] = $list[$k]['send_state'] ? '已发送' : '未发送';
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
		$state && $this->record($id, LOG_DATA_TABLE_SENDLIST, LOG_OPERATION_TYPE_DELETE);

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
		$state && $this->record(0, LOG_DATA_TABLE_SENDLIST, LOG_OPERATION_TYPE_DELETE);

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

	public function import($id, $list)
	{
		$targetObj = Factory::getModel('target');
		$channelObj = Factory::getModel('channel');
		$taskmultiObj = Factory::getModel('taskmulti');

		$taskmulti = $taskmultiObj->getObject(array(array('id' => array('eq', $id))));
		$channel = $channelObj->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))));

		$field = $list[0];
		unset($field[0]);
		$field_count = count($field);
		unset($list[0]);

		$insert_count = 0;
		$failed_count = 0;

		$item_count = count($list);
		$batch_count = ceil($item_count / BATCH_IMPORT);

		for($i = 0; $i < $batch_count; $i++)
		{
			$this_list = array();
			$this_count = ($item_count > BATCH_IMPORT * ($i + 1)) ? BATCH_IMPORT : ($item_count - BATCH_IMPORT * $i);

			$targets_id = array();
			for($j = 1; $j <= $this_count; $j++)
			{
				$targets_id[] = $list[$i * BATCH_IMPORT + $j][0];
			}
			$targets_id = $targetObj->addBatch($targets_id, $channel['type']);

			for($j = 1; $j <= $this_count; $j++)
			{
				$v = $list[$i * BATCH_IMPORT + $j];
				$contact = $v[0];
				unset($v[0]);

				if(count($v) == $field_count && !empty($targets_id[$contact]) && ($target_id = $targets_id[$contact]))
				{
					$data = (count($field) > 0) ? json_encode(array_combine($field, $v)) : '';
					$this_list[] = array('target_id' => $target_id, 'task_id' => $id, 'data' => $data);
				}
				else
				{
					// 失败统计
				}
			}
			// 插入
			$insert_count += $this->insertBatch(array('target_id', 'task_id', 'data'), $this_list);
		}

		$condition = array();
		$condition[] = array('task_id' => array('eq', $id));
		$send_count = $this->getOne($condition, 'COUNT(*)');
	
		$condition = array();
		$condition[] = array('id' => array('eq', $id));
		$data = array('send_count' => $send_count);
		$this->update($condition, $data, 'task_multi');

		$this->record(0, LOG_DATA_TABLE_SENDLIST, LOG_OPERATION_TYPE_INSERT);

		return array('state' => true, 'message' => '共 ' . $item_count . ' 条数据，成功导入 ' . $insert_count . ' 条数据。有 ' . $failed_count . ' 条错误数据格式，有 ' . ($item_count - $insert_count - $failed_count) . ' 条目标地址错误或被禁止。');
	}
}
