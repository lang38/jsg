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
 * @version $Id: fans.mod.php 3780 2013-06-03 08:29:01Z wuliyong $
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

		$p = array(
			'page_num' => 8,
			'uid' => $uid,
		);
		$p['page_url'] = 'index.php?mod=fans' .
			(($uid > 0 && MEMBER_ID !=$uid) ? '&uid=' . $uid : '');
		$rets = jlogic('buddy_fans')->get($p);
		if(is_array($rets) && $rets['error']) {
			$this->Messager($rets['result'], null);
		}

		$member = $rets['member'];
		$this->Title = "关注{$member['nickname']}的人 - {$member['nickname']}的粉丝";
		include template();
	}

}