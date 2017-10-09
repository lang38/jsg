<?php
/**
 *
 * tag_recommend 操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag_recommend.logic.php 4049 2013-07-30 00:41:33Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TagRecommendLogic {

    public $db = null;

    public function __construct() {
        $this->db = jtable('tag_recommend');
    }
    
    public function get_name_by_top_id($limit) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$rets = $this->db->get_ids(array(
    			'result_count' => $limit,
    			'sql_order' => ' `id` DESC ',
    		), 'name');
    	}
    	return $rets;
    }

}