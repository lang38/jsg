<?php
/**
 *
 * 数据表 notice 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: notice.class.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_notice extends table {

	
	var $table = 'notice';

	function table_notice() {
		$this->init($this->table);
	}

	function get_data($perpage = null, $p = array()) {
		settype($p, 'array');
		$perpage = (int) $perpage;
		if($perpage < 1) {
			$perpage = (int) jconf::get('show', 'notice', 'list');
		}
		if($perpage < 1 || $perpage > 100) {
			$perpage = 10;
		}
		if(!isset($p['sql_order'])) {
			$p['sql_order'] = ' `id` DESC ';
		}
		if(!isset($p['perpage'])) {
			$p['perpage'] = $perpage;
		}
		return $this->get($p);
	}

	function new_data($limit = 5, $cache_time = 600, $p = array()) {
		$rets = array();
		$limit = (int) $limit;
		if($limit > 0) {
			settype($p, 'array');
			if(!isset($p['sql_order'])) {
				$p['sql_order'] = ' `id` DESC ';
			}
			if(!isset($p['result_count'])) {
				$p['result_count'] = $limit;
			}
			if(!isset($p['return_list'])) {
				$p['return_list'] = true;
			}
			if(!isset($p['cache_time']) && ($cache_time = (int) $cache_time) > 0) {
				$p['cache_time'] = $cache_time;
			}
			$rets = $this->get($p);
		}
		return $rets;
	}

}

?>