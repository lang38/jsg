<?php
/**
 *
 * 粉丝模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: fans.mod.php 5629 2014-03-06 09:42:47Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}
	
	function index() {
		$uid = jget('uid', 'int');
		$page_num = (int) jconf::get('show', 'topic', 'fans');
		if($page_num < 1) {
			$page_num = 10;
		}
		
		$p = array(
			'page_num' => $page_num,
			'uid' => $uid,
			'nickname' => jget('nickname', 'txt'),
			'order' => jget('order', 'txt'),
		);
		$p['page_url'] = 'index.php?mod=fans'.($uid > 0 ? '&uid='.$uid : '').($p['nickname'] ? '&nickname='.$p['nickname'] : '').($p['order'] ? '&order=' . $p['order'] : '');
		$rets = jlogic('buddy_fans')->get($p);
		if(is_array($rets) && $rets['error']) {
			$this->Messager($rets['result'], null);
		}
		
		$member = $rets['member'];		
		$this->Title = "关注{$member['nickname']}的人 - {$member['nickname']}的粉丝";		
		include template();
	}
	
}