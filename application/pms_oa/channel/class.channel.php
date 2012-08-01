<?php
class Channel
{
	public $channel_id = 0;

	public $type = '';
	public $type_obj = null;
	
	public $task_obj = array('single' => null, 'multi' => null);
	
	public $task_count = array('single' => 0, 'multi' => 0);
	
	public $run_time = array('start' => 0, 'end' => 0);
	
	public $run_count = array(
		'single' => array('success' => 0, 'failed' => 0), 
		'multi' => array('success' => 0, 'failed' => 0)
	);

	public $errors = array();

	public function __construct()
	{
		$this->run_time['start'] = microtime(true);

		$this->task_obj['single'] = Factory::getModel('tasksingle');
		$this->task_obj['multi'] = Factory::getModel('taskmulti');
	}

	public function __destruct()
	{
		$this->run_time['end'] = microtime(true);

		$date = $this->run_time['start'];

		// 生成日志内容
		$data = array(
			'start_time' => $this->run_time['start'],
			'end_time' => $this->run_time['end'],
			'task_count_single' => $this->task_count['single'],
			'task_count_multi' => $this->task_count['multi'],
			'run_count_single_success' => $this->run_count['single']['success'],
			'run_count_single_failed' => $this->run_count['single']['failed'],
			'run_count_multi_success' => $this->run_count['multi']['success'],
			'run_count_multi_failed' => $this->run_count['multi']['failed'],
		);
		$log = date('Y-m-d H:i:s', $date) . "\n" . json_encode($data) . "\n" . json_encode($this->errors) . "\n\n";

		// 保存日志
		$path = APP_DIR_LOG . 'channel/' . date('Ym', $date) . '/';
		$file = date('d', $date) . '_' . $this->channel_id . '.txt';
		!is_dir($path) && mkdir($path, 0755, true);
		$handle = fopen($path . $file, 'a');
		fwrite($handle, $log);
		fclose($handle);
	}

	public function run()
	{
		if($this->type == CHANNEL_TYPE_EMAIL)
		{
			return $this->runEmail();
		}
		throw new Exception('code have not finish.');
	}

	public function runEmail()
	{
		foreach($this->task_obj as $k => $v)
		{
			$result = $this->task_obj[$k]->taskReceive($this->channel_id, $this->task_count[$k]);
			if(!$result['state'])
			{
				return $result;
			}
			$list = $result['data'];

			$this->task_count[$k] = count($list);
			if(is_array($list) && count($list) > 0)
			{
				$result = array();
				foreach($list as $_v)
				{
					try
					{
						$this->type_obj->AddAddress($_v['contact']);
						$this->type_obj->Subject = $_v['title'];
						$this->type_obj->MsgHTML($_v['content']);
						$state = $this->type_obj->Send();
						$result[$_v['id']] = $state;
						$this->run_count[$k][$state ? 'success' : 'failed']++;
					}
					catch(phpmailerException $e)
					{
						$result[$_v['id']] = false;
						$this->run_count[$k]['failed']++;
						$this->errors[] = $k . '_' . $_v['id'] . ':' . $e->errorMessage();
					}
				}
				$this->task_obj[$k]->taskSubmit($result);
			}
			$this->task_obj[$k]->taskReflash($this->channel_id);
		}
		return array('state' => true, 'message' => '发送信息 ' . ($this->task_count['single'] + $this->task_count['multi']) . ' 条，耗时 ' . number_format(microtime(true) - $this->run_time['start'], 4, '.', '') . ' 秒。');
	}
}
