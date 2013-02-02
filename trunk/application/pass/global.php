<?php

define('ADMIN_ID', 1);

define('APP_NAME', 'Downy Pass');

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

// 状态
define('STATUS_DEFAULT', 1);		// 正常
define('STATUS_DISABEL', 2);		// 禁用（被动）
define('STATUS_UNACTIVE', 4);	// 未激活
define('STATUS_DISCARD', 8);	// 抛弃（主动）

// 提示类型
define('PROMPT_ERROR', 0);			// 错误
define('PROMPT_SUCCESS', 1);		// 成功
define('PROMPT_INFORMATION', 2);	// 信息
define('PROMPT_WARNING', 3);		// 警告

// 审核分类
define('CHECK_MEMBER_REGISTER', 1);		// 用户注册
define('CHECK_MEMBER_MODIFY_BASE', 2);	// 用户基础信息修改
define('CHECK_MEMBER_MODIFY_INFO', 3);	// 用户扩展信息修改

// 已验证信息
define('MEMBER_VERIFY_EMAIL',	0x1);	// 邮箱
define('MEMBER_VERIFY_MOBILE',	0x10);	// 手机

// 类型
// - InOut
define('INOUT_TPYE_IN', 1);		// 登录
define('INOUT_TPYE_OUT', 2);	// 登出

// QAndA
define('MAX_COUNT', 3);	// QA最大数量

// 配置
// - ACTION 设置，是否需要登录，是否需要长时间执行
$GLOBALS['CONFIG']['ACTION_OPTIONS'] = array(
	'common' => array(
		'NOT_LOGIN' => array('message'),
		'RUN_LONG_TIME' => array()
	),
	'index' => array(
		'NOT_LOGIN' => array('index', 'captcha', 'sethomepage', 'intl'),
		'RUN_LONG_TIME' => array()
	),
	'member' => array(
		'NOT_LOGIN' => array('login', 'logout', 'register', 'recover', 'active'),
		'RUN_LONG_TIME' => array()
	),
	'connect' => array(
		'NOT_LOGIN' => array('login'),
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
// 隐私
$GLOBALS['PRIVACY'] = array(
	'TYPE' => array('ALL' => 1, 'SELF' => 2),
	'DEFAULT' => array('name' => -1, 'portrait' => -1, 'email' => 2, 'mobile' => 2, 'sex' => 2, 'birthday' => 2, 'blood' => 2, 'sign' => -1)
);
