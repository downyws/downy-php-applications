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
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', null, 0, 0))
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
		// 获取参数
		$params = $this->_submit->obtain(array(
			'id' => array(array('format', 'int'), array('valid', 'egt', '编号错误', null, 0)),
			'name' => array(array('format', 'trim'), array('valid', 'empty', '通道名称不能为空。', null, null)),
			'is_disable' => array(array('format', 'int'), array('valid', 'in', '状态错误', null, array_keys($GLOBALS['CONFIG']['IS_DISABLE']))),
			'type' => array(array('format', 'int'), array('valid', 'in', '类型错误', null, array_keys($GLOBALS['CONFIG']['CHANNEL']['TYPE'])))
		));

		// 保存
		if(count($this->_submit->errors) > 0)
		{
			$message = implode('， ', $this->_submit->errors) . '。';
			$result = array('state' => false, 'message' => $message);
		}
		else
		{
			$channelObj = Factory::getModel('channel');
			if($params['id'] == 0)
			{
				$result = $channelObj->add($params);
				if($result['state'])
				{
					$result['script'] = 'alert("保存成功。");window.location="/index.php?a=channel&m=edit&id=' . $result['message'] . '";';
					$result['message'] = '保存成功。';
				}
			}
			else
			{
				$result = $channelObj->edit($params['id'], $params);
			}
		}

		// 返回
		echo json_encode($result);
	}
}
