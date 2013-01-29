<?php

// 调试模式
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 数据库配置
$GLOBALS['CONFIG']['DB'] = array(
	'HOST' => 'localhost',
	'PORT' => '3306',
	'USERNAME' => '',
	'PASSWORD' => '',
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
$GLOBALS['CONFIG']['SHORT_URI'] = array(
	'ERROR_PAGE' => DOMAIN_SURI . 'index.php?a=suri&m=error&code=1',
	'DISABLE_PAGE' => DOMAIN_SURI . 'index.php?a=suri&m=error&code=2'
);

// 通道参数配置
$GLOBALS['CONFIG']['CHANGE'] = array(
	'CHANGE_1' => array(
		'single' => 10,
		'multi' => 50
		'SMTPAuth' => true;
		'Port' => 25;
		'Host' => 'smtp.qq.com';
		'Username' => '';
		'Password' => '';
		'From' => '';
		'FromName' => '';
		'IsHTML' => true;
	),
	'CHANGE_2' => array(
		'single' => 10,
		'multi' => 50
		'SMTPAuth' => true;
		'SMTPSecure' => 'ssl';
		'Port' => 465;
		'Host' => 'smtp.gmail.com';
		'Username' => '';
		'Password' => '';
		'From' => '';
		'FromName' => '';
		'IsHTML' => true;
	),
	'CHANGE_3' => array(
		'single' => 10,
		'multi' => 50
		'SMTPAuth' => true;
		'Port' => 25;
		'Host' => 'smtp.qq.com';
		'Username' => '';
		'Password' => '';
		'From' => '';
		'FromName' => '';
		'IsHTML' => true;
	),
	'CHANGE_4' => array(	// http://www.chanyoo.cn/mod_static-view-sc_id-1111117.html
		'single' => 10,
		'multi' => 50
		'username' => '',
		'password' => '',
		'urlsend' => 'http://api.chanyoo.cn/utf8/interface/send_sms.aspx'
	)
);
