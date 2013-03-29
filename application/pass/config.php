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

// 网站接入配置
$GLOBALS['CONFIG']['CONNECT'] = array
(
	'KAIXIN001' => array('A_KEY' => '4749569909151e5c28c33f030f9662d5', 'S_KEY' => 'bb72ce25f066c8e2c797a8e944b9522f'),
	'QQ' => array('A_KEY' => '100264185', 'S_KEY' => '58702073b12f343b071390b5b42d108a'),
	'RENREN' => array('A_KEY' => 'fbe208a41246410da5e7b8a8a05893b3', 'S_KEY' => 'e164a32435cf4774aaaba0619410608c'),
	'WEIBO' => array('A_KEY' => '20777358', 'S_KEY' => 'd85cb33680fb31fed6da0943701c7731')
);

// 邮箱配置
$GLOBALS['CONFIG']['EMAIL'] = array
(
	'SMTPAuth' => true,
	'Port' => '',
	'Host' => '',
	'Username' => '',
	'Password' => '',
	'From' => '',
	'FromName' => '',
	'IsHTML' => true,
	'CharSet' => 'UTF-8'
);

// CURL
$GLOBALS['CONFIG']['CURL'] = array
(
	'TIMEOUT' => 60,
	'ENCODING' => 'gzip, deflate',
	'PROXY' => false, 
	'PROXYPORT' => '',
	'COOKIE' => array('OPEN' => false, 'LOCK' => false, 'PATH' => ''),
	'REFERER' => array('OPEN' => false, 'LOCK' => false, 'VALUE' => ''),
	'USERAGENT' => array('OPEN' => false, 'VALUE' => '0'),
	'AUTO_REDIRECT_COUNT' => 5
);
