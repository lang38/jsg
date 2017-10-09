<?php
/**
 *
 * 好友关注相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_follow.logic.php 5351 2014-01-03 07:45:01Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class BuddyFollowLogic {

	var $db = null;

	function BuddyFollowLogic() {
		$this->db = jtable('buddy_follow');
	}

	function get($p = array()) {
		$uid = (int) $p['uid'];
		$uid = ($uid > 0 ? $uid : MEMBER_ID);
		if($uid < 1) {
			return jerror('获取用户关注列表数据时，UID不能为空或您没有登录系统', -1);
		}
		$member = jsg_member_info($uid);
		if(empty($member)) {
			return jerror('您要查看的用户已经不存在了，UID错误', -2);
		}
		$count = (int) $member['follow_count'];
		if($count < 1) {
			return array();
		}

		$page_num = 20;
		if(isset($p['page_num'])) {
			$page_num = (int) $p['page_num'];
			if($page_num < 1 || $page_num > 200) {
				return jerror('请设置每页显示的数量在 1 ~ 200 之间', -3);
			}
		}
		


		
		$ps = array(
			
			'result_count' => $count,

			
			'page_num' => $page_num,
			'page_url' => $p['page_url'],

			
			'sql_field' => ' BF.`remark`, BF.`gids`, M.* ',
			'sql_table' => ' `' . DB::table($this->db->table_name($uid)) . '` AS BF LEFT JOIN `' . DB::table('members') . '` AS M ON M.`uid`=BF.`touid` ',
			'sql_where' => " BF.`uid`='{$uid}' AND M.`uid` IS NOT NULL ",
			
			
			'result_list_row_make_func' => 'jsg_member_make',
			'result_list_make_func' => 'buddy_follow_html',
		);
		if(true === IN_JISHIGOU_WAP) {
			unset($ps['result_list_make_func']);
		}

		$group = array();
		$group_list = array();
		if(jallow($uid)) {
			$group_list = jlogic('buddy_follow_group')->get_my_group($uid);

			if($p['nickname']) {
				$nickname = jfilter($p['nickname'], 'txt');
				if(strlen($nickname) < 3 || strlen($nickname) > 15) {
					return jerror('搜索用户昵称时，字数请控制在 3 ~ 15 个字符之间', -4);
				}
				unset($ps['result_count']);
				$ps['cache_time'] = 600; 				$ps['sql_where'] .= ' AND ' . build_like_query(' M.`nickname` ', $nickname);
			} elseif ($p['gid']) {
				$gid = (int) $p['gid'];
				$group = jlogic('buddy_follow_group')->get_my_group_info($uid, $gid);
				if(empty($group)) {
					return jerror('请指定一个正确的分组GID', -5);
				}
				$ps['result_count'] = $group['count'];
				$ps['sql_table'] = ' `' . DB::table(jtable('buddy_follow_group_relation')->table_name($uid)) . '` AS BFGR
					LEFT JOIN `' . DB::table('members') . '` AS M
						ON M.`uid` = BFGR.`touid`
					LEFT JOIN `' . DB::table($this->db->table_name($uid)) . '` AS BF
						ON (BF.`uid`="' . $uid . '" AND BF.`touid`=M.`uid`) ';
				$ps['sql_where'] = ' BFGR.`gid`="' . $gid . '" AND BFGR.`uid`="' . $uid . '" AND M.`uid` IS NOT NULL ';
				$ps['sql_order'] = ' BFGR.`dateline` DESC ';
			}

			if($p['order'] && in_array($p['order'], array('lastpost', 'fans_count'))) {
				$p['sql_order'] = ' M.`' . $p['order'] . '` DESC ';
			}
		}
		


		
		$rets = $this->db->get($ps);
		if(is_array($rets)) {
			$rets['member'] = $member;
			$rets['group'] = $group;
			$rets['group_list'] = $group_list;
			if($rets['list'] && $group_list) {
				foreach($rets['list'] as $k=>$v) {
					if($v['gids']) {
						$rets['list'][$k]['buddy_group_list'] = jlogic('buddy_follow_group')->get_group_list($v['gids'], $group_list);
					}
				}
			}
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