<?php
class MathHelper
{
	public function fromNto10($from, $num)
	{
		$result = "";
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$chars = substr($chars, 0, $from);

		if($num != "" && !preg_replace("/[" . $chars . "]/i", '', $num) != "")
		{
			$temp = 1;
			for($i = strlen($num) - 1; $i > -1; $i--)
			{
				$result = $result + ($temp * strpos($chars, $num[$i]));
				$temp = $temp * $from;
			}
		}
		return $result;
	}

	public function from10toN($to, $num)
	{
		$result = "";
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$chars = substr($chars, 0, $to);

		if($num != "" && intval($num) == $num)
		{
			$temp = 0;
			while($num != 0)
			{
				$temp = $num % $to;
				$result = $chars[$temp] . $result;
				$num = ($num - $temp) / $to;
			}
		}
		return $result;
	}

	public function fromN2N($from, $to, $num)
	{
		$num = ($from != 10) ? $this->fromNto10($from, $num) : $num;
		$num = ($num != "" && $to != 10) ? $this->from10toN($to, $num) : $num;
		return $num;
	}
}
