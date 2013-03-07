<?php
class YoukuHelper
{
    public function getVideo($id)
    {
		$config = array(
			'referer_url' => 'http://v.youku.com/v_show/id_',
			'base_url' => 'http://v.youku.com/player/getPlayList/VideoIDS/',
			'real_url' => 'http://f.youku.com/player/getFlvPath/sid/',
			'params' => array(
				'segments' => '~"segs"\s*:\s*\{(.*\])\}\s*,~iUs',
				'seed' => '~"seed"\s*:\s*(\d+)\s*,~iUs',
				'encoded' => '~\{\s*"(flv|mp4|hd2|hd1)"\s*:\s*"(.*)"\s*[,\}]~iUs',
				'play_unit' => '~"(.*)"\s*:\s*\[(.*)\]~iUs',
				'play' => '~\{"no":"*(\d+)"*,"size":"(\d+)","seconds":"(\d+)","k":"(.*)".*\}~iUs'
			)
		);

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/\:._-1234567890';
		$hexarr = '0123456789ABCDEF';

		// 抓取内容
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $config['base_url'] . $id);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_REFERER, $config['referer_url'] . $id);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_NOBODY, 0);
		$content = curl_exec($curl);
		curl_close($curl);

		// 拆解内容
		preg_match($config['params']['segments'],	$content, $segments);
		preg_match($config['params']['seed'],		$content, $seed);
		preg_match($config['params']['encoded'],	$content, $encoded);
		preg_match($config['params']['play_unit'], $segments[1], $play_unit);
		preg_match_all($config['params']['play'], $play_unit[2], $play);

		// 解析内容
		$fileid = '';
		$use_chars = '';
        $random = intval($seed[1]);
		$len = strlen($chars);
		for($i = 0; $i < $len; $i++)
		{
	    	$random = ($random * 211 + 30031) % 65536;
			$char = intval($random / 65536 * strlen($chars));
			$use_chars .= $chars[$char];
			$chars = str_replace($chars[$char], '', $chars);
		}
		$cipher = explode('*', $encoded[2]);
		for($i = 0; $i < count($cipher) - 1; $i++)
		{
			$fileid .= $use_chars[intval($cipher[$i])];
		}
		if($fileid == '-3')
		{
			return false;
		}
		$prefix = substr($fileid, 0, 8);
		$suffix = substr($fileid, 10, strlen($fileid));

		$sid = time() . mt_rand(10, 99) . '1000' . mt_rand(30, 80) . '00';
		$urls = array();
		for($i = 0; $i < count($play[1]); $i++)
		{
			$dec = intval($play[1][$i]);
			$code = ($dec < 16) ? ('0' . $hexarr[$dec]) : ($hexarr[intval($dec / 16)] . $hexarr[$dec % 16]);
			$url = $config['real_url'] . $sid . '_'. $code . '/st/';
			$url .= ($encoded[1] == 'hd2') ? 'flv' : $encoded[1];
			$url .= '/fileid/' . $prefix . $code . $suffix . '?K=' . $play[4][$i];
			$url .= ($encoded[1] == 'hd2') ? '&hd=2' : '';

			$urls[] = $url;
			// video long $play[3][$i] second
	 	}
		return $urls;
    }
}
