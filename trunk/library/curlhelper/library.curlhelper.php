<?php
class CurlHelper
{
	public $_curl = null;
	public $_split_line = "\r\n\r\n";
	public $_useragents = array('file' => '', 'size' => 0, 'data' => array());

	public function __construct($config)
	{
		$this->_useragents['file'] = dirname(dirname(__FILE__)) . '/curlhelper/useragent.tdb';
		$this->_config = $config;
		if($this->_config['USERAGENT']['OPEN'] && is_numeric($this->_config['USERAGENT']['VALUE']))
		{
			$this->setUseragent($this->_config['USERAGENT']['VALUE']);
		}
	}

	public function download($url, $post, $save_path)
	{
		// 打开CURL
		$referer_lock = $this->_config['REFERER']['LOCK'];
		$this->_config['REFERER']['LOCK'] = true;
		$this->curl_open($url, $post);
		$this->_config['REFERER']['LOCK'] = $referer_lock;
		curl_setopt($this->_curl, CURLOPT_HEADER, false);

		// 获取请求结果
		$response = curl_exec($this->_curl);
		$code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);

		// 关闭CURL
		curl_close($this->_curl);

		// 保存文件
		$file_name = date('Y_m_d_H_i_s_') . substr(round(microtime(1) * 1000), -3) . '_' . mt_rand();
		if($response && $code >= 200 && $code < 400)
		{
			if(file_put_contents($save_path . '/' . $file_name, $response))
			{
				return $file_name;
			}
		}
		return null;
	}

	public function request($url, $post, $split = true)
	{
		// 打开CURL
		$this->curl_open($url, $post);

		// 获取请求结果
		$response = curl_exec($this->_curl);
		$response = ($response !== false && $split) ? $this->format($response) : $response;

		// 关闭CURL
		curl_close($this->_curl);

		return $response;
	}

	public function curl_open($url, $post)
	{
		// 创建CURL对象
		$this->_curl = curl_init();

		// 代理设置
		if(!empty($this->_config['PROXY']))
		{
			curl_setopt($this->_curl, CURLOPT_PROXY, $this->_config['PROXY']);
			curl_setopt($this->_curl, CURLOPT_PROXYPORT, $this->_config['PROXYPORT']);
		}

		// HTTPS设置
		if(preg_match('/^https/i', $url))
		{
			curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, false);
		}

		// POST设置
		if(!empty($post) && is_array($post) && count($post) > 0)
		{
			curl_setopt($this->_curl, CURLOPT_POST, 1);
			$data = array();
			foreach($post as $k => $v)
			{
				$data[] = $k . '=' . urlencode($v);
			}
			$data = implode('&', $data);
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
		}

		// COOKIE设置
		if($this->_config['COOKIE']['OPEN'])
		{
			curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $this->_config['COOKIE']['PATH']);
		}
		if($this->_config['COOKIE']['OPEN'] && !$this->_config['COOKIE']['LOCK'])
		{
			curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_config['COOKIE']['PATH']);
		}

		// REFERER设置
		if($this->_config['REFERER']['OPEN'])
		{
			curl_setopt($this->_curl, CURLOPT_REFERER, $this->_config['REFERER']['VALUE']);
		}
		if($this->_config['REFERER']['OPEN'] && !$this->_config['REFERER']['LOCK'])
		{
			$this->_config['REFERER']['VALUE'] = $url;
		}

		// USERAGENT设置
		if($this->_config['USERAGENT']['OPEN'])
		{
			curl_setopt($this->_curl, CURLOPT_USERAGENT, $this->_config['USERAGENT']['VALUE']);
		}
		
		// 是否自动重定向
		if($this->_config['AUTO_REDIRECT_COUNT'])
		{
			curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, $this->_config['AUTO_REDIRECT_COUNT'] > 0); 
			curl_setopt($this->_curl, CURLOPT_MAXREDIRS, $this->_config['AUTO_REDIRECT_COUNT']); 
		}

		// 请求设置
		curl_setopt($this->_curl, CURLOPT_URL, $url);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_config['TIMEOUT']);
		curl_setopt($this->_curl, CURLOPT_ENCODING, $this->_config['ENCODING']);
		curl_setopt($this->_curl, CURLOPT_HEADER, true);
	}

	public function setUseragent($index = 0, $useragent = '')
	{
		// 自定义Useragent内容
		if(!empty($useragent))
		{
			$this->_config['USERAGENT']['VALUE'] = $useragent;
			return;
		}

		// 加载Useragent列表文件
		if(empty($this->_useragents['data']))
		{	
			$content = '';
			$handle = fopen($this->_useragents['file'], "r");
			while(!feof($handle))
			{
				$content .= fgets($handle);
			}
			fclose($handle);
			$this->_useragents['data'] = explode("\n", $content);
			$this->_useragents['size'] = count($this->_useragents['data']);
		}

		// 选择Useragent
		if($index <= 0 || $index > $this->_useragents['size'])
		{
			$index = rand(0, $this->_useragents['size'] - 1);
		}

		// 保存
		$this->_config['USERAGENT']['VALUE'] = $this->_useragents['data'][$index];
	}

	public function format($response)
	{
		$result = array('code' => 0, 'header' => array(), 'body' => '');

		// 取出分割点
		$split_point = strpos($response, $this->_split_line);
		if($split_point)
		{
			$result['header'] = substr($response, 0, $split_point);
			// 取出响应状态码
			if(preg_match('/\d{3}/', $result['header'], $m))
			{
				$result['code'] = $m[0];
			}
			// 取出响应header
			if(preg_match_all('/^([^ :]+) *: *(.+?)$/mi', $result['header'], $m))
			{
				$header = array();
				foreach($m[0] as $k => $v)
				{
					$header[$m[1][$k]] = trim($m[2][$k]);
				}
				$result['header'] = $header;
			}
			// 取出响应body
			$result['body'] = substr($response, $split_point + 4);
		}

		return $result;
	}

	public function assemblyGet($url, $get)
	{
		$temp = array();
		foreach($get as $k => $v)
		{
			$temp[] = $k . '=' . urlencode($v);
		}
		return $url . (strpos($url, '?') === false ? '?' : '&') . implode('&', $temp);
	}
}
