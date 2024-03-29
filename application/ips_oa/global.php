<?php

define('APP_NAME', '信息推送系统');

define('ADMIN_ID', 1);

define('CRONTAB_SALT', '001cd7b0808ebebb');

define('BATCH_IMPORT', 500);

define('TASK_TIMEOUT', 3600);
define('TASK_SCAN_RANGE', 15552000);	// 86400 * 30 * 6 天

define('LOG_DATA_TABLE_CHANNEL', 1);
define('LOG_DATA_TABLE_LOG', 2);
define('LOG_DATA_TABLE_SENDLIST', 3);
define('LOG_DATA_TABLE_SHORTURI', 4);
define('LOG_DATA_TABLE_TARGET', 5);
define('LOG_DATA_TABLE_TASKMULTI', 6);
define('LOG_DATA_TABLE_TASKSINGLE', 7);
define('LOG_DATA_TABLE_USER', 8);

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

define('APP_PAGER_SIZE', 15);
define('APP_PAGER_COUNT', 10);

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

$GLOBALS['CONFIG']['IS_DISABLE'] = array(
	'0' => '正常',
	'1' => '禁用',
);

$GLOBALS['CONFIG']['TASKSINGLE'] = array(
	'PV_IMAGE' => DOMAIN_OUTSIDE . '/images/pv.gif',
	'STATE' => array(
		'1' => '未发送',
		'2' => '发送中',
		'3' => '已发送',
		'4' => '取消',
		'5' => '发送失败'
	)
);

$GLOBALS['CONFIG']['TASKMULTI'] = array(
	'PV_IMAGE' => DOMAIN_OUTSIDE . '/images/pv.gif',
	'STATE' => array(
		'1' => '未发送',
		'2' => '发送中',
		'3' => '已发送',
		'4' => '取消',
		'5' => '未确认',
		'6' => '等待审核'
	),
	'SEND_LIST_STATE' => array(
		'1' => '未发送',
		'2' => '发送中',
		'3' => '已发送',
		'5' => '发送失败'
	)
);

$GLOBALS['CONFIG']['CHANNEL'] = array(
	'TYPE' => array(
		'1' => '邮件',
		'2' => '短信'
	)
);

$GLOBALS['CONFIG']['TARGET'] = array(
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
		'4' => 'short_uri',
		'5' => 'target',
		'6' => 'task_multi',
		'7' => 'task_single',
		'8' => 'user'
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
	'CHANNEL:EDIT' => array('NAME' => '通道编辑', 'ACTIONMETHOD' => array('channel:edit')),
	'TARGET:READ' => array('NAME' => '目标查看', 'ACTIONMETHOD' => array('target:list')),
	'TARGET:EDIT' => array('NAME' => '目标编辑', 'ACTIONMETHOD' => array('target:edit')),
	'SURI:READ' => array('NAME' => '短网址查看', 'ACTIONMETHOD' => array('suri:list')),
	'SURI:EDIT' => array('NAME' => '短网址编辑', 'ACTIONMETHOD' => array('suri:edit', 'suri:clear')),
	'USER:READ' => array('NAME' => '用户查看', 'ACTIONMETHOD' => array('user:list', 'user:detail')),
	'USER:EDIT' => array('NAME' => '用户编辑', 'ACTIONMETHOD' => array('user:edit')),
	'TASKMULTI:READ' => array('NAME' => '多任务查看', 'ACTIONMETHOD' => array('taskmulti:list', 'taskmulti:detail', 'taskmulti:sendlist')),
	'TASKMULTI:CANCEL' => array('NAME' => '多任务取消', 'ACTIONMETHOD' => array('taskmulti:cancel')),
	'TASKMULTI:EDITSELF' => array('NAME' => '多任务编辑（自己）', 'ACTIONMETHOD' => array('taskmulti:edit', 'taskmulti:importlist', 'taskmulti:clearlist', 'taskmulti:removesend', 'taskmulti:submitcheck')),
	'TASKMULTI:EDITALL' => array('NAME' => '多任务编辑（所有用户）', 'ACTIONMETHOD' => array('taskmulti:edit', 'taskmulti:importlist', 'taskmulti:clearlist', 'taskmulti:removesend', 'taskmulti:submitcheck')),
	'TASKMULTI:CHECK' => array('NAME' => '多任务审核', 'ACTIONMETHOD' => array('taskmulti:check')),
	'TASKSINGLE:READ' => array('NAME' => '单任务查看', 'ACTIONMETHOD' => array('tasksingle:list', 'tasksingle:detail')),
	'TASKSINGLE:CANCEL' => array('NAME' => '单任务取消', 'ACTIONMETHOD' => array('tasksingle:cancel', 'tasksingle:apicancel')),
	'TASKSINGLE:EDITSELF' => array('NAME' => '单任务编辑（自己）', 'ACTIONMETHOD' => array('tasksingle:edit')),
	'TASKSINGLE:EDITALL' => array('NAME' => '单任务编辑（所有用户）', 'ACTIONMETHOD' => array('tasksingle:edit'))
);
