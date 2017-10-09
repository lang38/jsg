<?php
/**
 *
 * 底层权限、用户操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member.class.php 5555 2014-02-19 03:16:03Z wuliyong $
 */


if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class member {
	var $ID = 0;	var $sid = '';
	var $SessionExists = false;
	var $MemberPassword = '';
	var $MemberFields = array();
	var $ActionList = array();
	var $CurrentAction = array();	var $_Error = array();
	var $no_record_guest = false;

	function member() {
		$this->setSessionId();
		$this->no_record_guest = $GLOBALS['_J']['config']['no_record_guest'];
	}
	
	function visit($uid = MEMBER_ID) {
		global $_J;
		$rets = array();
		if(true !== jsg_member_is_founder($uid)) {
			$member = jsg_member_info($uid);
						$visit_state = $_J['config']['visitState'];
			if(2 == $visit_state) {
							} elseif (1 == $visit_state) {
				if($_J['config']['visitIp']) {
					if(false == preg_match("~^(" . preg_quote($_J['config']['visitIp'], '~') . ")~", $_J['client_ip'])) {
						return jerror('【站点内部开放】您的IP地址 ' . $_J['client_ip'] . ' 没有权限访问', -2);
					}
				}
				
				if($_J['config']['visitMember']) {
					$visitMember = explode(',', $_J['config']['visitMember']);
					if(empty($member) || false == in_array($member['nickname'], $visitMember)) {
						if($uid == MEMBER_ID && MEMBER_ID > 0) {
							jsg_member_logout();
						}
						return jerror('【站点内部开放】您 ' . $member['nickname'] . ' 没有访问权限', -3);
					}
				}
				
				if($_J['config']['allowed_visit_role_list']) {
					$allowed_visit_role_list = explode(',', $_J['config']['allowed_visit_role_list']);
					if(empty($member) || false == in_array($member['role_id'], $allowed_visit_role_list)) {
						if($uid == MEMBER_ID && MEMBER_ID > 0) {
							jsg_member_logout();
						}
						return jerror('【站点内部开放】您所在的用户组 ' . $member['role_name'] . ' 没有访问权限，<a href="index.php?mod=login">请点此进行登录</a>', -4);
					}
				}
			}
			
			if($member && 1 == $_J['config']['email_must_be_true'] && 0 == $member['email_checked'] && !$_J['config']['ldap_enable']) {
				return jerror('该网站需<a href="index.php?mod=member&code=setverify&ids=' . $uid . '">邮箱验证</a>后才能进行访问', -5);
			}
		}
		return $rets;
	}
	function init() {
		$id = 0;
		$pass = '';

		if(($auth = jsg_getcookie('auth'))) {
			list($pass, $id) = explode("\t", authcode($auth, 'DECODE'));
		}
		return $this->FetchMember($id, $pass);
	}
	function setSessionId($sid=null) {
		if(!is_null($sid)) {
			$this->sid = $sid;
			jsg_setcookie('sid', $sid, 311040000);
		} else {
			$this->sid = get_param('sid') ? get_param('sid') : jsg_getcookie('sid');
		}
		if(!empty($this->sid)) {
			if(false == preg_match('~^[\w\d]{2,18}$~i', $this->sid)) {
				jlog('sid', $this->sid, 0);
				exit('sid is invalid');
			}
		}
	}
	function FetchMember($id, $pass) {
		$this->ID   = max(0, (int) $id);
		$this->MemberPassword = trim($pass);
		$this->GetMember();
		if($this->MemberFields) {
						if(118 == $this->MemberFields['role_id']) {
				if(jsg_getcookie('auth')) {
					jsg_member_logout();
				}
				exit('<meta http-equiv="refresh" content="3; URL=\'index.php\'">Your role id is disable.');
			}

			jdefine("MEMBER_ID",(int) $this->MemberFields['uid']);
			jdefine("MEMBER_UCUID",(int) $this->MemberFields['ucuid']);
			jdefine("MEMBER_NAME",$this->MemberFields['username']);
			jdefine("MEMBER_NICKNAME",$this->MemberFields['nickname']);
			jdefine("MEMBER_ROLE_TYPE",$this->MemberFields['role_type']);
			define("MEMBER_STYLE_THREE_TOL", (int) (1 == $this->MemberFields['style_three_tol'] ? 1 :
			(-1 == $this->MemberFields['style_three_tol'] ? 0 : $GLOBALS['_J']['config']['style_three_tol'])));

			jdefine('JISHIGOU_FOUNDER', jsg_member_is_founder(MEMBER_ID));			
		

			
			if(($GLOBALS['_J']['client_ip'] != $this->MemberFields['lastip'] || (date('YmdH', $this->MemberFields['lastactivity']) != date('YmdH', TIMESTAMP)))) {
				jsg_login_log();
			}
		}

		return $this->MemberFields;
	}

	function UpdateSessions() {
		if (jsg_getcookie('sid')=='' || $this->sid!=jsg_getcookie('sid')) {
			$this->setSessionId($this->sid);
		}

				$uid = MEMBER_ID;
		$timestamp = TIMESTAMP;
		$member = $this->MemberFields;
		$member['slastactivity'] = $timestamp;
		$member['action'] = (int) $this->CurrentAction['id'];
		if($this->SessionExists) {
			if(($member['action']>0 && $member['action'] != $this->MemberFields['action']) || ($timestamp - $this->MemberFields['slastactivity'] > 900)) {
				if($uid > 0 || !$this->no_record_guest) {
					DB::query("UPDATE ".DB::table('sessions')." SET `action`='{$member['action']}', `slastactivity`='{$member['slastactivity']}' WHERE `sid`='{$this->sid}'");
				}
			}
		} else {
			$onlinehold		= 3600;			$ip = $GLOBALS['_J']['client_ip'];
			if($uid > 0 || !$this->no_record_guest) {
				$ips = explode('.',$ip);
				$sql = "DELETE FROM `".TABLE_PREFIX."sessions`
				WHERE
					`sid`='{$this->sid}'
					OR `slastactivity`<'".($timestamp-$onlinehold)."'
					OR ('".$uid."'<>'0' AND `uid`='".$uid."')
					OR (`uid`='0' AND `ip1`='$ips[0]' AND `ip2`='$ips[1]' AND `ip3`='$ips[2]' AND `ip4`='$ips[3]' AND `slastactivity`>'".($timestamp-60)."')";
				DB::query($sql, 'SILENT');

				DB::query("REPLACE INTO ".DB::table('sessions')."
					SET `sid`='{$this->sid}', `ip1`='{$ips[0]}', `ip2`='{$ips[1]}', `ip3`='{$ips[2]}', `ip4`='{$ips[3]}',
					`uid`='{$member['uid']}', `action`='{$member['action']}', `slastactivity`='{$member['slastactivity']}'",
				'SILENT');
			}

						if($uid > 0) {
								if(($ip != $this->MemberFields['lastip'] || ($timestamp - $this->MemberFields['lastactivity'] > $onlinehold))) {
					$sql="
					UPDATE
						".TABLE_PREFIX.'members'."
					SET
						lastip='$ip',
						last_ip_port='{$GLOBALS['_J']['client_ip_port']}',
						lastactivity='$timestamp'
					WHERE
						uid='".$uid."'";
					DB::query($sql, 'SILENT');
				}
			}
		}
	}

	function access($mod, $code = '', $uid = 0, $is_admin = 0) {
		return $this->HasPermission($mod, $code, $is_admin, $uid);
	}

	
	function HasPermission($mod, $code, $is_admin=0, $uid=0) {
		$MemberFields = array();
		if($uid) {
			if(is_array($uid)) {
				$MemberFields = $uid;
			} elseif(($uid = max(0, (int) $uid)) > 0 && $uid != $this->MemberFields['uid']) {
				$MemberFields = jsg_member_info($uid);
			}
			if($MemberFields && $_role_info = jtable('role')->row($MemberFields['role_id'])) {
				$MemberFields = array_merge($MemberFields, $_role_info);
			}
		}
		if(!$MemberFields || $MemberFields['uid'] < 1) {
			$MemberFields = $this->MemberFields;
		}

		$mod = trim($mod);
		$action = trim($code);
		$role_id = (int) $MemberFields['role_id'];
		$role_name = $MemberFields['role_name'];
		$role_privilege = $MemberFields['role_privilege'];

		if($role_id < 1 && true !== JISHIGOU_FOUNDER) {
			$this->_SetError("角色编号不能为空,或者该编号在服务器上已经删除");
			return false;
		}

		$is_admin = ($is_admin ? 1 : 0);
		if(!isset($this->ActionList[$mod])) {
			$this->ActionList[$mod] = jtable('role_action')->get_list($mod, $is_admin);
		}

		$current_action = array();
		if((($current_action_id=$this->ActionList[$mod]['index'][$action])!==null) || (($current_action_id=$this->ActionList[$mod]['index']["*"])!==null)) {
			$current_action = $this->ActionList[$mod]['info'][$current_action_id];
			$current_action['id'] = $current_action_id;
			$current_action['mod'] = $mod;
			$this->_SetCurrentAction($current_action);

			if(true === JISHIGOU_FOUNDER) {
				return true;
			}
			if($current_action['allow_all']==1) {
				return true;
			}
			if($current_action['allow_all']=='-1') {
				$this->_SetError("系统已经禁止<B>{$current_action['name']}</B>的任何操作");
				return false;
			}
						if($MemberFields['role_privilege']=="*") {
				return true;
			}
						if(false===jsg_find($role_privilege, $current_action_id, ',')) {
				if($ActionList[$current_action_id]['message']) {
					$message = $ActionList[$current_action_id]['message'];
				} else {
					$message = "您的角色({$role_name})没有{$current_action['name']}权限";
					if(5 == $role_id) {
						$message .= "；<br />请先通过<a href='index.php?mod=settings#modify_email_area'>邮件验证</a>或者<a href='index.php?mod=other&code=contact'>联系我们</a>";
					}
				}
				$this->_SetError($message);
				return false;
			}
		} else { 			$this->_SetCurrentAction($current_action);
						

			if(!$GLOBALS['_J']['config']['safe_mode']) {
				return true; 			}
			if(!$is_admin) {
				return true; 			}
			if('POST' != $_SERVER['REQUEST_METHOD']) {
				return true; 			}
			if(!$GLOBALS['_J']['config']['jishigou_founder']) {
				return true; 			}

			$error = "操作模块:{$mod}<br>操作指令:{$action}<br><br>";
			$error.= "由于此操作在系统中没有权限控制,您暂时无法执行该操作,请联系网站的超级管理员。";
			$this->_SetError($error);

			return false;
		}

		return true;
	}
	function _iddstrs($row,$id=0) {
		$_ids = explode(",", $row["privilege"]);
		$ids = array();
		foreach($_ids as $_id) {
			$_id = (is_numeric($_id) ? $_id : 0);
			if($_id > 0) {
				$ids[$_id] = $_id;
			}
		}
		$id = (is_numeric($id) ? $id : 0);
		if($id > 0) {
			$ids[$id] = $id;
		}
		sort($ids);

		return implode(",",$ids);
	}
	function _SetCurrentAction($action) {
		$this->CurrentAction=$action;
	}
	function GetMemberFields() {
		return $this->MemberFields;
	}
	function GetMember() {
		global $_J;

		$this->MemberFields = array();
		if($this->sid) {
			$sql = '';
			if($this->ID) {
				$sql = "SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid
						LEFT JOIN ".DB::table("sessions")." `S` ON S.uid=M.uid
					WHERE M.uid='{$this->ID}' AND M.password='{$this->MemberPassword}' AND S.sid='{$this->sid}' AND
						CONCAT_WS('.', S.ip1, S.ip2, S.ip3, S.ip4)='{$_J['client_ip']}'";
			} else {
				if(!$this->no_record_guest) {
					$sql = "SELECT * FROM ".DB::table("sessions")." WHERE sid='{$this->sid}' AND CONCAT_WS('.', ip1, ip2, ip3, ip4)='{$_J['client_ip']}'";
				}
			}
						if($sql) {
				$this->MemberFields = DB::fetch_first($sql);
			}

			if($this->MemberFields && !$this->ID && $this->MemberFields['uid'] > 0) {
				$row = DB::fetch_first("SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid WHERE M.uid='{$this->MemberFields['uid']}'");
				if($row) {
					$this->MemberFields = array_merge($row, $this->MemberFields);
				}
			}
		}
		$this->SessionExists = (($this->MemberFields && $this->MemberFields['uid']==$this->ID) ? true : false);


		if(!$this->SessionExists) {
			jsg_setcookie('sid', '', -311040000);

			if($this->ID) {
				$sql = "SELECT * FROM ".DB::table("members")." `M` LEFT JOIN ".DB::table("memberfields")." `MF` ON MF.uid=M.uid
					WHERE M.uid='{$this->ID}' AND M.password='{$this->MemberPassword}'";
				$this->MemberFields = DB::fetch_first($sql);
				if(!$this->MemberFields) {
					jsg_setcookie('auth', '', -311040000);
				}
			} else {
				jsg_setcookie('auth', '', -311040000);
			}

			$this->sid = $this->MemberFields['sid'] = random(6);
		}


		$this->MemberFields['role_id'] = (int) $this->MemberFields['role_id'];
		if($this->MemberFields['role_id'] < 1) {
			$this->MemberFields = array_merge($this->MemberFields, jtable('role')->guest());
		} else {
			$role = jtable('role')->row($this->MemberFields['role_id']);
			if($role) {
				$this->MemberFields = array_merge($this->MemberFields, $role);
			}
		}

		if($this->MemberFields['uid'] > 0) {
			$this->MemberFields = jsg_member_make($this->MemberFields);
		}

		$_J['uid'] = $this->MemberFields['uid'];
		$_J['username'] = $this->MemberFields['username'];
		$_J['nickname'] = $this->MemberFields['nickname'];
		$_J['role_id'] = $this->MemberFields['role_id'];

		$_J['member'] = & $this->MemberFields;
	}
	function _SetError($error)
	{
		$this->_Error[]=$error;
	}
	function GetError()
	{
		return $this->_Error;
	}
}
?>