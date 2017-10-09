<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename app.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-20 962028818 8872 $
 */




if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}


function app_get_list()
{
	$app_list = array(
		'vote',
		'qun',
		'fenlei',
		'event',
		'live',
		'talk',
		'reward',
		'channel',
		'company',
		'mall',
	);
	#if NEDU
	defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->merge_list($app_list);
	#endif
	return $app_list;
}


function app_check($item, $item_id = null)
{
	$app_list = app_get_list();
	if (in_array($item, $app_list)) {
		if (is_null($item_id)) {
			return true;
		}
	} else {
		return false;
	}
	#if NEDU
	if (defined('NEDU_MOYO'))
	{
		$r = nlogic('feeds.app.jsg')->check_item($item, $item_id);
		if (is_array($r) && $r['checked'] === true)
		{
			return (bool)$r['result'];
		}
	}
	#endif

		$item = $item=='company' ? 'cp' : $item;	if (!Load::logic($item)) {
		return false;
	}

	$class_name = ucwords($item).'Logic';
	if (method_exists($class_name, 'is_exists')) {
		$ret = call_user_func(array($class_name, 'is_exists'), $item_id);

		if (empty($ret)) {
			return false;
		}
		return true;
	}

		return false;
}


function app_get_topic_list($item, $item_id, $options = null)
{
	$no_where = false;

	$where_sql = " 1 ";

		if ($item_id) {
		$tids = app_itemid2tid($item, $item_id, $options);

		
		if ($options['reply'] == true) {
			$where_sql = " roottid IN(".jimplode($tids).") ";
			if (!empty($options['where'])) {
				$where_sql .= " AND {$options['where']} ";
			}

			$query = DB::query("SELECT DISTINCT roottid FROM ".DB::table('topic')." WHERE {$where_sql}");
			$tids = array();
			while ($value = DB::fetch($query)) {
				$tids[] = $value['roottid'];
			}
			$where_sql = " 1 AND tid IN(".jimplode($tids).") ";
			$no_where = true;
			unset($query);
			unset($tids);
		} else {
			$where_sql .= " AND tid IN(".jimplode($tids).") ";
		}
	}

	if (!$no_where) {
		if (!empty($options['where'])) {
			$where_sql .= " AND {$options['where']} ";
		}
	}

	$order_sql = ' dateline DESC ';
	if (!empty($options['order'])) {
		$order_sql = $options['order'];
	}

	$limit_sql = '';
	if (!empty($options['limit'])) {
		$limit_sql = " LIMIT {$options['limit']} ";
	}

	$field = ' * ';
	if (!empty($options['$field'])) {
		$field = $options['$field'];
	}

	if($item == 'talk'){
		$count = count($tids);
	}else{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic')." WHERE {$where_sql}");
	}

	if ($count) {
		$list = array();
		if ($options['page']) {
			$page_arr = page($count, $options['perpage'], $options['page_url'], array('return'=>'array',));
			$limit_sql = $page_arr['limit'];
		}

		$condition = " WHERE {$where_sql} ORDER BY {$order_sql} {$limit_sql} ";
		$list = jlogic('topic')->Get($condition);
		if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
			$list = jlogic('buddy')->follow_html2($list);
		}
		return array('count' => $count, 'list' => $list, 'page' => $page_arr);
	}
	return false;
}


function app_add_relation($param)
{
	$item = $param['item'];
	#if NEDU
	if (defined('NEDU_MOYO'))
	{
		$r = nlogic('feeds.app.jsg')->make_relation($item, $param);
		if ($r)
		{
			return;
		}
	}
	#endif
	$table_name = app_table($item);
	if(!$table_name) {
		return ;
	}
	$data = array(
		'item_id' => $param['item_id'],
		'tid' => $param['tid'],
		'uid' => $param['uid'],
	);
	if($item == 'talk'){
		$data['totid'] = $param['totid'];
		$data['touid'] = $param['touid'];
	}
	DB::insert($table_name, $data);

		if ($item == 'qun') {
		DB::query("UPDATE ".DB::table('qun')." SET thread_num=thread_num+1,lastactivity = '".time()."' WHERE qid='{$param['item_id']}'");
	}
		if($item == 'talk'){
		if(!$data['totid'] && !$data['touid']){
			DB::query("UPDATE ".DB::table($table_name)." SET istop=1 WHERE tid='{$param['tid']}'");
		}
		if($data['totid'] > 0 && $data['touid'] > 0){
			DB::query("UPDATE ".DB::table($table_name)." SET istop=1 WHERE tid='{$param['totid']}'");
			DB::query("UPDATE ".DB::table('topic')." SET lastupdate=".time()." WHERE tid='{$param['totid']}'");
		}
	}
		if ($item == 'channel') {
		DB::query("UPDATE ".DB::table('channel')." SET topic_num=topic_num+1,total_topic_num=total_topic_num+1 WHERE ch_id='{$param['item_id']}'");
		$p_id = DB::result_first("SELECT parent_id FROM ".DB::table('channel')." WHERE ch_id='{$param['item_id']}'");
		if($p_id > 0){
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+1 WHERE ch_id='{$p_id}'");
		}
	}
}


