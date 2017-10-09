<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename vote.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1232283110 32841 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class VoteLogic
{
	


	function VoteLogic()
	{

	}

	
	function &get_list($param)
	{
		$list = array();
		extract($param);
		$where_sql = ' WHERE 1 ';
		$order_sql = '';
		$limit_sql = '';
		if ($where) {
			$where_sql .= ' AND '.$where;
		}

		if ($order) {
			$order_sql = ' ORDER BY '.$order;
		}

		if ($limit) {
			$limit_sql = ' LIMIT '.$limit;
		}

		$query = DB::query("SELECT * FROM ".DB::table('vote')." {$where_sql} {$order_sql} {$limit_sql}");
		while($value = DB::fetch($query)) {
			$list[] = $value;
		}
		return $list;
	}

	
	function &get_new_list($num = 10)
	{
		$num = intval($num);
		if (empty($num)) {
			return false;
		}
		$list = array();
		$list = $this->get_list(array('order' => 'dateline DESC ', 'limit' => $num));
		return $list;
	}

	
	function &get_hot_list($num = 10)
	{
		$num = intval($num);
		if (empty($num)) {
			return false;
		}
		$list = array();
		$timerange = TIMESTAMP - 2592000;
		$where = " lastvote >= '{$timerange}' ";
		$order = " voter_num DESC ";
		$where = array(
			'where' => $where,
			'order' => $order,
			'limit' => $num,
		);
		$list = $this->get_list($where);
		return $list;
	}

	
	function &get_recd_list()
	{
		$show_config = jconf::get('show');
		$num = $show_config['vote']['recd'];
		$list = array();
		$where = " recd=1 ";
		$order = " lastvote DESC ";
		$where = array(
			'where' => $where,
			'order' => $order,
			'limit' => $num,
		);
		$list = $this->get_list($where);
		return $list;
	}

	
	function find($param)
	{
		if (!empty($param['where'])) {
			$where_sql .= " {$param['where']} ";
		}

		$order_sql = " ";
		if (!empty($param['order'])) {
			$order_sql = " {$param['order']} ";
		}

		$limit_sql = " ";
		if (!empty($param['limit'])) {
			$limit_sql = " {$param['limit']} ";
		}

		$vote_list = array();
		$count = max(0, (int) $param['count']);
		if($count < 1) {
			$count_sql = "SELECT COUNT(*)
						  FROM ".DB::table('vote')." AS v
						  WHERE {$where_sql}";
			$count = DB::result_first($count_sql);
		}

		if ($count) {
			if ($param['page']) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $param['perpage'], $param['page_url'], $_config);
				$limit_sql = $page_arr['limit'];
			} elseif ($param['count']) {
				$limit_sql = " LIMIT {$count} ";
			}

			$uid_ary = array();
			$sql = "SELECT v.*,vf.*
				    FROM ".DB::table('vote')." AS v
					LEFT JOIN ".DB::table('vote_field')." AS vf
					USING (vid)
					WHERE {$where_sql}
					{$order_sql}
					{$limit_sql} ";

			$query = DB::query($sql);
			while ($value = DB::fetch($query)) {
				$last_update_time = $value['lastvote'] ? $value['lastvote'] : $value['dateline'];
								$value['last_update_time'] = my_date_format2($last_update_time);
				$value['option'] = unserialize($value['option']);
				$value['input_type'] = $value['multiple'] ? 'checkbox' : 'radio';

								$value['is_expiration'] = false;
				if ($value['expiration'] <= TIMESTAMP) {
					$value['is_expiration'] = true;
				}
				$value['expiration'] = my_date_format($value['expiration']);
				$vote_list[] = $value;
				$uid_ary[] = $value['uid'];
			}


						$def_items = 2;

						foreach ($vote_list as $key => $val) {
				$vote_itemss = $this->get_vote_item($val['vid'], MEMBER_ID);
				$vote_items = !empty($vote_itemss) ? $vote_itemss['option'] : array();
				$vote_list[$key]['is_vote'] = false;
				$vote_list[$key]['vote_show'] = $val['option'];

				if (!empty($vote_items)) {
					$vi_count = count($vote_items);
					$vote_list[$key]['vi_count'] = $vi_count;
					$vote_list[$key]['is_vote'] = true;
					$vote_list[$key]['hasfollowed'] = $vote_itemss['follow_vote'];
					if ($vi_count >= $def_items) {
						$vote_list[$key]['vote_show'] = array_slice($vote_items, 0, $def_items);
					} else {
						$item = $vote_items[0];
						$index = array_search($item, $vote_list[$key]['vote_show']);
						if ($index !== false) {
							unset($vote_list[$key]['vote_show'][$index]);
							array_unshift($vote_list[$key]['vote_show'], $item);
						} else {
							unset($vote_list[$key]['vote_show'][$def_items-1]);
							array_unshift($vote_list[$key]['vote_show'], $item);
						}
					}
				}
				if ($val['lastvote'] > 0){
					$last_vote_data = DB::fetch_first("SELECT vu.*,m.nickname FROM ".DB::table('vote_user')." AS vu LEFT JOIN ".DB::table("members")." AS m  USING (uid) WHERE vu.dateline = '".$val['lastvote']."'");
					$last_vote_data['option'] = unserialize($last_vote_data['option']);
					$last_vote_data['option'] = '"'.implode('","', $last_vote_data['option']).'"';
					$vote_list[$key]['lusername'] = $last_vote_data['username'];
					$vote_list[$key]['lnickname'] = $last_vote_data['username'] ? $last_vote_data['nickname'] : '***';
					$vote_list[$key]['loption'] = $last_vote_data['option'];
					$vote_list[$key]['last_vote_time'] = my_date_format($last_vote_data['dateline']);
				}
			}
			return array(
				'count' => $count,
				'vote_list' => $vote_list,
				'uids' => array_unique($uid_ary),
				'page' => $page_arr,
			);
		}
		return false;
	}

	
	function &id2voteinfo($vid, $type = 'all')
	{
		$vid = (int) $vid;
		if ($type == 'all') {
			$vote = DB::fetch_first("SELECT vf.*, v.*
							 		 FROM ".DB::table('vote')." v
							 		 LEFT JOIN ".DB::table('vote_field')." vf
							 		 USING (vid)
							 		 WHERE v.vid='{$vid}'");
		} else if ($type == 'm') {
			$vote = DB::fetch_first("SELECT *
				 		 			 FROM ".DB::table('vote')."
				 		 			 WHERE vid='{$vid}'");
		}
		return $vote;
	}

	
	function id2subject($vid)
	{
		$vid = (int) $vid;
		$subject = DB::result_first("SELECT subject FROM ".DB::table('vote')." WHERE vid='{$vid}' ");
		return $subject;
	}

	
	function is_exists($vid)
	{
		$vid = (int) $vid;
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote')." WHERE vid='{$vid}'");
		return $count;
	}

	
	function &get_option_by_vid($vid)
	{
		$vid = (int) $vid;
				$allvote = 0;

				$query = DB::query("SELECT * FROM ".DB::table('vote_option')." WHERE vid='{$vid}' ORDER BY oid");

		while ($value = DB::fetch($query)) {
			$allvote += intval($value['vote_num']);
            $picURL = $this->getPicUrl($value['pid']);            if($picURL){
                $value = array_merge($value,$picURL);
            }

			$option[] = $value;
		}
		return array('option' => $option, 'allvote' => $allvote);
	}

	function getPicUrl($pid){
        $pid = (int) $pid;
		$query= DB::query("select picurl,picurl_big from ".TABLE_PREFIX."vote_image where `id` = '$pid'");
		return DB::fetch($query);
	}

		function option_nums($vid)
	{
		$vid = (int) $vid;
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_option')." WHERE vid='{$vid}'");
		return $count;
	}

	
	function chk_post(&$post, $type = "create", $params = null)
	{
				$maxoption = 50;
		$option_nums = 0;
		if ($type == 'modify') {
			$option_nums = $this->option_nums($post['vid']);
			$maxoption = 50 - $option_nums;
		}

		$newoption = $optionarr = array();

				$post['subject'] = strip_tags(getstr(trim($post['subject']), 50, 1, 1));

				if (strlen($post['subject']) < 2) {
			return -1;
		}

				if (!$params['no_chk_option']) {
			$post['preview'] = array();
			$post['option'] = array_unique($post['option']);
			foreach ($post['option'] as $key => $val) {
				$option = getstr(trim($val), 40, 1, 1);
				if(strlen($option) && count($newoption) < $maxoption) {
					$newoption[$key]['option'] = $option;
					if($post['pic_id']){
						$newoption[$key]['pid'] = $post['pic_id'][$key];
					}
					if(count($post['preview']) < 2 ) {
						$post['preview'][] = $option;
					}
				}
			}

			$maxoption = count($newoption);
			if ($type == 'modify') {
				$maxoption += $option_nums;
			}

						if (count($newoption) < 2 && $type == 'create') {
				return -2;
			}

			$post['newoption'] = $newoption;
		}

				$post['message'] = strip_tags(getstr(trim($post['message']), 800, 1, 1));

				if (!$params['no_chk_maxchoice']) {
			$post['maxchoice'] = $post['maxchoice'] < $maxoption ? intval($post['maxchoice']) : $maxoption;
		}

				$expiration = 0;
		if($post['expiration']) {
			$expiration = jstrtotime(trim($post['expiration']));
			if($expiration <= TIMESTAMP) {
				return -3;
			}
		}
		$post['expiration'] = $expiration;

		if(isset($post['is_view'])) {
			$post['is_view'] = (int) $post['is_view'];
		}

		return 1;
	}

	
	function create($post, &$ret)
	{
		$r = $this->chk_post($post);
		if ($r != 1) {
			return $r;
		}

		$ret['subject'] = $post['subject'];
		$setarr = array(
			'uid' => $post['uid'],
			'username' => $post['username'],
			'subject' => $post['subject'],
			'maxchoice' => $post['maxchoice'],
			'multiple' => $post['maxchoice'] > 1 ? 1 : 0,
			'is_view' => $post['is_view'],
			'expiration' => $post['expiration'],
			'dateline' => TIMESTAMP,
			'postip' => $GLOBALS['_J']['client_ip'],
			'item' => $post['item'],
			'item_id' => $post['item_id'],
			'verify' => isset($post['verify']) ? 0 : 1,
			'tab' => (isset($post['tab']) && $post['tab'] == 'pic')  ? 1 : 0,
		);
		$setarr['time_val'] = max(0, (int) $post['time_val']);
		$setarr['time_unit'] = in_array($post['time_unit'], array('y', 'm', 'd', 'h', 'i', 's', )) ? $post['time_unit'] : 'h';
		$setarr['vote_limit'] = max(0, min(100, (int) $post['vote_limit']));

		$vid = DB::insert('vote', $setarr, true);
		$ret['vid'] = $vid;

				if($setarr['verify'] == 0){
			if($notice_to_admin = $GLOBALS['_J']['config']['notice_to_admin']){
				$pm_post = array(
					'message' => MEMBER_NICKNAME."发布了一个投票进入待审核状态，<a href='admin.php?mod=vote&code=verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$notice_to_admin),
				);
								$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
				load::logic('pm');
				$PmLogic = new PmLogic();
				$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
		}

		$setarr = array(
			'vid' => $vid,
			'message' => $post['message'],
			'option' => addslashes(serialize($post['preview']))
		);
		DB::insert('vote_field', $setarr);

				if($post['item'] == 'qun' && $post['item_id']){
			$qun_vote = array(
				'qid' => $post['item_id'],
				'vid' => $vid,
				'recd' => 0,
			);
			DB::insert('qun_vote', $qun_vote);
		}

		$optionarr = array();
		$pid_arr = array();
		foreach($post['newoption'] as $key => $value) {
			$pid = $value['pid'] ? $value['pid']  : 0;
			$optionarr[] = "('$vid', '$value[option]' , '$pid')";
			if($pid > 0){
				$pid_arr[] = $pid;
			}
		}
		if($pid_arr){
			DB::query("update `" . TABLE_PREFIX . "vote_image` set `vid` = '$vid' where `id` in ('" . implode("','",$pid_arr) . "')");
		}

				DB::query("INSERT INTO ".DB::table('vote_option')."
				   (`vid` , `option` , `pid`) VALUES ".implode(',', $optionarr));

                update_credits_by_action('vote_add',$post['uid']);

		return 1;
	}

	
	function modify($post)
	{
		$setarr = array(
			'subject' => $post['subject'],
			'maxchoice' => $post['maxchoice'],
			'multiple' => $post['maxchoice'] > 1 ? 1 : 0,
			'is_view' => (int) $post['is_view'],
			'recd' => isset($post['recd']) ? 1 : 0,
			'expiration' => $post['expiration'],
		);
		DB::update('vote', $setarr, array('vid' => $post['vid']));
		DB::update('vote_field', array('message' => $post['message']), array('vid' => $post['vid']));

				if (!empty($post['newoption'])) {
			$optionarr = array();
			foreach($post['newoption'] as $key => $value) {
				$optionarr[] = "('{$post['vid']}', '$value')";
			}

						DB::query("INSERT INTO ".DB::table('vote_option')."
					   (`vid`, `option`) VALUES ".implode(',', $optionarr));
		}
	}

	
	function update_options($vid, $old_options, $new_options, $is_voted = false ,$old_pic, $new_pic)
	{
		$vid = (int) $vid;
		$options = $old_options;
		$preview_updata_flg = false;

				if (!empty($options)) {
			if (!$is_voted || MEMBER_ROLE_TYPE == 'admin') {
				$preview = array();
				$keys = array_keys($options);
				$options = array_unique($options);

								if (count($options) > 1) {
					foreach ($keys as $i) {
						if (!empty($options[$i])) {
														$val = $options[$i];
							$p = getstr(trim($val), 40, 1, 1);
							if (empty($p)) {
								continue;
							}

							$pid = $old_pic[$i] ? $old_pic[$i] : 0;
							if($pid){
								$old_pic_id = DB::result_first("select `pid` from `".TABLE_PREFIX."vote_option` where `oid` = '$i'");
								if($old_pic_id != $pid){
									$old_pic_url = DB::result_first("select `picurl` from `".TABLE_PREFIX."vote_image` where `id` = '$old_pic_id'");
									$old_pic_url && unlink($old_pic_url);
									DB::query("delete from `".TABLE_PREFIX."vote_image` where `id` = '$old_pic_id'");
								}
								DB::update('vote_image',array('vid'=>$vid),array('id'=>$pid));
							}
							DB::update('vote_option', array('option'=>$p,'pid'=>$pid), array('oid'=>$i));
						} else {
														DB::query("DELETE FROM ".DB::table('vote_option')." WHERE oid='{$i}'");
						}
					}
					$preview_updata_flg = true;
				}
			}
		}

				if (!empty($new_options)) {
			$new_options = array_unique($new_options);
			foreach ($new_options as $key=>$val) {
				$qid = $new_pic[$key] ? $new_pic[$key] : 0;
				$ret = $this->add_opt($vid, $val, $qid);
			}
		}

		if ($preview_updata_flg) {
						$preview = array();
			$options = $this->get_option_by_vid($vid);
			foreach ($options['option'] as $val) {
				if(count($preview) < 2 ) {
					$preview[] = $val['option'];
				}
			}
			$str_options = addslashes(serialize($preview));
			DB::update('vote_field', array('option'=>$str_options), array('vid'=>$vid));
		}
	}

	
	function get_joined($uid)
	{
		$vids = array();
		$where_sql = ' 1 ';

		if (is_array($uid)) {
			$where_sql .= " AND uid IN(".jimplode($uid).")";
		} else {
			$where_sql .= " AND uid='{$uid}' ";
		}

		$query = DB::query("SELECT vid
						    FROM ".DB::table('vote_user')."
						    WHERE {$where_sql} ");
		while ($value = DB::fetch($query)) {
			$vids[] = $value['vid'];
		}
		return $vids;
	}

	
	function get_vote_item($vid, $uid, $isanonymous=0, $dateline = 0)
	{
		$vid = (int) $vid;
		$uid = (int) $uid;
		if($dateline > 0) {
						$dmn = $dateline - rand(100, 1000);
			$dmx = $dateline + rand(1, 10);
			$ds = " AND (`dateline` BETWEEN $dmn AND $dmx) ";
					}
		if ($isanonymous) {
			$sql = "SELECT * FROM ".DB::table('vote_user')." WHERE uid='{$uid}' AND vid='{$vid}' $ds AND username<>'' ORDER BY `id` DESC LIMIT 1";
		}else{
			$sql = "SELECT * FROM ".DB::table('vote_user')." WHERE uid='{$uid}' AND vid='{$vid}' $ds ORDER BY `id` DESC LIMIT 1";
		}
		$ret = DB::fetch_first($sql);
		if ($ret) {
									$ret['option'] = unserialize($ret['option']);
			return $ret;
		}
		return false;
	}

	
	function is_voted($vid, $uid, $retc = 0)
	{
		$count = 0;
		$vid = (int) $vid;
		if($vid > 0) {
			$vote = DB::fetch_first("SELECT * FROM ".DB::table('vote')." WHERE `vid`='$vid'");
			if($vote) {
				$p = array('vid' => $vid);
				$uid = (int) $uid;
				if($uid > 0) {
					$p['uid'] = $uid;
				}
				$vote_limit = 0;
				if($vote['time_val'] > 0 && $vote['vote_limit'] > 0) {
					$time_units = array('y' => 31104000, 'm' => 2592000, 'd' => 86400, 'h' => 3600, 'i' => 60, 's' => 1, );
					$vote['limit_time'] = ($vote['time_val'] * $time_units[$vote['time_unit']]);
					if($vote['limit_time'] > 0) {
						$vote_limit = 1;
						$p['>@dateline'] = (TIMESTAMP - $vote['limit_time']);
					}
				}
				$count = jtable('vote_user')->count($p);
								if($retc) {
					return $count;
				}
				if($vote_limit) {
					$count = (($count < $vote['vote_limit']) ? 0 : 1);
				}
			}
		}
		return $count;
	}

	
	function is_followed($vid, $uid)
	{
		$vid = (int) $vid;
		$uid = (int) $uid;
		$follow_vote = DB::result_first("SELECT follow_vote
								   FROM ".DB::table('vote_user')."
								   WHERE uid='{$uid}' AND vid='{$vid}'");
		return $follow_vote ? 1 : 0;
	}

	
	function do_vote($param, &$result)
	{
		extract($param);

				if ($this->is_voted($vid, $uid)) {
			return -1;
		}

				$list = $optionarr = $setarr = array();
		foreach($option as $key => $val) {
			$optionarr[] = intval($val);
			if(count($optionarr) > $maxchoice) {
				return -2;
			}
		}

				$query = DB::query("SELECT `option`
							FROM ".DB::table('vote_option')."
							WHERE oid IN ('".implode("','", $optionarr)."') AND vid='{$vid}'");
		while($value = DB::fetch($query)) {
			$list[] = addslashes($value['option']);
		}

		if(empty($list)) {
			return -3;
		}

		$result['voted_option'] = array();
		if (count($list) == 1) {
			$result['voted_option'] = $list;
		} else {
			$result['voted_option'] = array_slice($list, 0, 2);
		}

				DB::query("UPDATE ".DB::table('vote_option')."
				   SET vote_num=vote_num+1
				   WHERE oid IN ('".implode("','", $optionarr)."') AND vid='{$vid}'");

		$joined_uids = array();
		$query = DB::query("SELECT uid FROM ".DB::table('vote_user')." WHERE vid='{$vid}' AND follow_vote=1");
		while ($value=DB::fetch($query)) {
			$joined_uids[$value['uid']] = $value['uid'];
		}

		
		$setarr = array(
			'uid' => $uid,
			'username' => $anonymous ? '': $username,
			'vid' => $vid,
			'option' => addslashes(serialize($list)),			'follow_vote' => $follow_vote,
			'dateline' => TIMESTAMP
		);

		DB::insert('vote_user', $setarr);

		DB::query("UPDATE ".DB::table('vote')."
				   SET voter_num=voter_num+1, lastvote='".TIMESTAMP."'
				   WHERE vid='{$vid}'");

				
		DB::query("UPDATE ".DB::table('members')." SET vote_new=vote_new+1 WHERE uid IN(".jimplode($joined_uids).")");

		update_credits_by_action('vote_vote', $uid);

		return 1;
	}

	
	function delete($ids)
	{
				$sparecredit = $spaces = $polls = $newpids = array();
		$delnum = 0;
		if (!is_array($ids)) {
			$ids = (array)$ids;
		}
		$query = DB::query("SELECT * FROM ".DB::table('vote')." WHERE vid IN (".jimplode($ids).")");
		while ($value = DB::fetch($query)) {
			if(jallow($value['uid'])) {
				$polls[] = $value;

                                update_credits_by_action('vote_del',$value['uid']);
			}
		}
		if (empty($polls)) {
			return false;
		}

				foreach($polls as $key => $value) {
			$newpids[] = $value['vid'];
		}

				DB::query("DELETE FROM ".DB::table('vote')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_field')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_option')." WHERE vid IN (".jimplode($newpids).")");
		DB::query("DELETE FROM ".DB::table('vote_user')." WHERE vid IN (".jimplode($newpids).")");
		$pic_arr = array();
		$query = DB::query("select * from ".DB::table('vote_image')." WHERE vid IN (".jimplode($newpids).")");
		while ($rs = DB::fetch($query)) {
			if($rs['picurl']){
				unlink($rs['picurl']);
			}
		}
		DB::query("delete from ".DB::table('vote_image')." WHERE vid IN (".jimplode($newpids).")");

				$tids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('topic_vote')." WHERE item_id IN (".jimplode($newpids).") ");
		while ($value = DB::fetch($query)) {
			$tids[] = $value['tid'];
		}

		if (!empty($tids)) {
						$topic_reply_ids = array();
			$query = DB::query("SELECT tid,type FROM ".DB::table('topic')." WHERE tid IN(".jimplode($tids).")");
			while ($value = DB::fetch($query)) {
				if ($value['type'] == 'reply') {
					$topic_reply_ids[] = $value['tid'];
				}
			}

			if (!empty($topic_reply_ids)) {

				$TopicLogic = jlogic('topic');
				$TopicLogic->Delete($topic_reply_ids);
			}

						DB::query("DELETE FROM ".DB::table('topic_vote')." WHERE item_id IN (".jimplode($newpids).") ");
		}

		return $polls;
	}

	
	function modify_expiration($vid, $expiration)
	{
		$vid = (int) $vid;
		$expiration = jstrtotime(trim($expiration));
		if($expiration <= TIMESTAMP) {
			return -1;
		}
		DB::update('vote', array('expiration' => $expiration), array('vid' => $vid));
		return 1;
	}

	
	function add_opt($vid, $newoption, $pid)
	{
		$vid = (int) $vid;
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_option')." WHERE vid='{$vid}'");
		if($count >= 50) {
			return -1;
		}
		$newoption = getstr(trim($newoption), 40, 1, 1);
		if(strlen($newoption) < 1) {
			return -2;
		}
		$setarr = array(
			'vid' => $vid,
			'option' => $newoption,
			'pid' => $pid
		);
		DB::insert('vote_option', $setarr);
		if($qid){
			DB::update('vote_image',array('vid'=>$vid),array('id'=>$pid));
		}
		return 1;
	}

	
	function update_recd($data)
	{
		$data['vid'] = (int) $data['vid'];
		DB::query("UPDATE ".DB::table("vote")." SET recd='{$data['recd']}' WHERE vid='{$data['vid']}'");
	}

	
	function get_publish_form_param($dateline = '')
	{
		if (empty($dateline)) {
						$expiration = my_date_format(TIMESTAMP+7*24*3600, 'Y-m-d');
			$hour_select = mk_time_select();
			$min_select =  mk_time_select('min');
		} else {
			$expiration = my_date_format($dateline, 'Y-m-d ');
			$hour_select = mk_time_select('hour', my_date_format($dateline, 'H'));
			$min_select =  mk_time_select('min', my_date_format($dateline, 'i'));
		}

		return array(
			'expiration' => $expiration,
			'hour_select' => $hour_select,
			'min_select' => $min_select,
		);
	}

	
	function process_detail($vote, $uid)
	{
		$vid = $vote['vid'];
		if('qun' == $vote['item'] && $vote['item_id']){
			$qun_info = $this->Vote_Qun_Info($vote['item_id']);
			$vote['from_html'] = "来自".$GLOBALS['_J']['config'][changeword][weiqun]."：<a href='index.php?mod=qun&qid=$qun_info[qid]' target='_blank'>".$qun_info['name']."</a>";
		}elseif('event' == $vote['item'] && $vote['item_id']){
			$event_info = $this->Vote_Event_Info($vote['item_id']);
			$vote['from_html'] = "来自活动：<a href='index.php?mod=event&code=detail&id=$event_info[id]' target='_blank'>".$event_info['title']."</a>";
		}

				$hasvoted = null;
		if($vote['time_val'] > 0 && $vote['vote_limit'] > 0) {
			$time_units = array('y' => '年', 'm' => '月', 'd' => '天', 'h' => '小时', 'i' => '分钟', 's' => '秒', );
			$voted_count = $this->is_voted($vid, $uid, 1);
			$vote['limit_html'] = "<b>{$vote['time_val']}{$time_units[$vote['time_unit']]}</b>之内限制最多只能投票<b>{$vote['vote_limit']}</b>次；本时间段内您已投票<b>{$voted_count}</b>次。";
			$hasvoted = (($voted_count < $vote['vote_limit']) ? 0 : 1);
		}

				if ($vote['multiple']) {
			$vote['input_type'] = 'checkbox';
		} else {
			$vote['input_type'] = 'radio';
		}

				$allowedvote = true;

		$expiration = false;
				if($vote['expiration'] && $vote['expiration'] < TIMESTAMP) {
			$allowedvote = false;
			$expiration = true;
		}

				if(is_null($hasvoted)) {
			$hasvoted = $this->is_voted($vid, $uid);
		}
				$hasfollowed = $this->is_followed($vid, $uid);

		$info = $this->get_option_by_vid($vid);
		$allvote = $info['allvote'];
		$option = $info['option'];

		$allow_view = true;
		if (!$vote['is_view'] && !$hasvoted) {
			$allow_view = false;
		}

				foreach ($option as $key => $value) {
			if ($allow_view) {
				if ($value['vote_num'] && $allvote) {
					$value['percent'] = round($value['vote_num']/$allvote, 2);
					$value['width'] = round($value['percent']*160);
					$value['percent'] = $value['percent']*100;
				} else {
					$value['width'] = $value['percent'] = 0;
				}
			} else {
				$value['vote_num'] = $value['width'] = $value['percent'] = 0;
			}
			$option[$key] = $value;
		}

		return array(
			'vote' => $vote,
			'option' => $option,
			'allow_view' => $allow_view,
			'allowedvote' => $allowedvote,
			'hasvoted' => $hasvoted,
			'hasfollowed' => $hasfollowed,
		);
	}

	
	function Vote_Qun_Info($qid){
		$qid = (int) $qid;
		$qun_info = array();
		$sql = "select `qid`,`name` from `".TABLE_PREFIX."qun` where `qid` = '$qid'";
		$qun_info = DB::fetch_first($sql);
		return $qun_info;
	}

	
	function Vote_Event_Info($eid){
		$eid = (int) $eid;
		$event_info = array();
		$sql = "select `id`,`title` from `".TABLE_PREFIX."event` where `id` = '$eid'";
		$event_info = DB::fetch_first($sql);
		return $event_info;
	}

	
	function allowedCreate($uid = MEMBER_ID){
		$uid = (int) $uid;
		$member = DB::fetch_first("SELECT validate FROM ".DB::table('members')." WHERE uid='{$uid}'");
		$config = jconf::get();
		if($config['vote_vip']){
			if(!$member['validate']){
				return "非V认证用户不允许发起投票,<a href='index.php?mod=other&code=vip_intro'>点此申请V认证</a>";
			}
		}
	}

	
	function upload_pic($id){

		$image_name = $id.".jpg";
		$image_path = RELATIVE_ROOT_PATH . 'images/vote/'.face_path($id);
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
			DB::update('vote_field', array('img' => $image_file), array('vid' => $id));
		}
		return true;
	}

    
    public function update_vote($vid, $option){
        $vid = (int) $vid;
		jtable('vote')->update($option,array('vid' => $vid));
    }

    
    public function update_vote_field($vid,$option){
        $vid = (int) $vid;
		jtable('vote_field')->update($option,array('vid' => $vid));
    }

    
    public function get_count_vote_user($vote_id){
    	$vote_id = (int) $vote_id;
        $r = jtable('vote_user')->count(array('vid'=>$vote_id));
        return $r;
    }


    
    public function delete_vote_option($oid){
        $oid = (int) $oid;
    	return jtable('vote_option')->delete(array('oid'=>$oid));
    }

    
    public function update_vote_user($vid,$option){
        $vid = (int) $vid;
		jtable('vote_user')->update($option,$vid);
    }

    
    public function count_vote($p){
        $r = jtable('vote')->count($p);
        return $r;
    }

    public function get_vote_subject($where_sql,$start,$perpage){
		$start = max(0, (int) $start);
		$perpage = max(0, (int) $perpage);
        $sys_config = jconf::get();
        $list = array();
        $query = DB::query("SELECT vid,subject
								FROM ".DB::table('vote')."
								WHERE {$where_sql}
								ORDER BY dateline DESC
								LIMIT $start,$perpage ");

		while ($value = DB::fetch($query)) {
			$value['vote_url'] = get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$value['vid']);
			$value['radio_value'] = str_replace(array('"', '\''), '', $value['subject']).' - '.$value['vote_url'];
			$list[] = $value;
		}
        return $list;
    }

    
    public function get_vote_field_message($vid){
        $vid = (int) $vid;
		$r = jtable('vote_field')->val(array('vid'=>$vid),'message');
        return $r;
    }

    public function insert_vote_img($member_id,$image_th_file,$image_file){

        DB::query("insert into `".TABLE_PREFIX."vote_image` (`uid`,`picurl`,`picurl_big`,`dateline`) values
            ('".$member_id."','$image_th_file','$image_file',".time().") ");

        return DB::insert_id();
    }

}

?>