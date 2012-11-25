<?php

class Logs
{
	public function message($type, $log_info)
	{
		$path = APP_DIR_LOGS . $type . '/' . date('Y_m_d') . '.log';
		$this->mkdir($path);
		$trace = debug_backtrace();
		array_shift($trace);
		$caller = array_shift($trace);
		$row = date('Y-m-d H:i:s ') . 
				(isset($caller['class']) ? $caller['class'] . '::' : '') . 
				(isset($caller['function']) ? $caller['function'] . ' ' : '') .
				(is_array($log_info) ? join(' ', $log_info) : $log_info) . "\n";
		file_put_contents($path, $row, LOCK_EX | FILE_APPEND);
	}

	public function attachment($type, $content)
	{
		do
		{
			$filename = date('Y_m_d_H_i_s_') . substr(round(microtime(1) * 1000), -3) . '_' . mt_rand();
			$path = APP_DIR_LOGS . $type . '/attachment/' . date('Y-m-d') .'/' . $filename;
			$this->mkdir($path);
		}
		while(file_exists($path));

		if(!is_string($content))
		{
			$content = var_export($content, 1);
		}

		file_put_contents($path, $content);

		return $filename;
	}

	protected function mkdir($path)
	{
		$dir = dirname($path);
		if(is_dir($dir))
		{
			return true;
		}
		else if(file_exists($dir))
		{
			return false;
		}
		return mkdir($dir, 0777, true);
	}
}
