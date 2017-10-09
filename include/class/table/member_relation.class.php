<?php
/**
 *
 * 数据表 member_relation 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member_relation.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_member_relation extends table {
	
	
	var $_table = 'member_relation';
	
	
	var $_sub_table_by_field = 'touid';
	
	function table_member_relation() {
		$this->_init_table();
	}

	function table_name($id = 0, $type = 0) {
		return $this->_init_table($id, $type);
	}

	function row($touid, $tid) {
		$ret = false;
		$touid = jfilter($touid, 'int');
		$tid = jfilter($tid, 'int');
		if($touid > 0 && $tid > 0) {
			$this->_init_table($touid);
			$ret = $this->info(array(
				'touid' => $touid,
				'tid' => $tid,
			));
		}
		return $ret;
	}
	
	function add($tid) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		if($tid > 0) {
			$row = jtable('topic')->row($tid);
			if($row && ($touid = $row['touid']) > 0 && $row['totid'] > 0 && 'first' != $row['type'] && 
				!$this->row($touid, $tid)) {
				$this->_init_table($touid);
				$ret = $this->insert(array(
					'touid' => $touid,
					'totid' => $row['totid'],
					'tid' => $tid,
					'dateline' => $row['dateline'] ? $row['dateline'] : TIMESTAMP,
					'type' => $row['type'],
				)) ? true : false;
			}
		}
		return $ret;
	}
	
	function rm($touid, $tid) {
		$ret = false;
		$touid = jfilter($touid, 'int');
		$tid = jfilter($tid, 'int');
		if($touid > 0 && $tid > 0 && $this->row($touid, $tid)) {
			$this->_init_table($touid);
			$ret = $this->delete(array(
				'touid' => $touid,
				'tid' => $tid,
			)) ? true : false;
		}
		return $ret;
	}
	
	
	function get_tids($touid, $p = array(), $more = 0) {
		settype($p, 'array');
		$touid = jfilter($touid, 'int');
		if($touid < 1) {
			return false;
		}
		$row = jtable('members')->row($touid);
		if(!$row) {
			return false;
		}		
		$p['touid'] = $touid;
		if(!isset($p['type'])) {
			$p['type'] = array('both', 'reply');
		}
		if(!$p['sql_order']) {
			$p['sql_order'] = ' `dateline` DESC ';
		}
		$this->_init_table($touid);
		$rets = $this->get_ids($p, 'tid', $more);
		return $rets;
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