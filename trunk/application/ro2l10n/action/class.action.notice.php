<?php
class ActionNotice extends ActionCommon
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

		$noticeObj = Factory::getModel('notice');
		$objects = $noticeObj->getList($params['page'], APP_PAGER_SIZE);

		$this->assign('objects', $objects);
	}

	public function methodDelete()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$noticeObj = Factory::getModel('notice');
		$noticeObj->remove($params['id']);

		$this->redirect('/index.php?a=notice&m=list');
	}

	public function methodEdit()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$object = array(
			'id' => -1, 
			'start_time' => time(), 'end_time' => time() + 86400 * 7,
			'sort' => 0, 'content' => '',
			'style' => '', 'style_u' => '', 'style_b' => '', 'style_i' => '', 'style_s' => '', 'style_color' => ''
		);
		if($params['id'] >= 0)
		{
			$noticeObj = Factory::getModel('notice');
			$object = array_merge($object, $noticeObj->getById($params['id']));
		}

		$this->assign('object', $object);
	}

	public function methodEditAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'sort' => array(array('format', 'int')),
			'start_time' => array(array('format', 'trim'), array('valid', 'empty', 'Please input start time.', null, null)),
			'end_time' => array(array('format', 'trim'), array('valid', 'empty', 'Please input end time.', null, null)),
			'content' => array(array('format', 'trim'), array('valid', 'empty', 'Please input content.', null, null)),
			'style_u' => array(array('format', 'int')),
			'style_b' => array(array('format', 'int')),
			'style_s' => array(array('format', 'int')),
			'style_i' => array(array('format', 'int')),
			'style_color' => array(array('format', 'trim'))
		));

		// 错误提示
		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$noticeObj = Factory::getModel('notice');
		$result = $noticeObj->save($params);
		if($result['state'])
		{
			$result['url'] = '/index.php?a=notice&m=edit&id=' . $result['state'];
		}

		$this->jsonout($result);
	}
}
