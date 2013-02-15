<?php
function blood_format($value)
{
	switch($value)
	{
		case $GLOBALS['BLOOD']['A']: $value = 'A型'; break;
		case $GLOBALS['BLOOD']['B']: $value = 'B型'; break;
		case $GLOBALS['BLOOD']['AB']: $value = 'AB型'; break;
		case $GLOBALS['BLOOD']['O']: $value = 'O型'; break;
		default: 
			if(array_key_exists($value, $GLOBALS['BLOOD']))
			{
				blood_format($GLOBALS['BLOOD'][$value]);
				exit;
			}
			$value = '其他'; 
			break;
	}
	echo $value;
}
function blood_key($value)
{
	$keys = array_flip($GLOBALS['BLOOD']);
	echo array_key_exists($value, $keys) ? $keys[$value] : '';
}

function sex_format($value)
{
	switch($value)
	{
		case $GLOBALS['SEX']['MALE']: $value = '男'; break;
		case $GLOBALS['SEX']['FEMALE']: $value = '女'; break;
		default: 
			if(array_key_exists($value, $GLOBALS['SEX']))
			{
				sex_format($GLOBALS['SEX'][$value]);
				exit;
			}
			$value = '其他'; 
			break;
	}
	echo $value;
}
function sex_key($value)
{
	$keys = array_flip($GLOBALS['SEX']);
	echo array_key_exists($value, $keys) ? $keys[$value] : '';
}

function privacy_format($value)
{
	switch($value)
	{
		case $GLOBALS['PRIVACY']['TYPE']['SELF']: $value = '仅自己'; break;
		default: 
			if(array_key_exists($value, $GLOBALS['PRIVACY']['TYPE']))
			{
				privacy_format($GLOBALS['PRIVACY']['TYPE'][$value]);
				exit;
			}
			$value = '公开'; 
			break;
	}
	echo $value;
}
function privacy_key($value)
{
	$keys = array_flip($GLOBALS['PRIVACY']['TYPE']);
	echo array_key_exists($value, $keys) ? $keys[$value] : '';
}

function int_to_word($value)
{
	Factory::loadLibrary('stringhelper');
	$stringhelper = new StringHelper();
	echo $stringhelper->intToWord($value, 'cn');
}
