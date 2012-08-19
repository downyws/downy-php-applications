<?php
class ActionSystem extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array();
	public $RUN_LONG_TIME = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodDetail()
	{
		Factory::loadLibrary('filehelper');
		Factory::loadLibrary('imagehelper');
		$commonObj = Factory::getModel('common');
		$filehelper = new FileHelper();
		$imagehelper = new ImageHelper();

		$info = array();
		
		$info['os_version'] = PHP_OS;
		$info['os_ip'] = $_SERVER['SERVER_ADDR'];
		$info['os_time'] = time();
		
		$info['web_version'] = apache_get_version();
		$info['web_safe_mode'] = (bool)ini_get('safe_mode');
		$info['web_safe_mode_gid'] = (bool)ini_get('safe_mode_gid');
		$info['web_upload_max_filesize'] = ini_get('upload_max_filesize');

		$info['mysql_version'] = $commonObj->fetchMySqlVersion();

		$info['php_version'] = PHP_VERSION;
		$info['php_gd_version'] = $imagehelper->getVersion();
		$info['php_socket'] = function_exists('fsockopen');
		$info['php_zlib'] = function_exists('gzclose');

		$info['app_path'] = APP_DIR;
		$info['app_name'] = APP_NAME;
		$info['app_file_size'] = $filehelper->getDirSize(APP_DIR);
		$info['app_db_size'] = $commonObj->fetchDbSize();
		$info['app_timezone'] = date_default_timezone_get();

		$this->assign('info', $info);
	}
}
