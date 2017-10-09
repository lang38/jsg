<?php
/**
 *
 * 关注模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: follow.mod.php 3914 2013-06-28 10:16:45Z wuliyong $
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
		$p['page_url'] = 'index.php?mod=follow' .
			(($uid > 0 && MEMBER_ID !=$uid) ? '&uid=' . $uid : '');
		$rets = jlogic('buddy_follow')->get($p);
		if(is_array($rets) && $rets['error']) {
			$this->Messager($rets['result'], null);
		}

		$member = $rets['member'];
		$this->Title = "{$member['nickname']}关注的人";
		include template();
	}

}