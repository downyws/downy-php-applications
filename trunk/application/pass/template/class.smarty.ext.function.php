<?php
function blood_format($value)
{
	switch($value)
	{
		case $GLOBALS['BLOOD']['A']: $value = 'A型'; break;
		case $GLOBALS['BLOOD']['B']: $value = 'B型'; break;
		case $GLOBALS['BLOOD']['AB']: $value = 'AB型'; break;
		case $GLOBALS['BLOOD']['O']: $value = 'O型'; break;
		default: $value = '其他'; break;
	}
	echo $value;
}
function sex_format($value)
{
	switch($value)
	{
		case $GLOBALS['SEX']['MALE']: $value = '男'; break;
		case $GLOBALS['SEX']['FEMALE']: $value = '女'; break;
		default: $value = '其他'; break;
	}
	echo $value;
}
function privacy_format($value)
{
	switch($value)
	{
		case $GLOBALS['SEX']['SELF']: $value = '仅自己'; break;
		default: $value = '公开'; break;
	}
	echo $value;
}
