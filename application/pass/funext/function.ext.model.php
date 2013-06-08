<?php
function report_format($value)
{
	if($value['type'])
	{
		return report_inout($value);
	}
	else
	{
		return report_log($value);
	}
}

function report_inout($value)
{
	switch($value['type'])
	{
		case INOUT_TPYE_IN:
			$value['cate'] = '操作';
			$value['text'] = '登录系统';
			break;
		case INOUT_TPYE_OUT:
			$value['cate'] = '操作';
			$value['text'] = '登出系统';
			break;
		default: 
			$value = report_unknow($value, 'inout');
			break;
	}
	return $value;
}

function report_log($value)
{
	$data = parse_url($value['request_url']);
	parse_str($data['query'], $data);
	$data = array_merge($data, unserialize($value['request_data']));

	switch($data['a'] . $data['m'])
	{
		case 'memberregister':
			$value['cate'] = '注册';
			$value['text'] = '注册成为会员';
			break;
		case 'membermypassword':
			$value['cate'] = '编辑';
			$value['text'] = '修改密码';
			break;
		case 'membermyqanda':
			$value['cate'] = '编辑';
			$value['text'] = '编辑保密问题';
			break;
		case 'membermymobile':
			if($data['step'] == 1)
			{
				$value['cate'] = '编辑';
				$value['text'] = '绑定手机号码';
			}
			else if($data['step'] == 2)
			{
				$value['cate'] = '编辑';
				$value['text'] = '解除手机号码绑定';
			}
			else
			{
				$value = report_unknow($value, 'log');
			}
			break;
		case 'membermyemail':
			if($data['step'] == 1)
			{
				$value['cate'] = '编辑';
				$value['text'] = '绑定电子邮箱';
			}
			else if($data['step'] == 2)
			{
				$value['cate'] = '编辑';
				$value['text'] = '解除电子邮箱绑定';
			}
			else
			{
				$value = report_unknow($value, 'log');
			}
			break;
		case 'memberbasemodify':
			switch($data['field'])
			{
				case 'birthday':
					$value['cate'] = '编辑';
					$value['text'] = '修改与生日相关的信息';
					break;
				case 'blood':
					$value['cate'] = '编辑';
					$value['text'] = '修改与血型相关的信息';
					break;
				case 'email':
					$value['cate'] = '编辑';
					$value['text'] = '修改与邮箱隐私权限';
					break;
				case 'mobile':
					$value['cate'] = '编辑';
					$value['text'] = '修改与手机隐私权限';
					break;
				case 'name':
					$value['cate'] = '编辑';
					$value['text'] = '修改姓名';
					break;
				case 'portrait':
					$value['cate'] = '编辑';
					$value['text'] = '修改头像';
					break;
				case 'sex':
					$value['cate'] = '编辑';
					$value['text'] = '修改与性别相关的信息';
					break;
				case 'sign':
					$value['cate'] = '编辑';
					$value['text'] = '修改签名';
					break;
				default:
					$value = report_unknow($value, 'log');
					break;
			}
			break;
		case 'supportresetpassword':
			$value['cate'] = '编辑';
			$value['text'] = '密码重置';
			break;
		default:
			$value = report_unknow($value, 'log');
			break;
	}
	return $value;
}

function report_unknow($value, $ext_cate)
{
	$value['cate'] = '未知';
	$value['text'] = '未知日志类型。';
	$value['ext'] = "解析日志发生错误。\n编号：" . $value['id'] . "\t\t分类：" . $ext_cate . "\t\t校验码：" . md5($value['request_data']);
	return $value;
}

function rand_qanda()
{
	$result = array();
	switch(mt_rand(0, 2))
	{
		case 1:
			$num = array(mt_rand(200, 999), mt_rand(0, 99), mt_rand(0, 99));
			$result['question'] = $num[0] . ' - ' . $num[1] . ' - ' . $num[2] . ' = ?';
			$result['answer'] = $num[0] - $num[1] - $num[2];
			break;
		case 2:
			$num = array(mt_rand(0, 9), mt_rand(0, 9), mt_rand(0, 9));
			$result['question'] = $num[0] . ' * ' . $num[1] . ' * ' . $num[2] . ' = ?';
			$result['answer'] = $num[0] * $num[1] * $num[2];
			break;
		case 0:
		default:
			$num = array(mt_rand(0, 99), mt_rand(0, 99), mt_rand(0, 99));
			$result['question'] = $num[0] . ' + ' . $num[1] . ' + ' . $num[2] . ' = ?';
			$result['answer'] = $num[0] + $num[1] + $num[2];
			break;
	}
	return $result;
}

function islike_rand_qanda($q, $a)
{
	if(preg_match('/^\d{1,3}(\s[+\-*]\s\d{1,3}){2}\s\=\s\?$/', $q))
	{
		$q = substr($q, 0, -3);
		$q = eval("return $q;");
		return $q == $a;
	}
	return false;
}

function content_fetch($tpl, $data)
{
	require_once LIBRARY_DIR . 'smarty/library.smarty.php';
	$writer = new Smarty();
	$writer->cache_dir = APP_DIR_CACHE . 'smarty/page/';
	$writer->compile_dir = APP_DIR_CACHE . 'smarty/compile/';
	$writer->left_delimiter = '{';
	$writer->right_delimiter = '}';
	$writer->error_reporting = E_ALL;
	$writer->assign('data', $data);
	return $writer->fetch($tpl);
}

function send_email($target, $tpl, $data)
{
	$content = content_fetch($tpl, $data);
	$content = explode('<!--#split#-->', $content);

	Factory::loadLibrary('phpmailer');
	$config = $GLOBALS['CONFIG']['EMAIL'];
	$phpmailer = new PHPMailer(true);
	$phpmailer->IsSMTP();
	$phpmailer->SMTPAuth = $config['SMTPAuth'];
	$phpmailer->Port = $config['Port'];
	$phpmailer->Host = $config['Host'];
	$phpmailer->Username = $config['Username'];
	$phpmailer->Password = $config['Password'];
	$phpmailer->From = $config['From'];
	$phpmailer->FromName = $config['FromName'];
	$phpmailer->IsHTML($config['IsHTML']);
	$phpmailer->CharSet = $config['CharSet'];

	try
	{
		$phpmailer->AddAddress($target);
		$phpmailer->Subject = $content[0];
		$phpmailer->MsgHTML($content[1]);
		return $phpmailer->Send();
	}
	catch(phpmailerException $e)
	{
		$logs = new Logs();
		$logs->message('Error: Send email Failed, see ' . $logs->attachment(array($e->errorMessage())));
	}
	return false;
}

function send_mobile($target, $tpl, $data)
{
	$content = content_fetch($tpl, $data);

	return true;
}