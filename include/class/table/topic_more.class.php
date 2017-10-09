<?php
/**
 *
 * 数据表 topic_more 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_more.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic_more extends table {
	
	
	var $_table = 'topic_more';
	
	
	var $_sub_table_by_field = 'tid';
	
	function table_topic_more() {
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
	
	function add($tid, $parents = null, $longtext = '') {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && ($row = jtable('topic')->row($tid)) && !$this->row($tid)) {
			if(is_null($parents)) {
				$parents = $row['totid'] > 0 ? $this->get_parents($tid) : '';
			}
			$this->_init_table($tid);
			$ret = $this->insert(array(
				'tid' => $tid,
				'parents' => $parents,
				'longtext' => ($longtext ? $longtext : ($row['content'] . $row['content2'])),
			)) ? true : false;
		}
		return $ret;
	}
	
	function modify($p) {
		$ret = false;
		$tid = jfilter($p['tid'], 'int');
		if($tid > 0 && ($row = $this->row($tid)) && $p != $row) {
			$this->_init_table($tid);
			$ret = $this->update($p) ? true : false;
		}
		return $ret;
	}
	
	function longtext($tid, $longtext = null, $options = null) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			if(is_null($longtext)) {
				$ret = $this->get_longtext($tid, (bool) $options);
			} else {
				$ret = $this->set_longtext($tid, $longtext, (array) $options);
			}
		}
		return $ret;
	}
	
	function get_longtext($tid, $is_verify = 0) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0 && false != ($row = jtable('topic')->row($tid))) {
			if($is_verify) {
				if(($longtextid = $row['longtextid']) > 0) {
					$ret = jlogic('longtext')->longtext($longtextid);
				}
			} else {
				$ret = $this->get_val($tid, 'longtext');
			}
			if(!$ret) {
				$ret = ($row['content'] . $row['content2']);
				if($ret) {
					$this->set_longtext($tid, $ret);
				}
			}
		}
		return $ret;
	}
	
	function get_parents($tid) {
		$ret = '';
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			if($this->row($tid)) {
				$ret = $this->get_val($tid);
			} else {
				if(($row = jtable('topic')->row($tid)) && $row['totid'] > 0) {
					$ret = ($p = $this->get_val($row['totid'])) ? $p . ',' . $row['totid'] : $row['totid'];
				}
			}
		}
		return $ret;
	}
	
	function get_val($tid, $key='parents') {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			$this->_init_table($tid);
			$ret = $this->val($tid, $key);
		}
		return $ret;
	}
	
	function set_longtext($tid, $longtext, $p = array()) {
		return $this->set_val($tid, $longtext, 'longtext', $p);
	}
	
	function set_val($tid, $val, $key='longtext', $p = array()) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			settype($p, 'array');
			$p[$key] = $val;
			$this->_init_table($tid);
			$ret = $this->update($p, array('tid'=>$tid));
		}
		return $ret;
	}
	
	function update_replyidscount($tid, $count = 0) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			$this->_init_table($tid);
			$ret = $this->update_count($tid, 'replyidscount', $count);
		}
		return $ret;
	}
	
	function update_diguids($tid, $diguids = null, $act = 'add', $uid = MEMBER_ID) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			if(is_null($diguids)) {
								$diguids = jtable('topic_dig')->get_ids(array(
					'result_count' => 999,
					'tid' => $tid,
					'sql_order' => ' `id` DESC ',
				), 'uid');
				$uid = jfilter($uid, 'int');
				if('add' == $act) {
					$diguids[$uid] = $uid;
				} elseif ('del' == $act) {
					unset($diguids[$uid]);
				}
			}
			if(is_array($diguids)) {
				$diguids = serialize($diguids);
			}
			$ret = $this->set_val($tid, $diguids, 'diguids');
		}
		return $ret;
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