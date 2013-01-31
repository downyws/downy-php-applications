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
 * This msgcode prefix: 202
 * 
 */

$GLOBALS['MSGCODE']['C']['MCOT_GET_TOKEN_FAILED'] = '20201010';
$GLOBALS['MSGCODE']['M']['20201010'] = '获取关联账号授权失败。';

$GLOBALS['MSGCODE']['C']['MCOT_GET_OPENID_FAILED'] = '20201020';
$GLOBALS['MSGCODE']['M']['20201020'] = '获取用户身份失败。';

$GLOBALS['MSGCODE']['C']['MCOT_ACCOUNT_DISABLE'] = '20202010';
$GLOBALS['MSGCODE']['M']['20202010'] = '关联账号已被禁用。';

$GLOBALS['MSGCODE']['C']['MCOT_ACCOUNT_ERRSTA_TELA'] = '20202020';
$GLOBALS['MSGCODE']['M']['20202020'] = '关联账号状态错误，请联系管理员。';

$GLOBALS['MSGCODE']['C']['MCOT_ACCOUNT_NOEXIST'] = '20202030';
$GLOBALS['MSGCODE']['M']['20202030'] = '关联账号不存在。';
