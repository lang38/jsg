<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename channel.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1232389701 34189 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ChannelLogic
{

	function ChannelLogic()
	{

	}

	
	function is_exists($ch_id = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		return $count > 0 ? true : false;
	}
	
	function have_child($ch_id = 0)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('channel')." WHERE parent_id='{$ch_id}'");
		return $count > 0 ? true : false;
	}

		function is_update_credits_byid($ch_id=0,$isadd=1){
		if(!$this->is_exists($ch_id)){
			return 0;
		}
		if($this->is_credits_chid($ch_id,$isadd)){
			return $ch_id;
		}else{
			$channels = $this->id2category($ch_id);
			if($channels['parent_id'] > 0 && $this->is_credits_chid($channels['parent_id'],$isadd)){
				return $channels['parent_id'];
			}else{
				return 0;
			}
		}

	}
	function is_credits_chid($ch_id=0,$isadd=1){
		if($isadd){
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('credits_rule')." WHERE related='{$ch_id}' AND `action`='_C".crc32($ch_id)."'");
		}else{
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('credits_rule')." WHERE related='{$ch_id}' AND `action`='_D".crc32($ch_id)."'");
		}
		return $count > 0 ? true : false;
	}

		function get_my_cannot_view_chids(){
		$ch_ids = array();
		global $_J;
		$query = DB::query("SELECT ch_id,purpostview FROM ".DB::table('channel')." where purpostview !=''");
		while ($value = DB::fetch($query))
		{
			if(!in_array($_J['member']['role_id'],explode(',',$value['purpostview']))){
				$ch_ids[] = $value['ch_id'];
			}
		}
		return $ch_ids;
	}

		function can_view_topic($ch_id=0){
		$return = false;
		$is_channel = $this->is_exists($ch_id);
		if($is_channel){
			global $_J;
			$purpostview = DB::result_first("SELECT purpostview FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
			if(empty($purpostview) || in_array($_J['member']['role_id'],explode(',',$purpostview))){
				$return = true;
			}
		}
		return $return;
	}

	
	function can_pub_topic($ch_id=0)
	{
		$return = false;
		$is_channel = $this->is_exists($ch_id);
		if($is_channel){
			global $_J;
			$purview = DB::result_first("SELECT purview FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
			if(empty($purview) || in_array($_J['member']['role_id'],explode(',',$purview))){
				$return = true;
			}
		}
		return $return;
	}

	
	function verify_pub_topic($ch_id)
	{
		$return = false;
		$is_channel = $this->is_exists($ch_id);
		if($is_channel){
			global $_J;
			$val = DB::fetch_first("SELECT `verify`,`filter` FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
			if($val['verify'] && !in_array($_J['member']['role_id'],explode(',',$val['filter']))){
				$return = true;
			}
		}
		return $return;
	}

	
	function id2subject($ch_id)
	{
		static $channelname;
		if($channelname[$ch_id]){
			$subject = $channelname[$ch_id];
		}else{
			$subject = DB::result_first("SELECT ch_name FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}' ");
			$channelname[$ch_id] = $subject;
		}
		return $subject;
	}

	
	function mychannel($uid = MEMBER_ID)
	{
		$channels = array();
		$uid = $uid ? $uid : MEMBER_ID;
		$query = DB::query("SELECT bc.ch_id,c.ch_name,c.purview,c.topic_num,c.total_topic_num,c.buddy_numbers FROM ".DB::table('buddy_channel')." bc LEFT JOIN ".DB::table('channel')." c ON bc.ch_id=c.ch_id where bc.uid='$uid'");
		while ($value = DB::fetch($query))
		{
			$value['topicnum'] = $value['total_topic_num'] > $value['topicnum'] ? $value['total_topic_num'] : $value['topicnum'];
			$channels[$value['ch_id']] = $value;
		}
		return $channels;
	}

	
	function category_exists($ch_name, $pid = 0)
	{
		$count = DB::result_first("SELECT COUNT(*)
								   FROM ".DB::table('channel')."
								   WHERE ch_name='{$ch_name}' AND parent_id='{$pid}'");
		return $count > 0 ? true : false;
	}

	
	function id2category($ch_id)
	{
		$channel = array();
		$channel = DB::fetch_first("SELECT * FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		return $channel;
	}

	
	function &get_category_tree($dotree=0)
	{
		$tree = $cat_ary = $indextree = array();
		$channeltype = $this->get_channel_type();
		$query = DB::query("SELECT * FROM ".DB::table('channel')." ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$value['channel_type'] = $channeltype[$value['channel_typeid']];
			$cat_ary[] = $value;
			$indextree[$value['ch_id']]['id'] = $value['ch_id'];
			$indextree[$value['ch_id']]['name'] = $value['ch_name'];
			$indextree[$value['ch_id']]['picture'] = $value['picture'] ? str_replace('./','/',$value['picture']) : "/images/channelimg.gif";
		}

		if (!empty($cat_ary)) {
			if($dotree){
				$tree = $indextree;
			}else{
				$tree = $this->category_tree($cat_ary);
			}
		}
		return $tree;
	}

	
	function update_data()
	{
		$query = DB::query("SELECT * FROM ".DB::table('channel')." ORDER BY parent_id DESC");
		while ($value = DB::fetch($query)) {
			$totala = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic')." WHERE item = 'channel' AND item_id = '{$value['ch_id']}'");
			DB::query("UPDATE ".DB::table('channel')." SET total_topic_num={$totala},topic_num={$totala} WHERE ch_id='{$value['ch_id']}'");
			if($value['parent_id'] == 0){
				$total = DB::result_first("SELECT SUM(topic_num) FROM ".DB::table('channel')." WHERE parent_id = '{$value['ch_id']}'");
				if($total > 0){
					DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+{$total} WHERE ch_id='{$value['ch_id']}'");
				}
			}
		}
		$this->update_category_cache();
	}

	
	function move_channel($from_id=0,$to_id=0,$cutc=0)
	{
		if($from_id > 0 && $to_id > 0 && $from_id != $to_id){
			$from_info = $this->id2category($from_id);
			if($from_info['topic_num']>0){
				$to_info = $this->id2category($to_id);
				if($from_info['purpostview'] != $to_info['purpostview']){					if($from_info['purpostview'] == ''){
						$addupdatesql = ",`type`='channel'";
					}elseif($to_info['purpostview'] == ''){
						$addupdatesql = ",`type`='first'";
					}else{
						$addupdatesql = "";
					}
				}
				if($from_info['topictype'] != $to_info['topictype']){					$addupdatesql .= ",`managetype`='".$to_info['topictype']."'";
				}
				DB::query("UPDATE ".DB::table('topic')." SET `item_id`='{$to_id}'".$addupdatesql." WHERE item = 'channel' AND item_id='{$from_id}'");
				DB::query("UPDATE ".DB::table('buddy_channel')." SET ch_id='{$to_id}' WHERE ch_id='{$from_id}'");
								DB::query("DELETE a FROM ".DB::table('buddy_channel')." AS a,(SELECT * FROM ".DB::table('buddy_channel')." GROUP BY uid,ch_id HAVING COUNT(*)>1) AS b WHERE a.uid=b.uid AND a.ch_id=b.ch_id	AND a.id>b.id");
								DB::query("UPDATE ".DB::table('topic_recommend')." SET item_id='{$to_id}' WHERE item = 'channel' AND item_id='{$from_id}'");
				DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+{$from_info['total_topic_num']},topic_num=topic_num+{$from_info['topic_num']} WHERE ch_id = '{$to_id}'");
				DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=0,topic_num=0 WHERE ch_id = '{$from_id}'");
				if($from_info['parent_id'] != $to_info['parent_id']){
					if($from_info['parent_id'] > 0){						DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num-{$from_info['total_topic_num']} WHERE ch_id = '".$from_info['parent_id']."'");
					}
					if($to_info['parent_id'] > 0){						DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+{$from_info['total_topic_num']} WHERE ch_id = '".$to_info['parent_id']."'");
					}
				}
				if($cutc){
					DB::query("DELETE FROM ".DB::table('channel')." WHERE ch_id='{$from_id}'");
				}
				$this->update_category_cache();
			}
			return 1;
		}else{
			return 0;
		}
	}

	
	function category_tree($data, $parent_id = 0)
	{
		$tree = array();
		foreach ($data as $value) {
			if ($value['parent_id'] == $parent_id) {
				$tmp = array();
				$tmp = $value;
				$tmp['child'] = $this->category_tree($data, $value['ch_id']);
				$tree[$value['ch_id']] = $tmp;
			}
		}
		return $tree;
	}

	
	function add_category($ch_name, $display_order = 0, $parent_id = 0, $channel_typeid = 0)
	{
		$set_ary = array(
			'ch_name' => $ch_name,
			'parent_id' => $parent_id,
			'display_order' => $display_order,
			'channel_typeid' => $channel_typeid,
		);
		$cid = DB::insert('channel', $set_ary, true);
		return $cid;
	}

	
	function update_category($ch_id, $ch_name, $display_order, $recommend, $feed)
	{
		$set_ary = array(
			'ch_name' => $ch_name,
			'display_order' => $display_order,
			'recommend' => $recommend,
			'feed' => $feed,
		);
		DB::update('channel', $set_ary, array('ch_id' => $ch_id));
	}
	function update_catedata($ch_id, $set_ary=array(), $check_data = array()){
		DB::update('channel', $set_ary, array('ch_id' => $ch_id));
		if($check_data['tid'] != $check_data['otid']){
			DB::query("UPDATE ".DB::table('channel')." SET `channel_typeid` = '".$check_data['tid']."' where `parent_id`='{$ch_id}'");
		}elseif($check_data['pid'] != $check_data['opid']){
			DB::query("UPDATE ".DB::table('channel')." SET `channel_typeid` = '".$check_data['tid']."' where `ch_id`='{$ch_id}'");
			$topicnums = DB::result_first("SELECT topic_num FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
						DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num-{$topicnums} WHERE ch_id = '".$check_data['opid']."'");
						DB::query("UPDATE ".DB::table('channel')." SET total_topic_num=total_topic_num+{$topicnums} WHERE ch_id = '".$check_data['pid']."'");
		}elseif($check_data['ttp'] != $check_data['ottp']){			DB::query("UPDATE ".DB::table('topic')." SET managetype = '".$check_data['ttp']."' WHERE item = 'channel' AND item_id = '{$ch_id}'");
		}elseif($check_data['ppv'] != $check_data['oppv']){			if(''==$check_data['ppv']){
				DB::query("UPDATE ".DB::table('topic')." SET `type` = 'first' WHERE item = 'channel' AND item_id = '{$ch_id}'");
			}elseif(''==$check_data['oppv']){
				DB::query("UPDATE ".DB::table('topic')." SET `type` = 'channel' WHERE item = 'channel' AND item_id = '{$ch_id}'");
			}
		}
	}

	
	function delete_category($ch_id)
	{
				$category = $this->id2category($ch_id);
		if (empty($category)) {
			return -1;
		}
		if ($category['topic_num'] > 0) {
			return -2;
		}
				$sub_count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('channel')." WHERE parent_id='{$ch_id}'");
		if ($sub_count) {
			return -3;
		}
		DB::query("DELETE FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		DB::query("DELETE FROM ".DB::table('buddy_channel')." WHERE ch_id='{$ch_id}'");
		return 1;
	}

	
	function update_category_cache()
	{
		$cat_ary = $channles = $channle_types = $features = $ch_feed = $ch_recommend = $channle_trees = array();
		$query = DB::query("SELECT * FROM ".DB::table('feature'));
		while ($value = DB::fetch($query)) {
			$features[$value['featureid']] = $value['featurename'];
		}
		$query = DB::query("SELECT * FROM ".DB::table('channel_type'));
		while ($value = DB::fetch($query)) {
			$value['feature'] = array();
			if($value['default_feature']){
				$value['feature'][0] = $value['default_feature'];
			}
			if($value['featureid']){
				foreach(explode(',',$value['featureid']) as $val){
					if(in_array($val,array_keys($features))){
						$value['feature'][$val] = $features[$val];
					}
				}
			}
			$channle_types[$value['channel_typeid']] = $value;
		}
		$query = DB::query("SELECT * FROM ".DB::table('channel')." ORDER BY display_order ASC");
		while ($value = DB::fetch($query)) {
			$value['buddy_numbers'] = $this->getChannelBuddyUserNums($value['ch_id']);
			$channles[$value['ch_id']][$value['ch_id']] = $value['ch_id'];
			$cat_ary['channel_types'][$value['ch_id']]['channel_type'] = $channle_types[$value['channel_typeid']]['channel_type'] ? $channle_types[$value['channel_typeid']]['channel_type'] : '';
			$cat_ary['channel_types'][$value['ch_id']]['channel_typename'] = $channle_types[$value['channel_typeid']]['channel_typename'] ? $channle_types[$value['channel_typeid']]['channel_typename'] : '';
			$cat_ary['channel_types'][$value['ch_id']]['parent_id'] = $value['parent_id'];
			$cat_ary['channel_types'][$value['ch_id']]['manageid'] = $value['manageid'];
			$cat_ary['channel_types'][$value['ch_id']]['channel_typeid'] = $value['channel_typeid'];
			$cat_ary['channel_types'][$value['ch_id']]['feature'] = $channle_types[$value['channel_typeid']]['feature'] ? $channle_types[$value['channel_typeid']]['feature'] : array();
			$cat_ary['channel_types'][$value['ch_id']]['topic_template'] = $channle_types[$value['channel_typeid']]['topic_template'] ? $channle_types[$value['channel_typeid']]['topic_template'] : '';
			if ($value['parent_id'] == 0) {
				$cat_ary['channel_types'][$value['ch_id']]['template'] = $value['template'] ? $value['template'] : ($channle_types[$value['channel_typeid']]['template'] ? $channle_types[$value['channel_typeid']]['template'] : '');
				unset($value['template']);
				$cat_ary['first'][$value['ch_id']] = $value;
				if($value['recommend']){
					$ch_recommend[$value['ch_id']] = $value['ch_name'];
				}
				$channle_trees[$value['ch_id']]['id'] = $value['ch_id'];
				$channle_trees[$value['ch_id']]['name'] = $value['ch_name'];
			} else {
				$cat_ary['channel_types'][$value['ch_id']]['template'] = $value['template'] ? $value['template'] : ($channle_types[$value['channel_typeid']]['child_template'] ? $channle_types[$value['channel_typeid']]['child_template'] : '');
				unset($value['template']);
				$cat_ary['second'][$value['ch_id']] = $value;
				$channles[$value['parent_id']][$value['ch_id']] = $value['ch_id'];
				$channle_trees[$value['parent_id']]['child'][$value['ch_id']] = $value['ch_name'];
			}
			if($value['feed']){
				$ch_feed[$value['ch_id']] = $value['ch_name'];
			}
		}
		$cat_ary['recommends'] = $ch_recommend;
		$cat_ary['feeds'] = $ch_feed;
		$cat_ary['trees'] = $channle_trees;
		$cat_ary['channels'] = $channles;
		jconf::set('channel', $cat_ary);
	}

	
	function get_category()
	{
		$cat_ary = array();
		$cat_ary = jconf::get('channel');
		if (empty($cat_ary)) {
			$this->update_category_cache();
			$cat_ary = jconf::get('channel');
		}
		return $cat_ary;
	}
	function get_pub_channel()
	{
		global $_J;
		$channels = array();
		$cachefile = $this->get_category();
		$channel_enable = $cachefile && $cachefile['channels'] ? true : false;
		if($cachefile){
			$channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
			foreach($channel_one as $key => $val){
				if(empty($val['purview']) || ($val['purview'] && in_array($_J['member']['role_id'],explode(',',$val['purview'])))){$val['ok'] = true;}else{$val['ok'] = false;}
				if($val['verify'] && !in_array($_J['member']['role_id'],explode(',',$val['filter']))){$val['ck'] = 1;}else{$val['ck'] = 0;}
				$channels[$key] = $val;
			}
			$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
			foreach($channel_two as $k => $v){
				if(empty($v['purview']) || ($v['purview'] && in_array($_J['member']['role_id'],explode(',',$v['purview'])))){$v['ok'] = true;}else{$v['ok'] = false;}
				if($v['verify'] && !in_array($_J['member']['role_id'],explode(',',$v['filter']))){$v['ck'] = 1;}else{$v['ck'] = 0;}
				$channels[$v['parent_id']]['child'][$k] = $v;
			}
		}
		return array('channel_enable'=>$channel_enable,'channels'=>$channels);
	}
	function get_select_channel()
	{
		global $_J;
		$channels = array();
		$cachefile = $this->get_category();
		$channel_one = is_array($cachefile['first']) ? $cachefile['first'] : array();
		$channel_two = is_array($cachefile['second']) ? $cachefile['second'] : array();
		foreach($channel_one as $key => $val){
			if(!empty($val['purview']) && !in_array($_J['member']['role_id'],explode(',',$val['purview']))){
				$val['disabled'] = true;
			}
			$channels[$key] = $val;
			foreach($channel_two as $k => $v){
				if($v['parent_id'] == $key){
					if(!empty($v['purview']) && !in_array($_J['member']['role_id'],explode(',',$v['purview']))){
						$v['disabled'] = true;
					}
					$v['ch_name'] = '&nbsp;&nbsp;'.$v['ch_name'];
					$channels[$k] = $v;
				}
			}
		}
		return $channels;
	}

	
	function get_catselect($id = 0)
	{
		$html = '';
		$cat_ary = $this->get_category();
		if (!empty($cat_ary)) {
			$first_cat = $cat_ary['first'];
			foreach ($first_cat as $value) {
				$ps = '';
				if ($value['ch_id'] == $id) {
					$ps = ' selected="selected"';
				}
												$html .= '<option value="'.$value['ch_id'].'"'.$ps.'>'.$value['ch_name'].'</option>';
			}
						$html = '<select name="parent_id" id="parent_id">'.$html.'</select>';
		}
		return $html;
	}
	function get_channel_type(){
		$channeltype = array(0=>'普通模型');
		$query = DB::query("SELECT * FROM ".DB::table('channel_type'));
		while ($ctype = DB::fetch($query)){
			$channeltype[$ctype['channel_typeid']] = $ctype['channel_typename'];
		}
		return $channeltype;
	}
	function get_cattypeselect($id = 0)
	{
		$html = '';
		$channeltype = $this->get_channel_type();
		foreach ($channeltype as $key => $value) {
			$ps = '';
			if ($key == $id) {
				$ps = ' selected="selected"';
			}
			$html .= '<option value="'.$key.'"'.$ps.'>'.$value.'</option>';
		}
		$html = '<select name="channel_typeid" id="channeltypeid">'.$html.'</select>';
		return $html;
	}

	
	function upload_pic($id){

		$image_name = $id.".png";
		$image_path = RELATIVE_ROOT_PATH . 'images/channel/'.face_path($id);
		$image_file = $image_path . $image_name;
		if (!is_dir($image_path))
		{
			jio()->MakeDir($image_path);
		}
		jupload()->init($image_path,'image',true);
		jupload()->setMaxSize(1000);
		jupload()->setNewName($image_name);
		$result=jupload()->doUpload();
		if($result)
        {
			$result = is_image($image_file);
		}
		if(!$result)
        {
			unlink($image_file);
			return false;
		}else{
			if($GLOBALS['_J']['config']['ftp_on']) {
	            $ftp_key = randgetftp();
				$get_ftps = jconf::get('ftp');
	            $site_url = $get_ftps[$ftp_key]['attachurl'];
	            $ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
	            if($ftp_result > 0) {
	                jio()->DeleteFile($image_file);
	                $image_file = $site_url .'/'. str_replace('./','',$image_file);
	            }
	        }
			DB::update('channel', array('picture' => $image_file), array('ch_id' => $id));
		}
		return true;
	}

    
    function getChannelUser($channel,$limit=100){
        $uid = array();
        if (!$channel) {
            return $uid;
        } else if (is_array($channel)) {
            $where = " where `ch_id` in (".  jimplode($channel).")";
        } else {
            $channel = (int) $channel;
            $where = " where `ch_id` = '$channel' ";
        }
        $sql = "select `uid` from `".DB::table('buddy_channel')."` $where limit $limit ";

        $query = DB::query($sql);
        while ($rs = DB::fetch($query)) {
            $uid[$rs['uid']] = $rs['uid'];
        }
        return $uid;
    }

	

	function getThisChannel($ch_id=0){
		$channel = DB::fetch_first("SELECT * FROM ".DB::table('channel')." WHERE ch_id='{$ch_id}'");
		$return = array('name'=>'频道不存在','list'=>'txt','view'=>'post');
		$navhtml = '<a href="'.jurl('index.php?mod=channel').'" class="navLefthome">频道首页</a>';
		if($channel){
			if($channel['parent_id'] > 0){
				$channelp = DB::fetch_first("SELECT ch_name FROM ".DB::table('channel')." WHERE ch_id='{$channel['parent_id']}'");
				$navhtml .= $channelp ? '<a href="'.jurl('index.php?mod=channel&id='.$channel['parent_id']).'" class="navLeftsub">'.$channelp['ch_name'].'</a>' : '';
				$navhtml .= '<a href="'.jurl('index.php?mod=channel&id='.$channel['ch_id']).'" class="navLeftsub2 subon">'.$channel['ch_name'].'</a>';
			}else{
				$navhtml .= '<a href="'.jurl('index.php?mod=channel&id='.$channel['ch_id']).'" class="navLeftsub subon">'.$channel['ch_name'].'</a>';
			}
			$my_channels = $this->mychannel();
			$my_channels_key = array_keys($my_channels);
			$channel_buddy = follow_channel($channel['ch_id'],in_array($channel['ch_id'],$my_channels_key));
									$return['description'] = $channel['description'];
			$return['name'] = $channel['ch_name'];
			$return['list'] = $channel['display_list'];
			$return['view'] = $channel['display_view'];
			$return['follow_num'] = '<font class="follow_num_'.$channel['ch_id'].'">'.$channel['buddy_numbers'].'</font>';
			$return['topic_num'] = $channel['total_topic_num'];
			$return['follow_button'] = '<span id="follow_channel" class="follow_c_'.$channel['ch_id'].'">'.$channel_buddy.'</span>';
		}
		$return['navhtml'] = $navhtml;
		return $return;
	}


	

	function getChannelBuddyUserNums($ch_id=0){
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('buddy_channel')." WHERE ch_id='{$ch_id}'");
		DB::query("UPDATE ".DB::table('channel')." SET `buddy_numbers` = '".$count."' where `ch_id`='{$ch_id}'");
		return $count;
	}

	

	function getChannelRecTopic(){
		$rtopic = array();
		$sql = "SELECT r.tid,r.item_id,r.r_title,c.ch_name FROM ".DB::table('topic_recommend')." AS r LEFT JOIN ".DB::table('channel')." AS c ON r.item_id = c.ch_id WHERE r.item = 'channel' AND r.r_title <> '' AND (r.expiration>".time()." OR r.expiration=0) ORDER BY r.dateline DESC LIMIT 12";
        $query = DB::query($sql);
        while ($rs = DB::fetch($query)) {
            $rtopic[] = $rs;
        }
		return $rtopic;
	}
	function getChannelTopTopic(){
		$ttopic = array();
		$sql = "SELECT r.tid,t.content,t.content2 FROM ".DB::table('topic_recommend')." AS r LEFT JOIN ".DB::table('topic')." AS t ON r.tid = t.tid WHERE r.item = 'channel' AND r.recd = 4 AND (r.expiration>".time()." OR r.expiration=0) ORDER BY r.dateline DESC LIMIT 1";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)) {
            $ttopic = $rs;
        }
		if($ttopic){
			$ttopic['content'] .= $ttopic['content2'];
			unset($ttopic['content2']);
			$ttopic['content'] = jhtmlspecialchars(strip_tags($ttopic['content']));
			if(false !== strpos($ttopic['content'], 'http:/'.'/')) {
				$ttopic['content'] = preg_replace('~(http:/'.'/[a-z0-9-\.\?\=&;_@/%#]+?)\s+~i', '<a href="\\1" target="_blank">Click Here</a> ', $ttopic['content']);
				$ttopic['content'] = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&;_@/%#]*\$|sim", "", $ttopic['content']);
			}
		}
		return $ttopic;
	}

	
	function getChannelAll(){
		$channles = array();
		$my_channels = $this->mychannel();
		$my_channels_key = array_keys($my_channels);
		$query = DB::query("SELECT ch_id,ch_name,topic_num,total_topic_num,parent_id,buddy_numbers,picture FROM ".DB::table('channel')." ORDER BY parent_id ASC,display_order ASC");
		while ($value = DB::fetch($query)) {
			$parent_id = $value['parent_id'];
			$value['picture'] = $value['picture'] ? $value['picture'] : './images/channelimg.gif';
			unset($value['parent_id']);
			if($value['total_topic_num'] > $value['topic_num']){
				$value['topic_num'] = $value['total_topic_num'];
			}
			unset($value['total_topic_num']);
			$value['channel_buddy'] = follow_channel($value['ch_id'],in_array($value['ch_id'],$my_channels_key));
			if ($parent_id == 0) {
				$channles[$value['ch_id']] = $value;
			} else {
				$channles[$parent_id]['child'][$value['ch_id']] = $value;
			}
		}
		return $channles;
	}

	
	function getUserFansTop(){
		$users = array();
		$query = DB::query("SELECT uid,username,nickname FROM ".DB::table('members')." ORDER BY fans_count DESC LIMIT 12");
		while ($value = DB::fetch($query)) {
			$value['face'] = face_get($value['uid']);
			$users[] = $value;
		}
		return $users;
	}

	
	function get_two_new_topic($channel_ids,$num=2){
		$tids = $topic_list = $c_tids = $channel_topic = array();
		$query = DB::query("SELECT ch_name FROM ".DB::table('channel')." WHERE ch_id IN(".implode(",",$channel_ids).") ORDER BY ch_id");
		while ($value = DB::fetch($query)) {
			$channel_topic[$value['ch_name']] = array();
		}
		$sql = "SELECT a.tid, c.ch_name FROM ".DB::table('topic_channel')." a LEFT JOIN ".DB::table('topic_channel')." b ON a.item_id=b.item_id AND a.tid<b.tid LEFT JOIN ".DB::table('channel')." c ON a.item_id=c.ch_id  WHERE a.item_id IN(".implode(",",$channel_ids).") GROUP BY a.item_id,a.tid HAVING COUNT(b.tid)<'".$num."' ORDER BY a.item_id,a.tid";
		$query = DB::query($sql);
		while ($value = DB::fetch($query)) {
			$c_tids[$value['ch_name']][] = $value['tid'];
			$tids[] = $value['tid'];
		}
		$options = array('tid'=>$tids,'count'=>'20','order'=>'dateline DESC');
		$TopicListLogic = jlogic('topic_list');
		$info = $TopicListLogic->get_data($options);
		$topic_list = $info['list'];
		foreach($c_tids as $key => $val){
			foreach($val as $v){
				if($topic_list[$v]){
					$channel_topic[$key][$v] = $topic_list[$v];
					$channel_topic[$key][$v]['content'] = cut_str(strip_tags($topic_list[$v]['content']),80);
				}
			}
		}
		unset($tids);unset($topic_list);unset($c_tids);
		return $channel_topic;
	}

	
	function get_child_channel($channel_id = 0){
		$channels = array();
		$query = DB::query("SELECT * FROM ".DB::table('channel')." WHERE parent_id = '".$channel_id."'ORDER BY ch_id ASC");
		while ($value = DB::fetch($query)) {
			$channels[] = $value;
		}
		return $channels;
	}

		function get_channel_typeinfo_byid($id=0,$only_feature=0){
		$cachefile = jconf::get('channel');
		$channel_types = is_array($cachefile['channel_types']) ? $cachefile['channel_types'] : array();
		if($only_feature){
			return $channel_types[$id]['feature'] ? $channel_types[$id]['feature'] : array();
		}else{
			return $channel_types[$id] ? $channel_types[$id] : array();
		}
	}

	function get_user_role(){
		$role_list = array();
		$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
		while (false != ($row = DB::fetch($query))) {
			$role_list[$row['value']] = $row;
		}
		return $role_list;
	}

	function delete_channel_type($ids=array()){
		$noids = array();
		$channel_typeids = array(1,2);		$query = DB::Query("SELECT DISTINCT channel_typeid FROM ".DB::table('channel')." WHERE channel_typeid>0");
		while(false != ($row = $query->GetRow())){
			if(!in_array($row['channel_typeid'],array(1,2))){
				$channel_typeids[] = $row['channel_typeid'];
			}
		}
		foreach($ids as $k => $v){
			if(in_array($v,$channel_typeids)){
				$noids[] = $v;
				unset($ids[$k]);
			}
		}
		if($ids){
			DB::Query("delete from ".DB::table('channel_type')." where `channel_typeid` in(".jimplode($ids).")");
			return array('ids'=>$ids,'noids'=>$noids);
		}else{
			return '';
		}
	}

	function add_channel_type($name='', $type=''){
	 	$query = DB::Query("select * from ".DB::table('channel_type')." where `channel_typename` = '{$name}' or `channel_type` = '{$type}' ");
		$channel_typelist = $query->GetRow();
		if($channel_typelist){
			return -1;
		}
		DB::Query("insert into ".DB::table('channel_type')."(`channel_typename`,`channel_type`) values ('{$name}','{$type}')");
		return 1;
	}

	function get_channel_typebyid($id=0){
		$query = DB::Query("select * from ".DB::table('channel_type')." where `channel_typeid` = '{$id}' ");
		return $query->GetRow();
	}

	function get_feature_formdata(){
		$feature_list = array();
		$query = DB::query("select featureid as value,featurename as name from ".DB::table('feature'));
		while (false != ($row = DB::fetch($query))){
			$feature_list[] = $row;
		}
		return $feature_list;
	}

	function get_channel_typelist($per_page_num=20){
		$query_link = 'admin.php?mod=channel&code=channeltype';
		$total_record = DB::result_first("select count(*) as `total_record` from ".DB::table('channel_type'));
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array'),'20 50 100 200,500');
	 	$query = DB::Query("select * from ".DB::table('channel_type')." order by `channel_typeid` desc {$page_arr['limit']}");
		$channel_type_list=array();
		while(false != ($row = $query->GetRow())){
			$row['template'] = $row['template'] ? $row['template'].'.html' : '';
			$row['child_template'] = $row['child_template'] ? $row['child_template'].'.html' : '';
			$row['topic_template'] = $row['topic_template'] ? $row['topic_template'].'.html' : '';
			$channel_type_list[]=$row;
		}
		return array('total'=>$total_record, 'page'=>$page_arr, 'list'=>$channel_type_list);
	}

	function check_channel_type_byname($name=''){
		$query = DB::Query("select * from ".DB::table('channel_type')." where `channel_typename` = '{$name}' ");
		$channel_typelist = $query->GetRow();
		if($channel_typelist){
			return 1;
		}
		return 0;
	}

	function modiy_channel_type($typeid=0, $name='', $template='', $child_template='', $topic_template='', $featureid=0, $default_feature=''){
		$sql = "update ".DB::table('channel_type')." set  `channel_typename` = '{$name}', `template` = '{$template}', `child_template` = '{$child_template}', `topic_template` = '{$topic_template}', `featureid` = '{$featureid}', `default_feature` = '{$default_feature}' where `channel_typeid`='$typeid'";
		DB::Query($sql);
		$this->update_category_cache();
	}

		function buddy_channel($cid=0, $action=1){
		$isexists = $this->is_exists($cid);
		if($isexists){
			if($action){
				DB::query("INSERT INTO ".DB::table('buddy_channel')." (`uid`,`ch_id`) values ('".MEMBER_ID."','{$cid}')");
				DB::query("UPDATE ".DB::table('channel')." SET `buddy_numbers` = buddy_numbers+1 where `ch_id`='{$cid}'");
			}else{
				DB::query("DELETE FROM ".DB::table('buddy_channel')." WHERE uid = '".MEMBER_ID."' AND ch_id = '$cid'");
				DB::query("UPDATE ".DB::table('channel')." SET `buddy_numbers` = buddy_numbers-1 where `ch_id`='{$cid}'");
			}
		}
	}

	function channel_isbuddy($cid){
		return DB::result_first("SELECT count(*) FROM ".DB::table('buddy_channel')." WHERE uid = '".MEMBER_ID."' AND ch_id = '$cid'");
	}

		function get_channel_topic_num(){
		$topic_nums = array();
		$query = DB::query("SELECT ch_id,total_topic_num FROM ".DB::table('channel')." where recommend >0");
		while ($value = DB::fetch($query))
		{
			$topic_nums[$value['ch_id']] = $value['total_topic_num'];
		}
		return $topic_nums;
	}

		function get_channel_manager($id){
		$managers = array();
		$info = DB::fetch_first("SELECT `manageid`,`managename` FROM ".DB::table('channel')." WHERE ch_id='{$id}'");
		if($info && $info['manageid'] && $info['managename']){
			$uids = explode(',',$info['manageid']);
			$username = explode('|',$info['managename']);
			foreach($uids as $k => $v){
				$managers[] = array('uid'=>$v,'nickname'=>$username[$k]);
			}
		}
		return $managers;
	}
}
?>