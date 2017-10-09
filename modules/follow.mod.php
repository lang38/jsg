<?php
/**
 *
 * 排行模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: follow.mod.php 3780 2013-06-03 08:29:01Z wuliyong $
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
		$page_num = (int) jconf::get('show', 'topic', 'follow');
		if($page_num < 1) {
			$page_num = 10;
		}
		
		$p = array(
			'page_num' => $page_num,
			'uid' => $uid,
			'gid' => jget('gid', 'int'),
			'nickname' => jget('nickname', 'txt'),
			'order' => jget('order', 'txt'),
		);
		$p['page_url'] = 'index.php?mod=follow' .
			(($uid > 0 && MEMBER_ID !=$uid) ? '&uid=' . $uid : '') .
			(($uid > 0 && MEMBER_ID == $uid) ? (
				($p['gid'] ? '&gid=' . $p['gid'] : '') .
				($p['nickname'] ? '&nickname=' . $p['nickname'] : '') .
				($p['order'] ? '&order=' . $p['order'] : '')
			) : '');
		$rets = jlogic('buddy_follow')->get($p);
		if(is_array($rets) && $rets['error']) {
			$this->Messager($rets['result'], null);
		}
		
		$member = $rets['member'];
		$group = $rets['group'];
		$group_list = $rets['group_list'];
		if($group_list) {
			$group_list_header = array_slice($group_list, 0, min(4, count($group_list)));
		}
		$this->Title = "{$member['nickname']}关注的微博";		
		include template();
	}
	
}