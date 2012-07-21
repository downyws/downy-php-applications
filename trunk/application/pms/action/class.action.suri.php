<?php
class ActionSuri extends ActionCommon
{
	public $NOT_LOGIN = array('redirect', 'error');
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->obtain(array(
			'p' => array(array('format', 'int'), array('valid', 'gt', null, 1, 0)),
			'task_title' => array(array('format', 'trim')),
			'uri' => array(array('format', 'trim')),
			'type' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0)),
			'is_disable' => array(array('valid', 'empty', null, -1, null), array('format', 'int'), array('valid', 'in', null, 0, array(-1, 0, 1)))
		));

		$shorturiObj = Factory::getModel('shorturi');
		$userObj = Factory::getModel('user');

		$p = $params['p'];
		unset($params['p']);
		
		$list = $shorturiObj->getList($p, $params);
		$list['data'] = $shorturiObj->formatList($list['data']);
		$list['pager']['params'] = 'task_title=' . urlencode($params['task_title']) . '&uri=' . urlencode($params['uri']) . '&type=' . $params['type'] . '&is_disable=' . $params['is_disable'];

		$this->assign('userpower', $userObj->getUserPower());
		$this->assign('type_list', $GLOBALS['CONFIG']['SHORT_URI']['TYPE']);
		$this->assign('list', $list);
		$this->assign('params', $params);
	}

	public function methodEdit()
	{
		echo 'ActionSuri';
	}

	public function methodDelete()
	{
		echo 'ActionSuri';
	}

	public function methodRedirect()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'key' => array(array('format', 'trim'))
		));

		// 获取跳转地址
		$shortUriObj = Factory::getModel('shorturi');
		$uri = $shortUriObj->getUri($params['key']);
		if(!$uri)
		{
			$this->redirect($GLOBALS['CONFIG']['SHORT_URI']['ERROR_PAGE']);
		}
		else if($uri['is_disable'])
		{
			$this->redirect($GLOBALS['CONFIG']['SHORT_URI']['DISABLE_PAGE']);
		}

		// 更新
		$shortUriObj->updateCount($params['key'], 1);

		// 跳转
		$this->redirect($uri['uri']);
	}

	public function methodError()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'code' => array(array('format', 'trim'))
		));

		// 选择错误信息
		$message = '';
		switch($params['code'])
		{
			case SHORT_URI_ERROR_UNDEFINE:
				$message = '该短网址 不存在 或者 已被删除。';
				break;
			case SHORT_URI_ERROR_DISABLE:
				$message = '该短网址已被禁用。';
				break;
			case SHORT_URI_ERROR_UNKNOW:
			default:
				$message = '未知错误。';
		}

		// 输出
		$this->assign('message', $message);
	}
}
