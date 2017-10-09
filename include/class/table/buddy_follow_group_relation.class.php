<?php
/**
 *
 * 数据表 buddy_follow_group_relation 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_follow_group_relation.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_buddy_follow_group_relation extends table {

	
	var $_table = 'buddy_follow_group_relation';

	
	var $sub_table_num = 0;

	
	var $_sub_table_by_field = '';

	function table_buddy_follow_group_relation() {
		$this->sub_table_num = jtable('buddy_follow')->sub_table_num;
		$this->_sub_table_by_field = jtable('buddy_follow')->_sub_table_by_field;
		$this->_init_table();
	}

	
	function table_name($uid) {
		return $this->_init_table($uid);
	}

	
	function row($uid, $touid, $gid) {
		$rets = array();
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		$gid = jfilter($gid, 'int');
		if($uid > 0 && $touid > 0 && $gid > 0) {
			$p = array(
				'uid' => $uid,
				'touid' => $touid,
				'gid' => $gid,
			);
			$this->_init_table($uid);
			$rets = $this->info($p);
		}
		return $rets;
	}

	
	function add($uid, $touid, $gid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		$gid = jfilter($gid, 'int');
		$row = $this->row($uid, $touid, $gid);
		if(!$row) {
			$g_info = jtable('buddy_follow_group')->info($gid);
			if($g_info && $uid == $g_info['uid']) {
				$this->_init_table($uid);
				$ret = $this->insert(array(
					'uid' => $uid,
					'touid' => $touid,
					'gid' => $gid,
					'dateline' => TIMESTAMP,
				));
				$this->_update_count($uid, $gid);
				$this->_set_gids($uid, $touid);
			}
		}
		return $ret;
	}

	
	function del($uid, $touid, $gid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		$gid = jfilter($gid, 'int');
		$row = $this->row($uid, $touid, $gid);
		if($row) {
			$this->_init_table($uid);
			$ret = $this->delete(array(
					'uid' => $uid,
					'touid' => $touid,
					'gid' => $gid,
			));
			$this->_update_count($uid, $gid);
			$this->_set_gids($uid, $touid);
		}
		return $ret;
	}

	
	function del_multi($uid, $touid = 0, $gid = 0) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		if($uid > 0) {
			$p = array(
				'uid' => $uid,
			);
			$touid = jfilter($touid, 'int');
			if($touid > 0) {
				$p['touid'] = $touid;
			}
			$gid = jfilter($gid, 'int');
			if($gid > 0) {
				$p['gid'] = $gid;
			}
			if($touid > 0 && $gid > 0) {
				$ret = $this->del($uid, $touid, $gid);
			} else {
				$this->_init_table($uid);
				$ret = $this->delete($p);
				if($gid > 0) {
					$this->_update_count($uid, $gid);
				}
				if($touid > 0) {
					$this->_set_gids($uid, $touid);
				}
			}
		}
		return $ret;
	}

	
	function get_my_group_uids($uid, $gid, $p = array()) {
		$rets = array();
		$uid = jfilter($uid, 'int');
		$gid = jfilter($gid, 'int');
		if($uid > 0 && $gid > 0 && ($group_info = jtable('buddy_follow_group')->info($gid)) && $group_info['uid'] == $uid) {
			settype($p, 'array');
			if(!isset($p['result_count'])) {
				$p['result_count'] = $group_info['count'];
			}
			$p['uid'] = $uid;
			$p['gid'] = $gid;
			$this->_init_table($uid);
			$rets = $this->get_ids($p, 'touid');
		}
		return $rets;
	}

	
	function get_user_group($uid, $touid) {
		$rets = array();
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		if($uid > 0 && $touid > 0) {
			$rets = DB::fetch_all("SELECT G.id, G.name, GR.gid, GR.dateline FROM ".DB::table($this->_init_table($uid))." GR
				LEFT JOIN ".DB::table("buddy_follow_group")." G ON G.id=GR.gid WHERE GR.uid='$uid' AND GR.touid='$touid'");
		}
		return $rets;
	}

	function get_user_group_ids($uid, $touid) {
		$ids = array();
		$list = $this->get_user_group($uid, $touid);
		if($list) {
			foreach ($list as $row) {
				$ids[$row['id']] = $row['id'];
			}
		}
		return $ids;
	}

	
	function _update_count($uid, $gid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$gid = jfilter($gid, 'int');
		if($uid > 0 && $gid > 0) {
			$this->_init_table($uid);
			$count = $this->count(array(
				'gid' => $gid,
				'uid' => $uid,
			));
			$ret = jtable('buddy_follow_group')->update_count($gid, 'count', $count);
			if($ret) {
				jtable('buddy_follow_group')->_rm_my_cache($uid);
			}
		}
		return $ret;
	}

	
	function _set_gids($uid, $touid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		if($uid > 0 && $touid > 0) {
			$this->_init_table($uid);
			$rets = $this->get_ids(array(
				'uid' => $uid,
				'touid' => $touid,
			), 'gid');
			$gids = $rets ? implode(',', $rets) : '';
			$ret = jtable('buddy_follow')->set_gids($uid, $touid, $gids);
		}
		return $ret;
	}

	
	function _init_table($uid = 0) {
		$table = $this->_table;
		if($this->sub_table_num > 1 && $uid > 0) {
			$table_id = jtable('buddy_follow_table_id')->table_id($uid, $this->sub_table_num);
			if(false !== $table_id) {
				$table .= '_' . $table_id;
			}
			$this->copy($table);
		}
		$this->init($table);

		return $table;
	}

}

?>