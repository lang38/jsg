<?php
/**
 *
 * 数据表 topic_mention 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_mention.class.php 3678 2013-05-24 09:48:20Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_topic_mention extends table {
	
	
	var $table = 'topic_mention';
	
	function table_topic_mention() {
		$this->init($this->table);
	}

	function row($tid, $uid = MEMBER_ID) {
		$row = array();
		$tid = jfilter($tid, 'int');
		$uid = jfilter($uid, 'int');
		if($tid > 0 && $uid > 0) {
			$row = $this->info(array('tid' => $tid, 'uid' => $uid));
		}
		return $row;
	}
	
	function is_at($tid, $uid = MEMBER_ID) {
		return ($this->row($tid, $uid) ? true : false);
	}
	
	function add($tid, $uid) {
		$ret = false;
		$tid = jfilter($tid, 'int');
		$uid = jfilter($uid, 'int');
		if($tid > 0 && $uid > 0 && 
			!($this->is_at($tid, $uid)) && 
			($row = jtable('topic')->row($tid)) && 
			$uid != $row['uid']) {
			$ret = $this->insert(array(
				'tid' => $tid,
				'uid' => $uid,
				'tuid' => $row['uid'],
				'dateline' => TIMESTAMP,
			), 1);
			if($ret) {
				$this->cache_rm(array('tid' => $tid, 'uid' => $uid));
				jtable('members')->update_count($uid, 'at_new', '+1', array('+@at_count'=>1));
			}
		}
		return $ret;
	}
	
		function hot_at_me($uid = MEMBER_ID, $limit = 10, $day = 7) {
		return $this->hot_at($uid, $limit, $day, 'uid', 'tuid');
	}	
		function my_hot_at($uid = MEMBER_ID, $limit = 10, $day = 7) {
		return $this->hot_at($uid, $limit, $day, 'tuid', 'uid');
	}
	function hot_at($uid = MEMBER_ID, $limit = 10, $day = 7, $key = 'uid', $field = 'tuid') {
		$rets = array();
		$uid = jfilter($uid, 'int');
		$limit = jfilter($limit, 'int');
		$day = jfilter($day, 'int');
		if($uid > 0 && $limit > 0 && $day > 0) {
			$cache_id = $this->cache_id("hot_at-$field-$key-$uid-$limit-$day");
			if(false === ($rets = cache_db('mget', $cache_id))) {
				$gets = $this->get(array(
					'sql_field' => " `{$field}`, COUNT(`{$field}`) AS `at_count` ",
					$key => $uid,
					'>@dateline' => (TIMESTAMP - $day * 86400),
					'sql_group' => " `{$field}` ",
					'sql_order' => ' `at_count` DESC, `id` DESC ',
					'result_count' => $limit,
					'return_list' => 1,
				));
				$uids = array();
				if($gets) {
					$at_counts = array();
					foreach($gets as $k=>$v) {
						$uid = (int) $v[$field];
						if($uid > 0) {
							$uids[$uid] = $uid;
							$at_counts[$uid] = $v['at_count'];
						}
					}
				}
				$rets = array();
				if($uids) {
					$rets = jlogic('topic')->GetMember($uids, "`uid`,`ucuid`,`username`,`nickname`,`face`,`face_url`,`fans_count`");
					foreach($rets as $k=>$row) {
						$row['at_count'] = $at_counts[$row['uid']];
						$rets[$k] = $row;
					}
				}
				cache_db('mset', $cache_id, $rets, 3600);
			}
		}
		return $rets;
	}
	
}

?>