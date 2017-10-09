<?php
/**
 *
 * 邮件发送函数
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mail.func.php 5323 2013-12-26 01:38:03Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}


if(!defined('NEW_LINE')) {
	substr(PHP_OS,0,3)=="WIN"?define("NEW_LINE","\r\n"):define("NEW_LINE","\n");
}


function _send_mail($to,$subject,$message,$nickname='',$email='',$attachments=array(),$priority=3,$html=true,$smtp_config=array())
{
	$sys_config = jconf::get();

	if(!($nickname && $email)) {
		$nickname = $sys_config['site_name'];
		$email = $sys_config['site_admin_email'];
	}

	$smtp_config = ($smtp_config ? $smtp_config : (jconf::get('smtp')));	
	if($smtp_config['enable'] && is_array($smtp_config['smtp']) && count($smtp_config['smtp'])) {
				$k = array_rand($smtp_config['smtp']);
		$smtp = $smtp_config['smtp'][$k];
		if(is_array($smtp) && count($smtp)) {
			if($nickname && $email) {
				$smtp['email_from'] = "{$nickname} <{$email}>";
			}
			return _send_mail_by_smtp($to,$subject,$message,$smtp,$html);
		} else {
			jlog('SMTP', '$smtp is empty', 0);
		}
		
	} else {
		$charset = $sys_config['charset'];
		$jishigou_mail = jclass('jishigou/mail');

		if(is_array($attachments) and count($attachments)>=1) {
			$boundary="----_NextPart_".md5(uniqid(time()))."_000";
			$jishigou_mail->SetHeader('Content-Type: multipart/mixed;boundary="'.$boundary.'"');
			$body="--".$boundary."".NEW_LINE."";
			$body.="Content-Type: text/".($html ? 'html' : 'plain')."; charset=\"".$sys_config['charset']."\"".NEW_LINE."";
			$body.="Content-Transfer-Encoding: base64".NEW_LINE."".NEW_LINE."";
			$body.=chunk_split(base64_encode($message))."".NEW_LINE."";

			foreach($attachments as $attachment) {
				$body.="--".$boundary."".NEW_LINE."";
				$body.="Content-Type: application/octet-stream;".NEW_LINE."\t\tname=\"{$attachment['name']}\"".NEW_LINE."";
				$body.="Content-Transfer-Encoding: base64".NEW_LINE."";
				$body.="Content-Disposition: attachment;".NEW_LINE."\t\tFileName=\"{$attachment['name']}\"".NEW_LINE."".NEW_LINE."";
				$body.=chunk_split(base64_encode(file_get_contents($attachment['path'])))."".NEW_LINE."";;
			}
						$message=$body;
		} else {
			$jishigou_mail->SetHeader('Content-Type: text/'.($html ? 'html' : 'plain').'; charset=' . $sys_config['charset']);
			$jishigou_mail->SetHeader('Content-Transfer-Encoding: base64');
			$message = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message)))))));
		}
		$subject = '=?'.$charset.'?B?'.base64_encode(str_replace("\r", '', str_replace("\n", '', $subject))).'?=';
		$nickname = '=?'.$charset.'?B?'.base64_encode($nickname)."?=";

		$jishigou_mail->SetSenderName($nickname);
		$jishigou_mail->SetSenderMail($email);
		$jishigou_mail->SetSendMailFrom($email);
		$jishigou_mail->SetUseHtml($html);
		$jishigou_mail->SetHeader("Return-Path: {$email}");
		$jishigou_mail->SetHeader("MIME-Version: 1.0");
		$jishigou_mail->SetHeader("X-Priority: $priority");
		$jishigou_mail->SetHeader("Sender: {$email}");
		$jishigou_mail->SetRecipient($to);
		$jishigou_mail->SetSubject($subject);
		$jishigou_mail->SetMessage($message);

		return $jishigou_mail->doSend();
	}
}

function _send_mail_by_smtp($email_to,$email_subject,$email_message,$smtp_config=array(),$html=true) {

	$sys_config = jconf::get();
	if(empty($smtp_config)) {
		$smtp_conf = jconf::get('smtp');
		$k = array_rand($smtp_conf['smtp']);
		$smtp_config = $smtp_conf['smtp'][$k];
	}
	if(empty($smtp_config)) {
		jlog('SMTP', '$smtp_config is empty', 0);
	}

	$mail['from'] = $smtp_config['mail'];
	$mail['server'] = ($smtp_config['ssl'] ? 'ssl:/'.'/' : '') . $smtp_config['host'];
	$mail['port'] = $smtp_config['port'];
	$mail['auth'] = (bool) ($smtp_config['username'] && $smtp_config['password']);
	$mail['auth_username'] = $smtp_config['username'];
	$mail['auth_password'] = $smtp_config['password'];

	$log = 'jlog';
	$charset = $sys_config['charset'];
	$bbname = $sys_config['site_name'];
	$adminemail = $sys_config['site_admin_email'];
	$maildelimiter = NEW_LINE;
	$mailusername = 1;

	$email_subject = '=?'.$charset.'?B?'.base64_encode(str_replace("\r", '', str_replace("\n", '', $email_subject))).'?=';
	$email_message = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $email_message)))))));

	$email_from = $smtp_config['email_from'] ? $smtp_config['email_from'] : $smtp_config['mail'];
		$email_from = ($email_from == '' ? '=?'.$charset.'?B?'.base64_encode($bbname)."?= <$adminemail>" : (preg_match('/^(.+?) \<(.+?)\>$/',$email_from, $from) ? '=?'.$charset.'?B?'.base64_encode($from[1])."?= <$from[2]>" : $email_from));

	foreach(explode(',', $email_to) as $touser) {
		$tousers[] = preg_match('/^(.+?) \<(.+?)\>$/',$touser, $to) ? ($mailusername ? '=?'.$charset.'?B?'.base64_encode($to[1])."?= <$to[2]>" : $to[2]) : $touser;
	}
	$email_to = implode(',', $tousers);

	$headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: JishiGou ".SYS_VERSION."{$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/".($html ? 'html' : 'plain')."; charset=$charset{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";
	$mail['port'] = $mail['port'] ? $mail['port'] : 25;
	if(!$fp = jfsockopen($mail['server'], $mail['port'], $errno, $errstr, 3)) {
		$log('SMTP', "($mail[server]:$mail[port]) CONNECT - Unable to connect to the SMTP server", 0);
		return false;
	}
	stream_set_blocking($fp, true);

	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != '220') {
		$log('SMTP', "$mail[server]:$mail[port] CONNECT - $lastmessage", 0);
		return false;
	}

	fputs($fp, ($mail['auth'] ? 'EHLO' : 'HELO')." JishiGou\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
		$log('SMTP', "($mail[server]:$mail[port]) HELO/EHLO - $lastmessage", 0);
		return false;
	}

	while(1) {
		if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
			break;
		}
		$lastmessage = fgets($fp, 512);
	}

	if($mail['auth']) {
		fputs($fp, "AUTH LOGIN\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			$log('SMTP', "($mail[server]:$mail[port]) AUTH LOGIN - $lastmessage", 0);
			return false;
		}

		fputs($fp, base64_encode($mail['auth_username'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 334) {
			$log('SMTP', "($mail[server]:$mail[port]) USERNAME - $lastmessage", 0);
			return false;
		}

		fputs($fp, base64_encode($mail['auth_password'])."\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 235) {
			$log('SMTP', "($mail[server]:$mail[port]) PASSWORD - $lastmessage", 0);
			return false;
		}

		$email_from = $mail['from'];
	}

	fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			$log('SMTP', "($mail[server]:$mail[port]) MAIL FROM - $lastmessage", 0);
			return false;
		}
	}

	$email_tos = array();
	foreach(explode(',', $email_to) as $touser) {
		$touser = trim($touser);
		if($touser) {
			fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250) {
				fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
				$lastmessage = fgets($fp, 512);
				$log('SMTP', "($mail[server]:$mail[port]) RCPT TO - $lastmessage", 0);
				return false;
			}
		}
	}

	fputs($fp, "DATA\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 354) {
		$log('SMTP', "($mail[server]:$mail[port]) DATA - $lastmessage", 0);
		return false;
	}

	$headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">{$maildelimiter}";

	fputs($fp, "Date: ".date('r')."\r\n");
	fputs($fp, "To: ".$email_to."\r\n");
	fputs($fp, "Subject: ".$email_subject."\r\n");
	fputs($fp, $headers."\r\n");
	fputs($fp, "\r\n\r\n");
	fputs($fp, "$email_message\r\n.\r\n");
	$lastmessage = fgets($fp, 512);
	if(substr($lastmessage, 0, 3) != 250) {
		$log('SMTP', "($mail[server]:$mail[port]) END - $lastmessage", 0);
		return false;
	}

	fputs($fp, "QUIT\r\n");

	return true;
}

/*使用
 $attachment_list=array(array('name'=>basename(__FILE__),'path'=>__FILE__));//这里是附件
 $result=send_mail('foxis@qq.com',"主题测试","内容测dddd试<BR><BR><BR><BR>","狐狸",'foxis@qq.com',$attachment_list);
 if($result==true)
 {
 	echo "发送成功";
 }
*/
