<?php
/**
 * 文件名：login.mod.php
 * @version $Id: login.mod.php 5261 2013-12-13 03:02:47Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户登录
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{


	
	var $Code = false;

	
	var $Username = '';

	
	var $Password = '';

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->Username = isset($this->Post['username'])?trim($this->Post['username']):"";
		$this->Password = isset($this->Post['password'])?trim($this->Post['password']):"";

		if(MEMBER_ID > 0) {
			$this->IsAdmin = $this->MemberHandler->HasPermission('member','admin');
		}
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'goto':
				$this->GoToto();
				break;
			case 'changelogin':
				$this->changeLogin();
				break;
			case 'dologin':
				$this->DoLogin();
				break;
			case 'logout':
				$this->LogOut();
				break;
			default:
				$this->login();
				break;
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}
    
    function login() {
		if(MEMBER_ID > 0){
			header('Location: index.php?mod=topic');
			exit;
		}

		$this->Title="用户登录";

		$referer = referer('index.php');
		$enreferer = urlencode($referer);
		if(jsg_getcookie("referer")=="") {
			jsg_setcookie("referer", $referer);
		}

		$action="index.php?mod=login&code=dologin";

		include(template("login/login_global"));
	}

	
	function GoToto() {		
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		
				$gets = unserialize(base64_decode(jget('gets')));
		if($gets) {
			$url = http_build_query($gets);
			if('topic' == $gets['mod']) {
				$url_mobile = $url_wap = 'mod=topic&code=' . $gets['code'];
				if(is_numeric($gets['code'])) {
					$url_mobile = 'mod=topic&code=detail&tid=' . $gets['code'];
				}
				
				$is_weixin = (false !== strpos($agent, 'micromessenger')) ? true : false;
				if($is_weixin) {
					$this->Messager(null, $this->Config['wap_url'] . '/index.php?' . $url_wap);
				}
			}
					}

		$is_android = (false !== strpos($agent, 'android')) ? true : false;

		$is_iphone = (false !== strpos($agent, 'iphone')) ? true : false;

		include template('goto_wap');
	}


	
	function changeLogin(){
		if(MEMBER_ID < 1){
			json_error('请先登录');
		}
		if(!$this->Config['vest_enable']){
			json_error('未开启马甲功能');
		}
		$uid = jget('uid','int');
		if($uid < 1){
			json_error('登录失败');
		}

		$ret = jlogic('member_vest')->checkMemberVest(MEMBER_ID,$uid);
		if($ret){
			$member = jsg_member_login_set_status($uid);
			if($member){
				json_result('登录马甲成功');
			}
		}
		json_error('登录失败');
	}


	
	function DoLogin()
	{
		
		if ($this->Config['seccode_enable']==1 && $this->Config['seccode_login']) {
			if (!ckseccode(@$_POST['seccode'])) {
				$this->Messager("验证码输入错误",-1);
			}
		}elseif ($this->Config['seccode_enable']>1 && $this->Config['seccode_login'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['YinXiangMa_challenge'],@$_POST['YXM_level'][0],@$_POST['YXM_input_result']);
			if($YinXiangMa_response != "true"){
				$this->Messager("验证码输入错误",-1);
			}
		}


        if($this->Username=="" || $this->Password=="")
		{
			$this->Messager("无法登录,用户名或密码不能为空", -1);
		}


		
		$username = $this->Username;
		$password = $this->Password;


		
		$referer = jget('referer');
		if(!$referer) {
			$referer = jsg_getcookie('referer');
		}

		$rets = jsg_member_login($username, $password);
        $uid = (int) $rets['uid'];
        if($uid < 1) {
        	$this->Messager($rets['error'], null);
        }

        $member = jsg_member_info(MEMBER_ID);

        

		$this->Config['reg_email_verify'] == 1 && $member['email_checked'] == 0 && $referer = 'index.php?mod=member&code=setverify&ids='.$uid;

		$this->Config['email_must_be_true'] == 2 && $member['email_checked'] == 0 && $referer = 'index.php?mod=member&code=setverify&ids='.$uid;

		if($this->Config['extcredits_enable'] && $uid > 0)
		{
			
			update_credits_by_action('login',$uid);
		}

		
		Load::logic('other');
		$otherLogic = new OtherLogic();
		$sql = "SELECT m.id as medal_id,m.medal_img,m.medal_name,m.medal_depict,m.conditions,u.dateline,y.apply_id
				FROM ".TABLE_PREFIX."medal m
				LEFT JOIN ".TABLE_PREFIX."user_medal u ON (u.medalid = m.id AND u.uid = '$uid')
				LEFT JOIN ".TABLE_PREFIX."medal_apply y ON (y.medal_id = m.id AND y.uid = '$uid')
				WHERE m.is_open = 1
				ORDER BY u.dateline DESC,m.id";

		$query = $this->DatabaseHandler->Query($sql);
		while (false != ($rs = $query->GetRow())){
			$rs['conditions'] = unserialize($rs['conditions']);
			if(in_array($rs['conditions']['type'],array('topic','reply','tag','invite','fans','sign')) && !$rs['dateline']){
				$result .= $otherLogic->autoCheckMedal($rs['medal_id'],$uid);
			}
		}


		
		$redirecto=($referer?$referer:referer());

		$redirecto = str_replace('#','',$redirecto);				if($this->Post['loginType'] == 'share')
		{
			$redirecto = $this->Post['return_url'];
			$this->Messager(null,$redirecto,0);
		}

				if($this->Post['loginType'] == 'show_login')
		{
			$this->Messager(NULL,$redirecto,0);
		}

		if($rets['uc_syn_html'])
        {
            $this->Messager("登录成功{$rets['uc_syn_html']}",$redirecto,3);
        }
        else
        {
            $this->Messager(null,$redirecto);
        }
	}


	
	function LogOut()
	{
		$msg = null;
		$time = 0;
		$to = '?';


		$rets = jsg_member_logout();
		if($rets['uc_syn_html']) {
			$msg = "退出成功{$rets['uc_syn_html']}";
			$time = 3;
		}


		$rets = jsg_member_login_extract();
		if($rets && $rets['logout_url']) {
			$to = $rets['logout_url'];
		}


		$this->Messager($msg,$to,$time);
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
}
?>