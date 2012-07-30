<?php
class Channel
{
	public $channel_id = 0;

	public $mail = null;
	
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
		$this->run_time['start'] = microtime();

		$this->task_obj['single'] = Factory::getModel('tasksingle');
		$this->task_obj['multi'] = Factory::getModel('taskmulti');
	}

	public function __destruct()
	{
		$this->run_time['end'] = microtime();

		$date = explode(' ', $this->run_time['start']);

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
		$log = date('Y-m-d H:i:s', $date[1]) . "\n" . json_encode($data) . "\n" . json_encode($this->errors) . "\n\n";

		// 保存日志
		$path = APP_DIR_LOG . 'channel/' . date('Ym', $date[1]) . '/';
		$file = date('d', $date[1]) . '_' . $this->channel_id . '.txt';
		!is_dir($path) && mkdir($path, 0755, true);
		$handle = fopen($path . $file, 'a');
		fwrite($handle, $log);
		fclose($handle);
	}

	public function run()
	{
		throw new Exception('Subclass not exists run.');
	}
}
