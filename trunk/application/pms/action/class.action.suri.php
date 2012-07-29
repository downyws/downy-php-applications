<?php
class ActionSuri extends ActionCommon
{
	public $NOT_LOGIN = array('redirect', 'error');
	public $NOT_POWER = array();
	public $RUN_LONG_TIME = array();

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
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0))
		));

		if($params['id'])
		{
			$shorturiObj = Factory::getModel('shorturi');
			$object = $shorturiObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $shorturiObj->formatObject($object);

			$this->assign('object', $object);
		}

		$this->assign('shorturi_domain', DOMAIN_SURI);
		$this->assign('id', $params['id']);
		$this->assign('shorturi_type_list', $GLOBALS['CONFIG']['SHORT_URI']['TYPE']);
		$this->assign('is_disable_list', $GLOBALS['CONFIG']['IS_DISABLE']);
	}

	public function methodEditAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', '编号错误', null, 0)),
			'key' => array(array('format', 'trim')),
			'uri' => array(array('format', 'trim'), array('valid', 'url', '重定向网址错误', null, null)),
			'is_disable' => array(array('format', 'int'), array('valid', 'in', '状态错误', null, array_keys($GLOBALS['CONFIG']['IS_DISABLE']))),
			'type' => array(array('format', 'int'), array('valid', 'in', '类型错误', null, array_keys($GLOBALS['CONFIG']['SHORT_URI']['TYPE'])))
		));

		// 保存
		if(count($this->_submit->errors) > 0)
		{
			$message = implode('，', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			$shorturiObj = Factory::getModel('shorturi');
			if($params['id'] == 0)
			{
				$result = $shorturiObj->add($params);
				if($result['state'])
				{
					$result['script'] = '$.fn.dialogScript("提示信息", "保存成功。", "window.location.href=\"/index.php?a=suri&m=edit&id=' . $result['message'] . '\"");';
					$result['message'] = '保存成功。';
				}
			}
			else
			{
				$result = $shorturiObj->edit($params['id'], $params);
				$object = $shorturiObj->getObject(array(array('id' => array('eq', $params['id']))));
				$result['script'] = '$("input[name=\'key\']").val("' . $object['key'] . '")';
			}
		}

		// 返回
		$this->jsonout($result);
	}

	public function methodRedirect()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'key' => array(array('format', 'trim'), array('valid', 'regex', '', null, '/^[0-9A-za-z]+$/'))
		));

		if(count($this->_submit->errors) > 0)
		{
			$this->redirect($GLOBALS['CONFIG']['SHORT_URI']['DISABLE_PAGE']);
		}
		else
		{
			// 获取对象
			$shorturiObj = Factory::getModel('shorturi');
			$uri = $shorturiObj->getObject(array(array('`key`' => array('eq', $params['key']))));
			if(!$uri)
			{
				$this->redirect($GLOBALS['CONFIG']['SHORT_URI']['ERROR_PAGE']);
			}
			else if($uri['is_disable'])
			{
				$this->redirect($GLOBALS['CONFIG']['SHORT_URI']['DISABLE_PAGE']);
			}

			// 跳转
			$shorturiObj->addPv($uri['id']);
			$this->redirect($uri['uri']);
		}
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
