<?php
/**
 *
 * 数据表 qqwb_bind_topic 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: qqwb_bind_topic.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_qqwb_bind_topic extends table {
	
	
	var $table = 'qqwb_bind_topic';
	
	function table_qqwb_bind_topic() {
		$this->init($this->table);
	}
		
}

?>