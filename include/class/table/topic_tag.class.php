<?php
/**
 *
 * 数据表 topic_tag 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_tag.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic_tag extends table {
	
	
	var $table = 'topic_tag';
	
	function table_topic_tag() {
		$this->init($this->table);
	}
		
}

?>