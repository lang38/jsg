<?php
/**
 *
 * 数据表 login_log 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: login_log.class.php 5146 2013-12-03 02:23:40Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_login_log extends table {
	
	
	var $table = 'login_log';
	
	function table_login_log() {
		$this->init($this->table);
	}
		
}

?>