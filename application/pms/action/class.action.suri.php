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
		$params = $this->_submit->filter(array(
			'p' => array('complete' => array(array('gt', 0), array('int'))),
			'task_title' => array('complete' => array(array('trim'))),
			'uri' => array('complete' => array(array('trim')))
		));

		$shorturiObj = Factory::getModel('shorturi');
		$p = $params['p'];
		unset($params['p']);
		$list = $shorturiObj->getList($p, $params);
		$list['data'] = $shorturiObj->formatData($list['data']);

		$list['pager']['params'] = 'task_title=' . urlencode($params['task_title']) . '&uri=' . urlencode($params['uri']);

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
		$params = $this->_submit->filter(array(
			'key' => array('complete' => array(array('trim')))
		));

		// 获取跳转地址
		$shortUriObj = Factory::getModel('shorturi');
		$uri = $shortUriObj->getUri($params['key'], true);

		// 跳转
		$this->redirect($uri);
	}

	public function methodError()
	{
		// 获取参数
		$params = $this->_submit->filter(array(
			'code' => array('complete' => array(array('trim')))
		));

		// 选择错误信息
		$message = '';
		switch($params['code'])
		{
			case SHORT_URI_ERROR_UNDEFINE:
				$message = '该短网址 不存在 或者 已被删除。';
				break;
			case SHORT_URI_ERROR_UNKNOW:
			default:
				$message = '未知错误。';
		}

		// 输出
		$this->assign('message', $message);
	}
}
