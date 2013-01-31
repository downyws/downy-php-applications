<?php
function MCGetC($key)
{
	return $GLOBALS['MSGCODE']['C'][$key];
}

function MCGetM($key)
{
	$key = is_numeric($key) ? $key : MCGetC($key);
	return $GLOBALS['MSGCODE']['M'][$key];
}
