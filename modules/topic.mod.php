<?php
/**
 * 文件名：topic.mod.php
 * @version $Id: topic.mod.php 5798 2014-08-29 06:25:05Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博话题模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;

	var $ID = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = jget('id', 'int');

		$this->TopicLogic = jlogic('topic');
		$this->ChannelLogic = jlogic('channel');

		$this->CacheConfig = jconf::get('cache');

		$this->ShowConfig = jconf::get('show');

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		if('normal' == $this->Code){
			$this->normalIndex();
		}elseif('simple' == $this->Code){
			$this->simpleIndex();
		}elseif('only_login' == $this->Code){
			$this->only_login_index();
		}elseif('channellogin' == $this->Code){
			$this->channelIndex();
		} elseif ('group' == $this->Code) {
			$this->ViewGroup();
		} elseif ('view' == $this->Code) {
			$this->View();
		} elseif ('photo' == $this->Code) {
			$this->Photo();
		} elseif (is_numeric($this->Code)) {
			$this->ID = (int) $this->Code;
			$this->View();
		} else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
				$code_ary = array (
			'myblog',
			'myhome',
			'tag',
			'qun',
			'recd',
			'other',
			'bbs',
			'cms',
			'department',
			'company',
			'channel',
			'topicnew',
			'bcj',		);
		if(!in_array($this->Code,$code_ary)) {
			$tos = array('new' => 'index.php?mod=plaza',
				'tc' => 'index.php?mod=plaza&code=new_tc',
				'hotreply' => 'index.php?mod=plaza&code=hot_reply',
				'hotforward' => 'index.php?mod=plaza&code=hot_forward',
				'newreply' => 'index.php?mod=plaza&code=new_reply',
				'newforward' => 'index.php?mod=plaza&code=new_forward',
				'top' => 'index.php?mod=top&code=member',
				'channellogin' => 'index.php?mod=channel',
			);
			if(isset($tos[$this->Code])) {
				$this->Messager(null, $tos[$this->Code]);
				exit;
			}
			

			unset($this->Code);
		}

		$channel_enable = jconf::get('channel') ? true : false;
		$content_dstr = $this->Config['in_publish_notice_str'];
		$content_ostr = $this->Config['on_publish_notice_str'];
				if ('topic'==trim($this->Get['mod']) && empty($this->Get['code']) && empty($this->Get['mod_original'])) {
						if (MEMBER_ID > 0) {
				if($this->Config['topic_home_page']) {
					$this->Messager(null, $this->Config['topic_home_page']);
				} else {
					$this->Code = 'myhome';
				}
			} else {
				$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
				return ;
			}
		}

				$member = $this->_member();
		if(!$member) {
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
			return false;
		}

		$title = '';
				$per_page_num = 20;
		$topic_uids = $topic_ids = $order_list = $where_list = $params = array();
		$where = $order = $limit = "";
		$cache_time = 0;
		$cache_key = '';

				$options = array();
		$gets = array(
			'mod' => ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module),
			'code' => $this->Code,
			'type' => $this->Get['type'],
			'gid' => $this->Get['gid'],
			'qid' => $this->Get['qid'],
			'chid' => $this->Get['chid'],
			'view' => $this->Get['view'],
			'filter' => $this->Get['filter'],
			'orderby' => $this->Get['orderby'],
		);
		$options['page_url'] = "index.php?".url_implode($gets);
		unset($gets['type']);
		$type_url = "index.php?".url_implode($gets);

		$params['uid'] = $uid = $member['uid'];

				$is_personal = ($uid == MEMBER_ID);
		$params['is_personal'] = $is_personal;

		$params['code'] = $this->Code;

		if (!in_array($params['code'], $code_ary)) {
						$params['code'] = 'myblog';
		}

				$page_str = $params['code'];
		if($params['code'] == 'bbs' || $params['code'] == 'cms') {
			$page_str = 'myhome'; 		}
		if (($show_topic_num = (int) $this->ShowConfig['topic'][$page_str]) > 0) {
			$per_page_num = $show_topic_num;
		}

		$options['perpage'] = $per_page_num;

				$groupname = '';
		$groupid = 0;

		$TopicListLogic = jlogic('topic_list');

		#if NEDU
		if (defined('NEDU_MOYO'))
		{
			nui('jsg')->cindex($this, $params, $topic_list_get);
		}
		#endif

		
		$sendMail = false;
		$checkUser = false;
		if($this->Config['sendmailday'] > 0) {
			if(!jsg_getcookie('mail_cookie')) {
				jsg_setcookie('mail_cookie', TIMESTAMP, 300);
				$sendMail = true;
			}
			if(!jsg_getcookie('check_user')) {
				jsg_setcookie('check_user', TIMESTAMP, 86400);
				$checkUser = true;
			}
		}

				$tpl = 'topic_index';
		if ('myhome'==$params['code']) {
			$tpl = 'topic_myhome';
			
			$topic_selected = 'myhome';

						$type = get_param('type');
			if($type && !in_array($type, array('pic', 'video', 'music', 'vote', 'event', ))) {
				$type = '';
			}
			if($type) {
				$params['type'] = $type;
			}

						$gid = max(0, (int) get_param('gid'));
			if($gid) {
				$params['gid'] = $gid;
			}

						$topic_myhome_time_limit = 0;
			if($this->Config['topic_myhome_time_limit'] > 0) {
				$topic_myhome_time_limit = (TIMESTAMP - ($this->Config['topic_myhome_time_limit'] * 86400));
				if ($topic_myhome_time_limit > 0) {
					$options['dateline'] = $topic_myhome_time_limit;
				}
			}

						$options['uid'] = array($member['uid']);
			if ($member['uid'] == MEMBER_ID) {
				$cache_time = 600;
				$cache_key = "{$member['uid']}-topic-myhome-{$type}-{$gid}";
				$title = '我的首页';
				
								$refresh_time = max(30, (int) $this->Config['ajax_topic_time']);
				if(get_param('page') < 2 && ($member['lastactivity'] + $refresh_time < TIMESTAMP)) {
					$new_topic = jlogic('buddy')->check_new_topic($uid, 1);
					if($new_topic > 0) {
						cache_db('rm', "{$uid}-topic-%", 1);
					}
				}

								if ($gid) {
					$group_info = jtable('buddy_follow_group')->info($gid);
					if (empty($group_info) || MEMBER_ID != $group_info['uid']) {
						$this->Messager("当前分组不存在", 'index.php?mod=myhome');
					}
					$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&code={$this->Code}&type={$this->Get['type']}&gid={$this->Get['gid']}" : "");
					if($group_info['count'] > 0) {
						$g_view_uids = jtable('buddy_follow_group_relation')->get_my_group_uids(MEMBER_ID, $gid);
					}
					$groupid = $gid;
					$groupname = $group_info['name'];

										if($g_view_uids) {
						$options['uid'] = $g_view_uids;
					} else {
						$this->Messager("没有设置用户，无法查看这个组的微博",'index.php?mod=topic&code=group&gid='.$gid);
					}
					$active[$gid] = "current";
				} else {
					if($type || false === (cache_db('get', $cache_key))) {
						$buddyids = get_buddyids($params['uid'], $this->Config['topic_myhome_time_limit']);
						if($buddyids) {
							$options['uid'] = array_merge($options['uid'], $buddyids);
						}
					}

					$active['all'] = "current";
				}
			} else {
				$title = "{$member['nickname']}的微博";
				$this->_initTheme($member);
			}

						if($type) {
				$getTypeTidReturn = $TopicListLogic->GetTypeTid($type,$options['uid'],$options);
				$options['tid'] = $getTypeTidReturn['tid'];
				$options['count'] = $getTypeTidReturn['count'];
				$options['limit'] = $per_page_num;
			}
		} else if ('channel' == $params['code'] && $this->Channel_enable && $this->Config['channel_enable']) {
			$viewtype = jget('view');
			$ch_id = jget('cid');
			$title = '我的频道微博';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}
						jlogic('member')->clean_new_remind('channel_new', MEMBER_ID);

			$orderby = in_array($this->Get['orderby'],array('post','dig','mark','ldig','top')) ? $this->Get['orderby'] : 'dig';
			$ChannelLogic = jlogic('channel');
			$my_buddy_channel = $ChannelLogic->mychannel();
			$cachefile = jconf::get('channel');
			$channel_channels = $cachefile['channels'];
			$channel_ids = array();
			if(empty($my_buddy_channel)){
				$channel_ids = array(0);
			}else{
				$my_chs = array_keys($my_buddy_channel);
				foreach($my_chs as $val){
					$channel_ids = array_merge($channel_ids,(array) $channel_channels[$val]);
				}
				if(is_array($channel_ids)) {
					$channel_ids = array_unique($channel_ids);
				}
			}
			$my_cannot_view_chids = jlogic('channel')->get_my_cannot_view_chids();
			if($my_cannot_view_chids){
				foreach($channel_ids as $key => $val){
					if(in_array($val,$my_cannot_view_chids)){
						unset($channel_ids[$key]);
					}
				}
			}
			$options['item'] = 'channel';
			$options['item_id'] = $channel_ids;
			$options['type'] = array('first','channel');
			if($orderby == 'mark'){
				$order = ' lastupdate DESC';
			}elseif($orderby == 'dig'){
				$order = ' lastdigtime DESC';
			}elseif($orderby == 'ldig'){
				$order = ' digcounts DESC,lastdigtime DESC';
			}else{
				$order = ' dateline DESC';
			}
			$options['order'] = $order;
			if($orderby == 'top'){
				$where = $channel_ids ? "tr.item = 'channel' AND tr.item_id IN(".implode(",",$channel_ids).")" : "tr.item = 'channel'";
				$options = array('where'=>$where,'perpage'=>$per_page_num);
				$info = $TopicListLogic->get_recd_list($options);
				if (!empty($info)) {
					$total_record = $info['count'];
					$topic_list = $info['list'];
					$page_arr = $info['page'];
					}
				$topic_list_get = true;
			}
		}  else if('company' == $params['code'] && $this->Config['company_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')) {
			$title = '我的单位微博';
			if($member['companyid']<1){
				$this->Messager("您还不属于任何".$d_c_name."，请先加入某个".$d_c_name."！",'index.php?mod=settings&code=base');
			}

						jlogic('member')->clean_new_remind('company_new', MEMBER_ID);

			$viewtype = jget('view');
			$options['uid'] = jlogic('cp')->getcpuids('company',$member['companyid']);
			if($viewtype == 'secret'){
				$options['type'] = 'company';
			}
		}  else if('bcj' == $params['code'] && $this->Channel_enable && $this->Config['channel_enable']) {			$ch_id = jget('cid','int');
			$featureid = jget('view','int');
			if($ch_id == 3){
				$title = '建议';
			}elseif($ch_id == 2){
				$title = '提问';
			}else{
				$title = '资讯';
				if(!in_array($ch_id,array('4','6'))){
					$ch_id = 5;
				}
			}
			if(!in_array($featureid,array('1','2','3'))){
				$featureid = 0;
			}
			$options['item'] = 'channel';
			$options['item_id'] = $ch_id;
			$my_cannot_view_chids = jlogic('channel')->get_my_cannot_view_chids();
			if($my_cannot_view_chids && in_array($ch_id,$my_cannot_view_chids)){
				$forbidden_view = true;
				$options['tid'] = 0;
			}
			$options['page_url'] .= "&cid=$ch_id";
			if(isset($_GET['view'])){
				$options['where'] = " featureid = '{$featureid}' ";
				$options['page_url'] .= "&view=$featureid";
			}

		}  else if('department' == $params['code']) {
			$tpl = 'topic_department';
			$title = ($this->Config['default_department'] ? $this->Config['default_department'] : '部门').'微博';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}
			if (!($this->Config['department_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php'))){
				$this->Messager("网站没有开启该功能",null);
			}
			$views = array('all', 'my', 'other');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'other';
			}
			$active[$view] = 'current';
			$dids = array();
			if($member['departmentid']>0){ 				if($view == 'my'){
					$dids[] = $member['departmentid'];
				}else{
					$sql = "select did	from `".TABLE_PREFIX."buddy_department` where `uid`='".MEMBER_ID."'";
					$query = $this->DatabaseHandler->Query($sql);
					while (false != ($row = $query->GetRow())) {
						$dids[] = $row['did'];
					}
					if($view == 'all'){
						$dids[] = $member['departmentid'];
					}
				}
			}
			if($dids){
								$sql = "select `uid` from `".TABLE_PREFIX."members` where  `departmentid` in(".jimplode($dids).")";
				$query = $this->DatabaseHandler->Query($sql);
				$options['uid'] = array();
				while (false != ($row = $query->GetRow())) {
					$options['uid'][] = $row['uid'];
				}
								if(false != ($type = $this->Get['type']) && 'all' != $type){
					$options['tid'] = $TopicListLogic->GetTypeTid($type,$options['uid']);
				}
			}else{
				$options['tid'] = array();
			}
			if($member['departmentid']){
				$department = DB::fetch_first("SELECT * FROM ".DB::table('department')." WHERE id='".$member['departmentid']."'");
			}
			$user_lp = $this->TopicLogic->GetMember(array($department['leaderid'],$department['managerid']), "`uid`,`ucuid`,`username`,`nickname`,`face`,`face_url`,`validate`,`validate_category`,`aboutme`");
			$mybuddys = (array) get_buddyids(MEMBER_ID);
						$user_l = $user_lp[$department['leaderid']];
			if($user_l){
				$user_l['here_name'] = $department['leadername'];
				$user_l['follow_html'] = follow_html2($department['leaderid'],in_array($department['leaderid'],$mybuddys));
				$leader_list[] = $user_l;
			}
						$user_m = $user_lp[$department['managerid']];
			if($user_m){
				$user_m['here_name'] = $department['managername'];
				$user_m['follow_html'] = follow_html2($department['managerid'],in_array($department['managerid'],$mybuddys));
				$manager_list[] = $user_m;
			}
						$CpLogic = jlogic('cp');
			$department_list = $CpLogic->Getdepartment($member['companyid'],$member['departmentid']);

		}  else if('tag' == $params['code']) {

			$tpl = 'topic_tag';
			
			$title = '我关注的话题';
			if ($member['uid'] != MEMBER_ID) {
				$this->Messager("您无权查看该页面",null);
			}

			$views = array('new', 'new_reply', 'my_reply', 'recd');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'new';
			}
			$active[$view] = 'current';

			$tag_ids = jlogic('tag_favorite')->my_favorite_tag_ids(MEMBER_ID);
			if($tag_ids) {
				if('new' == $view) {
										$p = $options;
					$p['tag_id'] = $tag_ids;
					$p['sql_order'] = ' `item_id` DESC ';
					$rets = jtable('topic_tag')->get_ids($p, 'item_id', 1);
					$topic_list_get = true;
					$total_record = $rets['count'];
					$page_arr = $rets['page'];
					$topic_list = (($total_record > 0 && $rets['ids']) ? $this->TopicLogic->Get($rets['ids']) : array());
				} else {
										$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where  `tag_id` in('".implode("','",$tag_ids)."') ORDER BY `item_id` DESC LIMIT 2000 ";
					$query = $this->DatabaseHandler->Query($sql);
					$topic_ids = array();
					while (false != ($row = $query->GetRow())) {
						$topic_ids[$row['item_id']] = $row['item_id'];
					}
					$options['tid'] = $topic_ids;
					unset($topic_ids);
				}

								if ($this->Get['type']) {
					$options['filter'] = $this->Get['type'];
				}

				if ($view == 'new_reply') {
					$options['where'] = " replys>0 ";
					$options['order'] = " lastupdate DESC ";
				} else if ($view == 'recd') {

					$p = array(
						'where' => " tr.recd >= 1 AND tr.item='tag' AND tr.item_id IN(".jimplode($tag_ids).") ",
						'perpage' => 10,
						'filter' => $this->Get['type'],
					);
					$info = $TopicListLogic->get_recd_list($p);
					if (!empty($info)) {
						$total_record = $info['count'];
						$topic_list = $info['list'];
						$page_arr = $info['page'];
					}
					$topic_list_get = true;
				}
			}else{
				$topic_list_get = true;
			}

						if($GLOBALS['_J']['member']['topic_new']) {
				jlogic('member')->clean_new_remind('topic_new', MEMBER_ID);
			}

		} else if ('myblog' == $params['code']) {
                        $this->myblog_no_recommend = TRUE;
			$tpl = 'topic_myblog';
			
			$where = " 1 ";
						$options['uid'] = array($member['uid']);

						if ($this->Get['type']) {
								if ('profile' == $this->Get['type']) {
                    $title = $member['gender_ta'].'的资料';
					$type = 'profile';
					#用户填写的自定义添加项
					$member_profile = jlogic('member_profile')->getMemberProfileInfo($member['uid']);
					if($member_profile){
						$member = array_merge($member,$member_profile);
					}
					#用户自定义添加项的可见度
					$memberProfileSet = jlogic('member_profile')->getMemberProfileSet($member['uid']);
                                        $member_info = $member;
                    if($member_info['gender']){
                        if($member_info['gender'] == 1){
                            $member_info['gender'] = '男';
                        }elseif($member_info['gender'] == 2){
                            $member_info['gender'] = '女';
                        }
                    }
                                        if($member_info['bday']){
                        $bdayInt = strtotime($member_info['bday']);
                        if(!$bdayInt){
                            unset($member_info['bday']);
                        }
                    }
                    $member_info = array_filter($member_info);

					$groupProfile = jconf::get('groupprofile');
                                        foreach($groupProfile as $key_p=>$group_p){
                        $keys_p = array_keys($group_p['list']);
                        foreach($keys_p as $key_one){
                            if($member_info[$key_one]){
                                $groupProfile[$key_p]['isProfile'] = true;
                            }
                            continue;
                        }
                    }
				}
								if ('album' == $this->Get['type']) {
                    $this->Title = ($member['uid']==MEMBER_ID ? '我' : $member['nickname']).'的相册';
					$nav_url = $this->Title;
					$uid = $member['uid'];
					$imgid = jget('pid');
					if(isset($_GET['aid'])){
						$albumid = jget('aid');
						$list_type = 'image';
						$albumname = jlogic('image')->getalbumname($albumid,0,0,$uid);
						if($albumid > 0 && $albumname){
							if(!jlogic('image')->checkalbumbyid($albumid)){
								$this->Messager("您没有权限浏览该相册",null);
							}
							$count = jlogic('image')->albumimgnums($albumid,1);
							$albuminfo = jlogic('image')->getalbumbyid('album',$albumid);
							if($count != $albuminfo['picnum']){
								jlogic('image')->update_album_picnum($albumid,$count);
							}
						}else{
							$count = jlogic('image')->albumimgnums(0,0,$uid);
						}
						$albumname = $albumname ? $albumname : ($albumid > 0 ? '错误页面' : '默认相册');
						$pernum = 9;
						$pagehtml = page($count,$pernum,'index.php?mod='.$member['username'].'&type=album&aid='.$albumid,array('return'=>'array'));
						$limit_sql = $pagehtml['limit'];
						if($albumid > 0){
							$myalbums = jlogic('image')->getallalbumimg($albumid,$limit_sql,1);
						}else{
							$myalbums = jlogic('image')->getallalbumimg(0,$limit_sql,0,$uid);
						}
						foreach($myalbums as $key => $val){
							$myalbums[$key]['pic'] = $val['site_url'].str_replace('./','/',str_replace('_o.jpg','_s.jpg',$val['photo']));
							$myalbums[$key]['albumname'] = $val['description'] ? cut_str($val['description'],18) : '';
							$myalbums[$key]['title'] = $val['description'];
							$myalbums[$key]['url'] = jurl('index.php?mod='.$member['username'].'&type=album&pid='.$val['id']);
							$myalbums[$key]['rel'] = $val['photo'] ? $val['site_url'].'/'.str_replace('./','',$val['photo']) : '';
						}
						$nav_url = '<a href="index.php?mod='.$member['username'].'&type=album">'.$this->Title.'</a> >> '.$albumname;
					}elseif($imgid > 0){
						$content_ostr = '';
						$type = 'album';
						$infos = jlogic('image')->get_uploadimg_byid($imgid,$uid);
						$imginfo = $infos[$imgid];
						if(!$imginfo){
							$this->Messager("不存在该图片",null);
						}
						if($imginfo['albumid'] > 0 && !jlogic('image')->checkalbumbyid($imginfo['albumid'])){
							$this->Messager("您没有权限浏览该图片",null);
						}
						$imginfo['photo'] = $imginfo['site_url'].'/'.str_replace('./','',$imginfo['photo']);
						$albumname = $imginfo['albumid']>0 ? jlogic('image')->get_albumname_byid($imginfo['albumid']) : '默认相册';
						$imgname = $imginfo['description'] ? cut_str($imginfo['description'],18) : $imginfo['name'];
						$imgwidth = $imginfo['width'] > 580 ? 580 : $imginfo['width'];
						$imgheight = $imginfo['width'] > 580 ? ceil(($imginfo['height']/$imginfo['width'])*580) : $imginfo['height'];
						$imgsize = $imginfo['filesize'] > 0 ? ($imginfo['filesize'] < 1024*100 ? round(($imginfo['filesize']/1024),1).'K' : round(($imginfo['filesize']/(1024*1024)),1).'M') : '未知';
						$imgtime = my_date_format($imginfo['dateline']);
						$imgfrom = $imginfo['tid']>0 ? '<a href="'.jurl('index.php?mod=topic&code='.$imginfo['tid']).'">微博</a>' : ($imginfo['tid']<0 ? '私信' : '相册');
						$this->item = 'topic_image';
						$this->item_id = $imgid;
						$albumid = $imginfo['albumid'];
						$h_key = 'album';
												$gets = array(
							'mod' => $member['username'],
							'type' => 'album',
							'pid' => $imgid,
						);
						$page_url = 'index.php?'.url_implode($gets);
						$tids = jlogic('image')->get_topic_by_imageid($imgid);
						$options = array(
							'tid' => $tids,
							'perpage' => 5,								'page_url' => $page_url,
						);
						$topic_info = jlogic('topic_list')->get_data($options);
						$topic_list = array();
						if (!empty($topic_info)) {
							$topic_list = $topic_info['list'];
							$page_arr['html'] = $topic_info['page']['html'];
						}
						$albums = jlogic('image')->getalbum();
						$nav_url = '<a href="index.php?mod='.$member['username'].'&type=album">'.$this->Title.'</a> >> <a href="index.php?mod='.$member['username'].'&type=album&aid='.($imginfo['albumid']>0 ? $imginfo['albumid'] : 0).'">'.$albumname.'</a> >> '.($imgname ? $imgname : '图片浏览');
					}else{
						$list_type = 'album';
						$count = jlogic('image')->albumnums(1,$uid);
						$pernum = 8;
						$pagehtml = page($count,$pernum,'index.php?mod='.$member['username'].'&type=album',array('return'=>'array'));
						$limit_sql = $pagehtml['limit'];
						$myalbums = jlogic('image')->getalbum($limit_sql,1,$uid);
						$myalbums[0] = array('albumid'=>0,'albumname'=>'默认相册');
						$purview_name = $member['uid']==MEMBER_ID ? '我' : $member['gender_ta'];
						$purviewtext = array(0=>'所有人可见',1=>'仅'.$purview_name.'关注的人可见',2=>'仅'.$purview_name.'的粉丝可见',3=>'仅'.$purview_name.'自己可见');
						foreach($myalbums as $key => $val){
							$myalbums[$key]['pic'] = $val['pic'] ? str_replace('_o.jpg','_s.jpg',$val['pic']) :'images/noavatar.gif';
							$myalbums[$key]['albumname'] = cut_str($val['albumname'],18);
							$myalbums[$key]['title'] = $val['depict'] ? $val['depict'] : $val['albumname'];
							$myalbums[$key]['url'] = jurl('index.php?mod='.$member['username'].'&type=album&aid='.$val['albumid']);
							$myalbums[$key]['id'] = $val['albumid'];
							$myalbums[$key]['purview'] = $purviewtext[$val['purview']];
						}
					}
				}
                                if('mycomment' == $this->Get['type']){
                    $title = '评论'.$member['gender_ta'].'的';
                    if ($member['uid'] < 1) {
                        $this->Messager("您无权查看该页面",null);
                    }

                                        if ($member['comment_new']) {
                        jlogic('member')->clean_new_remind('comment_new', $member['uid']);
                    }

                    $topic_selected = 'mycomment';
                                        $_rets = jtable('member_relation')->get_tids($member['uid'], array('perpage'=>$per_page_num), 1);
                                        
                    if($_rets) {
                        $topic_list = $this->TopicLogic->Get($_rets['ids']);
                        $total_record = $_rets['count'];
                        $page_arr = $_rets['page'];
                        unset($_rets);
                    }
                    $topic_list_get = true;
                }
                                if('tocomment' == $this->Get['type']){
                    $tpl = 'topic_myblog';
                    $title = $member['gender_ta'].'评论的';
                    $topic_selected = 'mycomment';
                    unset($options['uid']);
                    $options['where'] = "`uid` = '{$member['uid']}' and `type` in ('both','reply')";
                }

								if('vote' == $this->Get['type']){
					$type = 'vote';
					$tpl = 'topic_vote';
					$perpage = $this->ShowConfig['vote']['list'];
					$perpage = empty($perpage) ? 20 : $perpage;
					$vote_where = ' 1 ';
										$filter = get_param('filter');
					if ($filter == 'joined') {
												$vids = jlogic('vote')->get_joined($member['uid']);
						if (!empty($vids)) {
							$vote_where .= " AND `v`.`vid` IN(".jimplode($vids).") ";
						} else {
							$vote_where = ' 0 ';
						}
					} else if ($filter == 'new_update') {
												jlogic('member')->clean_new_remind('vote_new', $uid);

						$vids = jlogic('vote')->get_joined($uid);
						if (!empty($vids)) {
							$vote_where .= " AND `v`.`vid` IN(".jimplode($vids).") ";
						}
						$vote_where .= " OR `v`.`uid`='{$uid}' ";
					}  else {
						$vote_where .= " AND `v`.`uid`='{$uid}' ";
						$filter = 'created';
					}
					$vote_order_sql = ' ORDER BY lastvote DESC ';
					$vote_where .=" AND v.verify = 1";
					$param = array(
						'where' => $vote_where,
						'order' => $vote_order_sql,
						'page' => true,
						'perpage' => $perpage,
						'page_url' => $options['page_url'],
					);
					$vote_info = jlogic('vote')->find($param);
					$count = 0;
					$vote_list = array();
					$page_arr['html'] = '';
					$uid_ary = array();
					if (!empty($vote_info)) {
						$count = $vote_info['count'];
						$vote_list = $vote_info['vote_list'];
						$page_arr['html'] = $vote_info['page']['html'];
						$uid_ary = $vote_info['uids'];
					}
										if (!empty($uid_ary)) {
						$members = $this->TopicLogic->GetMember($uid_ary);
					}
					$topic_list_get = true;
				}
								if('event' == $this->Get['type']){
					$type = 'event';
					$tpl = 'topic_event';
					$filter = get_param('filter');
					$param = array('perpage'=>"10",'page'=>true,);
					$return = array();
					if($filter == 'joined'){
						$this->Title = $member['nickname']."参与的活动";
						$param['where'] = " m.play = 1 and m.fid = '$uid' ";
						$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
						$param['page_url'] = $options['page_url'];
						$return = jlogic('event')->getEvents($param);
										} else if ($filter == 'new_update'){
												jlogic('member')->clean_new_remind('event_new', $uid);

						$this->Title = "最近更新的活动";
						$param['uid'] = $uid;
						$param['page_url'] = $options['page_url'];
						$return = jlogic('event')->getNewEvent($param);
										}else{
						$filter = 'created';
						$this->Title = $member['nickname']."的活动";
						$param['where'] = " a.postman = '$uid' and a.verify = 1 ";
						$param['order'] = " order by a.lasttime desc,a.app_num desc,a.posttime desc ";
						$param['page_url'] = $options['page_url'];
						$return = jlogic('event')->getEventInfo($param);
					}
					$rs = $return['event_list'];
					$count = $return['count'];
					$page_arr = $return['page'];
					$topic_list_get = true;
				}

								if ('my_reply' == $this->Get['type']) {
					$title = $member['gender_ta'].'评论的';
					if($member['uid']!=MEMBER_ID) {
											}
					$type = $this->Get['type'];
					$options['type'] = array('reply', 'both');
				}
								if ('mydig' == $this->Get['type']) {
					if ($member['dig_new']) {
						jlogic('member')->clean_new_remind('dig_new', $member['uid']);
					}
					$is_dig_tpl = true;
					$tpl = 'comment_inbox';
				}
								if(in_array($this->Get['type'],array('pic','video','music','attach','mydig','mydigout', 'my_reply'))){
					if($this->Get['follow'] && 'my_reply'!=$this->Get['type']) {
						$buddyids = get_buddyids($params['uid'], $this->Config['topic_myhome_time_limit']);

						if($buddyids) {
							$options['uid'] = $buddyids;
						}
					}
					$type = $dtype = $this->Get['type'];
					$options['get_list'] = 1;
					$getTypeTidReturn = $TopicListLogic->GetTypeTid($dtype,$options['uid'],$options);
					if(isset($getTypeTidReturn['list'])) {
						$topic_list = $getTypeTidReturn['list'];
						$total_record = $getTypeTidReturn['count'];
						$page_arr = $getTypeTidReturn['page'];
						$topic_list_get = true;
					} else {
						$options['tid'] = $getTypeTidReturn['tid'];
						$options['count'] = $getTypeTidReturn['count'];
						$options['limit'] = $per_page_num;
						if($type == 'mydigout'){$options['uid']=array();}
						if($type == 'mydig' || $type == 'mydigout'){$options['order'] = ' lastdigtime DESC ';}
					}
				}

                if('forward' == $this->Get['type'] || 'first' == $this->Get['type']){
                    $p = $options;
                    $p['type'] = 'forward';
                    if('first' == $this->Get['type']){
                        $p['type'] = 'first';
                    }
                    $p['sql_order'] = ' `forwards` DESC, `dateline` DESC ';

                    if(!$p['sql_order']) {
                        $p['sql_order'] = ' `dateline` DESC ';
                    }
                    $_rets = jtable('member_topic')->get_tids($member['uid'], $p, 1);
                    $total_record = $_rets['count'];
                    $page_arr = $_rets['page'];
                    $topic_list = $this->TopicLogic->Get($_rets['ids']);
                    $topic_list_get = true;
                }
				if('my_verify' == $this->Get['type']) {
					
					$title = '审核中的微博';
					if('admin' != MEMBER_ROLE_TYPE){
						if ($member['uid'] != MEMBER_ID) {
							$this->Messager("您无权查看该页面",-1);
						}
					}

										$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic_verify` where `uid`='{$uid}' AND `managetype`='0'";

					$total_record = DB::result_first($sql);

										$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>"Array"));

										$sql = "select v.*
							from `".TABLE_PREFIX."topic_verify` v
							where v.uid='{$uid}'
							and v.managetype = 0
							order by v.lastupdate desc {$page_arr['limit']}";
					$query = $this->DatabaseHandler->Query($sql);
					while (false != ($row = $query->GetRow())) {
						if ($row['id']<1) {
							continue;
						}
						$row['tid'] = $row['id'];
						if($row['longtextid']) {
							$row['content'] = jlogic('longtext')->longtext($row['longtextid']);
							unset($row['content2'], $row['longtextid']);
						}
						$topic_list[$row['id']]= $row;
					}
					$topic_list = $this->TopicLogic->MakeAll($topic_list);
					$topic_list_get = true;
				}
			}

            
                        if($this->Config['channel_enable']){
                $myBuddyChannel = jlogic('channel')->mychannel($member['uid']);
            }
						$dateline = TIMESTAMP - 2592000;
			if(empty($this->Get['type']) || in_array($this->Get['type'], array('hot_reply', 'hot_forward', 'hot_dig'))) {
				$p = $options;
				$p['type'] = array('first', 'forward', 'both');
				if($this->Get['type']) {
					if('hot_reply' == $this->Get['type']) {
						$p['>@replys'] = 0;
						$p['>@dateline'] = $dateline;
						$p['sql_order'] = ' `replys` DESC, `dateline` DESC ';
					} elseif('hot_forward' == $this->Get['type']) {
						$p['>@forwards'] = 0;
						$p['>@dateline'] = $dateline;
						$p['sql_order'] = ' `forwards` DESC, `dateline` DESC ';
					}elseif('hot_dig' == $this->Get['type']) {
						$p['>@digcounts'] = 0;
						$p['>@dateline'] = $dateline;
						$p['sql_order'] = ' `digcounts` DESC, `dateline` DESC ';
					}
				}
				if(!$p['sql_order']) {
					$p['sql_order'] = ' `dateline` DESC ';
				}
				$_rets = jtable('member_topic')->get_tids($member['uid'], $p, 1);
				$total_record = $_rets['count'];
				$page_arr = $_rets['page'];
				$topic_list = $this->TopicLogic->Get($_rets['ids']);
				$topic_list_get = true;

				if(empty($this->Get['type']) && 3 == count($this->Get) && empty($this->Get['code']) && $member['topic_count'] != $_rets['count']) {
					$member['topic_count'] = $_rets['count'];
					jtable('members')->update_count($member['uid'], 'topic_count', $member['topic_count'], 1);
				}

				$credit = jconf::get('credits');
				foreach ($credit['ext'] as $key => $value) {
					$member['jifen'] .= $value['name'].'：'.$member[$key].' ';
				}
				$title = '个人主页';
								foreach ($topic_list as $key => $val) {
					if ( $val[uid] == 0 && $val['anonymous_data'][uid] != MEMBER_ID )	unset($topic_list[$key]);
				}
            }elseif($this->Get['type'] == 'search' && trim($this->Get['q'])){                $_rets = jtable('topic')->get_ids(array('uid'=>$member['uid'],'like@content'=>'%'.trim(jget('q')).'%','sql_order'=>'`dateline` desc','page_num'=>10),'tid',1);
				$total_record = $_rets['count'];
				$page_arr = $_rets['page'];
                $topic_list = $this->TopicLogic->Get($_rets['ids']);
				$topic_list_get = true;
            }

			if ($member['uid'] != MEMBER_ID) {
				$title = "{$member['nickname']}的微博";

								$list_blacklist = is_blacklist($member['uid'], MEMBER_ID);


								$fg_code = 'hisblog';
				$this->_initTheme($member);
			} else {
				$title = '我的微博';
				$this->MetaKeywords ="{$member['nickname']}的微博";
			}
			$buddys = array();

						if (MEMBER_ID > 0 && $member['uid'] != MEMBER_ID) {
				$buddys = jlogic('buddy')->info($member['uid'], MEMBER_ID);
				$buddys['id'] = $buddys['touid'];
			}

		} else if ('qun' == $params['code']) {
			$tpl = 'topic_qun';
			$title = "我的".$this->Config[changeword][weiqun];

			$qun_setting = $this->Config['qun_setting'];
			if (!$qun_setting['qun_open']) {
				$this->Messager("当前站点没有开放".$this->Config[changeword][weiqun]."功能", null);
			}
						if(0 != $GLOBALS['_J']['member']['qun_new']) {
				jlogic('member')->clean_new_remind('qun_new', MEMBER_ID);
			}

			$views = array('new', 'new_reply', 'my_reply', 'recd');
			$view = trim($this->Get['view']);
			if (!in_array($view, $views)) {
				$view = 'new';
			}
			$active[$view] = "current";

			$u = MEMBER_ID;

						if(false === ($my_qun_ids = cache_db('get', ($cache_id = 'topic/'.$u.'-my_qun_ids')))) {
				$my_qun_ids = jtable('qun_user')->get_ids(array('uid'=>$u), 'qid', 1);
				cache_db('set', $cache_id, $my_qun_ids, 300);
			}
			$qid_ary = $my_qun_ids['ids'];
			$join_qun_count = $my_qun_ids['count'];

			$qun_name = '';
			if (!empty($qid_ary) && $join_qun_count > 0) {
				$where_sql = " 1 ";
				$order_sql = " t.dateline DESC ";
				$jget_type = jget('type', 'txt');
				$jget_type = (in_array($type, array('pic', 'video', 'music', 'vote')) ? $jget_type : '');
				if ($jget_type) {
					if ('pic' == $jget_type){
						$where_sql .= " AND t.`imageid` > 0 ";
					} else if('video' == $jget_type) {
						$where_sql .= " AND t.`videoid` > 0 ";
					} else if('music' == $jget_type) {
						$where_sql .= " AND t.`musicid` > 0 ";
					} else if ('vote' == $jget_type) {
						$where_sql .= " AND t.item='vote' ";
					} else {
						$jget_type = '';
					}
				}

				$topic_get_flg = false;
				if ($view == 'new') {
					
					$where_sql .= " AND tq.item_id IN(".jimplode($qid_ary).") ";
				} else if ($view == 'new_reply') {
					$where_sql .= " AND tq.item_id IN(".jimplode($qid_ary).") AND t.replys>0 ";
					$order_sql = " t.lastupdate DESC ";
				} else if ($view == 'recd') {
					$p = array(
						'where' => " tr.recd >= 1 AND tr.item='qun' AND tr.item_id IN(".jimplode($qid_ary).") ",
						'perpage' => $options['perpage'],
						'filter' => $this->Get['type'],
					);
					$info = $TopicListLogic->get_recd_list($p);
					if (!empty($info)) {
						$total_record = $info['count'];
						$topic_list = $info['list'];
						$page_arr = $info['page'];
					}
					$topic_get_flg = true;
				}

				if (!$topic_get_flg) {
					$total_record = DB::result_first("SELECT COUNT(*)
											   FROM ".DB::table('topic')." AS t
											   LEFT JOIN ".DB::table('topic_qun')." AS tq
											   USING(tid)
											   WHERE {$where_sql}");
					if ($total_record > 0) {
												$page_arr = page($total_record, $options['perpage'], $options['page_url'], array('return'=>'array'));
						$query = DB::query("SELECT t.*
											FROM ".DB::table('topic')." AS t
											LEFT JOIN ".DB::table('topic_qun')." AS tq
											USING(tid)
											WHERE {$where_sql}
											ORDER BY {$order_sql}
											{$page_arr['limit']} ");
						$topic_list = array();
						while ($row = DB::fetch($query)) {
							$topic_list[$row['tid']] = $row;
						}
						$topic_list = $this->TopicLogic->MakeAll($topic_list);
					}
				}

								if(0 != $GLOBALS['_J']['member']['qun_new']) {
					jlogic('member')->clean_new_remind('qun_new', MEMBER_ID);
				}
			}

						$showConfig = $this->ShowConfig;
			$recd_qun_limit = (int) $showConfig['page_r']['recd_qun'];
						if($recd_qun_limit) {
				$cache_id = 'topic/hot_qun-' . $recd_qun_limit;
				if(false === ($hot_qun = cache_file('get', $cache_id))) {
					$sql = "select * from `".TABLE_PREFIX."qun`  where `recd` = 1 order by `member_num` desc limit $recd_qun_limit  ";

					$query = $this->DatabaseHandler->Query($sql);
					$hot_qun = array();
					$qunLogic = jlogic('qun');
					while (false != ($row = $query->GetRow()))
					{
						$row['icon'] = $qunLogic->qun_avatar($row['qid'], 's');
						$hot_qun[] = $row;
					}
					cache_file('set', $cache_id, $hot_qun, 3600);
				}
			}
			$topic_list_get = true;

		} else if ('recd' == $params['code']) {
			
			$title = "官方推荐";
			$view = trim($this->Get['view']);
			$where_sql = '';
			if ($view == 'new_reply') {
				$where_sql = ' AND t.replys>0 ';
			} else {
				$view = 'all';
			}
			$active[$view] = 'current';

			$p = array(
				'where' => ' tr.recd > 0 '.$where_sql,
				'perpage' => $options['perpage'],
				'filter' => $this->Get['type'],
			);
			$info = $TopicListLogic->get_recd_list($p);
			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;

		} else if ('cms' == $params['code']) {
			
			$title = "网站资讯";
			$param = array(
				'perpage' => $options['perpage'],
				'page_url' => $options['page_url'],
			);
			$view = 'all';
			$active[$view] = 'current';
			$info = array();
						if($this->Config['dedecms_enable'] && (jconf::get('dedecms'))){
				Load::logic("topic_cms");
				$TopicCmsLogic = new TopicCmsLogic();
				$info = $TopicCmsLogic->get_cms($param);
				$cms_url = CMS_API_URL;
			}
			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;

		} else if ('bbs' == $params['code']) {
			
			$title = "我的论坛";
			$view = trim($this->Get['view']);
			$where_sql = '';
			if ($view == 'favorites') {
				$where_sql = 'favorites';
			}else if($view == 'favorite'){
				$where_sql = 'favorite';
			}else if($view == 'thread'){
				$where_sql = 'thread';
			}else if($view == 'reply'){
				$where_sql = 'reply';
			}else if($view == 'all'){
				$where_sql = 'all';
			} else {
				if($this->Config['dzbbs_enable']){
					$view = 'favorites';
					$where_sql = 'favorites';
				}else{
					$view = 'all';
					$where_sql = 'all';
				}
			}
			$active[$view] = 'current';
			$info = array();
			$param = array(
				'where' => $where_sql,
				'perpage' => $options['perpage'],
				'page_url' => $options['page_url'],
			);
						if(($this->Config['dzbbs_enable'] && jconf::get('dzbbs')) || ($this->Config['phpwind_enable'] && $this->Config['pwbbs_enable'] && jconf::get('phpwind'))){
				Load::logic("topic_bbs");
				$TopicBbsLogic = new TopicBbsLogic();
				$info = $TopicBbsLogic->get_bbs($param);
				$bbs_url = BBS_API_URL;
			}

			if (!empty($info)) {
				$total_record = $info['count'];
				$topic_list = $info['list'];
				$page_arr = $info['page'];
			}
			$topic_list_get = true;
		} else if ('topicnew' == $params['code']) {
			$title = '最新内容';
			$options['where'] = '';
			$params['orderby'] = $orderby = in_array($this->Get['orderby'],array('post','dig','mark')) ? $this->Get['orderby'] : 'dig';
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

		if (!$topic_list_get) {
						if($cache_time > 0 && !$options['tid']) { 				$cache_key = ($cache_key ? $cache_key : "{$member['uid']}-topic-{$params['code']}-{$params['type']}-{$params['gid']}-{$params['qid']}-{$params['view']}");

				$options = $TopicListLogic->get_options($options, $cache_time, $cache_key);
			}
						$info = $TopicListLogic->get_data($options);
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = $info['list'];
				$total_record = $info['count'];
				if($info['page']){
					$page_arr = $info['page'];
				}else{
					$page_arr = $getTypeTidReturn['page'];
				}
			}
		}

		$topic_list_count = 0;
		if ($topic_list) {
			if($GLOBALS['_J']['config']['is_topic_user_follow'] && !$GLOBALS['_J']['disable_user_follow']) {
				$topic_list = jlogic('buddy')->follow_html2($topic_list);
			}
			$topic_list_count = count($topic_list);
			if (!$topic_parent_disable && ('bbs' != $this->Code || 'cms' != $this->Code)) {
								$parent_list = $this->TopicLogic->GetParentTopic($topic_list, ('mycomment' == $this->Code));
							}
						$relate_list = $this->TopicLogic->GetRelateTopic($topic_list);
		}

				if (!in_array($params['code'],array('bbs','cms'))){			$ajaxkey = array();			$ajaxnum = 10;			if(count($topic_list)>$ajaxnum){				$topic_keys = array_keys($topic_list);				$topic_list = array_slice($topic_list,0,$ajaxnum);				array_splice($topic_keys,0,$ajaxnum);				$num = ceil(count($topic_keys)/$ajaxnum);				for($i=0;$i<$num;$i++){
					if(count($topic_keys)>$ajaxnum){
						$topic_key = array_splice($topic_keys,0,$ajaxnum);					}else{
						$topic_key = $topic_keys;
					}
					$ajaxkey[] = base64_encode(serialize($topic_key));				}
				$isloading = true;			}
		}

				$group_list = $grouplist2 = array();
		$group_list = $this->_myGroup($member['uid']);
		$cut_num = 5;
		if ($group_list) {
			$group_count = count($group_list);
			if ($group_count > $cut_num) {
				$grouplist2 = array_slice($group_list,0,$cut_num);
				$grouplist_more = array_slice($group_list, $cut_num);
				foreach ($grouplist_more as $key => $value) {
					if ($value['id'] == $gid) {
						$tmp = $grouplist2[$cut_num-1];
						$grouplist2[$cut_num-1] = $value;
						$grouplist_more[] = $tmp;
						unset($grouplist_more[$key]);
						break;
					}
				}
				$group_list = $grouplist_more;
			} else {
				$grouplist2 = $group_list;
				$group_list = array();
			}
		}

				if(!$this->Config['acceleration_mode']){
			$member_medal = $my_member ? $my_member : $member;
			if ($member_medal['medal_id']) {
				$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
			}
		}
		
		$exp_return = user_exp($member_medal['level'],$member_medal['credits']);
		if($exp_return['exp_width'] >= 1){
			$exp_width = $exp_return['exp_width'];
		} else {
			$exp_width = 0;
		}
				$nex_exp_credit  = $exp_return['nex_exp_credit'];
				$nex_level  = $exp_return['nex_exp_level'];

		$this->Title || $this->Title = $title;
		$tpl = $tpl ? $tpl : 'topic_index';

				if(in_array($tpl,array('topic_index','topic_myhome'))){
			$tid = jlogic('buddy')->check_new_recd_topic(MEMBER_ID);
			if($tid && $topic_list){
				foreach($topic_list as $key => $val){
					if($tid == $val['tid']) {
						unset($topic_list[$key]);
					}
				}
			}
		}
		


		$albums = jlogic('image')->getalbum();
		include(template($tpl));
	}


		function View()
	{
		
		$view_rets = jlogic('topic')->check_view($this->ID);
		if($view_rets['error']) {
			$this->Messager($view_rets['result'], null);
		}

		$per_page_num = 20;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

		        $topic_info = jlogic('longtext')->get_info($this->ID, array('type'=>'artZoom2'));
        if($topic_info['longtextid'] > 0) {
        	$topic_info['content'] = nl2br($topic_info['content']);
        }

				$allow_op = 1;

		
		if ($topic_info['item'] == 'qun' && !empty($topic_info['item_id'])) {
			Load::logic('qun');
			$QunLogic = new QunLogic();
			$qun_info = $QunLogic->get_qun_info($topic_info['item_id']);
			if (!empty($qun_info)) {
				$qun_info['icon'] = $QunLogic->qun_avatar($qun_info['qid'], 's');
				$allow_op = $is_qun_member = $QunLogic->is_qun_member($topic_info['item_id'], MEMBER_ID);
			}
		} else {
									if ($topic_info['type'] == 'reply') {
				$roottid = $topic_info['roottid'];

								if (empty($roottid)) {
					$root_type = 'reply';
				} else {
					$root_type = DB::result_first("SELECT type FROM ".DB::table('topic')." WHERE tid='{$roottid}'");
				}
			} else {
				$root_type = $topic_info['type'];
			}
		}


		$parent_list = $t_parent_list = $t_relate_list = array();
		if($topic_info['parent_id'])
		{
			$parent_id_list = array
			(
			$topic_info['parent_id'],
			$topic_info['top_parent_id'],
			);

			if($parent_id_list)
			{
				                $t_parent_list[$topic_info['top_parent_id']] = jlogic('longtext')->get_info($topic_info['top_parent_id'], array('type'=>'artZoom2'));
			}
		}

		if($topic_info['relateid'] > 0){			$t_relate_list = jlogic('longtext')->get_info($topic_info['relateid']);
		}

				if ($topic_info['digcounts'] > 0) {
			$dig_users = array();
			$user = $this->TopicLogic->GetDigUids($topic_info['tid']);
						if(!$user || count($user) != $topic_info['digcounts']){
				$query = DB::query("SELECT uid FROM `".TABLE_PREFIX."topic_dig`	WHERE tid = '".$topic_info['tid']."' ORDER BY id DESC");
				while ($rs = DB::fetch($query)){
					$user[$rs['uid']] = $rs['uid'];
				}
			}
						$dig_users = $this->TopicLogic->GetMember($user, "`uid`,`username`,`nickname`,`face`");
			foreach($dig_users as $k => $v){
				array_splice($dig_users[$k],4,3);
			}
			$listotherdig = true;
		}

		if ($topic_info['replys'] > 0) {
			$total_record = $topic_info['replys'];
			$p = array(
				'perpage' => $per_page_num,
				'result_count' => $total_record,
			);
			$orderby = jget('orderby');
			if('dig' == $orderby) {
				$p['sql_order'] = ' `digcounts` DESC, `lastdigtime` DESC ';
			} elseif('post' == $orderby) {
				$p['sql_order'] = ' `dateline` DESC ';
			} else {
				$p['sql_order'] = ' `dateline` ASC ';
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

			$relate_reply = array();
			if($reply_list) {
				if($topic_info['relateid'] > 0){
					if($reply_list[$topic_info['relateid']]){						$relate_reply[$topic_info['relateid']] = $reply_list[$topic_info['relateid']];
						unset($reply_list[$topic_info['relateid']]);
					}else{
						$relate_reply[$topic_info['relateid']] = jlogic('longtext')->get_info($topic_info['relateid']);
					}
					if(in_array($topic_info['channel_type'],array('ask','idea'))){
						$relate_reply[$topic_info['relateid']]['ch_ty_css'] = ($topic_info['channel_type'] ? $topic_info['channel_type'] : 'default').'_relate_mark';
					}
				}
				$ajaxkey = array();
				$ajaxnum = 10;
				if(count($reply_list)>$ajaxnum){
					$topic_keys = array_keys($reply_list);
					$reply_list = array_slice($reply_list,0,$ajaxnum);
					array_splice($topic_keys,0,$ajaxnum);
					$num = ceil(count($topic_keys)/$ajaxnum);
					for($i=0;$i<$num;$i++){
						if(count($topic_keys)>$ajaxnum){
							$topic_key = array_splice($topic_keys,0,$ajaxnum);
						}else{
							$topic_key = $topic_keys;
						}
						$ajaxkey[] = base64_encode(serialize($topic_key));
					}
					$isloading = true;
				}
				if($relate_reply){					$reply_list = array_merge($relate_reply,$reply_list);
				}
											}
		}
		if($t_parent_list) {
			foreach($t_parent_list as $k=>$v) {
				if(!isset($parent_list[$k])) {
					$parent_list[$k] = $v;
				}
			}
		}


		


		if (MEMBER_ID > 0) {
			$sql = "select * from `".TABLE_PREFIX."topic_favorite` where `uid`='".MEMBER_ID."' and `tid`='{$topic_info['tid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$is_favorite = $query->GetRow();
		}

		$member = $this->_member($topic_info['uid']);

				$member_medal = $member;
		if($member_medal['medal_id'])
		{
			$medal_list = $this->_Medal($member_medal['medal_id'],$member_medal['uid']);
		}

		
		$titletopicname = $topic_info['anonymous'] ? '匿名发布' : $member['nickname'];
		$this->Title = cut_str(strip_tags($topic_info['content']),50)." - {$titletopicname}的微博";

				$Keywords = array();
		if(strpos($topic_info['content'],'#'))
		{
			preg_match_all('~\#([^\#\s\'\"\/\<\>\?\\\\]+?)\#~',strip_tags($topic_info['content']),$Keywords);
		}
		if(is_array($Keywords[1]) && count($Keywords[1]))
		{
			$this->MetaKeywords = implode(',',$Keywords[1]);
		}

		$this->MetaDescription = cutstr(strip_tags($topic_info['content']), 140);
		


		if(MEMBER_ID != $member['uid'])
		{
			$this->_initTheme($member);
		}


		jtable('topic')->update_count($topic_info['tid'], 'views', '+1', 1);


		$topic_view = 1;
		$this->item = $topic_info['item'];
		$this->item_id = $topic_info['item_id'];

		include(template('topic_view'));
	}

	function ViewGroup()
	{
		$member = $this->_member();
		if (!$member || MEMBER_ID < 1) {
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}
				$gid = (int) $this->Get['gid'];
		if($gid < 1) {
			$this->Messager('分组ID不能为空');
		}
				$group_view = jtable('buddy_follow_group')->info(array(
			'id' => $gid,
			'uid' => MEMBER_ID,
		));
		if(!$group_view) {
			$this->Messager('您要查看的分组不存在');
		}

				$count = $member['follow_count'];
		if($count > 0) {
			$per_page_num = 24;
			$page_arr = page($count,$per_page_num,"index.php?mod=topic&code=group&gid={$gid}", array('return'=>'Array'));

			$p = array(
				'result_count' => $count,
				'sql_order' => ' `dateline` DESC ',
				'sql_limit' => " {$page_arr['limit']} ",
				'uid' => $member['uid'],
			);
			$uids = get_buddyids($p);

			if($uids) {
				$buddysList = $this->TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`validate_category`,`nickname`");
			}
		}

				$group_list = $grouplist2 = array();
		$group_list = $this->_myGroup($member['uid']);
		if($group_list) {
			$grouplist2 = array_slice($group_list,0,min(4,count($group_list)));
		}

		
		include(template('topic_group'));
	}

	
	function _member($uid=0)
	{
		$member = array();
		if($uid < 1)
		{
			$member = jsg_member_info_by_mod();
		}

		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($uid > 0 && !$member)
		{
			$member = $this->TopicLogic->GetMember($uid);
		}
		if(!$member)
		{
			return false;
		}
		$uid = $member['uid'];

		if (!$member['follow_html'] && $uid!=MEMBER_ID && MEMBER_ID>0)
		{
			$member['follow_html'] = buddy_follow_html($member, 'uid', 'follow_html', 1);
		}

				if(true === UCENTER_FACE && MEMBER_ID == $uid && MEMBER_UCUID > 0 && !($member['__face__']))
		{
			include_once(ROOT_PATH . './api/uc_client/client.php');

			$uc_check_result = uc_check_avatar(MEMBER_UCUID);

			if($uc_check_result)
			{
				$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `face`='./images/noavatar.gif' where `uid`='{$uid}'");
			}
		}
		if($GLOBALS['_J']['plugins']['func']['printuser']) {
			jlogic('plugin')->hookscript('printuser', 'funcs', $member, 'printuser');
		}
		return $member;
	}

	function _recommendTag($day=1,$limit=12,$cache_time=0)
	{
		if($limit < 1) return false;

		$time = $day * 86400;
		$cache_time = ($cache_time ? $cache_time : $time / 90);
		$cache_id = "misc/recommendTopicTag-{$day}-{$limit}";

		if (false === ($list = cache_file('get', $cache_id))) {
			$dateline = TIMESTAMP - $time;
			$sql = "SELECT DISTINCT(tag_id) AS tag_id, COUNT(item_id) AS item_id_count FROM `".TABLE_PREFIX."topic_tag` WHERE dateline>=$dateline GROUP BY tag_id ORDER BY item_id_count DESC LIMIT {$limit}";
			$query = $this->DatabaseHandler->Query($sql);
			$ids = array();
			while (false != ($row = $query->GetRow()))
			{
				$ids[$row['tag_id']] = $row['tag_id'];
			}

			$list = array();
			if($ids) {
				$sql = "select `id`,`name`,`topic_count` from `".TABLE_PREFIX."tag` where `id` in('".implode("','",$ids)."')";
				$query = $this->DatabaseHandler->Query($sql);
				$list = $query->GetAll();
			}

			cache_file('set', $cache_id, $list, $cache_time);
		}

		return $list;

	}

		function _myGroup($uid=0,$limit = 0) {
		return jlogic('buddy_follow_group')->get_my_group($uid, $limit);
	}

	function _Medal($medalid=0,$uid=0)
	{
		$uid = (is_numeric($uid) ? $uid : 0);

		$medal_list = array();

		if($uid > 0)
		{
			$sql = "select  U_MEDAL.dateline ,  MEDAL.medal_img , MEDAL.conditions , MEDAL.medal_name ,MEDAL.medal_depict ,MEDAL.id , U_MEDAL.*
					from `".TABLE_PREFIX."medal` MEDAL
					left join `".TABLE_PREFIX."user_medal` U_MEDAL on MEDAL.id=U_MEDAL.medalid
					where U_MEDAL.uid='{$uid}'
					and U_MEDAL.is_index = 1
					and MEDAL.is_open = 1 ";

			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				$row['dateline'] = date('m-d日 H:s ',$row['dateline']);
				$medal_list[$row['id']] = $row;
			}
		}

		return $medal_list;
	}

		function Photo()
	{
		$this->Title = '图片墙';
		if($_GET['type']=='3d'){
			$xml = '<?xml version="1.0"?>';
			$xml .= '<!DOCTYPE cross-domain-policy SYSTEM "http:/'.'/www.macromedia.com/xml/dtds/cross-domain-policy.dtd">';
			$xml .= '<cross-domain-policy>';
			$xml .= '<allow-access-from domain="'.$this->Config['site_domain'].'" />';
			$xml .= '<allow-access-from domain="*.cooliris.com" />';
			$xml .= '<allow-http-request-headers-from domain="*" headers="*" />';
			$xml .= '</cross-domain-policy>';
			if(@!is_file("crossdomain.xml")){
				file_put_contents("crossdomain.xml",$xml);
			}else{
				$oxml = file_get_contents("crossdomain.xml");
				if(strcmp($xml,$oxml)){
					file_put_contents("crossdomain.xml",$xml);
				}
			}
			$this->Title .= '—3D浏览';
			include(template('topic_photo_3d'));
		}
		else
		{
			$nickname = '我';
			$uid = 0;
			if(isset($this->Get['uid'])){
				if((int)$this->Get['uid']>0){
					$uid = (int)$this->Get['uid'];
					$type = 1;
					if($uid != MEMBER_ID){
						$nickname = DB::result_first("select `nickname` from ".TABLE_PREFIX."members where uid = '$uid'");
						$this->Title = $nickname."关注的人的图片";
					}else{
						$this->Title = '我关注人的图片';
					}
				}
			}

			$TopicListLogic = jlogic('topic_list');
			$photo_num = 20; 			$p = array(
				'count' => $photo_num,
				'vip' => $this->Config['only_show_vip_topic'],
				'limit' => $photo_num,
				'uid' => $uid,
			);
			$info = $TopicListLogic->get_photo_list($p);
			if (!empty($info)) {
				$topic_list = $info['list'];
				$isloading = ($info['count'] >= $p['limit'] ? true : false);
			}else{
				$isloading = false;
			}
			if($this->Config['attach_enable']){$allow_attach = 1;}else{$allow_attach = 0;}

			$t_col_foot = 't_col_foot'; 			$t_col_backTop = 't_col_backTop';
			$url_uid = ($uid ? $uid : MEMBER_ID); 			include(template('topic_photo'));
		}
	}

	function normalIndex(){
		if(MEMBER_ID > 0) {
			$member = jsg_member_info(MEMBER_ID);
			if($member){
				header('Location: index.php?mod=topic');
				exit;
			}
		}
		$username = jget('username') ? jget('username') : (jget('email') ? jget('email') : $this->Config['changeword']['username']);

				$this->ShowConfig = jconf::get('show');
		$this->CacheConfig = jconf::get('cache');
		$limit = $this->ShowConfig['topic_index']['guanzhu'];
		if ($limit > 0) {
			$cache_id = "index/r_users";
			if(false === ($r_users = cache_file('get', $cache_id))) {
				$r_users = jlogic('topic')->GetMember("where face !='' order by `fans_count` desc limit {$limit}","`uid`,`ucuid`,`username`,`face_url`,`face`,`fans_count`,`validate`,`validate_category`,`nickname`");

				cache_file('set', $cache_id, $r_users, $this->CacheConfig['topic_index']['guanzhu']);
			}
		}

				$day2_r_users = jlogic('member')->get_member_by_topic($this->ShowConfig['topic_index']['new_user'], $this->CacheConfig['topic_index']['new_user']);

				$r_tags = $this->_recommendTag(2,$this->ShowConfig['topic_index']['hot_tag'],$this->CacheConfig['topic_index']['hot_tag']);

		$recommend_count = 0;
		if ($this->ShowConfig['topic_index']['recommend_topic']) {
			$cache_id = "index/recommend_topics";
			if (false === ($cache_recommend_topics = cache_file('get', $cache_id))) {
				
				$TopicListLogic = jlogic('topic_list');
				$type_sql = jimplode(get_topic_type());
				$fields = " a.* ";
				$vip_t = $vip_w = '';
				if($this->Config['only_show_vip_topic']) {
					$vip_t = ', '.DB::table('members').' m ';
					$vip_w = ' and m.uid=a.uid and m.validate="1" ';
				}
				$table = " ".DB::table("topic")." a,(SELECT uid, max(dateline) max_dateline FROM ".DB::table("topic")." WHERE type IN(".$type_sql.") GROUP BY uid) b $vip_t ";
				$where = "  WHERE a.uid = b.uid AND a.dateline = b.max_dateline AND a.type IN({$type_sql}) $vip_w ORDER BY a.dateline DESC LIMIT {$this->ShowConfig['topic_index']['recommend_topic']}";
				$recommend_topics = jlogic('topic')->Get($where, $fields, 'Make', $table);

								$parent_list = jlogic('topic')->GetParentTopic($recommend_topics);
				
				$cache_recommend_topics = array(
					'recommend_topics' => $recommend_topics,
					'parent_list' => $parent_list,
				);
				cache_file('set', $cache_id, $cache_recommend_topics, $this->CacheConfig['topic_index']['recommend_topic']);
			} else {
				$recommend_topics = $cache_recommend_topics['recommend_topics'];
				$parent_list = $cache_recommend_topics['parent_list'];
			}
			$recommend_count = count($recommend_topics);
		}


		include(template('login/login_index'));
	}

	function simpleIndex(){
		if(MEMBER_ID > 0) {
			$member = jsg_member_info(MEMBER_ID);
			if($member){
				header('Location: index.php?mod=topic');
				exit;
			}
		}
		$username = jget('username') ? jget('username') : (jget('email') ? jget('email') : $this->Config['changeword']['username']);

				$cache_id = 'notice/list-topic_index_guest';
		if (false===($list_notice = cache_file('get', $cache_id))) {
			$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `id` desc limit 5 ";
			$query = $this->DatabaseHandler->Query($sql);
			$list_notice = array();
			while (false != ($row = $query->GetRow())) {
				$row['titles']	= $row['title'];
				$row['title'] 	= cutstr($row['title'],30);
				$list_notice[] 	= $row;
			}
			cache_file('set', $cache_id, $list_notice, 86400);
		}
		$channels = jlogic('channel')->get_category_tree(1);
		$returnchs = array();
		if(count($channels)>8){			$keys = array_rand($channels,8);
			foreach($keys as $v){
				$returnchs[$v] = $channels[$v];
			}
		}else{
			if($channels){
				shuffle($channels);			}
			$returnchs = $channels;
		}
		include(template('login/login_index_simpler'));
	}

	function only_login_index() {
		if(MEMBER_ID > 0) {
			$this->Messager(null, 'index.php?mod=topic');
		}

		include template('login/login_index_simplest');
	}

	function channelIndex(){
		if(MEMBER_ID > 0) {
			$member = jsg_member_info(MEMBER_ID);
			if($member){
				header('Location: index.php?mod=topic');
			}
		}else{
			$channeltoptopic =  $this->ChannelLogic->getChannelTopTopic();
			$channelrectopic =  $this->ChannelLogic->getChannelRecTopic();
			$channellist =  $this->ChannelLogic->getChannelAll();
			$userfanstop =  $this->ChannelLogic->getUserFansTop();
			$this->Title = '频道首页';
			$this->MetaKeywords = '频道首页';
			$this->MetaDescription = '微博频道,频道首页';
			include template('channel/channel_index');
		}
	}
}

?>