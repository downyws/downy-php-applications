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
		Factory::loadLibrary('imagehelper');
		$imagehelper = new ImageHelper();
		$image = &$imagehelper->captcha($this->captchaCreate(), array('width' => 200, 'height' => 70));
		header("Content-type: image/gif");
		Imagegif($image);
		ImageDestroy($image);
		exit;
	}
}
