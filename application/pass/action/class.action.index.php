<?php
class ActionIndex extends ActionCommon
{
	public function __construct()
	{
		parent::__construct();

		include_once(APP_DIR_MSGCODE . str_replace('Action', 'define.action.', __CLASS__) . '.php');
	}

	public function methodIndex()
	{
		$memberObj = Factory::getModel('member');
		$m = $memberObj->isLogin() ? 'home' : 'login';
		$this->redirect('/index.php?a=member&m=' . $m);
	}

	public function methodCaptcha()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'wh' => array(array('valid', 'regex', '', '200x70', '/^\d+x\d+$/'))
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

	public function methodSetHomepage()
	{
	}

	public function methodIntl()
	{
		// 获取参数
		$params = $this->_submit->obtain($_REQUEST, array(
			'article' => array(array('valid', 'isset', '', '', 0), array('format', 'trim')),
		));

		if(count($this->_submit->errors) > 0 || !file_exists(APP_DIR_TEMPLATE . 'index_intl/' . $params['article'] . '.html'))
		{
			$this->message(MCGetC('ACON_PAGE_404'));
		}

		$this->assign('article', $params['article']);
	}
}
