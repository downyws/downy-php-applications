<?php

// ����ģʽ
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ���ݿ�����
$GLOBALS['CONFIG']['DB'] = array
(
	'HOST' => 'localhost',
	'PORT' => '3306',
	'USERNAME' => 'root',
	'PASSWORD' => 'root',
	'DBNAME' => 'pms',
	'CHARSET' => 'utf8',
	'PREFIX' => 'pms_'
);

// ����ַ����
$GLOBALS['CONFIG']['SHORT_URI'] = array
(
	'DOMAIN' => 'http://pms.oa.org/',
	'ERROR_PAGE' => 'http://pms.oa.org/index.php?a=suri&m=error&code=1'
);
