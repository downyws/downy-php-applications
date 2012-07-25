<?php
class ActionRead extends ActionCommon
{
	public $NOT_LOGIN = array('email', 'pv');
	public $NOT_POWER = array();

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

		// 检查任务是否存在
		$taskObj = Factory::getModel('task' . $params['ms']);
		$object = $taskObj->getObject(array(array('id' => array('eq', $params['id']))));
		if(!$object)
		{
			$this->redirect('', 404);
		}

		// 检查通道是否正确
		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $object['channel_id']))));
		if($channel['type'] != CHANNEL_TYPE_EMAIL)
		{
			$this->redirect('', 404);
		}

		// 开始显示
		if($params['ms'] == 'single')
		{
			if(!$params['preview'])
			{
				$object['content'] .= '<img src="' . DOMAIN_OUTSIDE . 'index.php?a=read&m=pv&id=' . $params['id'] . '&ms=single" />';
			}
			$this->assign('title', $object['title']);
			$this->assign('content', $object['content']);
		}
		else if($params['ms'] == 'multi')
		{
			var_dump('代码还没写');
		}
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
		$this->redirect($GLOBALS['CONFIG']['TASKSINGLE']['PV_IMAGE']);
	}
}
