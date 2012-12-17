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

		/*if(!in_array($params['m'], $options['NOT_LOGIN']) && $params['t'] == 'api')
		{
			$api_params = $this->_submit->obtain($_REQUEST, array(
				'account' => array(array('format', 'trim')),
				'hash' => array(array('format', 'trim')),
				'time' => array(array('format', 'int'))
			));

			if(!empty($api_params['account']) && !empty($api_params['hash']) && !empty($api_params['time']))
			{
				// 时间戳检查
				$now = time();
				if($api_params['time'] - 60 > $now || $now > $api_params['time'] + 60)
				{
					$this->jsonout(array('state' => false, 'message' => '时间戳过期。', 'code' => 'TIMESTAMP ERROR'));
				}

				// 账号检查
				$memberObj = Factory::getModel('member');
				$member = $memberObj->getMember($api_params['account'], 'account');
				if(!$member)
				{
					$this->jsonout(array('state' => false, 'message' => '账号错误。', 'code' => 'ACCOUNT ERROR'));
				}

				// HASH校验
				$req = $_REQUEST;
				unset($req['a'], $req['m'], $req['t'], $req['hash']);
				ksort($req);
				$hash = '';
				foreach($req as $v) $hash .= $v;
				$hash = md5($hash . $member['password']);
				if($api_params['hash'] != $hash)
				{
					$this->jsonout(array('state' => false, 'message' => 'hash校验错误。', 'code' => 'HASH ERROR'));
				}

				// 自动登录
				$result = $memberObj->login(0, $member['account'], $member['password'], true);
				if(!$result['state'])
				{
					$this->jsonout(array('state' => false, 'message' => '账号异常或被禁用。', 'code' => 'ACCOUNT ABNORMAL'));
				}
			}
			else if(!empty($api_params['account']) || !empty($api_params['hash']) || !empty($api_params['time']))
			{
				$this->jsonout(array('state' => false, 'message' => '参数错误。', 'code' => 'PARAMS ERROR'));
			}
		}*/

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
					$this->jsonout(array('state' => false, 'message' => '请先登陆。'));
				}
				else if($params['t'] == 'api')
				{
					$this->jsonout(array('state' => false, 'message' => '请先登陆。', 'code' => 'NOT LOGIN'));
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
						$this->jsonout(array('state' => false, 'message' => '您没有权限。'));
					}
					else if($params['t'] == 'api')
					{
						$this->jsonout(array('state' => false, 'message' => '没有权限。', 'code' => 'NO POWER'));
					}
					else
					{
						$this->message('您没有权限。', array(array('title' => '返回首页', 'href' => '/')));
					}
				}
			}
		}
	}

	public function message($message, $links = array(), $prompt_type = PROMPT_INFORMATION, $tpl = 'common_message.html')
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
