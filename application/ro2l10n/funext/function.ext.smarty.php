<?php
function mapping_state_format($value)
{
	switch($value)
	{
		case MAPPING_STATE_TW: $value = 'TW'; break;
		case MAPPING_STATE_TD: $value = 'TD'; break;
		case MAPPING_STATE_TB: $value = 'TB'; break;
		case MAPPING_STATE_PW: $value = 'PW'; break;
		case MAPPING_STATE_PD: $value = 'PD'; break;
		case MAPPING_STATE_PB: $value = 'PB'; break;
		case MAPPING_STATE_AW: $value = 'AW'; break;
		case MAPPING_STATE_AD: $value = 'AD'; break;
		case MAPPING_STATE_AB: $value = 'AB'; break;
		case MAPPING_STATE_CP: $value = 'CP'; break;
		default: $value = 'UN'; break;
	}
	echo $value;
}
