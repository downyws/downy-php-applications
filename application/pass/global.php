<?php

define('ADMIN_ID', 1);

define('APP_NAME', 'Downy Pass');

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

define('APP_PAGEE_SIZE', 15);
define('APP_PAGER_COUNT', 10);

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

// 类型
// - InOut
define('INOUT_TPYE_IN', 1);		// 登录
define('INOUT_TPYE_OUT', 2);	// 登出

// Support
define('SUPPORT_QUESTION', 0);			// 疑问
define('SUPPORT_LOST_ACCOUNT', 1);		// 忘记账号
define('SUPPORT_LOGIN_QUESTION', 2);	// 登录疑问

// 配置
// - ACTION 设置
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
		'NOT_LOGIN' => array('login', 'logout', 'register', 'active'),
		'RUN_LONG_TIME' => array(),
		'MYACTIVITY' => array('PAGE_SIZE' => 10)
	),
	'connect' => array(
		'NOT_LOGIN' => array('login'),
		'RUN_LONG_TIME' => array()
	),
	'support' => array(
		'NOT_LOGIN' => array('ask', 'login', 'recover', 'resetpassword'),
		'RUN_LONG_TIME' => array()
	)
);
// - MODEL 设置
$GLOBALS['CONFIG']['MODEL_OPTIONS'] = array(
	'member' => array(
		'REPORT' => array('CATCH_KEY' => 'member_report', 'CATCH_TIME' => 1800, 'SCOPE' => 8640000, 'LIMIT' => 1000)
	)
);
// - 登录验证码配置
$GLOBALS['CONFIG']['LOGIN_CAPTCHA_OPTIONS'] = array('COUNT' => 3, 'TIME' => 300, 'KEY' => 'captcha_login/');
// - 注册验证码配置
$GLOBALS['CONFIG']['REGISTER_CAPTCHA_OPTIONS'] = array('COUNT' => 0, 'TIME' => 300, 'KEY' => 'captcha_register/');
// - 手机绑定验证码
$GLOBALS['CONFIG']['BIND_MOBILE_OPTIONS'] = array('KEY' => 'bind_mobile/', 'INTERVAL' => 300, 'EXPIRY' => 1800, 'TRY_COUNT' => 10, 'TRY_TIME' => 600);
// - 邮箱绑定验证码
$GLOBALS['CONFIG']['BIND_EMAIL_OPTIONS'] = array('KEY' => 'bind_email/', 'INTERVAL' => 180, 'EXPIRY' => 1800, 'TRY_COUNT' => 10, 'TRY_TIME' => 600);
// - 用户支持配置
$GLOBALS['CONFIG']['SUPPORT_ASK_OPTIONS'] = array('KEY' => 'support/', 'SUBMIT_INTERVAL' => 600, 'SUBMIT_COUNT' => 3);
// - 找回密码配置
$GLOBALS['CONFIG']['LOST_PASSWORD_OPTIONS'] = array(
	'KEY' => 'lost_pwd/', 'TRY_TIME' => 900, 'TRY_COUNT' => 30,
	'RESET_KEY' => 'reset_pwd/', 'RESET_TIME' => 300,
	'EMAIL_INTERVAL' => 180, 'EMAIL_EXPIRY' => 1800, 'EMAIL_KEY' => 'log_pwd_email/',
	'MOBILE_INTERVAL' => 300, 'MOBILE_EXPIRY' => 1800, 'MOBILE_KEY' => 'log_pwd_mobile/'
);

// 性别
$GLOBALS['SEX'] = array('OTHER' => 0, 'MALE' => 1, 'FEMALE' => 2);
// 血型
$GLOBALS['BLOOD'] = array('OTHER' => 0, 'A' => 1, 'B' => 2, 'AB' => 3, 'O' => 4);
// 隐私
$GLOBALS['PRIVACY'] = array(
	'TYPE' => array('ALL' => 1, 'SELF' => 2),
	'DEFAULT' => array('name' => -1, 'portrait' => -1, 'email' => 2, 'mobile' => 2, 'sex' => 2, 'birthday' => 2, 'blood' => 2, 'sign' => -1)
);
// 保密问题
$GLOBALS['QANDA'] = array('COUNT' => 3);
