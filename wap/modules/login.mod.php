<?php
/**
 * 文件名：login.mod.php
 * @version $Id: login.mod.php 5267 2013-12-16 05:11:28Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户登录
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{


	
	var $Code = false;

	
	var $Username = '';

	
	var $Password = '';

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->Username = isset($this->Post['username'])?trim($this->Post['username']):"";
		$this->Password = isset($this->Post['password'])?trim($this->Post['password']):"";

		if(MEMBER_ID > 0) {
			$this->IsAdmin = $this->MemberHandler->HasPermission('member','admin');
		}
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'dologin':
				$this->DoLogin();
				break;
			case 'logout':
				$this->LogOut();
				break;
			default:
				$this->login();
				break;
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
	
	function login()
	{
		if(MEMBER_ID != 0 AND false == $this->IsAdmin) {
			$this->Messager("您已经使用用户名 ". MEMBER_NICKNAME ." 登录系统，无需再次登录！",null);
		}

		$this->Title = "用户登录";
		$referer = referer('index.php');
		$enreferer = urlencode($referer);
		if(jsg_getcookie("referer")=="") {
			jsg_setcookie("referer", $referer);
		}

		$action="index.php?mod=login&amp;code=dologin";


		include(template("login_global"));

	}


	
	function DoLogin()
	{
        
        $this->Username = wap_iconv($this->Username,'utf-8',$this->Config['charset'], 1);
		$this->Password = wap_iconv($this->Password,'utf-8',$this->Config['charset'], 1);


        if($this->Username=="" || $this->Password=="")
		{
			$this->Messager("无法登录,用户名或密码不能为空",'index.php?mod=login');
		}

		
		$username = $this->Username;
		$password = $this->Password;


	
		if($this->Config['reg_email_verify'] == '1')
		{
			

			
		 	$member_info = DB::fetch_first("select `uid`,`username` from ".DB::table('members')." where `username`='$username' limit 0,1");

		    if($member_info)
		    {
		     	$member_validate = DB::fetch_first("select `uid`,`status` from ".DB::table('member_validate')." where `uid`='{$member_info['uid']}' ");
		    }

		    if($member_validate)
		    {
		    	if($member_validate['status'] != '1')
		    	{
		    		$this->Messager("必须完成邮件激活，才能正常访问！进入注册时填写的邮箱激活即可。",'index.php?mod=login');
		    				    	}
		    }
		}

		
		$referer = jget('referer');
		if(!$referer) {
			$referer = jsg_getcookie('referer');
		}

		$rets = jsg_member_login($username, $password);
        if($rets['uid'] < 1)
        {
        	$this->Messager(wap_iconv($rets['error']), null);
        }
        $uid = $rets['uid'];



		if($this->Config['extcredits_enable'] && $rets['uid'] > 0)
		{
			
			update_credits_by_action('login',$rets['uid']);
		}

		
		$redirecto=($referer?$referer:referer('index.php'));
		if(strpos($redirecto, 'login') !== false) {
            $redirecto = "index.php?mod=topic&code=myhome" ;
        }

				if($this->Post['loginType'] == 'share')
		{
			$redirecto = $this->Post['return_url'];
		}

		
		$this->Messager('登录成功', $redirecto, 0);
	}


	
	function LogOut()
	{
		$rets = jsg_member_logout();


		$this->Messager('退出成功','index.php?mod=plaza',0);
	}

}

?>