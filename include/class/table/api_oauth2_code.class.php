<?php
/**
 *
 * 数据表 api_oauth2_code 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: api_oauth2_code.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_api_oauth2_code extends table {
	
	
	var $table = 'api_oauth2_code';
	
	function table_api_oauth2_code() {
		$this->init($this->table);
	}
		
}

?>