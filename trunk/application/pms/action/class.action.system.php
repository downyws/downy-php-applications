<?php
class ActionSystem extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodDetail()
	{
		$commonObj = Factory::getModel('common');
		$filehelper = Factory::loadLibrary('filehelper');
		$filehelper = new Filehelper();

		$info = array();
		
		$info['os_version'] = PHP_OS;
		$info['os_time'] = time();
		
		$info['apache_version'] = apache_get_version();
		$info['apache_version'] = substr(
			$info['apache_version'], 
			strpos($info['apache_version'], '/') + 1, 
			strpos($info['apache_version'], ' ') - strpos($info['apache_version'], '/')
		);

		$info['mysql_version'] = $commonObj->fetchMySqlVersion();

		$info['php_version'] = PHP_VERSION;

		$info['app_path'] = APP_DIR;
		$info['app_name'] = APP_NAME;
		$info['app_file_size'] = $filehelper->getDirSize(APP_DIR);
		$info['app_db_size'] = $commonObj->fetchDbSize();

		$this->assign('info', $info);
	}
}
