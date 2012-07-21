<?php
class ModelCommon extends Model
{
	public function __construct()
	{
		parent::__construct($GLOBALS['CONFIG']['DB']);
	}

	public function record($user_id, $data_id, $data_table, $operation_type)
	{
		// 参数处理
		$params = array();
		$params['user_id'] = intval($user_id) > 0 ? intval($user_id) : 0;
		$params['data_id'] = intval($data_id) > 0 ? intval($data_id) : 0;
		$params['data_table'] = intval($data_table) > 0 ? intval($data_table) : 0;
		$params['operation_type'] = intval($operation_type) > 0 ? intval($operation_type) : 0;
		$params['create_time'] = time();

		// 保存
		$this->insert($params, 'log');
	}
}
