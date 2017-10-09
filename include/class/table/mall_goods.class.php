<?php
/**
 *
 * 数据表 mall_goods 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mall_goods.class.php 701455476 2013-11-11 578 foxis@qq.com $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_mall_goods extends table {
	
	
	var $table = 'mall_goods';
	
	function table_mall_goods() {
		$this->init($this->table);
	}
		
}

?>