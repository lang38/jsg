<?php
/**
 *
 * 数据表 sms_client_user 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: sms_client_user.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_sms_client_user extends table {
	
	
	var $table = 'sms_client_user';
	
	function table_sms_client_user() {
		$this->init($this->table);
	}
		
}

?>