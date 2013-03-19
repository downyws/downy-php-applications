<?php
class ModelSupport extends ModelCommon
{
	public $_table = 'support';

	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Model', 'define.model.', __CLASS__) . '.php');
	}

	public function add($support)
	{
		$err = array();
		$res = $this->checkErrCate($support);
		$res && $err[] = $res;
		$res = $this->checkErrTitle($support);
		$res && $err[] = $res;
		$res = $this->checkErrContent($support);
		$res && $err[] = $res;
		$res = $this->checkErrContact($support);
		$res && $err[] = $res;

		if(empty($err))
		{
			switch($support['cate_id'])
			{
				case SUPPORT_LOST_ACCOUNT:
					$support['title'] = $support['first_name'] . ' / ' . $support['last_name'];
					break;
				case SUPPORT_LOGIN_QUESTION:
					$support['title'] = $support['account'];
					break;
			}
			$data = array('style' => '', 'is_read' => 0, 'is_dispose' => 0, 'ip' => ip2long(REMOTE_IP_ADDRESS), 'create_time' => time());
			$support_id = $this->insert(array_merge($data, $support));
			if($support_id > 0)
			{
				return true;
			}
			else
			{
				$err[] = MCGetC('MCON_SYSERR_TELA');
			}
		}

		$this->_error[] = $err;
		return false;
	}

	public function checkErrCate($vals)
	{
		if(!in_array($vals['cate_id'], array(SUPPORT_QUESTION, SUPPORT_LOST_ACCOUNT, SUPPORT_LOGIN_QUESTION))) return MCGetC('MSUP_CATE_ERROR');
		return false;
	}

	public function checkErrTitle($vals)
	{
		switch($vals['cate_id'])
		{
			case SUPPORT_LOST_ACCOUNT:
				if(empty($vals['first_name'])) return MCGetC('MSUP_TITLE_FNAME_EMPTY');
				if(empty($vals['last_name'])) return MCGetC('MSUP_TITLE_LNAME_EMPTY');
				if(strlen($vals['first_name']) > 50) return MCGetC('MSUP_TITLE_FNAME_ERROR');
				if(strlen($vals['last_name']) > 50) return MCGetC('MSUP_TITLE_LNAME_ERROR');
				if(!preg_match('/^[\x{4e00}-\x{9fa5}\w\ ]+$/u', $vals['first_name'])) return MCGetC('MSUP_TITLE_FNAME_ERROR');
				if(!preg_match('/^[\x{4e00}-\x{9fa5}\w\ ]+$/u', $vals['last_name'])) return MCGetC('MSUP_TITLE_LNAME_ERROR');
				break;
			case SUPPORT_LOGIN_QUESTION:
				if(empty($vals['account'])) return MCGetC('MSUP_TITLE_ACCOUNT_EMPTY');
				$len = strlen($vals['account']);
				if($len < 6 || $len > 20) return MCGetC('MSUP_TITLE_ACCOUNT_ERROR');
				if($len > 8 && !preg_match('/[^\d+.]/', $vals['account'])) return MCGetC('MSUP_TITLE_ACCOUNT_ERROR');
				if(!preg_match('/^[a-z0-9.]+$/i', $vals['account'])) return MCGetC('MSUP_TITLE_ACCOUNT_ERROR');
				break;
			case SUPPORT_QUESTION:
				if(empty($vals['title'])) return MCGetC('MSUP_TITLE_EMPTY');
				break;
		}
		return false;
	}

	public function checkErrContent($vals)
	{
		if(empty($vals['content'])) return MCGetC('MSUP_CONTENT_EMPTY');
		if(strlen($vals['content']) < 10) return MCGetC('MSUP_CONTENT_SHORT');

		return false;
	}

	public function checkErrContact($vals)
	{
		if(empty($vals['contact'])) return MCGetC('MSUP_CONTACT_EMPTY');

		Factory::loadLibrary('stringhelper');
		$stringhelper = new StringHelper();
		if(!$stringhelper->dataTypeTrue($vals['contact'], 'email') && !$stringhelper->dataTypeTrue($vals['contact'], 'mobile')) return MCGetC('MSUP_CONTACT_ERROR');

		return false;
	}
}
