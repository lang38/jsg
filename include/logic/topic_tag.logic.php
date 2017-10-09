<?php
/**
 *
 * topic_tag 操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_tag.logic.php 4049 2013-07-30 00:41:33Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TopicTagLogic {

    public $db = null;

    public function __construct() {
        $this->db = jtable('topic_tag');
    }
    
    public function get_hot_tag_id($limit, $cache_time = 300, $day = 30) {
    	$rets = array();
    	$limit = (int) $limit;
    	if($limit > 0) {
    		$cache_time = max(300, (int) $cache_time);
    		$day = (int) $day;
    		$p = array(
    			'cache_time' => $cache_time,
    			'cache_key' => "topic_tag/get_hot_tag_id-$limit-$cache_time-$day",
    			'result_count' => $limit,
    			'sql_where' => " `dateline`>'" . (TIMESTAMP - $day * 86400) . "' GROUP BY `tag_id` ORDER BY COUNT(`item_id`) DESC ",
    		);
    		$rets = $this->db->get_ids($p, 'tag_id');
    	}
    	return $rets;
    }
	
    
	public function get_my_tag_tid($p, $more = 0) {
		$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
		if(jdisallow($uid)) {
			return jerror('您无权查看');
		}
		$page_num = (int) $p['page_num'];
		if($page_num < 1) {
			$page_num = 10;
		}
		$tag_ids = jlogic('tag_favorite')->my_favorite_tag_ids($uid);
		$ps = array(
			'tag_id' => $tag_ids,
			'sql_order' => ' `item_id` DESC ',
			'page_num' => $page_num,
		);
		if(isset($p['page_url'])) {
			$ps['page_url'] = $p['page_url'];
		}
		if(isset($p['result_count'])) {
			$ps['result_count'] = $p['result_count'];
		}
		return $this->db->get_ids($ps, 'item_id', $more);
	}
	
	
	public function get_my_tag_topic($p) {
		$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
		if(jdisallow($uid)) {
			return jerror('您无权查看');
		}
		$member = jsg_member_info($uid);
		        if ($member['topic_new']) {
        	jlogic('member')->clean_new_remind('topic_new', $uid);
        }
        $rets = $this->get_my_tag_tid($p, 1);
        if(is_array($rets)) {
        	$rets['member'] = $member;
        }
        return jlogic('topic')->get_by_ids($rets);
	}
	
	public function get_my_new_reply_tag_topic($p) {
		;
	}
	
	public function get_my_recd_tag_topic($p) {
		;
	}

}