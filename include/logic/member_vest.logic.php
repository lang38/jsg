<?php
/**
 *
 * 用户马甲逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member_vest.logic.php 5462 2014-01-18 01:12:59Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class MemberVestLogic {

	public $db = null;

	public function __construct() {
		$this->db = jtable('members_vest');
	}

	
	public function get($p) {
		return $this->db->get($p);
	}

	
	public function delete($p) {
		return $this->db->delete($p);
	}

	public function get_member_vest($uid, $is_admin = 0) {
		$rets = array();
		$uid = (int) $uid;
		if($uid > 0 && ($is_admin || jallow($uid))) {
			$useruid = $this->db->val(array('uid' => $uid), 'useruid');
			if($useruid > 0) {
				$sql = "select mv.`uid` , mv.`useruid` , m.`username` , m.`nickname` from `".TABLE_PREFIX."members_vest` mv
						 left join `".TABLE_PREFIX."members` m on m.`uid` = mv.`uid`
						 where `useruid` = '$useruid' ";
				$query = DB::query($sql);
				while ($rs = DB::fetch($query)) {
					$rs['face'] = face_get($rs['uid']);
					$rets[$rs['uid']] = $rs;
				}
			}
		}
		return $rets;
	}

	
	function setVest($uid,$useruid){
		if($uid < 1 || $useruid < 1){
			return 1;
		}

		$user_uid = DB::result_first("select `useruid` from `".TABLE_PREFIX."members_vest` where `uid` = '$useruid'");

		$useruid = $user_uid ? $user_uid : $useruid;
		$count = DB::result_first("select `useruid` from `".TABLE_PREFIX."members_vest` where `uid` = '$uid'");
		if ($count){
			return 2;
		} else {
			if(!$user_uid){
				DB::query("insert into `".TABLE_PREFIX."members_vest` (`uid`,`useruid`) values ('$useruid','$useruid') ");
			}

			DB::query("insert into `".TABLE_PREFIX."members_vest` (`uid`,`useruid`) values ('$uid','$useruid') ");

		}
		return 0;
	}

	
	function cancelVest($uid,$useruid){
		if($uid < 1 || $useruid < 1){
			return 1;
		}
		#不能取消自己
		if($uid == $useruid){
			return 2;
		}
		$user_uid = DB::result_first("select `useruid` from `".TABLE_PREFIX."members_vest` where `uid` = '$uid'");
		if($user_uid == $useruid){
			DB::query("delete from `".TABLE_PREFIX."members_vest` where `uid` = '$uid' and `useruid` = '$useruid'");
			#检测本尊下是否还有马甲，没有的话取消本尊
			$vest = $this->get_member_vest($useruid, 1);
			if(count($vest) < 2){
				DB::query("delete from `".TABLE_PREFIX."members_vest` where `useruid` = '$useruid' ");
			}
			return 0;
		} else {
			#没有权利取消马甲
			return 3;
		}
	}

	
	function checkMemberVest($uid,$useruid){
		if($uid < 1 || $useruid < 1){
			return 0;
		}
		$useruid_arr = $this->get_member_vest($uid, 1);

		if($useruid_arr){
			if($useruid_arr[$uid]){
				return 1;
			}
		} else {
			return 0;
		}
	}

	
	function getVestMemberByParam($param){
		$vest = array();
		$page_arr = array();
		$where_arr = array();
		if($param['uid']){
			if(is_array($param['uid'])){
				$where_arr[] = " mv.`uid` in ('" . implode("','",$param['uid']) . "')";
			} else {
				$where_arr[] = " mv.`uid` in = '$param[uid]' ";
			}
		}

		if($param['useruid']){
			if(is_array($param['useruid'])){
				$where_arr[] = " mv.`useruid` in ('" . implode("','",$param['useruid']) ."')";
			} else {
				$where_arr[] = " mv.`useruid` in = '$param[useruid]' ";
			}
		}

		#用户昵称
		if($param['nickname']){
			$uid = DB::result_first("select `uid` from `".TABLE_PREFIX."members` where `nickname` = '$param[nickname]' limit 1");
			$uid && $where_arr[] = " mv.`uid` =  '$uid' ";
		}

		#本尊用户昵称
		if($param['usernickname']){
			$useruid = DB::result_first("select `uid` from `".TABLE_PREFIX."members` where `nickname` = '$param[usernickname]' limit 1");
			$where_arr[] = " mv.`useruid` =  '$useruid' ";
		}

		if($param['where']){
			$where_arr[] = $param['where'];
		}

		$where = $where_arr ? ' where ' . implode(' and ',$where_arr) : '';

		$count = DB::result_first("select count(*) from `".TABLE_PREFIX."members_vest` mv $where ");

		if ($param['per_page_num']) {
			$page_arr = page($count,$param['per_page_num'],$param['page_url'],array('return'=>'array'));
			$limit = $page_arr['limit'];
		} else if ($param['limit']) {
			$limit = ' limit ' . $param['limit'];
		}

		$sql = "select m1.uid , m2.uid as useruid , m2.username ,m2.username as userusername , m1.nickname , m2.nickname as usernickname from `".TABLE_PREFIX."members_vest` mv
				 left join `".TABLE_PREFIX."members` m1 on m1.uid = mv.uid
				 left join `".TABLE_PREFIX."members` m2 on m2.uid = mv.useruid
				 $where
				 order by `uid` desc
				 $limit ";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)) {
			$vest[$rs['uid']] = $rs;
		}

		$ret['vest'] = $vest;
		$ret['page_arr'] = $page_arr;
		$ret['count'] = $count;
		return $ret;
	}

}