<?php
/**
 *
 * 微博广场相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: plaza.logic.php 5573 2014-02-25 05:09:42Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class PlazaLogic {

	public function __construct() {
		;
	}

	public function index_topic() {
		if(false === ($index_topic = cache_db('mget', 'plaza_index_topic'))) {
		$hottime = (int)jconf::get('cache', 'topic_plaza', 'hottime');
		$hottime = $hottime ? $hottime : 2592000;
		$hotwhere = " AND `dateline`>'" . (TIMESTAMP - $hottime) . "'";
		$recommend = $recommend_pic = $dig = $dig_pic = $reply = $reply_pic = $newreply = $newreply_pic = $newdig = $newdig_pic = $picid = $pics = $temp = array();
				DB::query("UPDATE ".DB::table('topic')." SET `imageid` = '' where `imageid`='0'");
				$query = DB::query("SELECT r.tid,r.r_title,t.imageid FROM ".DB::table('topic_recommend')." r LEFT JOIN ".DB::table('topic')." t ON r.tid=t.tid where t.imageid <> '' ORDER BY r.dateline DESC LIMIT 3");
		while ($value = DB::fetch($query))
		{
			$temp = explode(',',$value['imageid']);
			$value['imageid'] = $picid[] = $temp[0];
			$recommend_pic[] = $value;
		}
		$query = DB::query("SELECT r.tid,r.r_title,t.content FROM ".DB::table('topic_recommend')." r LEFT JOIN ".DB::table('topic')." t ON r.tid=t.tid where t.imageid = '' ORDER BY r.dateline DESC LIMIT 3");
		while ($value = DB::fetch($query))
		{
			$value['content'] = str_replace("\n",'',cut_str(strip_tags($value['content']),140,'...'));
			$recommend[] = $value;
		}
				$query = DB::query("SELECT tid,content,imageid,digcounts FROM ".DB::table('topic')." where digcounts > 0 and `type` = 'first' and imageid <> '' ".$hotwhere." ORDER BY digcounts DESC,dateline desc LIMIT 1");
		while ($value = DB::fetch($query))
		{
			$temp = explode(',',$value['imageid']);
			$value['imageid'] = $picid[] = $temp[0];			$value['content'] = cut_str(strip_tags($value['content']),30,'');
			$dig_pic[] = $value;
		}
		$query = DB::query("SELECT tid,content,digcounts FROM ".DB::table('topic')." where digcounts > 0 and `type` = 'first' and imageid = '' ".$hotwhere." ORDER BY digcounts DESC,dateline desc LIMIT 12");
		while ($value = DB::fetch($query))
		{
			$value['content'] = cut_str(strip_tags($value['content']),36);
			$dig[] = $value;
		}
				$query = DB::query("SELECT tid,content,imageid,replys FROM ".DB::table('topic')." where replys > 0 and `type` IN('reply','both') and imageid <> '' ".$hotwhere." ORDER BY replys DESC,dateline desc LIMIT 1");
		while ($value = DB::fetch($query))
		{
			$temp = explode(',',$value['imageid']);
			$value['imageid'] = $picid[] = $temp[0];
			$value['content'] = cut_str(strip_tags($value['content']),30,'');
			$reply_pic[] = $value;
		}
		$query = DB::query("SELECT tid,content,replys FROM ".DB::table('topic')." where replys > 0 and `type` IN('reply','both') and imageid = '' ".$hotwhere." ORDER BY replys DESC,dateline desc LIMIT 12");
		while ($value = DB::fetch($query))
		{
			$value['content'] = cut_str(strip_tags($value['content']),36);
			$reply[] = $value;
		}
				$query = DB::query("SELECT tid,content,imageid,digcounts FROM ".DB::table('topic')." where digcounts > 0 and type = 'first' and imageid <> '' ORDER BY dateline desc LIMIT 1");
		while ($value = DB::fetch($query))
		{
			$temp = explode(',',$value['imageid']);
			$value['imageid'] = $picid[] = $temp[0];
			$value['content'] = cut_str(strip_tags($value['content']),30,'');
			$newdig_pic[] = $value;
		}
		$query = DB::query("SELECT tid,content,digcounts FROM ".DB::table('topic')." where digcounts > 0 and type = 'first' and imageid = '' ORDER BY dateline desc LIMIT 12");
		while ($value = DB::fetch($query))
		{
			$value['content'] = cut_str(strip_tags($value['content']),36);
			$newdig[] = $value;
		}
				$query = DB::query("SELECT tid,content,imageid,replys FROM ".DB::table('topic')." where replys > 0 and `type` IN('reply','both') and imageid <> '' ORDER BY dateline desc LIMIT 1");
		while ($value = DB::fetch($query))
		{
			$temp = explode(',',$value['imageid']);
			$value['imageid'] = $picid[] = $temp[0];
			$value['content'] = cut_str(strip_tags($value['content']),30,'');
			$newreply_pic[] = $value;
		}
		$query = DB::query("SELECT tid,content,replys FROM ".DB::table('topic')." where replys > 0 and `type` IN('reply','both') and imageid = '' ORDER BY dateline desc LIMIT 12");
		while ($value = DB::fetch($query))
		{
			$value['content'] = cut_str(strip_tags($value['content']),36);
			$newreply[] = $value;
		}
				$query = DB::query("SELECT id,site_url,photo FROM ".DB::table('topic_image')." where id IN(".jimplode($picid).")");
		while ($value = DB::fetch($query))
		{
			$pics[$value['id']] = ($value['site_url'] ? $value['site_url'].'/' : '').str_replace('./','',str_replace('_o.','_p.',$value['photo']));
		}
		$index_topic = array(
			'recommend_pic' => $recommend_pic,
			'recommend' => $recommend,
			'dig_pic' => $dig_pic,
			'dig' => $dig,
			'reply_pic' => $reply_pic,
			'reply' => $reply,
			'newreply_pic' => $newreply_pic,
			'newreply' => $newreply,
			'newdig_pic' => $newdig_pic,
			'newdig' => $newdig
		);
		foreach($index_topic as $key => $val){
			foreach($val as $k => $v){
				if($v['imageid']){
					$index_topic[$key][$k]['img'] = $pics[$v['imageid']];
					unset($index_topic[$key][$k]['imageid']);
				}
			}
		}
		$cache_time = (int)jconf::get('cache', 'topic_plaza', 'topic');
		cache_db('mset', 'plaza_index_topic', $index_topic, $cache_time);
		}
		return $index_topic;
	}

	public function new_topic($p) {
		$ps = array(
			'cache_time' => max(0, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$ps['cache_key'] = 'topic-new_topic';
		$ps['order'] = ' `dateline` DESC ';
		$ps['where'] = ' `type` IN (' . jimplode(get_topic_type()) . ') ';
		return $this->_get($ps);
	}

	public function new_reply($p) {
		$ps = array(
			'cache_time' => max(0, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$ps['cache_key'] = 'topic-new_reply';
		$ps['order'] = ' `dateline` DESC ';
		$ps['where'] = " `type` IN ('reply', 'both') AND `totid`>'0' ";
		return $this->_get($ps);
	}

	public function new_dig($p) {
		$ps = array(
			'cache_time' => max(0, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$ps['cache_key'] = 'topic-new_dig';
		$ps['order'] = ' `dateline` DESC ';
		$ps['where'] = " `type` IN (" . jimplode(get_topic_type()) . ") AND `digcounts`>'0' ";
		return $this->_get($ps);
	}

	public function new_forward($p) {
		$ps = array(
			'cache_time' => max(0, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$ps['cache_key'] = 'topic-new_forward';
		$ps['order'] = ' `dateline` DESC ';
		$ps['where'] = " `type` IN ('forward', 'both') AND `totid`>'0' ";
		return $this->_get($ps);
	}

	public function hot_reply($p) {
		$ps = array(
			'cache_time' => max(300, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$day = (in_array($p['day'], array(1, 7, 14, 30, 90)) ? $p['day'] : 0);
		$ps['cache_key'] = 'topic-hot_reply-' . $day;
		$ps['order'] = ' `replys` DESC, `dateline` DESC ';
		$dwhere = $day ? " AND `dateline`>'" . (TIMESTAMP - ($day * 86400)) . "'" : '';
		$ps['where'] = " `type` IN ('forward', 'both') AND `replys`>'0' ".$dwhere;
		return $this->_get($ps);
	}

	public function hot_dig($p) {
		$ps = array(
			'cache_time' => max(300, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$day = (in_array($p['day'], array(1, 7, 14, 30, 90)) ? $p['day'] : 0);
		$ps['cache_key'] = 'topic-hot_dig-' . $day;
		$ps['order'] = ' `digcounts` DESC, `dateline` DESC ';
		$dwhere = $day ? " AND `dateline`>'" . (TIMESTAMP - ($day * 86400)) . "'" : '';
		$ps['where'] = " `type` IN (" . jimplode(get_topic_type()) . ") AND `digcounts`>'0' ".$dwhere;
		return $this->_get($ps);
	}

	public function hot_forward($p) {
		$ps = array(
			'cache_time' => max(300, (int) $p['cache_time']),
			'page_url' => $p['page_url'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
		}
		$day = (in_array($p['day'], array(1, 7, 14, 30, 90)) ? $p['day'] : 0);
		$ps['cache_key'] = 'topic-hot_forward-' . $day;
		$ps['order'] = ' `forwards` DESC, `dateline` DESC ';
		$dwhere = $day ? " AND `dateline`>'" . (TIMESTAMP - ($day * 86400)) . "'" : '';
		$ps['where'] = " `type` IN ('forward', 'both') AND `forwards`>'0' ".$dwhere;
		return $this->_get($ps);
	}

	public function new_tc($p) {
		$member = jsg_member_info(MEMBER_ID);
		$province_id = max(0, (int) $p['province_id']);
		$city_id = max(0, (int) $p['city_id']);
		$area_id = max(0, (int) $p['area_id']);
		if($province_id < 1) {
			if(($province = $member['province'])) {
				$province_id = jlogic('common_district')->get_id_by_name($province);
				if($province_id > 0 && ($city = $member['city'])) {
					$city_id = jlogic('common_district')->get_id_by_name($city);
					if($city_id > 0) {
						$area = $member['area'];
					}
				}
			}
		} else {
			$province = jlogic('common_district')->get_name_by_id($province_id);
			if($province && $city_id > 0) {
				$city = jlogic('common_district')->get_name_by_id($city_id);
				if($city && $area_id > 0) {
					$area = jlogic('common_district')->get_name_by_id($area_id);
				}
			}
		}
		$ps = array(
			'cache_time' => max(0, (int) $p['cache_time']),
			'cache_key' => 'topic-new_tc-' . "{$province}-{$city}-{$area}",
		
			'page_url' => $p['page_url'],
		
			'province' => $province,
			'city' => $city,
			'area' => $area,
			'type' => get_topic_type(),
			'vip' => $GLOBALS['_J']['config']['only_show_vip_topic'],
		);
		if(isset($p['perpage'])) {
			$ps['perpage'] = (int) $p['perpage'];
			if($ps['perpage'] < 1) {
				$ps['perpage'] = 20;
			}
		}
		
		$rets = jlogic('topic_list')->get_tc_data($ps);
		if(is_array($rets)) {
			$rets['province'] = $province;
			$rets['city'] = $city;
			$rets['area'] = $area;
                        $rets['province_id'] = $province_id;
                        $rets['city_id'] = $city_id;
                        $rets['area_id'] = $area_id;
			if($member) {
				$rets['member'] = $member;
			}                        
                        if($rets['list']) {
                                $rets['parent_list'] = jlogic('topic')->get_parent_list($rets['list']);
								$rets['relate_list'] = jlogic('topic')->get_relate_list($rets['list']);

                                if($GLOBALS['_J']['plugins']['func']['printtopic']) {
                                        foreach($rets['list'] as $row) {
                                                jlogic('plugin')->hookscript('printtopic', 'funcs', $row, 'printtopic');
                                        }
                                }
                        }
		}
		return $rets;
	}

	public function new_pic($p) {
		;
	}

	
	private function _get($p) {
		$p['cache_time'] = max(0, (int) $p['cache_time']);
		if(isset($p['perpage'])) {
			$p['perpage'] = (int) $p['perpage'];
			if($p['perpage'] < 1) {
				$p['perpage'] = 20;
			}
		}
		if($GLOBALS['_J']['config']['only_show_vip_topic']) {
                        $p['vip_uids'] = jsg_get_vip_uids();
			$p['where'] .= ' AND `uid` IN (' . $p['vip_uids'] . ') ';
		}
		$rets = jlogic('topic_list')->get_data($p);
                if(is_array($rets)) {
                    $rets['params'] = array('pp_time'=>$p['perpage'], 'c_time'=>$p['cache_time'], 'uid'=>base64_encode(serialize((array) $p['vip_uids'])));
                    if($rets['list']) {
                            $rets['parent_list'] = jlogic('topic')->get_parent_list($rets['list']);	
							$rets['relate_list'] = jlogic('topic')->get_relate_list($rets['list']);
                                                        if($GLOBALS['_J']['plugins']['func']['printtopic']) {
                                    foreach($rets['list'] as $row) {
                                            jlogic('plugin')->hookscript('printtopic', 'funcs', $row, 'printtopic');
                                    }
                            }
                    }
                }                    
		return $rets;
	}

}