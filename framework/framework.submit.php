<?php

class Submit
{
	protected $errors = array();

	public function error($is_all = true)
	{
		return $is_all ? $this->errors : end($this->errors);
	}

	/*$params = array(
		'user_name' => array(
			array('format', 'trim'), 
			array('vaild', 'set', 'message', 'default value', data),
			......
		)
	);*/
	public function obtain($params)
	{
		$this->errors = array();
		$result = array();

		foreach($params as $field => $rules)
		{
			if(!isset($_REQUEST[$field]) || (is_string($_REQUEST[$field]) && strlen(trim($_REQUEST[$field])) == 0))
			{
				$result[$field] = '';
			}
			else
			{
				$result[$field] = $_REQUEST[$field];
			}

			foreach($rules as $type => $rule)
			{
				$function = $rule[0] . $rule[1];
				$funres = $this->$function($rule, $result[$field]);
				if($rule[0] == 'format')
				{
					$result[$field] = $funres;
				}
				else if(!$funres)
				{
					if($rule[3] === null)
					{
						$this->errors[] = $rule[2];
						unset($result[$field]);
						break;
					}
					else
					{
						$result[$field] = $rule[3];
					}
				}
			}
		}

		return $result;
	}

	public function formatTrim($rule, $value)
	{
		return trim($value);
	}

	public function formatInt($rule, $value)
	{
		return intval($value);
	}
	
	public function formatFloat($rule, $value)
	{
		return floatval($value);
	}

	public function formatTimestamp($rule, $value)
	{
		return strtotime($value);
	}

	public function formatHtmlConv($rule, $value)
	{
		return htmlentities($value);
	}

	public function formatTagSrp($rule, $value)
	{
		return strip_tags($value);
	}

	public function validEmpty($rule, $value)
	{
		return !($value == '');
	}

	public function validEq($rule, $value)
	{
		return $value == $rule[4];
	}

	public function validGt($rule, $value)
	{
		return $value > $rule[4];
	}

	public function validEgt($rule, $value)
	{
		return $value >= $rule[4];
	}

	public function validLt($rule, $value)
	{
		return $value < $rule[4];
	}

	public function validElt($rule, $value)
	{
		return $value <= $rule[4];
	}

	public function validBetween($rule, $value)
	{
		return $value >= $rule[4][0] && $value <= $rule[4][1];
	}
	
	public function validIn($rule, $value)
	{
		return in_array($value, $rule[4]);
	}

	public function validRegex($rule, $value)
	{
		return preg_match($rule[4], $value);
	}

	public function validInt($rule, $value)
	{
		return preg_match('/^\d+$/', $value);
	}

	public function validUrl($rule, $value)
	{
		return preg_match('/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//', $value);
	}

	public function validNum($rule, $value)
	{
		return preg_match('/^\d+(?:\.\d+)?(?:[Ee]\d+(?:\.\d+)?)?$/', $value);
	}

	public function validEmail($rule, $value)
	{
		return preg_match('/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/', $value);
	}

	public function validZipcode($rule, $value)
	{
		return preg_match('/^\d{6}$/', $value);
	}

	public function validMobile($rule, $value)
	{
		return preg_match('/^1[358]\d{9}$/', $value);
	}

	public function validTelephone($rule, $value)
	{
		return preg_match('/^\+?\d+(?:-\d+)$/', $value);
	}
}
