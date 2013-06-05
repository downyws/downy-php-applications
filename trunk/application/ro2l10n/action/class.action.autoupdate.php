<?php
class ActionAutoupdate extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$autoupdateObj = Factory::getModel('autoupdate');
		$objects = $autoupdateObj->getList();
		$temp = array();
		foreach($objects['data'] as $v)
		{
			if(empty($temp[$v['keyword']]))
			{
				$temp[$v['keyword']] = array();
			}
			$temp[$v['keyword']][] = $v;
		}
		$objects['data'] = $temp;

		$this->assign('objects', $objects);
	}

	public function methodOperateAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'type' => array(array('format', 'trim'), array('valid', 'in', 'Type error.', null, array('del', 'upd', 'edit')))
		));
		if($this->_submit->errors)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$autoupdateObj = Factory::getModel('autoupdate');

		switch($params['type'])
		{
			case 'del':
				$result = array('state' => $autoupdateObj->remove($params['id']));
				if(!$result['state'])
				{
					$result['message'] = 'Delete data error.';
				}
				break;
			case 'upd':
				$result = array('state' => $autoupdateObj->remoteUpd($params['id']));
				if(!$result['state'])
				{
					$result['message'] = 'Remote update data error.';
				}
				else
				{
					$object = $autoupdateObj->getById($params['id']);
					$this->initTemplate();
					$this->assign('object', $object);
					$result['data'] = $this->_tpl->fetch('autoupdate_list_block.html');
				}
				break;
			case 'edit':
				$params = $this->_submit->obtain($_REQUEST, array(
					'id' => array(array('format', 'int')),
					'keyword' => array(array('format', 'trim')),
					'path' => array(array('format', 'trim')),
					'url' => array(array('format', 'trim'))
				));
				$result = $autoupdateObj->save($params);
				if($result['state'] && $params['id'] > 0)
				{
					$object = $autoupdateObj->getById($params['id']);
					$this->initTemplate();
					$this->assign('object', $object);
					$result['data'] = $this->_tpl->fetch('autoupdate_list_block.html');
				}
				break;
		}

		$this->jsonout($result);
	}

	public function methodSetting()
	{
		$autoupdateObj = Factory::getModel('autoupdate');
		$objects = $autoupdateObj->getSetting();

		$this->assign('objects', $objects);
	}

	public function methodSettingAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'type' => array(array('format', 'trim'), array('valid', 'in', 'Type error.', null, array('del', 'edit')))
		));
		if($this->_submit->errors)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$autoupdateObj = Factory::getModel('autoupdate');

		switch($params['type'])
		{
			case 'del':
				$result = array('state' => $autoupdateObj->remove($params['id']));
				if(!$result['state'])
				{
					$result['message'] = 'Delete data error.';
				}
				break;
			case 'edit':
				$params = $this->_submit->obtain($_REQUEST, array(
					'id' => array(array('format', 'int')),
					'keyword' => array(array('format', 'trim')),
					'path' => array(array('format', 'trim')),
					'url' => array(array('format', 'trim'))
				));
				$result = $autoupdateObj->saveSetting($params);
				if($result['state'] && $params['id'] < 0)
				{
					$object = $autoupdateObj->getById($params['id']);
					$this->initTemplate();
					$this->assign('object', $object);
					$result['data'] = $this->_tpl->fetch('autoupdate_setting_block.html');
				}
				break;
		}

		$this->jsonout($result);
	}

	public function methodInfo()
	{
		$autoupdateObj = Factory::getModel('autoupdate');
		$objects = $autoupdateObj->getSetting();

		$this->assign('objects', $objects);
	}

	public function methodFile()
	{
		$autoupdateObj = Factory::getModel('autoupdate');
		$objects = $autoupdateObj->getList();

		$this->assign('objects', $objects);
	}
}
