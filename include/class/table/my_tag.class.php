<?php
/**
 *
 * 数据表 my_tag 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: my_tag.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_my_tag extends table {
	
	
	var $table = 'my_tag';
	
	function table_my_tag() {
		$this->init($this->table);
	}
		
}

?>