<?php
/**
 *
 * 数据表 buddy_follow 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_follow.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_buddy_follow extends table {
	
	
	var $_table = 'buddy_follow';
	
	
	var $sub_table_num = 10;
	
	
	var $_sub_table_by_field = 'uid';
	
	function table_buddy_follow() {
		$this->sub_table_num = $GLOBALS['_J']['config']['table'][$this->_table]['sub_table_name'];
		$this->_init_table();
	}
	
	
	function table_name($uid) {
		return $this->_init_table($uid);
	}
	
	
	function row($uid, $touid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		if($uid < 1 || $touid < 1) {
			return $ret;
		}
		$this->_init_table($uid);
		$p = array(
			'uid' => $uid,
			'touid' => $touid,
		);
		$ret = $this->info($p);
		return $ret;
	}
	
	
	function add($uid, $touid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		if($uid > 0 && $touid > 0 && $uid != $touid) {
			$row1 = $this->row($uid, $touid);
			$row2 = $this->row($touid, $uid);
			$relation = ($row2 ? 3 : 1);
			if(!$row1) {
				$this->_init_table($uid);
				$ret = $this->insert(array(
					'uid' => $uid,
					'touid' => $touid,
					'relation' => $relation,
					'dateline' => TIMESTAMP,
				));
			}
			if($row1 && $relation != $row1['relation']) {
				$this->_set_relation($uid, $touid, $relation);
			}
			if($row2 && $relation != $row2['relation']) {
				$this->_set_relation($touid, $uid, $relation);
			}
		}
		return $ret;
	}
	
	
	function del($uid, $touid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$touid = jfilter($touid, 'int');
		if($uid > 0 && $touid > 0) {
			$row1 = $this->row($uid, $touid);
			if($row1) {
				$this->_init_table($uid);
				$ret = $this->delete(array(
					'uid' => $uid,
					'touid' => $touid,
				), 1);
				if($row1['gids']) {
					jtable('buddy_follow_group_relation')->del_multi($uid, $touid);
					$gids = explode(',', $row1['gids']);
					foreach($gids as $gid) {
						$gid = jfilter($gid, 'int');
						if($gid > 0) {
							jtable('buddy_follow_group')->update_count($gid, 'count', '-1');
						}
					}
				}
			}
			$row2 = $this->row($touid, $uid);
			if($row2 && 3 == $row2['relation']) {
				$this->_set_relation($touid, $uid, 1);
			}
		}
		return $ret;
	}
	
	
	function set_remark($uid, $touid, $remark = '') {
		$ret = false;
		$row = $this->row($uid, $touid);
		if($row) {
			$remark = jfilter($remark, 'txt');
			$remark = cutstr($remark, 30);
			$f_rets = filter($remark);
			if($f_rets && $f_rets['error']) {
				$remark = '';
			}
			if($remark != $row['remark']) {
				$this->_init_table($uid);
				$ret = $this->update(array(
					'remark' => $remark
				), array('uid'=>$uid, 'touid'=>$touid));
			}
		}
		return $ret;
	}
	
	
	function set_gids($uid, $touid, $gids = '') {
		$ret = false;
		$row = $this->row($uid, $touid);
		if($row && $gids != $row['gids']) {
			$this->_init_table($uid);
			$ret = $this->update(array(
				'gids' => $gids
			), array('uid'=>$uid, 'touid'=>$touid));
		}
		return $ret;
	}
	
	
	function _set_relation($uid, $touid, $relation = 3) {
		$ret = false;
		$row = $this->row($uid, $touid);
		if($row) {
			$relation = (3 == $relation ? 3 : 1);
			if($relation != $row['relation']) {
				$this->_init_table($uid);
				$ret = $this->update(array(
					'relation' => $relation
				), array('uid'=>$uid, 'touid'=>$touid));
			}
		}
		return $ret;
	}

	function _init_table($uid = 0) {
		$table = $this->_table;
		if($this->sub_table_num > 1 && $uid > 0) {
			$table_id = jtable('buddy_follow_table_id')->table_id($uid, $this->sub_table_num);
			if(false !== $table_id) {
				$table .= '_' . $table_id;
				$this->copy($table);
			}
		}
		$this->init($table);
		
		return $table;
	}
	
}

?>