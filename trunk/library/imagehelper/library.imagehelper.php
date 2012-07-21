<?php
class ImageHelper
{
	public function getVersion()
	{
		static $version = array(-2, '');
		
		if($version[0] == -2)
		{
			if(!extension_loaded('gd'))
			{
				$version = 0;
			}
			else
			{
				if(PHP_VERSION >= '4.3')
				{
					if(function_exists('gd_info'))
					{
						$ver_info = gd_info();
						preg_match('/\d/', $ver_info['GD Version'], $match);
						$version = $match[0];
					}
					else
					{
						if (function_exists('imagecreatetruecolor'))
						{
							$version = 2;
						}
						else if (function_exists('imagecreate'))
						{
							$version = 1;
						}
					}
				}
				else
				{
					if(preg_match('/phpinfo/', ini_get('disable_functions')))
					{
						$version = -1;
					}
					else
					{
						ob_start();
						phpinfo(8);
						$info = ob_get_contents();
						ob_end_clean();
						$info = stristr($info, 'gd version');
						preg_match('/\d/', $info, $match);
						$version = $match[0];
					}
				}
			}
		}

		switch(intval($version))
		{
			case 0:
				$version = array(0, 'N/A');
				break;
			case 1:
				$version = array(1, 'GD1');
				break;
			case 2:
				$version = array(2, 'GD2');
				break;
			default:
				$version = array(-1, 'Unknow');
				break;
		}

		return $version;
	}
}
