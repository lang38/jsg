<?php
/**
 * 文件名：sina.func.php
 * 作     者：狐狸<foxis@qq.com>
 * @version $Id: sina.func.php 4095 2013-08-08 02:09:43Z yupengfei $
 * 功能描述: 新浪微博接口函数
 * @version $Id: sina.func.php 4095 2013-08-08 02:09:43Z yupengfei $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function sina_enable($sys_config = array())
{
	return sina_weibo_enable($sys_config);
}
function sina_weibo_enable($sys_config = array())
{
	if(!$sys_config)
	{
		$sys_config = jconf::get();
	}

	if(!$sys_config['sina_enable'])
	{
		return false;
	}

	if(!$sys_config['sina'])
	{
		$sys_config['sina'] = jconf::get('sina');
	}

	return $sys_config;
}


function sina_weibo_login($ico='s')
{
	$return = '';

	if (($sys_config = sina_weibo_enable()) && $sys_config['sina']['is_account_binding'])
	{
		$icos = array
		(
			's' => $sys_config['site_url'] . '/images/xwb/bgimg/loginHeader_16.png',
			'm' => $sys_config['site_url'] . '/images/xwb/bgimg/loginHeader_24.png',
			'b' => $sys_config['site_url'] . '/images/xwb/bgimg/sina_login_btn.gif',
		);
		$ico = (isset($icos[$ico]) ? $ico : 's');
		$img_src = $icos[$ico];

		$login_url = $sys_config['site_url'] . '/index.php?mod=xwb&' . ($sys_config['sina']['oauth2_enable'] ? 'code=login' : 'm=xwbAuth.login');
		$return = '<a class="sinaweiboLogin" href="#" onclick="window.location.href=\''.$login_url.'\';return false;"><img src="'.$img_src.' " style="0;"/></a>';
	}

	return $return;
}

function sina_weibo_bind($uid=0, $token_check=0)
{
	$ret = false;
	$bind_info = sina_weibo_bind_info($uid);
	if($bind_info && $bind_info['sina_uid'] > 0) {
		$ret = ($token_check ? sina_weibo_token_check($uid) : true);
	}
	return $ret;
}
function sina_weibo_has_bind($uid=0, $token_check=0)
{
	return sina_weibo_bind($uid, $token_check);
}

function sina_weibo_bind_setting($uid=0)
{
	$ret = true;
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	if(isset($row['profiles']['bind_setting']) && !$row['profiles']['bind_setting']) {
		$ret = false;
	}
	return $ret;
}
function sina_weibo_synctopic_tojishigou($uid=0)
{
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	return $row['profiles']['synctopic_tojishigou'] ? true : false;
}
function sina_weibo_syncreply_tojishigou($uid=0)
{
	$row = (is_array($uid) ? $uid : sina_weibo_bind_info((int) $uid));
	return $row['profiles']['syncreply_tojishigou'] ? true : false;
}

function sina_weibo_bind_info($uid=0) {
	$ret = array();
	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));
	if($uid > 0) {
		if(false===($ret=jclass('misc')->account_bind_info($uid, 'xwb'))) {
			$ret = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_info where `uid`='{$uid}'");
			if($ret['profile']) {
				$ret['profiles'] = json_decode($ret['profile'], true);
			}

			jclass('misc')->update_account_bind_info($uid, 'xwb', $ret);
		}
	}
	if(false===$ret[0]) {
		return array();
	} else {
		return $ret;
	}
}
function sina_weibo_bind_topic($tid)
{
	static $sXWB_bind_topics=null;

	$return = array();

	$tid = max(0,(int) $tid);

	if($tid > 0)
	{
		if(null===($return = $sXWB_bind_topics[$tid]))
		{
			$return = DB::fetch_first("select * from ".TABLE_PREFIX."xwb_bind_topic where `tid`='{$tid}'");

			$sXWB_bind_topics[$tid] = $return;
		}
	}

	return $return;
}


function sina_weibo_bind_icon($uid=0)
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = sina_weibo_enable()))
	{

		$return = "<img src='{$sys_config['site_url']}/images/xwb/bgimg/sinawebo_off.gif' alt='未绑定新浪微博' />";

		if (sina_weibo_bind($uid, !(rand(0, 10)))) {
			$return = "<img src='{$sys_config['site_url']}/images/xwb/bgimg/sinawebo_on.gif' alt='已经绑定新浪微博' />";

			if($sys_config['sina']['is_synctopic_tojishigou'] && sina_weibo_synctopic_tojishigou($uid)) {
				$_read_now = true;

				if($sys_config['sina']['syncweibo_tojishigou_time'] > 0)
				{
					$xwb_bind_info = sina_weibo_bind_info($uid);

					if($xwb_bind_info['last_read_time'] + $sys_config['sina']['syncweibo_tojishigou_time'] > time())
					{
						$_read_now = false;
					}
				}

				if($_read_now && !(jaccess('xwb','__synctopic',$uid)))
				{
					$_read_now = false;
				}

				if($_read_now)
				{
					$return .= "<img src='{$sys_config['site_url']}/index.php?mod=xwb&code=synctopic&uid={$uid}' width='0' height='0' style='display:none' />";
				}
			}

			if($sys_config['sina']['is_syncreply_tojishigou'] && is_numeric($_GET['code']) &&
				sina_weibo_syncreply_tojishigou($uid) && ($xwb_bind_topic = sina_weibo_bind_topic($_GET['code'])) &&
				($topic_info = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='{$_GET['code']}'"))) {
				$_read_now = true;

				if($sys_config['sina']['syncweibo_tojishigou_time'] > 0)
				{
					if($xwb_bind_topic['last_read_time'] + $sys_config['sina']['syncweibo_tojishigou_time'] > time())
					{
						$_read_now = false;
					}
				}

				if($_read_now && !(jaccess('xwb','__syncreply',$topic_info['uid'])))
				{
					$_read_now = false;
				}

				if($_read_now)
				{
					$return .= "<img src='{$sys_config['site_url']}/index.php?mod=xwb&code=syncreply&tid={$_GET['code']}' width='0' height='0' style='display:none' />";
				}
			}
		}

		if (MEMBER_ID>0)
		{
			$return = "<a href='#' title='新浪微博绑定设置' onclick=\"window.location.href='{$sys_config['site_url']}/index.php?mod=account&code=sina';return false;\">{$return}</a>";
		}
	}

	return $return;
}


function sina_weibo_syn()
{
	$return = '';

	$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

	if ($uid > 0 && ($sys_config = sina_weibo_enable()) && (jconf::get('sina','is_synctopic_toweibo')))
	{
		$row = sina_weibo_bind_info($uid);

		$a = $b = $c = $d = $e = '';
		if ($row && $row['sina_uid'])
		{
			$b = "{$sys_config['site_url']}/images/xwb/bgimg/icon_on.gif";

			if((true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_AJAX || true === IN_JISHIGOU_ADMIN) && 'output'!=jget('mod')) {
				$d = "checked='checked'";
	            $dataSetting = 0;
				if (!sina_weibo_bind_setting($row))
				{
	                $dataSetting = 1;
					$b = "{$sys_config['site_url']}/images/xwb/bgimg/icon_off.gif";
				}
				$e = "<i></i><img id='syn_to_sina' src='{$b}' data-setting='{$dataSetting}' data-type='sina' onclick='modifySync(this);' title='同步发到新浪微博'/>";
			} else {
								$e = '<label><input type="checkbox" name="syn_to_sina" value="1" '.(sina_weibo_bind_setting($row) ? ' checked="checked" ' : '').' />
					<img src="'.$b.'" title="同步发到新浪微博" /></label>';
			}
		}
		else
		{
						$b = "{$sys_config['site_url']}/images/xwb/bgimg/icon_off.gif";
			$c = "disabled='disabled'";
			$e = "<a href='{$sys_config['site_url']}/index.php?mod=account&code=sina' title='开通此功能（将打开新窗口）'><i></i><img src='{$b}'  data-setting='1' title='同步发到新浪微博'/></a>";
		}

		$return = "{$a}{$e}";
	}

	return $return;
}


function sina_weibo_share($tid='')
{
	$return = '';

	if(($sys_config = sina_weibo_enable()) && (jconf::get('sina','is_rebutton_display')))
	{
		$tid = max(0,(int) ($tid ? $tid : $GLOBALS['jsg_tid']));

		$uid = max(0,(int) ($uid ? $uid : MEMBER_ID));

		$link = "javascript:void((function(s,d,e,r,l,p,t,z,c) {var%20f='http:/"."/v.t.sina.com.cn/share/share.php?appkey={$sys_config[sina][app_key]}',u=z||d.location,p=['&url=',e(u),'& title=',e(t||d.title),'&source=',e(r),'&sourceUrl=',e(l),'& content=',c||'gb2312','&pic=',e(p||'')].join('');function%20a() {if(!window.open([f,p].join(''),'mb', ['toolbar=0,status=0,resizable=1,width=440,height=430,left=',(s.width- 440)/2,',top=',(s.height-430)/2].join('')))u.href=[f,p].join('');}; if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else%20a();}) (screen,document,encodeURIComponent,'','','','','',''));";
		if ($uid > 0 && $tid > 0)
		{
			if (sina_weibo_bind($uid))
			{
				$link = "{$sys_config['site_url']}/index.php?mod=xwb&m=xwbSiteInterface.share&tid={$tid}";
				$link = "javascript:void( window.open('". urlencode($link). "', '', 'toolbar=0,status=0,resizable=1,width=680,height=500') );";
			}
		}

		$return = ' | <a title="转发到新浪微博" href="'.$link.'" id="sina_weibo_share"><img src="'.$sys_config['site_url'].'/images/xwb/bgimg/icon_logo.png" /></a>';
	}

	return $return;
}


function sina_weibo_oauth($access_token = null, $refresh_token = null) {
	$oauth = null;

	$sys_config = sina_weibo_enable();
	if($sys_config) {
		$client_id = $sys_config['sina']['app_key'];
		$client_secret = $sys_config['sina']['app_secret'];

		$oauth = jclass('jishigou_oauth2_client');
		$oauth->init($client_id, $client_secret, $access_token, $refresh_token);
		$oauth->host = 'https:/'.'/api.weibo.com/';
		$oauth->access_token_url = 'https:/'.'/api.weibo.com/oauth2/access_token';
		$oauth->authorize_url = 'https:/'.'/api.weibo.com/oauth2/authorize';
	}

	return $oauth;
}

function sina_weibo_api($url, $p, $method='POST', $oauth=null, $mutli=false) {
	$ret = '';

	$oauth = ($oauth ? $oauth : sina_weibo_oauth());
	if($oauth) {
		if('POST' == $method) {
			$ret = $oauth->post($url, $p, $mutli);
		} else {
			$ret = $oauth->get($url, $p);
		}
	}

	return $ret;
}

function sina_weibo_substr($str, $length) {
	$str = trim(strip_tags($str));
		if( strlen($str) > $length + 600 ){
		$str = substr($str, 0, $length + 600);
	}

	$p = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
	preg_match_all($p,$str,$o);
	$size = sizeof($o[0]);
	$count = 0;
	for ($i=0; $i<$size; $i++) {
		if (strlen($o[0][$i]) > 1) {
			$count += 1;
		} else {
			$count += 0.5;
		}

		if ($count  > $length) {
			$i-=1;
			break;
		}

	}
	return implode('', array_slice($o[0],0, $i));
}

function sina_weibo_sync_face($uid, $face='') {
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
		'pic_url' => $face
	);
	jlogic('user')->face($p, 0);

	return 0;
}


function sina_weibo_token_check($uid) {
	global $_J;
	$ret = false;
	if($_J['config']['sina_enable'] && $uid) {
		$bind_info = (is_array($uid) ? $uid : DB::fetch_first("select * from ".DB::table('xwb_bind_info')." where `uid`='$uid'"));
		if($bind_info && $bind_info['sina_uid'] > 0) {
			if($_J['config']['sina']) {
				if($_J['config']['sina']['oauth2_enable']) {
					if($bind_info['access_token']) {
						$expire_time = $bind_info['dateline'] + $bind_info['expires_in'];
						$pm_time = $bind_info['last_pm_time'];
						$t = $expire_time - TIMESTAMP;
						$ret = ($t > 0 ? true : false);
						if($t < 86400 && $pm_time < $bind_info['dateline'] && (($pm_time + 86400) < TIMESTAMP)) {
							DB::query("update ".DB::table('xwb_bind_info')." set `last_pm_time`='".TIMESTAMP."' where `uid`='$uid'");
							$member = jsg_member_info($uid);
							if($member) {
								$pm_post = array(
									'message' => "您的新浪微博授权 ACCESS_TOKEN 即将到期，
										到期后将不能正常使用部分功能（比如，同步微博到新浪、从新浪同步内容到我的微博等），
										请<a target=_blank href={$_J['config']['site_url']}/index.php?mod=xwb&code=login&forcelogin=1><b>点此重新登录一次新浪微博以延续授权的使用</b></a>",
									'to_user' => $member['nickname'],
								);
																$admin_info = jsg_member_info(1);
								jlogic('pm')->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
							}
						}
					}
				} else {
					$ret = ($bind_info['token'] && $bind_info['tsecret']);
				}
			}
		}
	}
	return $ret;
}

?>