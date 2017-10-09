<?php
/**
 *
 * 用户数据表相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: member.logic.php 5374 2014-01-08 06:58:26Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class MemberLogic {
    
    public $db = null;
    
    public function __construct() {
        $this->db = jtable('members');
    }
    
    public function get($ids, $fields = '*') {
		if(empty($ids)) {
			return array();
		}
		if(is_numeric($ids)) {
			if($ids == MEMBER_ID && $GLOBALS['_J']['member']) {
				return $GLOBALS['_J']['member'];
			}
			$r = $this->db->row($ids);
						if($r['role_id'] > 0) {
				$r = array_merge($r, jtable('role')->row($r['role_id']));
			}
		} else {
			$p = array(
				'sql_field' => $fields,
				'result_list_row_make_func' => 'jsg_member_make',
				'result_list_key_is_pri' => 1,
				'return_list' => 1,
			);
			if(is_array($ids)) {
				$p['uid'] = $ids;
				$p['result_count'] = count($ids);
				$p['result_list_order_by_self'] = 1;
			} else {
				$p['sql_where'] = (string) $ids;
				$sl = strtolower($p['sql_where']);
				if(false === strpos($sl, 'where ')) {
					$p['sql_where'] = ' WHERE 1 ' . $p['sql_where'];
				}
				if(false !== strpos($sl, ' limit ')) {
					$p['result_count'] = 99;
				}
			}
			$r = $this->db->get($p);
		}
		return $r;
	}

    
    public function get_member_by_top_fans($limit, $cache_time = 0, $day = 30) {
        $rets = array();
        $limit = (int) $limit;
        if($limit > 0) {
            $day = max(1, (int) $day);
            $cache_time = max(300, (int) $cache_time);
            $p = array(
                'cache_time' => $cache_time,
                'cache_key' => 'member/get_member_by_top_fans-' . "{$limit}-{$cache_time}-{$day}",
                'result_count' => $limit,
                '>@lastactivity' => (TIMESTAMP - (86400 * $day)),
                'sql_order' => ' `fans_count` DESC ',
            );
            $rets = $this->_get_top_member($p);
        }
        return $rets;
    }
    
    
    public function get_member_by_top_credits($limit, $cache_time = 0, $credits_field = '', $day = 30) {
        $rets = array();
        $limit = (int) $limit;
        if($limit < 1) {
            $limit = 20;
        }
        if($this->db->is_field($credits_field)) {
            $day = max(1, (int) $day);
            $cache_time = max(300, (int) $cache_time);
            $p = array(
                'cache_time' => $cache_time,
                'cache_key' => "member/get_member_by_top_credits-{$limit}-{$cache_time}-{$credits_field}-{$day}",
                'result_count' => $limit,
                '>@' . $credits_field => '0',
                '>@lastactivity' => (TIMESTAMP - (86400 * $day)),
                'sql_order' => ' `' . $credits_field . '` DESC, `lastactivity` DESC ',
            );
            $rets = $this->_get_top_member($p);
        }
        return $rets;
    }
    
    
    public function get_member_by_fans($limit, $cache_time = 0, $day = 30) {
        $rets = array();
        $limit = (int) $limit;
        if($limit > 0) {
            $day = max(1, (int) $day);
            $cache_time = max(300, (int) $cache_time);
            if(false === ($uids = cache_file('get', ($cache_key = 'member/get_member_by_fans-' . $limit . '-' . $day)))) {
                                $sql = "select DISTINCT(B.uid) AS uid, COUNT(B.touid) AS `count`
                        FROM ".DB::table(jtable('buddy_fans')->table_name(max(1, (int) MEMBER_ID)))." B
                                LEFT JOIN `".TABLE_PREFIX."members` M on (M.uid=B.uid)
                        WHERE B.dateline>='".(TIMESTAMP - (86400 * $day))."' AND M.face!=''
                        GROUP BY B.uid
                        ORDER BY `count` DESC, `lastactivity` DESC
                        LIMIT {$limit}";
                $query = DB::query($sql);
                $uids = array();
                while(false != ($row = DB::fetch($query))) {
                    $uids[$row['uid']] = $row['uid'];
                }
                cache_file('set', $cache_key, $uids, $cache_time);
            }
            if($uids) {
                $p = array(
                    'cache_time' => $cache_time,
                    'cache_key' => "member/get_member_by_fans-{$limit}-{$cache_time}-{$day}",
                    'result_count' => $limit,
                    'uid' => $uids,
                    'result_list_order_by_self' => 1,
                );
                $rets = $this->_get_top_member($p);
            }
        }
        return $rets;
    }
    
    
    public function get_member_by_topic($limit, $cache_time = 0, $day = 7) {
        $rets = array();
        $limit = (int) $limit;
        if($limit > 0) {
            $day = max(1, (int) $day);
            $cache_time = max(300, (int) $cache_time);
            if(false === ($uids = cache_file('get', ($cache_key = 'member/get_member_by_topic-' . $limit . '-' . $day)))) {
                                $sql = "SELECT DISTINCT(T.uid) AS uid , COUNT(T.tid) AS `count` 
                    FROM `".TABLE_PREFIX."topic` T 
                        left join `".TABLE_PREFIX."members` M on T.uid=M.uid 
                    WHERE T.dateline>='".(TIMESTAMP - (86400 * $day))."' and M.face!='' 
                        GROUP BY uid 
                    ORDER BY `count` DESC 
                    LIMIT {$limit}";
                $query = DB::query($sql);
                $uids = array();
                while(false != ($row = DB::fetch($query))) {
                    $uids[$row['uid']] = $row['uid'];
                }
                cache_file('set', $cache_key, $uids, $cache_time);
            }
            if($uids) {
                $p = array(
                    'cache_time' => $cache_time,
                    'cache_key' => "member/get_member_by_topic-{$limit}-{$cache_time}-{$day}",
                    'result_count' => $limit,
                    'uid' => $uids,
                    'result_list_order_by_self' => 1,
                );
                $rets = $this->_get_top_member($p);
            }
        }
        return $rets;
    }
    
    
    public function get_member_by_reply($limit, $cache_time = 0, $day = 7) {
        $rets = array();
        $limit = (int) $limit;
        if($limit > 0) {
            $day = max(1, (int) $day);
            $cache_time = max(300, (int) $cache_time);
            if(false === ($uids = cache_file('get', ($cache_key = 'member/get_member_by_topic-' . $limit . '-' . $day)))) {
                                $sql = "select DISTINCT(T.touid) AS uid ,  COUNT(T.tid) AS `count` 
                    from `".TABLE_PREFIX."topic` T 
                        left join `".TABLE_PREFIX."members` M on T.touid=M.uid 
                    WHERE M.face !='' and  T.dateline>='".(TIMESTAMP - (86400 * $day))."' and T.touid > 0  
                        GROUP BY `uid` 
                    ORDER BY `count` DESC 
                    LIMIT {$limit}";
                $query = DB::query($sql);
                $uids = array();
                while(false != ($row = DB::fetch($query))) {
                    $uids[$row['uid']] = $row['uid'];
                }
                cache_file('set', $cache_key, $uids, $cache_time);
            }
            if($uids) {
                $p = array(
                    'cache_time' => $cache_time,
                    'cache_key' => "member/get_member_by_reply-{$limit}-{$cache_time}-{$day}",
                    'result_count' => $limit,
                    'uid' => $uids,
                    'result_list_order_by_self' => 1,
                );
                $rets = $this->_get_top_member($p);
            }
        }
        return $rets;
    }

    
    private function _get_top_member($p) {
        $p['cache_time'] = max(300, (int) $p['cache_time']);
        $p['sql_field'] = $p['sql_field'] ? $p['sql_field'] : ' `uid`, `ucuid`, `username`, `nickname`, 
            `face_url`, `face`, `aboutme`,
            `topic_count`, `fans_count`, `digcount`,
            `validate`, `validate_category`,  
            `province`, `city` ';
        $p['result_list_row_make_func'] = 'jsg_member_make';
        $p['result_list_key_is_pri'] = 1;
        $p['return_list'] = 1;
        $rets = $this->db->get($p);
        if($rets) {
            $rets = buddy_follow_html($rets, 'uid', 'follow_html2');
            if($GLOBALS['_J']['plugins']['func']['printuser']) {
                foreach($rets as $row) {
                    jlogic('plugin')->hookscript('printuser', 'funcs', $row, 'printuser');
                }
            }
        }
        return $rets;
    }

    
    public function get_new_vip_uids($limit = 9) {
        $uids = array();
        $limit = max(0, (int) $limit);
        if($limit > 0) {
            $sql = "select distinct(uid) from `".TABLE_PREFIX."validate_category_fields` where is_audit = 1 order by `dateline` desc limit $limit ";
            $query = DB::query($sql);
            while ($value = DB::fetch($query)) {
                $uids[$value['uid']] = $value['uid'];
            }
        }
        return $uids;
    }

    public function get_new_vip_user($limit = 9) {
        $rets = array();
        $uids = $this->get_new_vip_uids($limit);
        if($uids) {
            $rets = jlogic('topic')->GetMember($uids);
        }
        return $rets;
    }
	
        public function clean_new_remind($key, $uid = MEMBER_ID) {
        $ret = false;
        $uid = jfilter($uid, 'int');
        if($uid > 0) {
            $ret = $this->db->update_count($uid, $key, 0, 1, array());
            if($ret && $uid == MEMBER_ID && isset($GLOBALS['_J']['member'][$key])) {
                $GLOBALS['_J']['member'][$key] = 0;
            }
        }
        return $ret;
    }
	
}