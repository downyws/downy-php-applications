<?php

class Front
{
	public static function dispatch()
	{
		$action = !empty($_GET['a']) ? strtolower($_GET['a']) : '';
		$method = !empty($_GET['m']) ? strtolower($_GET['m']) : '';
		$type = !empty($_GET['t']) ? strtolower($_GET['t']) : '';

		if($action && $method)
		{
			$actionPath = APP_DIR_ACTION . 'class.action.' . $action . '.php';

			if(file_exists($actionPath))
			{
				require_once($actionPath);

				$actionName = 'Action' . $action;
				$methodName = 'method' . $method . $type;
				if(class_exists($actionName))
				{
					$actionObj = new $actionName;
					if(is_callable(array($actionObj, $methodName)))
					{
						if(empty($type) && file_exists(APP_DIR_TEMPLATE . $action . '_' . $method . '.html'))
						{
							$actionObj->initTemplate(false);
							$actionObj->assign('action', $action);
							$actionObj->assign('method', $method);
							$actionObj->$methodName();
							$actionObj->render($action . '_' . $method . '.html');
						}
						else
						{
							$actionObj->$methodName();
						}
						exit;
					}
				}
			}
		}
		header('HTTP/1.1 404 Not Found');
		exit;
	}
}
