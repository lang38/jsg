<?php
/**
 * 文件名：upgrade.php
 * 作     者：狐狸<foxis@qq.com>
 * 功能描述: 从任意版本记事狗升级到最新版
 * @version $Id: upgrade.php 5584 2014-02-26 03:30:26Z wuliyong $
 * @todo 3.0.2 配置文件更新，数据库结构更新 2011年9月27日
 * @todo 3.0.4 数据库结构升级步骤调整 先结构再数据升级 2011年12月7日
 */



define('IN_JISHIGOU_UPGRADE', true);

require('./include/jishigou.php');
$jishigou = new jishigou();
$jishigou->init();

error_reporting(E_ERROR);
@set_time_limit(900);
ini_set('max_execution_time', 900);
ini_set("memory_limit","512M");

global $_J;
$this_file = 'upgrade.php';
$db_prefix = TABLE_PREFIX;

if(!empty($_GET['img'])) {
	upgrade_show_img($_GET['img']);
	exit; }

$ulic_id = 'upgrade_lock_ip';
if(false !== ($uli = cache_file('get', $ulic_id)) && $uli != $_J['client_ip']) {
	upgrade_message('已经在升级了...');
}

$upgrade = in_array($_GET['upgrade'],array('start','setting','db','done')) ? $_GET['upgrade'] : 'start';
if('start' == $upgrade) { 		$dirs = array('install',
		'data', 'data/attachs', 'data/cache', 'data/log', 'data/backup', 'data/backup/setting', 'data/backup/db', 'data/upgrade',
		'wap/data', 'wap/data/cache', );
	foreach($dirs as $_dir) {
		if(!is_dir(ROOT_PATH . $_dir)) {
			jmkdir(ROOT_PATH . $_dir);
		}
	}
	$f = ROOT_PATH . './data/cache/upgrade.lock';
	touch($f);

	cache_file('set', $ulic_id, $_J['client_ip']);


		@upgrade_remove_dir(ROOT_PATH . 'include/db/');
	@upgrade_remove_dir(ROOT_PATH . 'include/encoding/');
	@upgrade_remove_dir(ROOT_PATH . 'include/function/');
	@upgrade_remove_dir(ROOT_PATH . 'include/lib/');
	@upgrade_remove_dir(ROOT_PATH . 'include/oss/');
	@upgrade_remove_dir(ROOT_PATH . 'include/qqwb/');
	@upgrade_remove_dir(ROOT_PATH . 'include/task/');
	@upgrade_remove_dir(ROOT_PATH . 'include/xwb/');

	@upgrade_remove_dir(ROOT_PATH . 'modules/server/');
	@upgrade_remove_dir(ROOT_PATH . 'uc_client/');
	@upgrade_remove_dir(ROOT_PATH . 'pw_client/');
	@upgrade_remove_dir(ROOT_PATH . 'cache/');

	@unlink(ROOT_PATH . 'index.htm');
	@unlink(ROOT_PATH . 'index.html');
	@unlink(ROOT_PATH . 'public.php');
	@unlink(ROOT_PATH . 'server.php');
		@unlink(ROOT_PATH . 'include/func/server.func.php');
	@unlink(ROOT_PATH . 'modules/test.mod.php');
	@unlink(ROOT_PATH . 'modules/ajax/test.mod.php');
	@unlink(ROOT_PATH . 'plugin/.htaccess');
	@unlink(ROOT_PATH . 'data/.htaccess');

	$f = ROOT_PATH . 'data/install.lock';
	upgrade_lock_file($f);

	$f = ROOT_PATH . 'install/install.lock';
	upgrade_lock_file($f);

	if(!file_exists(ROOT_PATH . 'images/logo.png')) {
		@copy(ROOT_PATH . 'static/image/logo.png', ROOT_PATH . 'images/logo.png');
	}
	if(!file_exists(ROOT_PATH . 'images/logo2.png')) {
		@copy(ROOT_PATH . 'static/image/logo2.png', ROOT_PATH . 'images/logo2.png');
	}


	upgrade_message("请勿关闭窗口，正在升级中……",$this_file . "?upgrade=setting");

} elseif('done' == $upgrade) { 		cache_clear();

		cache_db('clear');

		jtable('failedlogins')->truncate();

		jconf::set('validate_category', array());

		jlogic('channel')->update_data();

		jlogic('image')->update_data();

		jlogic('credits')->rule_conf(true);

		$config_new = array();
	$config_new['safe_key'] = random(64); 	$config_new['upgrade_lock_time'] = 0;

	jconf::update($config_new);

	@unlink(ROOT_PATH . $this_file);
			upgrade_message("本次升级完毕：<br />
		1、<a href='index.php'>点此</a>进入网站前台<br />
		2、<a href='admin.php'>点此</a>进入网站后台<br />
		3、<a href='changelog.txt' target='_blank'>点此</a>查看更新日志<br />
		<iframe width='0' height='0' src='index.php'></iframe>", 'index.php', 30);
}

elseif('setting' == $upgrade) { 
		$config_new = $config_def = jconf::core_settings();

	$config_new['site_closed'] = 0;
	$config_new['upgrade_lock_time'] = TIMESTAMP;
		if(isset($config_def['imjiqiren_enable']) && !is_file(ROOT_PATH . 'include/func/imjiqiren.func.php')) {
		$config_new['imjiqiren_enable'] = 0;
	}
	if(isset($config_def['api_enable']) && !is_file(ROOT_PATH . 'include/func/api.func.php')) {
		$config_new['api_enable'] = 0;
	}
	if(isset($config_def['sms_extra_enable']) && !is_file(ROOT_PATH . 'include/func/sms_extra.func.php')) {
		$config_new['sms_extra_enable'] = 0;
	}
	if($config_def['templatedeveloper'] && '127.0.0.1'!=$_SERVER['REMOTE_ADDR']) {
		$config_new['templatedeveloper'] = 0;
	}
	if($config_def['department_enable'] || $config_def['company_enable']) {
		if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')) {
			$config_new['department_enable'] = $config_new['company_enable'] = 0;
		}
	}
	

		$config_defs = array(
		'auth_key' => random(64),
		'safe_key' => random(64),
		'cache_table_num' => 16,
		'cache_db_to_memory' => 1,
		'cache_file_to_memory' => 1,
    	'wap' => 1,
    	'video_status' => 1,
    	'city_status' => 1,
    	'lastpost_time' => 5,
    	'total_page_default' => 100,
    	'ajax_topic_time' => 45,
    	'topic_modify_time' => 60,
    	'wap_url' => $config_new['site_url'] . '/wap',
		'mobile_url' => $config_new['site_url'] . '/mobile',
    	'slide_index_enable' => 1,
    	'topic_myhome_time_limit' => 300,
    	'style_three_tol' => 1,
    	'slide_index_enable' => 1,
    	'regstatus' => array('normal', 'invite'),
    	'invite_limit' => $config_def['invite_enable'],
    	'verify_alert' => 1,
		'normal_default_role_id' => 3,
		'no_verify_email_role_id' => 5,
		'jishigou_founder' => ($config_def['aijuhe_founder'] ? $config_def['aijuhe_founder'] : 1),
		'qmd_file_url' => 'images/qmd/',
		'qun_attach_enable' => 1,
	    'qun_setting' => array (
		    'qun_open' => '1',
		    'new_qun' => '1',
		    'tc_qun' => '1',
		    'img_size' => 200,
		    'member_num' => 300,
		    'admin_num' => 5,
		    'qun_ploy' =>  array (
			      'avatar' => '1',
			      'vip' => '1',
	),
	),
		'follow_limit' => 2000,

				'channel_enable' => 1,
		'channel_must' => 0,
		'edit_face_enable' => 1,
		'face_enable' => 1,
		'image_enable' => 1,
		'music_enable' => 1,
		'tag_enable' => 1,
		'video_enable' => 1,
		'website_home_page' => 'normal',
		'sign_tag' => array(
			'enable' => 1,
		),
		'reg_step7_radio' => 1,
		'first_topic_to_channel' => 1,

		
		  'memory' =>
		  array (
		  	'redis' => array(
		  		'enable' => ((extension_loaded('redis') && ($obj = new Redis()) && @$obj->connect('127.0.0.1', '6379')) ? 1 : 0),
		  		'server' => '127.0.0.1',
		  		'port' => '6379',
		  		'pconnect' => 1,
		  		'serializer' => 1,
		  	),
		    'memcache' =>
		    array (
		      'enable' => ((extension_loaded('memcache') && ($obj = new Memcache()) && @$obj->connect('127.0.0.1', '11211')) ? 1 : 0),
		      'connect' =>
		      array (
		        0 =>
		        array (
		          'server' => '127.0.0.1',
		          'port' => '11211',
		        ),
		      ),
		    ),
		   	'apc' => array('enable' => ((function_exists('apc_cache_info') && apc_cache_info()) ? 1 : 0)),
		    'xcache' => array('enable' => (function_exists('xcache_get') ? 1 : 0)),
		    'eaccelerator' => array('enable' => (function_exists('eaccelerator_get') ? 1 : 0)),
		    'wincache' => array('enable' => ((function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo()) ? 1 : 0)),
		  ),

		  
		  'seccode_enable' => (@fsockopen('api.yinxiangma.com',80,$errno,$errstr,1) ? 2 : 1),
		  'seccode_comment' => 1,
		  'seccode_forward' => 1,
		  'seccode_login' => 1,
		  'seccode_no_email' => 0,
		  'seccode_no_photo' => 1,
		  'seccode_no_vip' => 0,
		  'seccode_password' => 1,
		  'seccode_pri_key' => 'ba654d1411c3ba4e3ed6b8b2ef29a470',
		  'seccode_pub_key' => '98583a76eb813b39381fdb1684908dc0',
		  'seccode_publish' => 1,
		  'seccode_purview' => '3,4,5,108',
		  'seccode_purviews' =>
		  array (
		    3 => 3,
		    4 => 4,
		    5 => 5,
		    108 => 108,
		  ),
		  'seccode_register' => 1,
		  'seccode_sms' => 1,

		  'default_code' => 'normal',
		  'default_module' => 'topic',
		  'account_on_off' => 1,
		  'card_pic_enable' =>
		  array (
		    'is_card_pic' => 1,
		  ),
		  		  'reward_open' => 1,
		  'gzip' => (function_exists('ob_gzhandler') ? 1 : 0),
		  'on_publish_notice' => '有什'. '么新' .'鲜'. '事与'. '大' .'家分' .'享？',
		  'cookie_domain' => '',
		  'image_size' => 2048,
		  'image_size_limit' => 2048 * 1024,
		  'image_width_p' => 280,
		  'image_thumb_quality' => 100,

		  'template_path' => 'default',

		  'anonymous_enable' => '1',

		  'sms_link_display' => '0',

		  'attach_file_type' => 'zip|rar|txt|doc|xls|pdf|ppt|docx|xlsx|pptx',

	);
	$config_defs['memory_enable'] = (($config_defs['memory']['redis']['enable'] ||
		$config_defs['memory']['redis']['enable'] ||
		$config_defs['memory']['memcache']['enable'] ||
		$config_defs['memory']['apc']['enable'] ||
		$config_defs['memory']['xcache']['enable'] ||
		$config_defs['memory']['eaccelerator']['enable'] ||
		$config_defs['memory']['wincache']['enable']) ? 1 : 0);
		foreach($config_defs as $k=>$v) {
		if(!isset($config_def[$k])) {
			$config_new[$k] = $v;
		}
	}
			$config_new['memory_enable'] = (($config_new['memory']['redis']['enable'] ||
		$config_new['memory']['redis']['enable'] ||
		$config_new['memory']['memcache']['enable'] ||
		$config_new['memory']['apc']['enable'] ||
		$config_new['memory']['xcache']['enable'] ||
		$config_new['memory']['eaccelerator']['enable'] ||
		$config_new['memory']['wincache']['enable']) ? 1 : 0);
	
		$config_unsets = array(
    	'url_status' => 1,
    	'invite_enable' => 1,
		'aijuhe_founder' => 1,
		'compiled_root_path' => 1,

		'access' => 1,
		'ad' => 1,
		'api' => 1,
		'area' => 1,
		'attach' => 1,
		'cache' => 1,
		'constants' => 1,
    	'credits_rule' => 1,
    	'credits' => 1,
		'dedecms' => 1,
		'default_regfollow' => 1,
		'dzbbs' => 1,
		'email_notice' => 1,
		'experience' => 1,
		'face' => 1,
		'filter' => 1,
		'follow' => 1,
		'ftp' => 1,
		'hot_tag_recommend' => 1,
		'imjiqiren' => 1,
		'link' => 1,
		'live' => 1,
		'login_enable' => 1,
		'navigation' => 1,
		'phpwind' => 1,
		'plugin' => 1,
		'qqwb' => 1,
		'qun_category' => 1,
		'qun_level' => 1,
		'qun_module' => 1,
		'qun_ploy' => 1,
		'report' => 1,
		'rewrite' => 1,
		'robot' => 1,
		'share' => 1,
		'sina' => 1,
		'slide_index' => 1,
		'sms' => 1,
		'smtp' => 1,
		'tag_num' => 1,
		'tag' => 1,
		'talk_category' => 1,
		'talk' => 1,
		'task' => 1,
		'theme' => 1,
		'topic_from' => 1,
		'topicface' => 1,
		'tusiji_face' => 1,
		'ucenter' => 1,
		'user' => 1,
		'ucenter' => 1,
		'validate_category' => 1,
		'web_info' => 1,
		'xss' => 1,
	);
	foreach($config_unsets as $k=>$v) {
		if(isset($config_def[$k])) {
			$config_new[$k] = null; unset($config_new[$k]);
		}
	}

		if('default' != $config_new['template_path'] && $config_new['upgrade_to_lock_version']<41) {
		$config_new['template_path'] = 'default';
	}

	if($config_new['install_lock_time'] < 100) {
		$config_new['install_lock_time'] = time();
	}

	if(!$config_new['no_verify_email_role_id'] || 1==$config_new['no_verify_email_role_id']) {
		$config_new['no_verify_email_role_id'] = 5;
	}

	if(!isset($config_def['topic_cut_length'])) {
		$config_new['topic_cut_length'] = ($config_def['topic_length'] ? $config_def['topic_length'] : 140);

		if(!isset($config_def['topic_input_length'])) {
			$config_new['topic_input_length'] = $config_new['topic_cut_length'];
		}
	}

	$_host = getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST'];
	if('www.'==substr($_host, 0, 4)) {
		$_c_d = substr($_host, 4);
		if(false !== ($_c_s_p = strpos($_c_d, ':'))) {
			$_c_d = substr($_c_d, 0, $_c_s_p);
		}
		if($_c_d && $_c_d != $config_def['cookie_domain']) {
			$config_new['cookie_prefix'] = 'JishiGou_' . random(6) . '_';
			$config_new['cookie_domain'] = $_c_d;
			$config_new['auth_key'] = random(64);
		}
	}

	if(strlen($config_def['auth_key']) < 64) {
		$config_new['auth_key'] = random(64);
	}

		unset($config_new['theme']);
	if($config_new['upgrade_to_lock_version']<41) {
		$config_theme = jconf::get('theme');
		$tbc = $config_theme['theme_list'][$config_new['theme_id']]['theme_bg_color'];
		if($tbc && $tbc != $config_new['theme_bg_color']) {
			$config_new['theme_bg_color'] = $tbc;
		} else {
			$config_new['theme_id'] = 't1';
			$config_new['theme_bg_color'] = '#edece9';
		}
	}

	unset($config_new['ucenter']);
	$config_ucenter = jconf::get('ucenter');
	$config_new['ucenter_enable'] = ($config_ucenter['enable'] ? 1 : 0);

	unset($config_new['rewrite']);
	$_rewrite = array();
	$config_rewrite = jconf::get('rewrite');
	if(!$config_rewrite) {
		if(!$_rewrite) {
			$_rewrite = array (
			  'mode' => '',
			  'abs_path' => '/',
			  'arg_separator' => '/',
			  'var_separator' => '-',
			  'prepend_var_list' =>
			  array (
			    0 => 'mod',
			    1 => 'code',
			  ),
			  'var_replace_list' =>
			  array (
			    'mod' =>
			    array (
			    ),
			  ),
			  'value_replace_list' =>
				 array (
			    'mod' =>
			    array (
					'topic' => array_rand(array('miniblog'=>1,'myblog'=>1,'blog'=>1,'topics'=>1,'weibo'=>1,)),
					'tag' => array_rand(array('keywords'=>1,'channels'=>1,'class'=>1,'tags'=>1,)),
					'profile' => array_rand(array('profiles'=>1,'personals'=>1,)),
					'member' => array_rand(array('users'=>1,'members'=>1,)),
					'plugin' => array_rand(array('plugins'=>1, 'extends'=>1, 'expands'=>1, 'applications'=>1, 'packages'=>1, )),
			    ),
			  ),
			  'gateway' => '',
			);
		}

		jconf::set('rewrite', $_rewrite);
	}
	$_rewrite = ($_rewrite ? $_rewrite : $config_rewrite);
	$config_new['rewrite_enable'] = ($_rewrite['mode'] ? 1 : 0);
	if('stand' == $_rewrite['mode'] && $_rewrite['abs_path'] && file_exists(ROOT_PATH . './.htaccess') && false===strpos(file_get_contents(ROOT_PATH . './.htaccess'), 'mobile')) {
		$htaccess_str = "# BEGIN JishiGou
<IfModule mod_rewrite.c>
RewriteEngine On
".((preg_match("~^localhost|127\.0\.0\.1|192\.168\.\d+\.\d+$~",$_SERVER['SERVER_ADDR']))?"Options FollowSymLinks":"")."
RewriteBase {$_rewrite['abs_path']}
RewriteCond %{REQUEST_URI}	!\.(gif|jpeg|png|jpg|bmp)$
RewriteCond %{REQUEST_URI}  !^{$_rewrite['abs_path']}(wap|mobile)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</IfModule>
# END JishiGou";

		file_put_contents(ROOT_PATH . './.htaccess', $htaccess_str);
	}
	unset($_rewrite);

	unset($config_new['robot']);
	$config_robot = jconf::get('robot');
	$config_new['robot_enable'] = ($config_robot['turnon'] ? 1 : 0);

	unset($config_new['ad']);
	$config_ad = jconf::get('ad');
	$config_new['ad_enable'] = ($config_ad['enable'] ? 1 : 0);

	unset($config_new['credits']);
	$config_credits = jconf::get('credits');
	if(!(is_array($config_credits)) || (isset($config_new['extcredits1']) && !isset($config_credits['ext']))) {
		$config_credits = array (
          'ext' =>
		array (
            'extcredits2' =>
		array (
              'enable' => 1,
              'ico' => '',
              'name' => '金币',
              'unit' => '',
              'default' => 0,
		),
		),
          'formula' => '$member[topic_count]+$member[extcredits2]',
		);
		jconf::set('credits',$config_credits);
	}
	$config_new['extcredits_enable'] = ($config_credits['ext'] ? 1 : 0);

		$tfd = $topic_from = jconf::get('topic_from');
	if(isset($topic_from['android'])) {
		unset($topic_from['android']);
	}
	if(!isset($topic_from['qqwb'])) {
		$topic_from['qqwb'] = array (
		    'name' => '腾讯微博',
		    'value' => 'qqwb',
		    'link' => 'index.php?mod=account&code=qqwb',
		  );
	}
	if($tfd != $topic_from) {
		jconf::set('topic_from', $topic_from);
	}

	unset($config_new['qqwb']);
	$config_qqwb = jconf::get('qqwb');
	if(!$config_qqwb) {
		$config_qqwb = array (
            'enable' => (upgrade_check_qqwb_env() ? 0 : 1),
            'app_key' => '8c84c76e55d6491a991d8b568ad15209',
			'app_secret' => '949d659ce5e89230c5bac10e17bc2ba8',
			'is_account_binding' => 1,
			'is_synctopic_toweibo' => 1,
			'is_syncreply_toweibo' => 1,
			'is_rebutton_display' => 1,
			'reg_pwd_display' => 1,
			'is_sync_face' => 1,
			'is_upload_image' => 1,
			'wbx_share_time' => 15,
			'is_synctopic_tojishigou' => 0,
			'is_syncreply_tojishigou' => 0,
			'is_syncimage_tojishigou' => 0,
			'syncweibo_tojishigou_time' => 180,
		);
		jconf::set('qqwb',$config_qqwb);
	}
	$config_new['qqwb_enable'] = ($config_qqwb['enable'] ? 1 : 0);

	unset($config_new['sina']);
	$config_sina = jconf::get('sina');
	if(!$config_sina) {
		$config_sina = array (
            'enable' => (upgrade_check_sina_env() ? 0 : 1),
            'app_key' => '3015840342',
            'app_secret' => '484175eda3cf0da583d7e7231c405988',
            'reg_pwd_display' => 1,
            'is_account_binding' => 1,
            'is_synctopic_toweibo' => 1,
            'is_syncreply_toweibo' => 1,
            'is_rebutton_display' => 1,
            'is_sync_face' => 1,
            'is_upload_image' => 1,
            'wbx_share_time' => 15,
            'is_synctopic_tojishigou' => 0,
            'is_syncreply_tojishigou' => 0,
            'is_syncimage_tojishigou' => 0,
            'syncweibo_tojishigou_time' => 180,
		);

		jconf::set('sina',$config_sina);
	}
	$config_new['sina_enable'] = ($config_sina['enable'] ? 1 : 0);

	$__XWB_SET = array();
	include(ROOT_PATH . 'include/ext/xwb/set.data.php');
	$__XWB_SET_OLD = $__XWB_SET;
	$__XWB_SET_DEFAULT=array (
      'wb_addr_display' => 1,
      'reg_pwd_display' => 1,
      'sync_uid' => 1,
      'sync_username' => '新浪微博',
      'is_rsync_comment' => 0,
      'is_wbx_display' => 1,
      'wbx_width' => 100,
      'wbx_height' => 500,
      'wbx_style' => 2,
      'wbx_line' => 7,
      'wbx_is_title' => 1,
      'wbx_is_blog' => 1,
      'wbx_is_fans' => 1,
      'wbx_url' => 'http:/'.'/service.t.sina.com.cn/widget/WeiboShow.php?uname=xweibo%E6%B5%8B%E8%AF%95%E5%B8%90%E5%8F%B7&width=180&height=500&skin=2&isTitle=1&isWeibo=1&isFans=1&fansRow=7&__noCache=1289876143693',
      'is_account_binding' => 1,
      'is_synctopic_toweibo' => 1,
      'is_syncreply_toweibo' => 1,
      'is_rebutton_display' => 1,
      'is_tips_display' => 1,
      'is_signature_display' => 1,
      'is_sync_face' => 1,
      'is_upload_image' => 1,
      'wbx_medal_update_time' => 1800,
      'wbx_share_time' => 15,
      'is_synctopic_tojishigou' => 0,
      'is_syncreply_tojishigou' => 0,
      'is_syncimage_tojishigou' => 0,
      'syncweibo_tojishigou_time' => 180,
	);
	foreach($__XWB_SET_DEFAULT as $k=>$v) {
		if(!isset($__XWB_SET[$k])) {
			$__XWB_SET[$k] = $v;
		}
	}
	if($__XWB_SET!=$__XWB_SET_OLD) {
		file_put_contents(ROOT_PATH . 'include/ext/xwb/set.data.php','<?php $__XWB_SET = '.var_export($__XWB_SET,true).'; ?>');
	}

	unset($config_new['web_info']);
	$config_web_info = jconf::get('web_info');
	if(!$config_web_info) {
		$config_web_info = array (
          'about' => '关于我们<br />',
          'contact' => '联系我们<br />',
          'joins' => '加入我们<br />',
		);

		jconf::set('web_info',$config_web_info);
	}


		unset($config_new['show']);
	$show_default = array (
	  'topic_index' =>
	  array (
	    'recommend_topic' => '20',
	    'hot_tag' => '10',
	    'new_user' => '9',
	    'guanzhu' => '9',
	  ),
	  'topic_new' =>
	  array (
	    'topic' => '20',
	    'tag' => '20',
	  ),
	  'topic_hot' =>
	  array (
	    'day1' => '20',
	    'day7' => '20',
	    'day14' => '20',
	    'day30' => '20',
	  ),
	  'reply_hot' =>
	  array (
	    'day1' => '20',
	    'day7' => '20',
	    'day14' => '20',
	    'day30' => '20',
	  ),
	  'new_reply' =>
	  array (
	    'reply' => '20',
	  ),
	  'topic_top' =>
	  array (
	    'guanzhu' => '20',
	    'renqi' => '20',
	    'huoyue' => '20',
	    'yingxiang' => '20',
	    'credits' => '20',
	  ),
	  'tag_index' =>
	  array (
	    'guanzhu' => '20',
	    'hot' => '20',
	    'day7' => '20',
	    'day7_guanzhu' => '20',
	    'tag_tuijian' => '10',
	  ),
	  'page_r' =>
	  array (
	    'recd_qun' => '8',
	    'tc_event' => '10',
	  ),
	  'qun' =>
	  array (
	    'activity' => '6',
	  ),
	  'topic' =>
	  array (
	    'myhome' => '20',
	    'myblog' => '20',
	    'myfavorite' => '20',
	    'favoritemy' => '20',
	    'mycomment' => '20',
	    'myat' => '20',
	    'follow' => '20',
	    'fans' => '20',
	  ),
	  'media' =>
	  array (
	    'user' => '20',
	  ),
	  'media_view' =>
	  array (
	    'user' => '20',
	  ),
	  'tag_view' =>
	  array (
	    'tag' => '20',
	  ),
	  'reg_follow' =>
	  array (
	    'user' => '20',
	  ),
	  'vote' =>
	  array (
	    'list' => '20',
	    'recd' => '10',
	  ),
	  'notice' =>
	  array (
	    'list' => '6',
	  ),
	  'topic_one_comment' =>
	  array (
	    'list' => '6',
	  ),
	);
	$show_old = jconf::get('show');
	$show_new = array();
	foreach ($show_default as $k=>$val) {
		if(!is_array($val)) {
			if (!$show_old[$k]) {
				$show_new[$k] = $val;
			} else {
				$show_new[$k] = $show_old[$k];
			}
		} else {
			foreach ($val as $_k=>$_v) {
				if (!$show_old[$k][$_k]) {
					$show_new[$k][$_k] = $_v;
				} else {
					$show_new[$k][$_k] = $show_old[$k][$_k];
				}
			}
		}
	}
	if ($show_old!=$show_new) {
		jconf::set('show',$show_new);
	}

		unset($config_new['slide_index']);
	$slide_index = jconf::get('slide_index');
	if(!$slide_index)
	{
		$slide_index = array (
          'list' =>
		array (
		0 =>
		array (
              'enable' => 1,
              'src' => 'static/image/index/ad.jpg',
              'href' => 'index.php?mod=member',
              'order' => 1,
		),
		1 =>
		array (
              'enable' => 1,
              'src' => 'static/image/index/ad_2.jpg',
              'href' => 'index.php?mod=member',
              'order' => 2,
		),
		),
          'enable' => 1,
		);

		jconf::set('slide_index',$slide_index);
	}

		unset($config_new['sms']);
	$sms = jconf::get('sms');
	if(!$sms) {
		$sms = array (
            	'r_enable' => 1,

                't_enable' => 1,
                'p_enable' => 0,
                'm_enable' => 1,
                'f_enable' => 0,
		);
		jconf::set('sms', $sms);
	}
	if(!isset($sms['r_enable'])) {
		$sms['r_enable'] = 1;

		jconf::set('sms', $sms);
	}

		unset($config_new['navigation']);
	$config_navigation = jconf::get('navigation');
	if(empty($config_navigation) || is_array($config_navigation['list'])) {
		
				$nav_def_conf = jconf::get('navigation_default');
		if($nav_def_conf) {
			jconf::set('navigation', $nav_def_conf);
		}
	}

		unset($config_new['attach']);
	$def_conf_attach = $config_attach = jconf::get('attach');
	if(empty($config_attach)) {
		$config_attach = array(
                'enable' => 1,
				'qun_enable' => 1,
				'request_file_type' => 'zip|rar|txt|doc|xls|pdf|ppt|docx|xlsx|pptx',
            	'request_size_limit' => 2048,
				'request_files_limit' => 3,
				'score_min' => 1,
				'score_max' => 20,
				'no_score_user' => '2',
            );
	} else {
		if(empty($config_attach['request_file_type'])) {
			$config_attach['request_file_type'] = 'zip|rar|txt|doc|xls|pdf|ppt|docx|xlsx|pptx';
		}
	}
	if($def_conf_attach != $config_attach) {
		jconf::set('attach', $config_attach);
	}
	if(empty($config_new['attach_file_type'])) {
		$config_new['attach_file_type'] = $config_attach['request_file_type'];
	}


		if($config_new!=$config_def) {
		jconf::set($config_new);
	}


	upgrade_message("文件配置升级成功，正在升级优化数据库结构……",$this_file . "?upgrade=db");

}

