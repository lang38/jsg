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
 * @version $Id: comment.mod.php 3914 2013-06-28 10:16:45Z wuliyong $
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

        $p = array(
            'page_num' => 10,
            'page_url' => 'index.php?mod=comment&code=inbox',
        );
        $rets = jlogic('comment')->inbox($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }

    	include template();
    }

    function outbox() {
    	$this->Title = '我发出的评论 - 我评论的';

        $p = array(
            'page_num' => 10,
            'page_url' => 'index.php?mod=comment&code=outbox',
        );
        $rets = jlogic('comment')->outbox($p);
        if(is_array($rets) && $rets['error']) {
        	$this->Messager($rets['result'], null);
        }

    	include template();
    }

}