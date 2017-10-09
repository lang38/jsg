<?php
/**
 *
 * 数据表 members 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: members.class.php 3774 2013-06-03 07:00:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_members extends table {
	
	
	var $table = 'members';
	
	function table_members() {
		$this->init($this->table);
	}

	function row($uid, $make = 1) {
		$row = array();
		$uid = jfilter($uid, 'int');
		if($uid > 0) {
			$row = $this->info($uid);
			if($row && $make) {
				$row = jsg_member_make($row);
			}
		}
		return $row;
	}
			
}

?>