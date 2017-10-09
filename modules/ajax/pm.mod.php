<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename pm.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 1362681282 7878 $
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

		$this->ID = (int) ($this->Post['pmid'] ? $this->Post['pmid'] : $this->Get['pmid']);


		$this->Execute();
	}

	
	function Execute()
	{
		switch($this->Code)
		{
			case 'send':
				$this->Send();
				break;

			case 'do_add':
				$this->DoAdd();
				break;
			case 'pm_follow_user':
				$this->Pm_Follow_User();
				break;

			case 'pm_group_list':
				$this->PmGroupList();
				break;

							case 'msglist':
				$this->msgList();
				break;
			case 'delmsg':
				$this->delMsg();
				break;
			case 'setread':
				$this->setRead();
				break;
			case 'delusermsg':
				$this->delUserMsg();
				break;
			case 'deloutboxmsg':
				$this->delOutboxMsg();
				break;
			case 'sendagain':
				$this->sendAgain();
				break;
			default:
				$this->Main();
				break;
		}

		exit;
	}

	function Main()
	{
		response_text("正在建设中……");
	}

	
	function msgList(){
		$uid = MEMBER_ID;
		$touid = (int) $this->Get['uid'];

		load::logic('pm');
		$PmLogic = new PmLogic();

		$page = array();
		$page['per_page_num'] = 20;
		$page['query_link'] = "index.php?mod=pm&code=history&uid=$touid";

		$return_arr = $PmLogic->getHistory($uid,$touid,$page);
		extract($return_arr);
		include template('msg_list_ajax');
	}

	
	function sendAgain(){
		$pmid = (int) $this->get['pmid'];
		if($pmid < 1){
			return '私信不存在或已删除';
		}

		return ;
				$query = $this->DatabaseHandler->Query("select ");

		$to_user = $this->Post['to_user'];
		if (empty($to_user)) {
			$to_user = $this->Get['to_user'];
		}
		$touid = $this->Post['touid'];
		load::logic('pm');
		$PmLogic = new PmLogic();
				$return_arr = $PmLogic->getHistory(MEMBER_ID,$touid,array(),' limit 2 ');
		extract($return_arr);

		ob_start();
		include(template('pm/pm_send_ajax'));
		response_text(ob_get_clean());
	}

	
	function delUserMsg(){
		$uid = (int) $this->Get['uid'];
		load::logic('pm');
		$PmLogic = new PmLogic();

		return $PmLogic->delUserMsg($uid);
	}

	
	function delOutboxMsg(){
		$pmid = (int) $this->Get['pmid'];
		if($pmid < 1){
			return '请选择要删除的信息';
		}else{
			load::logic('pm');
            $PmLogic = new PmLogic();
            return $PmLogic->delOutboxMsg($pmid);
		}
	}

	
	function delMsg($pmid){
		$pmid = $pmid ? $pmid : (int) $this->Get['pmid'];

		load::logic('pm');
		$PmLogic = new PmLogic();

		return $PmLogic->delMsg($pmid);
	}

	function setRead(){
		$uid = (int) get_param('uid');
		load::logic('pm');
		$PmLogic = new PmLogic();

		return $PmLogic->setRead($uid);
	}

	
	function Send() {
		$pmid = (int) $this->Post['pmid'];
		if($pmid > 0){
			$message = DB::result_first("select message from ".TABLE_PREFIX."pms where pmid = '$pmid'");
		}
		$to_user = $this->Post['to_user'];
		if (empty($to_user)) {
			$to_user = $this->Get['to_user'];
		}
		$touid = $this->Post['touid'];
		load::logic('pm');
		$PmLogic = new PmLogic();

		$return_arr = $PmLogic->getHistory(MEMBER_ID,$touid,array(),' limit 2 ');
		extract($return_arr);

		ob_start();
		include(template('pm/pm_send_ajax'));
		response_text(ob_get_clean());
	}

		function DoAdd()
	{
		if(MEMBER_ID < 1)
		{
			exit("请先登录或者注册一个帐号");
		}

				if ($this->Config['seccode_enable']>1 && $this->Config['seccode_sms'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['YinXiangMa_challenge'],@$_POST['YXM_level'][0],@$_POST['YXM_input_result']);
			if($YinXiangMa_response != "true"){
				exit("验证码输入错误");
			}
		}

				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			exit($this->MemberHandler->GetError());
		}

		load::logic('pm');
		$PmLogic = new PmLogic();

		$pmid = $this->Post['pmid'];
		if($che = $this->Post['che']){
			$this->Post['to_user'] = implode(",",$che);
		}
		$this->Post['message'] = jpost('message', 'txt');
		if($pmid > 0){
			$return = $PmLogic->pmSendAgain($this->Post);
		}else{
			$return = $PmLogic->pmSend($this->Post);
		}
		switch ($return){
			case '1':
				exit("内容不能为空");
			case '2':
				exit("收件人不能为空");
			case '3':
				exit("收件人不存在");
			case '4':
				exit("消息已经保存草稿箱");
			case '5':
				exit("信息不存在或已删除");
			case '6':
				exit("所在用户组没有发私信的权限");
			default:
				if($return && is_string($return)) {
					exit($return);
				}
				return '';
		}
	}

		function Pm_Follow_User()
	{
		$uid = MEMBER_ID;

				$uids = get_buddyids($uid);

				$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);

		$perpage = 9;

		if ($page == 0) {
			$page = 1;
		}

		$start = ($page - 1) * $perpage;

		$count = count($uids);

		if($count) {
			$members = $this->TopicLogic->GetMember("where `uid` in ('".implode("','",$uids)."') LIMIT $start,$perpage","`uid`,`ucuid`,`username`,`fans_count`,`validate`,`province`,`city`,`face`,`nickname`");

			$member_list = array();
			foreach ($members as $_m) {
				$member_list[$_m['uid']]['face'] = $_m['face'];
				$member_list[$_m['uid']]['uid'] = $_m['uid'];

				$member_list[$_m['uid']]['nickname'] = $_m['nickname'];
			}

			$page_html = ajax_page($count, $perpage, $page, 'PmFollowUserDialog');
		}

		include template('pm/pm_follow_user_ajax');
	}

		function PmGroupList()
	{
		$uid = (int) MEMBER_ID;
		$gid = (int) $this->Post['group_id'];
		if($uid < 1) {
			exit('请先登录后再操作');
		}
		$member_info = jsg_member_info($uid);
		$group_name = '';
		$count = 0;
		$perpage = 7;
		$page = max(1, jget('page', 'int'));

		$uids = array();
		if($gid > 0 && ($row = $group_info = jtable('buddy_follow_group')->info($gid)) && $row['uid'] == $uid) {
			$group_name = $group_info['name'];
			$count = $group_info['count'];
			if($count > 0) {
				$p = array(
					'uid' => $uid,
					'gid' => $gid,
					'result_count' => $count,
					'per_page_num' => $perpage,
					'sql_order' => ' `dateline` DESC ',
				);
				$uids = jtable('buddy_follow_group')->get_ids($p, 'touid');
			}
		} else {
			$count = $member_info['follow_count'];
			if($count > 0) {
				$p = array(
					'uid' => $uid,
					'result_count' => $count,
					'pre_page_num' => $perpage,
					'sql_order' => ' `dateline` DESC ',
				);
				$uids = get_buddyids($p);
			}
		}
		if($count > 0) {
			$page_html = ajax_page($count, $perpage, $page, 'pm_group_list',$gid);

			$members = $this->TopicLogic->GetMember("where `uid` in (".jimplode($uids).")", "`uid`,`ucuid`,`username`,`fans_count`,`validate`,`province`,`city`,`face`,`nickname`");
			foreach ($members as $_m) {
				$member_list[$_m['uid']]['uid'] = $_m['uid'];
				$member_list[$_m['uid']]['nickname'] = $_m['nickname'];
				$member_list[$_m['uid']]['face'] = $_m['face'];
			}
		}


		include template('pm/pm_follow_user_ajax');

	}

}

?>
