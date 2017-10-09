<?php
/**
 *
 * 数据表 qun_announcement 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: qun_announcement.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_qun_announcement extends table {
	
	
	var $table = 'qun_announcement';
	
	function table_qun_announcement() {
		$this->init($this->table);
	}

	function new_data($qid = 0, $limit = 5, $cache_time = 600, $p = array()) {
		$rets = array();		
		$limit = (int) $limit;
		if($limit > 0) {
			settype($p, 'array');
			$qid = (int) $qid;
			if(!isset($p['qid']) && $qid > 0) {
				$p['qid'] = $qid;
			}
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