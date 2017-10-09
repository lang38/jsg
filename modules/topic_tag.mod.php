<?php
/**
 *
 * 微博话题模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_tag.mod.php 3879 2013-06-23 09:27:38Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    
    function ModuleObject($config) {
        $this->MasterObject($config, 1);
    }

    function index() {
        $this->Title = '我关注话题的微博';
        
        $page_num = (int) jconf::get('show', 'topic', 'tag');
        if($page_num < 1) {
            $page_num = 20;
        }
        $p = array(
            'page_num' => $page_num,
            'page_url' => 'index.php?mod=topic_tag',
        );
        $rets = jlogic('topic_tag')->get_my_tag_topic($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }
        
        $member = $rets['member'];
        $topic_list = $rets['list'];
        $parent_list = $rets['parent_list'];
        $page_arr = $rets['page'];
        $total_record = $rets['count'];
        include template('topic_tag_index');
    }
	
}