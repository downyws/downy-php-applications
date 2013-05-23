<?php
class ActionLocalization extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$fileObj = Factory::getModel('file');
		$files = $fileObj->getList();
		$filetitle = array();
		foreach($files['data'] as $v)
		{
			$filetitle[$v['id']] = $v['file_name'] . ' [ T:' . $v['wait_t_count'] . ' / P:' . $v['wait_p_count'] . ' / A:' . $v['wait_a_count'] . ' ] ' . $v['desc'];
		}

		$this->assign('files', $files);
		$this->assign('filetitle', $filetitle);
	}

	public function methodListAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'page' => array(array('format', 'int'), array('valid', 'gte', '', 1, 1)),
			'key' => array(array('format', 'trim')),
			'content_new_en' => array(array('format', 'trim'))
		));
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'state' => array(array('format', 'int'))
		));
		$params['state'] = array();
		foreach($temp as $v)
		{
			$params['state'][] = $v['state'];
		}
		$this->_submit->errors = array();
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'file' => array(array('format', 'int'))
		));
		$params['file'] = array();
		foreach($temp as $v)
		{
			$params['file'][] = $v['file'];
		}

		$localizationObj = Factory::getModel('localization');
		$individuation_detail = $localizationObj->getSessionUser('individuation_detail');
		$objects = $localizationObj->getList($params['page'], $individuation_detail['line_ln'], $params);

		$this->initTemplate();
		$this->assign('objects', $objects);
		$result = array('state' => true, 'message' => '');
		$result['data'] = $this->_tpl->fetch('localization_list_block.html');

		$this->jsonout($result);
	}

	public function methodOccAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'opr' => array(array('format', 'trim'), array('valid', 'in', 'Opr error.', 0, array('translation', 'proof', 'audit', 'back')))
		));
		if($this->_submit->errors)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'opr_file_key' => array(array('format', 'trim'))
		));
		$params['opr_file_key'] = array();
		foreach($temp as $v)
		{
			$params['opr_file_key'][] = $v['opr_file_key'];
		}

		$localizationObj = Factory::getModel('localization');
		$result = $localizationObj->occupied($params);

		$this->jsonout($result);
	}

	public function methodMyTask()
	{
		$fileObj = Factory::getModel('file');
		$files = $fileObj->getListMyHasTask();
		$individuation_detail = $fileObj->getSessionUser('individuation_detail');

		$this->assign('line_task', $individuation_detail['line_task']);
		$this->assign('task_exco', $individuation_detail['task_exco']);
		$this->assign('files', $files);
	}

	public function methodMyTaskKeyAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'key' => array(array('format', 'trim')),
			'content_new_en' => array(array('format', 'trim'))
		));
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'state' => array(array('format', 'int'))
		));
		$params['state'] = array();
		foreach($temp as $v)
		{
			$params['state'][] = $v['state'];
		}
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'file' => array(array('format', 'int'))
		));
		$params['file'] = array();
		foreach($temp as $v)
		{
			$params['file'][] = $v['file'];
		}

		$localizationObj = Factory::getModel('localization');
		$data = $localizationObj->getMyTaskListKey($params);

		if($data !== false)
		{
			$result = array('state' => true, 'data' => $data);
		}
		else
		{
			$result = array('state' => false, 'message' => 'Filter error.');
		}

		$this->jsonout($result);
	}

	public function methodMyTaskListAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'key' => array(array('format', 'trim')),
			'start' => array(array('format', 'int')),
			'end' => array(array('format', 'int'))
		));

		$localizationObj = Factory::getModel('localization');
		$result = $localizationObj->getListByKey($params['start'], $params['end'], $params['key']);

		$this->jsonout($result);
	}

	public function methodMyTaskDoAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'state' => array(array('format', 'trim'), array('valid', 'in', 'State error.', 0, array('pass', 'back', 'forgo', 'confirm', 'finish'))),
			'stamp' => array(array('format', 'trim')),
			'reason' => array(array('format', 'trim'))
		));

		if($this->_submit->errors)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$localizationObj = Factory::getModel('localization');
		$result = $localizationObj->taskDo($params);

		$this->jsonout($result);
	}

	public function methodImportTaskAjax()
	{
		$localizationObj = Factory::getModel('localization');
		$result = $localizationObj->importTask($_FILES['file']['tmp_name']);
		$this->jsonout($result);
	}

	public function methodExportTask()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'page' => array(array('format', 'int'), array('valid', 'gte', '', 1, 1)),
			'key' => array(array('format', 'trim')),
			'content_new_en' => array(array('format', 'trim'))
		));
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'state' => array(array('format', 'int'))
		));
		$params['state'] = array();
		foreach($temp as $v)
		{
			$params['state'][] = $v['state'];
		}
		$temp = $this->_submit->obtainArray($_REQUEST, array(
			'file' => array(array('format', 'int'))
		));
		$params['file'] = array();
		foreach($temp as $v)
		{
			$params['file'][] = $v['file'];
		}

		$localizationObj = Factory::getModel('localization');
		$user = $localizationObj->getSessionUser();

		header('Content-type: text/tbl;charset=UTF-8');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename=' . date('YmdHis_') . $user['nick'] . '_task.txt');

		echo $localizationObj->exportTask($user['id'], $params);
		exit;
	}
}
