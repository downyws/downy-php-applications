<?php
class ModelCommon extends Model
{
	public function __construct()
	{
		parent::__construct($GLOBALS['CONFIG']['DB']);
	}

	public function record($param)
	{
		// 参数处理
		$param['user_id'] = intval($param['user_id']) > 0 ? intval($param['user_id']) : 0;
		$param['data_id'] = intval($param['data_id']) > 0 ? intval($param['data_id']) : 0;
		$param['data_table'] = intval($param['data_table']) > 0 ? intval($param['data_table']) : 0;
		$param['operation_type'] = intval($param['operation_type']) > 0 ? intval($param['operation_type']) : 0;
		$param['create_time'] = !empty($param['create_time']) && intval($param['create_time']) > 0 ? intval($param['create_time']) : time();

		// 保存
		$this->insert($param, 'log');
	}
}