function app_mod_relation($param)
{
	$old_item_id = $param['old_itemid'];
	if($old_item_id){
		DB::query("UPDATE ".DB::table('topic_channel')." SET item_id='{$param['itemid']}' WHERE tid='{$param['tid']}'");
		DB::query("UPDATE ".DB::table('channel')." SET topic_num=topic_num+1,total_topic_num=total_topic_num+1 WHERE ch_id='{$param['itemid']}'");
		$p_id = DB::result_first("SELECT parent_id FROM ".DB::table('channel')." WHERE ch_id='{$param['itemid']}'");
		if($p_id > 0){
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+1 WHERE ch_id='{$p_id}'");
		}
		DB::query("UPDATE ".DB::table('channel')." SET topic_num=topic_num-1,total_topic_num=total_topic_num-1 WHERE ch_id='{$old_item_id}'");
		$p_id = DB::result_first("SELECT parent_id FROM ".DB::table('channel')." WHERE ch_id='{$old_item_id}'");
		if($p_id > 0){
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num-1 WHERE ch_id='{$p_id}'");
		}
	}else{
		$table_name = app_table('channel');
		$data = array(
			'item_id' => $param['itemid'],
			'tid' => $param['tid'],
			'uid' => $param['uid'],
		);
		DB::insert($table_name, $data);
		DB::query("UPDATE ".DB::table('channel')." SET topic_num=topic_num+1,total_topic_num=total_topic_num+1 WHERE ch_id='{$param['itemid']}'");
		$p_id = DB::result_first("SELECT parent_id FROM ".DB::table('channel')." WHERE ch_id='{$param['itemid']}'");
		if($p_id > 0){
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+1 WHERE ch_id='{$p_id}'");
		}
	}
}


function app_delete_relation($item, $item_id, $tid)
{
	$delete_sql = " WHERE item_id='{$item_id}' AND tid='{$tid}' ";
	if(($table_name = app_table($item))) {
		DB::query("DELETE FROM ".DB::table($table_name)." {$delete_sql}");
	}

		if ($item == 'qun') {
		DB::query("UPDATE ".DB::table('qun')." SET thread_num=thread_num-1 WHERE qid='{$item_id}'");
	}
		if ($item == 'channel') {
		DB::query("UPDATE ".DB::table('channel')." SET topic_num=topic_num-1,total_topic_num=total_topic_num-1 WHERE ch_id='{$item_id}'");
		$p_id = DB::result_first("SELECT parent_id FROM ".DB::table('channel')." WHERE ch_id='{$item_id}'");
		if($p_id > 0){
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num-1 WHERE ch_id='{$p_id}'");
		}
	}
}


function app_table($item) {
	$table_name = 'topic_'.$item;
	if($table_name && false != jtable('app')->is_table($table_name)) {
		return $table_name;
	} else {
		return false;
	}
}


function app_itemid2tid($item, $item_id, $options = null)
{
	$table_name = app_table($item);
	if(!$table_name) {
		return array();
	}
	if($item == 'talk' && $options['talkwhere']){
		$where_sql = $options['talkwhere'];
	}else{
		$where_sql = ' 1 ';
	}
	if (is_array($item_id)) {
		$where_sql .= " AND item_id IN (".jimplode($item_id).") ";
	} else {
		$where_sql .= " AND item_id='{$item_id}' ";
	}

	$query = DB::query("SELECT tid FROM ".DB::table($table_name)." WHERE {$where_sql} ");
	$tid_ary = array();
	while ($value = DB::fetch($query)) {
		$tid_ary[] = $value['tid'];
	}
	return $tid_ary;
}


function app_getmyanswerid($item_id)
{
	$query = DB::query("SELECT tid FROM ".DB::table('topic_talk')." WHERE item_id = '$item_id' AND istop = 0 AND touid = '" . MEMBER_ID ."'");
	$totid_ary = array();
	while ($value = DB::fetch($query)) {
		$totid_ary[] = $value['tid'];
	}
	return $totid_ary;
}

?>