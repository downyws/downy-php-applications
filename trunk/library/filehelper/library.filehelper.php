<?php
class FileHelper
{
	public function getDirSize($path)
	{
		$handle = opendir($path);
		$size = 0;
		while(($file = readdir($handle)) !== false)
		{
			if($file != "." && $file != "..")
			{
				$size += is_dir("$path/$file") ? $this->getDirSize("$path/$file") : filesize("$path/$file");
			}
		}
		closedir($handle);
		return $size;
	}

	public function getEncode($file)
	{
		// mb_convert_encoding
		$string = file_get_contents($file);

		if($string === @iconv('UTF-8', 'ASCII', @iconv('ASCII', 'UTF-8', $string)))
		{
			return 'ASCII';
		}
		else if($string === @iconv('UTF-8', 'GB2312', @iconv('GB2312', 'UTF-8', $string)))
		{
			return 'GB2312';
		}
		else if(chr(239) . chr(187) . chr(191) == substr($string, 0, 3))
		{
			return 'UTF-8 BOM';
		}
		else if($string === @iconv('UTF-8', 'UTF-8', @iconv('UTF-8', 'UTF-8', $string)))
		{
			return 'UTF-8';
		}
		return 'UNKNOW';
	}
}
