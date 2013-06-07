<?php
class ActionCommon extends Action
{
	public function __construct()
	{
		parent::__construct();

		$params = $this->_submit->obtain($_REQUEST, array(
			'a' => array(array('format', 'trim')),
			'm' => array(array('format', 'trim')),
			't' => array(array('format', 'trim'))
		));

		if(!isset($GLOBALS['CONFIG']['ACTION_METHOD'][$params['a'] . '_' . $params['m']]))
		{
			echo 'ACTION METHOD NULL';
			exit;
		}

		$config = $GLOBALS['CONFIG']['ACTION_METHOD'][$params['a'] . '_' . $params['m']];
		if(!empty($config))
		{
			$commonObj = Factory::getModel('common');
			// 未登录
			if(!$commonObj->getSessionUser())
			{
				if($params['t'] == 'ajax')
				{
					$this->jsonout(array('state' => false, 'message' => '请先登录。'));
				}
				else
				{
					$this->redirect('/index.php?a=index&m=login');
				}
			}

			// 权限检查
			if(!in_array('*', $config))
			{
				$user = $commonObj->getSessionUser();
				$exists = false;
				foreach($config as $v)
				{
					if(!empty($user[$v]))
					{
						$exists = true;
						break;
					}
				}
				if(!$exists)
				{
					if($params['t'] == 'ajax')
					{
						$this->jsonout(array('state' => false, 'message' => '没有权限。'));
					}
					else
					{
						$this->redirect('/');
					}
				}
			}
		}
	}

	public function jsonout($json)
	{
		$json['state'] = $json['state'] ? true : false;
		echo json_encode($json);
		exit;
	}
}
