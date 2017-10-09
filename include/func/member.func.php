<?php
/**
 *
 * 用户注册登录函数
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member.func.php 5555 2014-02-19 03:16:03Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}



function jsg_member_register($nickname, $password, $email, $username = '', $ucuid = 0, $role_id = 0) {

	return jclass('passport')->register($nickname, $password, $email, $username, $ucuid, $role_id);
}
function jsg_member_register_check_invite($invite_code='', $reset=0) {
	return jclass('passport')->register_check_invite($invite_code, $reset);
}
function jsg_member_register_by_invite($invite_uid, $uid=MEMBER_ID, $check_result=array()) {
	return jclass('passport')->register_by_invite($invite_uid, $uid, $check_result);
}
function jsg_member_register_check_status() {
	$rets = array();
	if($GLOBALS['_J']['config']['ldap_enable']){
		$rets['error'] = '网站启用AD域帐号登录，禁止用户注册';
	}else{
		if($GLOBALS['_J']['config']['regstatus']) {
			foreach($GLOBALS['_J']['config']['regstatus'] as $v) {
				$rets["{$v}_enable"] = 1;
			}
		}
				if(!$rets && true!==JISHIGOU_FORCED_REGISTER) {
			$msg = '本站暂时关闭了普通注册功能 ';
			$msg .= jsg_member_third_party_reg_msg();

			$rets['error'] = ($GLOBALS['_J']['config']['regclosemessage'] ? $GLOBALS['_J']['config']['regclosemessage'] : $msg);
		}
	}
	return $rets;
}

function jsg_member_register_is_closed() {
	$rets = jsg_member_register_check_status();
	return ($rets['error'] ? true : false);
}

function jsg_member_third_party_reg_msg() {
	$msg = '';
	if($GLOBALS['_J']['config']['third_party_regstatus']) {
		$msg .= '您也可以通过以下的第三方帐号进行登陆：<br /><br />';
		if(in_array('sina', $GLOBALS['_J']['config']['third_party_regstatus']) && sina_weibo_init()) {
			$msg .= sina_weibo_login('b') . '<br /><br />';
		}
		if(in_array('qqwb', $GLOBALS['_J']['config']['third_party_regstatus']) && qqwb_init()) {
			$msg .= qqwb_login('b') . '<br /><br />';
		}
	}
	return $msg;
}


function jsg_member_login($nickname, $password, $is = '') {
	return $GLOBALS['_J']['config']['ldap_enable'] ? jclass('ldap')->login($nickname, $password) : jclass('passport')->login($nickname, $password, $is);
}


function jsg_member_login_check($nickname, $password, $is = '', $checkip = 1) {
	return jclass('passport')->login_check($nickname, $password, $is, $checkip);
}


function jsg_member_login_set_status($member) {
	return jclass('passport')->login_set_status($member);
}

function jsg_member_logout() {
	return jclass('passport')->logout();
}


function jsg_member_login_extract() {
	return jclass('passport')->login_extract();
}


function jsg_member_checkname($username, $is_nickname = 0, $ucuid = 0, $check_exists = -1) {
	return jclass('passport')->checkname($username, $is_nickname, $ucuid, $check_exists);
}



function jsg_member_checkemail($email, $ucuid = 0) {
	return jclass('passport')->checkemail($email, $ucuid);
}


function jsg_member_delete($ids) {
	return jclass('passport')->delete($ids);
}


function jsg_member_edit($oldnickname, $oldpw='', $nickname='', $password='', $email='', $username='', $ignoreoldpw=0, $inadmin=0, $email_checked=0) {
	return jclass('passport')->edit($oldnickname, $oldpw, $nickname, $password, $email, $username, $ignoreoldpw, $inadmin, $email_checked);
}


function jsg_get_member($nickname, $is = '', $cache=0) {
	$fields = '`uid`,`nickname`,`username`,`password`,`email`,`email_checked`,`ucuid`,`phone`,`role_id`,`role_type`,`salt`';

	$ret = jsg_member_info($nickname, $is, $fields, $cache);

	return $ret;
}


function jsg_member_info($uid, $is='uid', $fields='*', $cache=1)
{
	if(!$uid) {
		return array();
	}

	$iss = array('uid'=>1, 'username'=>1, 'nickname'=>1, 'email'=>1, 'phone'=>1, );
	if(empty($is) || !isset($iss[$is])) {
		$uid = jsg_member_nickname($uid, $cache);
		if($uid) {
			$is = 'nickname';
		} else {
			return array();
		}
	}

	$p = array(
		'fields' => $fields,
		$is => $uid,
		'count' => 1,
	);
	$rets = jsg_member_get($p, 1, $cache);
	if($rets && is_array($rets['list'][0])) {
		$mall_config  = jconf::get('mall');
		if($mall_config) {
			$rets['list'][0]['mall_credits_name'] = $mall_config['credits_name'];
			$rets['list'][0]['mall_credits'] = $rets['list'][0][$mall_config['credits']];
		}
	}
	return $rets['list'][0];
}


function jsg_member_nickname($nickname, $cache=1) {
	return jsg_member_val($nickname, 'nickname', $cache);
}
function jsg_member_uid($uid, $cache = 1) {
	return (int) jsg_member_val($uid, 'uid', $cache);
}
function jsg_member_val($key, $ret = 'nickname', $cache = 1) {
	$key = trim($key);
	if(empty($key)) {
		return '';
	}

	$info = array();
		if(is_numeric($key)) {
		if($GLOBALS['_J']['config']['sms_enable'] && jsg_is_mobile($key)) {
			$info = jsg_member_info($key, 'phone', '*', $cache);
		} else {
			$info = jsg_member_info($key, 'uid', '*', $cache);
		}
	} else {
				if(false !== strpos($key, '@')) {
			$info = jsg_member_info($key, 'email', '*', $cache);
		}
	}
	if(!$info) {
		$info = jsg_member_info($key, 'nickname', '*', $cache);
		if(!$info) {
			$info = jsg_member_info($key, 'username', '*', $cache);
		}
	}
	if(!$info) {
		return '';
	}

	return $info[$ret];
}



function jsg_member_get($p, $mark=1, $cache=1) {
	if($cache && $p['uid'] && $p['uid']==MEMBER_ID && $GLOBALS['_J']['member']) {
		return array('list'=>array($GLOBALS['_J']['member']));
	}

	static $S_members = array();

	if($cache) {
		$cache_id = md5(serialize($p).$mark);
		if(isset($S_members[$cache_id])) {
			return $S_members[$cache_id];
		}
	}

	$wheres = array();
	$ws = array('uid'=>1, 'username'=>1, 'nickname'=>1, 'email'=>1, 'phone'=>1, 'province'=>1, 'city'=>1, 'role_id'=>1, 'ucuid'=>1, 'invite_uid'=>1, );
	foreach($p as $k=>$v) {
		if(isset($ws[$k])) {
			$vs = (array) $v;
			$wheres[$k] = " `$k` IN ('".implode("','", $vs)."') ";
		}
	}

	$sql_where = ($wheres ? " WHERE " . implode(" AND ", $wheres) : "");

	$count = max(0, (int) $p['count']);
	if($count < 1) {
		$count = DB::result_first("SELECT COUNT(*) AS `count` FROM ".DB::table('members')." {$sql_where} ");
	}

	$rets = array();
	if($count > 0) {
		$page = array();
		$sql_limit = '';
		if($p['per_page_num']) {
			$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array', 'extra'=>$p['page_extra']));

			$sql_limit = " {$page['limit']} ";
		} elseif($p['limit']) {
			if(false !== strpos(strtolower($p['limit']), 'limit ')) {
				$sql_limit = " {$p['limit']} ";
			} else {
				$sql_limit = " LIMIT {$p['limit']} ";
			}
		} elseif ($p['count']) {
			$sql_limit = " LIMIT {$p['count']} ";
		}

		$sql_order = '';
		if($p['order']) {
			if(false !== strpos(strtolower($p['order']), 'order by ')) {
				$sql_order = " {$p['order']} ";
			} else {
				$sql_order = " ORDER BY {$p['order']} ";
			}
		}

		$sql_fields = ($p['fields'] ? $p['fields'] : "*");

		$query = DB::query("SELECT $sql_fields FROM ".DB::table('members')." $sql_where $sql_order $sql_limit ");
		$list = array();
		while(false != ($r = DB::fetch($query))) {
			if($mark) {
				$r = jsg_member_make($r);
			}
			$list[] = $r;
		}
		DB::free_result($query);

		if($list) {
			if($mark) {
				$list = buddy_follow_html($list, 'uid', (true === IN_JISHIGOU_WAP ? 'wap_follow_html' : 'follow_html'));
			}
			$rets = array('count'=>$count, 'list'=>$list, 'page'=>$page);
		}
	}

	if($cache && $cache_id) {
		$S_members[$cache_id] = $rets;
	}

	return $rets;
}

function jsg_member_make($row) {
	if (isset($row['uid'])) {
				if($row['face']) {
			$row['__face__'] = $row['face'];
		}
				if (true !== UCENTER_FACE && !$row['face']) {
			$row['face'] = $row['face_small'] = $row['face_original'] = face_get();		} else {
			$row['face_small'] = $row['face'] = face_get($row);
			$row['face_original'] = face_get($row, 'middle');
		}

				if($row['validate'] || $row['role_id']) {
			$row = jsg_member_make_validate($row);
		}
	}

		if (isset($row['province']) || isset($row['city'])) {
		$row['from_area'] = "{$row['province']} {$row['city']}";
	}

		if(isset($row['gender'])) {
		if($row['gender'] == 1) {
			$row['gender_ta'] = '他';
		} else {
			$row['gender_ta'] = '她';
		}
	}

		if($row['role_id'] > 0 && !isset($row['role_name'])) {
		$row['role_name'] = jtable('role')->get_name_by_id($row['role_id']);
	}
	

	return $row;
}
function jsg_member_make_validate($row) {
	if($row['validate']){
		$validate_id = ($row['validate_category'] ? $row['validate_category'] : $row['validate']);
	}
	if($row['role_id']){
				$role_info = jtable('role')->row($row['role_id']);
	}
	$row['validate_html'] = '';
	if($validate_id) {
				$validate_category = jconf::get('validate_category');
		if(!$validate_category){
			$query = DB::query("SELECT *
								FROM ".DB::table('validate_category')."
								ORDER BY id ASC");
			while ($value = DB::fetch($query)) {
				$validate_category[$value['id']] = $value;
			}
			jconf::set('validate_category', $validate_category);
		}
		$pcid = $validate_category[$validate_id]['category_id'];
		
		$row['vip_cat'] = $validate_category[$validate_id]['category_name'];
		if($pcid) {
			$row['vip_pcat'] = $validate_category[$pcid]['category_name'];
		}
		$row['vip_cat_string'] = ($row['vip_pcat'] ? "{$row['vip_pcat']}/" : "") . $row['vip_cat'];
		if($row['vip_cat_string']) {
			$row['vip_cat_html'] = "<a href='index.php?mod=people&code=view&ids={$validate_id}' title='查看 {$row['vip_cat_string']} 分类下的更多用户'>{$row['vip_cat_string']}</a>";
		}

		
		$category_pic = $validate_category[$validate_id]['category_pic'];
		if(!$category_pic && $pcid){
			$category_pic = $validate_category[$pcid]['category_pic'];
		}

		if(!isset($row['validate_remark']) || !isset($row['validate_true_name'])) {
			$memberfields = jtable('memberfields')->info($row['uid']);
			$row['validate_remark'] = $memberfields['validate_remark'];
			$row['validate_true_name'] = $memberfields['validate_true_name'];
		}

		$row['validate_user'] = $row['validate_true_name'];
		$row['vip_info'] = ($row['vip_cat_string'] ? "[{$row['vip_cat_string']}]" : "") . $row['validate_remark'];
		$row['vip_pic'] = $GLOBALS['_J']['config']['site_url'] . '/' . ($category_pic ? $category_pic : 'images/vip.gif');

		$row['validate_string'] = "<img class='vipImg' align='absmiddle' title='{$row['vip_info']}' src='{$row['vip_pic']}' />";
		$row['validate_html'] .= "<a href='{$GLOBALS['_J']['config']['site_url']}/index.php?mod=other&code=vip_intro' title='{$row['vip_info']}' target='_blank'>{$row['validate_string']}</a>";
	}
	if($role_info['role_icon']) {
		$row['role_icon'] = "<img class='vipImg' align='absmiddle' title='".$role_info['role_name']."' src='{$GLOBALS['_J']['config']['site_url']}/".$role_info['role_icon']."'>";
		$row['validate_string'] .= $row['role_icon'];
		$row['validate_html'] .= $row['role_icon'];
	}
	return $row;
}


function jsg_member_info_by_mod() {
	$ret = array();
	$mr = ($_POST['mod_original'] ? $_POST['mod_original'] : $_GET['mod_original']);
	if($mr) {
		$mr = get_safe_code($mr);
		$ret = jsg_member_info($mr, 'username');
		if(!$ret) {
			$ret = jsg_member_info($mr, (is_numeric($mr) ? 'uid' : 'nickname'));
		}
		
	}
	return $ret;
}

function jsg_role_info($id) {
	return jtable('role')->row($id, '');
}


function jsg_role_check_allow($action, $to_uid, $from_uid = MEMBER_ID) {
	$rets = array();

	$to_uid = is_numeric($to_uid) ? $to_uid : 0;
	$from_uid = is_numeric($from_uid) ? $from_uid : 0;
	if($to_uid < 1 || $from_uid < 1 || $to_uid == $from_uid) {
		return $rets;
	}

		if(MEMBER_ID == $from_uid && true === JISHIGOU_FOUNDER) {
		return $rets;
	}

	$actions = array('sendpm'=>'私信', 'topic_forward'=>'转发', 'topic_reply'=>'评论', 'topic_at'=>'@', 'follow'=>'关注', );
	$action_name = $actions[$action];
	if(is_null($action_name)) {
		return $rets;
	}

	$to_member = jsg_member_info($to_uid);
	$from_member = jsg_member_info($from_uid);

	if($to_member && $from_member) {
		$to_role_id = $to_member['role_id'];
		$from_role_id = $from_member['role_id'];

		$to_role = jsg_role_info($to_role_id);
		$from_role = jsg_role_info($from_role_id);

		if($to_role && $from_role) {
			$to_field = "allow_{$action}_to";
			$from_field = "allow_{$action}_from";

			$allow_action_to = $from_role[$to_field];
			if($allow_action_to) {
				if(-2 == $allow_action_to || !jsg_find($allow_action_to, $to_role_id)) {
					$rets['error'] = "由于用户组权限设置，您没有 $action_name TA的权限";

					return $rets;
				}
			}

			
		}
	}

	return $rets;
}

function jsg_find($haystack, $needle, $append=null) {
	$append = (isset($append) ? $append : ',');
	$haystack = $append.$haystack.$append;
	$needle = $append.$needle.$append;
	return (false !== strpos($haystack, $needle));
}


function jsg_get_vip_uids($limit=300, $day=30) {
	$limit = (int) $limit;
	if($limit < 1) {
		$limit = 300;
	}
	$day = (int) $day;
	if($day < 1) {
		$day = 30;
	}

	$vip_uids = array();
		$cache_id = "topic/hot-vip-uids-{$day}-{$limit}";
	if(false === ($vip_uids = cache_file('get', $cache_id))) {
		$query = DB::query("select `uid` from ".DB::table('members')." where `lastactivity`>'".(TIMESTAMP - 86400 * $day)."' and `validate`='1' order by `lastactivity` desc limit {$limit} ");
		while (false != ($row = DB::fetch($query))) {
			$vip_uids[$row['uid']] = $row['uid'];
		}

		cache_file('set', $cache_id, $vip_uids, 600);
	}

	return $vip_uids;
}

function jsg_member_is_founder($uid) {
	global $_J;

	$uid = (is_numeric($uid) ? $uid : 0);

	$ret = (bool) ($uid>0 && $_J['config']['jishigou_founder'] && jsg_find($_J['config']['jishigou_founder'], $uid, ','));

	return $ret;
}

function jsg_is_mobile($num) {
	$ret = false;
	if($num && is_numeric($num)) {
		settype($num,'string');
		$num_len = strlen($num);
		if(11==$num_len || 12==$num_len) {
			$ret = preg_match('~^((?:13|15|18)\d{9}|0(?:10|2\d|[3-9]\d{2})[1-9]\d{6,7})$~',$num);
		}
	}
	return $ret;
}


function jsg_member_password($password, $salt) {
	return md5(md5($password) . $salt);
}

function jsg_member_salt() {
	return random(6);
}

function jsg_login_log($log = array(), $check = null) {
	$check = (isset($check) ? (bool) $check : (empty($log) ? true : false));
	
	$logs = array(
		'uid' => MEMBER_ID,
		'user_nickname' => MEMBER_NICKNAME,
		'ip' => $GLOBALS['_J']['client_ip'],
		'ip_port' => $GLOBALS['_J']['client_ip_port'],
		'dateline' => TIMESTAMP,
		'time' => date('Y-m-d', TIMESTAMP),
	);

	if(defined('IN_JISHIGOU_LOGIN_TYPE')) {
		$ctype = constant('IN_JISHIGOU_LOGIN_TYPE');
	} else {
		if (true === IN_JISHIGOU_WAP) {
			$ctype = 'WAP';
		} elseif (true === IN_JISHIGOU_MOBILE) {
			$ctype = '3G';
		} elseif(true === IN_JISHIGOU_API) {
			$ctype = 'API';
		} else {
			$ctype = 'WEB';
		}
	}
	$logs['type'] = $ctype;

	settype($log, 'array');
	foreach($logs as $k=>$v) {
		if(!isset($log[$k])) {
			$log[$k] = $v;
		}
	}
	
	if($log['uid'] < 1) {
		return false;
	}
	if($check && false != (jtable('login_log')->info(array(
			'uid' => $log['uid'],
			'ip' => $log['ip'],
			'time' => $log['time'],
			'type' => $log['type'],
		)))) {
		return false;
	}
	return jtable('login_log')->insert($log, 1);
}
?>