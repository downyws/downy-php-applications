<?php

// µ÷ÊÔÄ£Ê½
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Êý¾Ý¿âÅäÖÃ
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

// ¶ÌÍøÖ·ÅäÖÃ
$GLOBALS['CONFIG']['SHORT_URI'] = array
(
	'DOMAIN' => 'http://pms.oa.org/',
	'ERROR_PAGE' => 'http://pms.oa.org/index.php?a=suri&m=error&code=1'
);
