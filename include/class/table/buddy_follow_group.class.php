<?php
/**
 *
 * 数据表 buddy_follow_group 相关操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: buddy_follow_group.class.php 3835 2013-06-08 07:15:10Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class table_buddy_follow_group extends table {

	
	var $table = 'buddy_follow_group';

	function table_buddy_follow_group() {
		$this->init($this->table);
	}

	
	function row($name, $uid) {
		$rets = array();
		$name = jfilter($name, 'trim');
		$uid = jfilter($uid, 'int');
		if($uid > 0 && $name) {
			$p = array(
				'uid' => $uid,
				'name' => $name,
			);
			$rets = $this->info($p);
		}
		return $rets;
	}

	
	function add($name, $uid = MEMBER_ID) {
		$uid = jfilter($uid, 'int');
		if($uid < 1) {
			return jerror('UID不能为空');
		}

		$name = jfilter($name, 'txt');
		if(($c_rets = $this->_check($name, $uid))) {
			return $c_rets;
		}

		$p = array(
			'uid' => $uid,
			'name' => $name,
			'dateline' => TIMESTAMP,
		);
		$id = $this->insert($p, true);

		$this->_rm_my_cache($uid);

		return $id;
	}

	
	function modify($p) {
		$ret = false;
		$id = jfilter($p['id'], 'int');
		if($id < 1) {
			return jerror('分组ID不能为空');
		}
		$info = $this->info($id);
		if(!$info) {
			return jerror('请指定一个正确的分组ID');
		}
		if(jdisallow($info['uid'])) {
			return jerror('您没有权限修改该分组信息');
		}

		$ps = array();
		if(isset($p['name']) && $p['name'] != $info['name']) {
			$p['name'] = jfilter($p['name'], 'txt');
			if(($c_rets = $this->_check($p['name'], $info['uid']))) {
				return $c_rets;
			}
			$ps['name'] = $p['name'];
		}
		if(isset($p['order']) && $p['order'] != $info['order']) {
			$ps['order'] = jfilter($p['order'], 'int');
		}
		if(isset($p['count']) && $p['count'] != $info['count']) {
			$ps['count'] = jfilter($p['count'], 'int');
		}
		if($ps && false != ($this->update($ps, $id))) {
			$ret = true;
		}
		$this->_rm_my_cache($info['uid']);
		return $ret;
	}

	
	function del($id) {
		$id = jfilter($id, 'int');
		if($id < 1) {
			return jerror('分组ID不能为空');
		}
		$info = $this->info($id);
		if(!$info) {
			return jerror('请指定一个正确的分组ID');
		}
		if(jdisallow($info['uid'])) {
			return jerror('您没有权限删除该分组信息');
		}
		jtable('buddy_follow_group_relation')->del_multi($uid, 0, $id);

		$this->_rm_my_cache($info['uid']);

		return $this->delete($id, 1);
	}

	
	function _check($name, $uid = 0) {
		if(empty($name)) {
			return jerror('分组名称不能为空');
		}
		if(strlen($name) > 100) {
			return jerror('分组名称的长度不能超过100个字符');
		}
		if(preg_match('~[\~\`\!\@\#\$\%\^\&\*\(\)\=\+\[\{\]\}\;\:\'\"\,\<\.\>\/\?]~', $name)) {
			return jerror('分组名称不能包含特殊字符');
		}
		$f_rets = filter($name);
		if($f_rets && $f_rets['error']) {
			return jerror($f_rets['msg']);
		}
		$uid = jget($uid, 'int');
		if($uid > 0 && ($this->row($name, $uid))) {
			return jerror('分组名称已经存在了');
		}
	}

	function _rm_my_cache($uid) {
		cache_db('mrm', $this->table . '-get_my_group-' . $uid);
	}

}

?>