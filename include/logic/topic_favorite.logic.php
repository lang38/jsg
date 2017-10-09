<?php
/**
 *
 * topic_favorite 操作基类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_favorite.logic.php 4822 2013-11-06 07:21:14Z chenxianfeng $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TopicFavoriteLogic {

    public $db = null;

    public function __construct() {
        $this->db = jtable('topic_favorite');
    }

    
    public function act($uid=0,$tid=0,$act='') {
		$uid = (is_numeric($uid) ? (int) $uid : 0);
		if(jdisallow($uid)) {
			return  "您无权进行此操作";
		}

		$tid = (is_numeric($tid) ? (int) $tid : 0);
		if ($tid < 1) {
			return  "请指定一个微博";
		}

		$topic_info = jtable('topic')->info($tid);
		if(!$topic_info) {
			return "指定的微博已经不存在了";
		}

		$infop = array('uid'=>$uid, 'tid'=>$tid);
		$topic_favorite = $this->db->info($infop);
		$is_favorite = $topic_favorite ? true : false;
		if('check' == $act) {
			return ($is_favorite ? 1 : 0);
		}
		if('info' == $act) {
			return $topic_favorite;
		}

		$ret = '';
		if(in_array($act, array('add', 'del', 'delete'))) {
			if('add' == $act) {
				if(!$is_favorite) {
					$this->db->insert(array(
						'uid' => $uid,
						'tid' => $tid,
						'tuid' => $topic_info['uid'],
						'dateline' => TIMESTAMP,
					));
										jtable('members')->update_count($topic_info['uid'], 'favoritemy_new', '+1');
										if($GLOBALS['_J']['config']['feed_type'] && is_array($GLOBALS['_J']['config']['feed_type']) && in_array('favorite',$GLOBALS['_J']['config']['feed_type']) && $GLOBALS['_J']['config']['feed_user'] && is_array($GLOBALS['_J']['config']['feed_user']) && array_key_exists(MEMBER_ID,$GLOBALS['_J']['config']['feed_user'])){
						$feed_msg = cut_str($topic_info['content'],30,'');
						feed_msg('leader','favorite',$tid,$feed_msg,$topic_info['item_id']);
					}
				}
				$ret = "<span><a href='javascript:void(0)'>已收藏</a></span>";
			} else {
				if($is_favorite) {
					$this->db->delete($infop);
									}
				$ret = "已取消";
			}
			jtable('members')->update_count($uid, 'topic_favorite_count', $this->db->count(array('uid'=>$uid)));
		}
		return $ret;
	}
	
	public function get_my_favorite_tid($p, $more = 0) {
		$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
		if(jdisallow($uid)) {
			return jerror('您无权查看');
		}
		$page_num = (int) $p['page_num'];
		if($page_num < 1) {
			$page_num = 10;
		}
		$ps = array(
			'uid' => $uid,
			'sql_order' => ' `id` DESC ',
			'page_num' => $page_num,
		);
		if(isset($p['page_url'])) {
			$ps['page_url'] = $p['page_url'];
		}
		return $this->db->get_ids($ps, 'tid', $more);
	}
	
	public function get_my_favorite_topic($p) {
		$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
		$rets = $this->get_my_favorite_tid($p, 1);
		if($rets && is_array($rets)) {
			if(!$rets['error']) {
				$rets['member'] = jsg_member_info($uid);
				if($rets['ids']) {
					$favorite_times = $this->_get_my_favorite_times($uid, $rets['ids']);
					$rets['list'] = jlogic('topic')->Get($rets['ids']);
					if($rets['list']) {
						foreach($rets['list'] as $k=>$row) {
							$row['favorite_time'] = $favorite_times[$row['tid']];
							$rets['list'][$k] = $row;
						}
						if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
                            if(true === IN_JISHIGOU_WAP) {
                            	$rets['list'] = buddy_follow_html($rets['list'], 'uid', 'wap_follow_html');
                            } else {
                            	$rets['list'] = jlogic('buddy')->follow_html2($rets['list']);
                            }
						}
						$rets['parent_list'] = jlogic('topic')->get_parent_list($rets['list']);	
					}
				} else {
					$rets['list'] = array();
				}
				if(true === IN_JISHIGOU_WAP) {
					$rets = wap_iconv($rets);
				}
			}				
		}
		return $rets;
	}
	
	public function get_favorite_me_topic($p) {
		$uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
		if(jdisallow($uid)) {
			return jerror('您无权查看');
		}
		$member = jsg_member_info($uid);
				if ($member['favoritemy_new'] > 0) {
			jlogic('member')->clean_new_remind('favoritemy_new', $member['uid']);
		}
		$page_num = (int) $p['page_num'];
		if($page_num < 1) {
			$page_num = 10;
		}
		$ps = array(
			'tuid' => $uid,
			'sql_order' => ' `id` DESC ',
			'page_num' => $page_num,
		);
		if(isset($p['page_url'])) {
			$ps['page_url'] = $p['page_url'];
		}
		$rets = $this->db->get($ps);
		if(is_array($rets)) {
			$rets['member'] = $member;
			if($rets['list']) {
				foreach($rets['list'] as $k=>$v) {
					if($v['tid'] < 1) {
						continue ;
					}
					$row = jlogic('topic')->Get($v['tid']);
					$row['fuid'] = $v['uid'];
					$row['favorite_time'] = my_date_format2($v['dateline']);
					$fuids[$v['uid']] = $v['uid'];
					$rets['list'][$k] = $row;
				}
				if($fuids) {
					$rets['favorite_members'] = jlogic('member')->get($fuids);
				}
				if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
	                if(true === IN_JISHIGOU_WAP) {
	                      $rets['list'] = buddy_follow_html($rets['list'], 'uid', 'wap_follow_html');
	                } else {
	                      $rets['list'] = jlogic('buddy')->follow_html2($rets['list']);
	                }
				}
				$rets['parent_list'] = jlogic('topic')->get_parent_list($rets['list']);			
			}
			if(true === IN_JISHIGOU_WAP) {
				$rets = wap_iconv($rets);
			}
		}
		return $rets;
	}
	
	private function _get_my_favorite_times($uid, $tids) {
		$list = array();
		$rets = $this->db->get(array('uid' => $uid, 'tid' => $tids));
		if($rets && is_array($rets['list'])) {
			foreach($rets['list'] as $row) {
				$k = $row['tid'];
				$v = my_date_format2($row['dateline']);
				$list[$k] = $v;
			}
		}
		return $list;
	}

}