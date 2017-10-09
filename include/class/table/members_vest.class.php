<?php
/**
 *
 * 数据表 members_vest 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: members_vest.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_members_vest extends table {
	
	
	var $table = 'members_vest';
	
	function table_members_vest() {
		$this->init($this->table);
	}
		
}

?>