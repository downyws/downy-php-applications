<?php

class Submit
{
	protected $errors = array();

	public function getError($is_all = false)
	{
		return $is_all ? $this->errors : end($this->errors);
	}

	public function filter($rules)
	{
		$this->errors = array();
		$res = array();

		foreach($rules as $field => $rule)
		{
			if(!isset($_REQUEST[$field]) || (is_string($_REQUEST[$field]) && strlen(trim($_REQUEST[$field])) == 0))
			{
				$val = '';
			}
			else
			{
				$val = $_REQUEST[$field];
			}

			if(!empty($rule['valid']) && ($val != '' && is_array($rule['valid'])))
			{
				if($error = $this->valid($val, $rule['valid'], $_REQUEST))
				{
					$this->errors[$field] = $error;
					continue;
				}
			}

			if(!empty($rule['complete']) && is_array($rule['complete']))
			{
				$val = $this->complete($val, $rule['complete'], $_REQUEST);
			}

			$res[$field] = $val;
		}

		return count($this->errors) > 0 ? false : $res;
	}

	public function valid($val, &$rules, &$vals)
	{
		if(!is_array($rules[0]))
		{
			$rules = array($rules);
		}

		foreach($rules as $rule)
		{
			switch($rule[0])
			{
				case 'confirm':
					$res = $val == $vals[$rule[2]];
					break;
				default:
					$method = 'valid' . ucfirst(strtolower($rule[0]));
					$res = empty($rule[2]) ? $this->$method($val) : $this->$method($val, $rule[2]);
			}

			if(!$res)
			{
				return $rule[1];
			}
		}

		return false;
	}

	public function complete($val, &$rules, &$vals)
	{
		if(!is_array($rules[0]))
		{
			$rules = array($rules);
		}

		foreach($rules as $rule)
		{
			switch($rule[0])
			{
				case 'value':
					empty($val) && $val = $rule[1];
					break;
				case 'field':
					$val = $vals[$rule[0]];
					break;
				case 'function':
					$val = $rule[0]($val, $vals);
					break;
				default:
					$method = 'complete' . ucfirst(strtolower($rule[0]));
					$val = !isset($rule[1]) ? $this->$method($val) : $this->$method($val, $rule[1]);
			}
		}

		return $val;
	}

	public function validInt($val)
	{
		return preg_match('/^\d+$/', $val);
	}

	public function validIn($val, $arr)
	{
		return in_array($val, $arr);
	}

	public function validEq($val, $org)
	{
		return $val == $org;
	}

	public function validGt($val, $org)
	{
		return $val > $org;
	}

	public function validLt($val, $org)
	{
		return $val < $org;
	}

	public function validBetween($val, $range)
	{
		return $val >= $range[0] && $val >= $range[1];
	}

	public function validUrl($val)
	{
		return preg_match('/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//', $val);
	}

	public function validNum($val)
	{
		return preg_match('/^\d+(?:\.\d+)?(?:[Ee]\d+(?:\.\d+)?)?$/', $val);
	}

	public function validRegex($val, $regex)
	{
		return preg_match($regex, $val);
	}

	public function validFunc($val, $callback)
	{
		return call_user_func($callback, $val);
	}

	public function validEmail($val)
	{
		return preg_match('/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/', $val);
	}

	public function validZipcode($val)
	{
		return preg_match('/^\d{6}$/', $val);
	}

	public function validMobile($val)
	{
		return preg_match('/^1[358]\d{9}$/', $val);
	}

	public function validSet($val)
	{
		return !empty($val);
	}

	public function validTel($val)
	{
		return preg_match('/^\+?\d+(?:-\d+)$/', $val);
	}

	public function completeSet($val, $default)
	{
		return $default;
	}

	public function completeInt($val, $default = 0)
	{
		$val = intval($val);

		return $val ? $val : $default;
	}

	public function completeFloat($val, $default = 0.0)
	{
		$val = floatval($val);

		return $val ? $val : $default;
	}

	public function completeGt($val, $params)
	{
		if(!is_array($params))
		{
			$params = array($params, $params + 1);
		}

		$val = floatval($val);

		return $val > $params[0] ? $val : $params[1];
	}

	public function completeLt($val, $params)
	{
		if(!is_array($params))
		{
			$params = array($params, $params - 1);
		}

		$val = floatval($val);

		return $val < $params[0] ? $val : $params[1];
	}

	public function completeIn($val, $arr)
	{
		if(empty($arr) or !is_array($arr))
		{
			throw new Exception('Array invalid.');
		}

		return in_array($val, $arr) ? $val : $arr[0];
	}

	public function completeBetween($val, $params)
	{
		if(empty($params) or !is_array($params) or count($params) < 3)
		{
			throw new Exception('Array invalid.');
		}

		return ($val >= $params[0] and $val <= $params[1]) ? $val : $params[2];
	}

	public function completeTrim($val, $chars = null)
	{
		if($chars)
		{
			return trim($val, $chars);
		}
		return trim($val);
	}

	public function completeTimestamp($val, $default = 0)
	{
		$val = strtotime($val);
		return ($val === false) ? $default : $val;
	}
}
