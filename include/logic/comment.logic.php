<?php
/**
 *
 * 评论操作基类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: comment.logic.php 3903 2013-06-27 07:56:49Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class CommentLogic {

    public function __construct() {
        ;
    }
    
    
    public function inbox($p) {
    	$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
    	if(jdisallow($uid)) {
    		return jerror('您没有权限进行此操作', -1);
    	}
    	$member = jsg_member_info($uid);
    	        if ($member['comment_new']) {
        	jlogic('member')->clean_new_remind('comment_new', $uid);
        }
        $pn = (int) $p['page_num'];
        if($pn < 1) {
        	$pn = 10;
        }
        $ps = array('page_num' => $pn);
        if(isset($p['page_url'])) {
        	$ps['page_url'] = $p['page_url'];
        }
        $rets = jtable('member_relation')->get_tids($uid, $ps, 1);
        if(is_array($rets)) {
        	$rets['member'] = $member;
        }
        return jlogic('topic')->get_by_ids($rets);
    }
    
    
    public function outbox($p) {
    	$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
    	if(jdisallow($uid)) {
    		return jerror('您没有权限进行此操作', -1);
    	}
    	$member = jsg_member_info($uid);
    	$pn = (int) $p['page_num'];
    	if($pn < 1) {
    		$pn = 10;
    	}
    	$ps = array('type' => array('both', 'reply'), 'page_num' => $pn);
        if(isset($p['page_url'])) {
        	$ps['page_url'] = $p['page_url'];
        }
    	$rets = jtable('member_topic')->get_tids($uid, $ps, 1);
        if(is_array($rets)) {
        	$rets['member'] = $member;
        }
        return jlogic('topic')->get_by_ids($rets);
    }

}