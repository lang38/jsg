<?php
/**
 * 移动客户端公用模块
 *
 * @author 		~ZZ~<505171269@qq.com>
 * @version $Id: master.mod.php 1342383717 2014 5475 foxis@qq.com $
 */

if(!defined('IN_JISHIGOU')) {
    exit('Access Denied');
}
define("OUT_CHARSET", "UTF-8");
class MasterObject
{
	
	var $Config=array();

	var $Get,$Post,$Files,$Request,$Cookie,$Session;

	
	var $DatabaseHandler;
	
	var $MemberHandler;

	
	var $Title='';

	var $MetaKeywords='';

	var $MetaDescription='';

	
	var $Position='';

	
	var $Module='index';

	
	var $Code='';

	var $hookall_temp = '';

	function MasterObject(&$config)
	{
		require_once ROOT_PATH . 'mobile/include/func/mobile.func.php';
				$config['client_type'] = '';

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

				if (empty($user_agent)) {
			exit('Access Denied');
		}

				$pc_browser = false;
		if (preg_match("/android/i", $user_agent)) {
			$config['client_type'] = "android";
		} else if (preg_match("/iphone/i", $user_agent)) {
			$config['client_type'] = "iphone";
		} else {
			$pc_browser = true;
		}

				$config['is_mobile_client'] = false;
		if (isset($_GET['JSG_SESSION']) && isset($_GET['iv']) && isset($_GET['app_key']) && isset($_GET['app_secret']) &&isset($_GET['bt'])) {
			$config['is_mobile_client'] = true;
			define("IS_MOBILE_CLIENT", true);
		} else {
						if (DEBUG !== true && $pc_browser) {
															}
		}

				define("CLIENT_TYPE", $config['client_type']);

		        $config['sys_version'] = sys_version();
        $config['sys_published'] = SYS_PUBLISHED;

                if(!$config['mobile_url']) {
       		$config['mobile_url'] = $config['site_url'] . "/mobile";
        }

		        if(!$config['topic_length']) {
            $config['topic_length'] = 140;
        }

        		$this->Config = $config;

				$this->Config = array_merge($this->Config, Mobile::config());

				define("CHARSET", $this->Config['charset']);

		Obj::register('config',$this->Config);


				$this->Get     = &$_GET;
		$this->Post    = &$_POST;





		$this->Module = trim($this->Post['mod'] ? $this->Post['mod'] : $this->Get['mod']);
		$this->Code   = trim($this->Post['code'] ? $this->Post['code'] : $this->Get['code']);

				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');

				$uid = 0;
		$password = '';
		$authcode = '';

				$implicit_pass = true;
		if (!empty($this->Get['JSG_SESSION']) && $config['is_mobile_client']) {
						$authcode = $this->Get['JSG_SESSION'];
			$authcode = rawurldecode($authcode);
			$implicit_pass = false;
		} else {
			$authcode = jsg_getcookie('auth');
		}

		if (!empty($authcode)) {
			list($password,$uid)=explode("\t",authcode($authcode,'DECODE'));
		}
        if($this->Get['openid'] && $this->Config['wechat_enable'] && !$uid && !$password){
            list($uid,$password)=  jlogic('wechat')->jsg_get_wechat_openid($this->Get['openid']);
        }

		$this->MemberHandler = jclass('member');
		$MemberFields = $this->MemberHandler->FetchMember($uid,$password);
		if ($this->MemberHandler->HasPermission($this->Module,$this->Code) == false) {
									Mobile::show_message(411);
			exit;
		}

		
		if(!in_array($this->Module, array('member', 'login', 'wechat', 'more', ))) {
			$visit_rets = $this->MemberHandler->visit();
			if($visit_rets['error']) {
				if(true === (Mobile::is_login())) {
					Mobile::show_message(411);
				}
				exit;
			}
		}

				$this->Title = $this->MemberHandler->CurrentAction['name'];
		Obj::register("MemberHandler", $this->MemberHandler);

				$rets = jsg_member_login_extract();
		if($rets) {
			if(MEMBER_ID < 1) {
				$func = $rets['login_direct'];
			} else {
				$func = $rets['logout_direct'];
			}
			if($func && function_exists($func)) {
				$ret = $func();
			}
		}

		if (MEMBER_ID > 0 && false == jsg_getcookie('auth')) {
			jsg_member_login_set_status($MemberFields);
		}

				if ($this->Config['extcredits_enable']) {
			if(MEMBER_ID>0 && jsg_getcookie('login_credits')+3600<time()) {
				update_credits_by_action('login',MEMBER_ID);
				jsg_setcookie('login_credits',time(),3600);
			}
		}


	}
}
?>