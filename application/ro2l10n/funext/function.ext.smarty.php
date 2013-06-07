<?php
function mapping_state_format($value)
{
	switch($value)
	{
		case MAPPING_STATE_TW: $value = '待翻'; break;
		case MAPPING_STATE_TD: $value = '正翻'; break;
		case MAPPING_STATE_TB: $value = '退翻'; break;
		case MAPPING_STATE_PW: $value = '待校'; break;
		case MAPPING_STATE_PD: $value = '正校'; break;
		case MAPPING_STATE_PB: $value = '退校'; break;
		case MAPPING_STATE_AW: $value = '待审'; break;
		case MAPPING_STATE_AD: $value = '正审'; break;
		case MAPPING_STATE_AB: $value = '退审'; break;
		case MAPPING_STATE_CP: $value = '完成'; break;
		default: $value = '未知'; break;
	}
	echo $value;
}
