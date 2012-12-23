<?php
class ActionCommon extends Action
{
	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MESSAGE . 'package.message.common.php');

		$params = $this->_submit->obtain($_REQUEST, array(
			'a' => array(array('format', 'trim')),
			'm' => array(array('format', 'trim')),
			't' => array(array('format', 'trim'))
		));

		$options = $GLOBALS['CONFIG']['ACTION_OPTIONS'][$params['a']];

		if(in_array($params['m'], $options['RUN_LONG_TIME']))
		{
			set_time_limit(0);
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
					$this->jsonout(array('state' => false, 'message' => $GLOBALS['MESSAGE'][COMMON_NOLOGIN]));
				}
				else if($params['t'] == 'api')
				{
					$this->jsonout(array('state' => false, 'message' => $GLOBALS['MESSAGE'][COMMON_NOLOGIN], 'code' => COMMON_NOLOGIN));
				}
				else
				{
					$this->redirect('/index.php?a=member&m=login&callback=' . urlencode(REMOTE_REQUEST_URI));
				} 
			}
			// 已登录，无权限
			else if(!in_array($params['m'], $options['NOT_POWER']))
			{
				if(!$memberObj->hasPower($params['a'], $params['m']))
				{
					if($params['t'] == 'ajax')
					{
						$this->jsonout(array('state' => false, 'message' => $GLOBALS['MESSAGE'][COMMON_NOPOWER]));
					}
					else if($params['t'] == 'api')
					{
						$this->jsonout(array('state' => false, 'message' => $GLOBALS['MESSAGE'][COMMON_NOPOWER], 'code' => COMMON_NOPOWER));
					}
					else
					{
						$this->message('', $GLOBALS['MESSAGE'][COMMON_NOPOWER], array(array('title' => '返回首页', 'href' => '/')));
					}
				}
			}
		}
	}

	public function message($title, $message, $links = array(), $prompt_type = PROMPT_INFORMATION, $tpl = 'common_message.html')
	{
		if(count($links) < 1)
		{
			$links[] = array('title' => '返回上一页', 'href' => 'javascript:history.go(-1)');
		}
		else
		{
			foreach($links as $k => $v)
			{
				if($v['title'] == '返回' && empty($links[$k]['href']))
				{
					$links[$k]['href'] = $_SERVER['HTTP_REFERER'];
				}
			}
		}
		$this->initTemplate(false);
		$this->assign('title', $title);
		$this->assign('message', $message);
		$this->assign('links', $links);
		$this->assign('prompt_type', $prompt_type);
		$this->render($tpl);
		exit;
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
