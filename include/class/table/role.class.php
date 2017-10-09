<?php
/**
 *
 * 数据表 role 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: role.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_role extends table {
	
	
	var $table = 'role';
	
	function table_role() {
		$this->init($this->table);
	}

	function row($id, $field_prefix = 'role_') {
		$row = array();
		$id = jfilter($id, 'int');
		if($id > 0) {
			if($field_prefix) {
				$p = array('id'=>$id, 'sql_field_prefix'=>$field_prefix, 'result_list_row_unset_empty_value'=>1);
			} else {
				$p = $id;
			}
			$row = $this->info($p, 2592000, 1);
		}
		return $row;
	}
	
	function guest() {
		$cache_id = 'role/guest';
		if(false === ($row = cache_file('get', $cache_id))) {
			$row = $this->row(1);
			$row['uid'] = 0;
			$row['role_id'] = 1;
			$row['nickname'] = $row['role_name'] = '游客';
			$row['username'] = 'guest';
			cache_file('set', $cache_id, $row);
		}
		return $row;	
	}
	
	function cache_rm($id) {
		$id = jfilter($id, 'int');
		if($id > 0) {
			parent::cache_rm($id);
			$p = array('id'=>$id, 'sql_field_prefix'=>'role_', 'result_list_row_unset_empty_value'=>1);
			parent::cache_rm($p);
			parent::cache_rm('role/guest', 1);
		}
	}
	
	function get_name_by_id($id) {
		static $S_names;		
		$id = jfilter($id, 'int');
		if(!isset($S_names[$id])) {
			if($id > 0) {
				$row = $this->row($id);
			} else {
				$row = $this->guest();
			}
			$S_names[$id] = $row['role_name'];
		}
		return $S_names[$id];
	}
	
}

?>