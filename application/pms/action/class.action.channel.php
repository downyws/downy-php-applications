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

	public function methodEdit()
	{
		$params = $this->_submit->filter(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', null, '0', 0))
		));

		if($params['id'])
		{
			$channelObj = Factory::getModel('channel');
			$channel = $channelObj->getObject(array(array('id' => array('eq', $params['id']))));

			$this->assign('channel', $channel);
		}

		$this->assign('id', $params['id']);
		$this->assign('channel_type_list', $GLOBALS['CONFIG']['CHANNEL']['TYPE']);
		$this->assign('is_disable_list', $GLOBALS['CONFIG']['IS_DISABLE']);
	}

	public function methodEditAjax()
	{
	/*	// 获取参数
		$params = $this->_submit->filter(array(
			'id' => array('complete' => array(array('int')), 'valid' => array(array('gt', '编号错误。', -1))),
			'name' => array('complete' => array(array('trim')), 'valid' => array(array('set', '名称不能为空。'))),
			'is_disable' => array('valid' => array(array('in', '状态错误。', $GLOBALS['CONFIG']['IS_DISABLE']))),
			'type' => array('valid' => array(array('in', '类型错误。', $GLOBALS['CONFIG']['CHANNEL'])))
		));*/

$result = array('state' => true, 'message' => 'test');
		die(json_encode($result));

		if(!$params)
		{
			$message = implode(' ', $this->_submit->error());
			$result = array('state' => false, 'message' => $message);
			die(json_encode($result));
		}

/*
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
		echo json_encode($result);*/
	}
}
