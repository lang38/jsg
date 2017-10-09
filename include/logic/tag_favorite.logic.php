<?php
/**
 *
 * 话题收藏操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag_favorite.logic.php 4049 2013-07-30 00:41:33Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class TagFavoriteLogic {

	var $db;

	function TagFavoriteLogic() {
		$this->db = jtable('tag_favorite');
	}

	function info($tag, $uid=MEMBER_ID) {
		$tag = jfilter($tag, 'txt');
		$uid = jfilter($uid, 'int');

		$rets = array();
		if($uid > 0 && $tag) {
			$rets = $this->db->info(array('uid'=>$uid, 'tag'=>$tag));
		}
		return $rets;
	}

		function madd($tagids = array(), $uid = MEMBER_ID) {
		$uid = jfilter($uid, 'int');
		if($uid < 1) {
			return jerror('请先登录或者注册一个帐号', -2);
		}

		$ids = array();
		settype($tagids, 'array');
		foreach($tagids as $k=>$v) {
			$v = (int) $v;
			if($v > 0) {
				$ids[$v] = $v;
			}
		}
		if($ids) {
						$tag_list = jtable('tag')->get($ids);
			if($tag_list) {
				foreach($tag_list as $row) {
					$this->add($row['name'], $uid);
				}
				return array();
			}
		}
		return jerror('关注话题失败,请选择关注对象', -1);
	}

		function add($tag, $uid=MEMBER_ID) {
		$tag = jfilter($tag, 'txt');
		$uid = jfilter($uid, 'int');

		$info = $this->info($tag, $uid);
		if($info) {
						return $info;
		}
		if($uid < 1) {
			return jerror('请指定一个正确的UID');
		}
		$uinfo = jsg_member_info($uid);
		if(!$uinfo) {
			return jerror('指定的UID已经不存在了');
		}
		if(!$tag) {
			return jerror('请指定一个正确的话题');
		}
		$tinfo = jtable('tag')->info(array('name'=>$tag));
		if(!$tinfo) {
			return jerror('指定的话题已经不存在了');
		}

		$data = array(
			'tag' => $tag,
			'uid' => $uid,
			'dateline' => TIMESTAMP,
		);
		$id = $this->db->insert($data, 1, 1, 1);
		if($id > 0) {
			$this->_rm_my_cache($uid);
			jtable('members')->update_count($uid, 'tag_favorite_count', '+1');
			jtable('tag')->update_count(array('name'=>$tag), 'tag_count', '+1');

			$info = $this->info($tag, $uid);
		}
		return $info;
	}

	function del($tag, $uid=MEMBER_ID) {
		$tag = jfilter($tag, 'txt');
		$uid = jfilter($uid, 'int');

		$info = $this->info($tag, $uid);
		if($info) {
			$this->db->delete(array('uid'=>$uid, 'tag'=>$tag));
			$this->_rm_my_cache($uid);

			jtable('members')->update_count($uid, 'tag_favorite_count', '-1');
			jtable('tag')->update_count(array('name'=>$tag), 'tag_count', '-1');
		}
	}

	function my_favorite($uid=MEMBER_ID, $limit=12) {
		if(is_numeric($uid)) {
			$uid = max(0, (int) $uid);
		} else {
			settype($uid, 'array');
		}
		$limit = max(0, jfilter($limit, 'int'));

		$rets = array();
		if($uid && false === ($rets = cache_db('get', ($cache_id = 'my_tag_favorite/' . $uid . '-' . $limit)))) {
			$p = array(
				'uid'=>$uid,
				'sql_order'=>' `id` DESC ',
				'return_list'=>1,
			);
			if($limit > 0) {
				$p['result_count'] = $limit;
			}
			$rets = $this->db->get($p);
			cache_db('set', $cache_id, $rets, 3600);
		}
		return $rets;
	}

	function my_favorite_tags($uid = MEMBER_ID, $limit = 12) {
		$tags = array();
		$rets = $this->my_favorite($uid, $limit);
		if ($rets) {
			foreach($rets as $row) {
				$tags[$row['tag']] = $row['tag'];
			}
		}
		return $tags;
	}

	function my_favorite_tag_ids($uid = MEMBER_ID, $limit = 0) {
		$rets = array();
		if(false === ($rets = cache_db('get', ($cache_id = 'my_favorite_tag_ids/' . $uid . '-' . $limit)))) {
			$tags = $this->my_favorite_tags($uid, $limit);
			$rets = array();
			if($tags && ($tags_count = count($tags)) > 0) {
				$rets = jtable('tag')->get_ids(array('name'=>$tags, 'result_count'=>$tags_count, 'sql_order'=>' `id` DESC '), 'id');
			}
			cache_db('set', $cache_id, $rets, 3600);
		}
		return $rets;
	}

		function favorite_uids($tags, $limit = 30) {
		$rets = array();
		if($tags) {
			settype($tags, 'array');
			$limit = jfilter($limit, 'int');
			$p = array(
				'tag' => $tags,
				'sql_order' => ' `id` DESC ',
			);
			if($limit > 0) {
				$p['result_count'] = $limit;
			}
			$rets = $this->db->get_ids($p, 'uid');
		}
		return $rets;
	}

	function favorite_users($tags, $limit = 12, $fields = '*') {
		$rets = array();
		$limit = jfilter($limit, 'int');
		if($tags && $limit > 0 && ($uids = $this->favorite_uids($tags, $limit))) {
			$rets = jlogic('topic')->GetMember($uids, $fields);
		}
		return $rets;
	}

		function topic_new($tags, $current_uid = MEMBER_ID) {
		$ret = false;
		settype($tags, 'array');
		foreach($tags as $k=>$v) {
			$v = jfilter($v, 'txt');
			if(empty($v) || !(jtable('tag')->info($v))) {
				unset($tags[$k]);
			} else {
				$tags[$k] = $v;
			}
		}
		if($tags) {
			$current_uid = jfilter($current_uid, 'int');
						$ret = DB::query("UPDATE ".DB::table('tag_favorite')." TF, ".DB::table('members')." M
			SET M.topic_new=M.topic_new+1
			WHERE TF.tag IN (".jimplode($tags).") AND M.uid=TF.uid AND M.uid!='$current_uid' AND M.lastactivity>'".(TIMESTAMP - 2592000)."'");
		}
		return $ret;
	}

	function _rm_my_cache($uid) {
		cache_db('rm', 'my_tag_favorite/' . $uid . '-%', 1);
		cache_db('rm', 'my_favorite_tag_ids/' . $uid . '-%', 1);
	}

}

?>