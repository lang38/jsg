<?php
/**
 *
 * 取回密码模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: get_password.mod.php 5467 2014-01-18 06:14:04Z wuliyong $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		if (MEMBER_ID>0) {
			}
		if($GLOBALS['_J']['config']['ldap_enable']){
			$this->Messager("网站启用AD域帐号登录，不支持用户对密码进行任何操作。",null);
		}

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'do_send':
				$this->DoSend();
				break;
			case 'do_reset':
				$this->DoReset();
				break;

			case 'sms':
				$this->Sms();
				break;
			case 'sms_send':
				$this->SmsSend();
				break;
			case 'sms_reset':
				$this->SmsReset();
				break;

			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
		$act_list = array();
		$act_list['base'] = '找回密码';
		$act_list['reset'] = '重设密码';

		$act = isset($act_list[$this->Code]) ? $this->Code : 'base';
		$act_name = $act_list[$act];

		if('reset' == $act) {
			extract($this->_resetCheck());
		}


		$this->Title = $act_list[$act];
		include(template('get_password_main'));
	}

	function DoSend() {
		if ($this->Config['seccode_enable']==1 && $this->Config['seccode_password']) {
			if (!ckseccode(@$_POST['seccode'])) {
				$this->Messager("验证码输入错误",-1);
			}
		}elseif ($this->Config['seccode_enable']>1 && $this->Config['seccode_password'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['add_YinXiangMa_challenge'],@$_POST['add_YXM_level'][0],@$_POST['add_YXM_input_result']);
			if($YinXiangMa_response != "true"){
				$this->Messager("验证码输入错误",-1);
			}
		}
		$to = trim($this->Post['to']);
		if(!$to) {
			$this->Messager('内容不能为空', -1);
		}
		if(false === strpos($to, '@')) {
			$member = jsg_get_member($to, 'nickname', 0);
			if(!$member) {
				$this->Messager('用户不存在，请返回重试或者与管理员取得联系。', -1);
			}
			$to = $member['email'];
		}

		$sql="select M.uid,M.username,M.nickname,M.email,MF.authstr
		FROM
			".TABLE_PREFIX.'members'." M LEFT JOIN ".TABLE_PREFIX.'memberfields'." MF ON(M.uid=MF.uid)
		WHERE
			M.email='{$to}'";
		$query = $this->DatabaseHandler->Query($sql);
		$member=$query->GetRow();
		if ($member==false) {
			$this->Messager("用户已经不存在", -1);
		}
		$timestamp=time();
		if ($member['authstr']) {
			list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);
			$inteval = 1800;			if ($dateline+$inteval>$timestamp) {
				$this->Messager("邮件刚刚已经发送了，请稍候。如果长时间都没有收到邮件，请半小时后再发送一次或者与管理员取得联系。",-1,null);
			}
		}

		$idstring = random(32);
		$member['authstr'] = "{$timestamp}\t1\t{$idstring}";
		$member['auth_try_times'] = 0;
		$result = jtable('memberfields')->update($member, array('uid'=>$member['uid']));
		if (!$result) {
			jtable('memberfields')->insert($member, 0, 1);
		}
		$onlineip= $GLOBALS['_J']['client_ip'];
				$email_message="您好：
您收到这封邮件，是因为Email地址在{$this->Config['site_name']}上被登记为用户邮箱，
且用户请求使用 Email 密码重置功能所致。
----------------------------------------------------------------------
重要：如果您没有提交密码重置的请求或不是{$this->Config['site_name']}的注册用户，请立即忽略
并删除这封邮件。
----------------------------------------------------------------------
如果是您发起了找回密码申请，请在两小时之内，通过点击下面的链接重置您的密码：
{$this->Config['site_url']}/index.php?mod=get_password&code=reset&uid={$member['uid']}&id={$idstring}
(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)

上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录
{$this->Config['site_name']}了。您可以在个人设置中随时修改您的密码。

本请求提交者的 IP 为 $onlineip
此致
{$this->Config['site_name']} 管理团队.
{$this->Config['site_url']}";
			$subject="[{$this->Config['site_name']}] 取回密码说明";
			send_mail($member['email'],$subject,
			$email_message,$this->Config['site_name'],$this->Config['site_admin_email'],
			array(),3,$html=false) ;
		$email_head = $member['email']{0} . $member['email']{1} . $member['email']{2};
		$mail_service=strstr($member['email'], '@');
		$message=array(
		"标题为\"<b>{$subject}</b>\"的邮件已经发送到您<b>\"{$email_head}******\"</b>开头且后缀为<b>\"{$mail_service}\"</b>的信箱中，请在 2小时之内修改您的密码。",
		"邮件发送可能会延迟几分钟，请耐心等待。",
		"部分邮件提供商会将本邮件当成垃圾邮件来处理，您或许可以进垃圾箱找到此邮件。",
		);
		$this->Messager($message,null,null);
	}

	function DoReset()
	{
		if ($this->Config['seccode_enable']==1 && $this->Config['seccode_password']) {
			if (!ckseccode(@$_POST['seccode'])) {
				$this->Messager("验证码输入错误",-1);
			}
		}elseif ($this->Config['seccode_enable']>1 && $this->Config['seccode_password'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['add_YinXiangMa_challenge'],@$_POST['add_YXM_level'][0],@$_POST['add_YXM_input_result']);
			if($YinXiangMa_response != "true"){
				$this->Messager("验证码输入错误",-1);
			}
		}
		$this->_resetCheck();

		if($this->Post['password']!=$this->Post['confirm'] or $this->Post['password']=='') {
			$this->Messager('两次输入的密码不一致,或新密码不能为空。',-1,null);
		}

		$uid = (int) get_param('uid');
        $member_info = jsg_member_info($uid);
        if(!$member_info) {
            $this->Messager("用户已经不存在了",null);
        }


		$sql="UPDATE ".TABLE_PREFIX.'memberfields'." SET `authstr`='', `auth_try_times`='0' WHERE uid='$uid'";
		DB::query($sql);


        jsg_member_edit($member_info['nickname'], '', '', $this->Post['password'], '', '', 1);


		$this->Messager("新密码设置成功，现在为您转入登录界面.",$this->Config['site_url'] . "/index.php?mod=login");
	}

	function _resetCheck() {
		$uid = (int) get_param('uid');
		$id = trim(get_param('id'));
		if (!$id || $uid<1) {
			$this->Messager("请求的链接地址错误",null);
		}

		$sql="select M.uid,M.username,M.nickname,M.email,MF.authstr,MF.auth_try_times
		FROM
			".TABLE_PREFIX.'members'." M LEFT JOIN ".TABLE_PREFIX.'memberfields'." MF ON(M.uid=MF.uid)
		WHERE
			M.uid='$uid'";
		$query = $this->DatabaseHandler->Query($sql);
		$member=$query->GetRow();
		if ($member==false) {
			$this->Messager("用户已经不存在了",null);
		}
		if(empty($member['authstr'])) {
			$this->Messager("重置密码的请求不存在", null);
		}
		$member['auth_try_times'] = (max(0, (int) $member['auth_try_times']) + 1);
		DB::query("UPDATE ".DB::table('memberfields')." SET `auth_try_times`='{$member['auth_try_times']}' WHERE `uid`='{$uid}'");
		if($member['auth_try_times']>=10) {
			$this->Messager('【'.$member['auth_try_times'].'】您尝试的错误次数太多了，请重新发起找回密码的请求', null);
		}
		$timestamp=time();
		list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);
		if(($dateline < ($timestamp - 3600 * 2)) || $operation != 1 || $idstring != $id) {
			$message=array(
				"重置密码的请求不存在或已经过期，无法取回密码。",
				"如您想重新设置密码，请<a href='index.php?mod=get_password'>单击此处</a>。"
			);
			$this->Messager($message,null);
		}
		$member['id'] = $id;

		return $member;
	}

	
	function Sms() {
		if(!sms_init()) {
			$this->Messager('还没有开启手机短信功能', null);
		}
		include template('get_password_sms');
	}
	function SmsSend() {
		if(!sms_init()) {
			$this->Messager('还没有开启手机短信功能', null);
		}
		$act_name = '请输入手机验证码';
		$rets = array();
		$key = jget('key', 'txt');
		$gsms = jget('sms', 'txt');
		if($key && $gsms) {
			$sms = $gsms;
			$act_name = '请重新输入手机验证码';
		} else {
			
			if ($this->Config['seccode_enable']==1 && $this->Config['seccode_password']) {
				if (!ckseccode(@$_POST['seccode'])) {
					$this->Messager("验证码输入错误",-1);
				}
			}elseif ($this->Config['seccode_enable']>1 && $this->Config['seccode_password'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
				$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['add_YinXiangMa_challenge'],@$_POST['add_YXM_level'][0],@$_POST['add_YXM_input_result']);
				if($YinXiangMa_response != "true"){
					$this->Messager("验证码输入错误",-1);
				}
			}

			$sms = jpost('sms', 'txt');
			$rets = sms_send_verify($sms);
		}
		if($rets['error']) {
			$this->Messager($rets['result']);
		} else {
			include template('get_password_sms_send');
		}
	}
	function SmsReset() {
		if(!sms_init()) {
			$this->Messager('还没有开启手机短信功能', null);
		}

		$sms = jpost('sms', 'txt');
		$key = jpost('key', 'txt');

		$rets = sms_check_verify($sms, $key);
		if($rets['error']) {
			$this->Messager($rets['result'] . " 请返回重试，或者<a href='index.php?mod=get_password'>点此重新发起验证</a>",
				"index.php?mod=get_password&code=sms_send&sms=$sms&key=$key");
		} else {
			if(jpost('reset_pwd_submit')) {
				$pwd = jpost('password');
				if(empty($pwd) || $pwd != jpost('confirm') || strlen($pwd) < 6) {
					$this->Messager('两次输入的密码不一致！请设置5位以上的密码！', 'index.php?mod=get_password');
				}
				$info = sms_bind_info($sms);
				$uid = $info['uid'];
				if(empty($info) || $uid < 1) {
					$this->Messager('此手机号未绑定任何帐号', null);
				}
				$member = jsg_member_info($uid);
				if(!$member) {
					$this->Messager("用户ID【{$uid}】已经不存在了", null);
				}

				sms_enter_verify($sms);

				jsg_member_edit($member['nickname'], '', '', $pwd, '', '', 1);

				$msg = "【{$member['uid']}】{$member['nickname']}，您的新密码已重新设置为 {$pwd} ，请注意保管！";
				sms_send($sms, $msg, 0);

				$this->Messager("新密码设置成功，现在为您转入登录界面.",$this->Config['site_url'] . "/index.php?mod=login");
			} else {
				$act_name = '重设您的新密码';
				include template('get_password_sms_reset');
			}
		}
	}

}

?>
