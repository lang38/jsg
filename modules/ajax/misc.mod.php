<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename misc.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 456154482 20137 $
 */




if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->my = $GLOBALS['_J']['member'];

		if (!MEMBER_ID && $this->Code != 'seccode') {
			js_alert_output("请先登录或者注册一个帐号");
		}

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'checkuser':
				$this->checkUser();
				break;
			case 'sendmailqueue':
				$this->sendMailQueue();
				break;
			case 'sendmail':
				$this->TestSendMail();
				break;
			case 'fansgroup_select':
				$this->FansGroup_Select();
				break;
			case 'atuser':
				$this->AtUser();
				break;
			case 'atuserw':
				$this->AtUserw();
				break;
			case 'tag':
				$this->Tag();
				break;
			case 'seccode':
				$this->Seccode();
				break;
			case 'publishbox':
				$this->PublishBox();
				break;
			case 'report':
				$this->Report();
				break;
            case 'setsync':
                $this->setSync();
                break;
			case 'cpcp':
				$this->Company();
				break;
			case 'department':
				$this->Department();
				break;
			case 'cpuser':
				$this->Cpuser();
				break;
			case 'listc':
				$this->Listc();
				break;
			case 'listcp':
				$this->Listcp();
				break;
			case 'acpcp':
				$this->Acpcp();
				break;
			case 'loadimg':
				$this->Loadimg();
				break;
			default:
				exit;
				break;
		}

		exit;
	}

	
	function checkUser() {
		$sendmailday = $this->Config['sendmailday'];
		if($sendmailday > 0) {
			$lastactivity = TIMESTAMP - $sendmailday * 86400;
			$sql = "select `uid`,`email`,`notice_at`,`notice_pm`,`notice_reply`,`notice_fans`,`notice_event`,
						`newpm`,`at_new`,`comment_new`,`event_new`,`fans_new`
					 from `".TABLE_PREFIX."members`
					 where `lastactivity` < '$lastactivity'
					 	and `last_notice_time` < '$lastactivity'
					 	and `email` != ''
					 	and `email_checked` = 1
					 order by `last_notice_time` asc, `lastactivity` asc
					 limit 20 ";
			$query = DB::query($sql);
			while ($rs = DB::fetch($query)) {
				$msg = array();
				$mailQueue = array();
				$mailQueue['uid'] = $rs['uid'];
				$mailQueue['email'] = $rs['email'];
				$mailQueue['dateline'] = TIMESTAMP;

				if($rs['notice_at'] && $rs['at_new']) $msg['at_new'] = $rs['at_new'];
				if($rs['notice_pm'] && $rs['newpm']) $msg['newpm'] = $rs['newpm'];
				if($rs['notice_reply'] && $rs['comment_new']) $msg['comment_new'] = $rs['comment_new'];
				if($rs['notice_fans'] && $rs['fans_new']) $msg['fans_new'] = $rs['fans_new'];
				if($rs['notice_event'] && $rs['event_new']) $msg['event_new'] = $rs['event_new'];
				$msg['load'] = 1;
				$mailQueue['msg'] = serialize($msg);
				$row = jtable('mailqueue')->row($rs['uid']);
				if(!$row) {
					jtable('mailqueue')->insert($mailQueue, true);
				} else {
					if($row['dateline'] > 0) {
						unset($mailQueue['dateline']);
					}
					jtable('mailqueue')->update($mailQueue, array('uid' => $mailQueue['uid']));
				}
			}
		}
	}

	
	function sendMailQueue() {
		if($this->Config['sendmailday'] < 1) {
			return ;
		}

		$time = date('Y-m-d H:i:s', TIMESTAMP);
		$fileLock = ROOT_PATH . 'data/sendmail.lock';
		$fileTime = @filemtime($fileLock);

		if(TIMESTAMP - $fileTime < 300) {
			return ;
		}

		@touch($fileLock, TIMESTAMP);

		$msg_config = jconf::get('mail_msg');

		$site_url = $this->Config['site_url'];
		$site_name =  $this->Config['site_name'];

						$query = DB::query("select * from `".TABLE_PREFIX."mailqueue` where `dateline`>'0' and `dateline`<='".TIMESTAMP."' order by `dateline` asc limit 20 ");
		$mail = array();
		while ($rs = DB::fetch($query)) {
			$mail[$rs['qid']] = $rs;

			jtable('mailqueue')->del($rs['uid']);
			jtable('members')->update_count($rs['uid'], 'last_notice_time', TIMESTAMP);
		}

		if($mail) {


			$recommend = cache_file('get','all-recommend-top10');
			$recommend_tips = '';
			if($recommend){
				foreach ($recommend as $key => $val) {
					$recommend_tips .="<br><a href='$site_url/index.php?mod=topic&code=$key' target='_blank'>{$val['content']}</a>";
				}
			}

			foreach ($mail as $k => $v) {
				$member_info = DB::fetch_first("select `nickname`,`lastactivity` from `".TABLE_PREFIX."members` where `uid` = '{$v['uid']}'");
				$nickname = $member_info['nickname'];
				$load = round((TIMESTAMP - $member_info['lastactivity']) / 86400);
				$load = $load ? $load."天" : '';
				$subject = $msg_config['subject'] ? str_replace(array(
					'newpm',
					'at_new',
					'comment_new',
					'event_new',
					'fans_new',
					'qun_new',
					'vote_new',
					'dig_new',
					'channel_new',
					'company_new',
					'load',
					'site_url',
					'site_name',
                    'time',
                    'nickname',
                    'time'),array(
					$newpm,
					$at_new,
					$comment_new,
					$event_new,
					$fans_new,
					$qun_new,
					$vote_new,
					$dig_new,
					$channel_new,
					$company_new,
					$load,
					$site_url,
					$site_name,
                    date('Y:m:d H:i:s'),
                    $nickname,
                    $time),$msg_config['subject']) : $nickname.'，您在'.$this->Config['site_name'].'有新的消息('.data('Y-m-d H:i:s').')';
				$msg = array();
				if($v['msg']){
					$msg = unserialize($v['msg']);
				}else {
					continue;
				}
				$site_new_data = array();
				$newpm = $msg['newpm'] ? $msg['newpm'] : 0;$newpm && $site_new_data['newpm'] = "有{$newpm}条未读私信";

				$at_new = $msg['at_new'] ? $msg['at_new'] : 0;$at_new && $site_new_data['at_new'] = "被@{$at_new}次";

				$comment_new = $msg['comment_new'] ? $msg['comment_new'] :0;$comment_new && $site_new_data['comment_new'] = "被评论{$comment_new}次";

				$event_new = $msg['event_new'] ? $msg['event_new'] : 0;$event_new && $site_new_data['event_new'] = "有{$event_new}个活动更新";

				$fans_new = $msg['fans_new'] ? $msg['fans_new'] : 0 ;$fans_new && $site_new_data['fans_new'] = "新增加{$fans_new}个粉丝";

				$qun_new = $msg['qun_new'] ? $msg['qun_new'] : 0;$qun_new && $site_new_data['qun_new'] = "有{$qun_new}个".$this->Config[changeword][weiqun]."更新";

				$vote_new = $msg['vote_new'] ? $msg['vote_new'] : 0;$vote_new && $site_new_data['vote_new'] = "投票有{$vote_new}个更新";

				$dig_new = $msg['dig_new'] ? $msg['dig_new'] : 0;$dig_new && $site_new_data['dig_new'] = "被赞{$dig_new}次";

				$channel_new = $msg['channel_new'] ? $msg['channel_new'] : 0;$channel_new && $site_new_data['channel_new'] = "频道有{$channel_new}个更新";

				$company_new = $msg['company_new'] ? $msg['company_new'] : 0;$company_new && $site_new_data['company_new'] = "单位有{$company_new}个更新";

				$site_new_data = $site_new_data ? '<br>您'.implode('，',$site_new_data) .'。' : '';


				if($msg_config['msg']){
					$message = $msg_config['msg'];
					$message = str_replace(array(
						'newpm',
						'at_new',
						'comment_new',
						'event_new',
						'fans_new',
						'qun_new',
						'vote_new',
						'dig_new',
						'channel_new',
						'company_new',
						'load',
						'site_url',
						'site_name',
	                    'time',
	                    'nickname',
	                    'site_new_data','time'),array(
						$newpm,
						$at_new,
						$comment_new,
						$event_new,
						$fans_new,
						$qun_new,
						$vote_new,
						$dig_new,
						$channel_new,
						$company_new,
						$load,
						$site_url,
						$site_name,
	                    date('Y:m:d H:i:s'),
	                    $nickname,
	                    $site_new_data,$time),$message);
				} else {
					$message = "尊敬的$nickname：<br>您好！<br>在未登录{$site_name}的{$load}期间，您收到了一些信息：".
							   "$site_new_data<br>点击<a href='$site_url'>{$site_url}</a>查看" .
							   "<br><br>（<font color='gray'>如此邮件提醒对您产生了干扰，请<a href='$site_url/index.php?mod=settings&code=sendmail' targegt='_blank'>点击修改提醒设置</a></font>）<br>".data('Y-m-d H:i:s');
				}

				$message .= ($recommend_tips ? '<br><br><b>官方推荐内容：</b>'.$recommend_tips : '');
				send_mail($v['email'],$subject,$message);
				sleep(rand(1, 3));
			}
		}
	}

	
	function TestSendMail(){
		$k = jget('k','int');

		$smtp = jconf::get('smtp');

		$test_smtp = $smtp['smtp'][$k];


		jfunc('mail');
		$ret = _send_mail_by_smtp($test_smtp['mail'],$this->Config['site_name'] . '-' . date('Y-m-d H:i:s'),'邮件测试正文---'.date('Y-m-d H:i:s'),$test_smtp);
		if($ret){
			echo '发送成功。';
		} else {
			echo '发送不成功，请检查配置是否正确。';
		}
	}

	
	function FansGroup_Select()
	{
		
	}

	
	function AtUserw()
	{
		$inkey = jget('key');$type = jget('type');$listid = jget('id');
		$atuserlist = 'atuserlist'.($type ? '_'.$type : '').($listid ? '_'.$listid : '');
		$atuser_search = 'atuser_search'.($type ? '_'.$type : '').($listid ? '_'.$listid : '');
		if($this->Config['media_open']){
			$cache_id = 'ajax/misc/recommendgroup';
			if(false === ($rec_group = cache_db('get', $cache_id))) {
				$rec_group = DB::fetch_all("SELECT id,media_name FROM ".DB::table('media')." WHERE media_count>0 ORDER BY `order` DESC LIMIT 10 ");
				cache_db('set', $cache_id, $rec_group, 3600);
			}
		}
		$cache_id = 'ajax/misc/buddygroup-'.MEMBER_ID;
		if(false === ($bud_group = cache_db('get', $cache_id))) {
			$bud_group = DB::fetch_all("SELECT id,name FROM ".DB::table('buddy_follow_group')." WHERE uid='".MEMBER_ID."' AND count>0 ORDER BY `order` DESC LIMIT 20 ");
			cache_db('set', $cache_id, $bud_group, 3600);
		}
		$atuser = array();
		$limit = 10;
		$rets = jtable('topic_mention')->my_hot_at(MEMBER_ID, $limit);
		if($rets) {
			foreach($rets as $row) {
				$row = jsg_member_make($row);
				$atuser[] = $row;
			}
		}
		$defvalue = 'pm'==$type ? '按昵称查找用户' : '搜索要@的好友';
		include(template('showatuserw'));
	}

		function Loadimg(){
		$h_key = jget('divid');
		$albums = jlogic('image')->getalbum();
		include(template('imgupload8'));	}

		function Listc()
	{
		$CpLogic = jlogic('cp');
		$id = jget('id');
		$j = (int)jget('j')+1;
		if($id){
			$company = $CpLogic->get_list_company($id,'id ASC');
		}
		$html = '';
		if($company){
			$html .= '<select name="cids[]" onchange="listnextc(this,\''.$j.'\');"><option value="">请选择...</option>';
			foreach($company as $val){
				$html .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
			}
			$html .= '</select>';
		}
		echo $html;
	}

		function Listcp()
	{
		$CpLogic = jlogic('cp');
		$id = jget('id');
		$j = (int)jget('j')+1;
		$table = jget('t');
		$cid = jget('cid');
		$html = '';
		if($id || 'department'==$table){
		$datas = $CpLogic->get_down_cp($table,$id,$cid);
		if($cid && !$datas){
			$html .= '<select name="'.$table.'ids[]"><option value="">请选择...</option></select>';
		}elseif($datas){
			$html .= '<select name="'.$table.'ids[]" onchange="listcphtml(this,\''.$table.'\',\''.$j.'\');"><option value="">请选择...</option>';
			foreach($datas as $val){
				$html .= '<option value="'.$val['id'].'">'.$val['name'].'</option>';
			}
			$html .= '</select>';
		}
		}
		echo $html;
	}

		function Company()
	{
		$CpLogic = jlogic('cp');
		$id = jget('id');
		$type = jget('t');
		$href = jget('h');
		$html = $CpLogic->Getulli($href,$type,$id);
		echo $html;
	}

		function Department()
	{
		$CpLogic = jlogic('cp');
		$id = jget('id');
		$html = $CpLogic->Getulli(0,'department',0,$id);
		echo $html;
	}

		function Acpcp()
	{
		$CpLogic = jlogic('cp');
		$id = jget('id');
		$type = jget('t');
		$html = $CpLogic->GetTable($type,$id);
		echo $html;
	}

		function Cpuser()
	{
		$type = jget('t');
		$id = jget('id');
		$CpLogic = jlogic('cp');
		$users = $CpLogic->get_list_user($type,$id);
		$html = '<ul class="showatuser">';
		foreach($users as $val) {
			$val = jsg_member_make($val);
			$html .= "<li><a href='".jurl('index.php?mod='.$val['username'])."' target='_blank'><img onerror='javascript:faceError(this);' src='{$val['face']}'></a><span><a href='".jurl('index.php?mod='.$val['username'])."' target='_blank'>".$val['nickname']."</a><span></li>";
        }
		$html .= '</ul>';
		echo $html;
	}

	
	function AtUser() {
		$limit = jget('limit', 'int');
		if ($limit < 1 || $limit > 100) {
			$limit = 10;
		}
		$nickname = jget('q', 'txt');
		$from = jget('from');
		$type = jget('type');
		$acode = jget('acode');
		$id = jget('id','int');
		$inkey = jget('key');
		$nickname = get_safe_code($nickname);
		$nl = strlen($nickname);

		$rets = array();
		$uids = array();
		if($acode=='bud'){
			$uids = jtable('buddy_follow_group_relation')->get_my_group_uids(MEMBER_ID, $id);
			$rets = DB::fetch_all("SELECT `uid`, `nickname`, `ucuid`, `face` FROM ".DB::table('members')." WHERE uid IN(".jimplode($uids).") ORDER BY `topic_count` DESC	LIMIT 10 ");
		}elseif($acode=='rec'){
			$rets = DB::fetch_all("SELECT `uid`, `nickname`, `ucuid`, `face` FROM ".DB::table('members')." WHERE `media_id`='{$id}' ORDER BY `media_order_id` DESC	LIMIT 10 ");
		}elseif($acode=='budn'){
			$buddyids = get_buddyids(array('uid'=>MEMBER_ID,'result_count'=>10, 'gids'=>''));
			$rets = DB::fetch_all("SELECT `uid`, `nickname`, `ucuid`, `face` FROM ".DB::table('members')." WHERE uid IN(".jimplode($buddyids).") ORDER BY `topic_count` DESC	LIMIT 10 ");
		}else{
			if ($nl < 1 || $nl > 50) {
				#一周内我经常AT的人
				$rets = jtable('topic_mention')->my_hot_at(MEMBER_ID, $limit);
			} else {
													$cache_id = 'ajax/misc/AtUser-' . $nickname;
					if(false === ($rets = cache_db('get', $cache_id))) {
						$where_sql = " ".build_like_query("nickname", $nickname)." ";
						$rets = DB::fetch_all("SELECT `uid`, `nickname`, `ucuid`, `face`
									FROM ".DB::table('members')."
									WHERE {$where_sql}
									ORDER BY `fans_count` DESC, `lastactivity` DESC
									LIMIT {$limit} ");
						cache_db('set', $cache_id, $rets, 3600);
					}
							}
		}
		if($rets) {
			foreach($rets as $row) {
				$row = jsg_member_make($row);
				if($from == 'admin'){
					echo "<li>{$row['nickname']}</li>";
				}elseif($from == 'web'){
					echo "<li onclick=\"atcin_user('{$inkey}','{$row[nickname]}','{$type}');\"><img onerror='javascript:faceError(this);' src='{$row['face']}'><span>{$row['nickname']}</span></li>";
				}else{
					echo "{$row['nickname']}|{$row['uid']}|{$row['face']}\n";
				}
			}
		}
		exit;
	}

	
	function Tag()
	{
		$limit = intval($this->Get['limit']);
		$tag = trim($this->Get['q']);
		if (empty($tag)) {
			exit;
		}
		if (empty($limit)) {
			$limit = 10;
		}

		$order_sql = " ORDER BY total_count DESC ";
		$tag = get_safe_code($tag);
		if ($tag) {
			$where_sql = " ".build_like_query("name", $tag)." ";
			$query = DB::query("SELECT id,name
								FROM ".DB::table('tag')."
								WHERE {$where_sql}
								$order_sql
								LIMIT {$limit} ");
			while ($value = DB::fetch($query)) {
				echo $value['id'].'|'.$value['name']."\n";
			}
		}
		exit;
	}

	
	function Seccode()
	{
		include(template('misc_seccode'));
	}

	
	function PublishBox()
	{
				global $_J;
		$channels = jlogic('channel')->get_pub_channel();
		$this->Channel_enable = $channels['channel_enable'];
		$this->Channels = $channels['channels'];
		$type = trim($this->Get['type']);
		$this->Code = $type;
		$this->item = $item = trim($this->Get['item']);
		$this->item_id = $itemid = jget('itemid','int','G');
		if($item == 'channel' && $itemid > 0){
			$ChannelLogic = jlogic('channel');
			if($ChannelLogic->is_exists($itemid)){
				$ch_info = $ChannelLogic->id2category($itemid);
				if(empty($ch_info['purview']) || ($ch_info['purview'] && in_array($_J['member']['role_id'],explode(',',$ch_info['purview'])))){
					$post_item_name = $item;$post_item_id = $itemid;
					$post_channel_name = $ch_info['ch_name'].'<em onclick="c_cut();">×</em>';
				}else{
					$post_channel_name = '<font color=gray>《'.$ch_info['ch_name'].'》频道您无权限发布，请更换</font><em onclick="c_cut();">×</em>';
				}
				if($ch_info['verify'] && !in_array($_J['member']['role_id'],explode(',',$ch_info['filter']))){
					$post_channel_check = 1;
				}else{
					$post_channel_check = 0;
				}
			}
			$this->item_id = 0;
		}
		$member = jsg_member_info(MEMBER_ID);
		$content_dstr = $this->Config['in_publish_notice_str'];
		$content_ostr = $this->Config['on_publish_notice_str'];
		$albums = jlogic('image')->getalbum();
		include(template('topic_publish_ajax'));
		exit;
	}

	
	function Report()
	{
		$tid = intval($this->Get['tid']);
		include(template('misc_report'));
	}


    
    function setSync(){
		$uid = max(0, (int) MEMBER_ID);
        if($uid < 1) {
			$this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
		}
        $setting = (int)$this->Get['setting'];
        if('sina' == $this->Get['type']){
            define('IS_IN_XWB_PLUGIN',      true);
            define('XWB_P_ROOT', ROOT_PATH . 'include/ext/xwb/');
            require_once XWB_P_ROOT.'sina.php';
            require_once XWB_P_ROOT.'lib/core.class.php';
            if(  XWB_S_UID < 1 || !XWB_plugin::pCfg('is_account_binding') ){
                XWB_plugin::showError('新浪微博绑定功能已经关闭！');
            }

            $tojishigou = XWB_plugin::V('G:tojishigou');
            $reply_tojishigou = XWB_plugin::V('G:reply_tojishigou');
            $profile = XWB_plugin::O('xwbUserProfile');
            $profile->set(array('bind_setting'=>(int)$setting,'synctopic_tojishigou'=>(int)$tojishigou,'syncreply_tojishigou'=>(int)$reply_tojishigou,));
            
            $r=jclass('misc')->update_account_bind_info(XWB_S_UID, '', '', 1);
        }elseif('qq' == $this->Get['type']){

            $this->DatabaseHandler->Query("update ".TABLE_PREFIX."qqwb_bind_info set `synctoqq`='$setting' where `uid`='$uid'");

            $r=jclass('misc')->update_account_bind_info($uid, '', '', 1);
        }elseif(('renren' == $this->Get['type']) && !$setting){

			$r=$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."renren_bind_info where `uid`='$uid'");

		}elseif ('kaixin' == $this->Get['type']) {
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."kaixin_bind_info where `uid`='$uid'");
		}

        json_result('ok',$setting?0:1);
    }
    }

?>
