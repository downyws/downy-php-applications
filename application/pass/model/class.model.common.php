<?php
class ModelCommon extends Model
{
	// 错误队列
	public $_error = array();

	public function __construct()
	{
		parent::__construct($GLOBALS['CONFIG']['DB']);

		include_once(APP_DIR_MSGCODE . str_replace('Model', 'define.model.', __CLASS__) . '.php');
	}

	public function getError($type = '')
	{
		switch($type)
		{
			case 'all':
				return $this->_error;
			case 'first':
				return reset($this->_error);
			case 'last':
			default:
				return end($this->_error);
		}
	}

	public function operateLog($member_id = 0)
	{
		$member_id = empty($member_id) ? $_SESSION['MEMBER']['id'] : $member_id;
		$request_data = serialize($_POST);
		$data = array('member_id' => $member_id, 'ip' => ip2long(REMOTE_IP_ADDRESS), 'create_time' => time(), 'request_url' => REMOTE_REQUEST_URI, 'request_data' => $request_data);
		$this->insert($data, 'logs');
	}

	public function operateCheck($data_id, $cate_id, $member_id = 0)
	{
		$member_id = empty($member_id) ? $_SESSION['MEMBER']['id'] : $member_id;
		$data = array('cate_id' => $cate_id, 'data_id' => $data_id, 'check_time' => 0, 'create_time' => time(), 'member_id' => $member_id, 'description' => '');
		$this->insert($data, 'check');
	}

	public function productDomain($url)
	{
		preg_match('/(\w+)(\.\w+)+/', $url, $result);
		$result = (count($result) > 0) ? $result[0] : '';
		return $result;
	}

	public function getSessionMember($field = null)
	{
		if(empty($_SESSION['MEMBER']))
		{
			return null;
		}
		else if(empty($field))
		{
			return $_SESSION['MEMBER'];
		}
		else
		{
			return empty($_SESSION['MEMBER'][$field]) ? null : $_SESSION['MEMBER'][$field];
		}
	}

	public function setSessionMember($member)
	{
		if(empty($_SESSION['MEMBER']))
		{
			$_SESSION['MEMBER'] = $member;
		}
		else
		{
			$_SESSION['MEMBER'] = array_merge($_SESSION['MEMBER'], $member);
		}
	}

	public function delSessionMember()
	{
		unset($_SESSION['MEMBER']);
	}
}
