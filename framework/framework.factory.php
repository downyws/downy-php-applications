<?php

class Factory
{
	public static $objs = array();

	public static function getModel($name, $param = null)
	{
		if(!class_exists($name))
		{
			require_once(APP_DIR_MODEL . 'class.model.' . $name . '.php');
		}

		$className = 'Model' . $name;
		if(empty($param))
		{
			if(empty(self::$objs[$className]))
			{
				self::$objs[$className] = new $className;
			}
		}
		else
		{
			$hash = md5(serialize($param));
			$key = $className . '/' . $hash;
			if(empty(self::$objs[$key]))
			{
				self::$objs[$key] = new $className($param);
			}
			$className = $key;
		}

		return self::$objs[$className];
	}

	public static function loadLibrary($name)
	{
		if(!class_exists($name))
		{
			require_once(LIBRARY_DIR . $name . '/library.' . $name . '.php');
		}
	}
}
