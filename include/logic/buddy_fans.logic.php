<?php
/**
 *
 * 好友粉丝相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_fans.logic.php 5351 2014-01-03 07:45:01Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class BuddyFansLogic {

	var $db = null;

	function BuddyFansLogic() {
		$this->db = jtable('buddy_fans');
	}

	function get($p = array()) {
		$uid = (int) $p['uid'];
		$uid = ($uid > 0 ? $uid : MEMBER_ID);
		if($uid < 1) {
			return jerror('获取用户粉丝列表数据时，UID不能为空或您没有登录系统', -1);
		}
		$member = jsg_member_info($uid);
		if(empty($member)) {
			return jerror('您要查看的用户已经不存在了，UID错误', -2);
		}
		$count = (int) $member['fans_count'];
		if($count < 1) {
			return array();
		}

		$page_num = 10;
		if(isset($p['page_num'])) {
			$page_num = (int) $p['page_num'];
			if($page_num < 1 || $page_num > 100) {
				return jerror('请设置每页显示的数量在 1 ~ 100 之间', -3);
			}
		}
		


		
		$ps = array(
			
			'result_count' => $count,

			
			'page_num' => $page_num,
			'page_url' => $p['page_url'],

			
			'sql_field' => ' M.* ',
			'sql_table' => ' `' . DB::table($this->db->table_name($uid)) . '` AS BF LEFT JOIN `' . DB::table('members') . '` AS M ON M.`uid`=BF.`touid` ',
			'sql_where' => " BF.`uid`='{$uid}' AND M.`uid` IS NOT NULL ",
			'sql_order' => ' BF.`dateline` DESC ',

			
			'result_list_row_make_func' => 'jsg_member_make',
			'result_list_make_func' => 'buddy_follow_html',
		);
		if(true === IN_JISHIGOU_WAP) {
			unset($ps['result_list_make_func']);
		}

		if(jallow($uid)) {
			if($member['fans_new'] > 0) {
				jlogic('member')->clean_new_remind('fans_new', $uid);
			}

			if($p['nickname']) {
				$nickname = jfilter($p['nickname'], 'txt');
				if(strlen($nickname) < 3 || strlen($nickname) > 15) {
					return jerror('搜索用户昵称时，字数请控制在 3 ~ 15 个字符之间', -4);
				}
				unset($ps['result_count']);
				$ps['cache_time'] = 600; 				$ps['sql_where'] .= ' AND ' . build_like_query(' M.`nickname` ', $nickname);
			}

			if($p['order'] && in_array($p['order'], array('lastpost', 'fans_count'))) {
				$p['sql_order'] = ' M.`' . $p['order'] . '` DESC ';
			}
		}
		


		
		$rets = $this->db->get($ps);
		if(is_array($rets)) {
			$rets['member'] = $member;
		}
		if(true === IN_JISHIGOU_WAP) {
			if($rets['list']) {
				$rets['list'] = buddy_follow_html($rets['list'], 'uid', 'wap_follow_html');
			}
			$rets = wap_iconv($rets);
		}
		return $rets;
	}

}