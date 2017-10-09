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
 * @version $Id: member_profile.logic.php 5462 2014-01-18 01:12:59Z wuliyong $
 */
if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class MemberProfileLogic {

    public function __construct() {
        ;
    }

    
    function getProfile() {
        $profile = array();

        $sql = "select * from `" . TABLE_PREFIX . "common_member_profile_setting` order by `displayorder` desc ";
        $query = DB::query($sql);
        while ($rs = DB::fetch($query)) {
            $profile[$rs['fieldid']] = $rs;
        }

        return $profile;
    }

    
    function setProfileOrder($order = array()) {
        DB::query("update `" . TABLE_PREFIX . "common_member_profile_setting` set `displayorder` = '0' ");
        if ($order) {
            foreach ($order as $k => $v) {
                $v = (int) $v;
                DB::query("update `" . TABLE_PREFIX . "common_member_profile_setting` set `displayorder` = '$v' where `fieldid` = '$k' ");
            }
        }
    }

    
    function setProfileTitle($title = array()) {
        if ($title) {
            foreach ($title as $k => $v) {
                if ($v) {
                    DB::query("update `" . TABLE_PREFIX . "common_member_profile_setting` set `title` = '$v' where `fieldid` = '$k' ");
                }
            }
        }
    }

    
    function getMemberProfileSet($uid) {
        $profile_set = DB::result_first("select `profile_set` from `" . TABLE_PREFIX . "memberfields` where `uid` = '$uid'");
        if ($profile_set) {
            return unserialize($profile_set);
        } else {
            return array();
        }
    }

    
    function getMemberProfileInfo($uid) {
        $member = DB::fetch_first("select * from `" . TABLE_PREFIX . "members_profile` where `uid` = '$uid'");
        #处理出生地
        if(!$member){
            $member = array();
        }
        if ($member['birthcity']) {
            $birthcity = explode('-', $member['birthcity']);
            $new_b_city['province'] = DB::result_first("select `name` from `" . TABLE_PREFIX . "common_district` where `id` = '$birthcity[0]'");
            $new_b_city['city'] = DB::result_first("select `name` from `" . TABLE_PREFIX . "common_district` where `id` = '$birthcity[1]'");
            $new_b_city['area'] = DB::result_first("select `name` from `" . TABLE_PREFIX . "common_district` where `id` = '$birthcity[2]'");
            $new_b_city['street'] = DB::result_first("select `name` from `" . TABLE_PREFIX . "common_district` where `id` = '$birthcity[3]'");
            $member['birthcity'] = implode('-', $new_b_city);
        }
        return $member;
    }

    
    public function set_member_profile_info($uid, $option) {
    	$r = false;
    	$uid = (int) $uid;
    	if($uid > 0 && $option) {
    		settype($option, 'array');
	    	if(count($option)) {
	    		$option['last_update'] = TIMESTAMP;
	    	}
	    	foreach($option as $k=>$v) {
	    		$option[$k] = (string) $v;
	    	}
	        $count = jtable('members_profile')->count(array('uid' => $uid));
	        if ($count) {
	            $r = jtable('members_profile')->update($option, array('uid' => $uid));
	        } else {
	            $option['uid'] = $uid;
	            $r = jtable('members_profile')->insert($option, 1);
	        }
    	}
    	return $r;
    }

    
    public function set_member_info($uid, $option) {
        $count = jtable('members')->count(array('uid' => $uid));
        if ($count) {
            $r = jtable('members')->update($option, array('uid' => $uid));
        } else {
            $option['uid'] = $uid;
            $r = jtable('members')->insert($option, 1);
        }
        if ($r)
            return TRUE;
        else
            return false;
    }

    public function search($keyword = '') {
    	$rets = array();
    	$keyword = jfilter($keyword, 'txt');
    	$search = jconf::get('profilesearch');
    	if($keyword && $search) {
    		$sql_where = build_like_query("`".implode("`, `", array_keys($search))."`", $keyword);
    		if($sql_where) {
    			$sql_where .= " AND `last_update`>'" . (strtotime(date('Y-m-d')) - 86400000) . "' ";
		    	$p = array(
		    		'cache_time' => 300,
		    		'result_count' => 300,
		    		'sql_where' => $sql_where,
		    		'sql_order' => ' `last_update` DESC, `uid` DESC ',
		    	);
		    	$rets = jtable('members_profile')->get_ids($p, 'uid');
    		}
    	}
    	return $rets;
    }

    public function get_by_uid($uids) {
    	$rets = array();
    	if($uids) {
    		settype($uids, 'array');
    		$p = array(
    			'uid' => $uids,
    			'result_count' => count($uids),
    			    			'return_list' => true,
    		);
    		$_rets = jtable('members_profile')->get($p);
    		foreach($_rets as $_r) {
    			$rets[$_r['uid']] = $_r;
    		}
    	}
    	return $rets;
    }

}