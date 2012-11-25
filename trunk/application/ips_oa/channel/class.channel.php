<?php
class Channel
{
	public $channel_id = 0;

	public $run = '';

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
		$path = APP_DIR_LOGS . 'channel/' . date('Ym', $date) . '/';
		$file = date('d', $date) . '_' . $this->channel_id . '.txt';
		!is_dir($path) && mkdir($path, 0755, true);
		$handle = fopen($path . $file, 'a');
		fwrite($handle, $log);
		fclose($handle);
	}

	public function run()
	{
		$call = $this->run;
		$result = $this->$call();
		if(empty($result))
		{
			$count = $this->task_count['single'] + $this->task_count['multi'];
			$time = number_format(microtime(true) - $this->run_time['start'], 4, '.', '');
			return array('state' => true, 'message' => '发送信息 ' . $count . ' 条，耗时 ' . $time . ' 秒。');
		}
		return $result;
	}

	public function runBase()
	{
		// 单任务，多任务
		foreach($this->task_obj as $k => $v)
		{
			// 获取发送内容
			$result = $this->task_obj[$k]->taskReceive($this->channel_id, $this->task_count[$k]);
			if(!$result['state'])
			{
				return $result;
			}
			$list = $result['data'];

			// 记录发送数量
			$this->task_count[$k] += count($list);
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
						// 记录成功或失败数量
						$this->run_count[$k][$state ? 'success' : 'failed']++;
					}
					catch(phpmailerException $e)
					{
						$result[$_v['id']] = false;
						// 记录失败数量
						$this->run_count[$k]['failed']++;
						// 记录错误信息
						$this->errors[] = $k . '_' . $_v['id'] . ':' . $e->errorMessage();
					}
				}
				// 提交任务结果
				$this->task_obj[$k]->taskSubmit($result);
			}
			// 刷新通道任务
			$this->task_obj[$k]->taskReflash($this->channel_id);
		}
	}

	public function uriRequest($url, $postdata = null, $proxy = null)
	{
		$curl = curl_init();
		if($proxy)
		{
			curl_setopt($curl, CURLOPT_PROXY, $proxy['host']); 
			curl_setopt($curl, CURLOPT_PROXYPORT, $proxy['port']);
		}
		if(preg_match('/^https/i', $url))
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if($postdata)
		{
			curl_setopt($curl, CURLOPT_POST, 1);
			$data = array();
			foreach($postdata as $key => $val)
			{
				$data[] = $key . '=' . urlencode($val);
			}
			$data = implode('&', $data);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if($http_code != 200)
		{
			return null;
		}
		return $result;
	}

	public function decodeXml($string)
	{
		if(is_string($string))
		{
			return simplexml_load_string($string);
		}
		return null;
	}
}
