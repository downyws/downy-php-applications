<?php

define('APP_DIR', dirname(dirname(__FILE__)) . '/');
include_once(APP_DIR . 'config.php');
include_once(APP_DIR . 'global.php');

include_once('../../../framework/framework.core.php');

include_once(APP_DIR_ACTION . 'class.action.common.php');
include_once(APP_DIR_MODEL . 'class.model.common.php');

Front::dispatch();
