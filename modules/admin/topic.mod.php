<?php
/**
 * 文件名：topic.mod.php
 * @version $Id: topic.mod.php 5462 2014-01-18 01:12:59Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 微博模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $TopicLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->TopicLogic = jlogic('topic');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
						case 'aboutme':
			case 'signature':
				$this->ASManage();
				break;
			case 'domanage':
				$this->doManage();
				break;

			case 'manage':
				$this->ManageDetail();
				break;
			case 'delmanage':
				$this->delManageDetail();
				break;

			case 'doverify':
				$this->doVerify();
				break;
		    case 'domodify':
				$this->DoModify();
				break;
			case 'modifylist':
				$this->ModifyList();
				break;
			case 'del_img':
				$this->DeleteImg();
				break;
			case 'del_attach':
				$this->DeleteAttach();
				break;
			case 'del_video':
				$this->DeleteVideo();
				break;
			case 'del_music':
				$this->DeleteMusic();
				break;
			case 'delrecycling':
				$this->delRecycling();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	function Main()
	{
		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		if($_GET['pn']) {
			$pn = '&pn='.$_GET['pn'];
		}
		$where_list = array();
		$query_link = 'admin.php?mod=topic'.$pn.'&code='.$this->Code . '&per_page_num=' . $per_page_num;

				$type = $this->Get['type'];
		if($type == 'first'){
			$type_arr['first'] = " selected ";
			$where_list['type'] = "`type` = '$type'";
			$query_link .= "&type=$type";
		}elseif($type == 'forward'){
			$type_arr['forward'] = " selected ";
			$where_list['type'] = "`type` in ('forward','both')";
			$query_link .= "&type=$type";
		}elseif($type == 'reply'){
			$type_arr['reply'] = " selected ";
			$where_list['type'] = "`type` in ('reply','both')";
			$query_link .= "&type=$type";
		}

				$config['channel'] = jconf::get('channel');
		if($config['channel']){
			$channels = $channel_one = is_array($config['channel']['first']) ? $config['channel']['first'] : array();
			$channel_two = is_array($config['channel']['second']) ? $config['channel']['second'] : array();
			$channel_channels = is_array($config['channel']['channels']) ? $config['channel']['channels'] : array();
			foreach($channel_two as $k => $v){$channels[$v['parent_id']]['child'][$k] = $v;}
			$channel = $this->Get['channel'];
						if($channel=='all'){$sel_str=' selected';}else{$sel_str='';}
			$channel_html = '频道：<select name="channel"><option value="">请选择...</option><option value="all"'.$sel_str.'>所有频道</option>';
			foreach($channels as $val){
				if($channel==$val['ch_id']){$sel_str=' selected';}else{$sel_str='';}
			    $channel_html .= '<option value="'.$val['ch_id'].'"'.$sel_str.'>'.$val['ch_name'].'</option>';
				if($val['child']){
					foreach($val['child'] as $v){
						if($channel==$v['ch_id']){$sel_str=' selected';}else{$sel_str='';}
						$channel_html .= '<option value="'.$v['ch_id'].'"'.$sel_str.'>|-'.$v['ch_name'].'</option>';
					}
				}
			}
			$channel_html .= '</select>';
						if(strlen($channel) > 0){
				if($channel == 'all'){
					$where_list['channel'] = "item = 'channel' AND item_id > 0";
					$query_link .= "&channel=all";
				}else{
					$channel = (int)$channel;
					$where_list['channel'] = "item = 'channel' AND item_id IN(".jimplode($channel_channels[$channel]).")";
					$query_link .= "&channel=".$channel;
				}
			}
			unset($channel_one);unset($channel_two);unset($channel_channels);
		}

				$postip = $this->Get['postip'];
		if($postip){
			$where_list['postip'] = " `postip` = '$postip' ";
						$where_list['lastupdatef'] = " `lastupdate` > '" . strtotime(date('Y-m-d',time()))."' ";
			$where_list['lastupdatet'] = " `lastupdate` < '".strtotime(date('Y-m-d',strtotime('+1 day')))."' ";
			$where_list['managetype'] = " managetype = 0";
			$query_link .= "&postip=$postip";
		}
		
				$tids = array();
		$tid = trim($this->Get['tid']);
		if($tid) {
			$_tids = explode(" ", str_replace(array(",", "|", ), " ", $tid));
			foreach($_tids as $_tid) {
				$_tid = jfilter($_tid, 'int');
				if($_tid > 0) {
					$tids[$_tid] = $_tid;
				}
			}
			$total_record = count($tids);
			$query_link .= "&tid=$tid";
		}
				$uid = 0;
		$username = jget('username', 'txt');
		if($username) {
			$uid = jsg_member_uid($username);
			$query_link .= "&username=$username";
		} else {
			$uid = jget('uid', 'int');
		}
		if($uid > 0) {
			$where_list['uid'] = "`uid`='$uid'";
			$query_link .= "&uid=$uid";
			$p = array(
				'type' => 'all',
				'perpage' => $per_page_num,
				'page_url' => $query_link,
			);
			$_rets = jtable('member_topic')->get_tids($uid, $p, 1);
			$tids = $_rets['ids'];
			if(!in_array($this->Code, array('verify', 'del'))) {
				$total_record = $_rets['count'];
				$page_arr = $_rets['page'];
			}
		}
		if($tids) {
			$where_list['tid'] = "`tid` in (".jimplode($tids).")";
		}

				$keyword = trim($this->Get['keyword']);
		if ($keyword) {
			$_GET['highlight'] = $keyword;

			$where_list['keyword'] = build_like_query('content,content2',$keyword);
			$query_link .= "&keyword=".urlencode($keyword);

		}
				$nickname = trim($this->Get['nickname']);
		if ($nickname) {

			$sql = "select `uid`,`nickname` from `".TABLE_PREFIX."members` where `nickname`='{$nickname}' limit 0,1";
			$query = $this->DatabaseHandler->Query($sql);
			$members=$query->GetRow();

			$where_list['uid'] = "`uid`='{$members['uid']}'";
			$query_link .= "&nickname=".urlencode($members['nickname']);
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where_list['timefrom'] = "`lastupdate`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where_list['timeto'] = "`lastupdate`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}
		
		$mtype = $this->Get['mtype'];
		if($mtype != ''){
			if($mtype == 1){
				$where_list['managetype'] = " managetype != 0";
			}else{
				$where_list['managetype'] = " managetype = '$mtype'";
			}
			$mtype_arr[$mtype] = " selected ";
			$query_link .= "&mtype={$mtype}";
		}

		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

		if($this->Code == 'verify' || $this->Code == 'del'){
			$template = 'topic_verify';
			$verify = 1;
			$del = (int)$this->Get['del'];
			if($del){
				$where = $where ? $where. " and managetype = 1 " : " where managetype = 1 ";
				$query_link .= "&del=1";
			}else{
				$where = $where ? $where. " and managetype = 0 " : " where managetype = 0 ";
			}
			$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic_verify` {$where} ";
		}else{
			$template = 'topic';
			$this->Code = 'topic_manage';
			$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."topic` {$where} ";
		}

		if(!$total_record) {
			$total_record = DB::result_first($sql);
		}

		if(!$page_arr) {
			$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		}
		$topic_list = array();
		if($this->Code == 'verify' || $this->Code == 'del') {
			$topic_list = $this->TopicLogic->Get(" {$where} order by `dateline` desc {$page_arr['limit']} ",'*','Make',TABLE_PREFIX.'topic_verify','id');
			$action = "admin.php?mod=topic&code=doverify";
			if($topic_list){
				foreach ($topic_list as $key=>$val) {
					if($val['type'] == 'forward' && $val['roottid'] > 0){
						$topic_list[$key]['root_topic'] = $this->TopicLogic->Get($val['roottid']);
					}
					if($val['longtextid'] > 0) {
						$topic_list[$key]['content'] = jlogic('longtext')->longtext($val['longtextid'], $val['tid']);
					}
				}
			}
		}else{
			if($tids) {
				$topic_list = $this->TopicLogic->Get($tids);
			} else {
				$topic_list = $this->TopicLogic->Get(" {$where} order by `dateline` desc {$page_arr['limit']} ");
			}
			$action = "admin.php?mod=topic&code=domanage";
			if($topic_list){
				foreach ($topic_list as $key=>$val) {
					if($val['managetype']==0 ||$val['managetype']==1){
						$topic_list[$key]['manage_type'][1] = " checked ";
					}else{
						$topic_list[$key]['manage_type'][$val['managetype']] = " checked ";
					}
					if($val['type'] == 'forward' && $val['roottid']) {
						$topic_list[$key]['root_topic'] = $this->TopicLogic->Get($val['roottid']);
					}
					if($val['longtextid'] > 0) {
						$topic_list[$key]['content'] = jtable('topic_more')->longtext($val['tid']);
					}
				}
			}
		}


		include(template('admin/'.$template));
	}

	
	function ManageDetail(){
		$where = "";
		$query_link = "admin.php?mod=topic&code=manage";
		$tid = $this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid'];
		if($tid > 0){
			$where .= " and d.tid = '$tid' ";
			$query_link .= "&tid=$tid";
		}
		$nickname = $this->Post['nickname'] ? $this->Post['nickname'] : $this->Get['nickname'];
		if($nickname){
			$where .= " and m.nickname = '$nickname' ";
			$query_link .= "&nickname=$nickname";
		}
				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where .= " and d.`dateline`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where .= " and d.`dateline`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}

		$sql = "select count(*) from ".TABLE_PREFIX."manage_detail d
				left join ".TABLE_PREFIX."members tm on tm.uid = d.tuid
				left join ".TABLE_PREFIX."members m on m.uid = d.uid
				where 1 $where ";
		$total_record = DB::result_first($sql);
		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));

		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');

		$sql = "select d.*,tm.nickname as tnickname,m.nickname as nickname
				from ".TABLE_PREFIX."manage_detail d
				left join ".TABLE_PREFIX."members tm on tm.uid = d.tuid
				left join ".TABLE_PREFIX."members m on m.uid = d.uid
				where 1  $where
				order by d.dateline desc ,d.tid desc
				$page_arr[limit] ";
		$query = $this->DatabaseHandler->Query($sql);
		$manageList = array();
		while (false != ($rs = $query->GetRow())){
			$manageList[$rs['id']] = $rs;
		}
		$action = "admin.php?mod=topic&code=delmanage";
		include(template('admin/topic_manage_detail'));
	}

		function delManageDetail(){
		if(MEMBER_ID != 1){
			$this->Messager("请用初始帐号执行此操作");
		}
		$ids = (array) $this->Post['ids'];
		foreach ($ids as $val) {
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."manage_detail where id = '$val'");
		}
		$this->Messager("操作成功");
	}

	
	function doManage(){

		$type = jget('type','int','G');
		$tid  = jget('tid','int','G');
		$TopicManage = jlogic('topic_manage');

		if($type && $tid){

			$score = jget('score','int','G');
			$TopicManage->doManage($tid,$type,$score);

			$str = '微博ID:['.$tid.']';
			$type === 1 && $return_str = $str."显示成功";
			$type === 2 && $return_str = $str."禁止成功";
			$type === 8 && $return_str = $str."禁转成功";
			$type === 9 && $return_str = $str."禁评成功";
			$type === 3 && $return_str = $str."待审成功";
			$type === 4 && $return_str = $str."删除成功";
			$this->Messager($return_str);

		}else{

			$managetype = get_param('managetype');
			$managetypescore = get_param('managetypescore');

			foreach ($managetype as $key=>$val) {
				$score = (int)$managetypescore[$key];
				$TopicManage->doManage($key,$val,$score);
			}
			$this->Messager('微博操作成功');

		}
	}

	
	function ASManage(){

		$code = $this->Code;
		if($code == 'signature'){
			$time = 'signtime';
		}else{
			$time = 'aboutmetime';
		}
		$action = "admin.php?mod=topic&code=$code";

		$managetype = (array) $this->Post['managetype'];

		if($managetype){
			foreach ($managetype as $val=>$act) {
				if($act==1){
										$sql = "update ".TABLE_PREFIX."members set $time = '".time()."' where uid = '$val'";
				}else{
										$sql = "update ".TABLE_PREFIX."members set $code = '',$time = 0 where uid = '$val'";
				}
				$this->DatabaseHandler->Query($sql);
			}
			$this->Messager("操作成功");
		}

		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$query_link = 'admin.php?mod=topic&code='.$code;
		$where = '';

				$mtype = (int)$this->Get['mtype'];
		if($mtype){
			$where .= " and $time != 0 ";
			$query_link .="&mtype=$mtype";
			$mtype_arr[1] = ' selected ';
		}else{
			$where .= " and $time = 0 ";
			$query_link .="&mtype=$mtype";
		}

				$nickname = $this->Get['nickname'] ? $this->Get['nickname'] : $this->Post['nickname'];
		if($nickname){
			$where .= " and nickname = '$nickname' ";
			$query_link .="&nickname=$nickname";
		}

				$keyword = $this->Get['keyword'] ? $this->Get['keyword'] : $this->Post['keyword'];
		if($keyword){
			$where .= " and $code like '%$keyword%' ";
			$query_link .="&keyword=$keyword";
		}

				$timefrom = $this->Get['timefrom'];
		if($timefrom){
			$str_time_from = strtotime($timefrom);
			$where .= " and `lastactivity`>'$str_time_from'";
			$query_link .= "&timefrom=".$timefrom;
		}
				$timeto = $this->Get['timeto'];
		if($timeto){
			$str_time_to = strtotime($timeto);
			$where .= " and `lastactivity`<'$str_time_to'";
			$query_link .= "&timeto=".$timeto;
		}

		$total_record = DB::result_first("select count(*) from ".TABLE_PREFIX."members where $code != '' $where ");
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200 500');
		$sql = "select uid,username,nickname,lastip,lastactivity,$this->Code
				from ".TABLE_PREFIX."members
				where $code != ''  $where
				order by lastactivity desc
				$page_arr[limit]";
		$query = $this->DatabaseHandler->Query($sql);
		$member = array();
		while (false != ($rs = $query->GetRow())){
			$rs['face'] = face_get($rs['uid']);
			$rs['content'] = $rs[$code];
			$member[$rs['uid']] = $rs;
		}

		include(template('admin/as_manage'));
	}

		function doVerify() {
		$manage = (array) jget('manage');
		$id = jget('id','int');
		$type = jget('type');
		if($id > 0 && $type) {
			$manage[$id] = $type;
		}

		$_POST['syn_to_sina'] = 1;
		$_POST['syn_to_qqwb'] = 1;
		$_POST['syn_to_kaixin'] = 1;
		$_POST['syn_to_renren'] = 1;
		if(is_array($manage) && count($manage)) {
			foreach ($manage as $key=>$value) {
				$key = (int) $key;
				if($value == 'keep') {
					continue;
				} elseif($key > 0 && ($tv = DB::fetch_first("SELECT * FROM ".DB::table('topic_verify')." WHERE `id`='$key'"))) {
					if ($value == 'yes' || $value == 2 || $value == 8 || $value == 9) {
						if($tv['longtextid']) {
							$longtext = jlogic('longtext')->longtext($tv['longtextid'], $tv['tid']);
							if($longtext) {
								$tv['content'] = $longtext;
							}
						} else {
							$tv['content'] .= $tv['content2'];
						}
						$tv['content'] = strip_tags(htmlspecialchars_decode($tv['content']));
						$tv['content'] = addslashes($tv['content']);
						

						$tv['verify'] = "verify";
						$managetype = $tv['managetype'];
						$tv['managetype'] = in_array($value,array('2','8','9')) ? $value : 1;
						$tv['checkfilter'] = 1;
						if($tv['content']) {
							$rets = $this->TopicLogic->Add($tv,0,$tv['imageid']);
						}
						

												if(1 == $managetype) {
							$managetype = 4;
						}else{
							$managetype = $tv['tid'] ? 3 : 0;
						}
						if($tv['tid']) {
							jlogic('topic_manage')->manageDetail($tv['tid'],$managetype,$tv['managetype'],1);
						}

				        				        jtable('topic_verify')->delete($key);
				        				        if($tv['longtextid']) {
				        	jlogic('longtext')->rm($tv['longtextid']);
				        }
					}elseif($value == 'no'){
						$managetype = $tv['tid'] ? 3 : 0;
						if($tv['tid']){
							jlogic('topic_manage')->manageDetail($tv['tid'],$managetype,4,1);
						}
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."topic_verify set `managetype` = '1' where id = '$key'");
					}elseif($value == 'dodel') {
						if($tv['tid']) {
							jlogic('topic_manage')->manageDetail($tv['tid'],4,5);
						}
						$this->TopicLogic->Delete($tv['id']);
					}
				}
			}
		}


		$this->Messager("操作成功");
	}

	
	function delRecycling(){
		$query = $this->DatabaseHandler->Query("select `id` from `".TABLE_PREFIX."topic_verify` where managetype = 1 limit 100");
		while ($rs = $query->GetRow()){
			$ids[$rs['id']] = $rs['id'];
		}
		if($ids){
			foreach ($ids as $id) {
				$this->TopicLogic->Delete($id);
			}
			$this->delRecycling();
		}else{
			$this->Messager('清空回收站成功','admin.php?mod=topic&code=del&del=1');
		}
	}

	function ModifyList()
	{
		 $title = "编辑微博";
		 $tid = (int) $this->Get['tid'];
		 $verify = $this->Get['verify'];

		 if(!empty($tid)) {
		 	if($verify){
				$action = "admin.php?mod=topic&code=domodify&verify=verify";
				$sql = "select  T.tid , T.imageid, T.videoid , T.musicid ,T.content,T.content2 ,M.nickname,M.username , T.*
						from `".TABLE_PREFIX."members` M
						left join `".TABLE_PREFIX."topic_verify` T on M.uid=T.uid
						where T.tid='{$tid}' limit 0,1";
			}else{
				$action = "admin.php?mod=topic&code=domodify";
				$sql = "select  T.tid , T.imageid, T.videoid , T.musicid ,T.content,T.content2 ,M.nickname,M.username , T.*
						from `".TABLE_PREFIX."members` M
						left join `".TABLE_PREFIX."topic` T on M.uid=T.uid
						where T.tid='{$tid}' limit 0,1";
			}
			$query = $this->DatabaseHandler->Query($sql);
			$topiclist=$query->GetRow();


			if($topiclist['longtextid'] > 0) {
								$topiclist['content'] = jtable('topic_more')->get_longtext($topiclist['tid'], $verify);
			} else {
				$topiclist['content'] = $topiclist['content'].$topiclist['content2'];
			}

		 } else {
		 		$this->Messager(NULL,'admin.php?mod=topic',0);
		 }

		 if($topiclist==false)
		 {
		 		$this->Messager("您要编辑的微博信息已经不存在!");
		 }


        		$topiclist['content'] = preg_replace('~<U ([0-9a-zA-Z]+)>(.+?)</U>~','',$topiclist['content']);

	    		$topiclist['content'] = strip_tags($topiclist['content']);

				if('both'==$topiclist['type'] || 'forward'==$topiclist['type'])
		{
			$topiclist['content'] = $this->TopicLogic->GetForwardContent($topiclist['content']);
		}


		$image_list = array();
		if($topiclist['imageid']){
			$image_id_arr = explode(",",$topiclist['imageid']);
			foreach ($image_id_arr as $value) {
				$value = (int) $value;
				if($value > 0) {
					$img = jtable('topic_image')->info($value);
		 		 	$image_list[$img['id']]['id'] = $img['id'];
					$image_list[$img['id']]['img_path'] = topic_image($img['id']);
				}
			 }
		 }

		$attach_list = array();
		if($topiclist['attachid']){
			$attach_id_arr = explode(",",$topiclist['attachid']);
			foreach ($attach_id_arr as $value) {
				if(($value = (int) $value) < 1) continue;

				$attach = jtable('topic_attach')->info($value);
	 		 	$attach_list[$attach['id']]['id'] = $attach['id'];
				$attach_list[$attach['id']]['attach_name'] = topic_attach($attach['id'],'name');
				$attach_list[$attach['id']]['attach_score'] = topic_attach($attach['id'],'score');
				$attach_list[$attach['id']]['attach_img'] = 'images/filetype/'.topic_attach($attach['id'],'filetype').'.gif';
			 }
		 }

		 if($topiclist['videoid']) {
			  $video = jtable('topic_video')->info($topiclist['videoid']);

			  $videoid 	 = $video['id'];
			  $videohost = $video['video_hosts'];
			  $videolink = $video['video_link'];
			  $videoimg  = $video['video_img'];
		 }

		 if($topiclist['musicid']) {
			  $topic_music = jtable('topic_music')->info($topiclist['musicid']);

			  $musicid_id = $topic_music['id'];
			  $ContentMusicid =  $topic_music['music_url'];
		 }

		include template('admin/topic_info');
	}

	function DoModify()
	{
		$verify = (int) get_param('verify');
		$tid = (int) get_param('tid');

		if($verify){			$sql = "select * from `".TABLE_PREFIX."topic_verify` where `tid` = '{$tid}' limit 0,1";
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$sql = "select * from `".TABLE_PREFIX."topic` where `tid` = '{$tid}' limit 0,1";
			$table = TABLE_PREFIX."topic";
		}
		$query = $this->DatabaseHandler->Query($sql);
		$topiclist=$query->GetRow();

		if($topiclist['content2'])
		{
			$sql = "update `" . TABLE_PREFIX . "topic` set `content2`='' where `tid`='{$tid}'";
        	$this->DatabaseHandler->Query($sql);
		}

		preg_match_all('~(?:https?\:\/\/)(?:[A-Za-z0-9_\-]+\.)+[A-Za-z0-9]{2,4}(?:\/[\w\d\/=\?%\-\&_\~`@\[\]\:\+\#]*(?:[^<>\'\"\n\r\t\s])*)?~',$topiclist['content'],$match);
		$is_post_url = implode(glue,$match[0]);

		        preg_match_all('~(.+?)</U>~', $topiclist['content'], $URL);
        $url_tag = implode($URL[0]);

		$content		=  strip_tags($this->Post['content']);
		$totid 			=  $topiclist['totid'];
		$imageid 		=  $topiclist['imageid'];
		$attachid 		=  $topiclist['attachid'];
		$type 			=  $topiclist['type'];
		$uid  			=  $topiclist['uid'];
		$username 		=  $topiclist['username'];
		$tid  			=  $topiclist['tid'];

		$return = $this->TopicLogic->Modify($tid,$content,$imageid,$attachid,$table);
		$newcontent = $url_tag.$content;

															        
				if(isset($this->Post['attach_score']) && $this->Post['attach_id'] && is_array($this->Post['attach_id'])){
			foreach($this->Post['attach_score'] as $key => $value){
				if($this->Post['old_attach_score'][$key] != $value){
					DB::update('topic_attach', array('score' => $value), array('id' => $this->Post['attach_id'][$key]));
				}
			}
		}

		if(!is_array($return)) {
			$this->Messager("【编辑失败】{$return}");
		}
		else {
			$this->Messager("编辑成功",'admin.php?mod=topic&code='.$verify);
	 	}
	}

	function DeleteImg()
	{
		$tid = (int) $this->Get['tid'];
		$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);

		jlogic('image')->delete($ids);

		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}

		$imageid = DB::result_first("select imageid from $table where tid = '$tid'");

		if(!$imageid) {
		    $this->Messager("请指定要删除的对象");
		}
		$image_id_arr = explode(",",$imageid);
		foreach ($image_id_arr as $key=>$value) {
			if($value == $ids){
				unset($image_id_arr[$key]);
			}
		}
		$new_imageid = implode(",",$image_id_arr);
		$updata = "update $table set `imageid`='$new_imageid' where `tid`= '$tid'";
		$result = $this->DatabaseHandler->Query($updata);

		$this->Messager("操作成功");
	}

function DeleteAttach()
	{
		$tid = (int) $this->Get['tid'];
		$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$uid = DB::result_first("select uid from ".TABLE_PREFIX."topic_attach where id = '$ids'");
		if(!$uid) {
		    $this->Messager("您要删除的附件不存在！");
		}
		$sql = "delete from `".TABLE_PREFIX."topic_attach` where `id`='{$ids}'";
		$this->DatabaseHandler->Query($sql);
				update_credits_by_action('attach_del',$uid);

		jio()->DeleteFile(topic_attach($ids,'file'));

		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}

		$attachid = DB::result_first("select attachid from $table where tid = '$tid'");
		if($attachid) {
			$attach_id_arr = explode(",",$attachid);
			foreach ($attach_id_arr as $key=>$value) {
				if($value == $ids){
					unset($attach_id_arr[$key]);
				}
			}
			$new_attachid = implode(",",$attach_id_arr);
			$updata = "update $table set `attachid`='$new_attachid' where `tid`= '$tid'";
			$result = $this->DatabaseHandler->Query($updata);
		}
		$this->Messager("操作成功");
	}

	function DeleteVideo()
	{
        	$ids =  ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
    		if(!$ids) {
    			$this->Messager("请指定要删除的对象");
    		}

			$sql = "select `id`,`tid`,`video_img` from `".TABLE_PREFIX."topic_video` where `id`='".$ids."' ";
			$query = $this->DatabaseHandler->Query($sql);
			$topic_video=$query->GetRow();


			$sql = "delete from `".TABLE_PREFIX."topic_video` where `id`='{$topic_video['id']}'";
			$this->DatabaseHandler->Query($sql);


			jio()->DeleteFile($topic_video['video_img']);

			$verify = $this->Get['verify'];
			if($verify){
				$table = TABLE_PREFIX."topic_verify";
			}else{
				$table = TABLE_PREFIX."topic";
			}

			$updata = "update `$table` set `videoid`='0' where `tid`='{$topic_video['tid']}'";
		    $result = $this->DatabaseHandler->Query($updata);

			$this->Messager("操作成功");
	}

	function DeleteMusic()
	{
		$ids =  (int) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);
		$sql = "delete from `".TABLE_PREFIX."topic_music` where `tid`='{$ids}'";
		$this->DatabaseHandler->Query($sql);

		$verify = $this->Get['verify'];
		if($verify){
			$table = TABLE_PREFIX."topic_verify";
		}else{
			$table = TABLE_PREFIX."topic";
		}

		$updata = "update `$table` set `musicid`='0' where `musicid`='$ids'";
		$result = $this->DatabaseHandler->Query($updata);

		$this->Messager("操作成功");
	}

}

?>
