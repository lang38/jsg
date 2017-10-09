<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename reward.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1997199471 18815 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}
class RewardLogic {

	function RewardLogic(){

	}

	
	function is_exists($id)
	{
		$id = (int) $id;
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('reward')." WHERE id='{$id}'");
		return $count;
	}

	
	function CheckReward($tid,$rid){
		$tid = (int) $tid;
		$rid = (int) $rid;
		$rewardInfo = DB::fetch_first("select `id`,`uid`,`image` FROM `".TABLE_PREFIX."reward` WHERE `tid` = '$tid' AND `id` = '$rid'");
		if($rewardInfo){
			if($rewardInfo['image'] > 0){
				$rewardInfo['image'] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '{$rewardInfo['image']}'");
				$rewardInfo['image'] = $rewardInfo['image'] ? $rewardInfo['image'] : './images/reward_noPic.gif';
			}

			return $rewardInfo;
		} else {
			return array();
		}
	}

	
	function add($param,$id=0){
		if (!$param) return false;

		if($param['topic']){
            if(!$param['tid']){
                $topic = array(
                    'content' => $param['topic'],
                    'from' => 'reward',
                    'type' => 'first',
                    'item' => 'reward',
                    'item_id' => 0,
                );
                $return = jlogic('topic')->Add($topic);
            }else{
                $return = jlogic('topic')->Modify($param['tid'],$param['topic']);
            }

			if (is_array($return) && $return['tid']){
				$data['tid'] = $return['tid'];
			}else if(is_string($return)){
				return $return;
			} else {
				return '转发的微博发布失败...';
			}
		}

		$data['uid'] = MEMBER_ID;
		#标题
		$data['title'] = $param['title'];
		#开始时间
		$data['fromt'] = $param['fromt'];
		#结束时间
		$data['tot'] = $param['tot'];
		#描述
		$data['content'] = $param['content'];
        #活动图片
        $data["event_image"] = $param["event_image"];
		$i = 1;
		foreach ($param['prize_name'] as $key=>$val) {

            $i++;
			if((!$val || !$param['prize'][$key])&&$key<6){
				continue;
			}

			$prize[] = array(
				'prize_name' => jfilter($val,'txt'),
				'prize' => jfilter($param['prize'][$key],'txt'),
				'prize_num' => (int) $param['prize_num'][$key],
				'prize_image' => $param['prize_image'][$key],
			);
			if(!$data['image'] && $param['prize_image'][$key]){
				$data['image']= $param['prize_image'][$key];
			}
		}
		#奖品
		$data['prize'] = serialize($prize);
		#转发规则
		$rules = array();
		foreach ($param['rules'] as $key => $val) {
			if (!$val=trim($val)) continue;
			switch ($key) {
				case 'tag':
					$rules['tag'] = explode('|',$val);
					$rules['tag'] = array_remove_empty($rules['tag']);
					break;
				case 'user':
					$user = str_replace('|',"','",$val);
					$sql = "select `uid`,`nickname` from `".TABLE_PREFIX."members` where `nickname` in ('$user')";
					$query = DB::query($sql);
					while ($rs = DB::fetch($query)) {
						$user_arr[$rs['uid']]['nickname'] = $rs['nickname'];
					}
					$rules['user'] = $user_arr;
					break;
				case 'at_num':
                    $num = (int) $val;
					$num > 0 && $rules['at_num'] = $num;
				default:
					break;
			}
		}
		$data['rules'] = serialize($rules);
		#发布时间
		$data['posttime'] = TIMESTAMP;
		#发布IP
		$data['postip'] = ip2long($GLOBALS['_J']['client_ip']);

		if($id){
			$reward = $this->getRewardInfo($id);
			if(!$reward){return '有奖转发不存在或已删除。';}
			$where = " where `id` = '$id' ";
			$rid = $this->update($data,TABLE_PREFIX.'reward',$where);
			if(!$rid) return '更新失败';
			$rid = $id;
		} else {
			$rid = $this->insert($data,TABLE_PREFIX.'reward');
		}

		if($rid > 0){
			#给图片赋予ID
			$param['prize_image'] = array_remove_empty($param['prize_image']);
			if(!empty($param['prize_image'])) {
				$ids = array();
				if(is_array($param['prize_image'])) {
					foreach($param['prize_image'] as $v) {
						$ids[] = (int) $v;
					}
				} else {
					$ids[] = (int) $param['prize_image'];
				}
				if($ids){
					DB::query("update `".TABLE_PREFIX."reward_image` set `rid` = '$rid' where `id` in (" . jimplode($ids) . ")");
				}
			}

			#将item_id赋给微博
			DB::query("update `".TABLE_PREFIX."topic` set `item_id` = '$rid' where `tid` = '$data[tid]'");
		}
		return $rid;
	}

	
	function getRewardInfo($id){
		if($id < 1) return array();

		$reward = array();

		$sql = "select * from `".TABLE_PREFIX."reward` where `id` = '$id'";
		$reward = DB::fetch_first($sql);

		if($reward){
			$reward['from_time'] = date('Y-m-d H:i',$reward['fromt']);
			$reward['to_time'] = date('Y-m-d H:i',$reward['tot']);
			$reward['post_time'] = date('Y-m-d H:i:s',$reward['posttime']);
			$reward['prize'] = unserialize($reward['prize']);
			$reward['rules'] = unserialize($reward['rules']);
			$reward['postip'] = long2ip($reward['postip']);

			$reward['event_image_path'] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '$reward[event_image]'");

			if($reward['prize']){
				foreach ($reward['prize'] as $key => $val) {
					if($image_id = (int)$val['prize_image']){
						$reward['prize'][$key]['prize_image_url'] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '$image_id'");

						if($image_id == $reward['image']){
							$reward['image'] = $reward['prize'][$key]['prize_image_url'];
						}
					}
					$reward['prize'][$key]['prize_image_url'] = $reward['prize'][$key]['prize_image_url'] ? $reward['prize'][$key]['prize_image_url'] : './images/reward_noPic.gif';
				}
				#默认图片
				$reward['image'] = $reward['image'] ? $reward['image'] : './images/reward_noPic.gif';
			}
			$reward['event_image_path']||$reward['event_image_path'] = $reward['image'];
			if($reward['fromt'] < TIMESTAMP && TIMESTAMP < $reward['tot']){
				$reward['type'] = 1;
				$reward['reward_type'] = '正在进行';
			} else if($reward['fromt'] > TIMESTAMP){
				$reward['type'] = 0;
				$reward['reward_type'] = '等待开始';
			} else if (TIMESTAMP > $reward['tot']){
				$reward['type'] = 2;
				$reward['reward_type'] = '已经结束';
			}
			$reward['time_lesser'] = ($reward['tot'] > TIMESTAMP) ? $reward['tot'] - TIMESTAMP : 0;

			$member = jsg_member_info($reward['uid']);
			$reward['username'] = $member['username'];
			$reward['nickname'] = $member['nickname'];
			$reward['validate_html'] = $member['validate_html'];

			#需要转发的微博
			if($reward['tid'] > 0){
								$reward['topic'] = jtable('topic_more')->get_longtext($reward['tid']);
				$reward['topic_content'] = cut_str($reward['topic'],150);
			}

			#需关注人与我的关系
			$my_buddyids = get_buddyids(MEMBER_ID);
			if($reward['rules']['user']){
				foreach ($reward['rules']['user'] as $uid => $val) {
					if (isset($my_buddyids[$uid])) {
						$reward['rules']['user'][$uid]['follow_html'] = follow_html($uid,1);
					} else {
						$reward['rules']['user'][$uid]['follow_html'] = follow_html($uid,0);
					}
				}
			}
		}

		return $reward;
	}

	
	function getRewardList($param){
		$where = '';

		if($fromt = $param['fromt']){
			$where_arr['fromt'] = " `fromt` > '$fromt' ";
		}

		if($tot = $param['tot']){
			$where_arr['tot'] = " `tot` < '$tot' ";
		}

		if($param['id']){
			if(is_array($param['id'])){
				$where_arr['id'] = " `id` in('".implode("','",$param['id'])."') ";
			} else {
				$where_arr['id'] = " `id` = '{$param[id]}' ";
			}
		}

		if($param['uid']){
			if(is_array($param['uid'])){
				$where_arr['uid'] = " `uid` in('".implode("','",$param['uid'])."') ";
			} else {
				$where_arr['uid'] = " `uid` = '$param[uid]' ";
			}
		}

		if($param['recd']){
			$where_arr['recd'] = " `recd` = '$param[recd]' ";
		}

		if($param['where']){
			$where_arr['where'] = $param['where'];
		}

		if(isset($param['verify'])){
			$where_arr['verify'] = " `verify` = '{$param[verify]}' ";
		} else {
			$where_arr['verify'] = ' `verify` = 1 ';
		}

		if($where_arr){
			$where = ' where ' . implode(' and ',$where_arr);
		}

		if($param['page']){
			$count = DB::result_first("select count(*) from `".TABLE_PREFIX."reward` $where ");
			if($count){
				$page_arr = page($count,$param['per_page_num'],$param['page_url'],$param['_config'],$param['page_set']);
			}
			$limit = $page_arr['limit'];
		} else if ($param['limit']) {
			$limit = ' limit ' . $param['limit'];
		} else {
			return array();
		}

		$order = ' order by ' . ($param['order'] ? $param['order'] : ' `id` desc ');
		$sql = "select * from `".TABLE_PREFIX."reward` $where $order $limit ";

		$query = DB::query($sql);
		$reward_list = array();
		while ($rs = DB::fetch($query)) {
			$reward_list[$rs['id']] = $rs;
			$reward_list[$rs['id']]['from_time'] = date('Y-m-d H:i:s',$rs['fromt']);
			$reward_list[$rs['id']]['to_time'] = date('Y-m-d H:i:s',$rs['tot']);
			$reward_list[$rs['id']]['post_time'] = date('Y-m-d H:i:s',$rs['posttime']);
			$reward_list[$rs['id']]['postip'] = long2ip($rs['postip']);
			if($image_id = $rs['image']){
				$reward_list[$rs['id']]['image'] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '$image_id'");
			}
			if($eventImageID = $rs['event_image']){
				$reward_list[$rs['id']]['event_image_path'] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '$eventImageID'");
			}
			#默认图片

			$reward_list[$rs['id']]['image'] = $reward_list[$rs['id']]['image'] ? $reward_list[$rs['id']]['image'] : './images/reward_noPic.gif';
			$reward_list[$rs['id']]['event_image_path'] || $reward_list[$rs['id']]['event_image_path'] = $reward_list[$rs['id']]['image'];
			$reward_list[$rs['id']]['content_cut'] = cut_str(strip_tags($rs['content']),20);
			$member = jsg_member_info($rs['uid']);
			$reward_list[$rs['id']]['username'] = $member['username'];
			$reward_list[$rs['id']]['nickname'] = $member['nickname'];
			$reward_list[$rs['id']]['face'] = face_get($rs['uid']);
			$reward_list[$rs['id']]['validate_html'] = $member['validate_html'];
			if($rs['recd']){
				$reward_list[$rs['id']]['recd_checked'] = 'checked';
				$reward_list[$rs['id']]['recd_html'] = '<font color=blue>是</font>';
			}else{
				$reward_list[$rs['id']]['recd_html'] = '否';
			}
		}

		return array('reward_list'=>$reward_list,'page_arr'=>$page_arr,'count'=>$count?$count:$limit);
	}

	
	function DoDraw($rid,$pid){
		$r_uid = array();
		$new_uid = array();
		$reward = $this->getRewardInfo($rid);
		if(!$reward){ return '请确认你要抽奖的有奖转发是否存在。';}

		if($reward['tot'] > TIMESTAMP){ return '该有奖转发还没有结束，不能抽奖。'; }

		#抽取奖励数
		$prize_num = (int) $reward['prize'][$pid]['prize_num'];

		$uid = $this->getPrizeUserUid($rid,$pid);
		$uid2 = $this->getprizeUserUid2($rid);

		if(count($uid) >= $prize_num){ return '该有奖转发的['.$reward['prize'][$pid]['prize_name'].']已全部抽取';}

		$prize_num = $prize_num - count($uid);

		if($prize_num > 0){
			if($uid2){$except_uid = " and `uid` not in(".jimplode($uid2).") ";}
			$sql = "select distinct `uid` from `".TABLE_PREFIX."reward_user` where `rid` = '$rid' and `on` = 1 $except_uid limit 9999999";
			$query = DB::query($sql);
			while ($rs = DB::fetch($query)) {
				if($uid2 && in_array($rs['uid'],$uid2)) {
					continue;
				}
				$uids[$rs['uid']] = $rs['uid'];
			}
            if($prize_num < count($uids)){
                $new_uid_key = array_rand($uids,$prize_num);
                if(is_array($new_uid_key)){
                    foreach ($new_uid_key as $val) {
                        $new_uid[$val] = $uids[$val];
                    }
                } else {
                    $new_uid[$new_uid_key] = $uids[$new_uid_key];
                }
            } else {
                $new_uid = $uids;
            }
		}

				$pm_to_user = '';
		if($new_uid){
			#给新抽取出来的用户发私信通知下
			$query = DB::query("select `uid`,`username`,`nickname` from `".TABLE_PREFIX."members` where uid in ('".implode(',',$new_uid)."')");
			while ($rs = DB::fetch($query)) {
				$pm_to_user = $pm_to_user ? $pm_to_user.','.$rs['nickname'] : $rs['nickname'];

				$r_uid[$rs['uid']] = $rs;
				$r_uid[$rs['uid']]['prize_name'] = $reward['prize'][$pid]['prize_name'];
				$r_uid[$rs['uid']]['prize'] = $reward['prize'][$pid]['prize'];

				#记录
				DB::query("insert into `".TABLE_PREFIX."reward_win_user` (`uid`,`rid`,`pid`,`dateline`) values ('$rs[uid]','$rid','$pid','".TIMESTAMP."')");
			}
		}

		if($pm_to_user){
			$post = array(
				'to_user' => $pm_to_user,
				'message' => '恭喜你在有奖转发【<a href="index.php?mod=reward&code=detail&id='.$rid.'" target="_blank">'.$reward['title'].'</a>】中获得'.$reward['prize'][$pid]['prize_name'].'：'.$reward['prize'][$pid]['prize'].'。请及时联系发起者（注意：本私信由活动发起者发送，请确认）。',
			);
			jlogic('pm')->pmSend($post);
		}

		return $r_uid;
	}

	
	function getPrizeUserUid($rid,$pid){
		$sql = "select `uid` from `".TABLE_PREFIX."reward_win_user` where `rid` = '$rid' and `pid` = '$pid' ";
		$query = DB::query($sql);

		$uid = array();
		while ($rs = DB::fetch($query)) {
			$uid[$rs['uid']] = $rs['uid'];
		}

		return $uid;
	}

	function getprizeUserUid2($rid){
		$sql = "select `uid` from `".TABLE_PREFIX."reward_win_user` where `rid` = '$rid' ";
		$query = DB::query($sql);

		$uid = array();
		while ($rs = DB::fetch($query)) {
			$uid[$rs['uid']] = $rs['uid'];
		}

		return $uid;
	}

	
	function getJoinedRewardRid($uid=MEMBER_ID){
		$rid = array();
		$sql = "select distinct(`rid`) from `".TABLE_PREFIX."reward_user` where `uid` = '$uid' and `on` = 1 order by `id` desc ";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)) {
			$rid[$rs['rid']] = $rs['rid'];
		}

		return $rid;
	}


	
	function getUserPrize($rid,$uid=MEMBER_ID){
		$reward = $this->getRewardInfo($rid);
		if(!$reward) return array();

		$prize_user = DB::fetch_first("select `dateline`,`pid` from `".TABLE_PREFIX."reward_win_user` where `rid` = '$rid' and `uid` = '$uid'");

		if($prize_user){
			$ret['prize_name'] = $reward['prize'][$prize_user['pid']]['prize_name'];
			$ret['prize'] = $reward['prize'][$prize_user['pid']]['prize'];
			$ret['dateline'] = date('Y-m-d H:i:s');

			return $ret;
		}
		return array();
	}

	
	function DoDelete($id){
		if ($id < 1) return false;

		$reward = $this->getRewardInfo($id);

		if (!$reward) return false;

		if(jdisallow($reward['uid'])){
			return false;
		}

		if($reward['prize']){
			foreach ($reward['prize'] as $key => $val) {
				if($val['prize_image_url'] && file_exists($val['prize_image_url'])){
					unlink($val['prize_image_url']);
				}
			}
		}

		#删除数据库里面的图片
		DB::query("delete from `".TABLE_PREFIX."reward_image` where `rid` = '$id' ");

		#删除有奖转发的参与者
		DB::query("delete from `".TABLE_PREFIX."reward_user` where `rid` = '$id' ");
		#删除有奖转发的获奖者
		DB::query("delete from `".TABLE_PREFIX."reward_win_user` where `rid` = '$id' ");

		#删除有奖转发
		DB::query("delete from `".TABLE_PREFIX."reward` where `id` = '$id' ");

		return true;
	}

	
	function getRewardUser($param){
		$where_arr =  array();
		if ($param['rid']) {
			if(is_array($param['rid'])){
				$where_arr['rid'] = " `rid` in('".implode("','",$param['rid'])."') ";
			}
			$where_arr['rid'] = " `rid` = '$param[rid]' ";
		}
		if (isset($param['on'])) {
			$where_arr['on'] = " `on` = '".$param['on']."' ";
		}

		$where_sql = $where_arr ? ' where '.implode(' and ',$where_arr) : '';

		$count = DB::result_first("select count(DISTINCT uid) as tot_count from `".TABLE_PREFIX."reward_user` $where_sql ");

		$page_arr = array();
		if($param['page']){
			$_config = array(
				'return' => 'array',
			);

			$page_arr = page($count,$param['per_page_num'],$param['url'],$_config);
			$limit = $page_arr['limit'];
		} else if ($param['limit']) {
			$limit = ' limit '.$param['limit'].' ';
		} else {
			return array();
		}

		$sql = "select u.uid,u.dateline,m.username,m.nickname,m.province,m.city
				 from `".TABLE_PREFIX."reward_user` u
				 left join `".TABLE_PREFIX."members` m on m.uid = u.uid
				 where u.id in (select id from ".TABLE_PREFIX."reward_user $where_sql group by uid)
				 order by u.dateline DESC
				 $limit ";
		$query = DB::query($sql);
		$user = array();
		while ($rs = DB::fetch($query)) {
			$rs['dateline'] = date('Y-m-d H:i:s',$rs['dateline']);
			$rs['face'] = face_get($rs['uid']);
			$user[$rs['uid']] = $rs;
		}

		$return = array(
			'count' => $count,
			'page_arr' => $page_arr,
			'user' => $user,
		);

		return $return;
	}

	
	function insert($data,$table){
		if (!$data) return 0;

		$data_key = array_keys($data);

		$sql = "insert into `$table` (`".implode('`,`',$data_key)."`) values ('".implode("','",$data)."')";

		DB::query($sql);

		return DB::insert_id();
	}

	
	function update($data,$table,$where){
		if (!$data) return 0;

		foreach ($data as $key=>$val) {
			$set_arr[] = " `$key` = '$val' ";
		}

		if(!$set_arr){ return 0 ;}

		$sql = "update `$table` set ".implode(',',$set_arr)." $where ";

		DB::query($sql);

		return 1;
	}
}
?>