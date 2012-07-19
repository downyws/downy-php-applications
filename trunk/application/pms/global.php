<?php

define('APP_NAME', '信息推送系统');

define('LOG_DATA_TABLE_CHANNEL', 1);
define('LOG_DATA_TABLE_LOG', 2);
define('LOG_DATA_TABLE_SENDLIST', 3);
define('LOG_DATA_TABLE_SENDLISTDATA', 4);
define('LOG_DATA_TABLE_SHORTURI', 5);
define('LOG_DATA_TABLE_TARGET', 6);
define('LOG_DATA_TABLE_TASKMULTI', 7);
define('LOG_DATA_TABLE_TASKSINGLE', 8);
define('LOG_DATA_TABLE_USER', 9);

define('LOG_OPERATION_TYPE_INSERT', 1);
define('LOG_OPERATION_TYPE_DELETE', 2);
define('LOG_OPERATION_TYPE_UPDATE', 3);
define('LOG_OPERATION_TYPE_SELECT', 4);

define('PROMPT_ERROR', 0);
define('PROMPT_SUCCESS', 1);
define('PROMPT_INFORMATION', 2);
define('PROMPT_WARNING', 3);

define('SHORT_URI_ERROR_UNKNOW', 0);
define('SHORT_URI_ERROR_UNDEFINE', 1);
define('SHORT_URI_ERROR_DISABLE', 2);

define('SHORT_URI_TYPE_LINK', 1);
define('SHORT_URI_TYPE_FILE', 2);

define('CHANNEL_TYPE_EMAIL', 1);
define('CHANNEL_TYPE_SMS', 2);

define('APP_PAGEE_SIZE', 15);
define('APP_PAGER_COUNT', 10);

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

$GLOBALS['CONFIG']['IS_DISABLE'] = array(
	'0' => '正常',
	'1' => '禁用',
);

$GLOBALS['CONFIG']['TASKSINGLE'] = array(
	'STATE' => array(
		'1' => '未发送',
		'2' => '发送中',
		'3' => '已发送',
		'4' => '取消',
		'5' => '发送失败'
	)
);

$GLOBALS['CONFIG']['TASKMULTI'] = array(
	'STATE' => array(
		'1' => '未发送',
		'2' => '发送中',
		'3' => '已发送',
		'4' => '取消',
		'5' => '未确认',
		'6' => '等待审核'
	)
);

$GLOBALS['CONFIG']['CHANNEL'] = array(
	'TYPE' => array(
		'1' => '邮件',
		'2' => '短信'
	)
);

$GLOBALS['CONFIG']['SHORT_URI']['TYPE'] = array(
	'1' => '链接',
	'2' => '文件'
);

$GLOBALS['CONFIG']['LOG'] = array(
	'DATA_TABLE' => array(
		'1' => 'channel',
		'2' => 'log',
		'3' => 'send_list',
		'4' => 'send_list_data',
		'5' => 'short_uri',
		'6' => 'target',
		'7' => 'task_multi',
		'8' => 'task_single',
		'9' => 'user'
	),
	'OPERATION_TYPE' => array(
		'1' => 'INSERT',
		'2' => 'DELETE',
		'3' => 'UPDATE',
		'4' => 'SELECT'
	)
);

$GLOBALS['CONFIG']['POWER'] = array(
	'SYSTEM:READ' => array('NAME' => '系统查看', 'ACTIONMETHOD' => array('system:detail')),
	'LOG:READ' => array('NAME' => '日志查看', 'ACTIONMETHOD' => array('log:list')),
	'CHANNEL:READ' => array('NAME' => '通道查看', 'ACTIONMETHOD' => array('channel:list')),
	'CHANNEL:EDIT' => array('NAME' => '通道编辑', 'ACTIONMETHOD' => array('channel:edit', 'channel:add')),
	'SURI:READ' => array('NAME' => '短网址查看', 'ACTIONMETHOD' => array('suri:list')),
	'SURI:EDIT' => array('NAME' => '短网址编辑', 'ACTIONMETHOD' => array('suri:edit', 'suri:add')),
	'USER:READ' => array('NAME' => '用户查看', 'ACTIONMETHOD' => array('user:list', 'user:detail')),
	'USER:EDIT' => array('NAME' => '用户编辑', 'ACTIONMETHOD' => array('user:edit', 'user:add')),
	'TASKMULTI:READ' => array('NAME' => '多任务查看', 'ACTIONMETHOD' => array('taskmulti:list', 'taskmulti:detail')),
	'TASKMULTI:CANCEL' => array('NAME' => '多任务取消', 'ACTIONMETHOD' => array()),
	'TASKMULTI:EDITSELF' => array('NAME' => '多任务编辑（自己）', 'ACTIONMETHOD' => array()),
	'TASKMULTI:EDITALL' => array('NAME' => '多任务编辑（所有用户）', 'ACTIONMETHOD' => array()),
	'TASKMULTI:CHECK' => array('NAME' => '多任务审核', 'ACTIONMETHOD' => array()),
	'TASKSINGLE:READ' => array('NAME' => '单任务查看', 'ACTIONMETHOD' => array('tasksingle:list', 'tasksingle:detail')),
	'TASKSINGLE:CANCEL' => array('NAME' => '单任务取消', 'ACTIONMETHOD' => array()),
	'TASKSINGLE:EDITSELF' => array('NAME' => '单任务编辑（自己）', 'ACTIONMETHOD' => array()),
	'TASKSINGLE:EDITALL' => array('NAME' => '单任务编辑（所有用户）', 'ACTIONMETHOD' => array())
);
