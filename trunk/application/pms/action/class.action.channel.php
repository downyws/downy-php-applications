<?php
class ActionChannel extends ActionCommon
{
	public $NOT_LOGIN = array();
	public $NOT_POWER = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function methodList()
	{
		$channelObj = Factory::getModel('channel');
		$userObj = Factory::getModel('user');

		$list = $channelObj->getAll();
		$list['data'] = $channelObj->formatList($list['data']);
		
		$this->assign('userpower', $userObj->getUserPower());
		$this->assign('list', $list);
	}

	public function methodDetail()
	{
		$params = $this->_submit->filter(array(
			'id' => array('complete' => array(array('gt', 0), array('int')))
		));

		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $params['id']))));
		$channel = $channelObj->formatObject($channel);

		$this->assign('channel', $channel);
	}

	public function methodDetailAjax()
	{
		// 获取参数
		$params = $this->_submit->filter(array(
			'id' => array('complete' => array(array('int'))),
			'key' => array('complete' => array(array('trim'))),
			'val' => array('complete' => array(array('trim')))
		));

		$user = $_SESSION['user'];
		$result = array('state' => true, 'message' => '未知错误');

		// 保存
		$channelObj = Factory::getModel('channel');
		$result = $channelObj->update($params['id'], array($params['key'] => $params['val']));
		if($result['state'])
		{
			$channelObj->record(array('user_id' => $user['id'], 'data_id' => $params['id'], 'data_table' => LOG_DATA_TABLE_CHANNEL, 'operation_type' => LOG_OPERATION_TYPE_UPDATE));
		}
		// 返回
		echo json_encode($result);
	}
}
