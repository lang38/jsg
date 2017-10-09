<?php
/**
 * 插件程序启动文件  For Discuz
 * @author yaoying
 * @author junxiong
 * @copyright [JishiGou] (C)2005 - 2099 Cenwor Inc.
 * @version $Id: sina.php 3699 2013-05-27 07:26:39Z wuliyong $
 *
 */


//插件常量定义
if(!defined("ROOT_PATH"))
{
	define("ROOT_PATH" , substr(dirname(__FILE__),0,-15) . "/");
}
//-----------------------------------------------------------------------
// 是否在插件程序中的标识
define('IS_IN_XWB_PLUGIN',      true);
define('XWB_P_PROJECT', 	   'xwb4jsg');
define('XWB_P_VERSION',		   '1.0.0');
define('XWB_P_INFO_API',	   'http:/'.'/x.weibo.com/service/stdVersion.php?p='. XWB_P_PROJECT. '&v='. XWB_P_VERSION );
define('XWB_P_STAT_DISABLE',    true);
//-----------------------------------------------------------------------

// 路径配置相关
define('XWB_P_ROOT',			dirname(__FILE__) );
define('XWB_P_DIR_NAME',		'include/ext/xwb' );
define('XWB_P_DATA',			XWB_P_ROOT. DIRECTORY_SEPARATOR. 'log' );
//-----------------------------------------------------------------------
// XWB 所用的SESSION数据存储变量名
define('XWB_CLIENT_SESSION',	'XWB_P_SESSION');

//-----------------------------------------------------------------------
//获取模块路由的变量名
define('XWB_R_GET_VAR_NAME',	'm');
//默认路由
define('XWB_R_DEF_MOD',			'test');
//默认路由方法
define('XWB_R_DEF_MOD_FUNC',	'default_action');

//XWB全局数据存储变量名
define('XWB_SITE_GLOBAL_V_NAME','XWB_SITE_GLOBAL_V_NAME');

//-----------------------------------------------------------------------
// 微博 api url
define('XWB_API_URL', 	'http:/'.'/api.t.sina.com.cn/');
// 微博API使用的字符集，大写 如果是UTF-8 则表示为  UTF-8
define('XWB_API_CHARSET',		'UTF8');


//-----------------------------------------------------------------------
//插件所服务的站点根目录。这是本文件唯一出现"S"类别的常量
define('XWB_S_ROOT',	ROOT_PATH);


//插件通用库引入
require_once XWB_P_ROOT.'/lib/compat.inc.php';//常用函数的替代
require_once XWB_P_ROOT.'/lib/core.class.php';

//现阶段在本插件中主要用于存储db类。
//要注意附属站点的环境可能会会覆盖这个变量里面的内容
//$GLOBALS[XWB_SITE_GLOBAL_V_NAME]	=  array();

/// 引入附属站点的环境
require_once XWB_P_ROOT . '/jishigou.env.php';

//从common.cfg.php移出的内容
//要注意附属站点的环境可能会会覆盖这个变量里面的内容
session_start();
if ( !isset($_SESSION[XWB_CLIENT_SESSION]) ){
	$_SESSION[XWB_CLIENT_SESSION]= array();
}



//插件所需数据和php4兼容方案安全性初始化
$GLOBALS['__CLASS'] = array();
$GLOBALS['xwb_tips_type'] = '' ;

//初始单例化一个client user用户
$sess = XWB_plugin::getUser();
if ( !defined('IN_XWB_INSTALL_ENV') ){

	if( defined('XWB_S_UID') &&  XWB_S_UID ){
		$bInfo = XWB_plugin::getBindInfo ();
		if (!empty ($bInfo) && is_array ($bInfo)) {
			$keys = array ('oauth_token' => $bInfo ['token'], 'oauth_token_secret' => $bInfo ['tsecret'] );
			$sess->setInfo( 'sina_uid', $bInfo ['sina_uid'] );
			$sess->setOAuthKey( $keys, true );
		}
	}

	$GLOBALS['xwb_tips_type']  = $sess->getInfo('xwb_tips_type');
	if( $GLOBALS['xwb_tips_type'] ){
		$sess->delInfo('xwb_tips_type');
		setcookie ('xwb_tips_type', '', time () - 3600);
	}

	$xwb_token = $sess->getToken ();
	if ( empty($xwb_token) ) {
		$sess->clearToken ();
		setcookie ('xwb_tips_type', '', time () - 3600);
	}
}