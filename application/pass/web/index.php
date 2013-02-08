<?php

define('APP_DIR', dirname(dirname(__FILE__)) . '/');
define('APP_DIR_CONNECT', APP_DIR . 'connect/');
define('APP_DIR_FUNEXT', APP_DIR . 'funext/');
define('APP_DIR_MSGCODE', APP_DIR . 'msgcode/');
define('APP_DIR_UPLOAD', APP_DIR . 'web/upload/');
define('APP_DIR_UPLOAD_TEMP', APP_DIR . 'web/upload/temp/');
define('APP_DIR_UPLOAD_PORTRAIT', APP_DIR . 'web/upload/portrait/');

include_once(APP_DIR . 'config.php');
include_once(APP_DIR . 'global.php');

include_once('../../../framework/framework.core.php');

include_once(APP_DIR_ACTION . 'class.action.common.php');
include_once(APP_DIR_MODEL . 'class.model.common.php');
include_once(APP_DIR_FUNEXT . 'funext.core.php');

define('APP_URL_UPLOAD', APP_URL . 'upload/');
define('APP_URL_UPLOAD_TEMP', APP_URL . 'upload/temp/');
define('APP_URL_UPLOAD_PORTRAIT', APP_URL . 'upload/portrait/');

Front::dispatch();
