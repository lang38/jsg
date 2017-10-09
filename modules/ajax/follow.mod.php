<?php
/**
 *
 * 排行模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: follow.mod.php 4622 2013-09-29 03:37:20Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}
	
		function add() {
		$GLOBALS['disable_show_msg'] = 1;		$response = '';

				$follow_button = $this->Post['follow_button'];

		if(MEMBER_ID < 1) {
                    js_show_login('登录后才能执行此操作');
		}

				$uid = jpost('uid', 'int');
                if($uid < 1) {
                    js_alert_output('$uid is empty');
                }
                
		if($follow_button == 'channel' || $follow_button == 'channelnav'){			$isbuddy = jlogic('channel')->channel_isbuddy($uid);
			$can_buddy = jlogic('channel')->can_view_topic($uid);
			if($isbuddy){
				jlogic('channel')->buddy_channel($uid,0);
				$response = follow_channel($uid,0);
			}else{
				if($can_buddy){
					jlogic('channel')->buddy_channel($uid,1);
					$response = follow_channel($uid,1);
				}else{
					$response = '';
				}
			}
		}elseif($this->Config['department_enable'] && $follow_button == 'department'){			$isbuddy = DB::result_first("SELECT count(*) FROM ".DB::table('buddy_department')." WHERE uid = '".MEMBER_ID."' AND did = '$uid'");
			if($isbuddy){
				DB::query("DELETE FROM ".DB::table('buddy_department')." WHERE uid = '".MEMBER_ID."' AND did = '$uid'");
				$response = follow_department($uid,0);
			}else{
				DB::query("INSERT INTO ".DB::table('buddy_department')." (`uid`,`did`) values ('".MEMBER_ID."','{$uid}')");
				$response = follow_department($uid,1);
			}
		}else{
			$rets = buddy_add($uid, MEMBER_ID, 1);
			if($rets) {
				if($rets['error']) {
					js_alert_output($rets['error']);
				} else {
										if($follow_button == 'xiao'){
						$response = follow_html2($uid, 0, 0, 0);
					} else {
						$response = follow_html($uid, 0, 0, 0);
					}
				}
			} else {
								if($follow_button == 'xiao'){
					$response = follow_html2($uid, 1, 0, 0);
				} else {
					$response = follow_html($uid, 1, 0, 0);
				}
			}
						$response .= '<success></success>';
			$u_nickname = DB::result_first("SELECT `nickname` FROM ".DB::table('members')." WHERE uid = '$uid'");
			$response .= $u_nickname;
		}
		response_text($response);
	}
	
}