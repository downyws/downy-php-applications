<?php

define('ADMIN_ID', 1);

define('APP_NAME', 'Downy Pass');

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

// 状态
// - 会员
define('MEMBER_STATUS_DEFAULT', 1);		// 正常
define('MEMBER_STATUS_DISABEL', 2);		// 禁用
define('MEMBER_STATUS_DELETE', 4);		// 删除
define('MEMBER_STATUS_UNACTIVE', 8);	// 未激活
// - 第三方登录
define('CONNECT_STATUS_DEFAULT', 1);	// 正常
define('CONNECT_STATUS_DISABEL', 2);	// 禁用

// 提示类型
define('PROMPT_ERROR', 0);			// 错误
define('PROMPT_SUCCESS', 1);		// 成功
define('PROMPT_INFORMATION', 2);	// 信息
define('PROMPT_WARNING', 3);		// 警告

// 审核分类
define('CHECK_MEMBER_REGISTER', 1);	// 注册

// 已验证信息
define('MEMBER_VERIFY_EMAIL',	0x1);	// 邮箱
define('MEMBER_VERIFY_MOBILE',	0x10);	// 手机

// 类型
// - InOut
define('INOUT_TPYE_IN', 1);		// 登录
define('INOUT_TPYE_OUT', 2);	// 登出
// - QAndA
define('QANDA_TYPE_SYS', 1);		// 系统
define('QANDA_TYPE_USEFUL', 2);		// 已用
define('QANDA_TYPE_UNUSEFUL', 3);	// 未用


// 配置
// - ACTION 设置，是否需要登录，是否需要权限，是否需要长时间执行
$GLOBALS['CONFIG']['ACTION_OPTIONS'] = array(
	'index' => array(
		'NOT_LOGIN' => array('index', 'captcha'),
		'NOT_POWER' => array(),
		'RUN_LONG_TIME' => array()
	),
	'member' => array(
		'NOT_LOGIN' => array('login', 'logout', 'register', 'recover', 'active'),
		'NOT_POWER' => array('logout', 'home', 'setprofile'),
		'RUN_LONG_TIME' => array()
	),
	'connect' => array(
		'NOT_LOGIN' => array('login'),
		'NOT_POWER' => array(),
		'RUN_LONG_TIME' => array()
	)
);
// - 登录验证码配置
$GLOBALS['CONFIG']['LOGIN_CAPTCHA_OPTIONS'] = array('COUNT' => 3, 'TIME' => 300, 'KEY' => 'captcha_login/');
// - 注册验证码配置
$GLOBALS['CONFIG']['REGISTER_CAPTCHA_OPTIONS'] = array('COUNT' => 0, 'TIME' => 300, 'KEY' => 'captcha_register/');

// 性别
$GLOBALS['SEX'] = array('OTHER' => 0, 'MALE' => 1, 'FEMALE' => 2);
// 血型
$GLOBALS['BLOOD'] = array('OTHER' => 0, 'A' => 1, 'B' => 2, 'AB' => 3, 'O' => 4);