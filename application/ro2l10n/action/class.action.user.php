<?php
class ActionUser extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'page' => array(array('format', 'int'), array('valid', 'gte', '', 1, 1))
		));

		$userObj = Factory::getModel('user');
		$objects = $userObj->getList($params['page'], APP_PAGER_SIZE * 2);

		$this->assign('objects', $objects);
	}

	public function methodEdit()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$object = array(
			'id' => 0,
			'nick' => '',
			'email' => '',
			'state' => 0,
			'task_max_count' => 0,
			'super' => 0,
			'dict' => 0,
			'translation' => 0,
			'proof' => 0,
			'audit' => 0
		);
		if($params['id'] > 0)
		{
			$userObj = Factory::getModel('user');
			$object = array_merge($object, $userObj->getById($params['id']));
		}

		$this->assign('object', $object);
	}

	public function methodEditAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'nick' => array(array('format', 'trim'), array('valid', 'empty', 'Please input nick.', null, null)),
			'email' => array(array('format', 'trim'), array('valid', 'empty', 'Please input email.', null, null)),
			'password' => array(array('format', 'trim')),
			'state' => array(array('format', 'int')),
			'task_max_count' => array(array('format', 'int'), array('valid', 'gte', '', 0, 0)),
			'super' => array(array('format', 'int')),
			'dict' => array(array('format', 'int')),
			'translation' => array(array('format', 'int'), array('valid', 'in', null, 0, array(0, 1, 2))),
			'proof' => array(array('format', 'int'), array('valid', 'in', null, 0, array(0, 1, 2))),
			'audit' => array(array('format', 'int'), array('valid', 'in', null, 0, array(0, 1, 2)))
		));

		// ´íÎóÌáÊ¾
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$userObj = Factory::getModel('user');
		$result = $userObj->save($params);
		if($result['state'])
		{
			$result['url'] = '/index.php?a=user&m=edit&id=' . $result['state'];
		}

		$this->jsonout($result);
	}

	public function methodIndividuation()
	{
	}

	public function methodIndividuationAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'line_dict' => array(array('format', 'int'), array('valid', 'gte', '', APP_PAGER_SIZE, 1), array('valid', 'lte', '', APP_PAGER_SIZE, 999)),
			'line_ln' => array(array('format', 'int'), array('valid', 'gte', '', APP_PAGER_SIZE, 1), array('valid', 'lte', '', APP_PAGER_SIZE, 999)),
			'line_task' => array(array('format', 'int'), array('valid', 'gte', '', APP_PAGER_SIZE, 1), array('valid', 'lte', '', APP_PAGER_SIZE, 999)),
			'task_exco' => array(array('format', 'int'), array('valid', 'in', '', 0, array(0, 1)))
		));

		$userObj = Factory::getModel('user');
		$result = $userObj->saveIndividuation($params);

		$this->jsonout($result);
	}

	public function methodEditPwd()
	{
	}

	public function methodEditPwdAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'old_password' => array(array('format', 'trim')),
			'new_password' => array(array('format', 'trim')),
			'cfm_password' => array(array('format', 'trim'))
		));

		$userObj = Factory::getModel('user');
		$result = $userObj->editPassword($params['old_password'], $params['new_password'], $params['cfm_password']);

		$this->jsonout($result);
	}

	public function methodDelete()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$userObj = Factory::getModel('user');
		$userObj->remove($params['id']);

		$this->redirect('/index.php?a=user&m=list');
	}

	public function methodRefreshMappingAjax()
	{
		$userObj = Factory::getModel('user');
		$result = array('state' => $userObj->refreshMapping());

		$this->jsonout($result);
	}
}
