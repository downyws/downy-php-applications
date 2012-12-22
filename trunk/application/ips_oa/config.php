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
	'DBNAME' => 'ips',
	'CHARSET' => 'utf8',
	'PREFIX' => 'ips_',
	'QUERY_LIMIT_BYTE' => '200000'
);

// 域名配置
define('DOMAIN_INSIDE', 'http://ips.oa.downy.org/');
define('DOMAIN_OUTSIDE', 'http://www.downy.org/');
define('DOMAIN_SURI', 'http://suri.org/');

// 短网址配置
$GLOBALS['CONFIG']['SHORT_URI'] = array
(
	'ERROR_PAGE' => DOMAIN_SURI . 'index.php?a=suri&m=error&code=1',
	'DISABLE_PAGE' => DOMAIN_SURI . 'index.php?a=suri&m=error&code=2'
);
