<?php
define('TASKSINGLE_COUNT', 10);
define('TASKMULTI_COUNT', 50);

class Channel_1 extends Channel
{
	public function __construct()
	{
		parent::__construct();

		// 参数配置
		$this->channel_id = 1;
		$this->task_count['single'] = 10;
		$this->task_count['multi'] = 50;

		// 初始化邮件
		Factory::loadLibrary('phpmailer');
		$this->mail = new PHPMailer(true);
		$this->mail->IsSMTP();
		$this->mail->SMTPAuth = true;
		$this->mail->Port = 25;
		$this->mail->Host = "smtp.exmail.qq.com";
		$this->mail->Username = "yjmao@baifurun.com";
		$this->mail->Password = "y12345";
		$this->mail->From = "yjmao@baifurun.com";
		$this->mail->FromName = "mao.yijun";
		$this->mail->IsHTML(true);
	}

	public function run()
	{
		foreach($this->task_obj as $k => $v)
		{
			$list = $this->task_obj[$k]->taskReceive($this->channel_id, $this->task_count[$k]);
			$this->task_count[$k] = count($list);
			if(is_array($list) && count($list) > 0)
			{
				$result = array();
				foreach($list as $_v)
				{
					try
					{
						$this->mail->AddAddress($_v['contact']);
						$this->mail->Subject = $_v['title'];
						$this->mail->MsgHTML($_v['content']);
						$state = false;//$this->mail->Send();
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
				$this->task_obj[$k]->taskSubmit($this->channel_id, $result);
			}
		}
	}
}
