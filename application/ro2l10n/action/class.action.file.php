<?php
class ActionFile extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$fileObj = Factory::getModel('file');
		$objects = $fileObj->getList();

		$this->assign('objects', $objects);
	}

	public function methodDeleteAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$fileObj = Factory::getModel('file');
		$result = array('state' => $fileObj->remove($params['id']));

		$this->jsonout($result);
	}

	public function methodEdit()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$object = array(
			'id' => 0,
			'file_name' => '',
			'file_name_short' => '',
			'desc' => '',
			'detail' => '',
			'name_key' => '',
			'name_val' => ''
		);
		if($params['id'] > 0)
		{
			$fileObj = Factory::getModel('file');
			$object = array_merge($object, $fileObj->getById($params['id']));
		}

		$this->assign('object', $object);
	}

	public function methodEditAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'file_name' => array(array('format', 'trim')),
			'file_name_short' => array(array('format', 'trim')),
			'desc' => array(array('format', 'trim')),
			'detail' => array(array('format', 'trim')),
			'name_key' => array(array('format', 'trim')),
			'name_val' => array(array('format', 'trim'))
		));

		$fileObj = Factory::getModel('file');
		$result = $fileObj->save($params);
		if($result['state'])
		{
			$result['url'] = '/index.php?a=file&m=edit&id=' . $result['state'];
		}

		$this->jsonout($result);
	}

	public function methodImportAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'file_name' => array(array('format', 'trim'))
		));

		if($params['file_name'] == $_FILES['file']['name'])
		{
			$fileObj = Factory::getModel('file');
			$file = $fileObj->getByFileName($params['file_name']);
			$result = $fileObj->import($_FILES['file']);
			$fileObj->refresh($file['id']);
			if($result['state'])
			{
				$object = $fileObj->getByFileName($params['file_name']);

				$this->initTemplate();
				$this->assign('object', $object);
				$result['data'] = $this->_tpl->fetch('file_list_block.html');
			}
		}
		else
		{
			$result = array('state' => false, 'message' => 'Please confirm file name.');
		}

		$this->jsonout($result);
	}

	public function methodExport()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$fileObj = Factory::getModel('file');
		$object = $fileObj->getById($params['id']);

		header('Content-type: text/tbl;charset=UTF-8');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename=' . $object['file_name']);

		echo $fileObj->export($params['id']);
		exit;
	}

	public function methodRefreshAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$fileObj = Factory::getModel('file');
		$result = array('state' => $fileObj->refresh($params['id']));
		if($result['state'])
		{
			$object = $fileObj->getById($params['id']);

			$this->initTemplate();
			$this->assign('object', $object);
			$result['data'] = $this->_tpl->fetch('file_list_block.html');
		}

		$this->jsonout($result);
	}

	public function methodAutoUpdate()
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

	public function methodAutoUpdateAjax()
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
					$result['data'] = $this->_tpl->fetch('file_autoupdate_block.html');
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
					$result['data'] = $this->_tpl->fetch('file_autoupdate_block.html');
				}
				break;
		}

		$this->jsonout($result);
	}

	public function methodGetAutoUpdate()
	{
		$autoupdateObj = Factory::getModel('autoupdate');
		$objects = $autoupdateObj->getList();

		$this->assign('objects', $objects);
	}
}
