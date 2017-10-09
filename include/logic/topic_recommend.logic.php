<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_recommend.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1250488667 2817 $
 */




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class TopicRecommendLogic
{
	
	function TopicRecommendLogic()
	{
	}

	function add($data)
	{
		DB::insert('topic_recommend', $data, false, true);
		DB::query("UPDATE ".DB::table('topic')." SET recommend = 1 WHERE tid = '".$data['tid']."'");
		if($data['recd']==4){
			DB::query("UPDATE ".DB::table('members')." SET close_recd_time = '".($data['dateline']-5)."' WHERE uid = '".MEMBER_ID."'");
		}
	}

	function modify($data, $c)
	{
		DB::update('topic_recommend', $data, $c);
		if($data['recd']==4){
			DB::query("UPDATE ".DB::table('members')." SET close_recd_time = '".($data['dateline']-5)."' WHERE uid = '".MEMBER_ID."'");
		}
	}

	function get_info($c)
	{
		$topic_recd = DB::fetch_first("SELECT * FROM ".DB::table('topic_recommend')." WHERE tid='{$c}'");
		return $topic_recd;
	}

	function is_exists($c)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_recommend')." WHERE tid='{$c}'");
		return $count > 0 ? true : false;
	}

	function delete($c)
	{
		DB::query("DELETE FROM ".DB::table('topic_recommend')." WHERE tid IN(".jimplode($c).")");
		DB::query("UPDATE ".DB::table('topic')." SET recommend = 0 WHERE tid IN(".jimplode($c).")");
	}

	function recd_levels($type = 'all')
	{
		$recd_levels = array(
			1 =>  array('name' => '取消设置', 'level' => 0),
			2 => array('name'=> '全站置顶', 'level' => 4),
			3 => array('name'=> '普通推荐', 'level' => 3),
			4 => array('name'=> '话题内置顶', 'level' => 2),
			5 => array('name'=> '话题内推荐', 'level' => 1),
			6 => array('name'=> '群内置顶', 'level' => 2),
			7 => array('name'=> '群内推荐', 'level' => 1),
		);

		if ($type == 'tag') {
			unset($recd_levels[6], $recd_levels[7]);
		} else if ($type == 'admin_qun') {
			unset($recd_levels[4], $recd_levels[5]);
		} else if ($type == 'qun') {
			return array($recd_levels[1],$recd_levels[7]);
		} else if ($type == 'topic' || $type == 'channel') {
			return array($recd_levels[1],$recd_levels[2],$recd_levels[3]);
		}
		return $recd_levels;
	}

	function recd_channels()
	{
		$channels = array();
		$cachefile = jconf::get('channel');
		$channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
		$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
		foreach($channel_one as $key => $val){
			$channels[$key] = $val;
			foreach($channel_two as $k => $v){
				if($v['parent_id'] == $key){
					$v['ch_name'] = '&nbsp;&nbsp;'.$v['ch_name'];
					$channels[$k] = $v;
				}
			}
		}
		return $channels;
	}

}

?>