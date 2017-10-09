<?php
/**
 *
 * 数据表 buddy_fans_table_id 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_fans_table_id.class.php 4114 2013-08-09 06:41:09Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_buddy_fans_table_id extends table {

	
	var $table = 'buddy_fans_table_id';

	
	function table_buddy_fans_table_id() {
		$this->init($this->table);
	}

	
	function add($uid, $table_id) {
		$uid = jfilter($uid, 'int');
		$table_id = jfilter($table_id, 'int');
		$ret = false;
		if($uid > 0 && $table_id > 0) {
			$ret = $this->replace(array(
				'uid' => $uid,
				'table_id' => $table_id
			));
			$this->cache_rm($uid);
		}
		return $ret;
	}

	
	function table_id($uid, $num = 0) {
		if($GLOBALS['_J']['config']['acceleration_mode'] || true === IN_JISHIGOU_UPGRADE) {
			return ($uid % $num) + 1;
		}
		$table_id = $this->val($uid, 'table_id', 1);
		if(false === $table_id) {
			$num = jfilter($num, 'int');
			if($num > 1) {
				if(is_numeric($uid)) {
					$table_id = ($uid % $num) + 1;
				} else {
					$table_id = rand(1, $num);
				}
				$this->add($uid, $table_id);
			}
		}
		return $table_id;
	}

}

?>