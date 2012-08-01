<?php
class ActionRead extends ActionCommon
{
	public $NOT_LOGIN = array('email', 'pv', 'sms');
	public $NOT_POWER = array();
	public $RUN_LONG_TIME = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodEmail()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '', null, 0)),
			'preview' => array(array('format', 'int')),
			'ms' => array(array('format', 'trim'), array('valid', 'in', '', null, array('single', 'multi')))
		));

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			$this->redirect('', 404);
		}
		else if($params['ms'] == 'single')
		{
			// 检查任务是否存在
			$tasksingleObj = Factory::getModel('tasksingle');
			$tasksingle = $tasksingleObj->getObject(array(array('id' => array('eq', $params['id']))));
			if(!$tasksingle)
			{
				$this->redirect('', 404);
			}

			// 检查通道是否正确
			$channelObj = Factory::getModel('channel');
			$channel = $channelObj->getObject(array(array('id' => array('eq', $tasksingle['channel_id']))));
			if($channel['type'] != CHANNEL_TYPE_EMAIL)
			{
				$this->redirect('', 404);
			}

			$title = $tasksingle['title'];
			$content = $tasksingle['content'];
		}
		else if($params['ms'] == 'multi')
		{
			$sendlistObj = Factory::getModel('sendlist');
			$sendlist = $sendlistObj->getObject(array(array('id' => array('eq', $params['id']))));
			if(!$sendlist)
			{
				$this->redirect('', 404);
			}

			$taskmultiObj = Factory::getModel('taskmulti');
			$taskmulti = $taskmultiObj->getObject(array(array('id' => array('eq', $sendlist['task_id']))));

			// 检查通道是否正确
			$channelObj = Factory::getModel('channel');
			$channel = $channelObj->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))));
			if($channel['type'] != CHANNEL_TYPE_EMAIL)
			{
				$this->redirect('', 404);
			}

			$data = json_decode($sendlist['data'], true);
			$title = $taskmultiObj->render($taskmulti['title'], $data);
			$content = $taskmultiObj->render($taskmulti['content'], $data);
		}

		if(!$params['preview'])
		{
			$content .= '<img src="' . DOMAIN_OUTSIDE . 'index.php?a=read&m=pv&id=' . $params['id'] . '&ms=' . $params['ms'] . '" />';
		}
		$this->assign('title', $title);
		$this->assign('content', $content);
	}

	public function methodSmsAjax()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '', null, 0)),
			'ms' => array(array('format', 'trim'), array('valid', 'in', '', null, array('single', 'multi')))
		));

		$result = array('state' => false, 'message' => '短信不存在。');

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			$this->redirect('', 404);
		}
		else if($params['ms'] == 'single')
		{
			// 检查任务是否存在
			$tasksingleObj = Factory::getModel('tasksingle');
			$tasksingle = $tasksingleObj->getObject(array(array('id' => array('eq', $params['id']))));
			if($tasksingle)
			{
				// 检查通道是否正确
				$channelObj = Factory::getModel('channel');
				$channel = $channelObj->getObject(array(array('id' => array('eq', $tasksingle['channel_id']))));
				if($channel['type'] == CHANNEL_TYPE_SMS)
				{
					$result = array('state' => true, 'message' => '', 'script' => '$.fn.dialogScript("短信内容", "' . strtr($tasksingle['content'], '"', '\\"') . '", "");');
				}
			}
		}
		else if($params['ms'] == 'multi')
		{
			$sendlistObj = Factory::getModel('sendlist');
			$sendlist = $sendlistObj->getObject(array(array('id' => array('eq', $params['id']))));
			if($sendlist)
			{
				$taskmultiObj = Factory::getModel('taskmulti');
				$taskmulti = $taskmultiObj->getObject(array(array('id' => array('eq', $sendlist['task_id']))));

				// 检查通道是否正确
				$channelObj = Factory::getModel('channel');
				$channel = $channelObj->getObject(array(array('id' => array('eq', $taskmulti['channel_id']))));
				if($channel['type'] == CHANNEL_TYPE_SMS)
				{
					$data = json_decode($sendlist['data'], true);
					$content = $taskmultiObj->render($taskmulti['content'], $data);
					$result = array('state' => true, 'message' => '', 'script' => '$.fn.dialogScript("短信内容", "' . strtr($content, '"', '\\"') . '", "");');
				}
			}
		}

		$this->jsonout($result);
	}

	public function methodPv()
	{
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'gt', '', null, 0)),
			'ms' => array(array('format', 'trim'), array('valid', 'in', '', null, array('single', 'multi')))
		));

		// 检查参数
		if(count($this->_submit->errors) > 0)
		{
			$this->redirect('', 404);
		}

		// 添加访问量
		$taskObj = Factory::getModel('task' . $params['ms']);
		$taskObj->addPv($params['id']);

		// 重定向
		$this->redirect($GLOBALS['CONFIG']['TASK' . strtoupper($params['ms'])]['PV_IMAGE']);
	}
}
