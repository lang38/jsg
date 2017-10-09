<?php
/**
 *
 * 地区操作基类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: common_district.logic.php 3853 2013-06-17 09:39:25Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class CommonDistrictLogic {

	public $db = null;

	public function __construct() {
		$this->db = jtable('common_district');
	}

	public function get_name_by_id($id) {
		$ret = '';
		$id = max(0, (int) $id);
		if($id > 0) {
			$ret = $this->db->val(array('id' => $id), 'name');
		}
		return $ret;
	}
	
	public function get_id_by_name($name) {
		$ret = 0;
		$name = jfilter($name, 'txt');
		if($name) {
			$ret = (int) $this->db->val(array('name' => $name), 'id');
		}
		return $ret;
	}
	
        
	public function get_province_list($mk_form_options = 0) {
		$rets = $this->db->get(array(
			'upid' => '0',
			'result_count' => 999,
			'sql_order' => ' `list` ASC ',
		));
                if($mk_form_options) {
                    $list = array();
                    foreach($rets['list'] as $k=>$v) {
                        $v['value'] = $id = $v['id'];
                        $v['name'] = $v['name'];
                        $list[$id] = $v;
                    }
                    return $list;
                } else {
                    return $rets['list'];
                }
	}

}