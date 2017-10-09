<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename my.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 784145127 2252 $
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}




function my_member_validate($uid,$email,$role_id='',$new=0,$check_allow=1)
{
	if(1 > ($uid = (int) $uid)) {
		return false;
	}
	if(!($email = trim($email))) {
		return false;
	}

	$sys_config = jconf::get();
	if($new == 0 && !$sys_config['reg_email_verify']) {
		return false;
	}

	
	if($check_allow && jdisallow($uid)) {
		return false;
	}

	$sql = "select * from `".TABLE_PREFIX."member_validate` where `uid`='{$uid}' order by `regdate` asc";
	$query = DB::query($sql);
	$data = array();
	if((DB::num_rows($query)) > 0) {
		DB::query("delete from `".TABLE_PREFIX."member_validate` where `uid`='$uid'");
	}

	$data['uid'] = $uid;
	$data['email'] = $email;
	$data['role_id'] = (int) ($role_id > 0 ? $role_id : $sys_config['normal_default_role_id']);
	$data['key'] = substr(md5(md5($uid . $email . $role_id) . md5(uniqid(mt_rand(),true))),3,16);
	$data['status'] = $data['verify_time'] = '0';
	$data['regdate'] = TIMESTAMP;
	$data['type'] = 'email';

	jtable('member_validate')->insert($data);

	$email_message="您好：
您收到此邮件是因为在 {$sys_config['site_url']} 用户注册中使用了该 Email，
如果您没有进行上述操作，请忽略这封邮件。
------------------------------------------------------
帐号激活说明：
为避免垃圾邮件或您的Email地址被滥用，我们需要对您的email有效性进行验证，
您只需点击下面的链接即可激活您的帐号，并享有真正会员权限：
{$sys_config['site_url']}/index.php?mod=member&code=verify&uid={$data['uid']}&key={$data['key']}&from=reg

(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)
感谢您的访问，祝您使用愉快！

此致，
{$sys_config['site_name']} 管理团队.
";

	$send_result = send_mail(
		$email,
		" [{$sys_config['site_name']}]Email地址验证",
		$email_message,
		$sys_config['site_name'],
		$sys_config['site_admin_email'],
		array(),
		3,
		false);

	return $send_result;
}

?>