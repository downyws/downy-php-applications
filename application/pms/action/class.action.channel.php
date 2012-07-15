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
		
		$list = $channelObj->getAll();
		$list['data'] = $channelObj->formatData($list['data']);

		$this->assign('list', $list);
	}

	public function methodDetail()
	{
		$params = $this->_submit->filter(array(
			'id' => array('complete' => array(array('gt', 0), array('int')))
		));

		$channelObj = Factory::getModel('channel');
		$channel = $channelObj->getObject(array(array('id' => array('eq', $params['id']))));

		$this->assign('channel', $channel);
	}
}
