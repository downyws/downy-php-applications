<?php
class ActionLog extends ActionCommon
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
			'account' => array(array('format', 'trim')),
			'data_table' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'operation_type' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0)),
			'start_time' => array(array('format', 'timestamp')),
			'end_time' => array(array('format', 'timestamp'))
		));

		$logObj = Factory::getModel('log');
		$p = $params['p'];
		unset($params['p']);
		$list = $logObj->getList($p, $params);
		$list['data'] = $logObj->formatList($list['data']);
		
		$list['pager']['params'] = 'account=' . urlencode($params['account']) . '&data_table=' . $params['data_table'] . '&operation_type=' . $params['operation_type'];
		if($params['start_time'] !== false)
		{
			$params['start_time'] = date('Y-m-d', $params['start_time']);
			$list['pager']['params'] .= '&start_time=' . $params['start_time'];
		}
		if($params['end_time'] !== false)
		{
			$params['end_time'] = date('Y-m-d', $params['end_time']);
			$list['pager']['params'] .= '&end_time=' . $params['end_time'];
		}

		$this->assign('data_table_list', $GLOBALS['CONFIG']['LOG']['DATA_TABLE']);
		$this->assign('operation_type_list', $GLOBALS['CONFIG']['LOG']['OPERATION_TYPE']);
		$this->assign('list', $list);
		$this->assign('params', $params);
	}
}
