<?php
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
		$this->run = 'runBase';
		Factory::loadLibrary('phpmailer');
		$this->type_obj = new PHPMailer(true);
		$this->type_obj->IsSMTP();
		$this->type_obj->SMTPAuth = true;
		$this->type_obj->Port = 25;
		$this->type_obj->Host = "";
		$this->type_obj->Username = "";
		$this->type_obj->Password = "";
		$this->type_obj->From = "";
		$this->type_obj->FromName = "";
		$this->type_obj->IsHTML(true);
	}
}
