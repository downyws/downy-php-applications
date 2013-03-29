<?php
/*
 * Msg mean: String
 * Code mean: [Type][Class][Category][Id][Status] => ABBCCDDE
 *		Type: Action => 1, Model => 2
 *		Class: number
 *		Category: number
 *		Id: number
 *		Status: false => 0, true => 1
 * 
 * This msgcode prefix: 105
 * 
 */

$GLOBALS['MSGCODE']['C']['ASUP_TRY_MAX_COUNT'] = '10501010';
$GLOBALS['MSGCODE']['M']['10501010'] = '您已连续提交多次，请稍后在提交。';

$GLOBALS['MSGCODE']['C']['ASUP_ERROR_MAX_COUNT'] = '10501020';
$GLOBALS['MSGCODE']['M']['10501020'] = '您已连续失败多次，请稍后在提交。';

$GLOBALS['MSGCODE']['C']['ASUP_ADD_STEP_ERROR'] = '10502010';
$GLOBALS['MSGCODE']['M']['10502010'] = '表单错误，请刷新页面。';

$GLOBALS['MSGCODE']['C']['ASUP_ADD_SUCCESS'] = '10503011';
$GLOBALS['MSGCODE']['M']['10503011'] = '提交成功。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_STEP_ERROR'] = '10504010';
$GLOBALS['MSGCODE']['M']['10504010'] = '表单错误，请刷新页面。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_ACCOUNT_CNT_EMPTY'] = '10505010';
$GLOBALS['MSGCODE']['M']['10505010'] = '请输入账号。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_ACCOUNT_ERROR'] = '10505020';
$GLOBALS['MSGCODE']['M']['10505020'] = '账号输入错误。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_ANSWER_ERROR'] = '10506010';
$GLOBALS['MSGCODE']['M']['10506010'] = '您回答的保密问题有错误答案。';

$GLOBALS['MSGCODE']['C']['ASUP_KEY_ERROR'] = '10507010';
$GLOBALS['MSGCODE']['M']['10507010'] = '校验码可能已过期，请重新找回密码。';

$GLOBALS['MSGCODE']['C']['ASUP_RESET_PWD_SUCCESS'] = '10508011';
$GLOBALS['MSGCODE']['M']['10508011'] = '密码修改成功。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_MOBILE_CNT_EMPTY'] = '10509010';
$GLOBALS['MSGCODE']['M']['10509010'] = '请输入手机。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_EMAIL_CNT_EMPTY'] = '10509020';
$GLOBALS['MSGCODE']['M']['10509020'] = '请输入邮箱。';

$GLOBALS['MSGCODE']['C']['ASUP_PLZ_WAIT'] = '10510010';
$GLOBALS['MSGCODE']['M']['10510010'] = '请稍后尝试。';

$GLOBALS['MSGCODE']['C']['ASUP_ACCOUNT_OR_MOBILE_ERR'] = '10511010';
$GLOBALS['MSGCODE']['M']['10511010'] = '账号或手机错误。';

$GLOBALS['MSGCODE']['C']['ASUP_ACCOUNT_OR_EMAIL_ERR'] = '10511020';
$GLOBALS['MSGCODE']['M']['10511020'] = '账号或邮箱错误。';

$GLOBALS['MSGCODE']['C']['ASUP_RECOVER_CAPTCHA_ERROR'] = '10512010';
$GLOBALS['MSGCODE']['M']['10512010'] = '验证码错误或者已过期。';
