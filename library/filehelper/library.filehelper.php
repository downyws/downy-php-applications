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
}