elseif ('db' == $upgrade)
{
	$tb_id = (int) $_GET['tb_id'];
	$tb_name_list_cache_id = 'upgrade/db_tb_name_list';

		global $db;

	$db = new upgrade_dbstuff;
	$db->connect($GLOBALS['_J']['config']['db_host'] . ($GLOBALS['_J']['config']['db_port'] ? ":{$GLOBALS['_J']['config']['db_port']}" : ''), $GLOBALS['_J']['config']['db_user'],$GLOBALS['_J']['config']['db_pass'],$GLOBALS['_J']['config']['db_name']);


	if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.4.4.lock'))) {
				$db->query("ALTER TABLE {$db_prefix}robot_log DROP KEY `name`", "SILENT");
		$db->query("ALTER TABLE {$db_prefix}robot_log ADD KEY `name` (`name`)", "SILENT");
		$db->query("ALTER TABLE {$db_prefix}robot_log ADD UNIQUE `date-name` (`date`, `name`)", "SILENT");

				$db->query("ALTER TABLE {$db_prefix}vote_user DROP PRIMARY KEY", "SILENT");
		$db->query("ALTER TABLE {$db_prefix}vote_user ADD `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY", "SILENT");

				$row = $db->fetch_first("SELECT COUNT(1) AS `count` FROM {$db_prefix}credits_log");
		if($row['count'] < 1) {
			$db->query("DROP TABLE IF EXISTS {$db_prefix}credits_log", "SILENT");
		} else {
			$db->query("ALTER TABLE {$db_prefix}credits_log ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT ,
				ADD `rid` tinyint(2) NOT NULL ,
				ADD `remark` varchar(255) NOT NULL DEFAULT ''", "SILENT");
		}

		@touch($lock_file);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db");
	} else {
		upgrade_lock_file($lock_file);
	}


		$jishigou_sql_file = ROOT_PATH . 'install/jishigou.sql';
	if (!file_exists($jishigou_sql_file)) {
		upgrade_message("数据库文件<b>{$jishigou_sql_file}</b>不存在，请检查");
	}

	$db_re_cache_id = 'upgrade/db_re';
	if(false === ($re = cache_file('get', $db_re_cache_id)))
	{
		$jishigou_sql_data = str_replace('`jishigou_','`'. $GLOBALS['_J']['config']['db_table_prefix'],file_get_contents($jishigou_sql_file));
		$jishigou_sql_data = preg_replace('~ COMMENT \'.*?\'~i', ' ', $jishigou_sql_data); 		preg_match_all('~\s+create\s+table\s+(.+)?\s*\(([^=\;]+)\).+?\;~i',$jishigou_sql_data,$re,2);

		cache_file('set', $db_re_cache_id, $re, 1800);

		if(false===cache_file('get', $tb_name_list_cache_id))
		{
			$re_count = count($re);

			$tb_name_list = array();
			for($i=0; $i<$re_count; $i++)
			{
				$tb_arr = $re[$i];
				$tb_name = trim($tb_arr[1]);
				$tb_name = trim($tb_name,'`');
				$tb_name_list[] = $tb_name;
			}

			cache_file('set', $tb_name_list_cache_id, $tb_name_list, 1800);
		}
	}
	$re_count = count($re);


	$_db_update = $_GET['db_update'];

	if(!$_db_update)
	{
		$sqls = '';
		$tb_name_list = array();
		for ($i=$tb_id;$i<$re_count;$i++)
		{
			$tb_arr = $re[$i];
			$tb_name = trim($tb_arr[1]);
			$tb_name = trim($tb_name,'`');
			$tb_name_list[] = $tb_name;


			$sql = "SHOW CREATE TABLE `{$tb_name}`";
			$query = $db->query($sql,'SILENT');
			$sqls = '';
			if (!$query)
			{
								$sqls = trim($tb_arr[0]);

								$sqls = preg_replace('~\s+ENGINE\s*\=\s*MyISAM\s*\;~i', (mysql_get_server_info() > '4.1' ? " ENGINE=MyISAM DEFAULT CHARSET=". $GLOBALS['_J']['config']['db_charset'] : " TYPE=MyISAM"), $sqls);
			}
			else
			{
				$tbfs = upgrade_table_fields($tb_arr[0]);

				$row = $db->fetch($query);

				$_tbfs = upgrade_table_fields($row["Create Table"]);

				if ($tbfs && $_tbfs && $tbfs != $_tbfs)
				{
					$sql_l = array();
					$sql_2 = array();
					foreach ($tbfs as $key=>$info)
					{
						if(!isset($_tbfs[$key]))
						{
							if(false===strpos($key,'KEY - ') && !in_array($info,$_tbfs))
							{
								$sql_l[] = "ADD " . $info;
							}
							else
							{
								list($_a,$_b) = explode(' - ',$key);
								if($_b && false === strpos($_a, 'PRIMARY'))
								{
									if ('KEY' == $_a && !in_array($info,$_tbfs))
									{
										$sql_l[] = "ADD INDEX " . $info;
									}
									else
									{
																			}
								}
							}
						}
						else
						{
							if(false===strpos($key,'KEY - ') && !in_array($info,$_tbfs))
							{
															}
						}
						unset($_tbfs[$key]);
					}

					if ($_tbfs)
					{
						foreach ($_tbfs as $_key=>$_info)
						{
							if(false===strpos($_key,'KEY - ') && !in_array($_info,$tbfs))
							{
								$sql_2[$_key] = "DROP `{$_key}`";
							}
							else
							{
								list($_a,$_b) = explode(' - ',$_key);
								if ($_b && false === strpos($_a, 'PRIMARY') && false === strpos($_a, 'UNIQUE') && !in_array($_info,$tbfs))
								{
									$sql_2['KEY - '.$_b] = "DROP KEY `{$_b}`";
								}
							}
						}
					}

					if (count($sql_l) > 0)
					{
						foreach ($sql_l as $_k=>$_v)
						{
							$_v = trim($_v);
							$_v = trim($_v,',');

							if($_v) {
								$sql_l[$_k] = $_v;
							}
						}

						if($sql_l) {
							$sqls = "ALTER TABLE `{$tb_name}` " . implode(" , \r\n\t ",$sql_l);
						}
					}
				}
			}

			if ($sqls || $sql_2) {
				if($sqls) {
					$sqls = trim($sqls); $sqls = str_replace(array(',,','``',),array(',','`',),$sqls); $sqls = trim($sqls,','); $sqls = trim($sqls,';'); $sqls = trim($sqls,','); $sqls = trim($sqls,',;');
					$db->query($sqls);
				}

								if(count($sql_2) > 0) {
					if($_GET['confirm']) {
						if($_GET['sql_3']) {
							$sql_3 = array();
							foreach($_GET['sql_3'] as $_k) {
								$_v = $sql_2[$_k];
								$_v = trim($_v);
								$_v = trim($_v, ',');
								if($_v) {
									$sql_3[$_k] = $_v;
								}
							}
							if($sql_3) {
								$sqls = "ALTER TABLE `{$tb_name}` " . implode(" , \r\n\t ", $sql_3);
								$sqls = trim($sqls); $sqls = str_replace(array(',,','``',),array(',','`',),$sqls); $sqls = trim($sqls,','); $sqls = trim($sqls,';'); $sqls = trim($sqls,','); $sqls = trim($sqls,',;');
								$db->query($sqls);
							}
						}
					} else {
						$sql_3_checkbox = '';
						foreach($sql_2 as $_k=>$_v) {
							$sql_3_checkbox .= "<label><input type='checkbox' name='sql_3[]' value='$_k' checked />$_k</label><br />";
						}
						upgrade_message("对比最新标准版，发现[{$tb_name}]表中存在如下多余字段，系统将自动删除：<br />
							【重要】如存在您自行添加的字段，请去掉相应的勾选！<br />
							<form method='get' action='{$this_file}'>
								<input type='hidden' name='upgrade' value='db' />
								<input type='hidden' name='tb_id' value='$i' />
								<input type='hidden' name='confirm' value='1' />
								$sql_3_checkbox <br />
								<input type='submit' value='确认删除，并进入到下一表的升级操作' />
							</form>");
					}
				}

				upgrade_message("[{$tb_name}]请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&tb_id=" . ($i + 1));
			}
		}
	}


	
		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.1.2.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<12)
	{
		$id_min = max(0,(int) $_GET['id_min']);
		$id_max = $id_min + 300;

		if($id_min < 1)
		{
			$db->query("ALTER TABLE {$db_prefix}url CHANGE `key` `key` VARCHAR(10) ".($db->version() > '4.1' ? " CHARACTER SET ". $GLOBALS['_J']['config']['db_charset']." COLLATE ". $GLOBALS['_J']['config']['db_charset']."_bin " : " BINARY ")." DEFAULT '' NOT NULL");
			$db->query("ALTER TABLE {$db_prefix}url CHANGE `url` `url` text NOT NULL default ''");
		}


		$query = $db->query("select * from {$db_prefix}url where `id`>'$id_min' and `id`<='$id_max' order by `id` asc limit 300");
		if($db->num_rows($query) < 1)
		{
			@touch($lock_file);
			jconf::update('upgrade_to_lock_version', 12);
		}

		while(false != ($row = $db->fetch($query)))
		{
			$url_key = upgrade_url_key($row['id']);

			if($url_key != $row['key'])
			{
				$db->query("UPDATE {$db_prefix}url SET `key`='{$url_key}' WHERE `id`='{$row['id']}'");
			}

			$id_max = $row['id'];
		}

		upgrade_message("[{$id_min}]请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1&id_min=$id_max");
	} else {
		upgrade_lock_file($lock_file);
	}


		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.2.2.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<22)
	{
		$row = $db->fetch_first("SELECT COUNT(1) AS `count` FROM {$db_prefix}common_district");
		if($row['count'] < 1) {
			$_file = ROOT_PATH . 'install/jishigou_data.sql';
			@$fp = fopen($_file, 'rb');
			@$sqls = fread($fp, filesize($_file));
			@fclose($fp);
			if($sqls)
			{
				$sqls = str_replace("\r", "\n", str_replace('`jishigou_',"`" . $GLOBALS['_J']['config']['db_table_prefix'], $sqls));
				$_arrs = explode(";\n", trim($sqls));
				$sql_list = array();
				foreach($_arrs as $query)
				{
					$queries = explode("\n", trim($query));
					$_sql = '';
					foreach($queries as $query)
					{
						$_sql .= (($query[0] == '#' || $query[0].$query[1] == '--') ? '' : $query);
					}

					if($_sql) $sql_list[] = $_sql;
				}
				unset($sqls);
				if($sql_list)
				{
					foreach ($sql_list as $key=>$sql)
					{
						$query=$db->query($sql,"SILENT");
					}
				}
			}
		}

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 22);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}


		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.2.3.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<23)
	{
		$copy_config_area = array();
		$copy_config_city = array();
		include(ROOT_PATH . 'install/jishigou_upgrade_area.php');
		if($copy_config_area && $copy_config_city) {
			$db->query("update {$db_prefix}members set `province`='其他' where `province`=''");
			$db->query("update {$db_prefix}members set `city`='其他' where `city`=''");
			$query = $db->query("select `uid`, `province`, `city` from {$db_prefix}members where `province`!='其他' and `city`!='其他' ");
			while (false != ($row = $db->fetch($query)))
			{
				if($row['province'] && $copy_config_area[$row['province']]){
					$db->query("update {$db_prefix}members set province = '".$copy_config_area[$row['province']]."' where `uid`='{$row[uid]}'");
					if($row['city'] && $copy_config_city[$row['city']]){
						$db->query("update {$db_prefix}members set city = '".$copy_config_city[$row['city']]."' where `uid`='{$row[uid]}'");
					}else{
						$cityofarea_arr = $db->fetch_first("select name from {$db_prefix}common_district where name = '{$copy_config_city[$row['city']]}'");
						if($cityofarea_arr){
							$cityofarea = $cityofarea_arr['name'] ? $cityofarea_arr['name'] : '其他';
						}else{
							$cityofarea = '其他';
						}
						$db->query("update {$db_prefix}members set city = '$cityofarea' where `uid`='{$row[uid]}'");
					}
					if($row['city'] == '涪陵'){
						$db->query("update {$db_prefix}members set province = '重庆市' where `uid`='{$row[uid]}'");
						$db->query("update {$db_prefix}members set city = '涪陵区' where `uid`='{$row[uid]}'");
					}
				}else{
					$db->query("update {$db_prefix}members set province = '其他' where `uid`='{$row[uid]}'");
					$db->query("update {$db_prefix}members set city = '其他' where `uid`='{$row[uid]}'");
				}
			}
		}

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 23);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.2.8.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<28)
	{
		$sql_list = array();
		$sql_list[] = "ALTER TABLE {$db_prefix}role_module CHANGE `module` `module` varchar(50) NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}role_module CHANGE `name` `name` varchar(255) NOT NULL";

		$sql_list[] = "ALTER TABLE {$db_prefix}role_action CHANGE `module` `module` varchar(50) NOT NULL default 'index'";
		$sql_list[] = "ALTER TABLE {$db_prefix}role_action CHANGE `action` `action` varchar(255) NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}role_action CHANGE `name` `name` varchar(255) NOT NULL";

		$sql_list[] = "update {$db_prefix}role set `creditslower`=20, `rank`=0 where `id`=3";
		$sql_list[] = "update {$db_prefix}role set `rank`=0 where `type`='admin'";
		$sql_list[] = "update {$db_prefix}role set `icon`='' where `icon`!=''";

				$sql_list[] = "ALTER TABLE {$db_prefix}topic CHANGE `type` `type` char(15) NOT NULL DEFAULT 'first'";
		$sql_list[] = "ALTER TABLE {$db_prefix}topic CHANGE `imageid` `imageid` char(100) NOT NULL DEFAULT ''";
		$sql_list[] = "ALTER TABLE {$db_prefix}topic_more CHANGE `replyids` `replyids` LONGTEXT NOT NULL";

				$sql_list[] = "ALTER TABLE {$db_prefix}members CHANGE `theme_id` `theme_id` CHAR(6) DEFAULT '' NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}members CHANGE `theme_bg_image_type` `theme_bg_image_type` enum('repeat','center','left','right','bottom') NOT NULL default 'repeat'";

				$sql_list[] = "ALTER TABLE {$db_prefix}url CHANGE `url` `url` text DEFAULT '' NOT NULL";

				$sql_list[] = "ALTER TABLE {$db_prefix}tag CHANGE `name` `name` CHAR(50) DEFAULT '' NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}tag_favorite CHANGE `tag` `tag` CHAR(64) DEFAULT '' NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}user_tag_fields CHANGE `tag_name` `tag_name` CHAR(64) DEFAULT '' NOT NULL";

				$sql_list[] = "ALTER TABLE {$db_prefix}share CHANGE `type` `type` char(20) NOT NULL";
		$sql_list[] = "ALTER TABLE {$db_prefix}share CHANGE `condition` `condition` text NOT NULL";


		if($sql_list)
		{
			foreach ($sql_list as $key=>$sql)
			{
				$query=$db->query($sql,"SILENT");
			}
		}


		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 28);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.2.9.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<29) {
		$upgrade_uid = max(0, (int) $_GET['upgrade_uid']);
		$upgrade_uid_max = $upgrade_uid + 200;

		if($upgrade_uid < 1) {
						
						if(true===UCENTER) {
				if(!$_GET['uc_confirm']) {
					$msg = "<div align='left'>请注意：为了保证各系统中用户名称的统一、减少用户歧义，记事狗从V3.5版本开始通过微博昵称与uc用户名对接（V3及之前版本是通过微博用户名与uc用户名对接）

所以请选择升级到最新版，对之前已整合用户昵称的处理方法：

一、将微博昵称设为与uc用户统一
【好处】减少用户歧义
【坏处】影响部分用户的习惯

二、不做处理
【好处】不影响用户习惯
【坏处】微博昵称与uc中的用户名不统一，给用户造成歧义；
比如：论坛用户A，微博昵称却设置成B；而论坛用户B，昵称设为C，则用户会把论坛的A和B混淆</div>

<a href='{$this_file}?upgrade=db&db_update=1&uc_confirm=1&uc_choose=1' onclick='return confirm(\"我已经仔细阅读上面的说明了，确认进行此操作\")'>使用第一种方法进行处理</a> <a href='{$this_file}?upgrade=db&db_update=1&uc_confirm=1&uc_choose=2' onclick='return confirm(\"我已经仔细阅读上面的说明了，确认进行此操作\")'>使用第二种方法</a>";
					upgrade_message(nl2br($msg));
				}

				if(2 == $_GET['uc_choose']) { 					$ucenter = jconf::get('ucenter');
					if($ucenter['enable'] && 'mysql'==$ucenter['uc_connect']) {
						include_once(ROOT_PATH.'./api/uc_api_db.php');

						$jsg_db = new JSG_UC_API_DB();
						$jsg_db->connect($GLOBALS['_J']['config']['db_host'],$GLOBALS['_J']['config']['db_user'],$GLOBALS['_J']['config']['db_pass'],$GLOBALS['_J']['config']['db_name'],$GLOBALS['_J']['config']['charset'],$GLOBALS['_J']['config']['db_persist'],$GLOBALS['_J']['config']['db_table_prefix']);
						$jsg_db->query("update ".TABLE_PREFIX."members set `ucuid`=0 where `ucuid`<0");

						$query = $jsg_db->query("select * from ".TABLE_PREFIX."members where ucuid=0");
						if($jsg_db->num_rows($query) > 0) {
							$uc_db = new JSG_UC_API_DB();
							$uc_db->connect($ucenter['uc_db_host'],$ucenter['uc_db_user'],$ucenter['uc_db_password'],$ucenter['uc_db_name'],$ucenter['uc_db_charset'],1,$ucenter['uc_db_table_prefix']);
							while (false != ($data = $jsg_db->fetch($query))) {
								$ucuid = -1;
								if($data['salt']) {
									$salt = $data['salt'];
									$password = $data['password'];
								} else {
									$salt = rand(100000, 999999);
									$password = md5($data['password'].$salt);
								}
								$data['username'] = addslashes($data['username']);

								$uc_user = $uc_db->fetch_first("SELECT * FROM {$ucenter['uc_db_table_prefix']}members WHERE username='{$data['username']}'");
								if(!$uc_user) {
									$uc_db->query("INSERT LOW_PRIORITY INTO {$ucenter['uc_db_table_prefix']}members SET username='{$data['username']}', `password`='$password',email='$data[email]', regip='$data[regip]', regdate='$data[regdate]', salt='$salt'", 'SILENT');
									$ucuid = $uc_db->insert_id();
									$uc_db->query("INSERT LOW_PRIORITY INTO {$ucenter['uc_db_table_prefix']}memberfields SET uid='$ucuid'",'SILENT');
								} else {
									$ucuid = $uc_user['uid'];
								}

								$ucuid = (int) $ucuid;
								$jsg_db->query("update ".TABLE_PREFIX."members set ucuid='{$ucuid}' where uid='{$data['uid']}'");
							}

							$upgrade_uid_max = $upgrade_uid + 10;
						}
					}
				} else {
					$db->query("update {$db_prefix}members set `nickname`=`username` where `ucuid`>0 and `nickname`!=`username` and `username`!=`uid`");
				}
			}
		}

		$query = $db->query("select * from {$db_prefix}members where `uid`>'$upgrade_uid' and `uid`<='$upgrade_uid_max' order by `uid` asc limit 200");
		if($db->num_rows($query) < 1) {
			$db->query("update {$db_prefix}members set `username`=`uid` where `username`=''");
			$db->query("update {$db_prefix}members set `username`=`uid` WHERE `username`!=`uid` AND `username` REGEXP '^[0-9]*$'");
			$db->query("update {$db_prefix}members set `nickname`=`username` where `nickname`=''");

			@touch($lock_file);
			jconf::update('upgrade_to_lock_version', 29);
			upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
		}

		while(false != ($row = $db->fetch($query))) {
			$_uid = $row['uid'];
			$_uname = addslashes($row['username']);
			$_nname = addslashes($row['nickname']);

						if($_uname != $_uid && $_uid > 1 && 'admin' != $row['role_type']) {				$_ret = jsg_member_checkname($_uname, 0, 0, 1);
				if($_ret < 1 && -3 != $_ret && $_uid > 1) {
					$_uname = $_uid;
					$db->query("update {$db_prefix}members set `username`='$_uname' where `uid`='$_uid'");
				}
			}

			if(!$row['invitecode']) {
				$_icode = substr(md5(random(64)), 0, 16);
				$db->query("update {$db_prefix}members set `invitecode`='$_icode' where `uid`='$_uid' and `invitecode`=''");
			}

			$upgrade_uid_max = $_uid;
		}

		upgrade_message("[{$upgrade_uid}]请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1&upgrade_uid=$upgrade_uid_max");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.3.0.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<30)
	{
		$sql = "select p.*,m.username,m.nickname from {$db_prefix}pms p
				left join {$db_prefix}members m on m.uid = p.msgtoid where p.plid < 1";
		$query = $db->query($sql);
		$pm_list = array();
		$uid_list = array();
		while (false!=($rs = $db->fetch($query))){
			$uids = '';
			$pm_list[$rs['pmid']] = $rs;
						if($rs['msgfromid'] > $rs['msgtoid']){
				$uids = $rs['msgtoid'].",".$rs['msgfromid'];
			}else{
				$uids = $rs['msgfromid'].",".$rs['msgtoid'];
			}
			$uid_list[] = $uids;
		}

		if($uid_list){
			$uid_list = array_unique($uid_list);
		}

		$plid_list = array();
		foreach ($uid_list as $val) {
						$sql = "insert into {$db_prefix}pms_index (uids) values ('$val')";
			$db->query($sql);
			$plid = mysql_insert_id();
			$plid_list[$val] = $plid;
		}

		foreach ($pm_list as $key=>$value) {
			$uids = '';
						if($value['msgfromid'] > $value['msgtoid']){
				$uids = $value['msgtoid'].",".$value['msgfromid'];
			}else{
				$uids = $value['msgfromid'].",".$value['msgtoid'];
			}

									$sql = "update {$db_prefix}pms
					set msgto = '$value[username]',
						tonickname = '$value[nickname]',
						delstatus = 0,
						plid = $plid_list[$uids]
					where pmid = '$key' ";
			$db->query($sql);
		}

				$sql="SELECT *, COUNT(*) as num
			  FROM (SELECT *
			        FROM {$db_prefix}pms
			        WHERE folder = 'inbox'
			        ORDER BY dateline DESC) lastmsg
			  GROUP BY lastmsg.plid
			  ORDER BY lastmsg.dateline DESC ";
		$query = $db->query($sql);
		while (false != ($rs = $db->fetch($query))){
			$lastmessage = addslashes(serialize($rs));
			$sql = "replace into {$db_prefix}pms_list
							(plid,uid,pmnum,dateline,lastmessage)
					values  ('$rs[plid]','$rs[msgfromid]','$rs[num]','$rs[dateline]','$lastmessage')";
			$db->query($sql);
			if($rs['msgfromid'] != $rs['msgtoid']){
				$sql = "replace into {$db_prefix}pms_list
								(plid,uid,pmnum,dateline,lastmessage)
						values  ('$rs[plid]','$rs[msgtoid]','$rs[num]','$rs[dateline]','$lastmessage')";
				$db->query($sql);
			}
			$lastmessage = '';
		}

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 30);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.3.6.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<36) {
		$sql_list = array();
		$sql_list[] = "UPDATE {$db_prefix}invite t1, {$db_prefix}members t2 SET t2.invite_uid=t1.uid WHERE t2.uid=t1.fuid AND t2.invite_uid<1 AND t1.uid>0 AND t1.fuid>0";

				$sql_list[] = "ALTER TABLE {$db_prefix}topic CHANGE `from` `from` enum('web','wap','mobile','qq','msn','api','sina','qqwb','vote','qun','event','android','iphone','ipad','sms','androidpad','fenlei','wechat','reward') NOT NULL DEFAULT 'web' COMMENT '微博来自'";
		$sql_list[] = "ALTER TABLE {$db_prefix}topic_verify CHANGE `from` `from` enum('web','wap','mobile','qq','msn','api','sina','qqwb','vote','qun','event','android','iphone','ipad','sms','androidpad','fenlei','wechat','reward') NOT NULL DEFAULT 'web' COMMENT '微博来自'";

				$sql_list[] = "ALTER TABLE {$db_prefix}sessions CHANGE `action` `action` smallint(4) unsigned NOT NULL default '0' ";

		$sql_list[] = "ALTER TABLE {$db_prefix}app CHANGE `app_key` `app_key` CHAR(32) NOT NULL default '' ";

		if($sql_list) {
			foreach ($sql_list as $key=>$sql) {
				$query=$db->query($sql, "SILENT");
			}
		}

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 36);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.3.7.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<37) {
		$sql = "SELECT id,
  uid,
  buddyid,
  COUNT(*) AS count1
FROM {$db_prefix}buddys
GROUP BY uid,buddyid
HAVING count1 > 1";
		$query = $db->query($sql);
				while(false != ($row = $db->fetch($query))) {
			$db->query("DELETE FROM {$db_prefix}buddys WHERE `id`>'{$row['id']}' AND `uid`='{$row['uid']}' AND `buddyid`='{$row['buddyid']}' ");
		}

		$sql_list = array();
		$sql_list[] = "DELETE FROM {$db_prefix}buddys WHERE `uid`=`buddyid` ";
		$sql_list[] = "ALTER TABLE {$db_prefix}buddys DROP KEY `uid_buddyid`";
		$sql_list[] = "ALTER TABLE {$db_prefix}buddys ADD UNIQUE `uid_buddyid` (`uid`, `buddyid`)";

		if($sql_list) {
			foreach ($sql_list as $key=>$sql) {
				$query=$db->query($sql, "SILENT");
			}
		}


		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 37);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.3.9.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<39) {
		$members_max_uid = max(0, (int) $_GET['members_max_uid']);
		if($members_max_uid < 1) {
			$row = $db->fetch_first("SELECT MAX(`uid`) AS `uid` FROM {$db_prefix}members ");
			$members_max_uid = $row['uid'];
		}
		if($members_max_uid > 0) {
			$per_limit = 100;
			$max_id = max(0, (int) $_GET['max_id']);
			$query = $db->query("select `uid` from {$db_prefix}members where `uid`>'{$max_id}' order by `uid` limit $per_limit ");
			if($db->num_rows($query) < 1) {
				@touch($lock_file);
				jconf::update('upgrade_to_lock_version', 39);
				upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
			} else {
								$db->query("UPDATE {$db_prefix}member_validate MV,
	  {$db_prefix}members M
	SET M.`email_checked` = '1'
	WHERE MV.`uid` > '$max_id'
		AND MV.`uid` <= '" . ($max_id + $per_limit) . "'
		AND MV.`status` = 1
	    AND MV.`verify_time` != 0
	    AND M.`uid` = MV.`uid`");

	  							while (false != ($row = $db->fetch($query))) {
					$uid = $row['uid'];
					if($max_id < $uid) {
						$max_id = $uid;
					}
					if(($db->num_rows($db->query("select * from {$db_prefix}buddy_follow_group where `uid`='{$uid}'"))) < 1) {
												$db->query("REPLACE INTO {$db_prefix}buddy_follow_group (`id`, `uid`, `name`, `count`)
						SELECT `id`, `uid`, `group_name`, `group_count` FROM {$db_prefix}group where `uid`='{$uid}' ORDER BY `id`");
												$db->query("replace into {$db_prefix}".(jtable('buddy_follow')->table_name($uid))." (`uid`, `touid`, `dateline`, `remark`)
						select `uid`, `buddyid`, `dateline`, `remark` from {$db_prefix}buddys where `uid`='{$uid}'");
												$db->query("update {$db_prefix}".(jtable('buddy_follow')->table_name($uid))." a, {$db_prefix}buddys b, {$db_prefix}buddys c
						set a.relation='3' where b.uid='{$uid}' and c.buddyid='{$uid}' and b.buddyid=c.uid and a.uid=b.uid and a.touid=b.buddyid");
												$db->query("replace into {$db_prefix}".(jtable('buddy_fans')->table_name($uid))." (`uid`, `touid`, `dateline`)
						select `buddyid`, `uid`, `dateline` from {$db_prefix}buddys where `buddyid`='{$uid}'");
												$db->query("update {$db_prefix}".(jtable('buddy_fans')->table_name($uid))." a, {$db_prefix}buddys b, {$db_prefix}buddys c
						set a.relation='3' where b.buddyid='{$uid}' and c.uid='{$uid}' and b.uid=c.buddyid and a.uid=b.buddyid and a.touid=b.uid");

						$_query = $db->query("select * from {$db_prefix}groupfields where `uid`='{$uid}'");
						if($db->num_rows($_query) > 0) {
														$db->query("replace into {$db_prefix}".(jtable('buddy_follow_group_relation')->table_name($uid))." (`uid`, `touid`, `gid`, `dateline`)
							select `uid`, `touid`, `gid`, '".time()."' from {$db_prefix}groupfields where `uid`='{$uid}'");
							$_gids = $_touids = array();
							while(false != ($_row = $db->fetch($_query))) {
																if(!isset($_gids[$_row['gid']])) {
									$_gids[$_row['gid']] = 1;
									jtable('buddy_follow_group_relation')->_update_count($uid, $_row['gid']);
								}
								if(!isset($_touids[$_row['touid']])) {
									$_touids[$_row['touid']] = 1;
									jtable('buddy_follow_group_relation')->_set_gids($uid, $_row['touid']);
								}
							}
						}
					}
				}
			}
			upgrade_message("[{$max_id}/{$members_max_uid}]请勿关闭窗口，正在升级好友关系及分组数据……",$this_file . "?upgrade=db&db_update=1&max_id={$max_id}&members_max_uid=$members_max_uid");
		}
	} else {
		upgrade_lock_file($lock_file);
	}

			if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.4.0.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<40) {
		$topic_max_tid = max(0, (int) $_GET['topic_max_tid']);
		if($topic_max_tid < 1) {
			$row = $db->fetch_first("SELECT MAX(`tid`) AS `tid` FROM {$db_prefix}topic");
			$topic_max_tid = $row['tid'];

						$db->query("ALTER TABLE {$db_prefix}topic_favorite DROP KEY `uid`", "SILENT");
			$db->query("ALTER TABLE {$db_prefix}topic_favorite ADD UNIQUE `uid-tid` (`uid`, `tid`)", "SILENT");
						$db->query("ALTER TABLE {$db_prefix}topic_mention DROP KEY `uid`", "SILENT");
			$db->query("ALTER TABLE {$db_prefix}topic_mention ADD KEY `uid-tid` (`uid`, `tid`)", "SILENT");
			$query = $db->query("SELECT id,
  uid,
  tid,
  COUNT(*) AS count1
FROM {$db_prefix}topic_mention
GROUP BY uid,tid
HAVING count1 > 1");
						while(false != ($row = $db->fetch($query))) {
				$db->query("DELETE FROM {$db_prefix}topic_mention WHERE `id`>'{$row['id']}' AND `uid`='{$row['uid']}' AND `tid`='{$row['tid']}' ");
			}
						$db->query("ALTER TABLE {$db_prefix}topic_mention DROP KEY `uid-tid`, ADD UNIQUE `uid-tid` (`uid`, `tid`)", "SILENT");
		}
		if($topic_max_tid > 0) {
			$tid_min = max(0, (int) $_GET['tid_min']);
			$row = $db->fetch_first("SELECT `tid` FROM {$db_prefix}topic WHERE `tid`>'$tid_min' LIMIT 1");
			if($row) {
				$tid_max = $tid_min + 300;

				$db->query("REPLACE INTO {$db_prefix}topic_relation (`totid`, `touid`, `tid`, `uid`, `dateline`, `type`, `digcounts`, `lastdigtime`)
		SELECT tr.tid, t1.uid, tr.replyid, t2.uid, t2.dateline, t2.type, t2.digcounts, t2.lastdigtime
		FROM {$db_prefix}topic_reply tr, {$db_prefix}topic t1, {$db_prefix}topic t2
		WHERE tr.tid>'$tid_min' AND tr.tid<='$tid_max' AND t1.tid=tr.tid AND t2.tid=tr.replyid ");

				$db->query("REPLACE INTO {$db_prefix}member_topic (`uid`, `tid`, `type`, `dateline`, `replys`, `forwards`, `lastupdate`, `digcounts`, `lastdigtime`)
		SELECT `uid`, `tid`, `type`, `dateline`, `replys`, `forwards`, `lastupdate`, `digcounts`, `lastdigtime`
		FROM {$db_prefix}topic
		WHERE `tid`>'$tid_min' AND `tid`<='$tid_max' ");

				$db->query("REPLACE INTO {$db_prefix}member_relation (`touid`, `totid`, `tid`, `uid`, `dateline`, `type`)
		SELECT `touid`, `totid`, `tid`, `uid`, `dateline`, `type`
		FROM {$db_prefix}topic
		WHERE `tid`>'$tid_min' AND `tid`<='$tid_max' AND `totid`>0 AND `touid`>0 ");

				$db->query("UPDATE {$db_prefix}topic_more tm, {$db_prefix}topic_longtext tl
				SET tm.longtext=tl.longtext
				WHERE tl.tid>'$tid_min' AND tl.tid<='$tid_max' AND tm.tid=tl.tid ");

				$db->query("UPDATE {$db_prefix}topic_mention tm, {$db_prefix}topic t SET tm.tuid=t.uid
				WHERE t.tid>'$tid_min' AND t.tid<='$tid_max' AND tm.tid=t.tid");

								upgrade_message("【{$tid_min}/{$topic_max_tid}】请勿关闭窗口，正在升级微博表数据……",$this_file . "?upgrade=db&db_update=1&tid_min=$tid_max&topic_max_tid=$topic_max_tid");
			}
		}

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 40);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.4.5.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<45) {
						$db->query("delete from {$db_prefix}role_action");
		$db->query("delete from {$db_prefix}role_module");

				upgrade_table_data();

		$db->query(" ALTER TABLE {$db_prefix}url ADD FULLTEXT `url` (`url`) ", "SILENT");
		$db->query(" ALTER TABLE {$db_prefix}share CHANGE `show_style` `show_style` TEXT NOT NULL ", "SLIENT");

				$db->query("update {$db_prefix}members SET theme_id='t1', theme_bg_color='#edece9' WHERE theme_id <> ''");

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 45);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}

		if(!file_exists(($lock_file = ROOT_PATH . 'data/upgrade/upgrade_to_0.4.6.lock')) && $GLOBALS['_J']['config']['upgrade_to_lock_version']<46) {
		$db->query(" ALTER TABLE {$db_prefix}memberfields CHANGE `authstr` `authstr` VARCHAR(50) DEFAULT '' NOT NULL ", "SILENT");

		@touch($lock_file);
		jconf::update('upgrade_to_lock_version', 46);
		upgrade_message("请勿关闭窗口，正在升级优化表结构……",$this_file . "?upgrade=db&db_update=1");
	} else {
		upgrade_lock_file($lock_file);
	}


		$db->query("update {$db_prefix}members set `username`=`uid` where `username`=''");
	$db->query("update {$db_prefix}members set `username`=`uid` WHERE `username`!=`uid` AND `username` REGEXP '^[0-9]*$'");
	$db->query("update {$db_prefix}members set `nickname`=`username` where `nickname`=''");
	$db->query("update {$db_prefix}members set `credits`=0, `extcredits1`=0, `extcredits2`=0, `extcredits3`=0, `extcredits4`=0, `extcredits5`=0, `extcredits6`=0, `extcredits7`=0, `extcredits8`=0 where `credits`>=2147480000");
	$db->query("delete from {$db_prefix}schedule where `uid`='0'");



		$tb_name_list = cache_file('get', $tb_name_list_cache_id);
	$tb_name_list_count = count($tb_name_list);
	$tb_name_id = max(0 , (int) $_GET['tb_name_id']);
	if(154 < $tb_name_list_count && $tb_name_list_count>=$tb_name_id && isset($tb_name_list[$tb_name_id]))
	{
		$_tb_repair = $_GET['tb_repair'];

		if(!$_tb_repair)
		{
			upgrade_message("文件和数据库升级已经完成，您可以<a href='{$this_file}?upgrade=db&db_update=1&tb_name_id={$tb_name_id}&tb_repair=1'>点此进行数据表优化</a>，或者<a href='{$this_file}?upgrade=done'>点此结束操作</a>");
		}
		else
		{
			$_tb_name = $tb_name_list[$tb_name_id];

			$sql="optimize table `{$_tb_name}`";
			$db->query($sql,'UNBUFFERED');

			$sql="repair table `{$_tb_name}`";
			$db->query($sql,'UNBUFFERED');


			$tb_name_id++;

			upgrade_message("[{$tb_name_id}/{$tb_name_list_count}、{$_tb_name}]正在优化表结构，也可以<a href='{$this_file}?upgrade=done'>点此结束操作</a>",$this_file . "?upgrade=db&db_update=1&tb_name_id={$tb_name_id}&tb_repair=1",1);
		}
	}


	upgrade_message("表结构升级优化完成",$this_file . "?upgrade=done");

}


function upgrade_remove_dir($dir_name)
{
	if(is_dir($dir_name) == false)Return false;
	$dir_handle = opendir($dir_name);
	while(($file = readdir($dir_handle)) !== false)
	{
		if($file != '.' and $file != "..")
		{
			if(is_dir($dir_name . '/' . $file))
			{
				upgrade_remove_dir($dir_name . '/' . $file);
			}
			if(is_file($dir_name . '/' . $file))
			{
				if(@unlink($dir_name . '/' . $file)==false)
				{
					die($dir_name . '/' . $file."删除失败");
				}
			}
		}
	}
	closedir($dir_handle);
	rmdir($dir_name);
	Return true;
}


function upgrade_message($message='',$url_forward='',$stop_time=1) {
	$this_file = basename(__FILE__);

	if(!$message) {
		@header("Location: {$url_forward}");
	} else {
		$message .= "<style> .guide td { 	border: 1px solid #1598CB; 	background-color: #FBFFE1; 	line-height: 1.2em; 	height: 28px; 	text-indent: 20px; 	color: #ccc; }  .tableborder { 	outline: 1px solid #525C3D; 	border: 0px !important; 	> border: 1px solid #525C3D !important; 	border: 1px solid #525C3D; 	empty-cells: show; 	border-collapse: separate !important; 	> border-collapse: collapse !important; 	border-collapse: collapse; }  .tableborder td { 	border-bottom: 1px solid #BBDCF1; 	line-height: 1.5em; 	height: 2em; 	padding: 4px; 	background: #FFFFFF; } .tableborder td ul, .tableborder td ul li { 	line-height: 22px; 	margin-bottom: 0px; 	margin-top: 0px; } .tableborder td img { 	margin-top: 8px; } .tableborder td .smalltxt { 	line-height: 20px; } .sub td.altbg1 { 	padding-left: 1.5em; } .category td { 	color: #333333; 	background-color: #F0F0F0; } td.altbg1, .altbg1 td { 	background: #F1F5F8; } td.altbg2, .altbg2 td { 	background: #FFFFFF; } .header td { 	background: #6780AD url(\"{$this_file}?img=bg_list.gif\") repeat-x; 	line-height: 16px; 	height: 31px !important; 	> height: 30px !important; 	height: 30px; 	font-weight: bold; 	color: #FFFFFF; 	border-bottom: 1px solid #525C3D; 	padding: 0px 8px; }  .header a{ 	color: #FFFFFF; }</style>";
		if($url_forward) {
			$message .= "<br /><br /><br /><a href=\"$url_forward\">如果您的浏览器没有自动跳转，请点击这里</a><meta http-equiv=\"refresh\" content=\"{$stop_time}; URL=$url_forward\">";
		}
		$SY = date('Y', time());
		echo "<title>消息提示  - 记事狗微博系统在线升级</title><br /><br /><br /><br /><br /><br />
		<table width=\"600\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" class=\"tableborder\">
		<tr class=\"header\"><td>消息提示</td></tr><tr><td class=\"altbg2\"><br /><div align=\"center\">
		{$message}</div><br /><br />
		</td></tr></table>
		<br /><br /><br />" .
		'<div style="clear:both;text-align:center;margin:5px auto;">Powered by <a target="_blank" href="http:/'.'/www.JishiGou.net/"><strong>JishiGou</strong></a><span> &copy; 2005 - ' . $SY . ' <a target="_blank" href="http:/'.'/www.cenwor.com/">Cenwor Inc.</a></span></div>' .
		'<div style="display:none"><script language="javascript" type="text/javascript" src="http:/'.'/js.users.51.la/439714.js"></script><noscript><a href="http:/'.'/www.51.la/?439714" target="_blank"><img alt="" src="http:/'.'/img.users.51.la/439714.asp" style="border:none" /></a></noscript></div>';
	}
	exit ;
}
function upgrade_show_img($name)
{
	$img_list = array
	(
    "bg_list.gif" => "R0lGODlhAQAeAMQAABWNuhWRwRWMuhWZzlKkxBWQvhWg1RWTxRWVyRWf1BWh2BWVxxWe0xWYyxWh11yuzhWYyhWQwBWbzhWRwxWOvBWc0RWTxFyvzhWh2RWj2hWNuxWb0BWOvhWe1AAAAAAAACH5BAAAAAAALAAAAAABAB4AAAUYIPFcAqBRXBEFk3UsCNQM0lYxXWI4CpaFADs=",
     "bg_button.gif" => "R0lGODlhAQAeAIAAALPT9AAAACH5BAAAAAAALAAAAAABAB4AAAIFhI+pawUAOw==",
	);
	$img = $img_list[$name];
	if($img === null)exit;
	$img = base64_decode($img);
	$img_size = strlen($img);
	header('Last-Modified: ' . date('r'));
	header('Accept-Ranges: bytes');
	header('Content-Length: ' . $img_size);
	header('Content-Type: image/jpeg');
	echo $img;
	exit;
}

function upgrade_check_qqwb_env() {
	jfunc('qqwb_env');

	return qqwb_env();
}

function upgrade_check_sina_env() {
	jfunc('sina_env');

	return sina_env();
}

function upgrade_url_key($id,$op="ENCODE") {
	$index = 'z6OmlGsC9xqLPpN7iw8UDAb4HIBXfgEjJnrKZSeuV2Rt3yFcMWhakQT1oY5v0d';
	$base = 62;

	$out = "";
	if('ENCODE' == $op) {
		for ( $t = floor( log10( $id ) / log10( $base ) ); $t >= 0; $t-- ) {
			$a = floor( $id / pow( $base, $t ) );
			$out = $out . substr( $index, $a, 1 );
			$id = $id - ( $a * pow( $base, $t ) );
		}
	} elseif ('DECODE' == $op) {
		;
	}

	return $out;
}

function upgrade_table_fields($str)
{
	if(false !== strpos($str, '/'.'*')) {
		$str = preg_replace('~\/\*.*?\*\/~s', '', $str);
	}
	$str = trim($str);
	if(!$str)
	{
		return false;
	}

	$_tmps = explode("\n",$str);
	$_tmps_count = count($_tmps);
	unset($_tmps[0],$_tmps[$_tmps_count - 1]);

	$tbfs = array();
	foreach ($_tmps as $_tmp)
	{
		$_tmp = trim($_tmp);
		$_tmp = trim($_tmp,',');
		if(false!==strpos($_tmp,'KEY '))
		{
			$_p = (strpos($_tmp,'KEY ') + 4);
			$_f = trim(substr($_tmp,0,$_p));
			$_f = trim($_f,'`');
			$_f = trim($_f,',');
			$_f = trim($_f,'`');
			$_f = trim($_f,'`,');
			$_tmp = trim(substr($_tmp,$_p));
			$_tmp = trim($_tmp,',');
			$_t = trim(substr($_tmp,0,strpos($_tmp,' ')));
			$_t = trim($_t,'`');
			$_t = trim($_t,',');
			$_t = trim($_t,'`');
			$_t = trim($_t,'`,');
			$_f .= ' - ' . $_t;
		}
		else
		{
			$_t = trim(substr($_tmp,0,strpos($_tmp,' ')));
			$_t = trim($_t,'`');
			$_t = trim($_t,',');
			$_t = trim($_t,'`');
			$_t = trim($_t,'`,');
			$_f = $_t;
		}
		$tbfs[$_f] = $_tmp;
	}

	return $tbfs;
}

function upgrade_table_data()
{
	global $db, $db_prefix;

	$db_tb_datas = array();
	include(ROOT_PATH . 'install/db_tb_datas.php');

	if($db_tb_datas)
	{
		$updates = array();

		foreach($db_tb_datas as $_tb=>$v1)
		{
			$tb = TABLE_PREFIX . $_tb;

			$row = $db->fetch_first("select count(*) as `count` from $tb");
			if($row && $row['count'] != count($v1['datas']))
			{
				$key = $v1['key'];
				foreach($v1['datas'] as $v2)
				{
					if($v2[$key] && !($db->fetch_first("select * from $tb where `$key`='".$v2[$key]."'")))
					{
						foreach($v2 as $v2k=>$v2v)
						{
							$v2[$v2k] = is_numeric($v2v) ? $v2v : addslashes($v2v);
						}
						$db->query("replace into $tb (`".implode("`,`",array_keys($v2))."`) values ('".implode("','",$v2)."')");

						$updates[$_tb] = 1;
					}
				}
			}
		}

				if($updates && isset($updates['role_action'])) {
			jtable('role')->copy('role_' . date('YmdHis'), 1);
			foreach($db_tb_datas['role']['datas'] as $r) {
				$db->query("update {$db_prefix}role set `privilege`='{$r['privilege']}' where `id`='{$r['id']}' and `name`='{$r['name']}'");
			}
		}

				$guest_roles = $db_tb_datas['role']['datas'][1];
		if(1 == $guest_roles['id']) {
			$db->query("replace into {$db_prefix}role(`".implode("`,`", array_keys($guest_roles))."`) values('".implode("','", $guest_roles)."')");
		}
	}
}

function upgrade_lock_file($f) {
	clearstatcache();
	if(!file_exists($f)) {
		@touch($f);
	}
	clearstatcache();
	if(!file_exists($f)) {
		upgrade_message("文件 $f 创建失败，请检查相应目录的权限。");
	}
	@chmod($f, 0644);
}



class upgrade_dbstuff {
	var $querynum = 0;
	var $link;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE) {
		if($pconnect) {
			if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		}

		if($this->version() > '4.1') {


			@mysql_query("SET character_set_connection=". $GLOBALS['_J']['config']['db_charset'].", character_set_results=". $GLOBALS['_J']['config']['db_charset'].", character_set_client=binary", $this->link);

			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}

		if($dbname) {
			$this->select_db($dbname);
		}

	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function fetch($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_first($sql,$type = '')
	{
		$query = $this->query($sql,$type);

		if($query)
		{
			return $this->fetch($query);
		}
		else
		{
			return false;
		}
	}

	function query($sql, $type = '') {
		global $debug, $sqldebug, $sqlspenttimes;
		
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();
				$this->connect($GLOBALS['_J']['config']['db_host'], $GLOBALS['_J']['config']['db_user'], $GLOBALS['_J']['config']['db_pass'], $GLOBALS['_J']['config']['db_name'], $GLOBALS['_J']['config']['db_persist']);
				$this->query($sql, 'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				$this->halt('MySQL Query Error', $sql);
			}
		}

		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function version() {
		return mysql_get_server_info($this->link);
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($msg = '', $sql = '') {
		echo('<br>JishiGou Upgrade Error : <br>'.$msg."<br>".$sql.'<br><hr><br>');
		jlog('upgrade', "\r\n Mysql Query Error \r\n $msg \r\n $sql \r\n\r\n", 0);
		if(true === DEBUG) {
			exit;
		}
	}
}
?>