<?php
class ActionCommon extends Action
{
	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Action', 'define.action.', __CLASS__) . '.php');

		$params = $this->_submit->obtain($_REQUEST, array(
			'a' => array(array('format', 'trim')),
			'm' => array(array('format', 'trim')),
			't' => array(array('format', 'trim'))
		));

		$options = $GLOBALS['CONFIG']['ACTION_OPTIONS'][$params['a']];

		if(in_array($params['m'], $options['RUN_LONG_TIME']))
		{
			set_time_limit(1800);
			ini_set('memory_limit', '512M');
		}

		if(!in_array($params['m'], $options['NOT_LOGIN']))
		{
			$memberObj = Factory::getModel('member');
			// 未登陆
			if(!$memberObj->isLogin())
			{
				if($params['t'] == 'ajax')
				{
					$this->jsonout(array('state' => false, 'message' => MCGetM('ACON_NO_LOGIN')));
				}
				else if($params['t'] == 'api')
				{
					$this->jsonout(array('state' => false, 'message' => MCGetM('ACON_NO_LOGIN'), 'code' => MCGetC('ACON_NO_LOGIN')));
				}
				else
				{
					$this->redirect('/index.php?a=member&m=login&callback=' . urlencode(REMOTE_REQUEST_URI));
				} 
			}
		}
	}

	public function message($code, $data = null, $tpl = 'common_message.html')
	{
		$this->initTemplate(false);
		$this->assign('code', $code);
		$this->assign('data', $data);

		if($tpl == 'string')
		{
			return $this->fetch('string: ' . MCGetM($code));
		}
		else
		{
			$this->assign('code_page', file_exists(APP_DIR_TEMPLATE . 'common_message/' . $code . '.html'));
			$this->render($tpl);
			exit;
		}
	}

	public function methodMessage()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'code' => array(array('format', 'trim'))
		));
		$this->message($params['code']);
	}

	public function jsonout($json)
	{
		$json['state'] = $json['state'] ? true : false;
		echo json_encode($json);
		exit;
	}

	public function captchaCreate()
	{
		$key = 'CAPTCHA';
		$_SESSION[$key] = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
		return $_SESSION[$key];
	}

	public function captchaCheck($value, $type = '')
	{
		$key = 'CAPTCHA';
		$result = !empty($_SESSION[$key]) && $value == $_SESSION[$key];

		if(($type == 'once') || ($result && $type == 'true'))
		{
			unset($_SESSION[$key]);
		}

		return $result;
	}
}
