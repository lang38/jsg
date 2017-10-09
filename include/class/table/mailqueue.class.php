<?php
/**
 *
 * 数据表 mailqueue 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mailqueue.class.php 4565 2013-09-24 03:36:05Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_mailqueue extends table {
	
	
	var $table = 'mailqueue';
	
	function table_mailqueue() {
		$this->init($this->table);
	}
	
	
	function row($uid) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		if($uid > 0) {
			$ret = $this->info(array('uid'=>$uid));
		}
		return $ret;
	}
	
	
	function add($uid, $check = '') {
		$ret = false;
		if($GLOBALS['_J']['config']['sendmailday'] > 0) {
			$user = array();
			if(is_numeric($uid)) {
				$user = jtable('members')->info($uid);
			} elseif(is_array($uid)) {
				$user = $uid;
			}
			if($user && ($uid = (int) $user['uid']) > 0 
			
			&& $user['email'] && 1 == $user['email_checked'] &&
			 
			(!$check || ($check && 1 == $user[$check])) &&
			 
			(TIMESTAMP - $user['lastactivity']) > 3600) {
				if(1 == $user['user_notice_time']) {
					$sendtime = TIMESTAMP; 				} elseif (2 == $user['user_notice_time']) {
					$sendtime = TIMESTAMP + 86400; 				} elseif (4 == $user['user_notice_time']) {
					$sendtime = TIMESTAMP + 86400 * 30; 				} else {
					$sendtime = TIMESTAMP + 86400 * 7; 				}
				$data = array(
					'dateline' => $sendtime,
					'uid' => $uid,
					'email' => $user['email'],
					'msg' => serialize(array(
								'comment_new' => $user['comment_new'],
								'newpm' => $user['newpm'],
								'event_new' => $user['event_new'],
								'at_new' => $user['at_new'],
								'fans_new' => $user['fans_new'],
								'vote_new' => $user['vote_new'],
								'dig_new' => $user['dig_new'],
								'channel_new' => $user['channel_new'],
								'company_new' => $user['company_new'],
								'qun_new' => $user['qun_new'])),
				);
				$row = $this->row($uid);
				if(!$row) {
					$ret = $this->insert($data, true, true);
				} else {
					if($row['dateline'] > 0) {
												unset($data['dateline']);
					}
					$ret = $this->update($data, array('uid'=>$uid));
				}
			}
		}
		return $ret;
	}

	
	function del($uid, $real = 0) {
		$ret = false;
		$uid = jfilter($uid, 'int');
		if($uid > 0) {
			$p = array('uid'=>$uid);
			if($real) {
				$ret = $this->delete($p);
			} else {
				$ret = $this->update_count($p, 'dateline', '0', 1, array('msg'=>''));
			}
		}
		return $ret;
	}
		
}

?>