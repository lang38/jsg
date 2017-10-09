<?php
/**
 *
 * 处理微博相关的数据逻辑类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic.logic.php 5628 2014-03-06 06:23:43Z chenxianfeng $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class TopicLogic
{
	
	var $_cache;

	
	var $_len = 280;

	
	var $_len2 = 0;

	
	var $ForwardSeprator;

	var $replaces = array();


	
	
	function TopicLogic() {
		if($GLOBALS['_J']['config']['topic_cut_length'] > 0) {
			$this->_len = $GLOBALS['_J']['config']['topic_cut_length'] * 2;		}
		if($GLOBALS['_J']['config']['topic_input_length'] > 0) {
			$this->_len2 = $GLOBALS['_J']['config']['topic_input_length'] * 2;
		}

		$this->ForwardSeprator = ' /'.'/@';
	}

		
	function Add($datas, $totid = 0, $imageid = 0, $attachid = 0, $from = 'web', $type = "first", $uid = 0, $item = '', $item_id = 0, $from_queue = false)
	{
		if ($GLOBALS['_J']['config']['wqueue_enabled'] && !$from_queue)
		{
			isset($datas['content']) && $datas['content'] = base64_encode($datas['content']);
			$wq_data = array(
				'datas' => $datas,
				'totid' => $totid,
				'imageid' => $imageid,
				'attachid' => $attachid,
				'from' => $from,
				'type' => $type,
				'uid' => $uid ? $uid : (isset($datas['uid']) ? $datas['uid'] : MEMBER_ID),
				'item' => $item ? $item : (isset($datas['item']) ? $datas['item'] : ''),
				'item_id' => $item_id ? $item_id : (isset($datas['item_id']) ? $datas['item_id'] : 0)
			);
			$wq_ds = base64_encode(serialize($wq_data));
			$wq_url = 'http:/'.'/'.$GLOBALS['_J']['config']['wqueue']['host'].'/?name='.$GLOBALS['_J']['config']['wqueue']['name'].'&opt=put&auth='.$GLOBALS['_J']['config']['wqueue']['auth'].'&data='.$wq_ds;
			$wq_r = dfopen($wq_url);
			if (strstr($wq_r, 'HTTPSQS_PUT_OK'))
			{
				return array();
			}
		}
		elseif ($from_queue)
		{

		}
		if(is_array($datas) && count($datas))
		{
						$ks = array(
        		'tid'=>1,
        		'uid'=>1,
			        		'content'=>1,
			        		'imageid'=>1,
				'attachid'=>1,
        		'videoid'=>1,
        		'musicid'=>1,
        		'longtextid'=>1,
									        		'totid'=>1,
        		'touid'=>1,
			        		'dateline'=>1,
			        		'from'=>1,
        		'type'=>1,
        		'item_id'=>1,
        		'item'=>1,

        		'postip'=>1,
        		'timestamp'=>1,
        		'managetype' => 1,
			        		'checkfilter' =>1,
			    'verify' => 1,
							'design' =>1,
        		'xiami_id' => 1,
				#标记有奖转发
				'is_reward' => 1,
			);
			foreach($datas as $k=>$v)
			{
				if(isset($ks[$k]))
				{
					${$k} = $v;
				}
			}
			$pverify = $datas['pverify'];
		}
		else
		{
			$content = $datas;
		}

		
		$is_verify = ($pverify || $GLOBALS['_J']['config']['verify']) ? true : false;
				$content = $this->_content_strip($content);

				$content_length = strlen($content);

		if ($content_length < 2)
		{
			return "内容不允许为空";
		}

				if($this->_len2 > 0 && $content_length > $this->_len2) {
			$content = cut_str($content, $this->_len2, '');
		}

				if(!$checkfilter){
			$f_rets = filter($content);
			if($f_rets)
			{
				if($f_rets['verify']){
					$is_verify = true;
				}elseif($f_rets['error'])
				{
					return $f_rets['msg'];
				}
			}
		} else {
			if($is_verify && 'verify' == $verify && true === IN_JISHIGOU_ADMIN) {
				$is_verify = false;
			}
		}

				$totid = max(0, (int)$totid);
		$data = array();

		if($managetype){
			$data['managetype'] = $managetype;
		}

		$is_new = 1;
		if($tid){
			$is_new = 0;
			$data['tid'] = $tid;
		}
		$parents = '';

		
		$_froms = array(
        	'web' => 1,
        	'wap' => 1,
        	'mobile' => 1,
			'sms' => 1,
        	'qq' => 1,
        	'msn' => 1,
        	'api' => 1,
        	'sina' => 1,
			'qqwb' => 1,
        	'vote'=>1,
        	'qun'=>1,
            'wechat'=>1,
			'fenlei'=>1,
			'event'=>1,
        	'android'=>1,
        	'iphone'=>1,
        	'ipad'=>1,
        	'pad'=>1,
        	'androidpad'=>1,
			'reward' => 1,
		);
		$from = (($from && ($_froms[$from])) ? $from : 'web'); 
				if (empty ($item) || $item_id < 0)
		{
						if (!is_numeric($type)) {
				$_types = array('first' => 1, 'forward' => 1, 'reply' => 1, 'both' => 1);
				
				$type = (($totid < 1 && $type && isset($_types[$type])) ? 'first' :  $type);
				if (empty($type)) {
					$type = 'first';
				}
			}
		}

		$data['from'] = $from; 		if (($type == 'forward' || $type == 'both')  && $item == 'qun') {
			$data['type'] = $item;
		} else {
			$data['type'] = $type; 		}

				if($item == 'channel' && $item_id > 0){
			$channeldata = jlogic('channel')->id2category($item_id);
			if($channeldata){
				if($channeldata['purpostview']){
					$data['type'] = 'channel';
				}
				if($channeldata['topictype']){
					$data['managetype'] = $channeldata['topictype'];
				}
				unset($channeldata);
			}
		}

		$data['uid'] = $uid = max(0, (int)($uid ? $uid : MEMBER_ID));
		$data['videoid'] = $videoid = max(0, (int)$videoid);
		$data['longtextid'] = $longtextid = max(0 , (int) $longtextid);		$timestamp = (int) ($timestamp ? $timestamp : $dateline);
		$data['dateline'] = $data['lastupdate'] = $timestamp = $dateline = ($timestamp > 0 ? $timestamp : TIMESTAMP);
		$data['totid'] = $totid;
		$data['touid'] = $touid;
		$data['anonymous'] = $GLOBALS['_J']['config']['anonymous_enable'] ? $datas['anonymous'] : 0;

				$data['item'] = $item;
		$data['item_id'] = $item_id;

				$member = $this->GetMember($data['uid']);
		if(!$member) {
			return "用户不存在";
		}

		
		if ($item == 'qun' && $item_id > 0)
		{
						$qun_closed = DB::result_first("SELECT closed FROM ".DB::table('qun')." WHERE qid='{$item_id}'");
			if ($qun_closed) {
				return "当前".$GLOBALS['_J']['config']['changeword']['weiqun']."已经关闭，你无法发布内容";
			}
			$r = $this->is_qun_member($item_id, $uid);
			if (!$r) {
				return "你没有权限进行当前操作";
			}
		}

				if($item == 'channel' && $item_id > 0){
			$can_pub_topic = jlogic('channel')->can_pub_topic($item_id);
			if (!$can_pub_topic) {
				return "你没有权限进行当前操作";
			}
		}

				if($item == 'company' && $item_id > 0){
			if($GLOBALS['_J']['config']['company_enable']){
				$my_companyid = $GLOBALS['_J']['member']['companyid'];
				$can_pub_cp_topic = false;
				if($item_id == $my_companyid){
					$can_pub_cp_topic = true;
				}elseif(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $member['companyid']>0){
					$is_my_cpid = jlogic('cp')->is_cp_company($item_id);
					if($is_my_cpid){
						$can_pub_cp_topic = true;
					}
				}
				if (!$can_pub_cp_topic) {
					return "你没有权限进行当前操作";
				}
			}else{
				$item = '';
				$item_id = 0;
				$data['type'] = 'first';
			}
		}

		if($GLOBALS['_J']['config']['add_topic_need_face'] && !$member['__face__']){
    		return "本站需上传头像才可互动。";
    	}

				$MemberHandler = & Obj::registry('MemberHandler');
		if($MemberHandler) {
			if(!in_array($type, array('both', 'reply', 'forward'))) { 				if(!($MemberHandler->HasPermission('topic','add',0,$member))) {
					if(true!==IN_JISHIGOU_SMS) {
						return ($MemberHandler->GetError());
					}
				}
			} else {
				if(('reply'==$type || 'both'==$type) && !($MemberHandler->HasPermission('topic','reply',0,$member))) {
					return ($MemberHandler->GetError());
				} elseif(('forward'==$type || 'both'==$type) && !($MemberHandler->HasPermission('topic','forward',0,$member))) {
					return ($MemberHandler->GetError());
				}
			}
		}


				if(MEMBER_ROLE_TYPE != 'admin'){
			if($GLOBALS['_J']['config']['topic_vip'] == 1){
				if(!$member['validate']){
					return "非V认证用户无法发布信息";
				}
			}elseif($GLOBALS['_J']['config']['topic_vip'] == 2){
				$to_verify = 1;
				if(!$member['validate']){
					$f_rets['vip'] = 1;
					$f_rets['msg'] = '非V认证用户发言内容进入<a href="index.php?mod='.$member['uid'].'&type=my_verify" target="_blank">待审核</a>,
									<a href="'.$GLOBALS['_J']['config']['site_url'].'/index.php?mod=other&code=vip_intro" target="_blank">点击申请认证</a>';
					$is_verify = true;
				}
			}
		}
		$data['username'] = $username = $member['username'];


		$topic_content_id = abs(crc32(md5($content)));

										if(!$verify){
			if($GLOBALS['_J']['config']['lastpost_time']>0 && !in_array($data['from'], array('sina', 'qqwb',  )) && (($timestamp - $member['lastpost']) < $GLOBALS['_J']['config']['lastpost_time'])) {
				return "您发布的太快了，请在<b>{$GLOBALS['_J']['config']['lastpost_time']}</b>秒后再发布";
			}
		}
				

		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			if (false != $deny = nlogic('feeds.app.jsg')->topic_publish_denied($data))
			{
				return $deny;
			}
		}
		#endif

				if($imageid) {
			if($verify){
				$data['imageid'] = $imageid;
			}else{
				$data['imageid'] = $imageid = jlogic('image')->get_ids($imageid, $data['uid']);
			}
		}
				if($attachid)
		{
			if($verify){
				$data['attachid'] = $attachid;
			}else{
				$data['attachid'] = $attachid = jlogic('attach')->get_ids($attachid, $data['uid']);
			}
		}

		$data['musicid'] = $musicid;

				if($xiami_id > 0) {
			$musicid = $data['musicid'] = jtable('topic_music')->insert(array(
				'uid' => $data['uid'],
				'username' => $data['username'],
				'dateline' => $timestamp,
				'xiami_id' => $xiami_id,
			), true);
		}


				$topic_more = array();
		$parents = '';
		$data['roottid'] = 0;
		if ($totid > 0) {
						$content = $this->GetForwardContent($content);

			$_type_names = array('both'=>'转发和评论', 'forward'=>'转发', 'reply'=>'评论');
			$_type_name = $_type_names[$type];

			$to_topic = $row = $this->Get($totid);
			if (!($to_topic)) {
				return "对不起,由于原微博已删除,不能{$_type_name}";
			}
						if(('reply' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_reply', $row['uid'], $data['uid']))) {
				return $rets['error'];
			} elseif (('forward' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_forward', $row['uid'], $data['uid']))) {
				return $rets['error'];
			}
			$topic_more = $this->GetMore($totid);

			$data['totid'] = $row['tid'];
			$data['touid'] = $row['uid'];
			$data['tousername'] = $row['nickname'];
			$parents = ($topic_more['parents'] ? ($topic_more['parents'] . ',' . $totid) : $totid);
			$data['roottid'] = ($topic_more['parents'] ? substr($parents, 0, strpos($parents,
                ',')) : $totid);

			$root_topic = $this->Get($data['roottid']);
						if ($root_topic['item'] == 'qun' && $root_topic['item_id'] > 0) {
								$qun_closed = DB::result_first("SELECT closed FROM ".DB::table('qun')." WHERE qid='{$root_topic['item_id']}'");
				if ($qun_closed) {
					return "当前".$GLOBALS['_J']['config'][changeword][weiqun]."已经关闭，你无法发布内容";
				}
			}

			if($data['totid']!=$data['roottid']) {
				$rrow = $this->Get($data['roottid']);
				if(!$rrow) {
					return "对不起,由于原始微博已删除,不能{$_type_name}";
				}

								if(('reply' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_reply', $rrow['uid'], $data['uid']))) {
					return $rets['error'];
				} elseif (('forward' == $type || 'both' == $type) && ($rets = jsg_role_check_allow('topic_forward', $rrow['uid'], $data['uid']))) {
					return $rets['error'];
				}

								if(('forward'==$type || 'both'==$type)) {
                    					$content .= $this->ForwardSeprator . "{$row['nickname']} : " . addslashes($this->_content_strip($row['raw_content']));
				}
			}
		}

		$_process_result = $this->_process_content($content, $data);

		$longtext = $_content = $_process_result['content'];
		
		$at_uids = $_process_result['at_uids'];

		$tags = $_process_result['tags'];

		$urls = $_process_result['urls'];

				unset($data['longtextid']);
		if(jstrlen($_content) > $this->_len) {
						$_content = cut_str($_content, $this->_len, '');
			$_content = $this->_content_end($_content);
			if(strlen($longtext) > strlen($_content)) {
				$longtextid = 0;
				if($is_verify) {
										$longtextid = jlogic('longtext')->Add($longtext, $data['uid']);
				}
				$longtextid = ($longtextid > 0 ? $longtextid : TIMESTAMP);
				$data['longtextid'] = $longtextid;
			}
		}

				if(!$GLOBALS['_J']['config']['clear_format_open']){
			$_content = $this->clearFormat($_content);
		}else{
									$_content = preg_replace('/\n{3,}/','\n\n',$_content);
			$_content = nl2br($_content);
		}
		if (strlen($_content) > 255) {
			$_content = cut_str($_content, 254 * 2, '');

			$data['content'] = cut_str($_content, 255, '');

			$data['content2'] = substr($_content, strlen($data['content']));
		} else {
			$data['content'] = $_content;
		}

		$data['postip'] = $postip ? $postip : $GLOBALS['_J']['client_ip'];
		$data['post_ip_port'] = $GLOBALS['_J']['client_ip_port'];
		
				if($is_verify){
			$sql = "insert into `" . TABLE_PREFIX . "topic_verify` (`" . implode("`,`", array_keys
			($data)) . "`) values ('" . implode("','", $data) . "')";
			DB::query($sql);
			$topic_id = $data['tid'] = $tid = DB::insert_id();

						if ($imageid)
			{
				DB::query("update ".TABLE_PREFIX."topic_image set `tid`='-1' where `id` in ($imageid)");
			}
						if ($attachid)
			{
				DB::query("update ".TABLE_PREFIX."topic_attach set `tid`='-1' where `id` in ($attachid)");
			}

						if($urls)
			{
				$date = $data;
				$date['id'] = $data['tid'];
				$date['tid'] = -1;
				$this->_process_urls($date,$urls,false,'topic_verify');
			}
			if($notice_to_admin = $GLOBALS['_J']['config']['notice_to_admin']){
				$pm_post = array(
					'message' => $member['nickname']."有一条微博进入待审核状态，<a href='admin.php?jump_url=admin.php?mod=topic&code=verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$notice_to_admin),
				);
								$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
				load::logic('pm');
				$PmLogic = new PmLogic();
				$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
			if($f_rets['verify'] || $f_rets['vip']){
				return array($f_rets['msg']);
			}
		}else{
			$tid = jtable('topic')->insert($data, true);
			if ($tid < 1) {
				return "未知的错误";
			}
			$topic_id = $data['tid'] = $tid;

						if(is_array($datas) && isset($datas['relateid'])) {
				$relateid = $datas['relateid'] ? $datas['relateid'] : 0;
				$featureid = $datas['featureid'] ? $datas['featureid'] : 0;
				if($relateid){
					DB::query("update `".TABLE_PREFIX."topic` set `relateid`='{$tid}',`featureid`='{$featureid}' where `tid`='{$relateid}'");
					$pmtoinfo = jlogic('topic')->Get($relateid,'`uid`,`item_id`,`relateid`,`featureid`','');
					$ch_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($pmtoinfo['item_id']);
					$msg = '您提出的';
					$msg .= $ch_typeinfo['channel_type'] == 'ask' ? '问题' : '建议';
					$msg .= $pmtoinfo['relateid'] == 0 ? '，已经有了答复' : '重新给予了答复';
					if($featureid != $pmtoinfo['featureid']){
						$msg .= '，状态变更为“'.($ch_typeinfo['feature'][$featureid] ? $ch_typeinfo['feature'][$featureid] : '等待处理').'”';
					}
					postpmsms($pmtoinfo['uid'],$relateid,$msg);
				}
			}

			if($is_new) {
								if (!empty($item) && $item_id > 0 && !($design == 'design' || $design == 'btn_wyfx')) {					jfunc('app');
					$param = array(
						'item' => $item,
						'item_id' => $item_id,
						'tid' => $tid,
						'uid' => $data['uid'],
					);
					if($item == 'talk'){
						$param['touid'] = $touid;
						$param['totid'] = $totid;
					}
					app_add_relation($param);
					unset($param);
				}

								jtable('topic_more')->add($tid, $parents, $longtext);
			}

						jtable('member_topic')->add($tid);

						if($parents && 'first' != $data['type']) {
				jtable('topic_relation')->add($tid, $parents);
			}

						$p = array(
				'uid' => $data['uid'],
				'lastactivity' => $data['lastupdate'],
				'lastpost' => $data['lastupdate'],
				'last_topic_content_id' => $topic_content_id,
			);
			if('reply' != $data['type']) {
				$p['+@topic_count'] = 1;
			}
			jtable('members')->update($p);

						if ($at_uids) {
				$this->_process_at_uids($data, $at_uids);
								ios_push_msg($at_uids,'你有新消息:1条@我');
			}
						if($totid > 0 && $parents) {
				$this->_process_reply($data);
								ios_push_msg($data['touid'],'你有新消息:1条评论');
			}
						if($urls) {
				$this->_process_urls($data, $urls);
			}

						if ($imageid) {
				jlogic('image')->set_tid($imageid, $tid);
			}
						if ($attachid) {
				jlogic('attach')->set_tid($attachid, $tid);
			}
						if($musicid) {
				$sql = "update `".TABLE_PREFIX."topic_music` set `tid` = '{$tid}' where `id` = '$musicid' ";
				DB::query($sql);
			}
						if ($data['videoid'] > 0) {
				$sql = "update `" . TABLE_PREFIX . "topic_video` set `tid`='{$tid}' where `id`='{$data['videoid']}'";
				DB::query($sql);
			}

			#有奖转发判断
			if($is_reward) {
				$allowed_reward = 1;
				$reward_info = jlogic('reward')->getRewardInfo($is_reward);
				if($reward_info['rules']){
					foreach ($reward_info['rules'] as $key=>$val) {
						if($allowed_reward == 0){
							break;
						}
						switch ($key) {
							case 'at_num':
								if($val > count($at_uids)){
									$allowed_reward = 0;
								}
								break;
							case 'user':
								$my_buddyids = get_buddyids($data['uid']);
								if(!$my_buddyids){
									$allowed_reward = 0;
									break;
								}
								foreach ($val as $re_uid => $re_name) {
									if($re_uid == $data['uid']){continue;}
									if(!in_array($re_uid,$my_buddyids)){
										$allowed_reward = 0;
										break;
									}
								}
								break;
							case 'tag':
								foreach ($val as $re_tag) {
									if(!$tags){
										$allowed_reward = 0;
										break;
									}
									if(!in_array($re_tag,$tags)){
										$allowed_reward = 0;
										break;
									}
								}
								break;
							default:
								break;
						}
					}
				}
				#超时转发也不可进入有奖转发名单
				if(TIMESTAMP > $reward_info['tot']){
					$allowed_reward = 0;
				}

				#记录有奖转发
									DB::query("insert into `".TABLE_PREFIX."reward_user` (`uid`,`tid`,`rid`,`on`,`dateline`) values('$data[uid]','$tid','$is_reward','$allowed_reward','".TIMESTAMP."')");
				
				DB::query("update `".TABLE_PREFIX."reward` set `f_num` = `f_num`+1,`a_num`=`a_num`+$allowed_reward where `id` = '$is_reward' ");
			}

						if ($item == 'qun' && ($data['type'] == 'qun' || $data['type'] == 'first')) {
				if (!empty($item_id)) {
					$query = DB::query("SELECT uid FROM ".DB::table('qun_user')." WHERE qid='{$item_id}'");
					$uids = array();
					while ($value=DB::fetch($query)) {
						if ($value['uid'] != $uid) {
							$uids[$value['uid']] = $value['uid'];
						}
					}

					if (!empty($uids)) {
						DB::query("UPDATE ".DB::table('members')."
	        					   SET qun_new=qun_new+1
	        					   WHERE uid IN(".jimplode($uids).")");
					}
				}
			}

						if ($item == 'channel' && $item_id > 0 && ($data['type'] == 'first' || $data['type'] == 'channel')) {
				if (!empty($item_id)) {
					$query = DB::query("SELECT uid FROM ".DB::table('buddy_channel')." WHERE ch_id='{$item_id}'");
					$uids = array();
					while ($value=DB::fetch($query)) {
						if ($value['uid'] != $uid) {
							$uids[$value['uid']] = $value['uid'];
						}
					}

					if (!empty($uids)) {
						DB::query("UPDATE ".DB::table('members')."
	        					   SET channel_new=channel_new+1
	        					   WHERE uid IN(".jimplode($uids).")");
					}
				}
								if ($GLOBALS['_J']['config']['extcredits_enable'] && $data['uid'] > 0){
					$credits_itemid = jlogic('channel')->is_update_credits_byid($item_id);
					if($credits_itemid){
						update_credits_by_action(('_C' . crc32($credits_itemid)), $data['uid']);
					}
				}
			}

						if ($item == 'company' && $item_id > 0 && $data['type'] == 'company') {
				$query = DB::query("SELECT uid FROM ".DB::table('members')." WHERE companyid='{$item_id}'");
				$uids = array();
				while ($value=DB::fetch($query)) {
					if ($value['uid'] != $uid) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
				$query = DB::query("SELECT uid FROM ".DB::table('cp_user')." WHERE companyid='{$item_id}'");
				while ($value=DB::fetch($query)) {
					if ($value['uid'] != $uid) {
						$uids[$value['uid']] = $value['uid'];
					}
				}
				if (!empty($uids)) {
					DB::query("UPDATE ".DB::table('members')." SET company_new=company_new+1 WHERE uid IN(".jimplode($uids).")");
				}
			}

						$update_credits = false;

			if ($tags)
			{
				Load::logic('tag');
				$TagLogic = new TagLogic('topic');
				$TagLogic->Add(array('item_id' => $tid, 'tag' => $tags, ), false);

				if ($GLOBALS['_J']['config']['extcredits_enable'] && $data['uid'] > 0)
				{
					
					if (is_array($tags) && count($tags)){
												if($GLOBALS['_J']['config']['sign']['sign_enable'] && jtable('sign_tag')->is_sign_tag($tags)){
							$sign_credits = update_credits_by_action('_S', $data['uid']);
						}

						if(!$sign_credits['updatecredit']){
							foreach ($tags as $_t)
							{
								if ($_t)
								{
									$update_credits = (update_credits_by_action(('_T' . crc32($_t)), $data['uid']) ||
									$update_credits);
								}
							}
						}
					}
				}

												jlogic('tag_favorite')->topic_new($tags, $data['uid']);
			}

						if ($GLOBALS['_J']['config']['extcredits_enable'])
			{
				if (!$update_credits && !$sign_credits && $data['uid'] > 0)
				{
					if ($totid > 0)
					{
						
						update_credits_by_action('reply', $data['uid']);
					}
					else
					{
						
						update_credits_by_action('topic', $data['uid']);
					}
				}
			}


						if ($GLOBALS['_J']['config']['imjiqiren_enable'] && imjiqiren_init())
			{
				$to_admin_robot = jconf::get('imjiqiren', 'admin_qq_robots');
				if ($to_admin_robot)
				{
					imjiqiren_send_message($to_admin_robot, 'to_admin_robot', array('site_url' => $GLOBALS['_J']['config']['site_url'], 'username' => $data['username'], 'content' => $data['content'],
	                    'topic_id' => $topic_id));
				}
			}


						if ($GLOBALS['_J']['config']['sms_enable'] && sms_init())
			{
				$to_admin_mobile = jconf::get('sms', 'admin_mobile');
				if ($to_admin_mobile)
				{
					sms_send_message($to_admin_mobile, 'to_admin_mobile', array('site_url' => $GLOBALS['_J']['config']['site_url'], 'username' => $data['username'], 'content' => $data['content'],
	                    'topic_id' => $topic_id));
				}
			}

						if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $GLOBALS['_J']['config']['company_enable'] && $member['companyid']>0){
				$CpLogic = jlogic('cp');
				$update_companyid = $member['companyid'];
				$update_departmentid = $member['departmentid'];
				if($item = 'company' && $item_id > 0 && $update_companyid != $item_id){					$cp_company_info = $CpLogic->get_cp_row_bycompany($item_id);
					if($cp_company_info){
						$update_companyid = $member['companyid'];
						$update_departmentid = $member['departmentid'];
					}
				}
				$CpLogic->update('company',$update_companyid,0,1);
				if($update_departmentid > 0){
					$CpLogic->update('department',$update_departmentid,0,1);
				}
			}

						$feed_action = '';
			if(in_array($data['type'],array('first','reply','forward','both'))){
				$feed_action = $data['type'];
				if($feed_action=='first'){
					$feed_action = 'post';
				}elseif($feed_action=='both'){
					$feed_action = 'reply';
				}
			}
			if($feed_action){
				$feed_msg = cut_str($data['content'], 30, '');
				feed_msg('channel',$feed_action,$tid,$feed_msg,$item_id,$data['anonymous']);
			}

			$this->_syn_to($data);

		}

		if($GLOBALS['_J']['plugins']['func']['posttopic']) {
			hookscript('posttopic', 'funcs', array('param' => array($data['tid']), 'step' => 'post'), 'posttopic');
		}

				if('reply' != $data['type']) {
			cache_db('rm', "{$data['uid']}-topic-%", 1);

						jtable('topic')->archive($data['tid']);
		}

		$this->cache_rm($data['tid']);

		#if NEDU
		defined('NEDU_MOYO') && nfevent('jsg.logic.topic.add', null, $data);
		#endif

		return $data;
	}

	
	function Modify($tid,$content,$imageid=0,$attachid=0,$table="",$itemid=0,$featureid=0)
	{
		$updatas = array();

		if(MEMBER_ID < 1) {
			return '游客不能执行此操作';
		}

		$tid = max(0, (int) $tid);
		if($tid < 1)
		{
			return "微博ID错误";
		}

		$topic_info = $this->get($tid,'*','Make',$table,'tid');
		if(!$topic_info)
		{
			return "微博已经不存在了";
		}
		if($topic_info['item'] == 'channel' && $topic_info['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topic_info = item_topic_from($topic_info);
		}

		if(!(jallow($topic_info['uid']) || $topic_info['ismanager'])){
			return '您没有权限进行编辑操作';
		}

		$content = $this->_content_strip($content);
		$content_length = strlen($content);
		if($content_length < 2)
		{
			return "微博内容不能为空";
		}

		if($this->_len2 > 0 && $content_length > $this->_len2) {
			$content = cut_str($content, $this->_len2, '');
		}

		$f_rets = filter($content);
		if($f_rets)
		{
			if($f_rets['error'])
			{
				return $f_rets['msg'];
			}
		}

				if($topic_info['totid'] > 0 && $topic_info['totid']!=$topic_info['roottid'])
		{
						$content = $this->GetForwardContent($content);

			$row = $this->Get($topic_info['totid'],'*','Make',$table);

			if($row && ('forward'==$topic_info['type'] || 'both'==$topic_info['type']))
			{
				$content .= $this->ForwardSeprator . "{$row['nickname']} : " . addslashes($this->_content_strip($row['content']));

				if(strlen($content) > $this->_len)
				{
									}
			}
		}


				if($imageid != $topic_info['imageid']) {
			if($imageid) {
				$imageid = jlogic('image')->get_ids($imageid, $topic_info['uid']);

				if($imageid) {
					jlogic('image')->set_tid($imageid, $tid);
				}
			}

						jlogic('image')->set_topic_imageid($tid, $imageid);
		}
				if($attachid != $topic_info['attachid'])
		{
			if($attachid)
			{
				$attachid = jlogic('attach')->get_ids($attachid, $topic_info['uid']);

				if($attachid)
				{
					jlogic('attach')->set_tid($attachid, $tid);
				}
			}

						jlogic('attach')->set_topic_attachid($tid);
		}



		
		$_process_result = $this->_process_content($content, $topic_info);

		$_content = $_process_result['content'];

		$at_uids = $_process_result['at_uids'];

		$tags = $_process_result['tags'];

		$urls = $_process_result['urls'];

				$updatas['longtextid'] = 0;
		$longtextid = jlogic('longtext')->modify($topic_info['tid'], $_content);
		if(jstrlen($_content) > $this->_len) {
						$_content = cut_str($_content, $this->_len, '');
			$_content = $this->_content_end($_content);
			if(strlen($_process_result['content']) > strlen($_content)) {
				$updatas['longtextid'] = $longtextid;
			}
		}

        		if(!$GLOBALS['_J']['config']['clear_format_open']){
			$_content = $this->clearFormat($_content);
		}else{
						$_content = preg_replace('/(\n\n)+/','\n',$_content);
			$_content = nl2br($_content);

		}
		if (strlen($_content) > 255) {
			$_content = cut_str($_content, 254 * 2, '');

			$content1 = cut_str($_content, 255, '');

			$content2 = substr($_content, strlen($content1));

			$updatas['content'] = $content1;

			$updatas['content2'] = $content2;
		} else {
			$updatas['content'] = $_content;
			$updatas['content2'] = '';
		}
				if($topic_info['item'] == 'channel' && $topic_info['item_id'] > 0 && $itemid == 0){			jfunc('app');
			app_delete_relation($topic_info['item'],$topic_info['item_id'],$tid);
			$updatas['item'] = '';
			$updatas['item_id'] = 0;
						if($GLOBALS['_J']['config']['extcredits_enable'] && $topic_info['uid'] > 0 && ($topic_info['type'] == 'first' || $topic_info['type'] == 'channel')){
				$credits_itemid = jlogic('channel')->is_update_credits_byid($topic_info['item_id'],0);
				if($credits_itemid){
					update_credits_by_action(('_D' . crc32($credits_itemid)), $topic_info['uid']);
				}
			}
		}elseif($itemid > 0 && (($topic_info['item'] == 'channel' && $topic_info['item_id'] > 0) || $topic_info['item'] == 'api' || $topic_info['item'] == '') && ($itemid != $topic_info['item_id'])){
			$true_channel_id = jlogic('channel')->is_exists($itemid);
			$can_pub_topic = jlogic('channel')->can_pub_topic($itemid);
			if($true_channel_id && $can_pub_topic){
				jfunc('app');
				$param = array(
					'tid' => $tid,
					'uid' => $topic_info['uid'],
					'old_itemid' => $topic_info['item_id'],
					'itemid' => $itemid,
				);
				app_mod_relation($param);
				unset($param);
				$updatas['item'] = 'channel';
				$updatas['item_id'] = $itemid;
								$to_info = jlogic('channel')->id2category($itemid);
				if($topic_info['item'] == ''){
					if($to_info['purpostview']){
						$updatas['type'] = 'channel';
					}
				}else{
					if($topic_info['type'] == 'channel' && $to_info['purpostview'] == ''){
						$updatas['type'] = 'first';
					}
				}
								if($topic_info['managetype'] != $to_info['topictype']){
					$updatas['managetype'] = $to_info['topictype'];
				}
								if ($GLOBALS['_J']['config']['extcredits_enable'] && $topic_info['uid'] > 0 && ($topic_info['type'] == 'first' || $topic_info['type'] == 'channel')){
					if($topic_info['item'] == 'channel' && $topic_info['item_id'] > 0){						$credits_itemid = jlogic('channel')->is_update_credits_byid($topic_info['item_id'],0);
						if($credits_itemid){
							update_credits_by_action(('_D' . crc32($credits_itemid)), $topic_info['uid']);
						}
					}
										$credits_itemid = jlogic('channel')->is_update_credits_byid($itemid);
					if($credits_itemid){
						update_credits_by_action(('_C' . crc32($credits_itemid)), $topic_info['uid']);
					}
				}
			}
		}
		$updatas['featureid'] = $featureid;
		if($topic_info['featureid'] != $featureid && $topic_info['item'] == 'channel' && $topic_info['item_id'] > 0){
			$ch_typeinfo = jlogic('channel')->get_channel_typeinfo_byid($topic_info['item_id']);
			if(in_array($ch_typeinfo['channel_type'],array('ask','idea'))){
				$msg = '您提出的';
				$msg .= $ch_typeinfo['channel_type'] == 'ask' ? '问题' : '建议';
				$msg .= '，状态变更为“'.($ch_typeinfo['feature'][$featureid] ? $ch_typeinfo['feature'][$featureid] : '等待处理').'”';
			}else{
				$msg = '您发布的微博';
				if($featureid > 0){
					$msg .= '，状态变更为“'.jlogic('feature')->id2subject($featureid).'”';
				}else{
					$msg .= '，取消了其属性状态';
				}
			}
			postpmsms($topic_info['uid'],$tid,$msg);
		}

				$updatas['lastupdate'] = TIMESTAMP;

		jtable('topic')->update($updatas, $tid);

		$verify_pub_topic = jlogic('channel')->verify_pub_topic($itemid);
		if($verify_pub_topic){
			$this->DeleteToBox($tid,0);
		}

		if($at_uids)
		{
			$this->_process_at_uids($topic_info, $at_uids);
		}


		if($tags)
		{
			Load::logic('tag');
			$TagLogic = new TagLogic('topic');

			$tags_old = array();

			if (false !== strpos($topic_info['content'], '#'))
			{
				preg_match_all('~<T>#(.+?)#</T>~', $topic_info['content'], $subpatterns);
				if ($subpatterns && is_array($subpatterns[1]))
				{
					$tags_old = $subpatterns['1'];
				}
			}

			$TagLogic->Modify(array('item_id' => $tid, 'tag' => $tags), $tags_old);
		}


				if($urls)
		{
			$this->_process_urls($topic_info,$urls,true);
		}


		return $topic_info;
	}

	
	function DeleteToBox($ids,$managetype=1, $score=0){
		if(MEMBER_ID < 1) {
			return '游客不能执行此操作';
		}
		if(!function_exists('item_topic_from')) {
			jfunc('item');
		}
		if(is_numeric($ids)){
			$where = " where tid = '$ids' ";
		}elseif(is_array($ids)){
			$where = " where tid in ('".implode("'.'",$ids)."') ";
		}elseif(is_string($ids)){
			$where = $ids;
		} else {
			return '所指定的微博有误。';
		}
        if(!$ids){
            return '微博已经不存在了';
        }

				$tbs = array(
        	'topic_recommend' => 'tid',		);

		$query = DB::query("select * from ".TABLE_PREFIX."topic $where ");
		$topics = array();
		while ($rs = DB::fetch($query)){
			if($rs['item'] == 'channel' && $rs['item_id'] > 0){
				$rs = item_topic_from($rs);
			}
			if(!(jallow($rs['uid']) || $rs['ismanager'])){
				return '您没有权限执行此操作';
			}
			$topics[$rs['tid']] = $rs;
		}
		if(count($topics) < 1) {
			return '微博已经不存在了.';
		}

		foreach ($topics as $value) {
			$tid = $value['tid'] = (int) $value['tid'];
						if($value['tid'] < 1) {
				continue;
			}
			jtable('topic')->rm($value['tid']);

			$value['managetype'] = $managetype;
			$value['content'] = addslashes($value['content']);
			$value['content2'] = addslashes($value['content2']);
			jtable('topic_verify')->insert($value);
						if ($value['imageid'])
			{
				DB::query("update ".TABLE_PREFIX."topic_image set `tid`='-1' where `id` in ($value[imageid])");
			}

						if ($value['attachid'])
			{
				DB::query("update ".TABLE_PREFIX."topic_attach set `tid`='-1' where `id` in ($value[attachid])");
			}

						if ($GLOBALS['_J']['config']['extcredits_enable'] && $value['uid'] > 0){
				
				
				if ($value['uid'] == MEMBER_ID) {
					update_credits_by_action('topic_del', $value['uid']);
				}else{
					$credit_logic = jlogic('credits');
					$rule = $credit_logic->GetRule('topic_del');
					$credit_logic->UpdateCreditsByRule($rule, $value['uid'], 1, $score);
					
					$data = array('uid' => $value['uid'], 'rid'=>0, 'relatedid'=>MEMBER_ID, 'dateline'=>time(),'remark'=>"删除微博 【微博ID:{$tid}】");
					foreach ($GLOBALS['_J']['config']['credits']['ext'] as $key => $value) {
						if ($value['enable'] == 1 && $score != 0) {
							$data[$key] = $score;
						}

						if ($value['enable'] == 1 && $score == 0) {
							$data[$key] = $rule[$key];
						}
					}
					jtable('credits_log')->insert($data);
				}


								if($value['item'] == 'channel' && $value['item_id'] > 0 && ($value['type'] == 'first' || $value['type'] == 'channel')){
					$credits_itemid = jlogic('channel')->is_update_credits_byid($value['item_id'],0);
					if($credits_itemid){
						update_credits_by_action(('_D' . crc32($credits_itemid)), $value['uid']);
					}
				}
			}


						if($tbs) {
				foreach($tbs as $k=>$vs) {
					$vs = (array) $vs;
					foreach($vs as $v) {
						DB::query("delete from `".TABLE_PREFIX."{$k}` where `{$v}`='{$tid}'", "SKIP_ERROR");
					}
				}
			}
		}
	}

	
	function Delete($ids)
	{
		if(MEMBER_ID < 1) {
			return '游客不能执行此操作';
		}
		if(is_numeric($ids)){
			$where = " where `id` = '$ids' ";
		}else if(is_string($ids)){
			$where = $ids;
		}else if(is_array($ids)){
			$where = " where `id` in ('".implode("','",$ids)."') ";
		} else {
			return '所指定的微博有误。';
		}
        if(!$ids){
            return '微博已经不存在了';
        }
		$query = DB::query("select * from ".TABLE_PREFIX."topic_verify $where ");
		$topics = array();
		while ($rs = DB::fetch($query)) {
			if(jdisallow($rs['uid'])) {
				return '您没有权限执行此操作';
			}
			$topics[] = $rs;
		}
		if(count($topics) < 1){
			return '微博已经不存在了';
		}

				$tbs = array(
        	        	'report' => 'tid',
        	'sms_receive_log' => 'tid',

        	'topic_favorite' => 'tid',
        				        	        	'topic_longtext' => 'tid',
        	'topic_mention' => 'tid',
        	'topic_more' => 'tid',
        	        	'topic_qun' => 'tid',
        	'topic_reply' => array('tid', 'replyid'),
        	'topic_tag' => 'item_id',
        	'topic_url' => 'tid',
        	        	'topic_vote' => 'tid',
        	'wall_draft' => 'tid',
        	'wall_playlist' => 'tid',
        	        	'topic_recommend' => 'tid',
			'topic_live' => 'tid',			'topic_talk' => 'tid',			'topic_channel' => 'tid',			'topic_dig' => 'tid',			'topic_topic_image' => 'tid',		);
		$topictids = array();
		foreach ($topics as $topic)
		{
			$topictids[] = $topic['tid'];
			if (false !== strpos($topic['content'], '#'))
			{
				preg_match_all('~<T>#(.+?)#</T>~', $topic['content'], $subpatterns);
				if ($subpatterns && is_array($subpatterns[1]))
				{
					Load::logic('tag');
					$TagLogic = new TagLogic('topic');

					$TagLogic->Delete(array('item_id' => $topic['tid'], 'tag' => $subpatterns['1'], ));
				}
			}
						if ($topic['imageid']) {
							}
						if ($topic['attachid'])
			{
				jlogic('attach')->delete($topic['attachid']);
			}
			if ($topic['videoid'])
			{
								$sql = "select `id`,`video_img` from `" . TABLE_PREFIX .
                    "topic_video` where `id`='" . $topic['videoid'] . "' ";
				$topic_video = DB::fetch_first($sql);

				jio()->DeleteFile($topic_video['video_img']);

				DB::query("delete from `".TABLE_PREFIX."topic_video` where `id` = '{$topic['videoid']}'");
			}
			#音乐
			if($topic['musicid']){
				DB::query("delete from `".TABLE_PREFIX."topic_music` where `id` = '{$topic['musicid']}'");
			}

			$tid = $topic['tid'];
			if($tid > 0){
								if (!empty($topic['item']) &&  $topic['item_id'] > 0) {
					jfunc('app');
					app_delete_relation($topic['item'], $topic['item_id'], $topic['tid']);
				}
								foreach($tbs as $k=>$vs)
				{
					$vs = (array) $vs;

					foreach($vs as $v)
					{
						DB::query("delete from `".TABLE_PREFIX."{$k}` where `{$v}`='{$tid}'", "SKIP_ERROR");
					}
				}
			}

			#删除审核表里的数据
			DB::query("delete from `".TABLE_PREFIX."topic_verify` where `id` = {$topic['id']}");

						if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $GLOBALS['_J']['config']['company_enable']){
				$cpstring = DB::fetch_first("SELECT companyid,departmentid FROM ".DB::table('members')." WHERE uid = '".$topic['uid']."'");
				if($cpstring['companyid']>0 || $cpstring['departmentid']>0){
					$CpLogic = jlogic('cp');
					if($cpstring['companyid']>0){
						$CpLogic->update('company',$cpstring['companyid'],0,-1);
					}
					if($cpstring['departmentid']>0){
						$CpLogic->update('department',$cpstring['departmentid'],0,-1);
					}
				}
			}
		}
		if($GLOBALS['_J']['plugins']['func']['deletetopic']) {
			hookscript('deletetopic', 'funcs', (is_array($topictids) ? $topictids : array($topictids)), 'deletetopic');
		}
		return '';
	}

	
	function get_by_ids($rets) {
		if($rets && is_array($rets) && !isset($rets['list']) && is_array($rets['ids'])) {
			$rets['list'] = ($rets['ids'] ? $this->Get($rets['ids']) : array());
			if($rets['list']) {
				if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
	                if(true === IN_JISHIGOU_WAP) {
	                      $rets['list'] = buddy_follow_html($rets['list'], 'uid', 'wap_follow_html');
	                } else {
	                      $rets['list'] = jlogic('buddy')->follow_html2($rets['list']);
	                }
				}
				$rets['parent_list'] = $this->get_parent_list($rets['list']);
			}
			if(true === IN_JISHIGOU_WAP) {
				$rets = wap_iconv($rets);
			}
		}
		return $rets;
	}

	
	function Get($ids, $fields = '*', $process = 'Make', $table = "", $prikey = 'tid', $cache=0) {
		$table = ($table ? $table : TABLE_PREFIX . "topic");

		if($cache) {
			$cache_key = md5($fields . $process . $table . $prikey);
		}

		$condition = "";
		$ids_count = 0;
		$is_num = is_numeric($ids);
		if ($is_num) {
			if ($cache && isset($this->_cache[$cache_key][$ids])) {
				return $this->_cache[$cache_key][$ids];
			}
			$condition = "WHERE `{$prikey}`='{$ids}'";
		} elseif (is_array($ids)) {
			$ids_count = count($ids);
			$condition = "WHERE `{$prikey}` IN ('" . implode("','", $ids) . "')";
		} elseif (is_string($ids) && false !== strpos(strtolower($ids), ' limit ')) {
			$condition = $ids;
		} else {
			return false;
		}


		$sql = "SELECT {$fields} FROM {$table} {$condition} ";

		$query = DB::query($sql);

		$list = array();
		if (!$query || ($num_rows = DB::num_rows($query)) < 1 || ($ids_count > 0 && $num_rows != $ids_count)) {
			if(TABLE_PREFIX . 'topic' == $table) {
				if($is_num) {
					$is_one = 1;
					$list = jtable('topic')->row($ids); 					if($list && $process) {
						$list = $this->$process($list);
					}
				} elseif($ids_count > 0) { 					$list = jtable('topic')->get_list($ids);
				}
			}
			if(!$list) {
				return false;
			}
		} else {
			$is_one = (($is_num && $num_rows < 2) ? 1 : 0);
			$pri_key_is_set = 1;
			while (false != ($row = DB::fetch($query))) {
				if ($process && ('Make'!=$process || $is_one)) {
					$row = (($cache && isset($this->_cache[$cache_key][$row[$prikey]])) ? $this->_cache[$cache_key][$row[$prikey]] :
					$this->$process($row));
				}
				if ($cache && isset($row[$prikey]) && !isset($this->_cache[$cache_key][$row[$prikey]])) {
					$this->_cache[$cache_key][$row[$prikey]] = $row;
				}

				if ($is_one) {
					$list = $row;
					break;
				} else {
					if(isset($row[$prikey])) {
						$pri_key_is_set = 1;
						$list[$row[$prikey]] = $row;
					} else {
						$list[] = $row;
					}
				}
			}
			DB::free_result($query);
			if($ids_count > 0 && $list && $pri_key_is_set) {
				$_list = array();
				foreach($ids as $_id) {
					$_list[$_id] = $list[$_id];
				}
				$list = $_list;
				unset($_list);
			}
		}
		if('Make'==$process && !$is_one) {
			$verify = $table == TABLE_PREFIX.'topic_verify' ? 1 : 0;
			$list = $this->MakeAll($list,1,$verify);
		}
		
		return $list;
	}

	
	function MakeAll($list, $make_row=1, $verify=0) {
		if(!$list) {
			return array();
		}

		$tids = array();
		$uids = array();
		$videoids = array();
		$musicids = array();
        $rewardids = array();
        $imageids = array();
		foreach($list as $k=>$v) {
			if(is_array($v) && count($v) && ($v['tid'] > 0 || $verify > 0)) {				if($make_row) {
					$v = $this->Make($v, 1);
				}
								if($v['uid']>0) {
					$uids[$v['uid']] = $v['uid'];
				}
				if($v['touid']>0) {
					$uids[$v['touid']] = $v['touid'];
				}
				if($v['videoid']>0) {
					$videoids[$v['videoid']] = $v['videoid'];
				}
				if($v['musicid']>0) {
					$musicids[$v['musicid']] = $v['musicid'];
				}
	            if ($v['item'] == 'reward' && $v['type'] == 'first' && $v['item_id'] > 0) {
	                $rewardids[$v['item_id']] = $v['item_id'];
	            }
	            if($v['imageid']){
	                $i = explode(",",$v['imageid']);
	                if($i){
	                    foreach($i as $key=>$val){
	                        $imageids[$val] = $val;
	                    }
	                }
	            }
	            if($v['attachid']){
	                $i = explode(",",$v['attachid']);
	                if($i){
	                    foreach($i as $key=>$val){
	                        $attachids[$val] = $val;
	                    }
	                }
	            }
			}
			$list[$k] = $v;
		}

		

		$member_list = array();
		if($uids) {
			$sql = "SELECT M.`uid`,
  M.`ucuid`,
  M.`username`,
  M.`nickname`,
  M.`signature`,
  M.`face_url`,
  M.`face`,
  M.`validate`,
  M.`role_id`,
  M.`validate_category`,
  M.`level`,
  MF.validate_true_name,
  MF.validate_remark
FROM ".DB::table('members')." M
  LEFT JOIN ".DB::table('memberfields')." MF
    ON MF.uid = M.uid
WHERE M.uid IN('".implode("','", $uids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$member_list[$row['uid']] = jsg_member_make($row);
			}
		}

		$video_list = array();
		if($videoids) {
			$sql = "SELECT `id`,
  `video_hosts`,
  `video_link`,
  `video_img`,
  `video_img_url`,
  `video_url`
FROM ".DB::table('topic_video')."
WHERE `id` IN('".implode("','", $videoids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$video_list[$row['id']] = $row;
			}
		}

		$music_list = array();
		if($musicids) {
			$sql = "SELECT `id`,
  `music_url`,
  `xiami_id`
FROM ".DB::table('topic_music')."
WHERE `id`IN('".implode("','", $musicids)."')";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
				$music_list[$row['id']] = $row;
			}
		}

                $reward_list = array();
        if($rewardids){
            $sql = "select `id`,`tid`,`event_image`,`image` from `".DB::table('reward')."`  where `id` IN (".jimplode($rewardids).") ";
			$query = DB::query($sql);
			while (false != ($row=DB::fetch($query))) {
                if($row['event_image'] || $row['image']){
					if(!$row['event_image']){
						$row['event_image'] = $row['image'];
					}
                    if(!$rewarImageList[$row['event_image']]){
                        $rewarImageList[$row['event_image']] = DB::result_first("select `image` from `".TABLE_PREFIX."reward_image` where `id` = '{$row['event_image']}'");
                        $rewarImageList[$row['event_image']] = $rewarImageList[$row['event_image']] ? $rewarImageList[$row['event_image']] : './images/reward_noPic.gif';
                    }
                    $row['event_image'] = $rewarImageList[$row['event_image']];
                } else {
                    $row['event_image'] = './images/reward_noPic.gif';
                }
				$reward_list[$row['id']] = $row;
			}
        }

                $image_list = array();
        if($imageids){
            $image_list = jlogic('image')->image_list($imageids);
        }
                $attach_list = array();
        if($attachids){
            $attach_list = jlogic('attach')->attach_list($attachids);
        }

		if($member_list || $video_list || $music_list || $reward_list || $image_list || $attach_list) {
			foreach($list as $k=>$v) {
				if(is_array($v) && count($v) && ($v['tid'] > 0 || $verify > 0)) {					if($v['uid']>0 && $member_list[$v['uid']]) {
						$v = array_merge($v, $member_list[$v['uid']]);
					}
					if($v['touid']>0 && $member_list[$v['touid']]) {
						if ($v['tousername'] != $member_list[$v['touid']]['nickname']) {
							jtable('topic')->update(array('tousername'=>$member_list[$v['touid']]['nickname']), $v['tid']);
						}
					}
					if($v['videoid']>0 && $video_list[$v['videoid']]) {
						$v['VideoID'] = $video_list[$v['videoid']]['id'];
						$v['VideoHosts'] = $video_list[$v['videoid']]['video_hosts'];
						$v['VideoLink'] = $video_list[$v['videoid']]['video_link'];
						$v['VideoUrl'] = $video_list[$v['videoid']]['video_url'];

						if ($video_list[$v['videoid']]['video_img']) {
							$v['VideoImg'] = ($video_list[$v['videoid']]['video_img_url'] ? $video_list[$v['videoid']]['video_img_url'] : $GLOBALS['_J']['config']['site_url']) . '/' . $video_list[$v['videoid']]['video_img'];
						} else {
							$v['VideoImg'] = $GLOBALS['_J']['config']['site_url'] . '/images/vd.gif';
						}
					}
					if($v['musicid']>0 && $music_list[$v['musicid']]) {
						$v['MusicID'] = $music_list[$v['musicid']]['id'];
						$v['MusicUrl'] = $music_list[$v['musicid']]['music_url'];
						$v['xiami_id'] = $music_list[$v['musicid']]['xiami_id'];
					}
	                if($v['item'] == 'reward' && $v['type'] == 'first' && $reward_list[$v['item_id']]['tid'] == $v['tid']){
	                    $v['is_reward'] = 1;
	                    $v['reward_image'] = $reward_list[$v['item_id']]['event_image'];
	                    $r_url = $GLOBALS['_J']['config']['site_url'].'/index.php?mod=reward&code=detail&id='.$v['item_id'];
	                    $reward_url = '  更多信息尽在<a href="'.$r_url.'">' . $r_url .'</a>';
	                    $v['content'] = $v['content'] . $reward_url;
	                }
	                if($v['imageid'] && $image_list){
	                    $i_arr = explode(",",$v['imageid']);
	                    foreach ($i_arr as $image_id) {
	                        $v['image_list'][$image_id] = $image_list[$image_id];
	                    }
	                }
	                if($v['attachid'] && $attach_list){
	                    $a_arr = explode(",",$v['attachid']);
	                    foreach ($a_arr as $attach_id) {
	                        $v['attach_list'][$attach_id] = $attach_list[$attach_id];
	                    }
	                }
				}
				if($v['anonymous']) {
					$v = $this->_anonymous($v);
				}
				$list[$k] = $v;
			}
		}

		return $list;
	}

	
	function Make($topic, $merge_sql = 0 , $option = array(), $clist_img = 1,$islongtext =0)
	{
		global $jishigou_rewrite;

				$clist_img = ($clist_img && !$merge_sql && (true === IN_JISHIGOU_INDEX || true === IN_JISHIGOU_AJAX || true === IN_JISHIGOU_ADMIN));

				$make_member_fields = "`uid`,`ucuid`,`username`,`nickname`,`signature`,`face_url`,`face`,`validate`,`validate_category`,`level`";

				$topic['content'] .= $topic['content2'];

				if($topic['longtextid'] > 0) {
			$topic['content'] = $this->_content_end($topic['content']);
		}


		


		$topic['raw_content'] = strip_tags($topic['content']);
		unset($topic['content2']);

				if ($topic['dateline']) {
			$topic['addtime'] = $topic['dateline'];
			$topic['dateline'] = my_date_format2($topic['dateline']);
		}
		
		$topic['is_vote'] = 0;
		if(!$topic['random']) {
			$topic['random'] = mt_rand();
		}

				if($GLOBALS['_J']['plugins']['func']['printtopic']) {
			jlogic('plugin')->hookscript('printtopic', 'funcs', $topic, 'printtopic');
		}

				if (false === strpos($topic['content'], '</') &&
			(false !== strpos($topic['content'], $GLOBALS['_J']['config']['site_url']) ||
				false !== strpos($topic['content'], '.wmv'))) {
			if (preg_match_all('~(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
			$topic['content'] . " ", $match)) {
				$cont_rpl = $cont_sch = array();
				foreach ($match[0] as $v) {
					$v = trim($v);
					if (($vl = strlen($v)) < 8 || $vl > 200) {
						continue;
					}
					if('.wmv' == substr($v, -4)){
						$cont_sch[] = "{$v}";
						if($islongtext){
							$cont_rpl[] = "<br><center><object border='0' data='{$v}' width='480' align='baseline' type='video/x-ms-wmv' height='400'></object></center><br>如要下载此视频，请右击此链接，选择“目标另存为”：<br><a target='_blank' href='{$v}'>{$v}</a>";
						}else{
							$cont_rpl[] = "<a target='_blank' href='".jurl('index.php?mod=topic&code='.$topic['tid'])."' title='单击播放'><img src='images/vd.gif'></a>";
						}
					}
					if (strtolower($GLOBALS['_J']['config']['site_url']) == strtolower(substr($v, 0, strlen($GLOBALS['_J']['config']['site_url'])))) {
						
						$app_type = '';
						$tmp_vid = 0;
						if (MEMBER_ID > 0) {
							if (preg_match("/mod=vote(?:&code=view)?&vid=([0-9]+)/", $v, $m) || preg_match("/vote(?:\/view)?\/vid\-([0-9]+)/", $v, $m)) {
								$app_type = 'vote';
								$tmp_vid = $m[1];
								if ($topic['is_vote'] === 0) {
									$topic['is_vote'] = $tmp_vid;
								}
							}
						}

												if ($app_type == 'vote') {
							$cont_sch[] = "{$v}";
							$vote_key = $topic['tid'].'_'.$topic['random'];
							if (IN_JISHIGOU_WAP === true || IN_JISHIGOU_MOBILE === true) {
								$cont_rpl[] = "<a href='{$v}'>{$v}<img src='{$GLOBALS['_J']['config']['site_url']}/images/voteicon.gif'/></a>";
							} else {
								$cont_rpl[] = "<a onclick='return getVoteDetailWidgets(\"{$vote_key}\", {$tmp_vid});' href='{$v}'>{$v}<img src='{$GLOBALS['_J']['config']['site_url']}/images/voteicon.gif'/></a>";
							}
						} else {
							$url_ext = jio()->FileExt($v);
							if(!in_array($url_ext, array('jpg', 'png', 'gif', 'bmp', 'jpeg', 'css', 'js'))) {
								$cont_sch[] = "{$v}";
								$cont_rpl[] = "<a target='_blank' href='{$v}'>{$v}</a>";
							}
						}
					}
				}

				if ($cont_rpl && $cont_sch) {
										$cont_sch = array_unique($cont_sch);
					$cont_rpl = array_unique($cont_rpl);

					$topic['content'] = trim($this->_str_replace($cont_sch, $cont_rpl, $topic['content']));
				}
			}
		}

				$this->_parseAt($topic);

				$highlight = jget('highlight', 'txt');
		if ($highlight && is_string($highlight)) {
			$topic['content'] = str_replace($highlight, "<font color=red>{$highlight}</font>", $topic['content']);
		}
		

				if (false !== strpos($topic['content'], '<T>#'))
		{
			static $topic_content_tag_href_pattern_static = '';
			if (!$topic_content_tag_href_pattern_static)
			{
				$topic_content_tag_href_pattern_static = "index.php?mod=tag&code=|REPLACE_VALUE|";

				
				if ($topic['item'] == 'qun') {
					$topic_content_tag_href_pattern_static = "index.php?mod=qun&qid={$topic['item_id']}&tag=|REPLACE_VALUE|";
				}

				if ($jishigou_rewrite)
				{
					$topic_content_tag_href_pattern_static = $jishigou_rewrite->formatURL($topic_content_tag_href_pattern_static);
				}

								if (defined("IN_JISHIGOU_MOBILE")) {
					$topic_content_tag_href_pattern_static = 'javascript:goToTopicList(\\\'|REPLACE_VALUE| . "\')"';
				}
			}
			$topic['content'] = preg_replace('~<T>#(.+?)#</T>~e', '\'<a href="' . str_replace('|REPLACE_VALUE|',
				'\' . ' . (defined('IN_JISHIGOU_MOBILE') ? '' : 'urlencode') . '(strip_tags(\'\\1\'))', $topic_content_tag_href_pattern_static) . ' . \'">#\\1#</a>\'', $topic['content']);
		}

		if (false !== strpos($topic['content'], '</U>')) {
			static $topic_content_url_href_pattern_static = '';
			if (!$topic_content_url_href_pattern_static) {
				$topic_content_url_href_pattern_static =
                    "index.php?mod=url&code=|REPLACE_VALUE|";
				if ($jishigou_rewrite) {
					$topic_content_url_href_pattern_static = ltrim($jishigou_rewrite->formatURL($topic_content_url_href_pattern_static),
                        '/');
				}
			}
			$sys_site_url = $GLOBALS['_J']['config']['site_url'];
			if ($jishigou_rewrite) {
				$sys_site_url = ((false !== ($_tmp_pos = strpos($sys_site_url, '/', 10))) ?
				substr($sys_site_url, 0, $_tmp_pos) : $sys_site_url);
			}
			$topic['content'] = preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~e', '\'<a title="\'.jhtmlspecialchars(strip_tags(\'\\2\')).\'" href="' .
				($sys_site_url . '/' . str_replace('|REPLACE_VALUE|', '\\1', $topic_content_url_href_pattern_static)) . '" target="_blank">' .
				($sys_site_url . '/' . str_replace('|REPLACE_VALUE|', '\\1', $topic_content_url_href_pattern_static)) . '</a>\'', $topic['content']);
		}
		if(false !== strpos($topic['content'], '<T>')) {
			$topic['content'] = str_replace(array('<T>', '</T>', '</U>', '<T', '</T', ), '', $topic['content']);
		}
		if(false !== strpos($topic['content'], '<U')) {
			$topic['content'] = preg_replace('~(</U>|<U[^><]*?>|<U\s*)~', '', $topic['content']);
		}
		
				$topic = $this->_make_topic_from($topic);
		
				$topic['top_parent_id'] = $topic['roottid'];
		$topic['parent_id'] = $topic['totid'];
		
        if ($topic['imageid'] && !$merge_sql) {
			$topic['image_list'] = jlogic('image')->image_list($topic['imageid']);
		}
		if ($topic['attachid'] && !$merge_sql) {
			$topic['attach_list'] = jlogic('attach')->attach_list($topic['attachid']);
		}

                $content_arr = explode("\n", $topic['content']);
        
        if(count($content_arr) > 1) {
            $content0 = $content_arr[0];
			if(jstrlen(strip_tags($content0))<=78 && strpos($content0,'[code]')===false) {
				if($islongtext){
					$content_arr[0] = '<center><b>'.$content0.'</b></center>';
				}else{
					$content_arr[0] = '<b>'.$content0.'</b>';
				}
                $topic['content'] = implode("\n", $content_arr);
            }
        }

				if (false !== strpos($topic['content'], '[')) {
            			if (false === strpos($topic['content'], '#[')) {
                				if (preg_match_all('~\[(.+?)\]~', $topic['content'], $match)) {
					static $face_conf=null;
					if(!$face_conf) {
						$face_conf = jconf::get('face');
					}
					foreach ($match[0] as $k => $v) {
						if (false != ($img_src = $face_conf[$match[1][$k]])) {
														if (defined("IN_JISHIGOU_MOBILE")) {
								$img_src = 'mobile/'.$img_src;
							}
							$topic['content'] = str_replace($v, '<img src="' . $GLOBALS['_J']['config']['site_url'] .
                                '/' . $img_src . '" border="0"/>', $topic['content']);
						}
					}
				}
            }

						if(false !== strpos($topic['content'], '[/image]') && preg_match_all('~\[image\](.+?)\[\/image\]~', $topic['content'], $match)) {
				$TPT = $option['TPT_id'] ? $option['TPT_id'] : '';
				$ptidv = $option['ptidv'] ? $option['ptidv'] : '';
				$type = $option['type'] ? $option['type'] : '';
				foreach ($match[0] as $k => $v) {
					if($option && ($image_url = $topic['image_list'][$match[1][$k]]['image_small']) && !$merge_sql && $clist_img) {
						$iid = $topic['image_list'][$match[1][$k]]['id'];
						$ikey = $topic['image_list'][$match[1][$k]]['image_key'];
						$image_original = $topic['image_list'][$match[1][$k]]['image_original'];
						if($type == 'artZoom2'){
						$imageHTML = '<ul class="imgList"><li><a id="TV_img_a_'.$iid.'" href="'.$image_original.'" class="artZoom2" rel="'.$image_url.'"><img id="TV_img_'.$iid.'" src="'.$image_original.'" onload="TV_resizeImage(this, 550, '.$iid.')" /></a></li></ul>';
						} else {
							$imageHTML = '<ul class="imgList"><li><a href="'.$image_original.'" class="artZoomAll" rel="'.$image_url.'" rev="'.$ikey.'"><img src="'.$image_url.'" onload="this.click();"/></a></li></ul>';
						}
						$topic['content'] = str_replace($v, $imageHTML, $topic['content']);
						unset($topic['image_list'][$match[1][$k]]);
					} else {
						 $topic['content'] = str_replace(array("<br />{$v}", "<br>{$v}", "{$v}<br />", "{$v}<br>", "{$v}"), '', $topic['content']);
					}
				}
			}

			

						if(false !== strpos($topic['content'], '[/b]') && preg_match_all('/\[b\](.+?)\[\/b\]/is',$topic['content'],$match)){
				foreach ($match[0] as $k => $v) {
					$topic['content'] = str_replace($v, '<b>'.$match[1][$k].'</b>', $topic['content']);
				}
			}
			if(false !== strpos($topic['content'], '[/u]') && preg_match_all('/\[u\](.+?)\[\/u\]/is',$topic['content'],$match)){
				foreach ($match[0] as $k => $v) {
					$topic['content'] = str_replace($v, '<u>'.$match[1][$k].'</u>', $topic['content']);
				}
			}
			if(false !== strpos($topic['content'], '[/color]') && preg_match_all('/\[color=(.+?)\](.+?)\[\/color\]/is',$topic['content'],$match)){
				foreach ($match[0] as $k => $v) {
					$topic['content'] = str_replace($v, '<span style="color:'.$match[1][$k].';">'.$match[2][$k].'</span>', $topic['content']);
				}
			}
			#引用的样式
			if(false !== strpos($topic['content'], '[/quote]') && preg_match_all('/\[quote\](.+?)\[\/quote\]/is',$topic['content'],$match)){
				foreach ($match[0] as $k => $v) {
					 $topic['content'] = str_replace($v, '<div class="quote">'.$match[1][$k].'</div>', $topic['content']);
				}
			}
			#代码的样式
			if(false !== strpos($topic['content'], '[/code]') && preg_match_all('/\[code\](.+?)\[\/code\]/is',$topic['content'],$match)){
				foreach ($match[0] as $k => $v) {
					$html = "<br /><div class='code'><ul>";
					$codeList = explode("\n",$match[1][$k]);
					if($codeList) {
						$li = '';
						foreach ($codeList as $code) {
							$li .= "<li>$code</li>";
						}
					}
					$html .= $li.'</ul></div><br />';
					$topic['content'] = str_replace($v, $html, $topic['content']);
				}
			}
						if(false !== strpos($topic['content'], '[')) {
				$topic['content'] = preg_replace('~\[\/?([\w\d\=]+)\]~i', '', $topic['content']);
			}
		}

				if ($topic['videoid'] > 0 && $GLOBALS['_J']['config']['video_status'] && !$merge_sql)
		{
			$sql = "select `id`,`video_hosts`,`video_link`,`video_img`,`video_img_url`,`video_url` from `" .
			TABLE_PREFIX . "topic_video` where `id`='" . $topic['videoid'] . "' ";
			$topic_video = DB::fetch_first($sql);

			$topic['VideoID'] = $topic_video['id'];
			$topic['VideoHosts'] = $topic_video['video_hosts'];
			$topic['VideoLink'] = $topic_video['video_link'];
			$topic['VideoUrl'] = $topic_video['video_url'];

			if ($topic_video['video_img'])
			{
				$topic['VideoImg'] = ($topic_video['video_img_url'] ? $topic_video['video_img_url'] : $GLOBALS['_J']['config']['site_url']) . '/' . $topic_video['video_img'];
			}
			else
			{
				$topic['VideoImg'] = $GLOBALS['_J']['config']['site_url'] . '/images/vd.gif';
			}
		}

				if ($topic['musicid'] > 0 && !$merge_sql)
		{
			$sql = "select `id`,`music_url`,`xiami_id` from `" . TABLE_PREFIX .
                "topic_music` where `id`='" . $topic['musicid'] . "' ";
			$topic_music = DB::fetch_first($sql);

			$topic['MusicID'] = $topic_music['id'];
			$topic['MusicUrl'] = $topic_music['music_url'];
			$topic['xiami_id'] = $topic_music['xiami_id'];
        }

                if($topic['image_list'] && !$merge_sql && $clist_img){
            $addHtml = '<ul class="imgList">';
            foreach ($topic['image_list'] as $k=> $v) {
                $image_url = $v['image_small'];
                $iid = $v['id'];
                $image_original = $v['image_original'];
                $addHtml .= '<li><a href="'.$image_original.'" class="artZoomAll" rel="'.$image_url.'" rev="'.$ikey.'"><img src="'.$image_url.'" onload="this.click();"/></a></li>';
            }
            $addHtml .= '</ul>';
            $topic['content'] .= $addHtml;
        }

				if(!$merge_sql) {
			$topic = array_merge($topic, (array) $this->GetMember($topic['uid'], $make_member_fields));

			
			if($topic['anonymous']) {
				$topic = $this->_anonymous($topic);
			}
		}

				return $topic;
	}

	
	private function _anonymous($topic) {
		if($topic['anonymous'] && isset($topic['uid']) && !isset($topic['anonymous_data'])) {
			$defs = array(
				'uid' => 0,
				'username' => '',
				'nickname' => '匿名用户',
				'face' => face_get(),
				

				'validate_category' => 0,
				'validate' => 0,
				'vip_cat' => '',
				'vip_pcat' => '',
				'vip_cat_string' => '',
				'vip_cat_html' => '',
				'validate_remark' => '',
				'validate_user' => '',
				'validate_true_name' => '',
				'vip_pic' => '',
				'vip_info' => '',
				'validate_html' => '',
				

				'from_area' => '',

				'role_id' => 0,
				'role_name' => '',
				'role_type' => '',
				

				'signature' => '',
				'level' => '',
			);
			$defs['face_original'] = $defs['face_small'] = $defs['face']; 
						foreach($defs as $k=>$v) {
				$topic['anonymous_data'][$k] = $topic[$k];
				$topic[$k] = $v;
			}
		}
		return $topic;
	}

	
	function _make_topic_from($topic) {
		$topic['from_html'] = $topic['from_string'] = '';
		if($topic['relateid'] > 0){
			$topic['channel_type'] = 'default';
		}

				if($topic['featureid'] > 0){
			$features = jlogic('feature')->get_feature();
			$topic['topic_feature_status'] = $features[$topic['featureid']] ? $features[$topic['featureid']] : '';
		}

		if($topic['item'] && $topic['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topic = item_topic_from($topic);
		} elseif($topic['from']) {
			static $topic_from_config=null;
			if(null===$topic_from_config) {
				$topic_from_config = jconf::get('topic_from');
			}
			$topic_from = $topic_from_config[$topic['from']];
			if($topic_from) {
				$topic['from_html'] = $topic['from_string'] = '来自 '.$topic_from['name'];
				if($topic_from['link']) {
					$topic['from_html'] = '来自 <a href="'.$topic_from['link'].'">'.$topic_from['name'].'</a>';
				}
			}
		}

		if(!$topic['from']) {
			$topic['from'] = 'web';
		}
		if(!$topic['from_string']) {
			$topic['from_string'] = '来自 '.$GLOBALS['_J']['config']['site_name'];
		}
		if(!$topic['from_html']) {
			$topic['from_html'] = '来自 <a href="'.$GLOBALS['_J']['config']['site_url'].'">'.$GLOBALS['_J']['config']['site_name'].'</a>';
		}

		return $topic;
	}

	
	function _parseAt(&$topic)
	{
		global $jishigou_rewrite, $topic_content_member_href_pattern_static;
		if (false !== strpos($topic['content'], '</M>')) {
						if (defined("IN_JISHIGOU_MOBILE")) {
				$topic_content_member_href_pattern_static = "javascript:;";
									preg_match_all("/<M ([^>]+?)>/", $topic['content'], $matches);
					if ($matches[1]) {
						$sql = "Select `uid`,`username` From " . TABLE_PREFIX . 'members' .
		                    " Where `username` in ('" . implode("','", $matches[1]) . "')";
						$query = DB::query($sql);
						$_search = $_replace = array();
						while (false != ($row = DB::fetch($query))) {
							$_replace[] = "<M {$row['uid']}>";
							$_search[] = "<M {$row['username']}>";
						}

						if ($_search && $_replace) {
							$topic['content'] = str_replace($_search, $_replace, $topic['content']);
							$topic_content_member_href_pattern_static = "javascript:goToUserInfo('|REPLACE_VALUE|')";
						}
					}
								$topic['content'] = preg_replace('~<M ([^>]+?)>\@(.+?)</M>~', '<a href="' .
				str_replace('|REPLACE_VALUE|', '\\1', $topic_content_member_href_pattern_static) .
                '" target="_blank">@\\2</a>', $topic['content']);
			} else {
				if(!$topic_content_member_href_pattern_static)
				{
					$topic_content_member_href_pattern_static = 'index.php?mod=|REPLACE_VALUE|';

					if($jishigou_rewrite)
					{
						$topic_content_member_href_pattern_static = $jishigou_rewrite->formatURL($topic_content_member_href_pattern_static);
					}
				}

				$topic['content'] = preg_replace('~<M ([^>]+?)>\@(.+?)</M>~', '<a href="' .
				                				str_replace('|REPLACE_VALUE|', '\\1', $topic_content_member_href_pattern_static) .
                '" target="_blank"'.(true!==IN_JISHIGOU_WAP ? '  onmouseover="get_at_user_choose(\'\\2\',this)"' : '').'>@\\2</a>', $topic['content']);			}
		}
	}

	
	function GetMore($tid) {
		$row = jtable('topic_more')->row($tid);
		return $this->MakeMore($row);
			}
	
	function MakeMore($row) {
		
		if ($row['diguids']) {
			$row['diguids'] = unserialize($row['diguids']);
		}

		return $row;
	}
	function GetDigUids($tid) {
		$topic_info = $this->Get($tid);
		if (!$topic_info) {
			return false;
		}
		$topic_more = $this->GetMore($tid);
		if (!$topic_more) {
			return false;
		}
		return $topic_more['diguids'];
	}

	
	function GetMember($ids, $fields = '*') {
		return jlogic('member')->get($ids, $fields);
	}

	function _syn_to($data)
	{
		
		$this->_syn_to_sina($data);

		
		$this->_syn_to_qqwb($data);

		$this->_syn_to_kaixin($data);

		$this->_syn_to_renren($data);
	}

	
	
	function _syn_to_sina($data = array())
	{
		if ($GLOBALS['_J']['config']['sina_enable'] && $data && $data['uid'] > 0 && $data['tid'] > 0 && 'sina'!=$data['from'] &&
		sina_weibo_init() && sina_weibo_bind($data['uid'], 1) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['sina_update_disable'])
		{
			$sina_config = jconf::get('sina');
			if (($data['totid'] > 0 && $sina_config['is_syncreply_toweibo'] && sina_weibo_bind_setting($data['uid'])) || ($data['totid'] <
			1 && $sina_config['is_synctopic_toweibo'] && jget('syn_to_sina')))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule(array('data'=>$data),'syn_to_sina', $data['uid']);
				}
				else
				{
					include(ROOT_PATH . 'include/ext/xwb/to_xwb.inc.php');
				}
			}
		}
	}

	function _syn_to_qqwb($data = array())
	{
		if ($GLOBALS['_J']['config']['qqwb_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'qqwb'!=$data['from'] &&
		($qqwb_config = qqwb_init()) && qqwb_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['qqwb_update_disable'])
		{
			if (($data['totid'] > 0 && $qqwb_config['qqwb']['is_syncreply_toweibo'] && qqwb_synctoqq($data['uid'])) || ($data['totid'] <
			1 && $qqwb_config['qqwb']['is_synctopic_toweibo'] && jget('syn_to_qqwb')))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data,'syn_to_qqwb');
				}
				else
				{
					@extract($data);

					include(ROOT_PATH . 'include/ext/qqwb/to_qqwb.inc.php');
				}
			}
		}
	}

	function _syn_to_kaixin($data = array())
	{
		if ($GLOBALS['_J']['config']['kaixin_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'kaixin'!=$data['from'] &&
		kaixin_init() && kaixin_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['kaixin_update_disable'])
		{
			$kaixin_config = jconf::get('kaixin');
			if (($data['totid'] > 0 && $kaixin_config['is_sync_topic']) || ($data['totid'] <
			1 && $kaixin_config['is_sync_topic'] && jget('syn_to_kaixin')))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data, 'syn_to_kaixin');
				}
				else
				{
					kaixin_sync($data);
				}
			}
		}
	}

	function _syn_to_renren($data = array())
	{
		if ($GLOBALS['_J']['config']['renren_enable'] && $data['uid'] > 0 && $data['tid'] > 0 && 'renren'!=$data['from'] &&
		renren_init() && renren_bind($data['uid']) && !$GLOBALS['imjiqiren_sys_config']['imjiqiren']['renren_update_disable'])
		{
			$renren_config = jconf::get('renren');
			if (($data['totid'] > 0 && $renren_config['is_sync_topic']) || ($data['totid'] <
			1 && $renren_config['is_sync_topic'] && jget('syn_to_renren')))
			{
				if( TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX || TRUE===IN_JISHIGOU_ADMIN )
				{
					$result = jsg_schedule($data, 'syn_to_renren');
				}
				else
				{
					renren_sync($data);
				}
			}
		}
	}

	
	
	private function _process_content($content, $topic_info=array())
	{
		$return = array();

		$content .= ' ';

		$cont_sch = $cont_rpl = $at_uids = $tags = $urls = array();

		$tuid = (int) $topic_info['uid'];
		# @user
		if (false !== strpos($content, '@')) {
			if (preg_match_all('~\@([\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+|[\xa3\xac]|[\xef\xbc\x8c]|[\,\.\;\[\#])~', $content, $match)) {
				if (is_array($match[1]) && count($match[1])) {
					foreach ($match[1] as $k => $v) {
						$v = trim($v);
						if ('　' == substr($v, -2)) {
							$v = substr($v, 0, -2);
						}

						if ($v && strlen($v) < 16) {
							$match[1][$k] = $v;
						}
					}

					$sql = "select `uid`,`nickname`,`username` from `" .
					TABLE_PREFIX . "members` where `nickname` in ('" . implode("','", $match[1]) .
                        "') ";
					$query = DB::query($sql);
					while (false != ($row = DB::fetch($query))) {
						if($row['uid']>0 && !is_blacklist($tuid, $row['uid']) && !jsg_role_check_allow('topic_at', $row['uid'], $tuid)) {
							$_at = "@{$row['nickname']} ";
							$cont_sch[$_at] = $_at;
							$cont_rpl[$_at] = "<M {$row['username']}>@{$row['nickname']}</M> ";
							$at_uids[$row['uid']] = $row['uid'];
						}
					}
				}
			}
		}
				if($topic_info['roottid'] > 0 && in_array($topic_info['type'], array('forward'))) {
			$rtopic = $this->Get($topic_info['roottid']);
			$ruid = (int) $rtopic['uid'];
			if($ruid > 0 && $ruid != $tuid && !is_blacklist($tuid, $ruid) && !jsg_role_check_allow('topic_at', $ruid, $tuid)) {
				$at_uids[$ruid] = $ruid;
			}
		}

				if (false !== strpos($content, '#'))
		{
			$tag_num = jconf::get('tag_num', 'topic');
			if (preg_match_all('~\#([^\/\@\#\[\$\{\}\(\)\;\<\>\\\\]+?)\#~', $content, $match))
			{
				$i = 0;
				foreach ($match[1] as $v)
				{
					$v = trim($v);
					if (($vl = strlen($v)) < 2 || $vl > 60)
					{
						continue;
					}

					$tags[$v] = $v;
					$_tag = "#{$v}#";
					$cont_sch[$_tag] = $_tag;
					$cont_rpl[$_tag] = "<T>#{$v}#</T>";

					if (++$i >= $tag_num) {
						break;
					}
				}
			}
		}
						if (false !== strpos($content, ':/' . '/') || false !== strpos($content, 'www.'))
		{
						if (preg_match_all('~(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&\;_\~\`\:\+\#\.\@\[\]]*(?:[^\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
			$content, $match))
			{
				foreach ($match[0] as $v)
				{
					$v = trim($v);
					if (($vl = strlen($v)) < 8)
					{
						continue;
					}
					if (strtolower($GLOBALS['_J']['config']['site_url']) == strtolower(substr($v, 0, strlen($GLOBALS['_J']['config']['site_url']))))
					{
						continue;
					}
					if('.wmv' == substr($v, -4)){
						continue;
					}

					if (!($arr = jlogic('url')->info($v)))
					{
						continue;
					}

					$_process_result = array();
					if(!isset($urls[$v]) && ($_process_result = $this->_process_url($v)))
					{
						$urls[$v] = $_process_result;
					}

					$rpl = ($_process_result['content'] ? " {$_process_result['content']} " : "") . "<U {$arr['key']}>{$v}</U>";
					if('image' == $_process_result['type'])
					{
						$rpl = ' ';
						if(strlen(trim($content)) <= strlen($v))
						{
							$rpl = ' 分享图片 ';
						}
					}
					elseif('music' == $_process_result['type'])
					{
						$rpl = ' ';
						if(strlen(trim($content)) <= strlen($v))
						{
							$rpl = ' 分享音乐 ';
						}
					}

					$cont_sch[$v] = "{$v}";
					$cont_rpl[$v] = $rpl;
				}
			}
		}

		if($this->replaces) {
			$cont_sch = array_merge($cont_sch, $this->replaces['search']);
			$cont_rpl = array_merge($cont_rpl, $this->replaces['replace']);
		}

		if($cont_sch && $cont_rpl) {
			$content = $this->_str_replace($cont_sch, $cont_rpl, $content);
		}

		$content = trim($content);

		$return['content'] = $content;

		$return['at_uids'] = $at_uids;

		$return['tags'] = $tags;

		$return['urls'] = $urls;

		return $return;
	}

	function _str_replace($sch, $rpl, $content) {
		if($sch) {
						uasort($sch, create_function('$a, $b', 'return (strlen($a)<strlen($b));'));
			foreach($sch as $k=>$v) {
				if($v) {
					$rv = '';
					if(is_array($rpl)) {
						if(isset($rpl[$k])) {
							$rv = $rpl[$k];
						}
					} else {
						$rv = $rpl;
					}
                    $content = str_replace($v, $rv, $content);
				}
			}
		}
		return $content;
	}

		
	function _process_url($url)
	{
		$return = array();

		$type = '';

		$ext = trim(strtolower(substr($url,strrpos($url,'.'))));

		if('.swf'==$ext)
		{
			$type = 'flash';

			$type_result = array(
                'id' => $url,
                'host' => $type,
                'url' => $url,
			);
		}
		elseif(in_array($ext,array('.mp3','.wma')))
		{
			$type = 'music';
		}
		elseif(in_array($ext,array('.jpg','jpeg','.gif','.png','.bmp',)))
		{
			$type = 'image';
		}
		else
		{
						$type_result = $this->_parse_video($url);

			if($type_result)
			{
				$type = 'video';
			}
		}

		if($type)
		{
			$return['url'] = $url;
			$return['type'] = $type;

			if($type_result)
			{
				$return[$type] = $type_result;

				if($type_result['title'])
				{
					$return['content'] = $type_result['title'];
				}
			}
		}

		return $return;
	}

	
	function _parse_video($url) {
		$ret = jlogic('video')->parse($url);

		return $ret;
	}

	
	function _parse_video_image($image_url)
	{
		$return = array();$video_img_url = '';

				if ($image_url)
		{
			$img_src_md5 = md5($image_url);
			$img_path = RELATIVE_ROOT_PATH.'images/video_img/' . $img_src_md5[0] . $img_src_md5[1] .'/';
			$img_name = $img_src_md5[2] . $img_src_md5[3] . crc32($image_url) . '.jpg';
			$video_img = $img_path . $img_name;

			if (!is_file($video_img) && ($temp_image = dfopen($image_url)))
			{



				if (!is_dir($img_path))
				{
					jio()->MakeDir($img_path);
				}

				jio()->WriteFile($video_img,$temp_image);

				if (!is_image($video_img))
				{
					jio()->DeleteFile($video_img);
					$video_img = '';
				}
				else
				{
					if($GLOBALS['_J']['config']['ftp_on'])
					{
						$ftp_key = randgetftp();
						$get_ftps = jconf::get('ftp');
						$video_img_url = $get_ftps[$ftp_key]['attachurl'];
						$ftp_result = ftpcmd('upload',$video_img,'',$ftp_key);
						if($ftp_result > 0)
						{
							jio()->DeleteFile($video_img);
						}
					}
				}
			}

			$return = array('img'=>$video_img,'url'=>$video_img_url);
		}

		return $return;
	}

	
	function _parse_url_image($data,$image_url) {
		$urls = array();
		if(is_array($image_url)) {
			$urls = $image_url;
		} else {
			$urls = (array) $image_url;
		}
		$ids = array();
		$image_id = 0;
		foreach($urls as $url) {
			$p = array(
				'pic_url' => $url,
				'tid' => $data['tid'],

				'uid' => $data['uid'],
				'username' => $data['username'],
			);
			$rets = jlogic('image')->upload($p);
			$id = max(0, (int) $rets['id']);
			if($id > 0) {
				$ids[$id] = $id;
			}
		}
		$image_id = implode(',', $ids);
		return $image_id;
	}
	
	function _parse_url_attach($data,$attach_url)
	{
		$__is_attach = false;


		$uid = $data['uid'];
		$username = $data['username'];
		$attach_id = jlogic('attach')->add($uid, $username);

		$p = array(
        	'id' => $attach_id,
        	'tid' => $data['tid'],
        	'file_url' => $attach_url,
		);
		jlogic('attach')->modify($p);

		$attach_path = RELATIVE_ROOT_PATH . 'data/attachs/topic/' . face_path($attach_id) . '/';
		$attach_type = strtolower(end(explode('.', $attach_url)));
		$attach_name = $attach_id . '.' . $attach_type;
		$attach_file = $attach_path . $attach_name;

		if (!is_file($attach_file))
		{
			if (!is_dir($attach_path))
			{
				jio()->MakeDir($attach_path);
			}

			if (($temp_attach = dfopen($attach_url)) && (jio()->WriteFile($attach_file,$temp_attach)) && is_attach($attach_file))
			{

				$attach_size = filesize($attach_file);
				$site_url = '';
				if($GLOBALS['_J']['config']['ftp_on'])
				{
					$ftp_key = randgetftp();
					$get_ftps = jconf::get('ftp');
					$site_url = $get_ftps[$ftp_key]['attachurl'];
					$ftp_result = ftpcmd('upload',$attach_file,'',$ftp_key);
				}

				$p = array(
		        	'id' => $attach_id,
        			'vtid' => $data['id'],		        	'site_url' => $site_url,
		        	'file' => $attach_file,
                	'name' => basename($attach_url),
					'filesize' => $attach_size,
					'filetype' => $attach_type,
				);
				jlogic('attach')->modify($p);

				$__is_attach = true;
			}
		}

		if (false === $__is_attach && $attach_id > 0)
		{
			jlogic('attach')->delete($attach_id);

			$attach_id = 0;
		}

		return $attach_id;
	}

	
	private function _process_at_uids($data, $at_uids) {
		$tid = (int) $data['tid'];
		if($tid < 1 || empty($at_uids)) {
			return false;
		}
		foreach ($at_uids as $at_uid) {
			$at_uid = (int) $at_uid;
			if($at_uid > 0 && $at_uid != $data['uid'] && !(jtable('topic_mention')->is_at($tid, $at_uid))) {
								jtable('topic_mention')->add($tid, $at_uid);

								if($GLOBALS['_J']['config']['imjiqiren_enable'] || $GLOBALS['_J']['config']['sms_enable'] || $GLOBALS['_J']['config']['sendmailday'] > 0) {
					$sql = "select `uid`,`username`,`nickname`,`email`,`lastactivity`,
								`at_new`,`comment_new`,`newpm`,`event_new`,`channel_new`,`company_new`,`dig_new`,`fans_new`,`qun_new`,`vote_new`,
								`email_checked`,`notice_at`,`user_notice_time`
							from `" . TABLE_PREFIX . "members`
							where `uid` = '$at_uid'";
					$user_notice = DB::fetch_first($sql);
					if($user_notice) {
												if ($GLOBALS['_J']['config']['imjiqiren_enable'] && imjiqiren_init()) {
							imjiqiren_send_message($user_notice, 't', $GLOBALS['_J']['config']);
						}
												if ($GLOBALS['_J']['config']['sms_enable'] && sms_init()) {
							sms_send_message($user_notice, 't', $GLOBALS['_J']['config']);
						}
												if ($GLOBALS['_J']['config']['sendmailday'] > 0) {
							jtable('mailqueue')->add($user_notice, 'notice_at');
						}
					}
				}
			}
		}
	}

	
	private function _process_reply($data) {
		$totid = jfilter($data['totid'], 'int');
				if($totid > 0 && $data['touid'] > 0 && $data['uid'] != $data['touid'] &&
		
		!jtable('topic_mention')->is_at($data['tid'], $data['touid'])) {
						if (($data['type'] == 'both' || $data['type'] == 'reply')) {
				jtable('members')->update_count($data['touid'], 'comment_new', '+1');
			}
						if($GLOBALS['_J']['config']['imjiqiren_enable'] || $GLOBALS['_J']['config']['sms_enable'] || $GLOBALS['_J']['config']['sendmailday'] > 0) {
				$sql = "select `uid`,`username`,`nickname`,`email`,`lastactivity`,
					`newpm`,`at_new`,`event_new`,`fans_new`,`vote_new`,`dig_new`,`channel_new`,`company_new`,`qun_new`,`comment_new`,
					`email_checked`,`notice_reply`,`user_notice_time`
				from `" . TABLE_PREFIX . "members`
				where `uid` = '{$data['touid']}'";
				$reply_notice = DB::fetch_first($sql);
				if($reply_notice) {
										if ($GLOBALS['_J']['config']['imjiqiren_enable'] && imjiqiren_init()) {
						imjiqiren_send_message($reply_notice, 'p', $GLOBALS['_J']['config']);
					}
										if ($GLOBALS['_J']['config']['sms_enable'] && sms_init()) {
						sms_send_message($reply_notice, 'p', $GLOBALS['_J']['config']);
					}
										if($GLOBALS['_J']['config']['sendmailday'] > 0) {
						jtable('mailqueue')->add($reply_notice, 'notice_reply');
					}
				}
			}
		}
	}

	function _process_urls($data,$urls,$is_modify=false,$table='topic')
	{
		$tid = $data['tid'];
		$timestamp = TIMESTAMP;

		foreach($urls as $k=>$v)
		{
			$url_type = $v['type'];

			if('flash'==$url_type || 'video'==$url_type)
			{
				$videos = $v[$url_type];
				$video_hosts = $videos['host'];
				$video_link = $videos['id'];
				$video_url = $videos['url'];
				if($is_modify && $data['videoid'] > 0) 				{
					$topic_video = DB::fetch_first("select * from ".TABLE_PREFIX."topic_video where `id`='{$data['videoid']}'");
					if($topic_video['video_url']==$video_url)
					{
						return ;
					}
					else
					{
						DB::query("delete from ".TABLE_PREFIX."topic_video where `id`='{$data['videoid']}'");
					}
				}
				$videos['image_local'] = '';
				if($videos['image_src'])
				{
					$return_video_img = $this->_parse_video_image($videos['image_src']);
					$videos['image_local'] = $return_video_img['img'];
				}
				$video_img = $videos['image_local'];
				$video_img_url = '';
				if($video_img)
				{
					$video_img_url = ($GLOBALS['_J']['config']['ftp_on'] ? $return_video_img['url'] : "");
				}

				DB::query("insert into `" . TABLE_PREFIX .
                "topic_video`(`uid`,`tid`,`username`,`video_hosts`,`video_link`,`video_url`,`video_img`,`video_img_url`,`dateline`) values ('" .
				$data['uid'] . "','" . $data['tid'] . "','" . $data['username'] . "','" . $video_hosts . "','" . $video_link .
                "','" . $video_url . "','" . $video_img . "','$video_img_url','{$timestamp}')");
				$videoid = DB::insert_id();

				if($videoid > 0)
				{
					if($table == 'topic_verify'){
						DB::query("update `" . TABLE_PREFIX . "topic_verify` set `videoid`='{$videoid}' where `id`='{$data['id']}'");
					}else{
						DB::query("update `" . TABLE_PREFIX . "topic` set `videoid`='{$videoid}' where `tid`='{$data['tid']}'");
					}
				}
			}
			elseif('music'==$url_type)
			{
				if($is_modify && $data['musicid'] > 0)
				{
					$topic_music = DB::fetch_first("select * from ".TABLE_PREFIX."topic_music where `id`='{$data['musicid']}'");
					if($topic_music['music_url']==$v['url'])
					{
						return ;
					}
					else
					{
						DB::query("delete from ".TABLE_PREFIX."topic_music where `id`='{$data['musicid']}'");
					}
				}

				DB::query("insert into `" . TABLE_PREFIX .
                "topic_music`(`uid`,`tid`,`username`,`music_url`,`dateline`) values ('" .
				$data['uid'] . "','" . $data['tid'] . "','" . $data['username'] . "','{$v['url']}','{$timestamp}')");
				$musicid = DB::insert_id();

				if($musicid > 0)
				{
					if($table == 'topic_verify'){
						DB::query("update `" . TABLE_PREFIX . "topic_verify` set `musicid`='{$musicid}' where `id`='{$data['id']}'");
					}else{
						DB::query("update `" . TABLE_PREFIX . "topic` set `musicid`='{$musicid}' where `tid`='{$data['tid']}'");
					}
				}

			} elseif('image'==$url_type) {
				if($is_modify && $data['imageid']) {
					$topic_image = jlogic('image')->get_info($data['imageid']);
					if($topic_image['image_url']==$v['url']) {
						return ;
					} else {
						jlogic('image')->delete($data['imageid']);
					}
				}

				$this->_parse_url_image($data,$v['url']);
			}
			elseif('attach'==$url_type)
			{
				if($is_modify && $data['attachid'])
				{
					$topic_attach = jlogic('attach')->get_info($data['attachid']);
					if($topic_attach['attach_url']==$v['url'])
					{
						return ;
					}
					else
					{
						jlogic('attach')->delete($data['attachid']);
					}
				}

				$this->_parse_url_attach($data,$v['url']);
			}
		}
	}

	
	function is_qun_member($qid, $uid = MEMEBR_ID)
	{
		return jlogic('qun')->is_qun_member($qid, $uid);
	}

	
	function check_view_perm($uid, $type)
	{
		return true;
	}

	function GetMedal($medalid=0,$uid=0)
	{
		if($GLOBALS['_J']['config']['acceleration_mode']){
			return array();
		}

		$uid = (is_numeric($uid) ? $uid : 0);

		$medal_list = array();

		if($uid > 0)
		{
			$sql = "select  U_MEDAL.dateline ,  MEDAL.medal_img , MEDAL.conditions
            			  , MEDAL.medal_name ,MEDAL.medal_depict ,MEDAL.id , U_MEDAL.*
            		from `".TABLE_PREFIX."medal` MEDAL
            		left join `".TABLE_PREFIX."user_medal` U_MEDAL on MEDAL.id=U_MEDAL.medalid
            		where U_MEDAL.uid='{$uid}'
            		and U_MEDAL.is_index = 1
            		and MEDAL.is_open = 1 ";

			$query = DB::query($sql);
			while (false != ($row = DB::fetch($query)))
			{
				$row['dateline'] = date('m-d日 H:s ',$row['dateline']);
				$medal_list[$row['id']] = $row;
			}
		}

		return $medal_list;
	}

	
	function GetParentTopic($topic_list, $get_parent = 0) {
		$parent_list = array();
		if ($topic_list) {
						$parent_id_list = array();
			foreach ($topic_list as $row) {
				if($get_parent && 0 < ($p = (int) $row['parent_id'])) {
					$parent_id_list[$p] = $p;
				}
				if (0 < ($p = (int) $row['top_parent_id'])) {
					$parent_id_list[$p] = $p;
				}
			}
			if ($parent_id_list) {
				$parent_list = $this->Get($parent_id_list);
			}
		}
		return $parent_list;
	}
	function get_parent_list($topic_list, $get_parent = 0) {
		return $this->GetParentTopic($topic_list, $get_parent);
	}

		function GetRelateTopic($topic_list) {
		$relate_list = array();
		if ($topic_list) {
						$relate_id_list = array();
			foreach ($topic_list as $row) {
				if (0 < ($p = (int) $row['relateid'])) {
					$relate_id_list[$p] = $p;
				}
			}
			if ($relate_id_list) {
				$relate_list = $this->Get($relate_id_list);
			}
		}
		return $relate_list;
	}
	function get_relate_list($topic_list) {
		return $this->GetRelateTopic($topic_list);
	}

	
	function GetForwardContent($content) {
				$seprator = $this->ForwardSeprator;
		$seprator = trim($seprator);
		$strpos = strpos($content, $seprator);

		if(false !== $strpos) {
			$content = substr($content, 0, $strpos);
		}

		return $content;
	}

	
	function getCommentUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-commentuser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(false === ($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT COUNT(*) AS c_count,t.uid,m.username,m.nickname
					FROM `".TABLE_PREFIX."topic` t
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid
					WHERE t.`touid` = '$uid'
					    AND t.`type` IN ('reply','both')
					    AND t.dateline > $time
					GROUP BY t.`uid`
					ORDER BY c_count DESC
					LIMIT $limit  ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	
	function getDigUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-diguser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(false === ($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT COUNT(*) AS d_count,t.uid,m.username,m.nickname
					FROM `".TABLE_PREFIX."topic_dig` t
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid
					WHERE t.`touid` = '$uid' AND t.dateline > $time
					GROUP BY t.`uid`
					ORDER BY d_count DESC
					LIMIT $limit  ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	
	function getMyCommentUser($uid=MEMBER_ID,$limit=10){
		$cache_id = $uid.'-mycommentuser-7days-'.$limit;
				$time = TIMESTAMP;
		$time = $time - 7*86400;
		if(false === ($user = cache_db('get', $cache_id))){
			$user = array();
			$sql = "SELECT COUNT(*) AS mc_count,t.touid as uid,m.username,m.nickname
					FROM `".TABLE_PREFIX."topic` t
					LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.touid
					WHERE t.`uid` = '$uid'
					    AND t.`type` IN ('reply','both')
					    AND t.dateline > $time
					GROUP BY t.`touid`
					ORDER BY mc_count DESC
					LIMIT $limit  ";
			$query = DB::query($sql);
			while($rs = DB::fetch($query)){
				if($rs['uid'] > 0) {
					$rs['face'] = face_get($rs['uid']);
					$user[$rs['uid']] = $rs;
				}
			}

			cache_db('set', $cache_id,$user,3600);
		}
		return $user;
	}

	function getMusicUser($limit=10){
				$time = TIMESTAMP;
		$time = $time - 30*86400;
		$user = array();
		$sql = "SELECT COUNT(*) AS m_count,t.uid,m.username,m.nickname
				FROM `".TABLE_PREFIX."topic_music` t
				LEFT JOIN ".TABLE_PREFIX."members m ON m.uid = t.uid
				WHERE t.dateline > $time
				GROUP BY t.`uid`
				ORDER BY m_count DESC
				LIMIT $limit  ";
		$query = DB::query($sql);
		while($rs = DB::fetch($query)){
			if($rs['uid'] > 0) {
				$rs['face'] = face_get($rs['uid']);
				$user[$rs['uid']] = $rs;
			}
		}

		return $user;
	}

	function _content_end($c) {
		$_srrp = strrpos($c, '<');
		if(false !== $_srrp) {
			$_r = substr($c, $_srrp);
			if(substr_count($_r, '<') != substr_count($_r, '>')) {
				$c = substr($c, 0, $_srrp);
			}
		}
		return $c;
	}
	function _content_strip($c) {
		if(false !== strpos($c, ':/'.'/')) {
			$st = $GLOBALS['_J']['config']['site_domain'];
			$bh = substr_count($st, '.') > 1 ? substr($st, strpos($st, '.') + 1) : $st;
			if(false !== strpos($c, $bh)) {
				$p = '~<iframe.+?src\s*\=\s*[\\\\]*[\'\"](https?\:\/\/(?:[\w]+\.)*(?:'.preg_quote($bh, '~').')[^\'\"]+?)[\\\\]*[\'\"].*?>.*?<\/iframe>~is';
				preg_match_all($p, $c, $rs);
				if($rs) {
					$s = $r = array();
					foreach($rs[0] as $k=>$v) {
						$s[$k] = $v;
						$this->replaces['replace'][] = jstripslashes(preg_replace(array('~ width\s*\=\s*[\\\\]*[\'\"]\d+[\\\\]*[\'\"]~i'), array('  width="460"'), $v));

						$r[$k] = '[:[__KEEP_IFRAME_CONTENT_' . mt_rand() . '__]:]';
						$this->replaces['search'][] = $r[$k];
					}
					if($s && $r) {
						$c = str_replace($s, $r, $c);
					}
				}
			}
		}

		
		$c = trim($c);
		$c = jhtmlspecialchars($c);
		$c = str_replace('&amp;', '&', $c);

		return $c;
	}

		function cache_rm($tid = 0) {
		unset($this->_cache);
		jtable('topic')->cache_rm($tid);
	}
	
	private function clearFormat($content){

		$tags = array(
			'quote','b','code','color'
			);
		foreach ($tags as $tag){
			$strpos = strpos($content,'['.$tag);
			if(false === $strpos){
				continue;
			}
			if($tag == 'color'){
				if(preg_match_all('/\[color=(.+?)\](.+?)\[\/color\]/is', $content, $found)){
					$content = str_replace($found[0],$found[2],$content);
				}
				continue;
			}
			if(preg_match_all('/\['.$tag.'\](.*)\[\/'.$tag.'\]/iU', $content, $found)){
				$content = str_replace($found[0],$found[1],$content);
			}
		}
		return $content;
   }

    
   function get_new_topic($limit=5,$type='first'){
       $list = jtable('topic')->get(array('sql_field'=>'`uid`,`content`','sql_order'=>'dateline DESC','sql_limit'=>$limit,'type'=>$type));
       if($list['list']){
           $memberTable = jtable('members');
           foreach ($list['list'] as &$one){
               $one['nickname'] = $memberTable->val(array('uid'=>$one['uid']),'nickname');
               $one['content'] = strip_tags($one['content'].$one['content2']);
			   $one['content'] = strip_tags($one['content']);
           }
           return $list['list'];
       }else{
           return FALSE;
       }
   }

	
	function check_view($tid, $uid = MEMBER_ID) {
		$tid = (is_numeric($tid) ? (int) $tid : 0);
    	if($tid < 1) {
    		return jerror('ID不能为空', -1);
    	}
    	$topic_info = $this->Get($tid);
    	if(empty($topic_info)) {
    		return jerror('请指定一个正确的ID', -2);
    	}
    	if(in_array($topic_info['item'], array('channel', 'qun')) && $topic_info['item_id'] > 0) {
    		$can_read = jlogic($topic_info['item'])->can_view_topic($topic_info['item_id'], $uid);
			if(!$can_read) {
				return jerror("您没有权限查看该内容", -3);
			}
    	}
	}

}

?>