<?php
/**
 *
 * AT操作基类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_mention.logic.php 3855 2013-06-18 07:45:42Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TopicMentionLogic {

    public $db = null;

    public function __construct() {
        $this->db = jtable('topic_mention');
    }

    public function get_at_my_tid($p, $more = 0) {
        $uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
        if(jdisallow($uid)) {
            return jerror('您无权查看该信息，请指定正确的UID参数', -1);
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

    public function get_at_my_topic($p) {
        $rets = array();
        $uid = (isset($p['uid']) ? (int) $p['uid'] : MEMBER_ID);
        $member = jsg_member_info($uid);
        if($member) {
                        if ($member['at_new'] > 0) {
                                jlogic('member')->clean_new_remind('at_new', $member['uid']);
            }
            $rets = $this->get_at_my_tid($p, 1);
            if($rets && is_array($rets)) {
                if(!$rets['error']) {
                    $rets['member'] = $member;
                    $rets['list'] = (($rets['count'] > 0 && $rets['ids']) ? jlogic('topic')->Get($rets['ids']) : array());
                    if($rets['list']) {
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
            }
        } else {
        	return jerror('您无权查看该信息，请先登录', -1);
        }
        return $rets;
    }

}