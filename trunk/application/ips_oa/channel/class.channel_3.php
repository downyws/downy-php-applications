<?php
class Channel_3 extends Channel
{
	public function __construct()
	{
		parent::__construct();
		$this->channel_id = 3;
		$config = $GLOBALS['CONFIG']['CHANGE']['CHANGE_' . $this->channel_id];

		// 参数配置
		$this->task_count['single'] = $config['single'];
		$this->task_count['multi'] = $config['multi'];

		// 初始化邮件
		$this->run = 'runBase';
		Factory::loadLibrary('phpmailer');
		$this->type_obj = new PHPMailer(true);
		$this->type_obj->IsSMTP();
		$this->type_obj->SMTPAuth = $config['SMTPAuth'];
		$this->type_obj->Port = $config['Port'];
		$this->type_obj->Host = $config['Host'];
		$this->type_obj->Username = $config['Username'];
		$this->type_obj->Password = $config['Password'];
		$this->type_obj->From = $config['From'];
		$this->type_obj->FromName = $config['FromName'];
		$this->type_obj->IsHTML($config['IsHTML']);
	}
}
