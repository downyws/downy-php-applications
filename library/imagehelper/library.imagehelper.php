<?php
class ImageHelper
{
	public $_fonts = null;

	public function _initFonts()
	{
		if(!isset($this->_fonts))
		{
			$path = dirname(__FILE__) . '/fonts/';
			$handle = opendir($path);
			while(false !== ($file = readdir($handle)))
			{
				if($file != '.' && $file != '..')
				{
					$this->_fonts[] = array('name' => $file, 'path' => $path . $file);
				}
			}
		}
	}

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

	public function &captcha($code, $options)
	{
		$this->_initFonts();

		// 颜色
		$ftcolor = array('r' => 0, 'g' => 0, 'b' => 0);
		$bgcolor = array('r' => 255, 'g' => 255, 'b' => 255);

		// 创建画板
		$image = ImageCreate($options['width'], $options['height']);
		$ftcolor = ImageColorAllocate($image, $ftcolor['r'], $ftcolor['g'], $ftcolor['b']);
		$bgcolor = ImageColorAllocate($image, $bgcolor['r'], $bgcolor['g'], $bgcolor['b']);
		ImageFill($image, 0, 0, $bgcolor);

		// 创建字体
		$font = count($this->_fonts) - 1;
		$font = $this->_fonts[mt_rand(0, $font)]['path'];

		// 计算大小位置 
		$font_size = floor(0.9 * $options['width'] / strlen($code));
		$pos = array('x' => $options['width'] * 0.05, 'y' => ($options['height'] - $font_size) / 2 + $font_size);
		ImageTtfText($image, $font_size, 0, $pos['x'], $pos['y'], $ftcolor, $font, $code);

		return $image;
	}

	public function clipping($filename, $destination, $top, $left, $width, $height)
	{
		// 获取图像信息
		$info = getimagesize($filename);
		
		// 检查图像&参数
		if($info === false)
		{
			return false;
		}
		if($top < 0 || $left < 0 || $width < 0 || $height < 0)
		{
			return false;
		}

		// 绘图
		switch($info[2])
		{
			case IMAGETYPE_GIF: $src_im = imagecreatefromgif($filename); break;
			case IMAGETYPE_JPEG: $src_im = imagecreatefromjpeg($filename); break;
			case IMAGETYPE_PNG: $src_im = imagecreatefrompng($filename); break;
			default: throw new Exception('This file type is not supported.'); break;
		}
		$dst_im = imagecreatetruecolor($width, $height);
		$color = imagecolorallocatealpha($dst_im, 255, 255, 255, 0);
		imagefill($dst_im, 0, 0, $color);
		imagecopy($dst_im, $src_im, 0, 0, $left, $top, $width, $height);
		switch($info[2])
		{
			case IMAGETYPE_JPEG: imagejpeg($dst_im, $destination, 100); break;
			case IMAGETYPE_PNG: imagepng($dst_im, $destination); break;
			case IMAGETYPE_GIF: imagegif($dst_im, $destination); break;
		}

		return true;
	}
}
