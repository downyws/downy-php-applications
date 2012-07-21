<?php
class ActionCommon extends Action
{
	public function __construct()
	{
		parent::__construct();
		
		$params = $this->_submit->obtain(array(
			'a' => array(array('format', 'trim')),
			'm' => array(array('format', 'trim')),
			't' => array(array('format', 'trim'))
		));

		if(!in_array($params['m'], $this->NOT_LOGIN))
		{
			$userObj = Factory::getModel('user');
			if(!$userObj->isLogin())
			{
				if($params['t'] == 'ajax')
				{
					echo json_encode(array('state' => false, 'message' => '请先登陆。'));
				}
				else
				{
					$this->redirect('/index.php?a=index&m=login');
				} 
			}
			else if(!in_array($params['m'], $this->NOT_POWER))
			{
				if(!$userObj->hasPower($params['a'], $params['m']))
				{
					if($params['t'] == 'ajax')
					{
						echo json_encode(array('state' => false, 'message' => '您没有权限。'));
					}
					else
					{
						$this->message('您没有权限。', array(array('title' => '返回首页', 'href' => '/index.php?a=index&m=index')));
					} 
				}
			}
		}
	}

	public function message($message, $links = array(), $prompt_type = PROMPT_INFORMATION)
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
		$this->render('common_message.html');
		exit;
	}
}
