<?php
/**
 *
 * 数据表 tag_extra 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag_extra.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_tag_extra extends table {
	
	
	var $table = 'tag_extra';
	
	function table_tag_extra() {
		$this->init($this->table);
	}
		
}

?>