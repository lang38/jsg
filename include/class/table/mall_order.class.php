<?php
/**
 *
 * 数据表 mall_order 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mall_order.class.php 5146 2013-12-03 02:23:40Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_mall_order extends table {
	
	
	var $table = 'mall_order';
	
	function table_mall_order() {
		$this->init($this->table);
	}
		
}

?>