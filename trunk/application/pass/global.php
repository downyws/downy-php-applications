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

// 已验证信息
define('MEMBER_VERIFY_EMAIL',	0x1);	// 邮箱
define('MEMBER_VERIFY_MOBILE',	0x10);	// 手机

// ACTION 设置，是否需要登录，是否需要权限，是否需要长时间执行
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

// 配置
$GLOBALS['OPTIONS'] = array
(
	// 登录失败后显示验证码
	'LOGIN_FAILED_CAPTCHA' => array('COUNT' => 3, 'TIME' => 300),
	// 验证码连续失败次数
	'CAPTCHA_TRY_COUNT' => array('COUNT' => 100, 'TIME' => 900)
);
