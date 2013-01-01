<?php
// Code mean: [model][function][type][no.][status] => AABBCCDDE

define('PAGE_NOT_EXISTS',	'030101010');
$GLOBALS['MESSAGE'][PAGE_NOT_EXISTS] = '您访问的页面不存在。';

define('GET_TOKEN_FAILED',	'030201010');
$GLOBALS['MESSAGE'][GET_TOKEN_FAILED] = '获取第三方授权失败。';
define('GET_OPENID_FAILED',	'030201020');
$GLOBALS['MESSAGE'][GET_OPENID_FAILED] = '获取用户身份失败。';

define('CONNECT_LOGIN_DISABEL',		'030301010');
$GLOBALS['MESSAGE'][CONNECT_LOGIN_DISABEL] = '关联账号已被禁用。';
define('CONNECT_LOGIN_UNKNOWSTATUS',	'030301020');
$GLOBALS['MESSAGE'][CONNECT_LOGIN_UNKNOWSTATUS] = '关联账号状态错误，请联系管理员。';
