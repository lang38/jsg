<?php
/**
 *
 * hot_tag 操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: hot_tag.logic.php 4050 2013-07-30 01:29:17Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class HotTagLogic {

    public $db = null;

    public function __construct() {
        $this->db = jtable('tag');
    }
    
        public function get_hot_tag($limit, $cache_time = 300, $day = 30) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$tag_id = jlogic('topic_tag')->get_hot_tag_id($limit, $cache_time, $day);
    		if($tag_id) {
    			$p = array(
    				'cache_time' => $cache_time,
    				'cache_key' => "hot_tag/get_hot_tag-$limit-$cache_time-$day",
    				'result_count' => $limit,
    				'id' => $tag_id,
    				'sql_field' => ' `id`, `name`, `topic_count`, `status`, `total_count`, `tag_count` ',
    				'sql_order' => ' `topic_count` DESC ',
    			);
    			$rets = $this->_get_hot_tag($p);
    		}
    	}
    	return $rets;
    }
    
        public function get_tag_by_top_tag_count($limit, $cache_time = 0, $day = 30) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$cache_time = (int) $cache_time;
    		$day = (int) $day;
    		$p = array(
    			'cache_time' => $cache_time,
    			'cache_key' => "hot_tag/get_tag_by_top_tag_count-$limit-$cache_time-$day",
    			'result_count' => $limit,
    			'sql_order' => "`tag_count` DESC, `last_post` DESC",
    		);    		
    		if($day > 0) {
    			$p['>@last_post'] = TIMESTAMP - $day * 86400;
    		}
    		$rets = $this->_get_hot_tag($p);
    	}
    	return $rets;
    }
    
        public function get_tag_by_top_topic_count($limit, $cache_time = 0, $day = 7) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$cache_time = (int) $cache_time;
    		$day = (int) $day;
    		$p = array(
    			'cache_time' => $cache_time,
    			'cache_key' => "hot_tag/get_tag_by_top_topic_count-$limit-$cache_time-$day",
    			'result_count' => $limit,
    			'sql_order' => "`topic_count` DESC, `last_post` DESC",
    		);    		
    		if($day > 0) {
    			$p['>@last_post'] = TIMESTAMP - $day * 86400;
    		}
    		$rets = $this->_get_hot_tag($p);
    	}
    	return $rets;
    }
    
    
    public function get_tag_by_recommend($limit, $cache_time = 0) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$name = jlogic('tag_recommend')->get_name_by_top_id($limit);
    		if($name) {
    			$p = array(
    				'cache_time' => $cache_time,
    				'cache_key' => "hot_tag/get_tag_by_recommend-$limit-$cache_time",
    				'result_count' => $limit,
    				'name' => $name,
    				'sql_order' => ' `last_post` DESC ',
    			);
    			$rets = $this->_get_hot_tag($p);
    		}
    	}
    	return $rets;
    }
    
    private function _get_hot_tag($p) {
    	$p['cache_time'] = max(300, (int) $p['cache_time']);
        $p['sql_field'] = $p['sql_field'] ? $p['sql_field'] : ' `id`, `name`, `topic_count`, `tag_count` ';
        $p['result_list_key_is_pri'] = 1;
        $p['return_list'] = 1;
        return $this->db->get($p);
    }

}