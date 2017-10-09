<?php
/**
 *
 * 微博ITEM相关函数集
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: item.func.php 5462 2014-01-18 01:12:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}



function item_topic_from($topic) {
	$topic['item_id'] = (int) $topic['item_id'];
	if($topic['item_id'] > 0) {
		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			$r = nlogic('feeds.app.jsg')->topic_from($topic['item'], $topic['item_id']);
			if ($r && is_array($r))
			{
				return array_merge($topic, $r);
			}
		}
		#endif
		$func = "_item_topic_from_{$topic['item']}";
		if(function_exists($func)) {
			return $func($topic);
		}
	}

	return $topic;
}
function _item_topic_from_api($topic) {
	static $api_config=null;
	if(null===$api_config) {
		$api_config = jconf::get('api');
	}
	$topic['from_html'] = $topic['from_string'] = '来自 网站API';

	if($api_config['enable'] && $api_config['from_enable']) {
		$api_info = jtable('app')->row($topic['item_id']);

		if($api_info['show_from']) {
			$topic['from_html'] = $topic['from_string'] = "来自 {$api_info['app_name']}";
			$topic['item_name'] = $api_info['app_name'];
			if($api_info['source_url']) {
				$topic['from_html'] = "来自 <a target='_blank' href='{$api_info['source_url']}'>{$api_info['app_name']}</a>";
			}
		}
	}

	return $topic;
}
function _item_topic_from_vote($topic) {
	$vote_href = jurl('index.php?mod=vote&code=view&vid=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 投票";
		$vote_info = jtable('vote')->info($topic['item_id']);
	$subject = $vote_info['subject'];
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = ' - '.$subject;
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自 <a href="'.$vote_href.'" target="_blank">投票'.$sub_from.'</a>';
	}

	return $topic;
}

function _item_topic_from_reward($topic) {
	$vote_href = jurl('index.php?mod=reward&code=detail&id=' . $topic['item_id']);

	$topic['from_html'] = $topic['from_string'] = "来自 有奖转发";
    $sql = "select title from `".TABLE_PREFIX."reward` where `id` = '$topic[item_id]'";
	$rewardName = DB::fetch_first($sql);
	if($rewardName["title"]) {
		$topic['item_name'] = $rewardName["title"];
        $rewardName["title"] = "-".$rewardName["title"];
		$topic['from_html'] = '来自 <a href="'.$vote_href.'" target="_blank">有奖转发'.$rewardName["title"].'</a>';
	}

	return $topic;
}

function _item_topic_from_qun($topic) {
	$qun_href = jurl('index.php?mod=qun&qid=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 ".$GLOBALS['_J']['config'][changeword][weiqun];
		$qun_info = jtable('qun')->info($topic['item_id']);
	$sub_from = '';
	if (!empty($qun_info)) {
		$sub_from = ' - '.$qun_info['name'];
		$topic['item_name'] = $qun_info['name'];
	}
	if($sub_from) {
		$topic['from_html'] = '来自 <a href="'.$qun_href.'" target="_blank">'.$GLOBALS['_J']['config'][changeword][weiqun].$sub_from.'</a>';
	}

	return $topic;
}
function _item_topic_from_fenlei($topic) {
		$topic['from_html'] = $topic['from_string'] = "来自 分类信息";

		$fenlei_info = jtable('fenlei_content')->info($topic['item_id']);
	if($fenlei_info){
		$fenlei_href = jurl('index.php?mod=fenlei&code=detail&fid=' . $fenlei_info['fid'] . '&id=' . $topic['item_id']);

		$sub_from = '';
		if (!empty($fenlei_info)) {
			$sub_from = ' - '.$fenlei_info['title'];
			$topic['item_name'] = $fenlei_info['title'];
		}
		$topic['from_html'] = '来自 <a href="'.$fenlei_href.'" target="_blank">分类信息'.$sub_from.'</a>';
	}

	return $topic;
}
function _item_topic_from_event($topic) {
	$event_href = jurl('index.php?mod=event&code=detail&id=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 活动";

		$event_info = jtable('event')->info($topic['item_id']);
	$main_from = $sub_from = '';
	if (!empty($event_info)) {
												$sub_from = ' - '.$event_info['title'];
		$topic['item_name'] = $event_info['title'];
	}
	if($sub_from) {
		$topic['from_html'] = '来自 '.$main_from.'<a href="'.$event_href.'" target="_blank" title="'.$event_info[title].'">活动'.$sub_from.'</a>';
	}

	return $topic;
}
function _item_topic_from_url($topic) {
	$topic['from_html'] = $topic['from_string'] = "来自 内容评论";

	$url_info = jlogic('url')->get_info_by_id($topic['item_id']);
	$sub_from = '';
	if($url_info) {
		$sub_from = $url_info['title'];
		$topic['item_name'] = $sub_from;
	}
	if($sub_from) {
		$topic['from_html'] = '来自 <a href="'.$url_info['url'].'" target="_blank" title="'.$url_info['title'].'">'.$sub_from.'</a>';
	}

	return $topic;
}
function _item_topic_from_live($topic) {
	$live_href = jurl('index.php?mod=live&code=view&id=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 微直播";
		$live_info = jtable('live')->info($topic['item_id']);
	$subject = $live_info['livename'];
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject;
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$live_href.'" target="_blank">'.$sub_from.'</a>&nbsp;&nbsp;微直播';
	}

	return $topic;
}
function _item_topic_from_talk($topic) {
	$talk_href = jurl('index.php?mod=talk&code=view&id=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 微访谈";
		$talk_info = jtable('talk')->info($topic['item_id']);
	$subject = $talk_info['talkname'];
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject;
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$talk_href.'" target="_blank">'.$sub_from.'</a>&nbsp;&nbsp;微访谈';
	}

	return $topic;
}
function _item_topic_from_channel($topic) {
	$channel_href = jurl('index.php?mod=channel&id=' . $topic['item_id']);

		$ch_info = jtable('channel')->info($topic['item_id']);
	if($ch_info['parent_id'] > 0){
		$ch_p_info = jtable('channel')->info($ch_info['parent_id']);
	}
	$ch_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($topic['item_id']);
	$subject = $ch_info['ch_name'];
	$sub_from = $psub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject;
		$topic['channel_type'] = $ch_typeinfo['channel_type'] ? $ch_typeinfo['channel_type'] : 'default';
		if($ch_typeinfo['manageid']){
			$topic['ismanager'] = in_array(MEMBER_ID,explode(',',$ch_typeinfo['manageid']));
		}
		if(!$topic['ismanager'] && $ch_typeinfo['parent_id'] > 0){
			$p_ch_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($ch_typeinfo['parent_id']);
			if($p_ch_typeinfo['manageid']){
				$topic['ismanager'] = in_array(MEMBER_ID,explode(',',$p_ch_typeinfo['manageid']));
			}
		}
	}
	if(!empty($ch_p_info['ch_name'])){
		$psub_from = $ch_p_info['ch_name'];
		$channelp_href = jurl('index.php?mod=channel&id=' . $ch_info['parent_id']);
	}
	$topic['from_string'] = "来自 频道";
	if($sub_from) {
		$topic['item_name'] = $psub_from ? $psub_from : $sub_from;
		$pfhtml = $psub_from ? '<a href="'.$channelp_href.'" target="_blank">'.$psub_from.'</a>&nbsp;>>&nbsp;' : '';
		$topic['from_html'] = '来自&nbsp;&nbsp;'.$pfhtml.'<a href="'.$channel_href.'" target="_blank">'.$sub_from.'</a>';
	}else{
		$topic['from_html'] = $topic['from_string'];
	}

	
	$features = jlogic('feature')->get_feature();
	$default_status = ($topic['featureid'] > 0 && $features[$topic['featureid']]) ? $features[$topic['featureid']] : '';
	$topic['topic_feature_status'] = $ch_typeinfo['feature'][$topic['featureid']] ? $ch_typeinfo['feature'][$topic['featureid']] : $default_status;

	return $topic;
}
function _item_topic_from_cms($topic) {
	$cms_href = jurl('index.php?mod=cms&code=article&id=' . $topic['item_id']);

		$topic['from_html'] = $topic['from_string'] = "来自 CMS文章";
	$cms_info = jtable('cms_article')->info($topic['item_id']);
	$subject = cut_str($cms_info['title'],20);
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject;
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$cms_href.'" target="_blank">'.$sub_from.'</a>&nbsp;&nbsp;CMS文章';
	}

	return $topic;
}
function _item_topic_from_topic_image($topic) {
	$img_href = jurl('index.php?mod=album&code=viewimg&pid=' . $topic['item_id']);
	$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$img_href.'" target="_blank">微博相册</a>';
	return $topic;
}
function _item_topic_from_company($topic) {
	$topic['from_string'] = "来自 单位微博";
	$iscompany = @is_file(ROOT_PATH . 'include/logic/cp.logic.php') && jlogic('cp')->is_exists($topic['item_id']);
	if($iscompany){
		$companyname = jlogic('cp')->Getone($topic['item_id']);
		$topic['item_name'] = $companyname;
		$company_href = jurl('index.php?mod=company&code=privately&id='.$topic['item_id']);
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="index.php?mod=company" target="_blank">单位微博</a>&nbsp;>>&nbsp;<a href="'.$company_href.'" target="_blank">'.$companyname.'</a>';
	}else{
		$topic['from_html'] = $topic['from_string'];
	}
	return $topic;
}

function _item_topic_from_contest($topic){
    $href = jurl('index.php?mod=contest&contest=' . $topic['item_id']);
	$topic['from_html'] = $topic['from_string'] = "来自 摄影大赛";
	$info = jtable('contest')->info($topic['item_id']);
	$subject = cut_str($info['name'],20);
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject.'';
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$href.'" target="_blank">'.$sub_from.'</a>&nbsp;&nbsp;';
	}

	return $topic;
}

function _item_topic_from_mall($topic) {
	$mall_href = jurl('index.php?mod=mall&code=goodsinfo&id=' . $topic['item_id']);
	$mall_d_href = jurl('index.php?mod=mall');
		$topic['from_html'] = $topic['from_string'] = "来自 积分商城";
	$mall_info = jtable('mall_goods')->info($topic['item_id']);
	$subject = cut_str($mall_info['name'],20);
	$sub_from = '';
	if (!empty($subject)) {
		$sub_from = $subject;
		$topic['item_name'] = $subject;
	}
	if($sub_from) {
		$topic['from_html'] = '来自&nbsp;&nbsp;<a href="'.$mall_d_href.'">积分商城</a>&nbsp;>>&nbsp;<a href="'.$mall_href.'" target="_blank">'.$sub_from.'</a>';
	}

	return $topic;
}
?>