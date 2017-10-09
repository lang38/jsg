<?php
/**
 *
 * 数据表 wall_draft 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: wall_draft.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_wall_draft extends table {
	
	
	var $table = 'wall_draft';
	
	function table_wall_draft() {
		$this->init($this->table);
	}
		
}

?>