<?php
class ActionIndex extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();
	}

	public function methodIndex()
	{
		$memberObj = Factory::getModel('member');
		if($memberObj->isLogin())
		{
			$this->redirect('/index.php?a=member&m=home');
		}
		else
		{
			$this->redirect('/index.php?a=member&m=login');
		}
	}

	public function methodCaptcha()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'wh' => array(array('valid', 'regex', '', '200x70', '/\d+x\d+$/'))
		));
		$params['wh'] = explode('x', $params['wh']);

		Factory::loadLibrary('imagehelper');
		$imagehelper = new ImageHelper();
		$image = &$imagehelper->captcha($this->captchaCreate(), array('width' => $params['wh'][0], 'height' => $params['wh'][1]));
		header("Content-type: image/gif");
		Imagegif($image);
		ImageDestroy($image);
		exit;
	}
}
