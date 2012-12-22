<?php

// 调试模式
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 数据库配置
$GLOBALS['CONFIG']['DB'] = array
(
	'HOST' => 'localhost',
	'PORT' => '3306',
	'USERNAME' => 'root',
	'PASSWORD' => 'root',
	'DBNAME' => 'pass',
	'CHARSET' => 'utf8',
	'PREFIX' => 'pass_',
	'QUERY_LIMIT_BYTE' => '200000'
);
