<?php
/**
 * 文件名：topic.mod.php
 * @version $Id: topic.mod.php 5672 2014-05-06 06:51:50Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博话题AJAX模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;

	var $ID;


	function ModuleObject($config)
	{
		$this->MasterObject($config);




		$this->TopicLogic = jlogic('topic');

		$this->ID = jget('id', 'int');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();

		switch($this->Code)
		{
			case 'favor_fenlei':
				$this->favoriteFenlei();
				break;
			case 'favor_event':
				$this->favoriteEvent();
				break;
			case 'favorite_tag':
				$this->FavoriteTag();
				break;
			case 'group_list':
				$this->GroupList();
				break;
			case 'group_menu':
				$this->Group_Menu();
				break;
			case 'forward_menu':
				$this->Forward_Menu();
				break;
			case 'usermenu':
				$this->UserMenu();
				break;
			case 'tag_menu':
				$this->Tag_Menu();
				break;
			case 'delete_video':
				$this->DeleteVideo();
				break;
			case 'delete_music':
				$this->DeleteMusic();
				break;
			case 'favor':
				$this->Favorite();
				break;
			case 'favor_tag':
				$this->FavoriteTag();
				break;
			case 'dovideo':
				$this->DoVideo();
				break;
			case 'do_add':
				$this->DoAdd();
				break;
			case 'delete':
				$this->Delete();
				break;
			case 'delverify':
				$this->delVerify();
				break;
			case 'list_reply':
				$this->ListReply();
				break;
			case 'forward':
				$this->Do_forward();
				break;
			case 'view_comment':
				$this->ViewComment();
				break;
			case 'view_bbs':
				$this->ViewBbs();
				break;
			case 'view_cms':
				$this->ViewCms();
				break;
			case 'create_group':
				$this->Create_Group();
				break;
			case 'do_group':
				$this->Do_Group();
				break;
			case 'group_fields':
				$this->Group_fields();
				break;
			case 'del_group':
				$this->Del_Group();
				break;
			case 'do_fansgroup':
				$this->Do_FansGroup();
				break;

			case 'del_fansgroup':
				$this->Del_FansGroup();
				break;
			case 'set_fansgroup':
				$this->Set_FansGroup();
				break;
			case 'do_setfansgroup':
				$this->Do_SetFansGroup();
				break;
			case 'create_fansgroup':
				$this->Create_FansGroup();
				break;
			case 'fansgrouplist':
				$this->FansGroupList();
				break;
			case 'add_user_follow':
				$this->Add_User_Follow();
				break;
			case 'follower_choose':
				$this->Follower_choose();
				break;
			case 'doblacklist':
				$this->DoAddMyBlackList();
				break;
			case 'do_delmyblacklist':
				$this->DoDelMyBlackList();
				break;
			case 'modifytopic':
				$this->ModifyTopic();
				break;
			case 'do_modifytopic':
				$this->Do_ModifyTopic();
				break;
			case 'topicshow':
				$this->TopicShow();
				break;
			case 'user_tag':
				$this->User_Tag();
				break;
			case 'del_tag':
				$this->Del_Tag();
				break;
			case 'pmfriends':
				$this->PmFriends();
				break;
			case 'open_mdeal':
				$this->Open_Mdeal_Index();
				break;

			case 'do_delmyfans':
				$this->DoDelMyFans();
				break;

							case 'recd':
				$this->recd();
				break;
			case 'do_recd':
				$this->do_recd();
				break;
							case 'editarea':
				$this->editErea();
				break;

							case 'publishsuccess':
				$this->publishSuccess();
				break;

			case 'list':
			case 'tag':
			case 'myhome':
			case 'mycomment':
			case 'mylastpublish':
			case 'updatecurrent':
			case 'myat':
			case 'myblog':
			case 'tocomment':
			case 'myfavorite':
			case 'favoritemy':
			case 'groupview':
			case 'topicnew':
				$this->DoList();
				break;
							case 'reg_follow_user':
				$this->Do_Reg_Follow_User();
				break;
							case 'showlogin':
				$this->ShowLogin();
				break;
							case 'add_favor_tag':
				$this->addFavoriteTag();
				break;
							case 'modify_user_signature':
				$this->Modify_User_Signature();
				break;

							case 'check_medal_list':
				$this->Check_Medal_List();
				break;
							case 'photo':
				$this->Photo();
				break;
							case 'ajax':
				$this->Ajax();
				break;
							case 'new':
			case 'tc':
			case 'hotforward':
			case 'channel':
				$this->Pic_ajax();
				break;
			default:
				$this->Main();
				break;
		}

		response_text(ob_get_clean());
	}

	function Main()
	{
		response_text("正在建设中……");
	}

	
	function Ajax()
	{
		$TopicListLogic = jlogic('topic_list');
		$tids = unserialize(base64_decode($this->Post['key']));
		$del_ajax_recd = (int)$this->Post['kill'];
		if($this->Post['order']=='asc'){$order = 'dateline ASC';}else{$order = '';}
		if(!is_array($tids)){$tids=array($tids);}
				foreach($tids as $k => $v){
			if(!is_numeric($v)){
				unset($tids[$k]);
			}
		}
				if($del_ajax_recd > 0){
			$recd_tid = jlogic('buddy')->check_new_recd_topic(MEMBER_ID);
			if($recd_tid && $tids){
				foreach($tids as $key => $val){
					if($recd_tid == $val){
						unset($tids[$key]);
					}
				}
			}
		}
		if($tids){
			$options = array('tid'=>$tids,'count'=>'20','order'=>$order);
			$info = $TopicListLogic->get_data($options);
			$topic_list = $info['list'];
		}
		if($topic_list){
			if(!empty($order)){$topic_view = 1;}
			$parent_list = $this->TopicLogic->GetParentTopic($topic_list,1);
						$relate_list = $this->TopicLogic->GetRelateTopic($topic_list);

			foreach($topic_list as $key => $val){
				if ($val['longtextid'] > 0) {
					$topic_list[$key]['content'] = $val['content'].'...';
				}
			}

			include(template('topic_list_js_ajax'));
		}
	}

		function Pic_ajax()
	{
		$options = array();
		$TopicListLogic = jlogic('topic_list');
		$per_page_num = $this->Post['pp_num'] ? (int)$this->Post['pp_num'] : 20;
		$cache_time = $this->Post['c_time'] ? (int)$this->Post['c_time'] : 10;
		$uid = $this->Post['uid'] ? $this->Post['uid'] : '';
		if($this->Code =='channel'){
			$id = $this->Post['id'] ? $this->Post['id'] : '';
			$ids = (array) unserialize(base64_decode($id));
			foreach($ids as $k=>$v) {
				if(!is_numeric($v) || (int) $v < 1) {
					unset($ids[$k]);
				}
			}
			$options = array(
				'item'=>'channel',
				'item_id' => $ids,
				'perpage' => $per_page_num,
			);
			$info = $TopicListLogic->get_data($options);
		}elseif ($this->Code =='new'){
			$uids = (array) unserialize(base64_decode($uid));
			foreach($uids as $k=>$v) {
				if(!is_numeric($v) || (int) $v < 1) {
					unset($uids[$k]);
				}
			}
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => 'topic-newtopic',
				'perpage' => $per_page_num,
				'order' => ' dateline DESC ',
				'uid' => $uids,
			);
			$info = $TopicListLogic->get_data($options);
		}elseif('hotforward' == $this->Code){
			$d = in_array($this->Post['d'],array(1,7,14,30)) ? (int)$this->Post['d'] : 7;
			$uid_sql = $this->Post['uid_sql'] ? $this->Post['uid_sql'] : '';
			$time = $d * 86400;
			$dateline = TIMESTAMP - $time;
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-hotforward-{$d}",
				'perpage' => $per_page_num,
				'type' => 'first',
				'where' => " $uid_sql `forwards`>'0' AND `dateline`>='$dateline' ",
				'order' => " `forwards` DESC , `dateline` DESC ",
			);
			$info = $TopicListLogic->get_data($options);
		}elseif('tc' == $this->Code){
			$province = $this->Post['province'] ? $this->Post['province'] : '';
			$city = $this->Post['city'] ? $this->Post['city'] : '';
			$area = $this->Post['area'] ? $this->Post['area'] : '';
			$vip = $this->Post['vip'] ? $this->Post['vip'] : '';
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => "topic-tctopic-{$province}-{$city}-{$area}",
				'perpage' => $per_page_num,
				'province' => $province,
				'city' => $city,
				'area' => $area,
				'vip' => $vip,
			);
			$info = $TopicListLogic->get_tc_data($options);
		}
		$topic_list = array();
		if (!empty($info)) {
			$topic_list = $info['list'];
			foreach ($topic_list as $key => $row) {				if($row['top_parent_id'] || $row['parent_id']) {
					unset($topic_list[$key]);
				}
			}
		}
		if($topic_list){
			include(template('topic_new_pic_ajax'));
		}
	}

		function editErea(){
		$province = (int) $this->Get['province'];
		if($province){
			$province_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$province'");
		}

		$city = (int) $this->Get['city'];
		if($city){
			$city_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$city'");
		}

		$area = (int) $this->Get['area'];
		if($area){
			$area_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$area'");
		}

		$street = (int) $this->Get['street'];
		if($street){
			$street_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$street'");
		}

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set province = '$province_name' , city = '$city_name' , area = '$area_name' , street = '$street_name' where uid = ".MEMBER_ID);
		echo $province_name." ".$city_name;
	}

	
	function DoList()
	{
		$options = array();
		if(($per_page_num = (int) jconf::get('show','topic',$this->Code)) < 1) {
			$per_page_num = 20;
		}

		$uid = (int) (get_param('uid'));
		$is_personal = (int) (get_param('is_personal'));
		$tag_id = (int) (get_param('tag_id'));

		$topic_parent_disable = false;

		$start = max(0, (int) $start);
		$limit = "limit {$start},{$per_page_num}";
		$next = $start + $per_page_num;

		if ($tag_id > 0) {
			$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where `tag_id`='{$tag_id}' order by `item_id` desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_ids[0] = 0;
			while (false != ($row = $query->GetRow())) {
				$topic_ids[$row['item_id']] = $row['item_id'];
			}
			$options['tid'] = $topic_ids;
		}

		$options['perpage'] = $per_page_num;
				$tpl = 'topic_list_ajax';
		if ('myhome' == $this->Code) {
			$uid = MEMBER_ID;
			$cache_time = 600;
			$cache_key = "{$uid}-topic-myhome--0";
			
			$topic_myhome_time_limit = 0;
			if($this->Config['topic_myhome_time_limit'] > 0) {
				$topic_myhome_time_limit = (time() - ($this->Config['topic_myhome_time_limit'] * 86400));

				if($topic_myhome_time_limit > 0) {
					$options['dateline'] = $topic_myhome_time_limit;
				}
			}

			$topic_uids[$uid] = $uid;
			if($is_personal) {
				if(false === (cache_db('get', $cache_key))) {
					$buddyids = get_buddyids(MEMBER_ID, $this->Config['topic_myhome_time_limit']);
					if($buddyids) {
						$topic_uids = array_merge($topic_uids, $buddyids);
					}
				}
			}

			$options['uid'] = $topic_uids;
		} else if('myat' == $this->Code) {
			$uid = MEMBER_ID;

						$rets = jtable('topic_mention')->get_ids(array(
					'uid' => $uid,
					'sql_order' => ' `id` DESC ',
					'perpage' => $options['perpage'],
					'page_url' => $options['page_url'],
			), 'tid', 1);
			$total_record = $rets['count'];
			$page_arr = $rets['page'];
			$topic_list = (($total_record > 0 && $rets['ids']) ? $this->TopicLogic->Get($rets['ids']) : array());
			$topic_list_get = true;
		} else if ('groupview' == $this->Code) {
			$gid = jget('gid', 'int');
			$g_view_uids = array();
			if($gid > 0) {
				$ginfo = jtable('buddy_follow_group')->info($gid);
				if($ginfo && $ginfo['count'] > 0 && $ginfo['uid'] == MEMBER_ID) {
					$groupid = $gid;
					$groupname = $ginfo['name'];
										$g_view_uids = jtable('buddy_follow_group_relation')->get_my_group_uids(MEMBER_ID, $gid);
				}
			}

						if ($g_view_uids) {
				$options['uid'] = $g_view_uids;
			} else {
				exit();
			}
		} else if ('mylastpublish' == $this->Code) {

			$topic_list = $this->TopicLogic->Get(" where `uid`='".MEMBER_ID."' order by `dateline` desc limit 0,1 ");
                        $temp_last_topic=end($topic_list);
            if($temp_last_topic['item'] == 'reward' && !$this->item){
                $this->item = 'reward';
            }
            
						$no_from = false;
									$ref_mod = $this->Post['ref_mod'];
			$talk_r = $this->Post['r'];
			if($talk_r == 'answer' || $talk_r == 'talk'){
				$tpl = 'talk/talk_item_ajax';
			}
			$ref_code = $this->Post['ref_code'];
			$no_from = $this->_no_from($ref_mod, $ref_code);
			$topic_list_get = true;
			if($ref_mod == 'live' || $ref_mod == 'talk'){
				foreach($topic_list as $key => $val){
					$item = $topic_list[$key]['item'];
					$itemid = $topic_list[$key]['item_id'];
					$uid = $topic_list[$key]['uid'];
					$user_type = DB::result_first("SELECT type FROM ".DB::table('item_user')." WHERE item = '$item' AND itemid='{$itemid}' AND uid = '$uid'");
					$topic_list[$key]['user_css'] = $item.$user_type;
					if($ref_mod == 'talk' && $user_type == 'guest'){
						$topic_list[$key]['user_str'] = '本期嘉宾';
					}else{
						$topic_list[$key]['user_str'] = '&nbsp;';
					}
				}
				$no_mBlog_linedot2 = false;
			}else{
				$no_mBlog_linedot2 = true;
			}
		} else if ('updatecurrent' == $this->Code) {
						$tid = intval($this->Post['tid']);
			if (empty($tid)) {
				exit;
			}

			$refcode = trim($this->Post['refcode']);
			$refmod = trim($this->Post['refmod']);
			$tpl = 'topic_item_ajax';
			if ('topic' == $refmod && 'myfavorite' == $refcode) {
				$sql = "SELECT TF.dateline as favorite_time , T.*
						FROM ".DB::table("topic_favorite")." AS TF
						LEFT JOIN ".DB::table("topic")." AS T
						ON T.tid=TF.tid where T.tid='{$tid}'";
				$this->Code = $refcode;
				$topic_parent_disable = true;
			} else {
				$sql = "SELECT * FROM ".DB::table('topic')." WHERE tid='{$tid}'";
			}

			$data = DB::fetch_first($sql);
			if (empty($data)) {
				exit;
			}
			if (isset($data['favorite_time'])) {
				$data['favorite_time'] = my_date_format2($data['favorite_time']);
			}
			$val = $this->TopicLogic->Make($data,0,array(),0);
			$topic_list[] = $val;

			$no_from = false;
						if ('vote' == $refmod && 'view' == $refcode) {
				$no_from = true;
			}

						if ($refmod == 'qun') {
				$this->Module = 'qun';
			}

						if ($refcode == 'reply_list_ajax') {
				$tpl = 'topic_comment_item';
				$topic_parent_disable = true;
				$v = $val;
			}

			$topic_list_get = true;
		} else if ('mycomment' == $this->Code) {

			$options['where'] = " `touid`='".MEMBER_ID."' ";

		} elseif ('tocomment' == $this->Code) {
			$title = '我评论的';
			$topic_selected = 'tocomment';
			$options['where'] = " `uid` = '".MEMBER_ID."' and `type` in ('both','reply') ";

		} elseif ('myblog' == $this->Code) {
			$options['uid'] = $uid;

		} else if ('myfavorite' == $this->Code) {
			$uid = MEMBER_ID;

			$sql = "select TF.dateline as favorite_time , T.* from `".TABLE_PREFIX."topic_favorite` TF left join `".TABLE_PREFIX."topic` T on T.tid=TF.tid where TF.uid='{$uid}' order by TF.id desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				if($row['tid']<1) {
					continue;
				}
				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$topic_list[$row['tid']] = $row;
			}
			$topic_list = $this->TopicLogic->MakeAll($topic_list);
			$topic_list_get = true;

		} else if ('favoritemy' == $this->Code) {
			$uid = MEMBER_ID;

			$sql = "select TF.dateline as favorite_time , TF.tuid , T.* from `".TABLE_PREFIX."topic_favorite` TF left join `".TABLE_PREFIX."topic` T on T.tid=TF.tid where TF.tuid='{$uid}' order by TF.id desc {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tuids = array();
			while (false != ($row = $query->GetRow()))
			{
				if($row['tid']<1) {
					continue;
				}
				$row['favorite_time'] = my_date_format2($row['favorite_time']);
				$topic_list[$row['tid']] = $row;
				$tuids[$row['tuid']] = $row['tuid'];
			}
			$topic_list = $this->TopicLogic->MakeAll($topic_list);
			if($tuids) {
				$topic_members = $this->TopicLogic->GetMember($tuids,"`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`validate`");
			}

			$topic_parent_disable = true;
			$topic_list_get = true;

		} else if ('topicnew' == $params['code'] || 'topicnew' == $this->Code) {
			$title = '最新内容';
			$options['where'] = '';
			$orderby = in_array($this->Get['orderby'],array('post','dig','mark')) ? $this->Get['orderby'] : 'post';
			if($orderby == 'mark'){
				$order = ' `lastupdate` DESC';
			}elseif($orderby == 'dig'){
				$order = ' `lastdigtime` DESC';
			}else{
								$options['type'] = array('first','forward','both');
				$order = ' `dateline` DESC';
			}
			$options['order'] = $order;

            if($this->Get["date"] && strtotime($this->Get["date"])>0){
                $dateline = strtotime($this->Get["date"]);
                $options['where'] .= " `dateline` > '".$dateline."' and `dateline`<'".($dateline+86400)."'";
            } else {
                $this->Get["date"] = date("Y-m-d", TIMESTAMP);
            }

            if(empty($options['type'])) {
            	$options['type'] = get_topic_type();
            }
		}

		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_request($options);
		#endif

		if (!$topic_list_get) {
						if($cache_time > 0 && $cache_key && !$options['tid']) { 				$options = jlogic('topic_list')->get_options($options, $cache_time, $cache_key);
			}
			
			$info = jlogic('topic_list')->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
		}

		$topic_list_count = 0;
		if($topic_list) {
			$topic_list_count = count($topic_list);

			if(!$topic_parent_disable) {
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list);
							}
						$relate_list = $this->TopicLogic->GetRelateTopic($topic_list);
		}
		if($tpl == 'talk/talk_item_ajax'){
			$answer_list = array();
			if($parent_list){
				$answer_list = $topic_list;
				$topic_list = $parent_list;
			}
			foreach($topic_list as $key => $val){
				if(empty($topic_list[$key]['touid'])){
					$topic_list[$key]['biank_css'] = 'talk_view_ping';
					$topic_list[$key]['tubiao_css'] = 'talk_view_pin';
				}else{
					$topic_list[$key]['biank_css'] = 'talk_view_wenda';
					$topic_list[$key]['tubiao_css'] = 'talk_view_wen';
					$topic_list[$key]['ask_list'] = $answer_list;
					foreach($topic_list[$key]['ask_list'] as $k => $v){
						$topic_list[$key]['ask_list'][$k]['tubiao_css'] = 'talk_view_da';
						$topic_list[$key]['ask_list'][$k]['user_css'] = 'talkguest';
					}
				}
			}
		}

		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_response($topic_list, $page_arr);
		#endif
		include(template($tpl));
	}

		function _no_from($ref_mod, $ref_code = '')
	{
		$no_from = true;
		if ($ref_mod == 'topic' || $ref_mod == 'qun' || $ref_mod == 'live' || $ref_mod == 'talk' || $ref_mod == 'channel') {
			$no_from = false;
		}
		return $no_from;
	}

	function DoAdd()
	{
		if (MEMBER_ID < 1) {
			response_text("请先登录或者注册一个帐号");
		}

				$content = trim($this->Post['content']);

		if (!$content) {
			response_text("请输入内容");
		}

				$topic_type = $this->Post['topictype'];

		
		if('both' == $topic_type){
			$type = 'both';
		} elseif('reply' == $topic_type){
			$type = 'reply';
		} elseif('company' == $topic_type){
			$type = 'company';
		} elseif('qun' == $topic_type){
			$type = 'qun';
		} elseif('live' == $topic_type){
			$type = 'live';
		} elseif('talk' == $topic_type){
			$type = 'talk';
		} elseif ('personal' == $topic_type) {
			$type = 'personal';
		} elseif(in_array($topic_type,array('answer','event','vote','fenlei','reward'))){
			$type = 'reply';
		} elseif (is_numeric($topic_type)) {
			$type = 'first';
		} else{
			$type = 'first';
		}
		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->topic_detect_type($type, $topic_type);
		#endif

				if(!in_array($type, array('both', 'reply', 'forward'))) { 			if(!($this->MemberHandler->HasPermission('topic','add'))) {
				response_text("您的角色没有发布的权限");
			}
		} else {
			if(('reply'==$type || 'both'==$type) && !($this->MemberHandler->HasPermission('topic','reply'))) {
				response_text("您的角色没有评论的权限");
			} elseif(('forward'==$type || 'both'==$type) && !($this->MemberHandler->HasPermission('topic','forward'))) {
				response_text("您的角色没有转发的权限");
			}
		}

				if($this->Config['seccode_enable']>1) {
			$YXM_check = jlogic('seccode')->topiccheckYXM($type);
			if ($YXM_check && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
				$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['YinXiangMa_challenge'],@$_POST['YXM_level'][0],@$_POST['YXM_input_result']);
				if($YinXiangMa_response != "true"){
					response_text("验证码输入错误");
				}
			}
		}

		$roottid = max(0, (int) $this->Post['roottid']);
		$totid = max(0, (int) $this->Post['totid']);
		$touid = max(0, (int) $this->Post['touid']);
		$imageid = trim($this->Post['imageid']);
		$attachid = trim($this->Post['attachid']);
		$relateid = trim($this->Post['relateid']);
		$featureid = trim($this->Post['featureid']);
		$videoid = max(0, (int) $this->Post['videoid']);
		$anonymous = (int)$this->Post['anonymous'];

		$longtextid = max(0, (int) $this->Post['longtextid']);
		$design = trim($this->Post['r']);
		$xiami_id = trim($this->Post['xiami_id']) ? trim($this->Post['xiami_id']) : 0;
				$from = trim($this->Post['from']);
		$verify = $this->Post['verify'] ? 1 : 0;

        $is_reward = jget('is_reward','int');
		

		$item = trim($this->Post['item']);
		$item_id  = intval(trim($this->Post['item_id']));
		if('company' == $item){
			$item_id = $GLOBALS['_J']['member']['companyid'];
		}
		if (!empty($item_id)) {
						if(!($item == 'company' || $item == 'topic_image')){
				jfunc('app');
				$ret = app_check($item, $item_id);
				if (!$ret) {
					$item = '';
					$item_id = 0;
				}
			}
						if($item == 'channel'){
				$can_pub_topic = jlogic('channel')->can_pub_topic($item_id);
				if (!$can_pub_topic) {
					$item = '';
					$item_id = 0;
				}
			}
		} else {
			$item = '';
			$item_id = 0;
		}
		$data = array(
			'content' => $content,
			'totid'=>$totid,
			'imageid'=>$imageid,
			'attachid'=>$attachid,
			'videoid'=>$videoid,
			'from'=>empty($from) ? 'web' : $from,
			'type'=>$type,
					'design'=>$design,

					'item' => $item,
			'item_id' => $item_id,
			'touid' => $touid,
					'longtextid' => $longtextid,
					'xiami_id' => $xiami_id,
					'pverify' => $verify,
            #有奖转发标记
			'is_reward' => $is_reward,
						'relateid' => $relateid,
						'featureid' => $featureid,
						'anonymous' => $anonymous,
		);

		if($GLOBALS['_J']['plugins']['func']['posttopic']) {
			hookscript('posttopic', 'funcs', array('param' => $this->Post, 'step' => 'check'), 'posttopic');
		}

		$return = $this->TopicLogic->Add($data);

		if (is_array($return) && $return['tid'] > 0) {

			$r = $this->Post['r'];

			$is_huifu = $this->Post['is_huifu'];

			$return_reply = $this->Post['return_reply'];

			if($totid > 0 && $r) {
				if('vc' == $r) {
					if($is_huifu == 'is_huifu') {
						$return_reply = 'is_huifu';
					}
					$this->ViewComment($return['totid'], $return['tid'], $return_reply, $roottid);
				} elseif ('rl' == substr($r,0,2)) {
					$_GET['page'] = 999999999;
					$this->ListReply(((is_numeric(($tti=substr($r,3))) && $tti > 0) ? $tti : $return['totid']),$return['tid']);
				} elseif (in_array($r,array('tohome','lt','myblog','myhome','tagview','view'))) {
					exit;
				}
			}
		} else {
			if(is_string($return)){
				$return  = '[发布失败]'.$return;
			}elseif(is_array($return)){
				$return = '[发布成功]'.implode(",",$return);
			}else{
				$return = '未知错误';
			}

			response_text("{$return}");
		}
	}

	function Delete()
	{
		$tid = jget('tid', 'int');

		if ($tid < 1) {
			js_alert_output("请指定一个您要删除的话题");
		}
		$topic = $this->TopicLogic->Get($tid);
		if (!$topic) {
			js_alert_output("话题已经不存在了");
		}
		if($topic['item'] == 'channel' && $topic['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topic = item_topic_from($topic);
		}

		if(!(jallow($topic['uid']) || $topic['ismanager'])){
			js_alert_output("您无权删除该话题");
		}

		$return = $this->TopicLogic->DeleteToBox($tid);

		response_text($return . $this->js_show_msg());
	}

		function delVerify(){
		$tid = jget('tid', 'int');

		return $this->TopicLogic->Delete($tid);
	}

	function ViewComment($tid=0, $highlight=0, $return_reply='', $roottid=0, $tid2=0) {
		$limit = max(0, (int) jconf::get('show', 'topic_one_comment', 'list'));
		if($limit < 1 || $limit > 100) {
			$limit = 6;
		}
		$more_display = 1;

		$highlight = ($highlight ? $highlight : get_param('highlight'));
		$_GET['highlight'] = $highlight;

		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));
		$tid2 = max(0, (float) ($tid2 ? $tid2 : jget('tid2')));

		$reply_list = array();
		if($tid > 0) {
			$topic_info = $this->TopicLogic->Get($tid);
			if($topic_info) {
								if($return_reply == 'is_huifu') {
					$roottid = (int) ($roottid ? $roottid : $topic_info['roottid']);
					if($roottid > 0) {
						$topic_info = $this->TopicLogic->Get($roottid);
					}
				}
				if ($topic_info['replys'] > 0) {
					$p = array(
						'result_count' => $limit,
						'sql_order' => ' `dateline` DESC ',
					);
					if($tid2 > 0) {						$more_display = 0;
						$p['tid'] = $tid2;
					}
					$rets = jtable('topic_relation')->get_list($topic_info['tid'], $p);
					$reply_list = $rets['list'];
					$parent_list = $rets['parent_list'];
					$relate_reply = array();
					if($reply_list) {
						if($topic_info['relateid'] > 0){
							if($reply_list[$topic_info['relateid']]){								$relate_reply[$topic_info['relateid']] = $reply_list[$topic_info['relateid']];
								unset($reply_list[$topic_info['relateid']]);
							}else{
								$relate_reply[$topic_info['relateid']] = jlogic('longtext')->get_info($topic_info['relateid']);
							}
							$reply_list = array_merge($relate_reply,$reply_list);
						}
						foreach($reply_list as $key => $val){							if(strpos($val['from_string'], $this->Config['site_name'])){
								$val['from_string'] = '';
							}
							if($val['parents_list']) {
								foreach($val['parents_list'] as $k => $v) {
									if(strpos($v['from_string'], $this->Config['site_name'])){
										$v['from_string'] = '';
									}
									$val['parents_list'][$k] = $v;
								}
							}
							if(in_array($topic_info['channel_type'],array('ask','idea')) && $val['tid'] == $topic_info['relateid']){
								$val['ch_ty_css'] = ($topic_info['channel_type'] ? $topic_info['channel_type'] : 'default').'_relate_mark';
							}else{
								$val['ch_ty_css'] = 'comentdot';
							}
							$reply_list[$key] = $val;
						}
					}
				}
			}
		}

		include(template('topic_view_comment_ajax'));
	}

	function ViewBbs($tid=0)
	{
		$m_tl = '回复';
		$limit = max(0, (int) jconf::get('show', 'topic_one_comment', 'list'));
		if($limit < 1)
		{
			$limit = 6;
		}
		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));
		$info = array();
		if($tid > 0)
		{
						if($this->Config['dzbbs_enable']){
				if(jconf::get('dzbbs')){
					Load::logic("topic_bbs");
					$TopicBbsLogic = new TopicBbsLogic();
					$info = $TopicBbsLogic->get_reply($tid);
				}
			}elseif($this->Config['phpwind_enable']){
				$config = array();
				$config['phpwind'] = jconf::get('phpwind');
				if($config['phpwind']['bbs']){
					Load::logic("topic_bbs");
					$TopicBbsLogic = new TopicBbsLogic();
					$info = $TopicBbsLogic->get_reply($tid);
				}

			}
		}
		if (!empty($info)) {
			$replys = $info['count'];
			$reply_list = $info['list'];
			$replyurl = $info['url'];
		}
		include(template('topic_view_cmsbbs_ajax'));
	}

	function ViewCms($tid=0)
	{
		$m_tl = '评论';
		$limit = max(0, (int) jconf::get('show', 'topic_one_comment', 'list'));
		if($limit < 1)
		{
			$limit = 6;
		}
		$tid = max(0,(float) ($tid ? $tid : get_param('tid')));
		$info = array();
		if($tid > 0)
		{
						if($this->Config['dedecms_enable']){
				$dedecms_config = jconf::get('dedecms');
				if($dedecms_config){
					Load::logic("topic_cms");
					$TopicCmsLogic = new TopicCmsLogic();
					$info = $TopicCmsLogic->get_reply($tid);
				}
			}
		}
		if (!empty($info)) {
			$replys = $info['count'];
			$reply_list = $info['list'];
			$replyurl = $info['url'];
		}
		include(template('topic_view_cmsbbs_ajax'));
	}

		function ModifyTopic()
	{
		$tid = $modify_tid = max(0, (int) $this->Post['tid']);
		if($tid < 1)
		{
			js_alert_output('微博ID 错误');
		}
		$types = ($this->Post['types'] ? $this->Post['types'] : $this->Get['types']);
		$handle_key = ($this->Post['handle_key'] ? $this->Post['handle_key'] : $this->Get['handle_key']);
		$allow_attach = ($this->Post['attach'] ? $this->Post['attach'] : $this->Get['attach']);


				$types = $this->Post['types'];

		$topiclist = $this->TopicLogic->Get($modify_tid);
		if($topiclist['item'] == 'channel' && $topiclist['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topiclist = item_topic_from($topiclist);
		}

		if(!$topiclist)
		{
			response_text('您要编辑的微博已经不存在了');
		}


				if(!(MEMBER_ROLE_TYPE == 'admin' || $topiclist['ismanager']))
		{
			if(MEMBER_ID != $topiclist['uid'])
			{
				response_text("您没有权限编辑该微博");
			}

			if($topiclist['replys'] >= 1 || $topiclist['forwards'] >= 1 )
			{
				response_text("微博已被评论或者转发,不能编辑");
			}

						if($this->Config['topic_modify_time'] && (($topiclist['addtime'] ? $topiclist['addtime'] : $topiclist['dateline']) + ($this->Config['topic_modify_time'] * 60) < time()))
			{
				response_text("微博已超出可编辑时间了");
			}
		}



				if($topiclist['longtextid'] > 0) {
						$topiclist['content'] = jtable('topic_more')->get_longtext($tid);
		} else {
			$row = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='$tid'");
			$topiclist['content'] = ($row['content'] . $row['content2']);
		}

				$topiclist['content'] = strip_tags($topiclist['content']);
				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$topiclist['content'] = $this->TopicLogic->GetForwardContent($topiclist['content']);
		}
		$this->item = $topiclist['item'];
		$this->item_id = $topiclist['item_id'];
		if($topiclist['item'] == 'channel' || $topiclist['item'] == 'api' || $topiclist['item'] == ''){
			$channels = jlogic('channel')->get_select_channel();
		}
		if($topiclist['item'] == 'channel' && $topiclist['item_id'] > 0){
			$getfchid = $topiclist['item_id'];
		}else{
			$getfchid = 0;
		}
		$featureshtml = $types!='reply_list_ajax' ? jlogic('feature')->get_feature_select($getfchid,$topiclist['featureid']) : '';
		$h_key = 'mod'.$tid;
		$albums = jlogic('image')->getalbum();
		if($topiclist['imageid']){
			$uploadimages = jlogic('image')->get_uploadimg_byid($topiclist['imageid']);
		}
		include(template('modify_topic_ajax'));

	}

		function Do_ModifyTopic()
	{
		if(MEMBER_ID < 1)
		{
			echo '请先登录或者注册一个帐号';exit;
		}

		$tid = max(0, (int) $this->Post['tid']);

		if($tid < 1)
		{
			echo '微博ID不能为空';exit;
		}


		$topiclist = DB::fetch_first("select * from `".TABLE_PREFIX."topic` where `tid`='{$tid}'");

		if(!$topiclist)
		{
			echo '您要编辑的内容已经不存在了';exit;
		}
		if($topiclist['item'] == 'channel' && $topiclist['item_id'] > 0) {
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topiclist = item_topic_from($topiclist);
		}

				if(!(MEMBER_ROLE_TYPE == 'admin' || $topiclist['ismanager']))
		{
			if(MEMBER_ID != $topiclist['uid'])
			{
				echo '您没有权限编辑该微博';exit;
			}

			if($topiclist['replys'] >= 1 || $topiclist['forwards'] >= 1 )
			{
				echo '微博已被评论或者转发,不能编辑';exit;
			}

						if($this->Config['topic_modify_time'] && (($topiclist['addtime'] ? $topiclist['addtime'] : $topiclist['dateline']) + ($this->Config['topic_modify_time'] * 60) < time()))
			{
				echo '微博已超出可编辑时间了';exit;
			}
		}


		$content = strip_tags($this->Post['content']);

				
				if(empty($content))
		{
			echo '微博内容不能为空';exit;
		}


				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$content = $this->TopicLogic->GetForwardContent($content);
		}


		$imageid = $this->Post['imageid'];
		$attachid = $this->Post['attachid'];
		$itemid = $this->Post['itemid'];
		$featureid = $this->Post['featureid'];

		$return = $this->TopicLogic->Modify($tid,$content,$imageid,$attachid,'',$itemid,$featureid);


		if(!is_array($return))
		{
			echo '【编辑失败】'.$return;exit;
		}
	}


	function ListReply($tid=0,$highlight=0, $tid2=0)
	{
		$per_page_num = 10;
		$tid = max(0,(float) ($tid? $tid : $this->Post['tid']));

		if ($tid < 1) {
			response_text("[链接参数错误]不存在的地址");
		}
		$highlight = ($highlight ? $highlight : get_param('highlight'));
		$_GET['highlight'] = $highlight;

		$topic_info = $this->TopicLogic->Get($tid);

				if ($topic_info['type'] == 'reply') {
			$roottid = $topic_info['roottid'];
			$root_type = DB::result_first("SELECT `type` FROM ".DB::table('topic')." WHERE tid='{$roottid}'");
		} else {
			$root_type = $topic_info['type'];
		}

		if (!$topic_info) {
			response_text("您要查看的微博已经不存在了");
		}

		$reply_list = array();
		if ($topic_info['replys'] > 0) {
			$total_record = $topic_info['replys'];
			$page_url = "index.php?mod=topic&code={$topic_info['tid']}";
			$p = array(
				'perpage' => $per_page_num,
								'page_var' => 'p',
				'page_url' => $page_url,
				'page_extra' => 'onclick="replyList(this.title);return false;"',
			);
			$orderby = jget('orderby');
			if('dig' == $orderby) {
				$p['sql_order'] = ' `digcounts` DESC, `lastdigtime` DESC ';
			} elseif('post' == $orderby) {
				$p['sql_order'] = ' `dateline` DESC ';
			} else {
				$p['sql_order'] = ' `dateline` ASC ';
			}
			$tid2 = max(0, ($tid2 ? $tid2 : jget('tid2', 'int')));
			if($tid2 > 0) {
				$p['tid'] = $tid2;
				$p['result_count'] = 1;
			}
			$reply_list_ajax_disable = 1;
			$rets = jtable('topic_relation')->get_list($topic_info['tid'], $p);
			$page_arr = $reply_list = array();
			if($rets) {
				$page_arr = $rets['page'];
				$reply_list = $rets['list'];
				$total_record = $rets['count'];
				$parent_list = $rets['parent_list'];
			}
		}

		include(template('topic_reply_list_ajax'));
	}

		function Create_Group()
	{
		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}
		include(template('topic_group_create_ajax'));
	}


		function Do_Group()
	{
		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}

		$uid = MEMBER_ID;
		$group_name = ($this->Post['group_name'] ? $this->Post['group_name'] : $this->Post['name']);
		$gid = (int) $this->Post['gid'];
		$touid = (int) $this->Post['touid'];

		if(empty($group_name)) {
			js_alert_output('分组名称不能为空');
		}

		if($this->Post['act'] == 'modify') {
			if($gid < 1) {
				js_alert_output('分组ID不能为空');
			}
			$group_view = jtable('buddy_follow_group')->info($gid);
			if(!$group_view || $group_view['uid'] != $uid) {
				js_alert_output('请指定一个正确的分组ID');
			}
			$p = array(
				'id' => $gid,
				'name' => $group_name,
			);
			$rets = jtable('buddy_follow_group')->modify($p);
			if($rets && $rets['error']) {
				js_alert_output($rets['msg']);
			}
			$group_view['name'] = $group_name;

			include(template('modify_group_ajax'));
		} else {
			$rets = jtable('buddy_follow_group')->add($group_name, $uid);
			if($rets && $rets['error']) {
				js_alert_output($rets['msg']);
			}
			$group_id = (int) $rets;
			if($group_id < 1) {
				js_alert_output('分组添加错误');
			}

			if($this->Post['act'] == 'add') {
				echo "<script language='Javascript'>";
				echo "window.location.href='index.php?mod=topic&code=group&gid={$group_id}';";
				echo "</script>";
				exit;
			} else {
				$group_list[] = jtable('buddy_follow_group')->info($group_id);

				if ($this->Post['act'] == 'menu_add') {
					include(template('topic_group_add_item'));
				} else {
					include(template('topic_group_ajax'));
				}
			}
		}
	}



		function Group_fields()
	{
		$uid = MEMBER_ID;
		$gid = jget('gid', 'int', 'P');
		$touid = jget('touid', 'int', 'P');
		if($uid < 1 || $gid < 1 || $touid < 1) {
			exit;
		}
		if(($member_info = jsg_member_info($touid)) &&
			($group_info = jtable('buddy_follow_group')->info($gid)) && $group_info['uid'] == $uid) {
			$relation_info = jtable('buddy_follow_group_relation')->row($uid, $touid, $gid);
			if($relation_info) {
				jtable('buddy_follow_group_relation')->del($uid, $touid, $gid);
			} else {
				jtable('buddy_follow_group_relation')->add($uid, $touid, $gid);
			}
		}
	}


	
		function Group_Menu()
	{
		if (MEMBER_ID < 1) {
			js_alert_output("请先登录或者注册一个帐号");
		}
		$uid = MEMBER_ID;
		$timestamp = time();
		$userid = jget('to_user', 'int');

				if($userid < 1 || !($member = jsg_member_info($userid))) {
			js_alert_output("您要操作的用户已经不存在了");
		}

				$buddy_info = jlogic('buddy')->info($userid, $uid);

				$group_list = jlogic('buddy_follow_group')->get_my_group($uid);

				$group_set = jtable('buddy_follow_group_relation')->get_user_group_ids($uid, $userid);

		$val["uid"]=$userid;
		$handle_key = get_param('handle_key');
		include(template('topic_group_menu'));
	}

		function GroupList()
	{
		$ret = '';
		$uid = MEMBER_ID;
		$touid = jget('touid', 'int');
		if($uid > 0 && $touid > 0) {
			$user_group = jtable('buddy_follow_group_relation')->get_user_group($uid, $touid);
			if($user_group) {
				foreach($user_group as $row) {
					$ret .= '<a href="index.php?mod=follow&gid='.$row['id'].'">[ '.$row['name']." ]".'</a> ';
				}
			}
		}
		echo $ret;
		exit;
	}

		function DoDelMyFans() {
		$buddyid = MEMBER_ID;
				$touid = (int) $this->Post['touid'];
		if($buddyid > 0 && $touid > 0) {
						$is_black = $this->Post['is_black'];
			if($is_black) {
								$this->_AddBlackList($buddyid,$touid,'add');
			}

			jlogic('buddy')->del_info($buddyid, $touid);
		}
	}

		function UserMenu()
	{
		if($this->Post['nickname']) {
			#查找的时候会将名字加红，有<font>标签
			$this->Post['nickname'] = strip_tags($this->Post['nickname']);
			$member = jsg_member_info($this->Post['nickname'], 'nickname');
		}
		if($this->Post['arrow']=='yes') {
			$arrow = true;
		}

		$uid = (int) ($this->Post['uid'] ? $this->Post['uid'] : $member['uid']);
		if($uid < 1) {
			exit;
		}

				$buddy_info = jlogic('buddy')->info($uid, MEMBER_ID);
				$blacklist_info = jlogic('buddy')->blacklist($uid, MEMBER_ID);

		$list_members = $this->TopicLogic->GetMember($uid,"`uid`,`ucuid`,`medal_id`,`username`,`nickname`,`face`,`face_url`,`fans_count`,`topic_count`,`validate`,`validate_category`,`bday`,`gender`,`phone`,`qq`,`msn`,`aboutme`,`province`,`city`,`level`,`company`,`department`");
		$list_members['aboutme'] = cut_str($list_members['aboutme'],54);
		$list_members = buddy_follow_html($list_members, 'uid', 'follow_html', 1);
		$follow_html = $list_members['follow_html'];

		$ProfileSet = jconf::get('profileshowincord');
		if($ProfileSet){
			$memberProfileSet = jlogic('member_profile')->getMemberProfileSet($uid);
			$member_profile = jlogic('member_profile')->getMemberProfileInfo($uid);
			if($member_profile){
				$list_members = array_merge($list_members,$member_profile);
			}
			unset($ProfileSet['aboutme']);
		}
		if($list_members['gender']){
			$list_members['gender'] = $list_members['gender'] == 1?'男':'女';
		}
				$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$usertag = $query->GetAll();
		if($GLOBALS['_J']['plugins']['func']['printuser']) {
			jlogic('plugin')->hookscript('printuser', 'funcs', $list_members, 'printuser');
		}

		include(template('topic_user_card'));
	}

		function Follower_choose()
	{
		$nickname = get_param('nickname');
		$template = get_param('template');
		$types = get_param('types');
		$uid = (int) get_param('uid');

		$touid = $uid;

		if($touid)
		{
			$sql = "select `uid`,`ucuid`,`nickname`,`username`,`signature` from `".TABLE_PREFIX."members` where `uid`='{$touid}' ";
			$query = $this->DatabaseHandler->Query($sql);
			$members = $query->GetRow();
		}

		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while (false != ($rsdb = $query->GetRow())){
			$province[$rsdb['id']]['value']  = $rsdb['id'];
			$province[$rsdb['id']]['name']  = $rsdb['name'];
		}
		$province_list = jform()->Select("province",$province,''," onchange=\"changeProvince();\"");




		include(template('user_follower_menu'));
	}

		function DoAddMyBlackList()
	{
				$uid  = MEMBER_ID;
		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}

				$touid  = (int) $this->Post['touid'];
		if($touid < 1) {
			json_error("请指定要拉黑的用户");
		}

				$member = $this->TopicLogic->GetMember($touid);
		if(!$member) {
			json_error("请指定一个正确的用户ID");
		}

				$types	= $this->Post['types'];

				$follow_html = $this->_AddBlackList($uid,$touid,$types);

				$template	= $this->Post['template'];
		if($template) {
			$template = dir_safe($template);

			include(template($template));
		}
	}

		function DoDelMyBlackList()
	{
				$uid  = MEMBER_ID;

		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}

				$touid  = (int) $this->Post['touid'];

				$this->_AddBlackList($uid,$touid,'del');

		include(template('social/blacklist'));

	}


		function Tag_Menu()
	{
		$uid  = (int) $this->Post['uid'];
		$type = $this->Post['type'];
				if('my_tag' == $type)
		{
			$list = jtable('tag_favorite')->get(array(
				'uid' => $uid,
				'result_count' => 12,
				'sql_order' => ' `id` DESC ',
				'sql_field' => ' `id`, `tag` as `tag_name` ',
				'return_list' => 1,
			));
			$my_tag_class = 'here';
					} elseif('day_tag' == $type){

			$sql = "select `id`,`name` as tag_name,`topic_count` from `".TABLE_PREFIX."tag`  WHERE dateline>='".(time() - 86400 * 7)."' GROUP BY `tag_count` DESC limit 0,12";
			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
			$day_tag_class = 'here';
					} elseif('day_hot' == $type){

			$sql = "select `id`,`name` as tag_name,`topic_count`,`tag_count` from `".TABLE_PREFIX."tag`  WHERE dateline>='".(time() - 86400 * 7)."' GROUP BY `topic_count` DESC limit 0,12";
			$query = $this->DatabaseHandler->Query($sql);
			$list = $query->GetAll();
			$day_hot_class = 'here';
					} elseif('tui_tag' == $type){

						
			$hot_tag_recommend = jconf::get('hot_tag_recommend');
			$list = $hot_tag_recommend['list'];
			$tui_tag_class = 'here';
		}


		include(template('tag/tag_menu'));
	}




	
	function Add_User_Follow() {
		$success = 0;
		$uid = MEMBER_ID;
		if ($uid < 1) {
			$msg = "请先登录或者注册一个帐号";
		} else {
						if($this->Post['uids']) {
				$uids = $this->Post['uids'];
			}
						if($this->Post['ids']) {
				$uids = $this->Post['ids'];
			}
						if($this->Post['media_uids_'.$this->Post['media_id']]) {
				$uids =  $this->Post['media_uids_'.$this->Post['media_id']] ;
			}
			if(empty($uids)) {
				$msg = "请选择你要关注的用户";
			} else {
				$uids = (array) $uids;
				$buddyids = array();
				foreach($uids as $v) {
					$v = (int) $v;
					if($v > 0) {
						$buddyids[$v] = $v;
					}
				}
				$GLOBALS['disable_show_msg'] = 1; 				if('add' == $this->Post['type']) {
					foreach($buddyids as $bid) {
						buddy_add($bid, $uid);
					}

					$success = 1;
					$msg = "关注成功";
				} elseif('del' == $this->Post['type']) {
					foreach($buddyids as $bid) {
						buddy_del($bid, $uid);
					}

					$msg = "取消成功";
				}
			}
		}

		
		$__to = get_param('__to');
		if('iframe' == $__to) {
			js_alert_output($msg, 'alert');
		} elseif('json' == $__to) {
			if(!$success) {
				json_error($msg);
			} else {
				json_result($msg);
			}
		} else {
			response_text($msg);
		}
	}

		function addFavoriteTag() {
		$uid = MEMBER_ID;
		$tagids = jget('tag'); 
				$rets = jlogic('tag_favorite')->madd($tagids, $uid);
		$msg = '话题关注成功';
		if($rets['error']) {
			$msg = $rets['msg'];
		}
		js_alert_output($msg, 'alert');
	}

		function Favorite()
	{
		if (MEMBER_ID < 1) {
			response_text("请登录");
		}

		$uid = MEMBER_ID;
		$tid = (int) ($this->Post['tid']);

		if ($tid < 1) {
			return  "请指定一个微博";
		}

		$act = $this->Post['act'];

		
		$ret = jlogic('topic_favorite')->act($uid,$tid,$act);

		response_text($ret);

	}

		function favoriteFenlei(){
		$id = (int) $this->Post['id'];
		$act = $this->Post['act'];
		if(MEMBER_ID > 0) {
			$time = time();
			if($act == 'add'){
				$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."fenlei_favorite (fid,uid,dateline) values ('$id','".MEMBER_ID."','$time')");
			}elseif($act == 'delete'){
				$row = DB::fetch_first("select * from " . DB::table('fenlei_favorite') . ' where `fid`="'.$id.'"');
				if($row && (jallow($row[uid]))) {
					$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."fenlei_favorite where fid = '$id'");
				}
			}
		}
	}

		function favoriteEvent(){
		$id = (int) $this->Post['id'];
		$act = $this->Post['act'];
		if(MEMBER_ID > 0) {
			$time = time();
			if($act == 'add'){
				$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."event_favorite (type_id,uid,dateline) values ('$id','".MEMBER_ID."','$time')");
			}elseif($act == 'delete'){
				$row = DB::fetch_first("select * from " . DB::table('event_favorite') . " where `type_id`='$id'");
				if($row && (jallow($row[uid]))) {
					$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."event_favorite where type_id = '$id'");
				}
			}
		}
	}

		function FavoriteTag() {
		$uid = MEMBER_ID;
		$tag = jget('tag', 'trim');
		$act = jget('act', 'trim', 'P');
		if ($uid < 1) {
			js_show_login("请先登录");
		}
		if(!$tag) {
			js_alert_showmsg('请先指定一个话题');
		}

		if('delete' == $act) {
			jlogic('tag_favorite')->del($tag, $uid);

			js_alert_showmsg("已取消话题关注");
		} else {
			$rets = jlogic('tag_favorite')->add($tag, $uid);

			if($rets['error'] && $rets['msg']) {
				js_alert_showmsg($rets['msg']);
			} else {
				if('input_add' != $act) {
					js_alert_showmsg('话题关注成功');
				} else {
					$my_favorite_tags = jlogic('tag_favorite')->my_favorite($uid, 12);
										include(template('tag/tag_favorite_ajax'));
				}
			}
		}
	}


		function Forward_Menu()
	{
		$tid = jget('tid','int','P');
		$forward_topic = $this->TopicLogic->Get($tid);

				$returncode = $this->Post['r'];

		if($forward_topic['roottid'])
		{
			$forward_topic = $this->TopicLogic->Get($forward_topic['roottid']);
		}

		$forward_tid = $forward_topic['tid'];
		if(!$forward_tid){
			json_error("抱歉，此微博已经被删除，无法进行转发哦，请试试其他内容吧。");
		}

                $member_info = jsg_member_info($forward_topic['uid']);
        $forward_topic['nickname'] = $member_info['nickname'];
		$forward_topic['content'] = str_replace(' onload="this.click();"','',$forward_topic['content']);
				include(template('topic_forward_menu'));
	}


		function Do_forward()
	{
		if (MEMBER_ID < 1) {
			response_text("请登录");
		}

				if($this->MemberHandler->HasPermission('topic','forward')==false) {
			response_text("您的角色没有转发的权限");
		}

		$content = strip_tags($this->Post['content']);

		$totid  		= (int) $this->Post['tid'];
		$imageid = trim($this->Post['imageid']);
		$attachid = trim($this->Post['attachid']);

		$type = $this->Post['topictype'];
		$from = 'web';

				if($this->Config['seccode_enable']) {
			$YXM_check = jlogic('seccode')->topiccheckYXM($type);
			if ($YXM_check && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
				$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['YinXiangMa_challenge'],@$_POST['YXM_level'][0],@$_POST['YXM_input_result']);
				if($YinXiangMa_response != "true"){
					response_text("验证码输入错误");
				}
			}
		}

		$is_reward = $this->Post['is_reward'];
		
		$item = trim($this->Post['item']);
		$item_id  = intval(trim($this->Post['item_id']));

		#为有奖转发添加小尾巴
		if($is_reward){
						            $reward = jlogic("reward")->getRewardInfo($is_reward);
                        foreach($reward["rules"]["user"] as $value){
                $uid = DB::result_first("select uid from `".TABLE_PREFIX."members` where nickname = '$value[nickname]' ");
                !$uid||buddy_add($uid);
            }
		}
		if (!empty($item_id)) {
						jfunc('app');
			$ret = app_check($item, $item_id);
			if (!$ret) {
				$item = '';
				$item_id = 0;
			} else {
				$from = $item;
			}
		} else {
			$item = '';
			$item_id = 0;
		}

		$data = array(
			'content' => $content,
			'totid'=>$totid,
			'imageid'=>$imageid,
			'attachid'=>$attachid,
			'from'=>$from,
			'type'=>$type,

					'item' => $item,
			'item_id' => $item_id,
			#有奖转发标记
			'is_reward' => $is_reward,
		);

		$return = $this->TopicLogic->Add($data);

		if (is_array($return) && $return['tid'] > 0)
		{
			response_text('<success></success>');
		}
		else
		{
			$return = (is_string($return) ? "[转发失败]".$return : (is_array($return) ? "[转发成功]但".implode("",$return) : "未知错误"));
			response_text("{$return}");

								}
	}

		function TopicShow()
	{
		$uid = MEMBER_ID;

		$sql = "select `uid` from `".TABLE_PREFIX."topic_show` where `uid` =  '{$uid}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$showlist = array();
		while (false != ($row = $query->GetRow())) {
			$showlist[] = $row;
		}


		$styleData = array(
			'titleColor' 	=> ($this->Post['titleColor'] ? $this->Post['titleColor'] : $this->Get['titleColor']),
			'width' 		=> ($this->Post['width'] ? $this->Post['width'] : $this->Get['width']),
			'height' 		=> ($this->Post['height'] ? $this->Post['height'] : $this->Get['height']),
			'bgColor' 		=> ($this->Post['bgColor'] ? $this->Post['bgColor'] : $this->Get['bgColor']),
			'textColor' 	=> ($this->Post['textColor'] ? $this->Post['textColor'] : $this->Get['textColor']),
			'linkColor' 	=> ($this->Post['linkColor'] ? $this->Post['linkColor'] : $this->Get['linkColor']),
			'borderColor'	=> ($this->Post['borderColor'] ? $this->Post['borderColor'] : $this->Get['borderColor']),
			'showFans' 		=> ($this->Post['showFans'] ? $this->Post['showFans'] : $this->Get['showFans']),
			'isFans' 		=> (int) ($this->Post['isFans'] ? $this->Post['isFans'] : $this->Get['isFans']),
			'isTopic' 		=> (int) ($this->Post['isTopic'] ? $this->Post['isTopic'] : $this->Get['isTopic']),
			'isTitle' 		=> (int) ($this->Post['isTitle'] ? $this->Post['isTitle'] : $this->Get['isTitle']),
			'isBorder'		=> (int) ($this->Post['isBorder'] ? $this->Post['isBorder'] : $this->Get['isBorder']),
		);

				if($showlist){

			$sql = "update `".TABLE_PREFIX."topic_show` set `stylevalue`='".serialize($styleData)."'  where `uid`='{$uid}'";
			$this->DatabaseHandler->Query($sql);

		} else{

			$sql = "insert into `".TABLE_PREFIX."topic_show` (`uid`,`stylevalue`) values ('{$uid}','".serialize($styleData)."')";
			$this->DatabaseHandler->Query($sql);
		}

		echo "<script language='Javascript'>";
		echo "location.replace('index.php?mod=show&code=show');";
		echo "</script>";
		exit;
	}


		function User_Tag()
	{
		$uid 	 	= (int) MEMBER_ID;
		$tagid 		= (int) $this->Post['tagid'];
		$tag_name 	= strip_tags($this->Post['tag_name']);
		$addtime 	= time();

		if($uid < 1)
		{
			js_alert_output("请先登录或者注册一个帐号");
		}

				$f_rets = filter($tag_name);
		if($f_rets && $f_rets['error'])
		{
			js_alert_output($f_rets['msg']);
		}

				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
		$total_record = DB::result_first($sql);


		if($total_record >= 10)
		{
			js_alert_output('最多只能设置10个标签');
		}

				if($this->Post['types'] == 'add')
		{
			if(empty($tag_name))
			{
				js_alert_output('请输入标签');
			}

			$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `tag_name` = '{$tag_name}' and `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

			if(!empty($row))
			{
				js_alert_output($tag_name.' 标签已经打上');
			}

						$sql = "select * from `".TABLE_PREFIX."user_tag` where `name` = '{$tag_name}'";
			$query = $this->DatabaseHandler->Query($sql);
			$usertag = $query->GetRow();
			if(empty($usertag))
			{
								$sql = "insert into `".TABLE_PREFIX."user_tag`(`name`,`dateline`) values ('{$tag_name}','{$addtime}')";
				$this->DatabaseHandler->Query($sql);
				$tag_insertid = $this->DatabaseHandler->Insert_ID();
			}

			$tag_insertid = $tag_insertid ? $tag_insertid :$usertag['id'];

						$sql = "insert into `".TABLE_PREFIX."user_tag_fields`(`tag_id`,`uid`,`tag_name`) values ('{$tag_insertid}','{$uid}','{$tag_name}')";
			$this->DatabaseHandler->Query($sql);
			$tag_fields_id = $this->DatabaseHandler->Insert_ID();

		}


				if($this->Post['types'] == 'useradd')
		{
			$sql = "select `tag_name` from `".TABLE_PREFIX."user_tag_fields` where `tag_name`='{$tag_name}' and `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();

			if(!empty($row))
			{
				js_alert_output('标签 '.$tag_name.' 已经打上');
			}


			$sql = "insert into `".TABLE_PREFIX."user_tag_fields`(`tag_id`,`uid`,`tag_name`) values ('{$tagid}','{$uid}','{$tag_name}')";
			$this->DatabaseHandler->Query($sql);
			$tag_fields_id = $this->DatabaseHandler->Insert_ID();

		}

		$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `id` = '{$tag_fields_id}' and `uid` = '".MEMBER_ID."' limit 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$user_tag_fields[]=$query->GetRow();

				$sql = "update `".TABLE_PREFIX."user_tag` set `count`=`count`+1 where `id`='{$tagid}'";
		$this->DatabaseHandler->Query($sql);

		include(template('user_tag_ajax'));
	}

		function Del_Tag()
	{
		$uid 		= (int) MEMBER_ID;
		$tag_id 	= (int) get_param('tag_id');

		

		if($uid > 0 && $tag_id > 0) {
			$info = DB::fetch_first("select * from `".TABLE_PREFIX."user_tag_fields` where `tag_id`='{$tag_id}' and `uid` = '{$uid}'");
			if($info) {
				$sql = "delete from `".TABLE_PREFIX."user_tag_fields` where `tag_id`='{$tag_id}' and `uid` = '{$uid}'";
				$this->DatabaseHandler->Query($sql);

				$sql = "update `".TABLE_PREFIX."user_tag` set `count`=if(`count`>1,`count`-1,0) where `id`='{$tag_id}'";
				$this->DatabaseHandler->Query($sql);
			}
		}

		include(template('user_tag_ajax'));
	}

		function Del_Group()
	{
		$uid 		= (int) MEMBER_ID;
		$group_id 	= (int) get_param('group_id');

		if($uid > 0 && $group_id > 0) {
			$rets = jtable('buddy_follow_group')->del($group_id);
			if($rets && $rets['error']) {
				exit($rets['msg']);
			}
		}
	}

		function DoVideo()
	{
		$url = $this->Post['url'];

		preg_match_all('~(?:https?\:\/\/)(?:[A-Za-z0-9_\-]+\.)+[A-Za-z0-9]{2,4}(?:\/[\w\d\/=\?%\-\&_\~`@\[\]\:\+\#]*(?:[^<>\'\"\n\r\t\s])*)?~',$url,$match);

		if (empty($match))
		{
			js_alert_output('输入正确的视频地址');
		}

		$ext = trim(strtolower(substr($url,strrpos($url,'.'))));

				$return = array();
		if('.swf'==$ext)
		{
						$return = array
			(
				'id' => $url,
				'host' => 'flash',
				'url' => $url,
				'title' => $url,
			);
		}
		else
		{
			$return = $this->TopicLogic->_parse_video($url);
		}

				$return_content = (
		$return['title'] ?
		$return['title'] . (
		$this->Config['video_status'] ?
				"" :
				" $url"
		)
		: '分享链接  '.$url
		);

		$return_content = str_replace(array("\r\n", "\n\r", "\n", "\r"), " ", $return_content);

		if ($return)
		{
			$video_link 	= $return['id'];
			$video_hosts 	= $return['host'];
			$video_url		= $return['url'];
			$video_img = '';
			if($return['image_src'])
			{
				$return_video_img = $this->TopicLogic->_parse_video_image($return['image_src']);
				$return['image_local'] = $return_video_img['img'];
			}
			$video_img = $return['image_local'];
			$video_img_url = '';
			if($video_img)
			{
				$video_img_url = ($this->Config['ftp_on'] ? $return_video_img['url'] : "");
			}
			$timestamp 		= time();

			$sql = "insert into `".TABLE_PREFIX."topic_video`
			(`uid`,`tid`,`username`,`video_hosts`,`video_link`,`video_url`,`video_img`,`video_img_url`,`dateline`)
			values
			('".MEMBER_ID."','".''."','".MEMBER_NAME."','".$video_hosts."','".$video_link."','".$video_url."','".$video_img."','$video_img_url','".$timestamp."')";
			$this->DatabaseHandler->Query($sql);

			$videoid = $this->DatabaseHandler->Insert_ID();
            if($this->Config['ftp_on']){
                $video_img = ltrim($video_img, '.');
            }
			if($video_img) $video_img_src = $video_img_url . $video_img;


			if(empty($video_img_src))
			{
				$video_img_src = 'images/vd.gif';
			}
						echo "<script language='Javascript'>";
			echo "parent.videoid={$videoid};";
			echo "parent.document.getElementById('upload_video_list').style.display='block';";
			echo "parent.document.getElementById('add_video').style.display='none';";
			echo "parent.document.getElementById('videoid').value='".$videoid."';";
			echo "parent.document.getElementById('video_img').src='".$video_img_src."';";
			echo "parent.document.getElementById('url').value='';";
			echo "parent.document.getElementById('i_already').value=parent.document.getElementById('i_already').value + ' ".$return_content." ';";
			echo "parent.document.getElementById('return_ajax_video_title').innerHTML='[".cut_str($return_content,36)."]';";
			echo "parent.document.getElementById('publishSubmit').disabled=false;";
			echo "parent.document.getElementById('i_already').focus();";
			echo "</script>";
		}
		else
		{

			echo "<script language='Javascript'>";
			echo "parent.document.getElementById('add_video').style.display='none';";
			echo "parent.document.getElementById('url').value='';";
			echo "if(''==parent.document.getElementById('i_already').value){parent.document.getElementById('i_already').value='".$return_content."';}";
			echo "parent.document.getElementById('publishSubmit').disabled=false;";
			echo "parent.document.getElementById('i_already').focus();";
			echo "</script>";
		}

	}

		function DeleteVideo() {
		if ($this->ID < 1 || false == (jlogic('video')->get_info($this->ID))) {
			response_text("视频已经不存在了");
		}
		if(jallow($topic_video['uid'])) {
			jlogic('video')->delete($this->ID);
		} else {
			response_text("您没有删除这个视频的权限");
		}
	}

		function DeleteMusic()
	{
		if($this->ID > 0) {
			$sql = "select * from `".TABLE_PREFIX."topic_music` where `id`='".$this->ID."' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_music=$query->GetRow();
		}

		if (!$topic_music) {
			response_text("音乐已经不存在了");
		}

		if(jallow($topic_music['uid']))
		{
			$sql = "delete from `".TABLE_PREFIX."topic_music` where `id`='{$this->ID}'";
			$this->DatabaseHandler->Query($sql);

			$updata = "update `".TABLE_PREFIX."topic` set `musicid`='0' where `tid`='{$topic_music['tid']}'";
			$result = $this->DatabaseHandler->Query($updata);
		}
		else
		{
			response_text("您没有删除这个音乐的权限");
		}

	}


		function Open_Mdeal_Index()
	{
		$medalid = (int) $this->Post['medalid'];

				$sql = "select is_index from `".TABLE_PREFIX."user_medal` where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		$show = DB::result_first($sql);

		if($show){
			$sql = "update `".TABLE_PREFIX."user_medal` set  `is_index`='0' where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		}else{
			$sql = "update `".TABLE_PREFIX."user_medal` set  `is_index`='1' where `medalid` = '{$medalid}' and uid = '".MEMBER_ID."'";
		}
		$this->DatabaseHandler->Query($sql);
		json_result("1");
	}

		function ShowLogin()
	{
		$referer = referer('?');
		$enreferer = urlencode($referer);
		$noemail = 0;
		$isreg = jget('isreg','int');
		$regstatus = jsg_member_register_check_status();
		$reg_error_msg = '';
		if(!$regstatus['normal_enable']){
			if($regstatus['invite_enable']){
				$reg_error_msg = "本站只支持邀请注册。您只能通过邀请链接地址进行注册！<br><br>如何获取邀请链接地址，请咨询网站管理员！";
			}else{
				$reg_error_msg = "本站已关闭注册功能。";
			}
		}
		if($reg_error_msg){
			$reg_error_msg .= "<br><br>您也可以通过右边的第三方帐号直接进行登陆";
		}
		include(template('login/login_ajax_show'));
	}

		function Do_Reg_Follow_User()
	{

				$follow_type = $this->Post['followType'] ;

				$_limit = $this->Post['list_limit'] ? $this->Post['list_limit'] + 15 : '15';

		$list = array();

				if ($follow_type == 'recommend') {

			$day = 7;
			$time = $day * 86400;
			$limit = (int) $this->ShowConfig['reg_follow']['user'];
			if($limit < 1) $limit = 20;

			$regfollow = jconf::get('regfollow');
						for ($i = 0; $i < count($regfollow); $i++)
			{
				if($regfollow[$i] == '')
				{
					unset($regfollow[$i]);
				}
			}
			if (!empty($regfollow)) {
				$count = count($regfollow);
				if ($count > $limit) {
					$keys = array_rand($regfollow, $limit);
					foreach ($keys as $k) {
						$uids[] = $regfollow[$k];
					}
				} else {
					$uids = $regfollow;
				}
			} else {

								$cache_id = "misc/RTU-{$day}-{$limit}";
				if (false === ($uids = cache_file('get', $cache_id))) {
					$dateline = time() - $time;
					$sql = "SELECT DISTINCT(uid) AS uid, COUNT(tid) AS topics FROM `".TABLE_PREFIX."topic` WHERE dateline>=$dateline GROUP BY uid ORDER BY topics DESC LIMIT {$limit}";

															$query = $this->DatabaseHandler->Query($sql);
					$uids = array();
					while (false != ($row = $query->GetRow()))
					{
						$uids[$row['uid']] = $row['uid'];
					}

					cache_file('set', $cache_id, $uids, 900);
				}

			}

		}
				elseif ($follow_type == 'huoyue') {

			$sql = "select DISTINCT(T.username) AS username , T.uid AS uid , COUNT(T.tid) AS count from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.uid=M.uid WHERE T.dateline>='".(time() - 86400 * 7)."' and M.face!='' GROUP BY username ORDER BY count DESC LIMIT 0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids =  array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['uid']] = $row['uid'];
			}

		}
				elseif ($follow_type == 'renqi') {
			$sql = "select DISTINCT(B.uid) AS uid, COUNT(B.touid) AS `count`
				FROM ".DB::table(jtable('buddy_fans')->table_name(max(1, MEMBER_ID)))." B
					LEFT JOIN `".TABLE_PREFIX."members` M on (M.uid=B.uid)
				WHERE B.dateline>='".(TIMESTAMP - 86400 * 7)."' AND M.face!=''
				GROUP BY B.uid
				ORDER BY `count` DESC
				LIMIT {$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['uid']] = $row['uid'];
			}
					}
				elseif ($follow_type == 'yingxiang') {

			$sql = "select DISTINCT(T.tousername) AS username ,  COUNT(T.tid) AS count, M.face ,M.username,M.uid from `".TABLE_PREFIX."topic` T left join `".TABLE_PREFIX."members` M on T.tousername=M.username WHERE M.face !='' and  T.dateline>='".(time() - 86400 * 7)."' and T.touid > 0  GROUP BY tousername ORDER BY count DESC LIMIT 0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$uids = array();
			while (false != ($row = $query->GetRow()))
			{
				$uids[$row['uid']] = $row['uid'];
			}
			
		}

		if($uids)
		{
			$_list = $this->TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face_url`,`face`,`validate`,`nickname`,`aboutme`");
			foreach ($uids as $uid) {
				if ($uid > 0 && isset($_list[$uid]) && $uid!=MEMBER_ID) {
					$list[$uid] = $_list[$uid];
				}
			}
						$user_count = $list ? count($list) : '0';

		} else {
			;
		}


				if ($follow_type == 'tag')
		{

			
			

			$sql = "select * from `".TABLE_PREFIX."tag_recommend` order by `id` desc limit  0,{$_limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$tag_name = array();
			while (false != ($row = $query->GetRow()))
			{
				$tag_name[$row['name']] = $row['name'];
			}

						if($tag_name)
			{
				$query = DB::query("SELECT `id`,`name` FROM ".DB::table('tag')." where `name` in ('".implode("','", $tag_name)."') order by `id` desc limit 0,{$_limit} ");
				$tag_list = array();
				while (false != ($row = DB::fetch($query)))
				{
					$tag_list[] = $row;
				}

			}
			$tag_count = count($tag_list);

		}

		include(template('reg_follow_user_ajax'));
	}


	
	function Modify_User_Signature()
	{
		$uid = (int) $this->Post['uid'];
		if($uid < 1) {
			showjsmessage("请先登录或者注册一个帐号");
		}
				if(jdisallow($uid)) {
			json_error("您无权修改此用户签名");
		}

				$rets = jclass('misc')->sign_modify($uid, $this->Post['signature']);
		if(is_array($rets) && $rets['error']) {
			json_error($rets['msg']);
		} else {
			json_result($rets);
		}
	}

	
	function _AddBlackList($uid=0,$touid=0,$types='')
	{
		$uid = (is_numeric($uid) ? $uid : 0);
		if ($uid < 1) {
			json_error("请先登录或者注册一个帐号");
		}
		$touid = (is_numeric($touid) ? $touid : 0);
		if($touid < 0) {
			json_error("请指定一个用户ID");
		}

				if('add' == $types) {
			if($touid == $uid) {
				json_error('不能拉黑自己');
			}

			jlogic('buddy')->add_blacklist($touid, $uid);
		}

				if('del' == $types) {
			jlogic('buddy')->del_blacklist($touid, $uid);
		}

		$follow_html = follow_html($touid);
		return $follow_html;
	}

		function _recd_levels($type = 'all')
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$recd_levels = $TopicRecommendLogic->recd_levels($type);
		return $recd_levels;
	}


		function recd()
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$tid = intval($this->Get['tid']);
		$tag_id = intval($this->Get['tag_id']);

				$topic = DB::fetch_first("SELECT * FROM ".DB::table("topic")." WHERE tid='{$tid}'");
		if (empty($topic)) {
			json_error("当前微博不存在或者已经被删除了");
		}
		if($topic['item'] == 'channel' && $topic['item_id'] > 0){
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topic = item_topic_from($topic);
		}
		if(!($this->MemberHandler->HasPermission('topic','do_recd') || $topic['ismanager'])) {
			json_error("您的角色没有推荐微博的权限！");
		}

		
		$topic_recd = $TopicRecommendLogic->get_info($tid);
		if (!empty($topic_recd)) {
			$topic_recd['expiration'] = empty($topic_recd['expiration']) ? '' : my_date_format($topic_recd['expiration'], 'Y-m-d H:i');
			$recd_html = jform()->Radio('recd[]',array(array("name"=>"重点推荐","value"=>"4","title"=>"在我的首页和指定的位置推荐"),array("name"=>"普通推荐","value"=>"2","title"=>"仅在指定的页面位置推荐"),array("name"=>"取消推荐","value"=>"0","title"=>"恢复为常态微博")),$topic_recd['recd'],'title');
		}else{
			$recd_html = jform()->Radio('recd[]',array(array("name"=>"重点推荐","value"=>"4","title"=>"在我的首页和指定的位置推荐"),array("name"=>"普通推荐","value"=>"2","title"=>"仅在指定的页面位置推荐")),2,'title');
		}
		$channels = array();
		if($this->Config['channel_enable']){
			$channels = $TopicRecommendLogic->recd_channels();
		}

		
		if ($topic['item'] == 'qun' && $topic['item_id'] > 0) {
						Load::logic('qun');
			$QunLogic = new QunLogic();
			$tmp_perm = $QunLogic->chk_perm($topic['item_id'], MEMBER_ID);
			if(!('admin' == MEMBER_ROLE_TYPE || in_array($tmp_perm, array(1,2)))){
				json_error("你没有权限推荐群内微博");
			}
		}
		include(template("topic_recd"));
	}

		function do_recd()
	{
		Load::logic('topic_recommend');
		$TopicRecommendLogic = new  TopicRecommendLogic();
		$tid = intval($this->Post['tid']);
				$topic = DB::fetch_first("SELECT * FROM ".DB::table("topic")." WHERE tid='{$tid}'");
		if (empty($topic)) {
			json_error("当前微博不存在或者已经被删除了");
		}
		if($topic['item'] == 'channel' && $topic['item_id'] > 0){
			if(!function_exists('item_topic_from')) {
				jfunc('item');
			}
			$topic = item_topic_from($topic);
		}
		if(!($this->MemberHandler->HasPermission('topic','do_recd') || $topic['ismanager'])) {
			json_error("您的角色没有推荐微博的权限！");
		}
		$recd = intval($this->Post['recd'][0]);
				if ($recd>4 || $recd < 0) {
			json_error("推荐类型错误");
		}

		
		if ($topic['item'] == 'qun' && $topic['item_id'] > 0) {
						Load::logic('qun');
			$QunLogic = new QunLogic();
			$tmp_perm = $QunLogic->chk_perm($topic['item_id'], MEMBER_ID);
			if(!('admin' == MEMBER_ROLE_TYPE || in_array($tmp_perm, array(1,2)))){
				json_error("你没有权限推荐群内微博");
			}
		}

		if ($recd == 0) {
			$topic_recd = $TopicRecommendLogic->delete(array($tid));
			json_result("0|||取消推荐成功！");
		} else {
			$expiration = jstrtotime(trim($this->Post['expiration']));
			$tag_id = intval($this->Post['tag_id']);
			$r_title = strip_tags(trim($this->Post['r_title']));
			if (!empty($tag_id)) {
								$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_tag')." WHERE item_id='{$tid}' AND tag_id='{$tag_id}' ");
				if(!empty($count)){
					$topic['item'] = 'tag';
					$topic['item_id'] = $tag_id;
				}
			}
			$item = $this->Post['item_id'] ? 'channel' : $topic['item'];
			$item_id = $this->Post['item_id'] ? intval($this->Post['item_id']) : $topic['item_id'];
			if(empty($r_title)){
				json_error("推荐标题为空或内容不合法");
			}
			if($expiration && $expiration < time()){
				json_error("时间设置无效，请重新设置");
			}
			$data = array(
				'expiration' => $expiration,
				'item' => $item,
				'item_id' => $item_id,
				'tid' => $tid,
				'recd' => $recd,
				'dateline' => TIMESTAMP,
				'r_uid' => MEMBER_ID,
				'r_nickname' => MEMBER_NICKNAME,
				'r_title' => $r_title,
			);
			if($TopicRecommendLogic->is_exists($tid)){
				unset($data['tid']);
				$TopicRecommendLogic->modify($data,array('tid'=>$tid));
				json_result("2|||重新推荐成功！");
			}else{
				$TopicRecommendLogic->add($data);
								feed_msg('recommend','recommend',$tid,$r_title,$item_id);
								if($recd == 4){
					$iphone_msg = cut_str($topic['content'], 30, '');
					ios_push_msg('all',$r_title.'：'.$iphone_msg);
				}
								$credits = jconf::get('credits');
				update_credits_by_action('recommend',$topic['uid']);
				json_result("1|||推荐成功！被推荐者因:".$this->js_show_msg(1));
				

			}
		}
	}

		function publishSuccess()
	{
		echo $this->js_show_msg();
		#if NEDU
		defined('NEDU_MOYO') && nlogic('feeds.app.jsg')->on_ajax_topic_published();
		#endif
	}


		function Check_Medal_List()
	{

		$types = 'user_type_medal';

		$uid = (int) $this->Post['uid'];
		$medal_id = (int) $this->Post['medal_id'];

		$medal_type = $this->Post['medal_type'];

		$medalinfo = $this->TopicLogic->GetMedal($medal_id,$uid);
		foreach ($medalinfo as $v)
		{
			$medalinfo = $v;
		}

				include(template('user_follower_menu'));

	}

		function Photo() {
		$page = (int)$_POST['page'];
		$uid = max(0,(int)$_POST['uid']);
		$photo_num = 20; 		$p_ajax_num = 12; 		if($page < 0){return false;}
				$total_page = ($this->Config['total_page_default'] ? $this->Config['total_page_default'] : 100);
		if($page > $total_page) {
			return false;
		}
		$i = $page*$p_ajax_num + $photo_num;
		$p = array(
			'count' => $p_ajax_num,
			'vip' => $this->Config['only_show_vip_topic'],
			'limit' => $i.','.$p_ajax_num,
			'uid' => $uid,
		);
		$info = jlogic('topic_list')->get_photo_list($p);
		$topic_list = array();
		if (!empty($info)) {
			$total_photo = $info['count'];
			$topic_list = $info['list'];
		}
		if($topic_list){
			if($this->Config['attach_enable']){$allow_attach = 1;}else{$allow_attach = 0;}
			include(template('topic_photo_ajax'));
		}
	}

}

?>
