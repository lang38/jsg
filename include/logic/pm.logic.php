<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1096528299 30274 $
 */



if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class PmLogic
{

	var $DatabaseHandler;

		
	function PmLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");

	}

	
	function doPmSend($post){
		$time = time();
		$this->_process_content($post['message']);
		if(trim($post['message']) == ''){
			return '请编辑要群发的私信内容';
		}
		$data=array(
			"msgfrom"		=>MEMBER_NAME,
			"msgnickname"		=>MEMBER_NICKNAME,
			"msgfromid" => MEMBER_ID,  								"msgto" => '',					"tonickname" => '',				"msgtoid"   => 0,								"message"   => trim($post['message']),
			"new"=>'1',
			"dateline"=>$time,
			"plid"=>0,
		);
		$lastmessage = addslashes(serialize($data));
		jtable('pms')->insert($data);

		$count = DB::result_first("select count(*) from ".TABLE_PREFIX."pms_list where plid = 0");
		if($count){
			DB::query("update ".TABLE_PREFIX."pms_list set uid = ".MEMBER_ID.",pmnum = pmnum + 1,dateline = '$time' ,lastmessage = '$lastmessage' where plid = 0");
		}else{
			DB::query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values(0,'".MEMBER_ID."',1,'$time','$lastmessage')");
		}
		return '';
	}

	
	function delNotice($pmid){
				DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$time = time();
				$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where plid = 0 and folder = 'inbox' order by dateline desc limit 1");
		$pm_list = $query->GetRow();

		if($pm_list){
			$uid = $pmlist['msgfromid'];
			$lastmessage = addslashes(serialize($pm_list));
			DB::query("update ".TABLE_PREFIX."pms_list set uid = '$uid',pmnum = pmnum - 1 ,dateline = '$time' , lastmessage = '$lastmessage' where plid = 0");
		}else{
			DB::query("delete from ".TABLE_PREFIX."pms_list where plid = 0");
		}

	}

	
	function getPmList($folder='inbox',$page=array(),$uid=0){
		$return_arr = array();
		$page_arr = array();
		$uid = $uid ? $uid : MEMBER_ID;
		$pm_list=array();
		$read = $page['read'];
		$read && $where_sql = " and is_new = 1 ";

		if($folder == 'inbox'){
			if($page) {
								if($page['count']) {
					$total_record = (int) $page['count'];
				} else {
					$sql = "select count(*) from ".TABLE_PREFIX."pms_list where `uid` = '{$uid}' and pmnum > 0 $where_sql or plid = 0";
					$total_record = DB::result_first($sql);
				}
				if($page['return_count']) {
					return $total_record;
				}

			  					$page_arr = page($total_record,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			}

			$sql="select *
			      from ".TABLE_PREFIX."pms_list
				  where (uid = '$uid' and pmnum > 0 $where_sql
				  		 or
				  		 plid = 0 )
				  ORDER BY dateline DESC
				  {$page_arr['limit']} ";
			$query = $this->DatabaseHandler->Query($sql);

			while($row=$query->GetRow()){
				$rsdb = unserialize($row['lastmessage']);
				if(is_array($rsdb)){
					foreach ($rsdb as $key=>$value) {
						$row[$key] = stripslashes($value);
					}
				}

								if($row['plid'] == 0){
					$row['uid'] = $row['msgfromid'];
					$row['face'] = face_get($row['msgfromid']);
					$row['username'] = $row['msgfrom'];
					$row['nickname'] = $row['msgnickname'];

								}else if($row['msgfromid'] == $uid){
					$row['uid'] = $row['msgtoid'];
					$row['face'] = face_get($row['msgtoid']);
					$row['username'] = $row['msgto'];
					$row['nickname'] = $row['tonickname'];
								}else{
					$row['uid'] = $row['msgfromid'];
					$row['face'] = face_get($row['msgfromid']);
					$row['username'] = $row['msgfrom'];
					$row['nickname'] = $row['msgnickname'];
				}
				$row['num'] = $row['pmnum'];

				$row['message'] = nl2br($this->FaceToMessage($row['message']));
				if($row['imageids']){
					$row['image_list'] = jlogic('image')->image_list($row['imageids']);
				}
				if($row['attachids']){
					$row['attach_list'] = jlogic('attach')->attach_list($row['attachids']);
				}
				$row = $this->msgformat($row);
				$pm_list[$row['plid']."_".$row['uid']]=$row;
			}
		}else{
			if($page){
			  					$sql="SELECT count(*) FROM ".TABLE_PREFIX."pms WHERE msgfromid='$uid' AND folder='outbox'";
				$total_record = DB::result_first($sql);
				$page_arr = page($total_record,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			}

			$sql="SELECT * FROM ".TABLE_PREFIX."pms WHERE msgfromid='$uid' AND folder='outbox' ORDER BY dateline DESC $page_arr[limit]";
			$query = $this->DatabaseHandler->Query($sql);

			while($row=$query->GetRow()){
				$row['uid'] = $row['msgtoid'];
				$row['face'] = face_get($row['msgtoid']);
				$row['username'] = $row['msgto'];
				$row['nickname'] = $row['tonickname'];
				$row['message'] = nl2br($this->FaceToMessage($row['message']));
				if($row['imageids']){
					$row['image_list'] = jlogic('image')->image_list($row['imageids']);
				}
				if($row['attachids']){
					$row['attach_list'] = jlogic('attach')->attach_list($row['attachids']);
				}
				$row = $this->msgformat($row);
				$pm_list[$row['pmid']]=$row;
			}
		}

				$return_arr['pm_list'] = $pm_list;
				$return_arr['page_arr'] = $page_arr;

		return $return_arr;
	}

	
	function getHistory($uid = MEMBER_ID,$touid = MEMBER_ID,$page=array(),$limit=''){
		$return_arr = array();
		$page_arr = array();

		if($page){
			if($page['count']) {
				$count = (int) $page['count'];
			} else {
				$sql = "select count(*) from ".TABLE_PREFIX."pms
						where ((msgfromid = '$uid' AND msgtoid = '$touid' AND delstatus != 1)
							     OR
							     (msgfromid = '$touid' AND msgtoid = '$uid' AND delstatus != 2))
							    AND folder = 'inbox' ";
				$count = DB::result_first($sql);
			}
			if($page['return_count']) {
				return $count;
			}

						$page_arr = page($count,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			$limit = $page_arr['limit'];
		}

		$sql = "select p.*,m1.nickname as msgnickname,m2.nickname as tonickname from ".TABLE_PREFIX."pms p
			    left join `".TABLE_PREFIX."members` m1 on m1.uid = p.msgfromid
				left join `".TABLE_PREFIX."members` m2 on m2.uid = p.msgtoid
				where ((p.msgfromid = '$uid' AND p.msgtoid = '$touid' AND p.delstatus != 1)
					     OR
					     (p.msgfromid = '$touid' AND p.msgtoid = '$uid' AND p.delstatus != 2))
					    AND p.folder = 'inbox'
				order by p.dateline desc $limit";

		$query = $this->DatabaseHandler->Query($sql);
		$pm_list = array();
		while (false != ($row = $query->GetRow())){
						if($row['msgfromid'] == $uid){
				$row['uid'] = $row['msgtoid'];
				$row['username'] = $row['msgto'];
				$row['nickname'] = $row['tonickname'];
						}else{
				$row['uid'] = $row['msgfromid'];
				$row['username'] = $row['msgfrom'];
				$row['nickname'] = $row['msgnickname'];
			}
			$row['face'] = face_get($row['msgfromid']);
			$row['message'] = nl2br($this->FaceToMessage($row['message']));
			if($row['imageids']){
				$row['image_list'] = jlogic('image')->image_list($row['imageids']);
			}
			if($row['attachids']){
				$row['attach_list'] = jlogic('attach')->attach_list($row['attachids']);
			}
			$nickname = $row['nickname'];
			$row = $this->msgformat($row);
			$pm_list[$row['pmid']]=$row;
		}

		$return_arr['nickname'] = $nickname;
		$return_arr['pm_list'] = $pm_list;
		$return_arr['page_arr'] = $page_arr;

		return $return_arr;
	}

	
	function getNotice($page){
		$return_arr = array();
		$page_arr = array();

		if($page){
			$sql = "select count(*) from ".TABLE_PREFIX."pms
					where plid = 0";
			$count = DB::result_first($sql);

						$page_arr = page($count,$page['per_page_num'],$page['query_link'],array('return'=>'array',));
			$limit = $page_arr['limit'];
		}else{
			$limit = $page['limit'];
		}

		$sql = "select * from ".TABLE_PREFIX."pms
				where plid = 0
				order by dateline desc $limit ";
		$query = $this->DatabaseHandler->Query($sql);
		$pm_list = array();

		while (false != ($row = $query->GetRow())){
			$row['uid'] = $row['msgfromid'];
			$row['face'] = face_get($row['msgfromid']);
			$row['username'] = $row['msgfrom'];
			$row['nickname'] = $row['msgnickname'];
			$row['message'] = nl2br($row['message']);
			$nickname = $row['nickname'];
			$row = $this->msgformat($row);
			$pm_list[$row['pmid']]=$row;
		}

		$return_arr['nickname'] = $nickname;
		$return_arr['pm_list'] = $pm_list;
		$return_arr['page_arr'] = $page_arr;
		return $return_arr;
	}

	
	function pmSend($post,$suid=MEMBER_ID,$susername=MEMBER_NAME,$snickname=MEMBER_NICKNAME){
				if(jaccess('pm','send', $suid)==false) {
			return 6;
		}

		$to_user_list=array();
		$f_rets = filter($post['message']);
		if($f_rets)
		{
			if($f_rets['error'])
			{
				return $f_rets['msg'];
			}
		}

		$post['subject']=jhtmlspecialchars(trim($post['subject']));

		$p_to_user = $post['to_user'];
		if (empty($p_to_user)) {
			return 2;
		}

		$this->_process_content($post['message']);
		if($post['message']=='') {
			return 1;
		}

		$p_to_user = (array) $p_to_user;
		$nks = array();
		foreach($p_to_user as $tmps) {
			$tmps = (string) $tmps;
			if(false !== strpos($tmps, ',')) {
				$_tmps = explode(',', $tmps);
				foreach($_tmps as $_tmp) {
					$nk = addslashes($_tmp);
					$nks[$nk] = $nk;
				}
			} else {
				$nk = addslashes($tmps);
				$nks[$nk] = $nk;
			}
		}

				$sql="select `uid`,`username`,`nickname`,`notice_pm`,`email`,`email_checked`,`newpm`,`at_new`,`event_new`,`fans_new`,`vote_new`,`qun_new`,`dig_new`,`channel_new`,`company_new`,`comment_new`,`user_notice_time`,`lastactivity`
		FROM
			".TABLE_PREFIX.'members'."
		WHERE `nickname` IN (" . jimplode($nks) . ") ORDER BY `uid` limit 100";
		$query = $this->DatabaseHandler->Query($sql);
		$to_uids = array();
		while($row=$query->GetRow())
		{
						if($suid == MEMBER_ID){
				if(is_blacklist($suid,$row['uid'])){
					return '你在'.$row['nickname'].'的黑名单中，不被允许发私信';
				}
			}
						$rets = jsg_role_check_allow('sendpm', $row['uid'], $suid);
			if($rets && $rets['error']) {
				return $rets['error'];
			} else {
				$to_user_list[$row['uid']]=$row;
				$to_uids[$row['uid']] = $row['uid'];
			}
		}
				ios_push_msg($to_uids,'你有新消息:1条私信');

						if($to_user_list==false)
		{
			return 3;
		}
				
				$post['imageids'] = DB::filter_in_num($post['imageids']);

				$post['attachids'] = DB::filter_in_num($post['attachids']);
		
		$time = TIMESTAMP;

		foreach($to_user_list as $to_user_id => $to_user_name)
		{
			$data=array(
			"msgfrom"	 =>$susername,
			"msgnickname"=>$snickname,
			"msgfromid"  =>$suid,  								"msgto" => $to_user_name['username'],					"tonickname" => $to_user_name['nickname'],				"msgtoid"   => $to_user_id,								'imageids' => $post['imageids'],
			'attachids' => $post['attachids'],
			"subject"   => $post['subject'],
			"message"   => $post['message'],
			"new"=>'1',
			"dateline"=>$time,
			);

			if($post["save_to_outbox"])
			{
				$data['folder']="outbox";
				$msg="消息已经保存草稿箱";
			}
						$uids = '';
			if($suid > $to_user_id){
				$uids = $to_user_id.",".$suid;
			}else{
				$uids = $suid.",".$to_user_id;
			}

			$plid = 0;
									if(!$msg){
								$lastmessage = addslashes(serialize($data));
				$plid = DB::result_first("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");

				if($plid == 0){
										DB::query("insert into ".TABLE_PREFIX."pms_index (uids) values('$uids')");
					$plid = $this->DatabaseHandler->Insert_ID();
					if(0 != $plid){
												DB::query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid','".$suid."',1,'$time','$lastmessage')");
						if($suid != $to_user_id){
							DB::query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage,is_new) values('$plid','$to_user_id',1,'$time','$lastmessage',1)");
						}
					}
				}else{
										DB::query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage',is_new = 1 where plid = '$plid' and uid = '$to_user_id' ");
					if($suid != $to_user_id){
						DB::query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage',is_new = 0 where plid = '$plid'  and uid = '$suid' ");
					}
				}
			}

			$data['plid'] = $plid;

			DB::insert('pms',$data);
		}

		#标记音乐和附件，使清缓存的时候不会把附件删除
		if($data['imageids']){
			DB::query("update `".TABLE_PREFIX."topic_image` set `tid` = -1 where `id` in ({$data['imageids']})");
		}
		if($data['attachids']){
			DB::query("update `".TABLE_PREFIX."topic_attach` set `tid` = -1 where `id` in ({$data['attachids']})");
		}
		$num=$post["save_to_outbox"]?0:1;
		if($num > 0){
						$_tmps=array_keys($to_user_list);
			$to_user_id_list = array();
			foreach($_tmps as $_tmp) {
				$_tmp = (int) $_tmp;
				if($_tmp > 0) {
					$to_user_id_list[$_tmp] = $_tmp;
				}
			}
			$this->UpdateNewMsgCount($num,$to_user_id_list);

			

			foreach ($to_user_list as $user_notice)
			{
				if ($GLOBALS['_J']['config']['sendmailday'] > 0)
				{
					jtable('mailqueue')->add($user_notice, 'notice_pm');
				}

				if($GLOBALS['_J']['config']['imjiqiren_enable'] && imjiqiren_init())
				{
					imjiqiren_send_message($user_notice,'m',$GLOBALS['_J']['config']);
				}

				if($GLOBALS['_J']['config']['sms_enable'] && sms_init())
				{
					sms_send_message($user_notice,'m',$GLOBALS['_J']['config']);
				}
			}

			if($GLOBALS['_J']['config']['extcredits_enable'] && $suid > 0)
			{
				
				update_credits_by_action('pm',$suid,count($to_user_list));
			}
		}

		
		if(!$post['is_pm_to_admin_notice']) {
			$this->to_admin_notice($to_uids);
		}

		return 0;
	}

	
	public function to_admin_notice($to_uids) {
		if(is_array($to_uids) && count($to_uids)) {
			$pm_to_admin = jconf::get('pm_to_admin');
			$to_msgs = array();
			if($pm_to_admin && $pm_to_admin['list']) {
				foreach($pm_to_admin['list'] as $row) {
					if($row['enable'] && $row['notice_to'] && $row['notice_msg']) {
						foreach($to_uids as $uid) {
							$uid = (int) $uid;
							if(in_array($uid, $row['to_uids'])) {
								$to_msgs[$row['notice_to']] = $row;
							}
						}
					}
				}
			}
			if($to_msgs) {
				foreach($to_msgs as $to=>$row) {
					$p = array(
						'to_user' => $to,
						'message' => $row['notice_msg'],
						'is_pm_to_admin_notice' => 1,
					);

					if($row['send_from'] && $row['send_from_uid'] > 0) {
						$this->pmSend($p, $row['send_from_uid'], $row['send_from_username'], $row['send_from']);
					} else {
						$this->pmSend($p);
					}
				}
			}
		}
	}

	

	function _process_content(&$content) {
		if('POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['message'])) {
			$content = jpost('message', 'txt');
		}
		$arr_keys = $arr_values = array();
		if(preg_match_all('~\<a.*?\<\/a>~i',$content,$match)){
			foreach($match[0] as $k => $v){
				$arr['@this_is_replace_ward_'.$k.'@'] = $v;
			}
			$arr_keys = array_keys($arr);
			$arr_values = array_values($arr);
			$content = str_replace($arr_values,$arr_keys,$content);
		}
		if (preg_match_all('~(?:https?\:\/\/|www\.)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&\;_\~\`\:\+\#\.\@\[\]]*(?:[^\<\>\'\"\n\r\t\s\x7f-\xff])*)?~i',
		$content, $match)) {
			foreach ($match[0] as $url) {
				$replce_url = (false !== strpos($url,'http:/'.'/')) ? $url : 'http:/'.'/'.$url;
				$content = str_replace($url,"<a href='$replce_url' target='_blank'>$url</a>",$content);
			}
		}
		if($arr_keys && $arr_values) {
			$content = str_replace($arr_keys,$arr_values,$content);
		}
				$content = addslashes($content);
	}

	
	function delUserMsg($uid){
		if($uid < 1){
			return '请选择要删除的聊天记录';
		}
		if($uid > MEMBER_ID){
			$uids = MEMBER_ID.",".$uid;
		}else{
			$uids = $uid.",".MEMBER_ID;
		}

		$plid = DB::result_first("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");
		if($plid < 1){
			return '数据已损坏';
		}

		$pm_list = array();
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where plid = '$plid' and folder = 'inbox'");
		while (false != ($row = $query->GetRow())){
			$pm_list[$row['pmid']] = $row;
		}

		foreach ($pm_list as $key=>$value) {
			if($value['msgfromid'] == $value['msgtoid']){
				DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
			}else if($value['msgfromid'] == MEMBER_ID){
			    if($value['delstatus'] == 2){
					DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
				}else if($value['delstatus'] == 0){
					DB::query("update ".TABLE_PREFIX."pms set delstatus = 1 where pmid = '$key'");
				}
			}else if($value['msgtoid'] == MEMBER_ID){
				if($value['delstatus'] == 1){
					DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$key'");
				}else if($value['delstatus'] == 0){
					DB::query("update ".TABLE_PREFIX."pms set delstatus = 2 where pmid = '$key'");
				}
			}
		}

		DB::query("update ".TABLE_PREFIX."pms_list set pmnum = 0,dateline = 0,lastmessage = '' where plid='$plid' and uid = ".MEMBER_ID);
		return '';
	}

	
	function delMsg($pmid){

		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$pm_list = $query->GetRow();

		$uid = $pm_list['msgfromid'] == MEMBER_ID ? $pm_list['msgfromid'] : $pm_list['msgtoid'];
		$otheruid = $pm_list['msgfromid'] == MEMBER_ID ? $pm_list['msgtoid'] : $pm_list['msgfromid'];
		$plid = $pm_list['plid'];

		if(empty($pm_list)){
			return '私信内容不存在或已删除';
		}
		if($pm_list['msgfromid'] == $pm_list['msgtoid']){
			DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		}else if($pm_list['msgfromid'] == MEMBER_ID){
			if($pm_list['delstatus'] == 2){
				DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
			}else if($pm_list['delstatus'] == 0){
				DB::query("update ".TABLE_PREFIX."pms set delstatus = 1 where pmid = '$pmid'");
			}
		}else if($pm_list['msgtoid'] == MEMBER_ID){
			if($pm_list['delstatus'] == 1){
				DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
			}else if($pm_list['delstatus'] == 0){
				DB::query("update ".TABLE_PREFIX."pms set delstatus = 2 where pmid = '$pmid'");
			}
		}

				$this->setNewList($uid,$otheruid,$plid);

		return '';
	}

	
	function setNewList($uid,$otheruid,$plid){
		$sql = "select * from ".TABLE_PREFIX."pms
				where ((msgfromid = '$uid' AND msgtoid = '$otheruid' AND delstatus != 1)
					     OR
					     (msgfromid = '$otheruid' AND msgtoid = '$uid' AND delstatus != 2))
					    AND folder = 'inbox'
					    order by dateline desc
					    limit 1 ";
		$query = $this->DatabaseHandler->Query($sql);
		$pm = $query->GetRow();
		if($pm){
			$lastmessage = addslashes(serialize($pm));
			$time = time();
			DB::query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum - 1,dateline = '$time',lastmessage = '$lastmessage' where plid='$plid' and uid = '$uid'");
		}else{
			DB::query("update ".TABLE_PREFIX."pms_list set pmnum = 0,dateline = 0,lastmessage = '' where plid='$plid' and uid = '$uid'");
		}
	}

	
	function pmSendAgain($post){
		$message = trim($post['message']);
		$time = time();

		if($message=='')
		{
			return 1;
		}

		$pmid = $post['pmid'];


		$pm = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$pm_list = $pm->GetRow();
		$pm_list['message'] = $message;

		$touid = $pm_list['msgtoid'];

		$uids = '';
		if($pm_list['msgtoid'] > $pm_list['msgfromid']){
			$uids = $pm_list['msgfromid'].",".$pm_list['msgtoid'];
		}else{
			$uids = $pm_list['msgtoid'].",".$pm_list['msgfromid'];
		}

		if($touid < 1){
			return 5;
		}
		$to_user_list = array();

				$sql="select uid,username,nickname,notice_pm,email,newpm
		FROM
			".TABLE_PREFIX.'members'."
		WHERE
			uid = '$touid'";
		$query = $this->DatabaseHandler->Query($sql);

		while($row=$query->GetRow())
		{
			$to_user_list[$row['uid']]=$row;
		}

		if($to_user_list==false)
		{
			return 3;
		}

		$plid = DB::result_first("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");

		if($plid == 0){
						DB::query("insert into ".TABLE_PREFIX."pms_index (uids) values('$uids')");
			$plid = mysql_insert_id();
			$pm_list['plid'] = $plid;
			$lastmessage = addslashes(serialize($pm_list));
						DB::query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid',".MEMBER_ID.",1,'$time','$lastmessage')");
			if($pm_list['msgtoid'] != $pm_list['msgfromid']){
				DB::query("insert into ".TABLE_PREFIX."pms_list (plid,uid,pmnum,dateline,lastmessage) values('$plid','$touid',1,'$time','$lastmessage')");
			}
		}else{
			$lastmessage = addslashes(serialize($pm_list));
						DB::query("update ".TABLE_PREFIX."pms_list set pmnum = pmnum + 1,dateline = '$time',lastmessage = '$lastmessage' where plid = '$plid'");
		}

				DB::query("update ".TABLE_PREFIX."pms set folder = 'inbox' ,message = '$message' ,dateline = '$time',plid = '$plid' where pmid = '$pmid'");

				$num = 1;
		$_tmps=array_keys($to_user_list);
		$to_user_id_list = array();
		foreach($_tmps as $_tmp) {
			$_tmp = (int) $_tmp;
			if($_tmp > 0) {
				$to_user_id_list[$_tmp] = $_tmp;
			}
		}
		$this->UpdateNewMsgCount($num,$to_user_id_list);

		

		foreach ($to_user_list as $user_notice)
		{
			if ($GLOBALS['_J']['config']['sendmailday'] > 0)
			{
				jtable('mailqueue')->add($user_notice, 'notice_pm');
			}

			if($GLOBALS['_J']['config']['imjiqiren_enable'] && imjiqiren_init())
			{
				imjiqiren_send_message($user_notice,'m',$GLOBALS['_J']['config']);
			}

			if($GLOBALS['_J']['config']['sms_enable'] && sms_init())
			{
				sms_send_message($user_notice,'m',$GLOBALS['_J']['config']);
			}
		}

		if($GLOBALS['_J']['config']['extcredits_enable'] && MEMBER_ID > 0)
		{
			
			update_credits_by_action('pm',MEMBER_ID,count($to_user_list));
		}
		return 0;
	}

		function UpdateNewMsgCount($num, $uids='') {
		if('' === $uids) {
			$uids = MEMBER_ID;
		}
		if(empty($uids)) {
			return false;
		}

		$num = (int) $num;
		if(empty($num)) {
			return false;
		}

		$uids = (array) $uids;
		$sql="
		UPDATE
			".TABLE_PREFIX.'members'."
		SET
			`newpm`=`newpm` + $num
		WHERE uid in (" . jimplode($uids) . ")";
		DB::query($sql);

		if($num < 0) {
			DB::query("update ".TABLE_PREFIX."members set `newpm`=0 where uid in (" . jimplode($uids) . ") and `newpm`<0");
		}

		return true;
	}

	function setRead($uid){
		if($uid < 1){
			return '请选择你要设置的私信';
		}
		if($uid > MEMBER_ID){
			$uids = MEMBER_ID.",".$uid;
		}else{
			$uids = $uid.",".MEMBER_ID;
		}
		$plid = DB::result_first("select plid from ".TABLE_PREFIX."pms_index where uids = '$uids'");
		if($plid < 1){
			return '数据已损坏';
		}
		DB::query("update ".TABLE_PREFIX."pms_list set is_new = 0 where plid='$plid' and uid = ".MEMBER_ID);
		return '';
	}

	
	function FaceToMessage($msg){
		if (false !== strpos($msg, '[')) {
			if (false === strpos($msg, '#[')) {
				if (preg_match_all('~\[(.+?)\]~', $msg, $match)) {
					static $face_conf=null;
					if(!$face_conf) {
						$face_conf = jconf::get('face');
					}
					foreach ($match[0] as $k => $v) {
						if (false != ($img_src = $face_conf[$match[1][$k]])) {
														if (defined("IN_JISHIGOU_MOBILE")) {
								$img_src = 'mobile/'.$img_src;
							}
							$msg = str_replace($v, '<img src="' . $GLOBALS['_J']['config']['site_url'] .
                                '/' . $img_src . '" border="0"/>', $msg);
						}
					}
				}
			}
		}

		return $msg;
	}

    function delOutboxMsg($pmid){
        $uid = MEMBER_ID;
        if(!$uid){
            return FALSE;
        }
        $query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		$pm = $query->GetRow();
        if($pm['msgfromid'] == $uid || $pm['msgfromid'] == $uid){
        	DB::query("delete from ".TABLE_PREFIX."pms where pmid = '$pmid'");
        }  else {
            return FALSE;
        }
    }

		function msgformat($topic){
		$content_arr = explode("\n", $topic['message']);
        if(count($content_arr) > 1) {
            $content0 = $content_arr[0];
			if(jstrlen($content0)<=60 && strpos($content0,'[code]')===false) {
                $content_arr[0] = '<b>'.$content0.'</b>';
                $topic['message'] = implode("\n", $content_arr);
            }
        }
		if(false !== strpos($topic['message'], '[')){
			if(false !== strpos($topic['message'], '[/image]') && preg_match_all('~\[image\](.+?)\[\/image\]~', $topic['message'], $match)) {
				foreach ($match[0] as $k => $v) {
					if($image_url = $topic['image_list'][$match[1][$k]]['image_small']) {
						$imageHTML = '<ul class="imgList"><li><img src="'.$image_url.'"/></li></ul>';
						$topic['message'] = str_replace($v, $imageHTML, $topic['message']);
						unset($topic['image_list'][$match[1][$k]]);
					} else {
						$topic['message'] = str_replace($v, '', $topic['message']);
					}
				}
			}
			if(false !== strpos($topic['message'], '[/b]') && preg_match_all('/\[b\](.+?)\[\/b\]/is',$topic['message'],$match)){
				foreach ($match[0] as $k => $v) {
					$topic['message'] = str_replace($v, '<b>'.$match[1][$k].'</b>', $topic['message']);
				}
			}
			if(false !== strpos($topic['message'], '[/color]') && preg_match_all('/\[color=(.+?)\](.+?)\[\/color\]/is',$topic['message'],$match)){
				foreach ($match[0] as $k => $v) {
					$topic['message'] = str_replace($v, '<span style="color:'.$match[1][$k].';">'.$match[2][$k].'</span>', $topic['message']);
				}
			}
																					if(false !== strpos($topic['message'], '[/quote]')){
				$topic['message'] = str_replace(array("[quote]", "[/quote]"), array('<div class="quote">','</div>'), $topic['message']);
			}
			if(false !== strpos($topic['message'], '[/code]') && preg_match_all('/\[code\](.+?)\[\/code\]/is',$topic['message'],$match)){
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
					$topic['message'] = str_replace($v, $html, $topic['message']);
				}
			}
		}
		return $topic;
	}
}
?>