<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename friend.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 640109832 9947 $
 */




class FriendLogic
{
	var $TopicLogic;
	var $TopicListLogic;
	var $Config;

	function FriendLogic()
	{
		Load::logic('topic');
		$this->TopicLogic = new TopicLogic();
		$this->Config = jconf::get();
	}

		function getFollowList($param)
	{
		$id = max(0, intval($param['max_id']));
		$max_id = $id + 1;
		$uid = intval($param['uid']);
		if (empty($uid)) {
			$uid = MEMBER_ID;
			$member = $this->TopicLogic->GetMember($uid);
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

		$limit = 20;
		if ($param['limit'] > 0) {
			$limit = $param['limit'];
		}

				$count = $member['follow_count'];
		if ($count > 0) {
			$offset = ($id * $limit);
			
			$sql = "SELECT b.remark,m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city
					FROM ".DB::table(jtable('buddy_follow')->table_name($member['uid']))." AS b
					LEFT JOIN ".DB::table('members')." AS m
					ON m.`uid` = b.`touid`
					WHERE b.`uid`='{$member['uid']}' 
					LIMIT {$offset}, {$limit}";
			$query = DB::query($sql);
			while ($row = $query->GetRow()) {
				$member_list[$row['uid']] = jsg_member_make($row);
			}
			if (empty($member_list)) {
								return 401;
			} else {
												$friendships = array(-1=>1, 0=>0, 1=>2, 2=>0, 3=>4);
				$_tmp_arr = buddy_follow_html($member_list);
				foreach($_tmp_arr as $k=>$row) {
					$member_list[$k]['friendship'] = $friendships[$row['is_follow_relation']];
				}
				unset($_tmp_arr);

				$member_list = array_values($member_list);
				$ret = array(
					'member_list' => $member_list,
					'total_record' => $count,
					'list_count' => count($member_list),
					'max_id' => $max_id,
				);
				return $ret;
							}
		}
				return 400;
	}

		function getFansList($param)
	{
		$id = max(0, intval($param['max_id']));
		$max_id = $id + 1;
		$uid = intval($param['uid']);
		if (empty($uid)) {
			$uid = MEMBER_ID;
			$member = $this->TopicLogic->GetMember($uid);
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

		$limit = 20;
		if ($param['limit'] > 0) {
			$limit = $param['limit'];
		}

		$count = $member['fans_count'];
		if ($count > 0) {
			$offset = ($id * $limit);

			$sql = "SELECT m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city
					FROM ".DB::table(jtable('buddy_fans')->table_name($member['uid']))." AS b
					LEFT JOIN ".DB::table('members')." AS m
					ON m.`uid` = b.`touid`
					where b.`uid`='{$member['uid']}'
					ORDER BY b.`dateline` DESC
					LIMIT {$offset}, {$limit}";
			$query = DB::query($sql);
			$member_list = array();
			while ($row = DB::fetch($query)) {
				$member_list[$row['uid']] = jsg_member_make($row);
			}
			if (empty($member_list)) {
								return 401;
			} else {
												$friendships = array(-1=>1, 0=>0, 1=>2, 2=>0, 3=>4);
				$_tmp_arr = buddy_follow_html($member_list);
				foreach($_tmp_arr as $k=>$row) {
					$member_list[$k]['friendship'] = $friendships[$row['is_follow_relation']];
				}
				unset($_tmp_arr);

				$member_list = array_values($member_list);
				$ret = array(
					'member_list' => $member_list,
					'total_record' => $count,
					'list_count' => count($member_list),
					'max_id' => $max_id,
				);
				return $ret;
							}
		}
				return 400;
	}

		function addFollow($uid)
	{
				if ($uid == MEMBER_ID) {
									return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

		$info = jlogic('buddy')->info($uid, MEMBER_ID);
		if (!$info) {
        	buddy_add($uid, MEMBER_ID);
    		    		return 200;
		}
						return 310;
	}

		function delFollow($uid)
	{
				if ($uid == MEMBER_ID) {
									return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

		$info = jlogic('buddy')->info($uid, MEMBER_ID);
		if ($info) {
			buddy_del($uid, MEMBER_ID);
    		    		return 200;
		}
						return 311;
	}

		function checkFollow($uid) {
		if ($uid == MEMBER_ID) {
			return 401;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
				return 300;
			}
		}
		$isBlackList = $this->check($uid);
		if ($isBlackList) {
			return -1;
		}

		if (($follow_info = jclass('buddy_follow')->row(MEMBER_ID, $uid)) &&
			($fans_info = jclass('buddy_follow')->row($uid, MEMBER_ID))) {
			return 2;
		} else if ($follow_info) {
			return 1;
		}
		return 0;
	}

		function getBlackList($param)
	{
		$uid = MEMBER_ID;
		$id = intval($param['max_id']);
		$where_sql = " bl.uid='{$uid}' ";
		$order_sql = " bl.id DESC ";
		$limit = intval($param['limit']);
		$count = DB::result_first("SELECT COUNT(*)
								   FROM ".DB::table('blacklist')." AS bl
								   LEFT JOIN ".DB::table("members")." AS m
								   USING(uid)
								   WHERE {$where_sql}");
		if ($count > 0) {
			$member_list = array();
			if ($id > 0) {
				$where_sql .= " AND bl.id<{$id} ";
			}
			$query = DB::query("SELECT bl.id AS bl_id,m.uid,m.nickname,m.username,m.face,m.fans_count,m.signature,m.topic_count,m.province,m.city
								FROM ".DB::table('blacklist')." AS bl
								LEFT JOIN ".DB::table("members")." AS m
								ON bl.touid = m.uid
								WHERE {$where_sql}
								ORDER BY {$order_sql}
								LIMIT  {$limit} ");
			while ($row = DB::fetch($query)) {
				$raw = jsg_member_make($row);
				$raw['friendship'] = -1;
				$member_list[] = $raw;
							}
			if (empty($member_list)) {
								return 401;
			} else {
				$member_list = array_values($member_list);
				$tmp_ary = $member_list;
				$tmp = array_pop($tmp_ary);
				$max_id = $tmp['bl_id'];
				$r = array(
					'total_record' => $count,
					'member_list' => $member_list,
					'max_id' => $max_id,
				);
								return $r;
			}
		}
				return 400;
	}

		function addBlacklist($uid)
	{
				if ($uid == MEMBER_ID) {
									return 402;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

				$touid = $uid;
		$uid = MEMBER_ID;

		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='{$uid}' AND touid='{$touid}'");
		if ($count == 0) {
			$sql = "insert into `".TABLE_PREFIX."blacklist` (`uid`,`touid`) values ('{$uid}','{$touid}')";
			DB::query($sql);

						buddy_del($touid, $uid);
			buddy_del($uid, $touid);

						return 200;
		}
				return 312;
	}

		function delBlacklist($uid)
	{
				if ($uid == MEMBER_ID) {
									return 402;
		} else {
			$member = $this->TopicLogic->GetMember($uid);
			if (empty($member)) {
												return 300;
			}
		}

				$touid = $uid;
		$uid = MEMBER_ID;

		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='{$uid}' AND touid='{$touid}'");
		if ($count > 0) {
			$sql = "delete from `".TABLE_PREFIX."blacklist` where `touid`='{$touid}' and `uid` = '".MEMBER_ID."'";
			DB::query($sql);
						return 200;
		}
				return 313;
	}

	function check($uid)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('blacklist')." WHERE uid='".MEMBER_ID."' AND touid='{$uid}'");
		return $count > 0 ? true : false;
	}



}


?>