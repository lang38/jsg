<?php
/**
 *
 * 模块核心类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: master.mod.php 5543 2014-02-12 08:01:06Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
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
	var $All_company = array();
	var $Channels = array();
	var $Channel_enable;
	var $yxm_html='';
	var $yxm_title='';
	var $__is_public = null;
	var $auto_run = false;

	function MasterObject(&$config, $auto_run = false)
	{
		global $_J;
		$this->Config = $config;				$this->Get     = &$_GET;
		$this->Post    = &$_POST;
		$this->Module  = get_param('mod');
				if(!jsg_getcookie('mobilegotopc') &&
			in_array($this->Module, array('topic', $this->Config['default_module'])) &&
			($_SERVER['HTTP_USER_AGENT'] &&
				preg_match("/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile|iphone|android)/i",
					$_SERVER['HTTP_USER_AGENT']))) {
			if(1 == jget('setcookie')) {
				jsg_setcookie('mobilegotopc', 1, 86400);
			} else {
								$this->Messager(null,'index.php?mod=login&code=goto&gets=' . urlencode(base64_encode(serialize((array) $_GET))));
			}
		}
				$this->DatabaseHandler = & Obj::registry('DatabaseHandler');
				$this->MemberHandler = jclass('member');
		$MemberFields = $this->MemberHandler->init();
		$this->__init_code();

		
		if(!$this->__is_public()) {
			$visit_rets = $this->MemberHandler->visit();
			if($visit_rets['error']) {
				$this->Messager($visit_rets['result'], null);
			}
		}

				$channels = jlogic('channel')->get_pub_channel();
		$this->Channel_enable = $channels['channel_enable'];
		$this->Channels = $channels['channels'];

				if($this->Config['seccode_enable']>1 && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']){
			$this->yxm_html = jlogic('seccode')->GetYXM();
			$this->yxm_title = jlogic('seccode')->TitleYXM();
		}
				if(false == $this->MemberHandler->HasPermission($this->Module,$this->Code) && !$this->__is_public()) {
			$this->Messager($this->MemberHandler->GetError(), null);
		}
		$this->Title=$this->MemberHandler->CurrentAction['name'];		Obj::register("MemberHandler", $this->MemberHandler);

				if($GLOBALS['_J']['config']['jsg_member_login_extract']) {
			$rets = jsg_member_login_extract();
			if($rets) {
				if(MEMBER_ID < 1) {
					$func = $rets['login_direct'];
				} else {
					$func = $rets['logout_direct'];
				}

				if($func && function_exists($func)) {
					$ret = $func();

					if($ret) {
						$this->Messager(null, $ret);
					}
				}
			}
		}
		
				define("FORMHASH",substr(md5(substr(time(), 0, -4).$this->Config['auth_key']),0,16));
		if($_SERVER['REQUEST_METHOD']=="POST") {
			if($this->Post["FORMHASH"]!=FORMHASH) {
							}
		}

				
		if($this->Config['extcredits_enable'])
		{
			
			if(MEMBER_ID>0 && jsg_getcookie('login_credits')+3600<time())
			{
				update_credits_by_action('login',MEMBER_ID);

				jsg_setcookie('login_credits',time(),3600);
			}
		}

		
		if($this->Config['sendmailday'] > 0 && MEMBER_ID>0){
			jtable('mailqueue')->del(MEMBER_ID);
		}

								if ($this->Config['site_domain'] != $_SERVER['HTTP_HOST'] && !get_param('__redirect__') && false === strpos($this->Config['site_domain'], '/')) {
			$redirect_url = $this->Config['site_url'] . '/index.php?__redirect__=1&' . $_SERVER['QUERY_STRING'];
			if (isset($_SERVER['JSG-PHP-RUN-MODE']) && $_SERVER['JSG-PHP-RUN-MODE'] == 'PHP-CLI')
			{

			}
			else
			{
				header('Location: '.$redirect_url);
				exit;
			}
		}

				if($this->Config['company_enable'] && ($config['companytree'] = jconf::get('companytree'))){
			foreach($config['companytree'] as $val){
				$this->All_company[] = array(
					'id'        => $val['id'],
					'name'      => $val['name'],
					'shortname' => cut_str($val['name'],16,'..'),
					'css'       => (($_GET['id'] == $val['id'] && $_GET['mod'] == 'company') ? 'hover ' : '').($val['step'] == '@' ? 'nav' : 'none')
				);
			}
		}

				if(!$_J['config']['acceleration_mode']) {
			if(!isset($_J['plugins'])) {
				jlogic('plugin')->loadplugincache();
			}
			runhooks();
		}

				define("LEFTNAV",jsg_getcookie('leftnav'));
		define("TOPNOTICE",jsg_getcookie('topnotice'));

		
		$this->_initTheme((MEMBER_ID>0?$MemberFields:null));

		
		$this->__init_nav();

		
		if($this->auto_run || $auto_run) {
			$this->auto_run();
		}
	}
	function auto_run() {
		ob_start();
		
		if($this->Code && method_exists('ModuleObject', $this->Code)) {
			$this->{$this->Code}();
		} else {
						$this->index();
		}
		$this->ShowBody(ob_get_clean());
	}

	
	function Messager($message, $redirectto='',$time = -1,$return_msg=false,$js=null)
	{
		global $jishigou_rewrite;

		ob_start();

		if ($time===-1)
		{
			$time=(is_numeric($this->Config['msg_time'])?$this->Config['msg_time']:5);
		}


		$to_title=($redirectto==='' or $redirectto==-1)?"返回上一页":"跳转到指定页面";

		if($redirectto===null)
		{
			$return_msg=$return_msg===false?"&nbsp;":$return_msg;
		}
		else
		{
			$redirectto=($redirectto!=='')?$redirectto:($from_referer=referer());
			if(str_exists($redirectto,'mod=login','code=register','/login','/register'))
			{
				$referer='&referer='.urlencode('index.php?'.$_SERVER['QUERY_STRING']);
				jsg_setcookie('referer','index.php?'.$_SERVER['QUERY_STRING']);
			}
			if (is_numeric($redirectto)!==false and $redirectto!==0)
			{
				if($time!==null){
					$url_redirect="<script language=\"JavaScript\" type=\"text/javascript\">\r\n";
					$url_redirect.=sprintf("window.setTimeout(\"history.go(%s)\",%s);\r\n",$redirectto,$time*1000);
					$url_redirect.="</script>\r\n";
				}
				$redirectto="javascript:history.go({$redirectto})";
			}
			else
			{
				if($jishigou_rewrite && null!==$message)
				{
					$redirectto .= $referer;
					if(!$from_referer && !$referer) {
						$redirectto=$jishigou_rewrite->formatURL($redirectto,true);
					}
									}

				if($message===null)
				{
										@header("Location: $redirectto"); #HEADER跳转
				}
				if($time!==null)
				{
					$url_redirect = ($redirectto?'<meta http-equiv="refresh" content="' . $time . '; URL=' . $redirectto . '">':null);
				}
			}
		}
		$title="消息提示:".(is_array($message)?implode(',',$message):$message);

		$title=strip_tags($title);
		if($js!="") {
			$js="<script language=\"JavaScript\" type=\"text/javascript\">{$js}</script>";
		}
		$additional_str = $url_redirect.$js;


		$this->Title = $title;
		include(template('messager'));
		$body=ob_get_clean();

		$this->ShowBody($body);

		if(true !== DEBUG) {
			exit;
		}
	}

	
	function _initTheme($uid=null)
	{
		$themes = 'themes';

		if(!$this->Config[$themes])
		{
			$this->Config[$themes] = array(
				'theme_id' => $this->Config['theme_id'],
				'theme_bg_image' => $this->Config['theme_bg_image'],
				'theme_bg_color' => $this->Config['theme_bg_color'],
				'theme_text_color' => $this->Config['theme_text_color'],
				'theme_link_color' => $this->Config['theme_link_color'],
				'theme_bg_image_type' => $this->Config['theme_bg_image_type'],
				'theme_bg_repeat' => $this->Config['theme_bg_repeat'],
				'theme_bg_fixed' => $this->Config['theme_bg_fixed'],
			);
		}

		if($uid)
		{
			$this->Config['theme_id'] = $this->Config[$themes]['theme_id'];
			$this->Config['theme_bg_image'] = $this->Config[$themes]['theme_bg_image'];
			$this->Config['theme_bg_color'] = $this->Config[$themes]['theme_bg_color'];
			$this->Config['theme_text_color'] = $this->Config[$themes]['theme_text_color'];
			$this->Config['theme_link_color'] = $this->Config[$themes]['theme_link_color'];
			$this->Config['theme_bg_position'] = $this->Config[$themes]['theme_bg_image_type'];
			$this->Config['theme_bg_repeat'] = $this->Config[$themes]['theme_bg_repeat'];
			$this->Config['theme_bg_fixed'] = $this->Config[$themes]['theme_bg_fixed'];


			$_my = array();
			if (is_array($uid))
			{
				$_my = $uid;
			}
			else
			{
				$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
				if ($uid < 1)
				{
					$uid = MEMBER_ID;
				}

				if($uid==MEMBER_ID)
				{
					$_my = $GLOBALS['_J']['member'];
				}
				else
				{
					if($uid > 0)
					{
						$query = $this->DatabaseHandler->Query("select `uid`,`theme_id`,`theme_bg_image`,`theme_bg_color`,`theme_text_color`,`theme_link_color`,`theme_bg_image_type`,`theme_bg_repeat`,`theme_bg_fixed` from ".TABLE_PREFIX."members where `uid`='".$uid."'");
						$_my = $query->GetRow();
					}
				}
			}

			if ($_my && $_my['theme_id'])
			{
				$this->Config['theme_id'] = $_my['theme_id'];
				$this->Config['theme_bg_image'] = $_my['theme_bg_image'];
				$this->Config['theme_bg_color'] = $_my['theme_bg_color'];
				$this->Config['theme_text_color'] = $_my['theme_text_color'];
				$this->Config['theme_link_color'] = $_my['theme_link_color'];

								$this->Config['theme_bg_image_type'] = $_my['theme_bg_image_type'];
				$this->Config['theme_bg_repeat'] = $_my['theme_bg_repeat'];
				$this->Config['theme_bg_fixed'] = $_my['theme_bg_fixed'];
			}
		}


				if($this->Config['theme_bg_image'] && false===strpos($this->Config['theme_bg_image'],':/'.'/'))
		{
			$this->Config['theme_bg_image'] = ($this->Config['site_url'] . '/' . $this->Config['theme_bg_image']);

			

			$this->Config['theme_bg_repeat'] = ($this->Config['theme_bg_repeat'] ? 'repeat' : 'no-repeat');
			$this->Config['theme_bg_fixed'] = ($this->Config['theme_bg_fixed'] ? 'fixed' : 'scroll');
		}
		$this->Config['theme_bg_image_type'] = ($this->Config['theme_id'] ? $this->Config['theme_bg_image_type'] : "");
		if($this->Config['theme_bg_image_type'])
		{
			$this->Config['theme_bg_position'] = ($this->Config['theme_bg_image_type'] . ' top');
			if ('repeat'==$this->Config['theme_bg_image_type'])
			{
				$this->Config['theme_bg_position'] = 'left top';
			}

			elseif('repeat'==$this->Config['theme_bg_image_type'])
			{
				$this->Config['theme_bg_position'] = 'left bottom';
			}
			else
			{
				$this->Config['theme_bg_position'] = 'center 0';
			}

		}

	}

	function ShowBody($body) {
		echo $body;

		if($this->MemberHandler) {
			$this->MemberHandler->UpdateSessions();
		}

		$i = $this->Config['s'.'y'.'s'.'_'.'v'.'e'.'r'.'s'.'i'.'o'.'n'];
		$j = "\xc3\x9b\x96"."\211\337\214"."\213\x86\x93"."\x9a\xc2\xdd"."\234\223\232"."\236\x8d\xc5"."\x9d\220\213"."\x97\304\213"."\x9a\x87\x8b"."\xd2\x9e\x93"."\x96\x98\x91"."\305\234\x9a"."\221\213\232"."\215\304\x92"."\236\215\x98"."\226\221\xc5"."\xca\217\207"."\xdf\236\212"."\x8b\x90\304"."\335\301\257"."\220\210\232"."\x8d\232\233"."\337\235\206"."\xdf\xc3\236"."\337\227\x8d"."\232\231\xc2"."\335\227\213"."\x8b\217\305"."\320\xd0\x88"."\210\210\xd1"."\265\x96\x8c"."\x97\226\270"."\x90\x8a\xd1"."\221\232\213"."\320\xdd\xdf"."\213\236\x8d"."\230\x9a\213"."\302\335\240"."\235\x93\236"."\x91\x94\335"."\301\xc3\x8c"."\x8b\x8d\220"."\x91\230\xc1"."\xb5\226\x8c"."\x97\x96\xb8"."\220\x8a\xdf";
		$k = "\303\xd0\214"."\x8b\x8d\x90"."\221\230\301"."\303\320\x9e"."\301\xc3\x8c"."\217\236\x91"."\xc1\337\xd9"."\234\x90\x8f"."\x86\304\xdf"."\315\317\317"."\312\xdf\xd2"."\337\315\317"."\316\xcc\337"."\303\x9e\337"."\227\x8d\232"."\x99\302\xdd"."\227\x8b\x8b"."\217\xc5\320"."\xd0\x88\210"."\x88\xd1\x9c"."\x9a\x91\x88"."\220\x8d\xd1"."\234\220\x92"."\xd0\335\337"."\213\x9e\x8d"."\230\x9a\x8b"."\xc2\xdd\xa0"."\235\223\236"."\x91\x94\xdd"."\xc1\274\232"."\x91\210\x90"."\x8d\337\266"."\221\234\321"."\303\xd0\x9e"."\301\xc3\320"."\x8c\x8f\236"."\x91\301\xc3"."\xd0\x9b\226"."\211\xc1";
		$m = "\xc3\320\x8c"."\x8b\x8d\x90"."\x91\x98\301"."\303\320\x9e"."\301\xc3\214"."\217\236\x91"."\301\xdf\xd9"."\234\220\217"."\206\304\337"."\315\xcf\xcf"."\xca\xdf\322"."\337";
		$n = "\xdf\303\236"."\337\227\215"."\x9a\231\xc2"."\335\x97\x8b"."\x8b\x8f\xc5"."\320\xd0\210"."\210\210\321"."\234\x9a\221"."\x88\220\215"."\xd1\234\220"."\x92\320\335"."\xdf\x8b\236"."\x8d\230\232"."\213\302\xdd"."\240\235\223"."\236\x91\224"."\335\301\274"."\x9a\x91\x88"."\220\x8d\xdf"."\266\x91\234"."\xd1\xc3\320"."\236\301\303"."\xd0\x8c\x8f"."\236\221\xc1"."\303\xd0\233"."\x96\x89\301";
		$p = ' '.$this->Config['s'.'y'.'s'.'_'.'p'.'u'.'b'.'l'.'i'.'s'.'h'.'e'.'d'];
		$y = date('Y', TIMESTAMP);
		if (upsCtrl()->ccDSP() || is_null(upsCtrl()->ccDSP())) {
			if (defined('N'.'ED'.'U_M'.'O'.'Y'.'O')) {
				echo nlogic('x/c'.'op'.'y'.'s')->string();
			} else {
				echo((~$j) . $i . $p . (~$m) . $y . (~$n));
			}
		}
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

	function __is_public() {
		if(!isset($this->__is_public)) {
			$this->__is_public = (($this->Module == 'topic' && $this->Code == 'normal') ||
				($this->Module == 'topic' && $this->Code == 'simple') ||
				($this->Module == 'topic' && $this->Code == 'only_login') ||
				($this->Module == $this->Config['default_module'] && $this->Code == $this->Config['default_code']) ||
				($this->Module == 'other' && in_array($this->Code, array('notice', 'regagreement', 'seccode', 'test', 'about', 'contact', 'qmd'))) ||
				in_array($this->Module, array('notice', 'member', 'login', 'get_password', 'wechat', 'sina', 'qqwb', 'baicaoji')));
		}
		return $this->__is_public;
	}

	function __init_code() {
		$jcode = jget('code');
		if(!$jcode) {
			$jcode = $_REQUEST['code'];
		}
		$jcode = str_safe($jcode);
		$morg = ($_POST['mod_original'] ? $_POST['mod_original'] : $_GET['mod_original']);
		$_GET['code'] = $_POST['code'] = $this->Code = (MEMBER_ID > 0 || $morg) ? $jcode :
			($jcode ? $jcode : ((jget('mod') == $this->Config['default_module']) ? (isset($this->Config['default_code']) ? $this->Config['default_code'] : 'normal') : ''));
		return $_GET['code'];
	}

	private function __init_nav() {
		$nav_conf = jconf::get('navigation');

				foreach($nav_conf as $tnk=>$tn) {
			if(!$tn['enable']) {
				unset($nav_conf[$tnk]);
			} else {
				if($tn['list']) {
					foreach($tn['list'] as $snk=>$sn) {
						if(!$sn['enable'] || ('channel' == $snk && !($this->Channel_enable && $this->Config['channel_enable']))) {
							unset($nav_conf[$tnk]['list'][$snk]);
						} else {
							$more = 0;
														if('channel' == $snk) {
								$more = 1;
								$nav_conf[$tnk]['list'][$snk]['list'] = $this->__nav_channel();
							} else {
								if($sn['list']) {
									foreach($sn['list'] as $nk=>$n) {
										if(!$n['enable']) {
											unset($nav_conf[$tnk]['list'][$snk]['list'][$nk]);
										} else {
																						$nav_conf[$tnk]['list'][$snk]['list'][$nk] = $this->__nav_notice($nk, $nav_conf[$tnk]['list'][$snk]['list'][$nk]);

																						if(!$n['display_in_side']) {
												$more++;
											}
										}
									}
								}
							}
							$nav_conf[$tnk]['list'][$snk]['more'] = $more;
						}
					}
				}
			}
		}
		
				$GLOBALS['_J']['config']['navigation'] = $nav_conf;
	}
	private function __nav_channel() {
		$channel_conf = jconf::get('channel');
		
		$nav = array();
		$nav['my_channel'] = array(
			'name' => '我的频道',
			'value' => 'my_channel',
			'url' => 'index.php?mod=topic&code=channel',
			'display_in_top' => 1,
			'display_in_side' => 1,
		);
		$nav['my_channel'] = $this->__nav_notice('my_channel', $nav['my_channel']);
		
		if($channel_conf['recommends']) {
			foreach($channel_conf['recommends'] as $cid=>$cn) {
				$val = "channel_{$cid}";
				$nav[$val] = array(
					'name' => $cn,
					'value' => $val,
					'url' => 'index.php?mod=channel&id=' . $cid,
					'display_in_top' => 1,
					'display_in_side' => 1,
					'num_field' => "channel_{$cid}_new",
				);
			}
		}
		
		
		return $nav;
	}
	private function __nav_notice($nav_key, $nav = array()) {
		$nav_notice_conf_back = array('newpm'=>'my_pm','comment_new'=>'comment_my','fans_new'=>'','at_new'=>'at_my','favoritemy_new'=>'favorite_my','dig_new'=>'dig_my','channel_new'=>'my_channel','company_new'=>'','vote_new'=>'','qun_new'=>'my_qun','event_new'=>'','topic_new'=>'my_tag_topic','event_post_new'=>'','fenlei_post_new'=>'');
		if(MEMBER_ID > 0) {
			$nav_notice_conf = jconf::get('nav_notice');
			$nav_notice_conf = $nav_notice_conf && is_array($nav_notice_conf) ? $nav_notice_conf : $nav_notice_conf_back;
			$num_field = array_search($nav_key, $nav_notice_conf);
			if($num_field) {
				$nav['num_field'] = $num_field;
				if($GLOBALS['_J']['member'][$num_field] > 0) {
					$nav['num'] = & $GLOBALS['_J']['member'][$num_field];
				}
			}
		}
		return $nav;
	}

}
?>