<?php
/**
 * 文件名：topic.mod.php
 * @version $Id: topic.mod.php 5274 2013-12-17 08:10:11Z wuliyong $
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

		$this->CacheConfig = jconf::get('cache');

		$this->ShowConfig = jconf::get('show');

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();

		if(empty($_GET['mod_original']))
		{
			$this->Code = $this->Code ? $this->Code :'myhome';
		}
		if('add' == $this->Code) {
			$this->Add();
		}

		elseif('do_add' == $this->Code) {
			$this->DoAdd();
		}

		elseif('doreply' == $this->Code) {
			$this->DoAdd();
		}

		elseif('forward' == $this->Code) {
			$this->Forward();
		}

		elseif('do_forward' == $this->Code) {
			$this->DoForward();
		}

		elseif('dofollow' == $this->Code) {
			$this->DoFollow();
		}

		elseif ('top' == $this->Code) {
			$this->Top();
		}

		elseif ('view' == $this->Code) {
			$this->View();
		}

		elseif ('favorite' == $this->Code) {
			$this->DoFavorite();
		}

		elseif (in_array($this->Code,array('new','hot',))) {
			$this->Hot();
		}

		elseif ('modify' == $this->Code) {
			$this->DoModify();
		}

		elseif ('del' == $this->Code) {
			$this->DelTopic();
		}

		elseif ('dodel' == $this->Code) {

			$this->DoDelTopic();
		}

		elseif ('info' == $this->Code) {
			$this->Info();
		}

		elseif (is_numeric($this->Code)) {
			$this->ID = (int) $this->Code;
			$this->View();
		}

		else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}


	function Main()
	{
		$options = array();
				if('topic'==$this->Get['mod'] && count($this->Get)<2) {
						if (MEMBER_ID > 0) {
				$this->Code = 'myhome';
			} else {
				$this->Hot();
				return ;
			}
		}


		$title = '';
		$per_page_num = 10;			$topic_uids = $topic_ids = $order_list = $where_list = $params = array();
		$where = $order = $limit = "";
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");


		$member = $this->_member();
		if(!$member) {
			$this->Hot();
			return ;
		}
		$params['uid'] = $uid = $member['uid'];

		$is_personal = ($uid == MEMBER_ID);
		$params['is_personal'] = $is_personal;

		$start = max(0, (int) $start);
		$limit = "limit {$start},{$per_page_num}";
		$next = $start + $per_page_num;

		$params['code'] = $this->Code;

		if (!in_array($params['code'],array('myblog','myhome','recd',))) {
			$params['code'] = 'myblog';		}

				if (($show_topic_num = $this->ShowConfig['topic'][$params['code']]) > 0) {
			$per_page_num = $show_topic_num;
		}
		$options['perpage'] = $per_page_num;


		if ('myhome'==$params['code']) {
			$topic_selected = 'myhome';

						if($member['uid']==MEMBER_ID) {
				$title = '我的首页';
				$cache_time = 600;
				$cache_key = "{$uid}-topic-myhome--0";
				
								$refresh_time = max(30, (int) $this->Config['ajax_topic_time']);
				if(get_param('page') < 2 && ($member['lastactivity'] + $refresh_time < TIMESTAMP)) {
					$new_topic = jlogic('buddy')->check_new_topic($uid, 1);
					if($new_topic > 0) {
						cache_db('rm', "{$uid}-topic-%", 1);
					}
				}

				if(false === cache_db('get', $cache_key)) {
					$buddyids = get_buddyids($params['uid'], $this->Config['topic_myhome_time_limit']);
					if($buddyids) {
						$topic_uids = array_merge($topic_uids, $buddyids);
					}
				}
			} else {
				$title = "{$member['nickname']}的微博";
			}
			$topic_uids[$uid] = $uid;

			$options['uid'] = $topic_uids;
			
		} elseif ('myblog' == $params['code']) {

			$where = " and `type` != 'reply' ";

			if($member['uid']!=MEMBER_ID) {
				$title = "{$member['nickname']}的微博";
			} else {
				$title = '我的微博';
			}

			$topic_selected = 'myblog';
						$options['uid'] = $member['uid'];

		}


		if(!$topic_list_get) {
			$options['page_url'] = $query_link;
			$options['order'] = " `dateline` DESC ";

						if($cache_time > 0 && $cache_key && !$options['tid']) { 				$options = jlogic('topic_list')->get_options($options, $cache_time, $cache_key);
				$options['page'] = wap_iconv($options['page']);
			}
			
			$info = jlogic('topic_list')->get_data($options, 'wap');
			$topic_list = array();
			$total_record = 0;
			if (!empty($info)) {
				$topic_list = wap_iconv($info['list']);
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}

		}

		$topic_list_count = 0;
		if($topic_list) {
			$topic_list_count = count($topic_list);

			if(!$topic_parent_disable) {
								$parent_id_list = array();
				foreach ($topic_list as $key => $row) {
					if(0 < ($p = (int) $row['parent_id'])) {
						$parent_id_list[$p] = $p;
					}
					if (0 < ($p = (int) $row['top_parent_id'])) {
						$parent_id_list[$p] = $p;
					}
				}

				if($parent_id_list) {
										$parent_list = $this->_topicLogicGet($parent_id_list);
				}
							}
		}


		$this->Title = $title;
		include(template('topic_index'));
	}


		function View()
	{
		
		$view_rets = jlogic('topic')->check_view($this->ID);
		if($view_rets['error']) {
			$this->Messager($view_rets['result'], null);
		}

		$per_page_num = 5;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

		$topic_info = $this->_topicLogicGet($this->ID);

		
		if ($topic_info['item'] == 'qun') {
			;
		} else {
									if ($topic_info['type'] == 'reply') {
				$roottid = $topic_info['roottid'];
				$root_type = DB::result_first("SELECT type FROM ".DB::table('topic')." WHERE tid='{$roottid}'");
			} else {
				$root_type = $topic_info['type'];
			}
		}

		if($topic_info['parent_id']) {
			$parent_id_list = array(
			$topic_info['parent_id'] => $topic_info['parent_id'],
			$topic_info['t
				op_parent_id'] => $topic_info['top_parent_id'],
			);

			if($parent_id_list) {

				$parent_list = $this->_topicLogicGet($parent_id_list);
			}

		}

		if ($topic_info['replys'] > 0) {
			$total_record = $topic_info['replys'];
			$rets = jtable('topic_relation')->get_tids($topic_info['tid'], array(
				'page_func' => 'wap_page',
				'page_url' => $page_link,
				'perpage' => $per_page_num,
				'result_count' => $total_record
			), 1);
			$page_arr = $tids = array();
			if($rets) {
				$page_arr = $rets['page'];
				$tids = $rets['ids'];
				$total_record = $rets['count'];
			}
			if($tids) {
				$condition = "where `tid` in ('".implode("','",$tids)."') order by `dateline` asc limit $per_page_num";
				$reply_list = $this->_topicLogicGet($condition);
			}
		}

				if($topic_info['longtextid'] > 0) {
			$longtext_info = $this->_longtextLogic($topic_info['tid']);
			$topic_info['content'] = nl2br($longtext_info['content']);
		}

		if (MEMBER_ID > 0) {
			$sql = "select * from `".TABLE_PREFIX."topic_favorite` where `uid`='".MEMBER_ID."' and `tid`='{$topic_info['tid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$is_favorite = $query->GetRow();
		}

		$member = $this->_member($topic_info['uid']);
		$this->Title = cut_str(strip_tags($topic_info['content']),50)." - {$member['nickname']}的微博";

		include(template('topic_view'));
	}

	function Follow()
	{
		$member = $this->_member();
		if (!$member) {
			$this->Messager("链接错误，请检查",'index.php?mod=plaza');
		}

		$member_list = array();
		$per_page_num = 8;
		$page_link = "index.php?mod={$member['username']}&amp;code=follow";
		$count = $member['follow_count'];

		if($count > 0) {
			$page_arr = wap_page($count, $per_page_num, $page_link, array('return'=>'Array'));

			$p = array(
				'uid' => $member['uid'],
				'result_count' => $count,
				'sql_limit' => " {$page_arr['limit']} ",
				'sql_order' => ' `dateline` DESC ',
			);
			$uids = get_buddyids($p);
			if($uids) {
				$member_list = $this->_topicLogicGetMember($uids);
				$member_list = buddy_follow_html($member_list, 'uid', 'wap_follow_html');
			}
		}


		$this->Title = "{$member['nickname']}关注的人";
		include(template('topic_follow'));
	}


	function Hot() {
		$title = '广场';

		$uid = MEMBER_ID;
		$member = $this->_member($uid);

				$per_page_num = $this->ShowConfig['topic_new']['topic'];
		if($per_page_num < 1) {
			$per_page_num = 20;
		}
		$cache_time = max(0, (int) $this->CacheConfig['topic_new']["topic"]);
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");


		if($per_page_num > 0) {
			$options = array(
				'cache_time' => $cache_time,
				'cache_key' => 'topic-newtopic-wap',
				'page_url' => $query_link,
				'perpage' => $per_page_num,
				'order' => " dateline DESC ",
				'type' => get_topic_type(),
			);

			if($this->Get['type'] == 'recd') {
								$hb_type = 'recd';
				$info = jlogic('topic_list')->get_recd_list($options, 'wap');
			} else {
								$hb_type = 'topic_new';
				if($this->Config['only_show_vip_topic']) {
					$title = '最新V博';
										$options['uid'] = jsg_get_vip_uids(); 					if($options['uid']){
						$info = jlogic('topic_list')->get_data($options, 'wap');
					} else {
						$info = array();
					}
				} else {
					$info = jlogic('topic_list')->get_data($options, 'wap');
				}
			}
			$topics = array();
			$total_record = 0;
			if (!empty($info)) {
				$topics = wap_iconv($info['list']);
				$total_record = $info['count'];
				$page_arr = $info['page'];
			}
		}

				$parent_id_list = array();
		if ($topics) {
			foreach ($topics as $row) {
				if(0 < ($p = (int) $row['parent_id'])) {
					$parent_id_list[$p] = $p;
				}
				if (0 < ($p = (int) $row['top_parent_id'])) {
					$parent_id_list[$p] = $p;
				}
			}
		}
		if($parent_id_list) {
			$parent_list = $this->_topicLogicGet($parent_id_list);
		}
		

		
		$topic_new_hb = 'hb';
		$this->Title = $title;
		include(template('topic_new'));
	}


		function DoFavorite()
	{
		if (MEMBER_ID < 1) {
			$this->Messager("请登录",'index.php?mod=login');
		}

		$uid = MEMBER_ID;

		$tid = (int) ($this->Get['tid']);

		if ($tid < 1) {
			$this->Messager("请指定一个微博",'index.php?mod=plaza');
		}

		$act = $this->Get['act'];

		$Favorite = wap_iconv(jlogic('topic_favorite')->act($uid,$tid,$act));

		$this->Messager($Favorite,'index.php?mod=topic_favorite');

	}


	function _member($uid=0)
	{
		$member = array();
		if($uid < 1) {
			$member = jsg_member_info_by_mod();
			$member = wap_iconv($member);
		}
		$uid = (int) ($uid ? $uid : MEMBER_ID);
		if($uid > 0 && !$member) {
			$member = $this->_topicLogicGetMember($uid);
		}
		if(!$member) {
			return false;
		}
		$uid = $member['uid'];

		if (MEMBER_ID>0 && $uid!=MEMBER_ID) {
			$member = buddy_follow_html($member, 'uid', 'wap_follow_html', 1);
		}

		return $member;
	}

		function DoFollow()
	{
		$rets = buddy_add($this->ID, MEMBER_ID, 1);
		if($rets && $rets['error']) {
			$msg = wap_iconv($rets['error']);

			$this->Messager($msg);
		}

		if('follow' == $this->Get['act']){
			$this->Messager('关注成功','index.php?mod=fans');
		} else {
			$this->Messager('取消关注成功','index.php?mod=follow');
		}
	}


		function Add()
	{
		if (MEMBER_ID < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

		
		$addtopic_hb = 'hb';

		$this->Title = '发表微博';

		include(template('addtopic'));

	}


		function DoAdd()
	{
		if (MEMBER_ID < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",'index.php?mod=login');
		}

		$timestamp 		= time();

		$content 		= (string) (trim(strip_tags($this->Post['content'])));

				$topic_type = $this->Post['topictype'];

		if(empty($topic_type)){
			$type = 'reply';
		} elseif($topic_type == 'both'){
			$type = 'both';
		} else {
			$type = 'first';
		}

		if($this->Post['tag']) {
			$content = $this->Post['tag'].$content;
		}

		$totid = max(0, (int) $this->Post['totid']);

		$content = wap_iconv($content,'utf-8',$this->Config['charset'], 1);
		if(empty($content)) {
			if($this->Code == 'do_add'){
				$this->Messager('请输入微博','index.php?mod=topic&code=myhome');
			}else{
				$this->Messager('请输入评论','index.php?mod=topic&code='.$this->Post['return_code']);
			}
		}

		$imageid = 0;
		$rets = jlogic('image')->upload();
		if(!$rets['error']) {
			$imageid = max(0, (int) $rets['id']);
		}

		$datas = array(
			'content' => $content,
			'totid' => $totid,
			'imageid' => $imageid,
			'from' => 'wap',
			'type' => $type,
		);

		$return = $this->TopicLogic->Add($datas);

		$return = wap_iconv($return);

		if (is_array($return)) {
			$msg = '';
			if($return['tid'] < 1) {
				if($return['msg']) {
					$msg = $return['msg'];
				} else {
					$msg = implode(',', $return);
				}
			}

			if('replycontent' == $this->Post['reply'])
			{
				$this->Messager("【评论成功】" . $msg, 'index.php?mod=topic&amp;code='.$this->Post['totid']);
			}
			else
			{
				$this->Messager("【发布成功】" . $msg, 'index.php?mod=topic&amp;code=myhome');
			}
		} else {
			$return = (is_string($return) ? $return : "未知错误");
			$this->Messager($return);
		}
	}

		function Forward()
	{
		$uid = MEMBER_ID;
		$member = $this->_member($uid);

		$tid = jget('tid', 'int');

		$list_topic = $this->_topicLogicGet($tid);

		if($list_topic['roottid'])
		{
			$list_topic = $this->_topicLogicGet($list_topic['roottid']);
		}

		$roottid  = (int) $list_topic['tid'];
		$totid    =  (int) $this->Get['tid'];

		$this->Title = "转发微博";

		include(template('forward'));
	}

		function DoForward()
	{
		$totid    = (int) $this->Post['totid'];				$roottid  = (int) $this->Post['roottid'];  		$imgid    = $this->Post['imgid'];
		$type     = ($this->Post['topictype'] ? 'both' : 'forward');
				$content  = trim($this->Post['content']);
		$content  = ($content ? $content : '转发微博');
		$content  = wap_iconv($content,'utf-8',$this->Config['charset'], 1);

		$datas = array(
			'content' => $content,
			'totid' => $totid,
			'imageid' => $imgid,
			'from' => 'wap',
			'type' => $type,
		);

		$return = $this->TopicLogic->Add($datas);

		if (is_array($return)) {
			$msg = '';
			if($return['tid'] < 1) {
				if($return['msg']) {
					$msg = $return['msg'];
				} else {
					$msg = implode(',', $return);
				}
			}

			$this->Messager("【转发成功】" . $msg, 'index.php?mod=topic&amp;code=myhome');
		} else {
			$return = (is_string($return) ? $return : "未知错误");

			$this->Messager($return);
		}
	}

		function DoModify()
	{
		if(MEMBER_ID < 1) {
			$this->Messager('请先登录，游客不能执行此操作');
		}
				$tid = jget('tid', 'int');
		if($tid < 1) {
			$this->Messager('请指定要编辑的微博');
		}

		$topic_info = jlogic('topic')->Get($tid);
		if(!$topic_info) {
			$this->Messager('您要编辑的微博已经不存在了');
		}

		if(jdisallow($topic_info['uid'])) {
			$this->Messager('您没有权限编辑该微博');
		}

		if($this->Get['tid'] > 0)
		{
			$action = 'index.php?mod=topic&amp;code=modify';

			$topiclist = $this->_topicLogicGet($tid);

			if($topiclist==false)
			{
				$this->Messager("您要编辑的微博信息已经不存在!",'index.php?mod=topic&code=myhome');
			}

						$row = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='$tid'");
			$topiclist['content'] = ($row['content'] . $row['content2']);


						$topiclist['content'] = preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~','',$topiclist['content']);

						$topiclist['content'] = strip_tags($topiclist['content']);

						if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
			{
				$topiclist['content'] = $this->TopicLogic->GetForwardContent($topiclist['content']);
			}

			$topiclist['content'] = wap_iconv($topiclist['content']);

			$this->Title = cut_str(strip_tags($topiclist['content']),50)." ";


			$return_messager = $this->Get['return'];

			include(template('topic_modify'));
		}
		else
		{
			$tid = (int) $this->Post['tid'];
			$imageid = (int) $this->Post['imageid'];
			$attachid = (int) $this->Post['attachid'];
			$content = strip_tags($this->Post['content']);

			$sql = "select * from `".TABLE_PREFIX."topic` where `tid`='{$tid}'";
			$query = $this->DatabaseHandler->Query($sql);
			$topiclist=$query->GetRow();


			$content = wap_iconv($content,'utf-8',$this->Config['charset'], 1);
			if(empty($content))
			{
				$return_messager = "modify_content_null";
				$this->Messager(NULL,'index.php?mod=topic&code=modify&tid='.$tid.'&return='.$return_messager);
			}

			$modify_result = $this->TopicLogic->Modify($tid,$content,$imageid,$attachid);

			if(is_array($modify_result))
			{
				$this->Messager('编辑成功','index.php?mod=topic&code=modify&tid='.$tid);
			}
			else
			{
				$this->Messager("编辑失败",'index.php?mod=topic&code=modify&tid='.$tid);
			}
		}

	}

		function Info() {
		$member = $this->_member();

		$this->Title = "消息提示";

		include(template('info'));
	}


		function DelTopic()
	{
		$tid = jget('tid', 'int');

		$this->Messager('确定删除微博，删除后不可恢复','index.php?mod=topic&amp;code=dodel&tid='.$tid);
	}
	function DoDelTopic()
	{
		if(MEMBER_ID < 1) {
			$this->Messager('游客不能执行此操作');
		}
		$tid = jget('tid', 'int');

		if ($tid < 1) {
			$this->Messager('请指定一个您要删除的话题','index.php?mod=topic&amp;code=myhome');
		}

		$topic = $this->TopicLogic->Get($tid);

		if (!$topic) {
			$this->Messager('话题已经不存在了','index.php?mod=topic&amp;code=myhome');
		}

		if (jdisallow($topic['uid'])) {
			$this->Messager('您无权删除该话题','index.php?mod=topic&amp;code=myhome');
		}

		$return = $this->TopicLogic->DeleteToBox($tid);

		$this->Messager(NULL,'index.php?mod=topic&code=myhome');
	}

}

?>
