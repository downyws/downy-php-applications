<?php
class Channel_4 extends Channel
{
	public function __construct()
	{
		parent::__construct();
		$this->channel_id = 4;
		$config = $GLOBALS['CONFIG']['CHANGE']['CHANGE_' . $this->channel_id];

		// 参数配置
		$this->task_count['single'] = $config['single'];
		$this->task_count['multi'] = $config['multi'];

		// 初始化
		$this->run = 'runSelf';
		$this->type_obj = array(
			'username' => $config['username'],
			'password' => $config['password'],
			'urlsend' => $config['urlsend']
		);
	}

	public function runSelf()
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
				$data = array(
					'username' => $this->type_obj['username'],
					'password' => $this->type_obj['password'],
				);
				foreach($list as $_v)
				{
					$data['receiver'] = $_v['contact'];
					$data['content'] = $_v['content'];
					$response = $this->uriRequest($this->type_obj['urlsend'], $data);
					$response = $this->decodeXml($response);
					$response = get_object_vars($response);
					if(empty($response) || intval($response['result']) < 0)
					{
						$result[$_v['id']] = false;
						$this->run_count[$k]['failed']++;
						$message = empty($response) ? 'response null' : $response['result'] . ' ' . $response['message'];
						$this->errors[] = $k . '_' . $_v['id'] . ':' . $message;
					}
					else
					{
						$result[$_v['id']] = true;
						$this->run_count[$k]['success']++;
					}
				}
				$this->task_obj[$k]->taskSubmit($result);
			}
			$this->task_obj[$k]->taskReflash($this->channel_id);
		}
	}
}
