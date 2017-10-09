<?php
/**
 *
 * 长文逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: longtext.logic.php 4933 2013-11-10 08:10:34Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 *
 * 长文本的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class LongtextLogic
{
	var $table = 'topic_longtext';

	function LongtextLogic() {
		;
	}

	function info($id) {
		$ret = false;
		$id = is_numeric($id) ? $id : 0;
		if($id > 0) {
			$ret = jtable($this->table)->info($id);
		}
		return $ret;
	}

	function rm($id) {
		$ret = false;
		$id = is_numeric($id) ? $id : 0;
		if($id > 0 && ($info = $this->info($id))) {
			$ret = jtable($this->table)->delete($id) ? true : false;
		}
		return $ret;
	}

	function val($id) {
		return $this->longtext($id);
	}
	function longtext($id, $tid = 0) {
		$ret = false;
		$id = is_numeric($id) ? $id : 0;
		$tid = is_numeric($tid) ? $tid : 0;
		if($id > 0) {
			$ret = jtable($this->table)->val($id, 'longtext');
			if(empty($ret) && $tid > 0) {
				$ret = jtable('topic_more')->get_val($tid, 'longtext');
			}
		}
		return $ret;
	}

	function get_info($id, $option=array()) {
		$id = is_numeric($id) ? $id : 0;
		if($id < 1 || !($info = jtable('topic')->info($id))) {
			return false;
		}
        if($info['longtextid'] > 0) {
        	$info['content'] = jtable('topic_more')->get_longtext($id);
        } else {
        	$info['content'] .= $info['content2'];
        }
        unset($info['content2']);
        $info = jlogic('topic')->Make($info, 0, $option, 1,1);
				$info['content'] = str_replace(array('  '), ' &nbsp; &nbsp; ', $info['content']);
		return $info;
	}

	function add($longtext, $uid=0)
	{
		$longtext = $this->_longtext($longtext);
		if(!$longtext) {
			return 0;
		}
		$uid = (is_numeric($uid) ? $uid : 0);

		$arr = array(
			'longtext' => $longtext,
			'uid' => ($uid > 0 ? $uid : MEMBER_ID),
			'dateline' => time(),
			'tid' => 0,
			'views' => 0,
		);
		$ret = jtable($this->table)->insert($arr, 1);

		return $ret;
	}

	function modify($tid, $longtext)
	{
		$tid = is_numeric($tid) ? $tid : 0;
		if($tid < 1) return 0;

		$longtext = $this->_longtext($longtext);

		jtable('topic_more')->set_longtext($tid, $longtext);

		return TIMESTAMP;
	}

	function set_tid($id, $tid)
	{
		$id = is_numeric($id) ? $id : 0;
		if($id < 1) return 0;

		$tid = is_numeric($tid) ? $tid : 0;

		return DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id`='$id'");
	}

	function _longtext($longtext)
	{
		$longtext = trim($longtext);

		$search = array(
			'~[\t]+~',
			'~([\r\n]){3,}~',
		);
		$replace = array(
			' ',
			'\\1\\1',
		);
		$longtext = preg_replace($search, $replace, $longtext);


		return $longtext;
	}

	function clear_invalid($time = 8640000) {
		$time = TIMESTAMP - max(0, (int) $time);
		return DB::query("DELETE FROM ".DB::table($this->table)." WHERE `tid`=0 AND `dateline`<'$time'");
	}

}

?>