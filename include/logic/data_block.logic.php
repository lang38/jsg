<?php
/**
 *
 * 数据区块操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: data_block.logic.php 5543 2014-02-12 08:01:06Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class DataBlockLogic {

	public $member_list = array();

	function __construct() {
		;
	}

	
	function hot_tag_recommend() {
		$rets = array();
		if($GLOBALS['_J']['config']['hot_tag_recommend_enable']) {
			$rets = jconf::get('hot_tag_recommend');
			if($rets['list'] && (TIMESTAMP - $rets['time'] >= 1800)) {
				$for_count = $rets['num'];
				foreach ($rets['list'] as $key=>$val) {
					if($for_count < 1 ) {
						break;
					}
					$for_count--;
					if($val['tag_id']) {
						$rets['list'][$key]['topic_count'] = DB::result_first("select `topic_count` from `".TABLE_PREFIX."tag` where `id`='{$val[tag_id]}' ");
					} elseif($val['name']) {
						$rets['list'][$key]['topic_count'] = DB::result_first("select `topic_count` from `".TABLE_PREFIX."tag` where `name`='{$val[name]}' ");
					} else {
						$rets['list'][$key]['topic_count'] = 0;
					}
				}
				$rets['time'] = TIMESTAMP;
				jconf::set('hot_tag_recommend', $rets);
			}
		}
		return $rets;
	}

	
	function may_interest_user($retry=FALSE,$getNum=4) {
		$uid = MEMBER_ID; 		if($uid < 1) {
			return array();
		}

		
		$buddyids = get_buddyids($uid, $GLOBALS['_J']['config']['topic_myhome_time_limit']);
        
        $type_array = array();

        if($GLOBALS['_J']['config']['same_city']){
            $type_array[] = 'city';
        }

        if(!$retry){
            $retry=$type_array = array('follow','tag','user_tag');
        }else{
            $type_array = $retry;
        }

        $refresh_type = $type_array[array_rand($type_array,1)];

		$cache_time = 1800;
		$cache_key = "{$uid}-may_interest_user-".$refresh_type;
		if(false === ($cache_data=cache_db('get', $cache_key))) {
			$uids = array();
			$uids_limit = 300; 
						if($refresh_type == 'follow') {
				
				if($buddyids) {
										$rs = array();
					$imax = min(10, count($buddyids));
					for($i=0; $i<$imax; $i++) {
						$bid = (int) $buddyids[array_rand($buddyids)];
						if($bid > 0 && !isset($rs[$bid])) {
							$rs[$bid] = 1;
							$p = array(
								'uid' => $bid,
								'buddy_lastuptime' => (TIMESTAMP - 864000),
								'result_count' => 100,
								'sql_order' => ' `dateline` DESC ',
							);
							$ids = get_buddyids($p);
							if($ids) {
								$uids = array_merge($uids, $ids);
                                $uids = array_unique($uids);
								if(count($uids) >= $uids_limit) {
									break;
								}
							}
						}
					}
				}
			}


						elseif($refresh_type == 'tag') {
								$query = DB::query("SELECT `tag` FROM ".DB::table('tag_favorite')." where uid='{$uid}'");
				$touser_tag = array();
				while ($value = DB::fetch($query)) {
					$touser_tag[] = $value['tag'];
				}

								if($touser_tag) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('tag_favorite')." where `tag` in ('".implode("','",$touser_tag)."') ORDER BY `id` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}


						elseif($refresh_type == 'user_tag') {
								$query = DB::query("SELECT `tag_id`,`uid` FROM ".DB::table('user_tag_fields')." where uid='{$uid}'");
				$touser_usertag_uid = array();
				while ($value = DB::fetch($query)) {
					$touser_usertag_uid[$value['tag_id']] = $value['tag_id'];
				}

								if($touser_usertag_uid) {
					$query = DB::query("SELECT `uid` FROM ".DB::table('user_tag_fields')." where `tag_id` in ('".implode("','",$touser_usertag_uid)."') ORDER BY `id` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}


						elseif($refresh_type == 'city') {
								$member_info = jsg_member_info($uid);

								if($member_info['city']) {
					$query = DB::query("select `uid` from ".DB::table('members')." where `city` = '{$member_info['city']}' ORDER BY `lastactivity` DESC LIMIT $uids_limit ");
					while ($value = DB::fetch($query)) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
			}
		} else {
			$uids = $cache_data['uids'];
			$refresh_type = $cache_data['refresh_type'];
		}

				$member_list = array();
		$black_list = array();
		$query = DB::query("select `touid` from `".TABLE_PREFIX."blacklist` where `uid` = '$uid'");
		while ($rs=DB::fetch($query)) {
			$black_list[$rs['touid']] = $rs['touid'];
		}
		if($uids) {
			
			if($buddyids || $black_list) {
				foreach($uids as $k=>$v) {
					if(isset($buddyids[$v])) {
						unset($uids[$k]);
					}
					if(isset($black_list[$v])) {
						unset($uids[$k]);
					}
                    if($v == $uid){
                        unset($uids[$k]);
                    }
				}
			}

			if($uids) {
				if(false===$cache_data) {
					$cache_data['uids'] = $uids;
					$cache_data['refresh_type'] = $refresh_type;

					cache_db('set', $cache_key, $cache_data, $cache_time);
				}

				
				$rand_number = count($uids) > $getNum ? $getNum : count($uids);
				$rand_uids1 = array_rand($uids,$rand_number);
				if($rand_uids1){
                    if(is_array($rand_uids1)){
                        foreach ($rand_uids1 as $key => $val) {
                            $rand_uids[$val] = $uids[$val];
                        }
                    } else {
                        $rand_uids[$rand_uids1] = $uids[$rand_uids1];
                    }
				}
				if($rand_uids) {
										$condition = " WHERE `uid` IN ('" . implode("','", $rand_uids) . "') LIMIT {$rand_number} ";
					$member_list = jlogic('topic')->GetMember($condition);
					$member_list = jlogic('buddy')->follow_html($member_list, 'uid', 'follow_html2',0,1);

										foreach($member_list as $k=>$row) {
						if($row['is_follow']) {
							unset($member_list[$k]);
						} else {
							$_uid = $row['uid'];
							$count = 1;
							                            $i = 0;
                            $moreHTML = '';
							if('follow' == $refresh_type) {
								$fansids = jlogic('buddy')->get_fansids(array(
									'uid' => $_uid,
									'touid' => $buddyids,
								));
                                $fan_info = jtable('members')->get(array('uid'=>$fansids));
                                foreach ($fan_info['list'] as $key=>$val) {
                                    if($i > 1){
                                        break;
                                    }
                                    $i++;
                                    $moreHTML = $moreHTML . ($moreHTML ?  "、" : '') . "<a href='index.php?mod={$val['uid']}'>{$val['nickname']}</a>";
                                }
								$count = count($fansids);
															} elseif ('user_tag' == $refresh_type) {
                                $user_tag_list = array();
								$query = DB::query("SELECT A.`tag_id`,A.`tag_name` FROM ".DB::table('user_tag_fields')." A, ".DB::table('user_tag_fields')." B WHERE A.uid='$_uid' AND B.uid='$uid' AND B.tag_id=A.tag_id");
                                while ($rs = DB::fetch($query)) {
                                    $user_tag_list[$rs['tag_id']] = $rs;
                                    if($i < 2){
                                        $moreHTML .= ($moreHTML ?  '、' : '') . $rs['tag_name'];
                                    }
                                    $i++;
                                }
                                $count = count($user_tag_list);
															} elseif ('tag' == $refresh_type) {
                                $user_tag_list = array();
                                $query = DB::query("SELECT A.id,A.tag FROM ".DB::table('tag_favorite')." A, ".DB::table('tag_favorite')." B WHERE A.uid='$_uid' AND B.uid='$uid' AND B.tag=A.tag");
                                while ($rs = DB::fetch($query)) {
                                    $user_tag_list[$rs['id']] = $rs;
                                    if($i < 2){
                                        $moreHTML .= ($moreHTML ?  '、' : '') . "<a href='inde.php?mod=tag&code={$rs['tag']}'>{$rs['tag']}</a>";
                                    }
                                    $i++;
                                }
                                $count = count($user_tag_list);
							}
                            $row['moreHtml'] = $moreHTML;
							$row['count'] = $count;
							$row['refresh_type'] = $refresh_type;
							$member_list[$k] = $row;
						}
					}
				}
			}
		}
        $this->member_list = array_merge($this->member_list,$member_list);
				if((!$member_list || count($this->member_list)<5) && count($retry) > 1) {
            $key = array_search($refresh_type, $retry);
            unset($retry[$key]);			$member_list = $this->may_interest_user($retry,$getNum);
		}


		return array_slice($this->member_list,0,5);
	}

	
	function _param_id($d) {
		if(is_string($d)) {
			$d = explode(',', str_replace(array("'", '"'), '', $d));
		}
		if($d) {
			$d = (array) $d;
		}
		return $d;
	}
}

?>