<?php

define('APP_DIR', dirname(dirname(__FILE__)) . '/');
define('APP_DIR_CONNECT', APP_DIR . 'connect/');
define('APP_DIR_MESSAGE', APP_DIR . 'message/');

include_once(APP_DIR . 'config.php');
include_once(APP_DIR . 'global.php');

include_once('../../../framework/framework.core.php');

include_once(APP_DIR_ACTION . 'class.action.common.php');
include_once(APP_DIR_MODEL . 'class.model.common.php');
include_once(APP_DIR_TEMPLATE . 'class.smarty.ext.function.php');

Front::dispatch();
