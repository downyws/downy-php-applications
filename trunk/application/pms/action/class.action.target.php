<?php
class ActionTarget extends ActionCommon
{
	public $NOT_LOGIN = array();
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
			'contact' => array(array('format', 'trim')),
			'type' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0)),
			'is_disable' => array(array('valid', 'empty', null, -1, null), array('format', 'int'), array('valid', 'in', null, 0, array(-1, 0, 1)))
		));

		$targetObj = Factory::getModel('target');
		$userObj = Factory::getModel('user');

		$p = $params['p'];
		unset($params['p']);
		
		$list = $targetObj->getList($p, $params);
		$list['data'] = $targetObj->formatList($list['data']);
		$list['pager']['params'] = 'contact=' . urlencode($params['contact']) . '&type=' . $params['type'] . '&is_disable=' . $params['is_disable'];

		$this->assign('userpower', $userObj->getUserPower());
		$this->assign('type_list', $GLOBALS['CONFIG']['TARGET']['TYPE']);
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
			$targetObj = Factory::getModel('target');
			$object = $targetObj->getObject(array(array('id' => array('eq', $params['id']))));
			$object = $targetObj->formatObject($object);

			$this->assign('object', $object);
		}

		$this->assign('id', $params['id']);
		$this->assign('target_type_list', $GLOBALS['CONFIG']['TARGET']['TYPE']);
		$this->assign('is_disable_list', $GLOBALS['CONFIG']['IS_DISABLE']);
	}

	public function methodEditAjax()
	{
		// 获取参数
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', '编号错误', null, 0)),
			'contact' => array(array('format', 'trim')),
			'is_disable' => array(array('format', 'int'), array('valid', 'in', '状态错误', null, array_keys($GLOBALS['CONFIG']['IS_DISABLE'])))
		));

		// 保存
		if(count($this->_submit->errors) > 0)
		{
			$message = implode('，', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			$targetObj = Factory::getModel('target');
			if($params['id'] == 0)
			{
				$result = $targetObj->add($params);
				if($result['state'])
				{
					$result['script'] = '$.fn.dialogScript("提示信息", "保存成功。", "window.location.href=\"/index.php?a=target&m=edit&id=' . $result['message'] . '\"");';
					$result['message'] = '保存成功。';
				}
			}
			else
			{
				$result = $targetObj->edit($params['id'], $params);
			}
		}

		// 返回
		echo json_encode($result);
	}
}
