<?php

// Login
define('MEMBER_LOGIN_UNACTIVE',		'020101010');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_UNACTIVE] = '账号还未激活。';
define('MEMBER_LOGIN_DISABEL',		'020101020');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_DISABEL] = '账号已被禁用。';
define('MEMBER_LOGIN_DELETE',		'020101030');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_DELETE] = '账号已被删除。';
define('MEMBER_LOGIN_UNKNOWSTATUS',	'020101040');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_UNKNOWSTATUS] = '账号状态错误，请联系管理员。';
define('MEMBER_LOGIN_NOTEXIST',		'020101050');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_NOTEXIST] = '账号或密码错误。';
define('MEMBER_LOGIN_ACCOUNTEMPTY',	'020101060');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_ACCOUNTEMPTY] = '请输入您的账号。';
define('MEMBER_LOGIN_PASSWORDEMPTY','020102010');
$GLOBALS['MESSAGE'][MEMBER_LOGIN_PASSWORDEMPTY] = '请输入您的密码。';

// Register
define('MEMBER_REGISTER_FNAMEEMPTY',			'020201010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_FNAMEEMPTY] = '姓氏不能为空。';
define('MEMBER_REGISTER_FNAMELENMAX',			'020201020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_FNAMELENMAX] = '您的姓氏好像太长了吧？';
define('MEMBER_REGISTER_FNAMEERROR',			'020201030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_FNAMEERROR] = '确定您输入的姓氏准确无误吗？';
define('MEMBER_REGISTER_LNAMEEMPTY',			'020202010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_LNAMEEMPTY] = '名字不能为空。';
define('MEMBER_REGISTER_LNAMELENMAX',			'020202020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_LNAMELENMAX] = '您的名字好像太长了吧？';
define('MEMBER_REGISTER_LNAMEERROR',			'020202030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_LNAMEERROR] = '确定您输入的名字准确无误吗？';
define('MEMBER_REGISTER_ACCOUNTEMPTY',			'020203010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_ACCOUNTEMPTY] = '账号不能为空。';
define('MEMBER_REGISTER_ACCOUNTCHRERROR',		'020203020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_ACCOUNTCHRERROR] = '请勿使用除字母、数字和英文句号外的其他字符。';
define('MEMBER_REGISTER_ACCOUNTLENERROR',		'020203030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_ACCOUNTLENERROR] = '输入的字符数应在 6 到 20 之间。';
define('MEMBER_REGISTER_ACCOUNTLENGTE8ERROR',	'020203040');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_ACCOUNTLENGTE8ERROR] = '很抱歉，长度在 8 位以上的账号应至少含有 1 个字母。';
define('MEMBER_REGISTER_ACCOUNTEXITS',			'020203050');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_ACCOUNTEXITS] = '该用户名已存在。改用其他用户名？';
define('MEMBER_REGISTER_PASSWORDEMPTY',			'020204010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_PASSWORDEMPTY] = '密码不能为空。';
define('MEMBER_REGISTER_PASSWORDLENERROR',		'020204020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_PASSWORDLENERROR] = '密码过短容易被猜到。请使用至少包含 6 个字符的密码。';
define('MEMBER_REGISTER_PASSWORDLENMAX',		'020204030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_PASSWORDLENMAX] = '最多只能包含 30 个字符。';
define('MEMBER_REGISTER_PASSWORDCFMEMPTY',		'020205010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_PASSWORDCFMEMPTY] = '请在此处确认您的密码。';
define('MEMBER_REGISTER_PASSWORDCFMNEQ',		'020205020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_PASSWORDCFMNEQ] = '两个密码不匹配。请重新输入。';
define('MEMBER_REGISTER_EMAILEMPTY',			'020206010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_EMAILEMPTY] = '邮箱不能为空。';
define('MEMBER_REGISTER_EMAILMOREAT',			'020206020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_EMAILMOREAT] = '看来您很喜欢使用 @ 符号，但邮箱地址中只能使用一个。';
define('MEMBER_REGISTER_EMAILERROR',			'020206030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_EMAILERROR] = '邮箱格式错误。';
define('MEMBER_REGISTER_EMAILEXITSVERIFY',		'020206040');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_EMAILEXITSVERIFY] = '该邮箱已被其他用户激活使用。改用其他邮箱？';
define('MEMBER_REGISTER_MOBILEEMPTY',			'020207010');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_MOBILEEMPTY] = '手机号码不能为空。';
define('MEMBER_REGISTER_MOBILERROR',			'020207020');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_MOBILERROR] = '该手机号码的格式无法识别。';
define('MEMBER_REGISTER_MOBILEEXITSVERIFY',		'020207030');
$GLOBALS['MESSAGE'][MEMBER_REGISTER_MOBILEEXITSVERIFY] = '该手机号码已被其他用户激活使用。改用其他手机号吗？';