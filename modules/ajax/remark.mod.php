<?php

/**
 *
 * remark 模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: remark.mod.php 3748 2013-05-30 07:36:29Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	function Execute() {
		switch($this->Code) {
			case 'buddy_remark':
				$this->BuddyRemark();
				break;
			case 'add_buddy_remark':
				$this->AddBuddyRemark();
				break;

			default : {
					$this->Main();
					break;
				}
		}
	}

	function Main() {
		response_text('page is not exits');
	}

	function BuddyRemark() {
		$uid = (int) get_param('uid');

		$buddy_info = jlogic('buddy')->info($uid, MEMBER_ID);

		include(template('topic_remark_ajax'));
	}

	function AddBuddyRemark() {
				$remark = trim(strip_tags($this->Post['remark']));

				$buddyid =  (is_numeric($this->Post['buddyid']) ? $this->Post['buddyid'] : 0);
		if($buddyid < 1) {
			response_text('请指定一个好友ID');
		}

		$buddy_info = jlogic('buddy')->info($buddyid, MEMBER_ID);
		if(!$buddy_info) {
			response_text('你的好友已经不存在了');
		}

		$f_rets = filter($remark);
		if ($f_rets && $f_rets['error']) {
			response_text($f_rets['msg']);
		}

				if ($remark && preg_match('~[\<\>\'\"]~',$remark)) {
			response_text('不能包含特殊字符');
		}
		
		if($remark != $buddy_info['remark']) {
			$ret = jlogic('buddy')->set_remark(MEMBER_ID, $buddyid, $remark);
		}
	}

}

?>
