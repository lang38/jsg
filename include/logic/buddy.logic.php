<?php
/**
 *
 * 好友相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy.logic.php 5351 2014-01-03 07:45:01Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class BuddyLogic {

	var $table = 'buddys';

	function BuddyLogic() {
		;
	}

		function is_buddy_relation($buddyid, $uid = MEMBER_ID) {
		$is_buddy_relation = 0;
		if($buddyid == $uid) {
			$is_buddy_relation = -1;
		} else {
			$is_follow = $this->is_follow($buddyid, $uid) ? 1 : 0;
			$is_fans = $this->is_fans($uid, $buddyid) ? 2 : 0;
			$is_buddy_relation = $is_follow + $is_fans;
		}
		return $is_buddy_relation;
	}
		function is_follow($buddyid, $uid = MEMBER_ID) {
		return (false != $this->info($buddyid, $uid));
	}
		function is_fans($buddyid, $uid = MEMBER_ID) {
		return (false != $this->info($uid, $buddyid));
	}

	
	function info($buddyid, $uid = MEMBER_ID) {
		return jtable('buddy_follow')->row($uid, $buddyid);
	}

	
	function get($p, $cache_time=0) {
		$wheres = array();

		$ids = $this->_param_id($p['id'] ? $p['id'] : $p['ids']);
		if($ids) {
			$wheres['id'] = " `id` IN ('".implode("','", $ids)."') ";
		}
		$uids = $this->_param_id($p['uid'] ? $p['uid'] : $p['uids']);
		if($uids) {
			$wheres['uid'] = " `uid` IN ('".implode("','", $uids)."') ";
		}
		$buddyids = $this->_param_id($p['buddyid'] ? $p['buddyid'] : $p['buddyids']);
		if($buddyids) {
			$wheres['buddyid'] = " `buddyid` IN ('".implode("','", $buddyids)."') ";
		}
		if($p['dateline']) {
			$wheres['dateline'] = " `dateline`>='".(max(0, (int) $p['dateline']))."' ";
		}
		if($p['buddy_lastuptime'] || $p['lastuptime']) {
			$wheres['buddy_lastuptime'] = " `buddy_lastuptime`>='".(max(0, (int) $p['buddy_lastuptime'], (int) $p['lastuptime']))."' ";
		}
		if($p['where']) {
			$wheres['where'] = $p['where'];
		}

		$sql_where = ($wheres ? " WHERE ".implode(" AND ", $wheres)." " : "");
		if($p['return_where']) {
			return $sql_where;
		}

		if($cache_time) {
			$cache_id = "{$this->table}-" . md5(serialize($p));
			if(false !== ($rets = cache_db('mget', $cache_id))) {
				return $rets;
			}
		}

		$count = max(0, (int) $p['count']);
		if($count < 1) {
			$count = DB::result_first("SELECT COUNT(1) AS `count` FROM ".DB::table($this->table)." $sql_where ");

			if($p['return_count']) {
				return $count;
			}
		}

		$rets = array();
		if($count > 0) {
			$page = array();
			$sql_limit = '';
			if($p['per_page_num']) {
				$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array', 'extra'=>$param['page_extra']));
				$sql_limit = " {$page['limit']} ";
			} elseif($p['limit']) {
				if(false !== strpos(strtolower($p['limit']), 'limit ')) {
					$sql_limit = " {$p['limit']} ";
				} else {
					$sql_limit = " LIMIT {$p['limit']} ";
				}
			} elseif($p['count']) {
				$sql_limit = " LIMIT $count ";
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

			$sql = "SELECT $sql_fields FROM ".DB::table($this->table)." $sql_where $sql_order $sql_limit ";
			if($p['return_sql']) {
				return $sql;
			}
			$list = DB::fetch_all($sql);

			if($list) {
				$rets = array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		if($cache_time) {
			cache_db('mset', $cache_id, $rets, $cache_time);
		}

		return $rets;
	}

	
	function add($p, $delete_if_exists=0) {
		$rets = array();
		$buddyid = (int) ($p['buddyid'] ? $p['buddyid'] : $p['touid']);
		$uid = ($p['uid'] > 0 ? $p['uid'] : MEMBER_ID);
		if($uid < 1 || $buddyid < 1 || $uid == $buddyid) {
			$rets['error'] = '您不能关注自己';
			return $rets;
		}

		$query = DB::query("SELECT * FROM `".TABLE_PREFIX."members` WHERE `uid` IN ('{$uid}','{$buddyid}')");
		$members = array();
		while (false != ($row = DB::fetch($query))) {
			$members[$row['uid']] = $row;
		}

		$info = $this->info($buddyid, $uid);
		if (!$info) {
			$sys_config = jconf::get();

			if(count($members) < 2) {
				$rets['error'] = '关注失败，TA已经消失不见了';
				return $rets;
			}

			if($sys_config['follow_limit']>0 && $members[$uid]['follow_count']>=$sys_config['follow_limit']) {
				$rets['error'] = '本站限制关注数量为<b>'.$sys_config['follow_limit'].'</b>人，您不能再关注更多的好友了';
				return $rets;
			}

			if($members[$buddyid]['disallow_beiguanzhu']) {
				$rets['error'] = '关注失败，TA设置了禁止被关注';
				return $rets;
			}

			if(is_blacklist($uid, $buddyid)) {
				$rets['error'] = '关注失败，对方已将您拉入了黑名单';
				return $rets;
			}

			$_tmps = jsg_role_check_allow('follow', $buddyid, $uid);
			if($_tmps && $_tmps['error']) {
				return $_tmps;
			}

			jtable('buddy_follow')->add($uid, $buddyid);
			jtable('buddy_fans')->add($buddyid, $uid);
						ios_push_msg($buddyid,'你有新消息:1个粉丝');

			$this->count($uid);
			$this->count($buddyid);

			
			if ($sys_config['sendmailday'] > 0) {
				jtable('mailqueue')->add($members[$buddyid], 'notice_fans');
			}

			if($sys_config['extcredits_enable'] && $uid>0) {
				
				$update_credits = false;
				if($members[$buddyid]['nickname']) {
					$update_credits = update_credits_by_action(("_U".crc32($members[$buddyid]['nickname'])),$uid);
				}

				if(!$update_credits) {
					
					update_credits_by_action('buddy',$uid);
				}
			}

			if($sys_config['imjiqiren_enable'] && imjiqiren_init($sys_config)) {
				imjiqiren_send_message($members[$buddyid],'f');
			}

			if($sys_config['sms_enable'] && sms_init($sys_config)) {
				sms_send_message($members[$buddyid],'f');
			}
		} else {
			if($delete_if_exists) {
				$this->del_info($buddyid, $uid);
			}
		}

		return $info;
	}

	
	function del_info($buddyid, $uid, $update = 1) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$buddyid = jfilter($buddyid, 'int');
		if($uid < 1 || $buddyid < 1) {
			return $ret;
		}
		$info = $this->info($buddyid, $uid);
		if($info) {
			jtable('buddy_follow')->del($uid, $buddyid);
			jtable('buddy_fans')->del($buddyid, $uid);
			
			$this->count($buddyid);

			if($update) {
				$this->count($uid);
				if($GLOBALS['_J']['config']['extcredits_enable'] && $uid>0) {
					
					update_credits_by_action('buddy_del', $uid);
				}
			}
			$ret = true;
		}
		return $ret;
	}

	
	function del_user($uids) {
		if(empty($uids)) {
			return false;
		}
		$p = array('uid' => $uids);
		$uids = jtable('members')->get_ids($p, 'uid');
		if($uids) {
			foreach($uids as $uid) {				
				$p = array('uid' => $uid);	
							
				$buddyids = $this->get_buddyids($uid);
				if($buddyids) {
					foreach($buddyids as $bid) {
						$this->del_info($bid, $uid, false);
					}
				}
				$fansids = $this->get_fansids($uid);
				if($fansids) {
					foreach($fansids as $fid) {
						$this->del_info($fid, $uid, false);
					}
				}

				jtable('buddy_fans')->table_name($uid);
				jtable('buddy_fans')->delete($p);

				jtable('buddy_follow')->table_name($uid);
				jtable('buddy_follow')->delete($p);

				jtable('buddy_follow_group_relation')->table_name($uid);
				jtable('buddy_follow_group_relation')->delete($p);

				jtable('buddy_follow_group')->delete($p);
				jtable('buddy_fans_table_id')->delete($p);
				jtable('buddy_follow_table_id')->delete($p);
			}
		}
		return true;
	}

	
	function del($p) {
		$p['result_count'] = ($p['result_count'] ? $p['result_count'] : ($p['count'] ? $p['count'] : 999999));

		$rets = $this->get($p);
		if($rets) {
			$list = $rets['list'];
			foreach($list as $row) {
				$uid = $row['uid'];
				$buddyid = $row['buddyid'];

				$this->del_info($buddyid, $uid);
			}
		}

		return true;
	}

	
	function count($uid) {
		$uid = max(0, (int) $uid);
		if($uid < 1) {
			return false;
		}

		$member = DB::fetch_first("SELECT `uid`, `follow_count`, `fans_count` FROM ".DB::table('members')." WHERE `uid`='$uid'");
		if(!$member) {
			return false;
		}
		$member['follow_count'] = max(0, (int) $member['follow_count']);
		$member['fans_count'] = max(0, (int) $member['fans_count']);

		jtable('buddy_follow')->table_name($uid);
		$follow_count = jtable('buddy_follow')->count(array('uid' => $uid));
		if($follow_count != $member['follow_count']) {
						jtable('members')->update_count($uid, 'follow_count', $follow_count);

			cache_db('rm', "{$uid}-buddyids-%", 1);
			cache_db('rm', "{$uid}-topic-%", 1);
		}

		jtable('buddy_fans')->table_name($uid);
		$fans_count = jtable('buddy_fans')->count(array('uid' => $uid));
		if($fans_count != $member['fans_count']) {
			$fans_new = 0;
			$fans_new_update = '';
			if($fans_count > $member['fans_count']) {
				$fans_new = max(0, (int) (($fans_count-$member['fans_count'])));

				if($fans_new > 0) {
					$fans_new_update = " , `fans_new` = `fans_new` + '{$fans_new}'";
				}
			}

						jtable('members')->update_count($uid, 'fans_count', $fans_count, 1, array('+@fans_new'=>$fans_new));
		}

		return true;
	}

	
	function get_ids($p, $cache_time=0) {
		$p['count'] = ($p['count'] ? $p['count'] : 999999);
				$by = ($p['fields'] ? $p['fields'] : 'buddyid');
		$p['fields'] = " DISTINCT (`{$by}`) AS `{$by}` ";
		$rets = $this->get($p, $cache_time);
		if(!$rets) {
			return false;
		}
		$list = array();
		foreach($rets['list'] as $row) {
			$list[$row[$by]] = $row[$by];
		}
		return $list;
	}

	
	function get_fansids($p) {
		if(empty($p['uid'])) {
			return false;
		}
		$p['result_count'] = ($p['result_count'] ? $p['result_count'] : ($p['count'] ? $p['count'] : 9999999));
		jtable('buddy_fans')->table_name($p['uid']);
		return jtable('buddy_fans')->get_ids($p, 'touid');
	}

	
	function get_buddyids($uid, $uptime_limit=0) {
		$p = array();
		$cache_time = 0;
		if(is_array($uid)) {
			$p = $uid;
		} else {
			$p['uid'] = $uid;
			if(is_numeric($p['uid'])) {
				$cache_time = 3600;
			}
		}
		if(empty($p['uid'])) {
			return false;
		}
		$p['result_count'] = (int) ($p['result_count'] ? $p['result_count'] : ($p['count'] ? $p['count'] : 999999));
		$upt = max(0, (int) $uptime_limit);
		$uptime_limit = max((int) $p['buddy_lastuptime'], (int) $p['lastuptime'], ($upt ? (TIMESTAMP - $upt * 86400) : 0));

		if($cache_time) {
			$uid = (int) $p['uid'];
			$cache_id = "{$uid}-buddyids-" . ($upt ? $upt : $uptime_limit);
			if(false !== ($ret = cache_db('get', $cache_id))) {
				return $ret;
			}
		}

		$table_name = jtable('buddy_follow')->table_name($p['uid']);
		if($uptime_limit) {
			$limit = min($p['result_count'], 1000);
						$sql = "SELECT M.`uid`
			FROM " . DB::table($table_name) . " BF
				LEFT JOIN " . DB::table('members') . " M ON (M.`uid`=BF.`touid`)
			WHERE BF.`uid` IN (" . jimplode($p['uid']) . ") AND M.`lastactivity`>'$uptime_limit'
			ORDER BY `lastactivity` DESC"
			. ($limit > 0 ? " LIMIT {$limit}" : "");
			$query = DB::query($sql);
			$ret = array();
			while($row = DB::fetch($query)) {
				$ret[$row['uid']] = $row['uid'];
			}
		} else {
						$ret = jtable('buddy_follow')->get_ids($p, 'touid');
		}

		if($cache_id && $cache_time) {
			cache_db('set', $cache_id, $ret, $cache_time);
		}

		return $ret;
	}

	
	function set_remark($uid, $touid, $remark='') {
		return jtable('buddy_follow')->set_remark($uid, $touid, $remark);
	}

	function follow_html2($member_list, $uid_field='uid', $follow_func='follow_html2', $is_one_row = 0,$refresh=0) {
		return $this->follow_html($member_list, $uid_field, $follow_func, $is_one_row);
	}
	
	function follow_html($member_list, $uid_field='uid', $follow_func='follow_html', $is_one_row = 0,$refresh=0) {
		if(!$member_list || MEMBER_ID < 1) {
			return $member_list;
		}

		if(!$uid_field) {
			$uid_field = 'uid';
		}

		if($is_one_row) {
			$one_row_key = false;
			if(isset($member_list[$uid_field])) {
				$one_row_key = 'one_row_key';
				$member_list = array(
					$one_row_key => $member_list,
				);
			} else {
				return $member_list;
			}
		} else {
			if($GLOBALS['_J']['config']['acceleration_mode']) {
				return $member_list;
			}
		}

		$uids = array();
		foreach($member_list as $v) {
			if(!isset($v[$uid_field]) || (!$is_one_row && isset($v['follow_html']))) {
				return $member_list;
			}

			$uid = (int) $v[$uid_field];
			if($uid > 0 && $uid != MEMBER_ID) {
				$uids[$uid] = $uid;
			}
		}

		$buddyids = array();
		$fansids = array();
		if($uids) {
			$p = array(
				'count' => count($uids),
				'uid' => MEMBER_ID,
				'touid' => $uids,
			);
			$buddyids = $this->get_buddyids($p); 
			$p = array(
				'count' => count($uids),
				'uid' => MEMBER_ID,
				'touid' => $uids,
			);
			$fansids = $this->get_fansids($p); 		}

		
		foreach($member_list as $k=>$v) {
			$uid = $v[$uid_field];
			if($uid > 0) {
				$member_list[$k]['is_follow'] = (isset($buddyids[$uid]) ? 1 : 0);
				$member_list[$k]['is_follow_me'] = (isset($fansids[$uid]) ? 2 : 0);
								$member_list[$k]['is_follow_relation'] = ($uid == MEMBER_ID ? -1 : ($member_list[$k]['is_follow'] + $member_list[$k]['is_follow_me']));
				if($follow_func) {
					$member_list[$k]['follow_html'] = $follow_func($uid, $member_list[$k]['is_follow'], $member_list[$k]['is_follow_me'],true,$refresh);
				}
			}
		}

		if($is_one_row && $one_row_key) {
			$member_list = $member_list[$one_row_key];
		}

		return $member_list;
	}

	
	function blacklist($touid, $uid) {
		$ret = array();

		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid > 0 && $uid > 0) {
			$ret = DB::fetch_first("SELECT * FROM ".DB::table('blacklist')." WHERE `touid`='$touid' AND `uid`='$uid'");
		}
		return $ret;
	}
	
	function add_blacklist($touid, $uid=MEMBER_ID) {
		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid < 1 || $uid < 1 || $touid == $uid) {
			return false;
		}

		$info = $this->blacklist($touid, $uid);
		if(!$info) {
			$data = array(
				'touid' => $touid,
				'uid' => $uid,
			);
			$ret = DB::insert('blacklist', $data, 1, 1, 1);
			if($ret) {
				$this->del_info($touid, $uid);

				$this->del_info($uid, $touid);
			}
		}
		return $info;
	}
	
	function del_blacklist($touid, $uid=MEMBER_ID) {
		$ret = false;
		$touid = (is_numeric($touid) ? $touid : 0);
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($touid < 1 || $uid < 1) {
			return false;
		}

		$info = $this->blacklist($touid, $uid);
		if($info) {
			$ret = DB::query("DELETE FROM ".DB::table('blacklist')." WHERE `id`='{$info['id']}'");
		}
		return $ret;
	}

	

	function check_new_recd_topic($uid=MEMBER_ID,$tid=0)
	{
		$uid = (int) ($uid ? $uid :MEMBER_ID);
		$tid = $tid ? (int)$tid :0;
		if($uid < 1) {
			return 0;
		}

		$info = jsg_member_info($uid);
		if(!$info) {
			return 0;
		}
		$t = $info['close_recd_time'];
		$cache_key = 'check_new_recd_topic-' . $t;
		if(false === ($row = cache_db('mget', $cache_key))) {
			$row = DB::fetch_first("select tid from ".TABLE_PREFIX."topic_recommend where dateline > '{$t}' and recd=4 and (expiration>".time()." OR expiration=0) order by `dateline` desc limit 1");
			$row = ($row ? $row : array());

			cache_db('mset', $cache_key, $row, 300);
		}
		if($row && $row['tid'] && $tid != $row['tid']){
			return $row['tid'];
		}else{
			return 0;
		}
	}

	
	function check_new_topic($uid=MEMBER_ID, $update_lastactivity=0, $return_tids = 0, $all_topic_notice = 0) {
		$uid = (int) ($uid ? $uid :MEMBER_ID);
		if($uid < 1) {
			return 0;
		}

		$info = jsg_member_info($uid);
		if(!$info) {
			return 0;
		}
		$t = $info['lastactivity'];

		$count = 0;$tids = array();
		if($t > 0 && ($t + 29 < TIMESTAMP)) {
			if($all_topic_notice) {
				$sql = TABLE_PREFIX."topic` WHERE `type`!='reply' AND `dateline`>'{$t}'";
				$count = DB::result_first("SELECT COUNT(1) AS `count` FROM `".$sql);
				if($return_tids) {
					$query = DB::query("SELECT tid FROM `".$sql);
					while (false != ($row = DB::fetch($query))) {
						$tids[] = $row['tid'];
					}
				}
			} else {
				$p = array(
					'uid' => $uid,
					'count' => 100,
					'buddy_lastuptime' => $t,
				);
				$buddy_uids = $this->get_buddyids($p); 				
				if($buddy_uids) {
					$sql = TABLE_PREFIX."topic` WHERE `uid` IN ('".implode("','",$buddy_uids)."') AND `type`!='reply' AND `dateline`>'{$t}'";
					$count = DB::result_first("SELECT COUNT(1) AS `count` FROM `".$sql);
					if($return_tids){
						$query = DB::query("SELECT tid FROM `".$sql);
						while (false != ($row = DB::fetch($query))) {
							$tids[] = $row['tid'];
						}
					}
				}
			}
		}
		if($update_lastactivity) {
			jtable('members')->update_count($uid, 'lastactivity', TIMESTAMP);
		}
		if($return_tids){
			return array('count'=>$count,'tids'=>$tids);
		}else{
			return $count;
		}
	}

	
	function _param_id($d) {
		if(is_string($d)) {
			$d = explode(',', str_replace(array("'", '"'), '', $d));
		}
		if($d) {
			$d = (array) $d;
		}
		return $d;
	}
}

?>