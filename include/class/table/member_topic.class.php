<?php
/**
 *
 * 数据表 member_topic 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member_topic.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_member_topic extends table {

	
	var $_table = 'member_topic';

	
	var $_sub_table_by_field = 'uid';

	function table_member_topic() {
		$this->_init_table();
	}

	function table_name($id = 0, $type = 0) {
		return $this->_init_table($id, $type);
	}

	
	function get_tids($uids, $p = array(), $more = 0) {
		$rets = array();
		settype($p, 'array');
		if(!isset($p['type'])) {
			$p['type'] = array('both', 'forward');
		} else {
			if(is_array($p['type'])) {
				sort($p['type']);
				if(array('both', 'forward', 'reply') == $p['type']) {
					unset($p['type']);
				}
			} else {
				if('all' == $p['type']) {
					unset($p['type']);
				}
			}
		}
		if(!$p['sql_order']) {
			$p['sql_order'] = ' `dateline` DESC ';
		}
		if(is_numeric($uids)) {
			$uid = jfilter($uids, 'int');
			if($uid > 0) {
				$p['uid'] = $uid;
				$this->_init_table($uid);
				$rets = $this->get_ids($p, 'tid', $more);
			}
		} else {
			if($uids) {
				$p = $this->_get_tids_maps($uids, $p);
				$rets = $this->get_ids($p, 'tid', $more);
			}
		}
		return $rets;
	}

	function row($uid, $tid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$tid = jfilter($tid, 'int');
		if($uid > 0 && $tid > 0) {
			$this->_init_table($uid);
			$ret = $this->info(array(
				'uid' => $uid,
				'tid' => $tid
			));
		}
		return $ret;
	}

	function add($tid) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && ($row = jtable('topic')->row($tid))) {
			$uid = (int) $row['uid'];
			if($uid > 0 && !$this->row($uid, $tid)) {
				$this->_init_table($uid);
				$ret = $this->insert(array(
					'uid' => $uid,
					'tid' => $tid,
					'type' => $row['type'],
					'totid' => $row['totid'],
					'touid' => $row['touid'],
					'dateline' => $row['dateline'] ? $row['dateline'] : TIMESTAMP,
				)) ? true : false;
				if($ret) {
					if($row['totid'] > 0) {
						jtable('member_relation')->add($tid);
					}
					$this->archive($uid);
				}
			}
		}
		return $ret;
	}

	function update_replys($uid, $tid, $replys) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$tid = jfilter($tid, 'int');
		if($uid > 0 && $tid > 0 && ($row = $this->row($uid, $tid))) {
			$this->_init_table($uid);
			$ret = $this->update_count(array('uid' => $uid, 'tid' => $tid), 'replys', $replys, 1);
		}
		return $ret;
	}

	function update_digcounts($uid, $tid, $digcounts) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		$tid = jfilter($tid, 'int');
		if($uid > 0 && $tid > 0 && ($row = $this->row($uid, $tid))) {
			$this->_init_table($uid);
			$ret = $this->update_count(array('uid' => $uid, 'tid' => $tid), 'digcounts', $digcounts, 1, array('lastdigtime' => TIMESTAMP));
		}
		return $ret;
	}

	
	function archive($uid = 0) {
		$num = 200000;		$uid = jfilter($uid, 'int');
		if($uid > 0 && (($uid - $GLOBALS['_J']['config']['last_archive_member_uid']) > $num)) {
						$max_uid = $num + max(0, (int) $GLOBALS['_J']['config']['last_archive_member_uid']);
			if(!jtable('member_table_id')->table_id($max_uid)) {								$table_id = jtable('member_table_id')->add($max_uid);
				if($table_id) {
										jtable($this->_table)->copy($this->_table . '_' . $table_id, 3, array('<=@uid' => $max_uid));
										jtable('member_relation')->copy('member_relation_' . $table_id, 3, array('<=@touid' => $max_uid));
										jconf::update('last_archive_member_uid', $max_uid);
				}
			}
		}
	}

	
	function _get_tids_maps($uids, $p = array()) {
		settype($uids, 'array');
		settype($p, 'array');
		$rets = jtable('member_table_id')->get_maps();
		if($rets && ($maps = $rets['list'])) {
			$maps_count = $rets['count'];
			$table_id = $rets['table_id'];
			if($maps_count < 2) {
				$p['uid'] = $uids;
				$this->_init_table($table_id, 2);
			} else {
				$_maps = array();
				foreach($uids as $k=>$uid) {
					$uid = jfilter($uid, 'int');
					if($uid > 0) {
						foreach($maps as $map) {
							if($uid >= $map['min'] && $uid < $map['max']) {
								$table_id = $map['id'];
								$_maps[$table_id][$uid] = $uid;
								break;
							}
						}
					} else {
						unset($uids[$k]);
					}
				}
				if($_maps) {
					$_maps_count = count($_maps);
					if($_maps_count > 1) {
						$_p = $p;
						$_p['return_sql'] = 1;
						$_rs = array();
						foreach($_maps as $table_id=>$_uids) {
							$_p['uid'] = $_uids;
							$this->_init_table($table_id, 2);
							$_rs[$table_id] = $this->get_ids($_p, 'tid', 1);
						}
						$p['sql_table'] = " ((" . implode(") UNION ALL (", $_rs) . ")) `{$this->_table}_union_all_table_alias` ";
					} else {
						$p['uid'] = $_maps[$table_id];
						$this->_init_table($table_id, 2);
					}
				}
			}
		} else {
			$p['uid'] = $uids;
			$this->_init_table();
		}
		return $p;
			}

	
	function _init_table($id = 0, $type = 0) {
		$table = $this->_table;
		if(2 == $type) {
			$table_id = $id;
		} else {
			if(jtable('member_table_id')->get_maps()) {
				$table_id = jtable('member_table_id')->table_id($id, $type);
			}
		}
		if($table_id) {
			$table = $this->_table . '_' . $table_id;
		}
		$this->init($table);
		return $table;
	}

}

?>