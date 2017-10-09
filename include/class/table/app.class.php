<?php
/**
 *
 * 数据表 app 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: app.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_app extends table {
	
	
	var $table = 'app';
	
	function table_app() {
		$this->init($this->table);
	}

	
	function rand_key($rand_key = '') {
		return md5(serialize($_SERVER) . mt_rand() . serialize(array($rand_key)));
	}
	
	function row($id) {
		$row = array();
		$id = jfilter($id, 'int');
		if($id > 0) {
			$row = $this->info($id, 2592000, 1);
		}
		return $row;
	}
	
}

?>