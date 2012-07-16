<?php

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

define('CHANNEL_TYPE_EMAIL', 1);
define('CHANNEL_TYPE_SMS', 2);

define('APP_PAGEE_SIZE', 15);
define('APP_PAGER_COUNT', 10);

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

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
	'TRANSLATE' => array(
		'SYSTEM:READ' => '系统查看',
		'SYSTEM:EDIT' => '系统设置',
		'CHANNEL:READ' => '通道查看',
		'CHANNEL:EDIT' => '通道编辑',
		'USER:READ' => '用户查看',
		'USER:EDIT' => '用户编辑',
		'SURI:READ' => '短网址查看',
		'SURI:EDIT' => '短网址编辑',
		'TASKMULTI:READ' => '多任务查看',
		'TASKMULTI:CHECK' => '多任务审核',
		'TASKMULTI:READ' => '多任务取消',
		'TASKMULTI:READ' => '多任务编辑（所有用户）',
		'TASKMULTI:READ' => '多任务编辑（个人）',
		'TASKSINGLE:READ' => '单任务查看',
		'TASKSINGLE:CANCEL' => '单任务取消',
		'TASKSINGLE:SEND' => '单任务发送',
		'TASKSINGLE:EDIT' => '单任务编辑',
		'SEND:RUN' => '定时脚本',
		'LOG:READ' => '日志查看'
	),
	'ACTIONMETHOD' => array(
		'system:detail' => 'SYSTEM:READ',
		'channel:list' => 'CHANNEL:READ',
		'channel:detail' => 'CHANNEL:READ',
		'user:list' => 'USER:READ',
		'suri:list' => 'SURI:READ',
		'taskmulti:list' => 'TASKMULTI:READ',
		'tasksingle:list' => 'TASKSINGLE:READ',
		'log:list' => 'LOG:READ'
	)
);
