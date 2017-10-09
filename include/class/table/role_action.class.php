<?php
/**
 *
 * 数据表 role_action 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: role_action.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_role_action extends table {
	
	
	var $table = 'role_action';
	
	function table_role_action() {
		$this->init($this->table);
	}
	
	function get_list($mod, $is_admin = 0) {
		$rets = array();
		$mod = jfilter($mod, 'txt');
		if($mod) {
			$is_admin = ($is_admin ? 1 : 0);
			$cache_id = 'role_action/' . $mod . '-' . $is_admin;
			if(false === ($rets = cache_file('get', $cache_id))) {
				$p = array(
					'module' => $mod,
					'is_admin' => $is_admin,
					'result_count' => 99,
					'return_list' => 1,
				);
				$list = $this->get($p);
				$rets = array();
				if($list) {
					$index = $info = array();
					foreach($list as $row) {
						$id = $row['id'];
						unset($row['id'], $row['module'], $row['is_admin']);
						foreach($row as $k=>$v) {
							if(!$v) {
								unset($row[$k]);
							}
						}
						if(false !== strpos($row['action'], '|')) {
							$_vs = explode('|', $row['action']);
							foreach($_vs as $_v) {
								$index[$_v] = $id;
							}
						} else {
							$index[$row['action']] = $id;
						}
						unset($row['action']);
						$info[$id] = $row;
					}
					$rets = array('index'=>$index, 'info'=>$info);
				}
				cache_file('set', $cache_id, $rets);
			}
		}
		return $rets;
	}
	
	function cache_rm($mod) {
		$mod = jfilter($mod, 'txt');
		if($mod) {
			parent::cache_rm($mod);
			cache_file('rm', 'role_action/' . $mod . '-0');
			cache_file('rm', 'role_action/' . $mod . '-1');
		}
	}
		
}

?>