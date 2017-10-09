<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename newtopic.hack.php $
 *
 * @Author 狐狸<foxis@qq.com> $
 *
 * @version $Id: newtopic.hack.php 3699 2013-05-27 07:26:39Z wuliyong $
 */

if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}

if (XWB_plugin::isUserBinded() && XWB_plugin::V('p:syn_to_sina')) {
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	$xp_publish->topic( (int) ($tid ? $tid : $GLOBALS['jsg_tid']), (int) ($totid ? $totid : $GLOBALS['jsg_totid']), (string) $GLOBALS['jsg_message'], (string) $GLOBALS['jsg_imageid'] );
}
