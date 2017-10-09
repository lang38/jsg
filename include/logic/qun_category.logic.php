<?php
/**
 *
 * 微群分类相关的数据库操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: qun_category.logic.php 3684 2013-05-27 02:58:05Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class QunCategoryLogic {
	
	var $db = null;

	function __construct() {
		$this->db = jtable('qun_category');
	}
	
	function update_qun_num($cat_id, $num = null) {
		$ret = false;
		$cat_id = (int) $cat_id;
		if($cat_id > 0) {
			if(isset($num)) {
				$num = (int) $num;				
				$ret = $this->db->update_count($cat_id, 'qun_num', $num);
			} else {
				;
			}			
		}
		if($ret) {
			jconf::set('qun_category', array());
		}
		return $ret;
	}
	
}