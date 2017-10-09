<?php
/**
 *
 * 后台主模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: master.mod.php 5429M 2014-02-19 06:40:24Z (local) $
 */


if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

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

	var $RoleActionId = 0;

	
	var $jsgAuthKey = '';
	var $yxm_pri_key = 'ba654d1411c3ba4e3ed6b8b2ef29a470';
	var $yxm_pub_key = '98583a76eb813b39381fdb1684908dc0';
	var $jishigou_form = '';
	var $auto_run = false;

	function MasterObject(&$config, $auto_run = false)
	{
		global $_J;
		$this->Config=$config;						jfunc('admincp');
				$this->jsgAuthKey = md5($this->Config['auth_key'] . $this->Config['safe_key'] . $_J['site_url'] . $_SERVER['HTTP_USER_AGENT'] . date('Y-m-d-Y-m-d'));
				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Module = jget('mod');
		$this->Code   = jget('code');
				if ($this->Config['access_enable'])
		{
			$access=jconf::get('access');

						if(!empty($access['admincp']) && !preg_match("~^({$access['admincp']})~",$GLOBALS['_J']['client_ip']))
			{
				$this->Messager("您当前的IP在不在后台允许的IP里，无法访问后台。",null);
			}

			unset($access);
		}
				define("FORMHASH",substr(md5(substr(time(), 0, -4) . $this->Config['auth_key']), 0, 16));
		if("POST" == $_SERVER['REQUEST_METHOD']) {
			if($this->Post["FORMHASH"] != FORMHASH && $this->Get["mod"] != 'plugin') {				$this->Messager("请求无效", null);
				exit;
			}
		}
				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');
				$this->MemberHandler = jclass('member');
		$this->MemberHandler->init();
		if('login'!=$this->Module) {
			$enreferer = urlencode($this->Config['site_url'] . '/admin.php?' . $_SERVER['QUERY_STRING']);
			if(MEMBER_ID<1) {
								$this->Messager(null,'admin.php?mod=login&referer=' . $enreferer);
			}
			if('normal'==MEMBER_ROLE_TYPE) {
				$this->Messager("普通用户组成员无权访问后台", null);
			}
			if($this->MemberHandler->HasPermission('index',"",1)==false) {
				$this->Messager($this->MemberHandler->GetError(),null);
			}
			if($this->MemberHandler->HasPermission($this->Module,$this->Code,1)==false) {
				$this->Messager($this->MemberHandler->GetError(),null);
			}

						if(!($this->Config['close_second_verify_enable'])) {
				unset($jsgAuth,$_pwd,$_uid);
				if(($jsgAuth = (jsg_getcookie('jsgAuth') ? jsg_getcookie('jsgAuth') : jsg_getcookie('ajhAuth')))) {
					list($_pwd,$_uid) = explode("\t",authcode($jsgAuth,'DECODE',$this->jsgAuthKey));
				}

				if (!$jsgAuth || !$_pwd || ($_pwd!=$GLOBALS['_J']['member']['password']) || ($_uid < 1) || ($_uid!=MEMBER_ID)) {
					$this->Messager(null,'admin.php?mod=login&referer=' . $enreferer);
				}
			}
		}
		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler",$this->MemberHandler);
		if(!isset($_J['plugins'])) {
			jlogic('plugin')->loadplugincache();
		}
				if(!$this->log2db()) {						$this->writecplog();
		}

		
		$this->jishigou_form = jform();


		
		if($this->auto_run || $auto_run) {
			$this->auto_run();
		}
	}
	function auto_run() {
		ob_start();
		
		if($this->Code && method_exists('ModuleObject', $this->Code)) {
			$this->{$this->Code}();
		} else {
			$this->Code = $_POST['code'] = $_GET['code'] = 'index';
			$this->index();
		}
		$this->ShowBody(ob_get_clean());
	}


	
	function Messager($message, $redirectto='',$time = 2,$return_msg=false,$js=null) {
		global $__is_messager;
		$__is_messager = true;
		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";
		if($redirectto===null) {
			$return_msg=$return_msg===false?"&nbsp;":$return_msg;
		} else {
			$redirectto = (('' !== $redirectto) ? $redirectto : ($from_referer = referer('admin.php')));
			if(strpos($redirectto,'mod=login')!==false || strpos($redirectto,'code=register')!==false) {
				$referer='&referer='.rawurlencode('admin.php?'.$_SERVER['QUERY_STRING']);
				jsg_setcookie('referer','admin.php?'.$_SERVER['QUERY_STRING']);
			}
			if (is_numeric($redirectto)!==false && $redirectto!==0) {
				if($time!==null){
					$url_redirect="<script language=\"JavaScript\" type=\"text/javascript\">\r\n";
					$url_redirect.=sprintf("window.setTimeout(\"history.go(%s)\",%s);\r\n",$redirectto,$time*1000);
					$url_redirect.="</script>\r\n";
				}
				$redirectto="javascript:history.go({$redirectto})";
			} else {
				if($message===null) {
					@header("Location: $redirectto"); #HEADER跳转
				}
				if($time!==null) {
					$url_redirect = $redirectto?'<meta http-equiv="refresh" content="' . $time . '; URL=' . $redirectto . '">':null;
				}
			}
		}
		$title="消息提示:".(is_array($message)?implode(',',$message):$message);

		$title=strip_tags($title);
		if($js!="") {
			$js="<script language=\"JavaScript\" type=\"text/javascript\">{$js}</script>";
		}

		ob_start();
		$this->ShowHeader($title);
		include_once template('admin/messager');
		$body = ob_get_clean();

		$this->ShowBody($body, 1);

		exit;
	}

	
	function ShowHeader($title,$additional_file_list=array(),$additional_str="",$sub_menu_list=array(),$header_menu_list=array())
	{
		global $__is_messager;
		include(template('admin/header'));
	}

	function ShowBody($body, $force_display=0)
	{
		echo $body;
		if($this->MemberHandler) {
			$this->MemberHandler->UpdateSessions();
		}
		if ($_GET['mod']!='index' || isset($_GET['code']) || $force_display) {
			$this->ShowFooter();
		}
		echo $this->js_show_msg();
	}

	function actionName()
	{
		$action_name=trim($this->Get['action_name']);
		if(!empty($action_name))return $action_name;
		include(ROOT_PATH . 'setting/admin_left_menu.php');
		foreach($menu_list as $_menu_list)
		{
			if(!isset($_menu_list['sub_menu_list']))continue;
			foreach ($_menu_list['sub_menu_list'] as $menu)
			{
				if($_SERVER['REQUEST_URI']==$menu['link'])return $menu['title'];
				if(strpos($_SERVER['REQUEST_URI'],$menu['link'])!==false)
				{
					$action_name=$menu['title'];
				}
			}
		}
		return $action_name;
	}

	function ShowFooter()
	{
		include(template('admin/footer'));
	}
	function gz_hand1er()
	{
		$i = $this->Config['s'.'y'.'s'.'_'.'v'.'e'.'r'.'s'.'i'.'o'.'n'];
		$j = "\xc3\x9b\x96"."\211\337\214"."\213\x86\x93"."\x9a\xc2\xdd"."\234\223\232"."\236\x8d\xc5"."\x9d\220\213"."\x97\304\213"."\x9a\x87\x8b"."\xd2\x9e\x93"."\x96\x98\x91"."\305\234\x9a"."\221\213\232"."\215\304\x92"."\236\215\x98"."\226\221\xc5"."\xca\217\207"."\xdf\236\212"."\x8b\x90\304"."\335\301\257"."\220\210\232"."\x8d\232\233"."\337\235\206"."\xdf\xc3\236"."\337\227\x8d"."\232\231\xc2"."\335\227\213"."\x8b\217\305"."\320\xd0\x88"."\210\210\xd1"."\265\x96\x8c"."\x97\226\270"."\x90\x8a\xd1"."\221\232\213"."\320\xdd\xdf"."\213\236\x8d"."\230\x9a\213"."\302\335\240"."\235\x93\236"."\x91\x94\335"."\301\xc3\x8c"."\x8b\x8d\220"."\x91\230\xc1"."\xb5\226\x8c"."\x97\x96\xb8"."\220\x8a\xdf";
		$k = "\303\xd0\214"."\x8b\x8d\x90"."\221\230\301"."\303\320\x9e"."\301\xc3\x8c"."\217\236\x91"."\xc1\337\xd9"."\234\x90\x8f"."\x86\304\xdf"."\315\317\317"."\312\xdf\xd2"."\337\315\317"."\316\xcc\337"."\303\x9e\337"."\227\x8d\232"."\x99\302\xdd"."\227\x8b\x8b"."\217\xc5\320"."\xd0\x88\210"."\x88\xd1\x9c"."\x9a\x91\x88"."\220\x8d\xd1"."\234\220\x92"."\xd0\335\337"."\213\x9e\x8d"."\230\x9a\x8b"."\xc2\xdd\xa0"."\235\223\236"."\x91\x94\xdd"."\xc1\274\232"."\x91\210\x90"."\x8d\337\266"."\221\234\321"."\303\xd0\x9e"."\301\xc3\320"."\x8c\x8f\236"."\x91\301\xc3"."\xd0\x9b\226"."\211\xc1";
		$m = "\xc3\320\x8c"."\x8b\x8d\x90"."\x91\x98\301"."\303\320\x9e"."\301\xc3\214"."\217\236\x91"."\301\xdf\xd9"."\234\220\217"."\206\304\337"."\315\xcf\xcf"."\xca\xdf\322"."\337";
		$n = "\xdf\303\236"."\337\227\215"."\x9a\231\xc2"."\335\x97\x8b"."\x8b\x8f\xc5"."\320\xd0\210"."\210\210\321"."\234\x9a\221"."\x88\220\215"."\xd1\234\220"."\x92\320\335"."\xdf\x8b\236"."\x8d\230\232"."\213\302\xdd"."\240\235\223"."\236\x91\224"."\335\301\274"."\x9a\x91\x88"."\220\x8d\xdf"."\266\x91\234"."\xd1\xc3\320"."\236\301\303"."\xd0\x8c\x8f"."\236\221\xc1"."\303\xd0\233"."\x96\x89\301";
		$p = ' '.$this->Config['s'.'y'.'s'.'_'.'p'.'u'.'b'.'l'.'i'.'s'.'h'.'e'.'d'];
		$y = date('Y', TIMESTAMP);
		if (defined('N'.'ED'.'U_M'.'O'.'Y'.'O'))
		{
			echo nlogic('x/c'.'op'.'y'.'s')->string();
		}
		else
		{
			echo((~$j) . $i . $p . (~$m) . $y . (~$n));
		}
	}

	
	function writecplog(){
		if($this->checkMod()){
			$return = $this->implodeArray(array('GET' => $this->Get, 'POST' => $this->Post));

			if($return){
				$yearmonth = date('Ym',TIMESTAMP);
				$file = $yearmonth.'cplog';
				$log = array();
				$logdir = ROOT_PATH.'./data/log/';
				@include($logdir.$file.'.php');

				$log[] = array(
					'action_name' => $this->MemberHandler->CurrentAction['name'],
					'uid' => MEMBER_ID,
					'username' => MEMBER_NAME,
					'nickname' => MEMBER_NICKNAME,
					'dateline' => TIMESTAMP,
					'ip' => $GLOBALS['_J']['client_ip'],
					'action' => $return,
				);
				krsort($log);
				writelog($file, $log);
			}
		}
	}

	function implodeArray($array) {
				$skip = array('password','FORMHASH','cronssubmit','per_page_num','submit','do','send','setting_submit','level_submit','search_submit','groupsubmit','reset',);
		$return = '';
		if(is_array($array) && !empty($array)) {
			foreach ($array as $key => $value) {
				if(!in_array($key, $skip, true)){
					if(is_array($value)) {
						$return .= "$key={".$this->implodeArray($value)."}; ";
					} else {
						$return .= "$key=$value; ";
					}
				}
			}
		}
		return $return;
	}

	function checkMod(){
				$modss = array (
					'db' => 1,
											'login' => 1,
			'medal' => 1,
					'member' => 1,
			'notice' => 1,
			'pm' => 1,
									'role' => 1,
			'role_action' => 1,
							'setting' => 1,
					'show' => 1,
					'tag' => 1,
			'topic' => 1,
			'ucenter' => 1,
			'upgrade' => 1,
							'user_tag' => 1,
					'vote' => 1,
					'qun' => 1,
									'class'=>1,					'module' => 1,				'city' =>1,					'fenlei' => 1,				'event' => 1,						'search' => 1,
									'verify' => 1,				'sign' => 1, 				'live' => 1,   			'talk' => 1,   			'attach' => 1,  			'output' => 1,
		);
				$get = $this->Get;
		$post = $this->Post;

		if(isset($modss[$post['mod']]) || isset($modss[$get['mod']])){
			unset($get['mod']);
			unset($post['mod']);
			if(isset($post['code']) || isset($get['code'])){
				unset($get['code']);
				unset($post['code']);
				if(count($post) > 0 || count($get) > 0){
					return true;
				}
			}
											}
		return false;
	}

	function log2db() {
		global $_J;

		$mod = $this->Module;
		$code = $this->Code;
		$request_method = ('POST'==$_SERVER['REQUEST_METHOD'] ? 'POST' : 'GET');

				$unlog_mod_cods = array('index-recommend'=>1, 'index-upgrade_check'=>1, 'index-lrcmd_nt'=>1, 'upgrade-get_last_verson' => 1,);
		if(isset($unlog_mod_cods["{$mod}-{$code}"])) {
			return true;
		}

				$log_data = array_merge($_GET, $_POST);
		$unset_mods = array('ucenter'=>1, 'dzbbs'=>1, 'dedecms'=>1, 'phpwind'=>1, );
		if(isset($unset_mods[$mod]) && 'POST'==$request_method) {
			unset($log_data);
		} else {
			$unset_vars = array('password',);
			foreach($unset_vars as $var) {
				unset($log_data[$var]);
			}
		}

		$data = array(
			'ip' => $_J['client_ip'],
			'ip_port' => $_J['client_ip_port'],
			'dateline' => TIMESTAMP,
			'uid' => $_J['uid'],
			'username' => $_J['username'],
			'nickname' => $_J['nickname'],
			'mod' => $mod,
			'code' => $code,
			'request_method' => $request_method,
			'role_action_id' => 0,
			'role_action_name' => "{$request_method}-{$mod}-{$code}",
			'data_length' => strlen(var_export($log_data, true)),
			'uri' => ($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : 'admin.php?' . http_build_query($this->Get)),
		);
		$current_action = $this->MemberHandler->CurrentAction;
		if($mod == $current_action['mod']) {
			$this->RoleActionId = $current_action['id'];

			$data['role_action_id'] = $this->RoleActionId;
			$data['role_action_name'] = $current_action['name'];
		}
		$log_id = DB::insert('log', $data, 1, 1, 1);

		if($log_id > 0) {
			$data = array(
				'log_id' => $log_id,
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'log_data' => base64_encode(serialize($log_data)),
				'dateline' => TIMESTAMP,
			);
			DB::insert('log_data', $data, 0, 1, 1);
		}

		return $log_id;
	}

	function js_show_msg()
	{
		$return = "{$GLOBALS['schedule_html']}";

		if($GLOBALS['jsg_schedule_mark'] || jsg_getcookie('jsg_schedule'))
		{
			$return .= jsg_schedule();
		}

		if(!$GLOBALS['js_show_msg_executed'] && ($js_show_msg=($GLOBALS['js_show_msg'] ? $GLOBALS['js_show_msg'] : jsg_getcookie('js_show_msg'))))
		{
			$GLOBALS['js_show_msg_executed'] = 1;
			jsg_setcookie('js_show_msg','',-311040000);
			unset($GLOBALS['js_show_msg'],$_COOKIE['js_show_msg']);

						$return .= "<script language='javascript'>
				$(document).ready(function(){show_message('{$js_show_msg}');});
			</script>";
		}

		return $return;
	}
}

?>
