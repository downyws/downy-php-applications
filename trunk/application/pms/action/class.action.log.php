<?php
class ActionLog extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$params = $this->_submit->filter(array(
			'p' => array('complete' => array(array('gt', 0), array('int'))),
			'account' => array('complete' => array(array('trim'))),
			'data_table' => array('complete' => array(array('gt', -1))),
			'operation_type' => array('complete' => array(array('gt', -1))),
			'start_time' => array('complete' => array(array('timestamp', false))),
			'end_time' => array('complete' => array(array('timestamp', false)))
		));

		$logObj = Factory::getModel('log');
		$p = $params['p'];
		unset($params['p']);
		$list = $logObj->getList($p, $params);
		$list['data'] = $logObj->formatData($list['data']);
		
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
