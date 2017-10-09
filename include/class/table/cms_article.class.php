<?php
/**
 *
 * 数据表 cms_article 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: cms_article.class.php 3973 2013-07-04 06:23:00Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_cms_article extends table {
	
	
	var $table = 'cms_article';
	
	function table_cms_article() {
		$this->init($this->table);
	}
		
}

?>