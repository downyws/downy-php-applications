<?php
class ModelCommon extends Model
{
	public function __construct()
	{
		parent::__construct($GLOBALS['CONFIG']['DB']);
	}

	public function getSessionUser($field = null)
	{
		if(empty($_SESSION['USER']))
		{
			return null;
		}
		else if(empty($field))
		{
			return $_SESSION['USER'];
		}
		else
		{
			return empty($_SESSION['USER'][$field]) ? null : $_SESSION['USER'][$field];
		}
	}

	public function delSessionUser()
	{
		unset($_SESSION['USER']);
	}

	public function setSessionUser($user)
	{
		if(empty($_SESSION['USER']))
		{
			$_SESSION['USER'] = $user;
		}
		else
		{
			$_SESSION['USER'] = array_merge($_SESSION['USER'], $user);
		}
	}
}
