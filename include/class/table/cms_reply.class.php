<?php
/**
 *
 * 数据表 cms_reply 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: cms_reply.class.php 2002950776 2013-11-11 573 foxis@qq.com $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_cms_reply extends table {
	
	
	var $table = 'cms_reply';
	
	function table_cms_reply() {
		$this->init($this->table);
	}
		
}

?>