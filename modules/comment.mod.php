<?php
/**
 *
 * 微博评论模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: comment.mod.php 5378 2014-01-09 03:00:45Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    
    function ModuleObject($config) {
        $this->MasterObject($config, 1);
    }

    function index() {
        $this->Messager(null, 'index.php?mod=comment&code=inbox');
    }
    
    function inbox() {
    	$this->Title = '我收到的评论 - 评论我的';
        
        $page_num = (int) jconf::get('show', 'topic', 'mycomment');
        if($page_num < 1) {
            $page_num = 20;
        }
        $p = array(
            'page_num' => $page_num,
            'page_url' => 'index.php?mod=comment&code=inbox',
        );
        $rets = jlogic('comment')->inbox($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }
        
        $member = $rets['member'];
        $topic_list = $rets['list'];
        $parent_list = $rets['parent_list'];
        $page_arr = $rets['page'];
        $total_record = $rets['count']; 
        $content_ostr = $this->Config['on_publish_notice_str'];   	
    	include template();
    }
    
    function outbox() {
    	$this->Title = '我发出的评论 - 我评论的';
        
        $page_num = (int) jconf::get('show', 'topic', 'tocomment');
        if($page_num < 1) {
            $page_num = 20;
        }
        $p = array(
            'page_num' => $page_num,
            'page_url' => 'index.php?mod=comment&code=outbox',
        );
        $rets = jlogic('comment')->outbox($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }
        
        $member = $rets['member'];
        $topic_list = $rets['list'];
        $parent_list = $rets['parent_list'];
        $page_arr = $rets['page'];
        $total_record = $rets['count'];   
        $content_ostr = $this->Config['on_publish_notice_str'];
		$params['code'] = 'outbox';
    	include template();
    }
	
}