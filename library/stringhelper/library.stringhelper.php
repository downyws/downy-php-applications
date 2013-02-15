<?php
class StringHelper
{
	public $_data_type = array(
		'email' => '/^[\w\._]+@(?:[\w-]+\.)+\w{2,4}$/',
		'mobile' => '/^1[358]\d{9}$/',
		'telephone' => '/^\+?\d+(?:-\d+)$/',
		'url' => '/^https?:\/\/([0-9a-z-]+\.)+[a-z]{2,4}\//',
		'zip' => '/^\d{6}$/'
	);

	public function dataTypeTrue($value, $type)
	{
		return empty($this->_data_type[$type]) ? false : !!preg_match($this->_data_type[$type], $value);
	}

	public function intToWord($value, $language)
	{
		$fun = 'intTo' . $language . 'word';
		if(method_exists(__CLASS__, $fun))
		{
			return $this->$fun($value);
		}
		else
		{
			throw new Exception('Unknow this language.');
		}
	}

	public function intToCnWord($value)
	{
		if(is_int($value) && strlen($value) <= 12)
		{
			if($value < 0)
			{
				$is_minus = true;
				$value = abs($value);
			}
			else
			{
				$is_minus = false;
			}

			$w = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '百', '千', '万', '亿', '负');
			$r = '';
			$value = str_pad($value, 12, '0', STR_PAD_LEFT);
			$v = array(substr($value, 0, 4), substr($value, 4, 4), substr($value, 8, 4));	// 将数字分成三等份

			for($i = 0; $i < 3; $i++)
			{
				// 当前组全部为0
				if($v[$i] == '0000')
				{
					// 当前是中间组，并且前后两组不全为0
					if($i == 1 && $v[$i - 1] != '0000' && $v[$i + 1] != '0000') $r = $r . $w[0];
					continue;
				}

				$t = '';

				// 个位不为0
				if($v[$i][3] != 0) $t = $w[$v[$i][3]];					// 个位

				// 十位不为0
				if($v[$i][2] != 0) $t = $w[$v[$i][2]] . $w[10] . $t;	// 十位
				// 十位为0 且 个位和百位不为0
				else if($v[$i][3] != 0 && $v[$i][1] != 0) $t = $w[0] . $t;

				// 百位不为0
				if($v[$i][1] != 0) $t = $w[$v[$i][1]] . $w[11] . $t;	// 百位
				// 百位为0 且 十（个）位和千位不为0
				else if(($v[$i][3] != 0 || $v[$i][2] != 0) && $v[$i][0] != 0) $t = $w[0] . $t;

				// 千位不为0
				if($v[$i][0] != 0) $t = $w[$v[$i][0]] . $w[12] . $t;	// 千位
				// 前一段是否有数字
				else if($i != 0 && $v[$i - 1] != '0000') $t = $w[0] . $t;

				if($t != '') $r = $r . $t . (($i != 2) ? $w[14 - $i] : '');
				// 后一段是否有数字
				else if($t == '' && $i != 2 && $v[$i + 1] != '0000') $r = $r . $w[0];
			}

			// 负数
			if($is_minus) $r = end($w) . $r;

			return $r;
		}
		else
		{
			throw new Exception('Unknow is num.');
		}
		return false;
	}
}
