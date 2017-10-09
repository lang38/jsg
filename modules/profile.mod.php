<?php
/**
 * 文件名：profile.mod.php
 * @version $Id: profile.mod.php 5543 2014-02-12 08:01:06Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个人设置模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;

	var $ID = '';

	var $TopicLogic;


	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->TopicLogic = jlogic('topic');

		$this->ID = jget('id', 'int');

		$this->Member = $this->_member();

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'do_notice':
				$this->DoNotice();
				break;
			case 'user_share':
				$this->DoUserShare();
				break;
			case 'invite_by_email':
				$this->InviteByEmail();
				break;
			case 'qqrobot':
				$this->Messager(null,"index.php?mod=tools&code=imjiqiren");
				break;

			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	function Main()
	{
				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$this->Messager($this->MemberHandler->GetError(),null);
		}

		$act_list = array();
		if ($this->Config['company_enable']){
			$act_list['company'] = '同单位';
		}
		if(!$this->Config['profile_search_close']) {
			$act_list['search'] = '同城用户';
		}
		if(!$this->Config['profile_maybe_friend_close']) {
			$act_list['maybe_friend'] = '同兴趣';
		}
		if(!$this->Config['profile_usertag_close']) {
			$act_list['usertag'] = '同类人';
		}
		if(!$this->Config['profile_role_close']) {
			$act_list['role'] = '同分组';
		}


				if(false == (jconf::get('invite', 'link_display_none'))) {
			$act_list['invite'] = '邀请好友';
		}

				$act = $this->Code;


		$member = $this->Member;

				if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}

		$member_nickname = $member['nickname'];

		
		if ('invite' == $act) {
			$sql = "delete from `".TABLE_PREFIX."invite` where `fuid`<'1' and `dateline`>'0' and `dateline`<'".(time() - 86400 * 7)."'";
			$this->DatabaseHandler->Query($sql);

			$sql = "select count(*) as my_invite_count from `".TABLE_PREFIX."invite` where `uid`='{$member['uid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$row = $query->GetRow();
			$my_invite_count = $row['my_invite_count'];

			$can_invite_count = max(0,$this->Config['invite_count_max']-$my_invite_count);

			if ($my_invite_count > 0) {

				$per_page_num = 5;

				$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

				$_config = array(
					'return' => 'array',
				);

				$page_arr = page($my_invite_count,$per_page_num,$query_link,$_config);

								$sql = "select i.*,m.province,m.city,m.topic_count,m.fans_count,m.nickname as fusername from `".TABLE_PREFIX."invite` i
						left join `".TABLE_PREFIX."members` m on m.uid = i.fuid
					 	where i.`uid`='{$member['uid']}' order by i.`id` desc {$page_arr['limit']}";
				$query = $this->DatabaseHandler->Query($sql);
				$invite_list = array();
				while (false != ($row = $query->GetRow())) {
					$row['from_area'] = $row['province'] ? ($row['province'].' '.$row['city']) : '无';
					$row['face'] = face_get($row['fuid']);

					$invite_list[] = $row;						}

								$invite_list = buddy_follow_html($invite_list, 'fuid');
			}

						$MEMBER_INVITE_CODE = '';
			if((!$this->Config['invite_count_max'] || $this->Config['invite_count_max'] > $member['invite_count'])) {
				$MEMBER_INVITE_CODE = $member['invitecode'];
			}
			if (!$MEMBER_INVITE_CODE) {
				$MEMBER_INVITE_CODE = random(16);
				$sql = "update `".TABLE_PREFIX."members` set `invitecode`='{$MEMBER_INVITE_CODE}' where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
			}
			$inviteURL = "index.php?mod=member&code=".urlencode(MEMBER_ID."_".$MEMBER_INVITE_CODE);

			$inviteURL = get_invite_url($inviteURL,$this->Config['site_url']);

						$invite = jconf::get('invite');
			$invite_msg = empty($invite) ? '' : jstripslashes($invite['invite_msg']);
			if (!empty($invite_msg)) {
				$replaces = array(
    				'nickname' => $member['nickname'],
    				'inviteurl' => $inviteURL,
    				'invite_num' => $this->Config['invite_limit'],
    				'site_name' => $this->Config['site_name'],
				);
				foreach ($replaces as $key => $val) {
					$invite_msg = str_replace("#".$key."#", $val, $invite_msg);
				}
			}

		}

		
		elseif ('maybe_friend' == $act) {
			$my_favorite_tags = jlogic('tag_favorite')->my_favorite_tags(MEMBER_ID, 20);

			if($my_favorite_tags) {
				$uids = jlogic('tag_favorite')->favorite_uids($my_favorite_tags, 30);

				if($uids) {
					$p = array(
						'uid' => MEMBER_ID,
						'touid' => $uids,
						'result_count' => count($uids),
					);
					$buddyids = get_buddyids($p);

					$sql = "select `uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`nickname` from `".TABLE_PREFIX."members` where `uid` in('".implode("','",$uids)."')";
					$query = $this->DatabaseHandler->Query($sql);
					$member_list = array();
					while (false != ($row = $query->GetRow())) {
						$buddy_status = isset($buddyids[$row['uid']]);
						if(!$buddy_status && MEMBER_ID!=$row['uid']) {
							$row['follow_html'] = follow_html($row['uid'],$buddy_status);

							$row = jsg_member_make($row);
							$member_list[$row['uid']] = $row;
							$tag_favorite_uids[$row['uid']] = $row['uid'];
						}
					}
				}
			}

						$user_favorite = array();
			if($tag_favorite_uids) {
				$user_favorite = jlogic('tag_favorite')->my_favorite($tag_favorite_uids, 100);
			}
		}


		elseif('usertag' == $act) {

			$per_page_num = 10;
			$query_link = 'index.php?mod=profile&code=usertag';
			$order = " order by `fans_count` desc ";

						$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '".MEMBER_ID."'";
			$query = $this->DatabaseHandler->Query($sql);
			$mytag = array();
			$user_tagid = array();
			while(false != ($row = $query->GetRow()))
			{
				$mytag[] = $row;
				$user_tagid[$row['tag_id']] = $row['tag_id'];
			}

			if($user_tagid)
			{
								$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` != '".MEMBER_ID."' and `tag_id` in (".jimplode($user_tagid).") ";
				$query = $this->DatabaseHandler->Query($sql);

				$member_uids = array();
				while(false != ($row = $query->GetRow()))
				{
					$member_uids[$row['uid']] = $row['uid'];
				}

				$where = $where_list = " where `uid` in (".jimplode($member_uids).")";
			}


						if($member_uids)
			{
				$member_list = array();

				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where}";
				$total_record = DB::result_first($sql);
				if($total_record > 0) {
					$_config = array (
							'return' => 'array',
					);

					$page_arr = page($total_record,$per_page_num,$query_link,$_config);

					$member_list = $this->TopicLogic->GetMember("{$where} {$order} {$page_arr['limit']}","`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate`");

					$member_list = buddy_follow_html($member_list);
				}

								$sql = "select * from `".TABLE_PREFIX."user_tag_fields` {$where}";
				$query = $this->DatabaseHandler->Query($sql);
				$member_tag = array();

				while(false != ($row = $query->GetRow()))
				{
					$member_tag[] = $row;
				}

			}

						$mytag = $this->_MyUserTag(MEMBER_ID);
		}

		
				else {

			$per_page_num = 10;
			$query_link = 'index.php?mod=profile&code='.$act;
			$where_list = array();
			if ('search' == $act){
			$province_name = $member['province'];
			$city_name = $member['city'];
			$area_name = $member['area'];
			$street_name = $member['street'];

			$province = $this->Get['province'];
			$city = $this->Get['city'];
			$area = $this->Get['area'];
			$street = $this->Get['street'];

			if($province){
				$province_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$province'");

				if($city){
					$city_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$city'");

					if($area){
						$area_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$area'");

						if($street){
							$street_name = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '$street'");
						}else{
							$street_name = '';
						}
					}else{
						$area_name = '';
						$street_name = '';
					}
				}else{
					$city_name = '';
					$area_name = '';
					$street_name = '';
				}
			}

			if(empty($where_list))
			{
				if($province_name){
					$where_list['province'] = "`province`='".addslashes("$province_name")."'";
					$query_link .= "&province=" . $province;

					if($city_name){
						$where_list['city'] = "`city`='".addslashes("$city_name")."'";
						$query_link .= "&city=" . $city;

						if($area_name){
							$where_list['area'] = "`area`='".addslashes("$area_name")."'";
							$query_link .= "&area=" . $area;

							if($street_name){
								$where_list['street'] = "`street`='".addslashes("$street_name")."'";
								$query_link .= "&street=" . $street;
							}
						}
					}
				}
			}


			
			$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province_arr[$rsdb['id']]['value']  = $rsdb['id'];
				$province_arr[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
					$province_id = $rsdb['id'];
				}
			}
			$province_id = $province ? $province:$province_id;
			$province_list = jform()->Select("province",$province_arr,$province_id,"onchange=\"changeProvince();\"");

			$hid_area = '';
			$hid_city = '';
			$hid_street = '';

			if(!$province && $province_id){
				if($member['city']){
					$hid_city = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$member[city]' and upid = '$province_id'");				}

				if($hid_city){
					if($member['area']){
						$hid_area = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$member[area]' and upid = '$hid_city'");					}

					if($hid_area){
						if($member['street']){
							$hid_street = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$member[street]' and upid = '$hid_area'");						}
					}
				}
			}

			$hid_city = $city ? $city : $hid_city;
			$hid_area = $area ? $area : $hid_area;
			$hid_street = $street ? $street : $hid_street;

			}elseif('company' == $act && $this->Config['company_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				global $_J;

				$companyid = jget('companyid', 'int');
				$departmentid = jget('departmentid', 'int');
				$jobid = jget('jobid', 'int');
				if($companyid < 1 && $departmentid < 1 && $jobid < 1) {
					$companyid = (int) $_J['member']['companyid'];
					$departmentid = (int) $_J['member']['departmentid'];
					$jobid = (int) $_J['member']['jobid'];
				}

				$company_list = jlogic('cp')->get_cp_html($companyid);
				if ($this->Config['department_enable']){
					$department_list = jlogic('cp')->get_cp_html($departmentid,'department',$companyid);
				}
				$job_list = jlogic('job')->get_job_select($jobid);
				if($companyid > 0) {
					$where_list['company'] = "`companyid`='".$companyid."'";
					$query_link .= '&companyid=' . $companyid;
				}
				if($departmentid > 0) {
					$where_list['department'] = "`departmentid`='".$departmentid."'";
					$query_link .= '&departmentid=' . $departmentid;
				}
				if($jobid > 0) {
					$where_list['job'] = "`jobid`='".$jobid."'";
					$query_link .= '&jobid=' . $jobid;
				}

			}elseif('role' == $act){
				global $_J;
				$roleid = $this->Get['roleid'] ? (int)$this->Get['roleid'] : ($_J['role_id'] ? $_J['role_id'] : 0);
				$roles = jlogic('channel')->get_user_role();
				$role_list = jform()->Select("roleid",$roles,$roleid,"");
				if($roleid) {
					$where_list['role'] = "`role_id`='".$roleid."'";
					$query_link .= "&roleid=" . $roleid;
				}
			}

			$member_list = array();
			if($where_list) {

				$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';

				$order = " order by `uid` desc ";
				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` {$where} ";
				
				$total_record = DB::result_first($sql);

				if($total_record > 0) {
					$_config = array (
						'return' => 'array',
					);
					$page_arr = page($total_record,$per_page_num,$query_link,$_config);

					$uids = array();
					$member_list = $this->TopicLogic->GetMember("{$where} {$order} {$page_arr['limit']}","`uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`aboutme`");
					
					foreach ($member_list as $_m) {
						$uids[$_m['uid']] = $_m['uid'];
					}


					if($uids && MEMBER_ID>0) {
						$member_list = buddy_follow_html($member_list);

						$province = isset($_GET['province']) ? $province : $member['province'];
						$city = isset($_GET['city']) ? $city : $member['city'];

						
						$sql ="select * from (select * from `".TABLE_PREFIX."topic` where `uid` in (".jimplode($uids).") and `type` != 'reply' order by `dateline` desc) a group by `uid` ";
						$query = $this->DatabaseHandler->Query($sql);
						$tids = array();
						while (false != ($row = $query->GetRow())) {
							$tids[$row['tid']] = $row['tid'];
						}
						$topic_list = $this->TopicLogic->Get($tids);
					}
				}
			}
			$gender_radio = jform()->Radio('gender',array(0=>array('name'=>'不限','value'=>0),1=>array('name'=>'男','value'=>1,),2=>array('name'=>'女','value'=>2,),),$gender);

		}

		$this->Title = $act_list[$act];
		include(template('social/profile_main'));
	}

	function InviteByEmail()
	{
		$inviteEmail = trim($this->Post['inviteEmail'] ? $this->Post['inviteEmail'] : $this->Get['inviteEmail']);


		if (!$inviteEmail) {
			$this->Messager("请填入Email地址",-1);
		}

		$email_list = explode(';',$inviteEmail);
		$send_success = $send_failed = 0;
		foreach($email_list as $email) {
			$email = trim($email);

			if (preg_match("~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i",$email)) {

				
				$_sql = "select * from `".TABLE_PREFIX."invite` where `femail`='{$email}' and `uid`='".MEMBER_ID."'";
				$_query = $this->DatabaseHandler->Query($_sql);
				$_row = $_query->GetRow();

				if ($_row) {
										if ($_row['fuid'] > 0 || $_row['dateline'] + 600 > time()) {
						continue ;
					}

					$_row['dateline'] = time();
					$_sql = "update `".TABLE_PREFIX."invite` set `dateline`='{$_row['dateline']}' where `id`='{$_row['id']}'";
					$this->DatabaseHandler->Query($_sql);
				} else {
					$_row['uid'] = MEMBER_ID;
					$_row['code'] = substr(md5(MEMBER_ID . random(16) . time()),0,16);
					$_row['dateline'] = time();
					$_row['femail'] = $email;
					$_sql = "insert into `".TABLE_PREFIX."invite` (`uid`,`code`,`dateline`,`femail`) values ('{$_row['uid']}','{$_row['code']}','{$_row['dateline']}','{$_row['femail']}')";
					$this->DatabaseHandler->Query($_sql);
					$_row['id'] = $this->DatabaseHandler->Insert_ID();
				}
				if($_row['id'] < 1 || !$_row['code'] || !$_row['femail']) {
					continue ;
				}
				

				$invite_id = $_row['id'];
				$invite_hash = md5($_row['id'].$_row['code'].$_row['dateline'].$_row['femail']);
				$invite_email = $_row['femail'];
				$invite_url = "{$this->Config['site_url']}/index.php?mod=member&code=".urlencode("{$invite_id}#{$invite_hash}")."&email=" . urlencode($invite_email);

				$mail_from_username = $this->Member['nickname'];
				$mail_from_email = 'no-reply@jishigou.net';
				$mail_from_email = $this->Config['site_admin_email'];

				$mail_to = $email;
				$mail_subject = "{$this->Config[site_name]}的邀请：来自好友{$this->Member['nickname']}";
				$mail_content = "{$this->Member['nickname']} 邀请你加入 {$this->Config[site_name]}!

请点击以下链接加入，随时随地关注、分享身边的新鲜事儿：
				{$invite_url}
（注意：此邀请链接仅能使用一次）

如果以上链接无法访问，请复制链接并粘贴到浏览器地址栏访问。


如果您已经拥有{$this->Config[site_name]}帐号，请访问我的个人页面：
				{$this->Config['site_url']}/index.php?mod={$this->Member['username']}

-------------------------------------------------------------------------
这是一封系统自动发送的邮件，请不要直接回复。
" . my_date_format(time(),'Y-m-d');

				$send_result = send_mail($mail_to,$mail_subject,$mail_content,$mail_from_username,$mail_from_email,array(),3,false);

				if ($send_result) {
					$send_success++;
				} else {
					$send_failed++;
				}

			}

		}
		$send_result = $send_success ? $send_success."份邀请发送成功；" :"";
		$send_result .= $send_failed ? $send_failed."份邀请发送失败。" :"";
		$this->Messager("{$send_result}",'',5);

	}

		function DoNotice()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}
		$notice_at			= $this->Post['notice_at'];
		$notice_pm			= $this->Post['notice_pm'] ;
		$notice_reply		= $this->Post['notice_reply'];
		$user_notice_time		= $this->Post['user_notice_time'];

		$sql = "update `".TABLE_PREFIX."members` set `notice_at`='{$notice_at}',`notice_pm`='{$notice_pm}',`notice_reply`='{$notice_reply}',`user_notice_time`='{$user_notice_time}' where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);

		$this->Messager(null,"index.php?mod=settings&code=notice");

	}

	function _member()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}

		$member = $this->TopicLogic->GetMember(MEMBER_ID);

		return $member;
	}


		function _MyUserTag($uid)
	{
		$sql = "select * from `".TABLE_PREFIX."user_tag_fields` where `uid` = '{$uid}'";
		$query = $this->DatabaseHandler->Query($sql);
		$mytag = array();
		$mytag_ids = array();
		while(false != ($row = $query->GetRow()))
		{
			$mytag[] = $row;
			$mytag_ids[$row['tag_id']] = $row['tag_id'];
		}

		return $mytag;
	}

}


?>
