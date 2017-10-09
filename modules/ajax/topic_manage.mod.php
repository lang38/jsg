<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename topic_manage.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 141892351 4974 $
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
			case 'force_out':
				$this->ForceOut();
				break;
			case 'do_force_out':
				$this->doForceOut();
				break;
			case 'sendemail':
				$this->sendEmail();
				break;
			case 'dosendemail':
				$this->doSendEmail();
				break;
			case 'force_ip':
				$this->ForceIP();
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

	
	function ForceIP(){
		$ip = trim($this->Post['ip']);
		if(!$ip){
			json_error("无效IP。");
		}
		if('admin'!=MEMBER_ROLE_TYPE){
			json_error("您没有封IP权限");
		}
				if($ip == $GLOBALS['_J']['client_ip'])
		{
			json_error("无法禁止当前的IP。");
		}
		if(preg_match("/^(\d{1,3}\.){1,3}\d{1,3}\.?$/",$ip)){
			$ip = str_replace(".","\.",$ip);
		}

		$access = jconf::get('access');
		$ip_arr = explode('|',$access['ipbanned']);
		if($ip_arr && in_array($ip,$ip_arr))
		{
			json_error("已禁止此IP");
		}

		if($access['ipbanned']){
			$access['ipbanned'] .= '|'.$ip;
		} else {
			$access['ipbanned'] = $ip;
		}
		jconf::set('access',$access);

		$config = array();
		$config['ipbanned_enable'] = ($access['ipbanned'] ? 1 : 0);

		jconf::update($config);
		json_result('禁止IP:'.$ip.'成功');
	}
	
	function ForceOut(){
		$uid = (int) $this->Get['uid'];
		$name = DB::result_first("select nickname from ".TABLE_PREFIX."members where uid = '$uid'");
		include template('social/force_out_ajax');;
	}

	function doForceOut(){
		$nickname_arr = array();
		$member_list = array();
		$force_out_list= array();

		if('admin'!=MEMBER_ROLE_TYPE){
			json_error("您没有封杀用户的权限");
		}

		$cause = trim($this->Post['cause']);
		$role_id = (int) $this->Post['role_id'];
		$nickname = trim($this->Post['name']);
		$nickname_arr = explode(",",$nickname);

		load::logic('topic_manage');
		$TopicManageLogic = new TopicManageLogic();

		$ret = $TopicManageLogic->doForceOut($nickname_arr,$cause,$role_id);
		$ret_arr = array(1=>'管理员不能放入封杀组',
						 2=>'封杀成功',);

		json_result($ret_arr[$ret]);

	}

		function sendEmail() {
		$tid = (int) $this->Get['tid'];
		$uid = (int) $this->Get['uid'];
		$type = $this->Get['type'] ? $this->Get['type'] : 'topic';

		if('admin' != MEMBER_ROLE_TYPE) {
			exit('您没有权限进行此项操作');
		}
		
		if($tid < 1 || $uid < 1) {
			exit("报备对象有误。");
		}

		if($type == 'event'){
			$typename = '活动';
		}elseif($type == 'qun'){
			$typename = $this->Config[changeword][weiqun];
		}else{
			$typename = $this->Config[changeword][n_weibo];
		}
		$nickname = DB::result_first("select nickname from ".TABLE_PREFIX."members where uid = '$uid' ");
		$message = "{$nickname}发布的{$typename}（{$tid}）,涉及到内容：";

				$leader_list = jconf::get('leader_list');

	    include template('social/force_out_ajax');
	}

	function doSendEmail() {
		if('admin' != MEMBER_ROLE_TYPE) {
			exit('您没有权限进行此项操作');
		}
		
		$message = jpost('message', 'txt');
		if(empty($message)) {
			json_result('内容不能为空');
		}
		
				$leader_list = jconf::get("leader_list");
		if(empty($leader_list)) {
			json_result('没有需要报备的领导、可以到后台<a href="admin.php?mod=member&code=leaderlist" target="_blank">报备用户列表</a>处添加需要报备的领导email');
		}

		$leader = (array) $this->Post['leader'];
		foreach($leader as $k=>$v) {
			if($v && $v==$leader_list[$k]) {
				;
			} else {
				unset($leader[$k]);
			}
		}
		if(count($leader) < 1) {
			json_result("请选择需要报备的领导email。");
		}

		$return_msg = '';
		foreach ($leader as $key => $val) {
			$mail_to = $val;
			$mail_subject = "来自".$this->Config['site_name']."微博-管理员 ".MEMBER_NICKNAME." 的报备";
			$mail_content = $message;
			$mail_from_username = MEMBER_NAME;
			$mail_from_email = 'no-reply@jishigou.net';
			$mail_from_email = $this->Config['site_admin_email'];

			$send_result = send_mail($mail_to,$mail_subject,$mail_content,$mail_from_username,$mail_from_email,array(),3,false);
			if(!$send_result) {
				$return_msg .= "发给[".$val."]出现错误.<br>";
			}
		}

		json_result($return_msg ? $return_msg : "报备成功");
	}

}