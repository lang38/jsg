<?php
/**
 *
 * 数据表 topic_relation 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_relation.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic_relation extends table {

	
	var $_table = 'topic_relation';

	
	var $_sub_table_by_field = 'totid';

	function table_topic_relation() {
		$this->_init_table();
	}

	function table_name($id = 0, $type = 0) {
		return $this->_init_table($id, $type);
	}

	function row($totid, $tid) {
		$ret = false;
		$totid = jfilter($totid, 'int');
		$tid = jfilter($tid, 'int');
		if($totid > 0 && $tid > 0) {
			$this->_init_table($totid);
			$ret = $this->info(array(
				'totid' => $totid,
				'tid' => $tid,
			));
		}
		return $ret;
	}

	function add($tid, $parents = '') {
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			$row = jtable('topic')->row($tid);
			if($row && $row['totid'] > 0 && ('both' == $row['type'] || 'reply' == $row['type'] || 'forward' == $row['type'])) {
				$parents = $parents ? $parents : jtable('topic_more')->get_parents($tid);
				if($parents) {
					$p = array('lastupdate'=>$row['lastupdate']);
					$k = 'forwards';
					$v = '+1';
					if('reply' == $row['type']) {
						$k = 'replys';
					} elseif('both' == $row['type']) {
						$p['+@replys'] = 1;
					} else {
						unset($p['lastupdate']);
					}

					$totids = explode(',', $parents);
					foreach($totids as $totid) {
						$totid = jfilter($totid, 'int');
						if($totid > 0 && ($torow = jtable('topic')->row($totid))) {
														if('forward' != $row['type'] && !$this->row($totid, $tid)) {
								$this->_init_table($totid);
								$ret = $this->insert(array(
									'totid' => $totid,
									'touid' => $torow['uid'],
									'tid' => $tid,
									'uid' => $row['uid'],
									'type' => $row['type'],
									'dateline' => $row['dateline'],
								));
							}
														jtable('topic')->update_count($totid, $k, $v, 1, $p);
							jtable('member_topic')->update_count(array('uid'=>$torow['uid'], 'tid'=>$totid),
								$k, $v, 1, $p);
						}
					}
				}
				$this->archive($row['totid']);
			}
		}
	}

	function rm($totid, $tid) {
		$ret = false;
		$totid = jfilter($totid, 'int');
		$tid = jfilter($tid, 'int');
		if($totid > 0 && $tid > 0 && $this->row($totid, $tid)) {
			$this->_init_table($totid);
			$ret = $this->delete(array(
				'totid' => $totid,
				'tid' => $tid,
			)) ? true : false;
			$this->update_replys($totid);
		}
		return $ret;
	}

	function update_replys($totid, $count = null) {
		$ret = false;
		$totid = jfilter($totid, 'int');
		if($totid > 0 && ($torow = jtable('topic')->row($totid))) {
			$count = isset($count) ? (int) $count : $this->count(array('totid'=>$totid));
			$this->_init_table($totid);
			$ret = jtable('topic')->update_replys($totid, $count) ? true : false;
			if($ret) {
				jtable('member_topic')->update_replys($torow['uid'], $totid, $count);
			}
		}
		return $ret;
	}

	function update_digcounts($tid, $digcounts = '+1') {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && ($row = jtable('topic')->row($tid))) {
			$parents = jtable('topic_more')->get_parents($tid);
			if($parents) {
				$totids = explode(',', $parents);
				foreach($totids as $totid) {
					$totid = jfilter($totid, 'int');
					if($totid > 0 && $this->row($totid, $tid)) {
						$this->_init_table($totid);
						$ret = $this->update_count(array('totid' => $totid, 'tid' => $tid), 'digcounts', $digcounts, 1, array('lastdigtime' => TIMESTAMP)) ? true : false;
					}
				}
			}
			jtable('member_topic')->update_digcounts($row['uid'], $tid, $digcounts);
		}
		return $ret;
	}

	
	function get_tids($totid, $p = array(), $more = 0) {
		settype($p, 'array');
		$totid = jfilter($totid, 'int');
		if($totid < 1) {
			return false;
		}
		$row = jtable('topic')->row($totid);
		if(!$row) {
			return false;
		}
		$p['totid'] = $totid;
		if(!$p['sql_order']) {
			$p['sql_order'] = ' `dateline` ASC ';
		}
		$this->_init_table($totid);
		$rets = $this->get_ids($p, 'tid', $more);
		if($more && !isset($p['result_count']) && $rets['count'] != $row['replys']) {
			$this->update_replys($totid, $rets['count']);
		}
		return $rets;
	}

	
	function get_list($totid, $p = array()) {
		settype($p, 'array');
		$totid = jfilter($totid, 'int');
		if($totid < 1) {
			return false;
		}
		$p['totid'] = $totid;
		if(!$p['sql_order']) {
			$p['sql_order'] = ' `dateline` DESC ';
		}
		$rets = $this->get_tids($totid, $p, 1);
		if($rets['count'] < 1 || empty($rets['ids'])) {
			return array();
		}
				$reply_building = (int) ($GLOBALS['_J']['config']['reply_mode_normal'] ? 0 : max(1, (int) $GLOBALS['_J']['config']['reply_mode_gentie']));
		if(!$reply_building) {			$rets['list'] = (($rets['count'] > 0 && $rets['ids']) ? jlogic('topic')->Get($rets['ids']) : array());
			if($rets['list']) {
				$rets['parent_list'] = jlogic('topic')->GetParentTopic($rets['list'], 1);
			}
		} else {						$rps = (($rps = jtable('topic_more')->get_parents($totid)) ? $rps . ',' : '') . $totid;
			$tps = $ps = $list = array();
			foreach($rets['ids'] as $tid) {				$tp = jtable('topic_more')->get_parents($tid);
				if($tp == $rps) {
					$tp = '';
				} else {
					if($rps . ',' == substr($tp, 0, ($rpsl = strlen((string) $rps)) + 1)) {
						$tp = substr($tp, $rpsl + 1);
					}
				}
				if($tp) {
					$ps[$tp][$tid] = $tid;
				}
				$tps[$tid] = $tp;
			}
						foreach($tps as $tid=>$tp) {				$pp = ($tp ? $tp . ',' : '') . $tid;
				if($rps != $pp && isset($ps[$pp])) {
					unset($tps[$tid]);
				}
			}
						if($tps) {
				$building = 0;
				foreach($tps as $tid=>$tp) {					if(($row = jtable('topic')->row($tid))) {
						if($tp) {							$pl = $pls = array();
							$count = $floor = 0;							$_tids = explode(',', $tp);
							foreach($_tids as $_tid) {
								$count++;
								if($reply_building > 0 && $building >= $reply_building && $floor >= 1) {
									$pl[$_tid] = array(
										'parents_list_lazyload' => 1,
									);
									$pls[$_tid] = $_tid;
																		if(($__tid = end($_tids)) > 0 && ($__row = jtable('topic')->row($__tid))) {
										$pl[$__tid] = $__row;
										$pls[$__tid] = $__tid;
									}
									break;								} else {
									if($floor <= 100) {										if(($_row = jtable('topic')->row($_tid))) {
											$_row['floor'] = ++$floor;
											$pl[$_tid] = $_row;
											$pls[$_tid] = $_tid;
										}
									} else {
										break;
									}
								}
							}
							if($pl) {
								$building++;
								$row['floor'] = ++$floor;
								$row['parents_count'] = $count;
								krsort($pls);
								$row['parents_ids'] = $pls;
								$row['parents_list'] = jlogic('topic')->MakeAll($pl);
							}
						}
						$list[$tid] = $row;
					}
				}
			}
			if($list) {
				$rets['list'] = jlogic('topic')->MakeAll($list);
			}
		}
		return $rets;
	}

	
	function archive($tid = 0) {
		$num = 500000;		$tid = jfilter($tid, 'int');
		if($tid > 0 && $tid - $GLOBALS['_J']['config']['last_archive_topic_relation_tid'] > $num) {
						$max_tid = $num + max(0, (int) $GLOBALS['_J']['config']['last_archive_topic_relation_tid']);
			if(!jtable('topic_relation_table_id')->table_id($max_tid)) {								$table_id = jtable('topic_relation_table_id')->add($max_tid);
				if($table_id) {
					$table_name = $this->_table . '_' . $table_id;
										jtable($this->_table)->copy($table_name, 3, array('<=@totid' => $max_tid));
										jconf::update('last_archive_topic_relation_tid', $max_tid);
				}
			}
		}
	}

	
	function _init_table($id = 0, $type = 0) {
		$table = $this->_table;
		if(2 == $type) {
			$table_id = $id;
		} else {
			if(jtable('topic_relation_table_id')->get_maps()) {
				$table_id = jtable('topic_relation_table_id')->table_id($id, $type);
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