<?php
/**
 *
 * 微博收藏模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_favorite.mod.php 3879 2013-06-23 09:27:38Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    
    function ModuleObject($config) {
        $this->MasterObject($config, 1);
    }

    function index() {
        $this->Title = '我收藏的微博';
        
        $page_num = (int) jconf::get('show', 'topic', 'myfavorite');
        if($page_num < 1) {
            $page_num = 20;
        }
        $p = array(
            'page_num' => $page_num,
            'page_url' => 'index.php?mod=topic_favorite',
        );
        $rets = jlogic('topic_favorite')->get_my_favorite_topic($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }
        
        $member = $rets['member'];
        $topic_list = $rets['list'];
        $parent_list = $rets['parent_list'];
        $page_arr = $rets['page'];
        $total_record = $rets['count'];
        include template('topic_favorite_index');
    }
    
    function me() {
    	$this->Title = '谁收藏了我的微博';
    	
    	$page_num = (int) jconf::get('show', 'topic', 'favoritemy');
        if($page_num < 1) {
            $page_num = 20;
        }
        $p = array(
            'page_num' => $page_num,
            'page_url' => 'index.php?mod=topic_favorite&code=me',
        );
        $rets = jlogic('topic_favorite')->get_favorite_me_topic($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }
        
        $member = $rets['member'];
        $topic_list = $rets['list'];
        $parent_list = $rets['parent_list'];
        $page_arr = $rets['page'];
        $total_record = $rets['count'];
        $favorite_members = $rets['favorite_members'];
    	include template();
    }
	
}