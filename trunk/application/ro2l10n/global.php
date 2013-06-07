<?php

define('APP_NAME', '仙境传说2 本地化');

define('APP_TIMEZONE', 'Asia/Shanghai');

session_start();

define('APP_PAGER_SIZE', 10);
define('APP_PAGER_COUNT', 10);

define('DICT_SHOW_COUNT', 100);

// 账号状态
define('USER_STATE_DEFAULT', 0);	// 正常
define('USER_STATE_LOCK', 1);		// 锁定

// 映射状态
define('MAPPING_STATE_TW', 10);		// 待翻译
define('MAPPING_STATE_TD', 11);		// 翻译中
define('MAPPING_STATE_TB', 12);		// 翻译退回
define('MAPPING_STATE_PW', 20);		// 待校对
define('MAPPING_STATE_PD', 21);		// 校对中
define('MAPPING_STATE_PB', 22);		// 校对退回
define('MAPPING_STATE_AW', 30);		// 待审核
define('MAPPING_STATE_AD', 31);		// 审核中
define('MAPPING_STATE_AB', 32);		// 审核退回
define('MAPPING_STATE_CP', 100);	// 完成

// 配置
// - ACTION 设置
$GLOBALS['CONFIG']['ACTION_METHOD'] = array(
	'index_login' => '',
	'index_logout' => '',
	'index_index' => array('*'),
	'index_navg' => array('*'),

	'index_home' => array('*'),

	'user_individuation' => array('*'),
	'user_editpwd' => array('*'),

	'localization_list' => array('*'),
	'localization_occ' => array('*'),
	'localization_mytask' => array('*'),
	'localization_mytaskkey' => array('*'),
	'localization_mytasklist' => array('*'),
	'localization_mytaskdo' => array('*'),
	'localization_importtask' => array('*'),
	'localization_exporttask' => array('*'),

	'dict_list' => array('super', 'dict'),
	'dict_edit' => array('super', 'dict'),
	'dict_delete' => array('super', 'dict'),
	'dict_import' => array('super', 'dict'),
	'dict_export' => array('super', 'dict'),
	'dict_clear' => array('super', 'dict'),
	'dict_search' => array('*'),

	'file_list' => array('super'),
	'file_edit' => array('super'),
	'file_delete' => array('super'),
	'file_import' => array('super'),
	'file_export' => array('super'),
	'file_refresh' => array('super'),

	'autoupdate_list' => array('super'),
	'autoupdate_operate' => array('super'),
	'autoupdate_setting' => array('super'),
	'autoupdate_info' => '',
	'autoupdate_file' => '',

	'user_list' => array('super'),
	'user_edit' => array('super'),
	'user_delete' => array('super'),
	'user_refreshmapping' => array('super'),

	'notice_list' => array('super'),
	'notice_edit' => array('super'),
	'notice_delete' => array('super'),

	'system_info' => array('super')
);
