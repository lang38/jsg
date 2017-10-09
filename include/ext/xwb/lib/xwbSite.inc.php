<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwbSite.inc.php $
 *
 * @Author 狐狸<foxis@qq.com> $
 *
 * @version $Id: xwbSite.inc.php 3699 2013-05-27 07:26:39Z wuliyong $
 */

if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}


/// 在附属站点中登录
function xwb_setSiteUserLogin($uid)
{
    $uid = (int) $uid;
    if ($uid < 1) {
    	return false;
    }

	/**
	 * 设置Cookie进行登录
	 */
	$member = jsg_member_login_set_status($uid);

   	/**
   	 * 存入全局 后面会调用
   	 */
   	$GLOBALS['_J']['config']['login_user'] = $member;

    return $member;
}

/// 在附属站点中注册一个用户
function xwb_setSiteRegister($nickname, $email, $pwd=false)
{
    $db = XWB_plugin::getDB();

    $uid = 0;
    $password = ($pwd ? $pwd : rand(100000,999999));

	$regstatus = jsg_member_register_check_status();

	if($regstatus['normal_enable'] || true===JISHIGOU_FORCED_REGISTER)
	{
		$uid = jsg_member_register($nickname, $password, $email);
	}

    $rst = array('uid'=>$uid, 'password'=>$password);

    return $rst;
}
