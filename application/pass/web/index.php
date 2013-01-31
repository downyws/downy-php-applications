<?php

define('APP_DIR', dirname(dirname(__FILE__)) . '/');
define('APP_DIR_CONNECT', APP_DIR . 'connect/');
define('APP_DIR_FUNEXT', APP_DIR . 'funext/');
define('APP_DIR_MSGCODE', APP_DIR . 'msgcode/');

include_once(APP_DIR . 'config.php');
include_once(APP_DIR . 'global.php');

include_once('../../../framework/framework.core.php');

include_once(APP_DIR_ACTION . 'class.action.common.php');
include_once(APP_DIR_MODEL . 'class.model.common.php');
include_once(APP_DIR_FUNEXT . 'funext.core.php');

Front::dispatch();
