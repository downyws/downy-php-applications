<?php
class ActionDict extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'page' => array(array('format', 'int'), array('valid', 'gte', '', 1, 1)),
			'cn' => array(array('format', 'trim')),
			'en' => array(array('format', 'trim'))
		));

		$dictObj = Factory::getModel('dict');
		$individuation_detail = $dictObj->getSessionUser('individuation_detail');
		$objects = $dictObj->getList($params['page'], $individuation_detail['line_dict'], $params);
		$objects['pager']['params'] = 'cn=' . urlencode($params['cn']) . '&en=' . urlencode($params['en']);

		$this->assign('params', $params);
		$this->assign('objects', $objects);
	}

	public function methodDeleteAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int'))
		));

		$dictObj = Factory::getModel('dict');
		$result = array('state' => $dictObj->remove($params['id']));

		$this->jsonout($result);
	}

	public function methodEditAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'id' => array(array('format', 'int')),
			'cn' => array(array('format', 'trim'), array('valid', 'regex', '中文内容中不能为空或使用[tab]。', null, '/^[^\t]+$/')),
			'en' => array(array('format', 'trim'), array('valid', 'regex', '英文内容中不能为空或使用[tab]。', null, '/^[^\t]+$/'))
		));

		if(count($this->_submit->errors) > 0)
		{
			$this->jsonout(array('state' => false, 'message' => implode('<br />', $this->_submit->errors)));
		}

		$dictObj = Factory::getModel('dict');
		$result = $dictObj->save($params);

		if($result['state'] && $params['id'] == 0)
		{
			$params['id'] = $result['state'];
			$this->initTemplate();
			$this->assign('object', $params);
			$result['data'] = $this->_tpl->fetch('dict_list_block.html');
		}

		$this->jsonout($result);
	}

	public function methodSearch()
	{
	}

	public function methodSearchAjax()
	{
		$params = $this->_submit->obtain($_REQUEST, array(
			'content' => array(array('format', 'trim'))
		));

		$dictObj = Factory::getModel('dict');
		$result = $dictObj->search($params['content']);

		$this->jsonout($result);
	}

	public function methodImportAjax()
	{
		$dictObj = Factory::getModel('dict');
		$result = $dictObj->import($_FILES['dict']['tmp_name']);
		$this->jsonout($result);
	}

	public function methodExport()
	{
		header('Content-type: application/octet-stream');
		header('Accept-Ranges: bytes');
		header('Content-Disposition: attachment; filename=dict_' . date('YmdHis') . '.txt');
		$dictObj = Factory::getModel('dict');
		echo $dictObj->export();
		exit;
	}

	public function methodClearAjax()
	{
		$dictObj = Factory::getModel('dict');
		$result = array('state' => $dictObj->clear());
		$this->jsonout($result);
	}
}
