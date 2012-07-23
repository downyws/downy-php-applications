<?php
class ModelTarget extends ModelCommon
{
	public $_table = 'target';

	public function __construct()
	{
		parent::__construct();
	}

	public function contactToId($contact)
	{
		$condition = array();
		$condition[] = array('contact' => array('eq', $contact));
		$id = $this->getOne($condition, 'id');
		if($id < 1)
		{
			$type = $this->contactType($contact);
			if($type > 0)
			{
				$id = $this->insert(array('contact' => $contact, 'type' => $type, 'is_disable' => 0));
			}
		}
		return $id;
	}

	public function contactType($contact)
	{
		if(preg_match('/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/', $contact))
		{
			return 1;
		}
		else if(preg_match('/^1[358]\d{9}$/', $contact))
		{
			return 2;
		}
		return 0;
	}
}
