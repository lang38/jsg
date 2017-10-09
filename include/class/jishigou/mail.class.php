<?php
/**
 *
 * 邮件相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mail.class.php 5320 2013-12-25 06:54:19Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}
substr(PHP_OS,0,3)=="WIN"?define("NEW_LINE","\r\n"):define("NEW_LINE","\n");
class jishigou_mail
{
	var $SenderName;       
	var $SenderMail;    
	var $Subject;			
	var $Message;     
	var $Headers;	
	var $Recipient;  
	var $Html;  
	
	function jishigou_mail()
	{
		$this->SenderMail  = '';
		$this->SenderName    = '';
		$this->Html      = true;

		$this->Recipient = '';
		$this->Message   = '';
		$this->Subject   = '';
		$this->Headers   = array();
	}
	
	function SetSmtpHost($smtp)
	{
		Return ini_set("SMTP",$smtp);
	}

	
	function SetSendPort($port)
	{
		Return ini_set("smtp_port",$port);
	}

	
	function SetSendMailFrom($mail)
	{
		Return 	ini_set('sendmail_from',$mail);
	}


	
	function SetSenderName($sender)
	{
		$this->SenderName=$sender;
	}

	
	function SetSenderMail($mail)
	{
		$this->SenderMail=$mail;
	}


	
	function SetRecipient($recipient)
	{
		$this->Recipient = $recipient;
	}

	
	function SetUseHtml($html=true)
	{
		$this->Html = (bool)$html;
	}


	
	function SetSubject($subject)
	{
		$this->Subject = $subject;
	}



	
	function SetMessage($message)
	{
		$this->Message = $message;
	}

	
	function SetHeader($header)
	{
		$this->Headers[] = $header;
	}

	
	function GetHeader($key)
	{
		return (isset($this->Headers[$key]) ? $this->Headers[$key] : false);
	}

	
	function DoSend()
	{
		if(false == $this->Subject || false == $this->Subject) {
			return false;
		}

		$this->SetHeader("From: \"{$this->SenderName}\" <{$this->SenderMail}>");
		$to = (strlen($this->Recipient) ? $this->Recipient : $this->SenderName . ' <' . $this->SenderMail . '>');

		$result= (function_exists('mail') && @mail($to, $this->Subject, $this->Message, implode(NEW_LINE, $this->Headers)));
		$this->Headers="";
		Return $result;
	}

}

?>