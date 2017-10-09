<?php
/**
 *
 * 数据表 topic 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic extends table {

	
	var $_table = 'topic';

	
	var $_sub_table_by_field = 'tid';

	function table_topic() {
		$this->_init_table();
	}

	function table_name($id = 0, $type = 0) {
		return $this->_init_table($id, $type);
	}

	function row($tid) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			$this->_init_table($tid);
			$ret = $this->info($tid);
		}
		return $ret;
	}

	function get_list($tids, $p = array()) {
		$list = array();
		if($tids) {
			settype($tids, 'array');
			foreach($tids as $tid) {
				if(!isset($list[$tid]) && ($row = $this->row($tid))) {
					$list[$tid] = $row;
				}
			}
		}
		return $list;
	}

	function add($data) {
		;
	}

	
	function rm($tid) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && ($row = $this->row($tid))) {
			jtable('member_topic')->delete(array('uid'=>$row['uid'], 'tid'=>$tid));			if($row['totid'] > 0) {
				if($row['touid'] > 0) {
					jtable('member_relation')->delete(array('touid'=>$row['touid'], 'tid'=>$tid));				}
				if('forward' != $row['type']) {
					jtable('topic_relation')->delete(array('totid'=>$row['totid'], 'tid'=>$tid));				}
				$topic_more = jtable('topic_more')->row($tid);
				if($topic_more['parents']) {					$p = array();
					$k = 'forwards';
					$v = '-1';
					if('reply' == $row['type']) {
						$k = 'replys';
					} elseif ('both' == $row['type']) {
						$p['-@replys'] = 1;
					}
					$totids = explode(',', $topic_more['parents']);
					foreach($totids as $totid) {
						$totid = jfilter($totid, 'int');
						if($totid > 0 && ($torow = $this->row($totid))) {
							$this->update_count($totid, $k, $v, 1, $p);
							jtable('member_topic')->update_count(array('uid'=>$torow['uid'], 'tid'=>$totid),
								$k, $v, 1, $p);
						}
					}
				}
			}
			if('reply' != $row['type']) {
				jtable('members')->update_count($row['uid'], 'topic_count', '-1');			}
			$this->delete($tid);		}
		return $ret;
	}

	function update_replys($tid, $replys) {
		return $this->_update_count($tid, 'replys', $replys);
	}

	function update_forwards($tid, $forwards) {
		return $this->_update_count($tid, 'forwards', $forwards);
	}

	function update_digcounts($tid, $digcounts = '+1') {
		$ret = $this->_update_count($tid, 'digcounts', $digcounts, array('lastdigtime'=>TIMESTAMP, 'lastdiguid'=>MEMBER_ID, 'lastdigusername'=>MEMBER_NICKNAME));
		if($ret) {
			jtable('topic_relation')->update_digcounts($tid, $digcounts);
		}
		return $ret;
	}

	function update_lastupdate($tid, $lastupdate) {
		return $this->_update_count($tid, 'lastupdate', $lastupdate);
	}

	function _update_count($tid, $key, $val, $p = array()) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && $key && is_numeric($val)) {
			$this->_init_table($tid);
			$ret = $this->update_count($tid, $key, $val, 1, $p) ? true : false;
		}
		return $ret;
	}

	function archive($tid = 0) {
		$num = 1000000; 		$keep = 200000; 		$tid = jfilter($tid, 'int');
		if($tid > 0 && $tid - $GLOBALS['_J']['config']['last_archive_topic_tid'] > $num + $keep) {
			$max_tid = $num + max(0, (int) $GLOBALS['_J']['config']['last_archive_topic_tid']);
			if(!jtable('topic_table_id')->table_id($max_tid)) { 								$table_id = jtable('topic_table_id')->add($max_tid);
				if($table_id) {
					$p = array('<=@tid' => $max_tid);
										jtable('topic')->copy($this->_table . '_' . $table_id, 3, $p);
										jtable('topic_more')->copy('topic_more_' . $table_id, 3, $p);
										jconf::update('last_archive_topic_tid', $max_tid);
				}
			}
		}
	}

	
	function _init_table($id = 0, $type = 0) {
		$table = $this->_table;
		if(2 == $type) {
			$table_id = $id;
		} else {
			if(jtable('topic_table_id')->get_maps()) {
				$table_id = jtable('topic_table_id')->table_id($id, $type);
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