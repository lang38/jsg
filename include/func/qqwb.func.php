<?php
/**
 * 文件名：qqwb.func.php
 * @version $Id: qqwb.func.php 5676 2014-05-09 09:20:56Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 腾讯微博接口函数
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function qqwb_enable($sys_config = array())
{
	if(!$sys_config)
	{
		$sys_config = jconf::get();
	}

	if(!$sys_config['qqwb_enable'])
	{
		return false;
	}

	if(!$sys_config['qqwb'])
	{
		$sys_config['qqwb'] = jconf::get('qqwb');
	}

	return $sys_config;
}


function qqwb_login($ico='s')
{
	$return = '';

	if (($sys_config = qqwb_enable()) && $sys_config['qqwb']['is_account_binding'])
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/qqwb/login16.png',
			'm' => $sys_config['site_url'] . '/images/qqwb/login24.gif',
			'b' => $sys_config['site_url'] . '/images/qqwb/login.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];

		$return = '<a class="qqweiboLogin" href="#" onclick="window.location.href=\''.$sys_config['site_url'].'/index.php?mod=qqwb&code=login\';return false;"><img src="'.$img_src.'" /></a>';
	}

	return $return;
}

function qqwb_bind($uid=0)
{
	$bind_info = qqwb_bind_info($uid);

	return ($bind_info && $bind_info['qqwb_username'] && $bind_info['openid'] && $bind_info['access_token']);
}
function qqwb_has_bind($uid=0)
{
	return qqwb_bind($uid);
}
function qqwb_synctoqq($uid=0)
{
	$return = true;

	$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));

	if($row)
	{
		$return = $row['synctoqq'];
	}

	return $return;
}
function qqwb_syncweibo_tojishigou($uid=0) {
	if(qqwb_init() && $GLOBALS['_J']['config']['qqwb']['is_synctopic_tojishigou']) {
		$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));
		if($row && $row['uid'] && qqwb_has_bind($row['uid']) && $row['sync_weibo_to_jishigou'] &&
		($row['last_read_time'] + $GLOBALS['_J']['config']['qqwb']['syncweibo_tojishigou_time']) < TIMESTAMP) {
			return true;
		}
	}
	return false;
}
function qqwb_syncreply_tojishigou($uid=0) {
	$row = (is_array($uid) ? $uid : qqwb_bind_info((int) $uid));
	if($row && $row['uid'] && qqwb_has_bind($row['uid']) && $row['sync_reply_to_jishigou']) {
		return true;
	}
	return false;
}

function qqwb_bind_info($uid=0) {
	$ret = array();
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	if($uid > 0) {
		if(false===($ret=jclass('misc')->account_bind_info($uid, 'qqwb'))) {
			$ret = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_info where `uid`='{$uid}'");

			jclass('misc')->update_account_bind_info($uid, 'qqwb', $ret);
		}
	}
	if(false===$ret[0]) {
		return array();
	} else {
		return $ret;
	}
}

function qqwb_bind_topic($tid) {
	static $sQQWB_bind_topics=null;
	$return = array();
	$tid = max(0,(int) $tid);
	if($tid > 0) {
		if(null===($return = $sQQWB_bind_topics[$tid])) {
			$return = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_topic where `tid`='{$tid}'");
			$sQQWB_bind_topics[$tid] = $return;
		}
	}
	return $return;
}


function qqwb_bind_icon($uid=0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = qqwb_enable()))
	{

		$return = "<img src='{$sys_config['site_url']}/images/qqwb/qqwb_off.gif' alt='未绑定腾讯微博' />";

		if (qqwb_bind($uid))
		{
			$return = "<img src='{$sys_config['site_url']}/images/qqwb/qqwb_on.gif' alt='已经绑定腾讯微博' />";

			if($sys_config['qqwb']['is_synctopic_tojishigou'] && qqwb_syncweibo_tojishigou($uid)) {
				$return .= "<img src='{$sys_config['site_url']}/index.php?mod=qqwb&code=sync_weibo&uid={$uid}' width='0' height='0' style='display:none' />";
			}
			if($sys_config['qqwb']['is_syncreply_tojishigou'] && 'topic' == jget('mod') && is_numeric(jget('code')) &&
			($tid = jget('code')) > 0 && ($qbt = qqwb_bind_topic($tid)) &&
			($qbt['last_read_time'] + $sys_config['qqwb']['syncweibo_tojishigou_time']) < TIMESTAMP) {
				$return .= "<img src='{$sys_config['site_url']}/index.php?mod=qqwb&code=sync_reply&tid={$tid}' width='0' height='0' style='display:none' />";
			}
		}

		if (MEMBER_ID>0)
		{
			$return = "<a href='#' title='腾讯微博绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=qqwb';return false;\">{$return}</a>";
		}
	}

	return $return;
}


function qqwb_syn()
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = qqwb_enable()) && (jconf::get('qqwb','is_synctopic_toweibo')))
	{
		$row = qqwb_bind_info($uid);

		$a = $b = $c = $e = '';
		if ($row && $row['qqwb_username'])
		{
			$b = "{$sys_config['site_url']}/images/qqwb/icon_on.gif";

			if((true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_AJAX || true === IN_JISHIGOU_ADMIN) && 'output'!=jget('mod')) {
	            $dataSetting = 0;
				if (!($row['synctoqq'])) {
	                $dataSetting = 1;
					$b = "{$sys_config['site_url']}/images/qqwb/icon_off.gif";
				}
				$e = "<i></i><img id='syn_to_qqwb' src='{$b}' data-setting='{$dataSetting}' data-type='qq' onclick='modifySync(this);' title='同步发到腾讯微博'/>";
			} else {
								$e = '<label><input type="checkbox" name="syn_to_qqwb" value="1" '.($row['synctoqq'] ? ' checked="checked" ' : '').' />
					<img src="'.$b.'" title="同步发到腾讯微博" /></label>';
			}
		}
		else
		{
						$b = "{$sys_config['site_url']}/images/qqwb/icon_off.gif";
						$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=qqwb' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}' title='同步发到腾讯微博'/></a>";
		}

		$return = "{$a}{$e}";
	}

	return $return;
}

function qqwb_sync_face($uid, $face='') {
	$uid = max(0, (int) $uid);
	if($uid < 1) return 0;
	$user_info = jsg_member_info($uid);
	if(!$user_info || $user_info['__face__']) {
		return 0;
	}

	$face = trim(strip_tags($face));
	if(false === strpos($face, ':/'.'/')) {
		return 0;
	}

	
	$p = array(
		'uid' => $uid,
		'pic_url' => $face,
	);
	jlogic('user')->face($p, 0);

	return 0;
}

function qqwb_oauth($access_token = null, $openid = null) {
	$oauth = null;

	$sys_config = qqwb_enable();
	if($sys_config) {
		$client_id = $sys_config['qqwb']['app_key'];
		$client_secret = $sys_config['qqwb']['app_secret'];

		jext('qqwb/qqwbOAuth', 'qqwbOAuth');
		$oauth = new qqwbOAuth();
		$oauth->init($client_id, $client_secret, $access_token, $openid);
	}

	return $oauth;
}

function qqwb_api($command, $params = array(), $method = 'GET', $oauth = null, $multi = false) {
	$oauth = ($oauth ? $oauth : qqwb_oauth());
	if($oauth) {
		return $oauth->api($command, $params, $method, $multi);
	}
}
