<?php
/**
 *
 * 好友粉丝相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_follow_group.logic.php 3836 2013-06-08 07:49:13Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class BuddyFollowGroupLogic {

	var $table = 'buddy_follow_group';
	var $db = null;

	function BuddyFollowGroupLogic() {
		$this->db = jtable($this->table);
	}

	function get_my_group_info($uid, $gid) {
		$uid = (int) $uid;
		$gid = (int) $gid;
		if($uid > 0 && $gid > 0 && (jallow($uid))) {
			$rets = $this->db->info($gid);
			if($rets && $rets['uid'] == $uid) {
				return $rets;
			}
		}
		return array();
	}

	
	function get_my_group($uid, $limit = 0) {
		$list = array();
		$uid = jfilter($uid, 'int');
		if($uid > 0 && (jallow($uid))) {
			if($limit < 1) {
				$cache_key = $this->table . '-get_my_group-' . $uid;
				$cache_time = 300;
				if(false !== ($list = cache_db('mget', $cache_key))) {
					return $list;
				}
			}
			$limit = jfilter($limit, 'int');
			$p = array(
				'uid' => $uid,
				'sql_order' => '`count` DESC, `order` ASC, `id` ASC',
				'result_count' => ($limit > 0 ? $limit : 999)
			);
			$rets = $this->db->get($p);
			$list = array();
			if($rets['list']) {
				foreach($rets['list'] as $row) {
					$list[$row['id']] = $row;
				}
			}
			if($cache_time > 0) {
				cache_db('mset', $cache_key, $list, $cache_time);
			}
		}
		return $list;
	}

	
	function get_group_list($gids, $my_group_list) {
		if(empty($gids) || empty($my_group_list)) {
			return false;
		}
		if(is_numeric($my_group_list)) {
			$my_group_list = $this->get_my_group($my_group_list);
		}
		$list = array();
		if($my_group_list && is_array($my_group_list)) {
			$ids = array();
			if(is_numeric($gids)) {
				$ids = array($gids);
			} elseif (is_string($gids)) {
				$ids = explode(',', $gids);
			} elseif (is_array($gids)) {
				$ids = $gids;
			}
			foreach($ids as $id) {
				if(($row = $my_group_list[$id])) {
					$list[$id] = $row;
				}
			}
		}
		return $list;
	}

}