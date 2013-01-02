<?php
include_once(APP_DIR_CONNECT . 'connect.oauth2.php');

class ConnectQQ extends ConnectOauth2
{
	public $_name = 'qq';
	public $_id;
	public function __construct($config)
	{
		parent::__construct($config);
		$this->_id = $this->_connectObj->getId($this->_name);
	}

	public function routeAuthorize()
	{
		header('location: ' . $this->getAuthorizeUrl());
	}

	public function routeVerify()
	{
		$code = !empty($_REQUEST['code']) ? $_REQUEST['code'] : '';

		$token_data = $this->getToken($code);
		if($token_data === false || !empty($token_data['error']))
		{
			$this->_action->message(GET_TOKEN_FAILED);
		}

		$open_data = $this->getOpenId($token_data['access_token']);
		if($open_data === false || !empty($open_data['error']))
		{
			$this->_acion->message(GET_OPENID_FAILED);
		}

		$this->login($this->_id, $open_data["openid"], $token_data);
	}

	public function getAuthorizeUrl()
	{
		$url = 'https://graph.qq.com/oauth2.0/authorize';
		$data = array(
			'response_type' => 'code',
			'client_id' => $this->_config['A_KEY'],
			'redirect_uri' => $this->getRedirectUrl()
		);
		return $this->_curl->assemblyGet($url, $data);
	}

	public function getRedirectUrl()
	{
		return $this->_base_url . '&r=verify';
	}

	public function getToken($code)
	{
		$url = 'https://graph.qq.com/oauth2.0/token';
		$data = array
		(
			'client_id' => $this->_config['A_KEY'],
			'client_secret' => $this->_config['S_KEY'],
			'grant_type' => 'authorization_code',
			'redirect_uri' => $this->getRedirectUrl(),
			'code' => $code
		);
		$url = $this->_curl->assemblyGet($url, $data);
		$result = $this->_curl->request($url, null);
		if($result !== false)
		{
			$result = $this->formatData($result['body'], 'url');
		}
		return $result;
	}

	public function getOpenId($token)
	{
		$url = 'https://graph.qq.com/oauth2.0/me';
		$data = array
		(
			'access_token' => $token
		);
		$url = $this->_curl->assemblyGet($url, $data);
		$result = $this->_curl->request($url, null);
		if($result !== false)
		{
			$result = $this->formatData($result['body'], 'callback');
		}
		return $result;
	}
}
