<?php

if(($handle = opendir(APP_DIR_FUNEXT)) !== false)
{
	// 遍历目录
	while(($file = readdir($handle)) !== false)
	{
		// 自动引入扩展函数
		if(strpos($file, 'function.ext.') !== false)
		{
			include_once(APP_DIR_FUNEXT . $file);
		}
	}
	closedir($handle);
}
