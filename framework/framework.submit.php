<?php

class Submit
{
	public $errors = array();

	/*$params = array(
		'user_name' => array(
			array('mapping', 'new name'),
			array('format', 'trim'),
			array('valid', 'set', 'message', 'default value', data),
			......
		)
	);*/
	public function obtain($values, $params)
	{
		$this->errors = array();
		$result = array();

		foreach($params as $field => $rules)
		{
			if(!isset($values[$field]) || (is_string($values[$field]) && strlen(trim($values[$field])) == 0))
			{
				$result[$field] = '';
			}
			else
			{
				$result[$field] = $values[$field];
			}

			$mapping = false;

			foreach($rules as $type => $rule)
			{


				if($rule[0] == 'mapping')
				{
					$mapping = $rule[1];
				}
				else if($rule[0] == 'format')
				{
					$function = $rule[0] . $rule[1];
					$funres = $this->$function($rule, $result[$field]);
					$result[$field] = $funres;
				}
				else if($rule[0] == 'valid')
				{
					$function = $rule[0] . $rule[1];
					$funres = $this->$function($rule, $result[$field]);

					if(!$funres && $rule[3] === null)
					{
						$this->errors[] = $rule[2];
						unset($result[$field]);
						break;
					}
					else if(!$funres)
					{
						$result[$field] = $rule[3];
					}
				}
			}

			if(!$this->errors && $mapping)
			{
				$result[$mapping] = $result[$field];
				unset($result[$field]);
			}
		}

		return $result;
	}

	public function obtainArray($values, $params)
	{
		$result = array(); $request = array(); $l = null;
		foreach($params as $field => $rules)
		{
			if(isset($values[$field]) && is_array($values[$field]))
			{
				if(!isset($l))
				{
					$l = count($values[$field]);
				}
				else if($l != count($values[$field]))
				{
					$this->errors[] = 'params count error.';
					break;
				}
			}
			else
			{
				$this->errors[] = 'params is null or is not array.';
				break;
			}
		}

		if(!$this->errors)
		{
			foreach($params as $field => $rules)
			{
				foreach($values[$field] as $k => $v)
				{
					$request[$k][$field] = $v;
				}
			}

			foreach($request as $k => $v)
			{
				$item = $this->obtain($v, $params);
				if($this->errors)
				{
					break;
				}
				$result[] = $item;
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

	public function validIsSet($rule, $value)
	{
		return isset($value);
	}

	public function validEq($rule, $value)
	{
		return $value == $rule[4];
	}

	public function validGt($rule, $value)
	{
		return $value > $rule[4];
	}

	public function validGte($rule, $value)
	{
		return $value >= $rule[4];
	}

	public function validLt($rule, $value)
	{
		return $value < $rule[4];
	}

	public function validLte($rule, $value)
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

	public function validNum($rule, $value)
	{
		return preg_match('/^\d+(?:\.\d+)?(?:[Ee]\d+(?:\.\d+)?)?$/', $value);
	}

	public function validUrl($rule, $value)
	{
		return preg_match('/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//', $value);
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

	public function validTime($rule, $value)
	{
		return (strtotime($value) !== false);
	}

	public function validFunction($rule, $value)
	{
		return call_user_func($rule[4], $value);
	}
}
