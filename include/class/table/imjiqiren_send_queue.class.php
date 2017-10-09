<?php
/**
 *
 * 数据表 imjiqiren_send_queue 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: imjiqiren_send_queue.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_imjiqiren_send_queue extends table {
	
	
	var $table = 'imjiqiren_send_queue';
	
	function table_imjiqiren_send_queue() {
		$this->init($this->table);
	}
		
}

?>