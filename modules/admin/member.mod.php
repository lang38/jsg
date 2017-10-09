<?php
/**
 * 文件名：member.mod.php
 * @version $Id: member.mod.php 5637 2014-03-10 08:57:11Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户管理模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{

	
	var $ID = 0;

	
	var $IDS;
	var $CpLogic;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = jget('id', 'int');

		$this->IDS = ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);



				if ($this->Config['company_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$this->CpLogic = jlogic('cp');
		}
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
						case 'profile':
				$this->Profile();
				break;
			case 'setprofile':
				$this->setProfile();
				break;

			case 'editface':
				$this->adminEditFace();
				break;
			case 'do_modify_face':
				$this->doModifyFace();
				break;
						case 'config':
				$this->vestSetting();
				break;
			case 'setvest':
				$this->SetVest();
				break;
			case 'vest':
				$this->memberVest();
				break;
									case 'force_out':
				$this->ForceOut();
				break;
			case 'login':
				$this->login();
				break;
			case 'leaderlist':
				$this->LeaderList();
				break;
			case 'setleaderlist':
				$this->SetLeaderList();
				break;
			case 'doforceout':
				$this->doForceOut();
				break;

			case 'doact':
				$this->doAct();


			case 'add':
				$this->Add();
				break;

			case 'doadd':
				$this->DoAdd();
				break;

			case 'delete':
			case 'dodelete':
				$this->DoDelete();
				break;

			case 'search':
				$this->search();
				break;
			case 'dosearch':
				$this->DoSearch();
				break;

			case 'modify':
				$this->Modify();
				break;
			case 'domodify':
				$this->DoModify();
				break;

							case 'follow_user_recommend':
				$this->Follow_User_Recommend();
				break;
			case 'do_follow_user_recommend':
				$this->Do_Follow_User_Recommend();
				break;
			case 'del_follow_user_recommend':
				$this->Del_Follow_User_Recommend();
				break;

			case 'export_all_user':
				$this->ExportAllUser();
				break;

			case 'sendpm':
				$this->SendPm();
				break;

							case 'waitvalidate':
				$this->WaitForValidation();
				break;
			case 'dowaitvalidate':
				$this->doWaitForValidation();
				break;
			case 'user_login_log':
				$this->user_login_log();
				break;
			case 'cpuser':
				$this->cp_user();
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
		$this->DoSearch();
	}

	
	function Profile(){
		#不允许无规则修改的项目
		$noEditProfile = array('realname','idcardtype','idcard','residecity');
				$can_search_profile = array('field1', 'field2', 'field3', 'field4', 'field5', 'field6', 'field7', 'field8');

		$profile = jlogic('member_profile')->getProfile();
		$groupProfile = jconf::get('groupprofile');
		$showincord = jconf::get('profileshowincord');
		$register = jconf::get('profileregister');
		$search = jconf::get('profilesearch');

		include template('admin/profile');
	}

	function setProfile(){
		
		#排序
		$displayorder = jget('displayorder');
		jlogic('member_profile')->setProfileOrder($displayorder);
		#自定义的title
		$title = jget('title');
		jlogic('member_profile')->setProfileTitle($title);

				$profile = jlogic('member_profile')->getProfile();

		#基本资料、联系方式、教育情况、工作情况、个人信息  分类保存
		$groupProfile = jget('groupprofile');
		$newGroupProfile = array();

		$type = array('base'=>'基本资料','job'=>'工作情况','edu'=>'教育情况','link'=>'联系方式','info'=>'个人信息');
		foreach ($type as $key => $val) {
			if($groupProfile[$key]){
				$newGroupProfile[$key] = array(
					'key' => $key,
					'title' => $val,
				);
				foreach ($groupProfile[$key] as $k => $v) {
					if($profile[$k]){
						$list[$k] = $profile[$k];
					}
				}
				$newGroupProfile[$key]['list'] = $list;
				unset($list);
			}
		}
		jconf::set('groupprofile',$newGroupProfile);

		#是否在名片夹中显示
		$showincord = jget('showincord');
		if($showincord){
			foreach ($showincord as $key => $val) {
				$showincord[$key] = $profile[$key]['title'];
			}
		} else {
			$showincord = array();
		}
		jconf::set('profileshowincord',$showincord);

		#是否在注册页面中可填写
		$register = jget('register');
		if($register){
			foreach ($register as $key => $val) {
				$register[$key] = $profile[$key];
			}
		} else {
			$register = array();
		}
		jconf::set('profileregister',$register);

				$search = jget('search');
		if($search){
			foreach ($search as $key => $val) {
				$search[$key] = $profile[$key];
			}
		} else {
			$search = array();
		}
		jconf::set('profilesearch',$search);


		$this->Messager('设置成功','admin.php?mod=member&code=profile');
	}

	
	function adminEditFace(){
		$uid = jget('uid','int');
		if($uid < 1){
			$this->Messager('请选择你要修改头像的用户',-1);
		}

		$member = jsg_member_info($uid);
        $temp_face = '';
        if($this->Get['temp_face'] && is_image($this->Get['temp_face']))
        {
        	$temp_face = $this->Get['temp_face'];

         	$member['face_original'] = $temp_face;
        }
		include template('admin/admin_edit_face');
	}

	function doModifyFace() {
        
        if(MEMBER_ID < 1) {
            $this->Messager("请先<a href='index.php?mod=login'>点此登录</a>或者<a href='index.php?mod=member'>点此注册</a>一个帐号",null);
        }

		$uid = jget('uid','int');
		if($uid < 1){
			$this->Messager('请选择你要修改头像的用户',-1);
		}

        $pic_file = $this->Post['img_path'];
        if(empty($pic_file) || !is_image($pic_file)) {
        	$this->Messager('请上传正确的图片文件');
        }

        
        $p = array(
        	'uid' => $uid,

        	'pic_file' => $pic_file,

        	'x' => $this->Post['x'],
        	'y' => $this->Post['y'],
        	'w' => $this->Post['w'],
        	'h' => $this->Post['h'],
        );
        $rets = jlogic('user')->face($p);
        if($rets && $rets['error']) {
        	$this->Messager($rets['msg']);
        }

        
        $to = 'admin.php?mod=member&code=modify&id='.$uid;
        if($this->Config['face_verify']) {
	        $this->Messager("头像设置成功,管理员审核中...", $to);
        } else {
        	$this->Messager("头像设置成功", $to);
        }
	}

	
	function vestSetting(){
		$type = 'set';
				$vest_enable = $this->jishigou_form->YesNoRadio('config[vest_enable]',(int) $this->Config['vest_enable']);

		$role_list = array();
		$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
		$v = 0;
		while (false != ($row = DB::fetch($query))) {
			$v = $row['value'];
			$role_list[$v] = $row;
		}

		$vest_role=$this->jishigou_form->CheckBox('config[vest_role][]',$role_list,explode(',',$this->Config['vest_role']));

		include template('admin/member_vest');
	}

	function SetVest() {
		$config = array();
		$config['vest_enable'] = ($this->Post['config']['vest_enable'] ? 1 : 0);
		$config['vest_role'] = ($this->Post['config']['vest_role'] ? implode(',', $this->Post['config']['vest_role']) : '');

		jconf::update($config);
		$this->Messager('设置成功','admin.php?mod=member&code=config');
	}

	function memberVest(){
		$type = 'manage';
		$param = array();
		$url = 'admin.php?mod=member&code=vest';
		$nickname = jget('nickname');
		$uid = DB::result_first("select `uid` from `".TABLE_PREFIX."members` where `nickname` = '$nickname' limit 1");
		$uid && $param['where'] = " mv.`uid` = '$uid' || mv.`useruid` = '$uid' ";

		$vest_member = DB::result_first("select count(*) from `".TABLE_PREFIX."members_vest` where `uid` = `useruid`");

		$param['per_page_num'] = 20;
		$param['page_url'] = $url;
		$ret = jlogic('member_vest')->getVestMemberByParam($param);
		extract($ret);
		include template('admin/member_vest');
	}

	
	function login(){
		$t = (int) get_param('time');
		$t = $t > 0 ? $t :24;
		$t_str = $t * 60 * 60;
		$t_str = TIMESTAMP - $t_str;
		$where = ' where ' . " `lastactivity` >= '$t_str' ";
		$nickname = get_param('nickname');
		if($nickname){
			$where .= " and `nickname` = '$nickname' ";
		}

		$param = array(
			'mod' => $this->Get['mod'],
			'code' => 'login',
			'time' => $t,
			'nickname' => $nickname,
		);
		$url = 'admin.php?'.url_implode($param);

		$members = array();
		$count = DB::result_first("  select count(*) from `".TABLE_PREFIX."members` $where ");
		if($count > 0){
			$page = page($count,50,$url,array('return'=>'array'));
			$query = DB::query("select `uid`,`username`,`nickname`,`lastactivity`,`lastip`,`last_ip_port`,`topic_count`,`province`,`city` from `".TABLE_PREFIX."members` $where order by `lastactivity` desc $page[limit] ");

			while ($rs = DB::fetch($query)) {
				$members[$rs['uid']] = $rs;
			}
		}

		include template('admin/user_login');
	}

	
	function WaitForValidation(){
				$sql = "select count(*) from `".TABLE_PREFIX."members` where role_id = 5 ";
		$count = DB::result_first($sql);

		$page_num=20;
		$query_link = "admin.php?mod=member&code=waitvalidate";
		$pages=page($count,$page_num,$query_link,array('return'=>'array'));
		$limit = $pages['limit'];

		$sql = "select m.uid,m.username,m.nickname,m.regip,m.regdate,m.email,m.invite_uid,m2.nickname as invite_name
				 from `".TABLE_PREFIX."members` m
				 left join `".TABLE_PREFIX."members` m2 on m2.uid = m.invite_uid
				 where m.role_id = 5 order by m.uid desc $limit ";
		$query = DB::query($sql);
		while ($rs = DB::fetch($query)){
			$rs['face'] = face_path($rs['uid']);
			$members[$rs['uid']] = $rs;
		}
		include template('admin/member_wait_validate');
	}
	function doWaitForValidation(){
		$ids = get_param('ids');
		if($ids){
						if($sendmsgname = $this->Config['notice_to_new_user']){
				$admin_info = DB::fetch_first("select `uid`,`username`,`nickname` from `".TABLE_PREFIX."members` where `nickname` = '$sendmsgname'");
			} else {
				$admin_info['uid'] = MEMBER_ID;
				$admin_info['username'] = MEMBER_NAME;
				$admin_info['nickname'] = MEMBER_NICKNAME;
			}

			$pmLogic = jlogic('pm');
			$uids = jimplode($ids);
			$sql = "select `uid`,`nickname` from `".TABLE_PREFIX."members` where uid in ($uids)";
			$query = DB::query($sql);
			while ($rs = DB::fetch($query)){
				$nickname_arr[$rs['uid']] = $rs['nickname'];
			}
			$nicknames = implode(',',$nickname_arr);
			$pm_post_touser = array(
				'message' => $this->Config['notice_to_validatesucssee_user'] ? $this->Config['notice_to_validatesucssee_user']  : "你注册的帐号已通过审核，欢迎加入".$this->Config['site_name'].",与大家一起开始交流吧。",
				'to_user' => $nicknames,
			);
			$pmLogic->pmSend($pm_post_touser,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);

						DB::query("update `".TABLE_PREFIX."members` set role_id = 3 where uid in ($uids)");
		}

		$this->Messager("设置成功","admin.php?mod=member&code=waitvalidate");
	}

		function ForceOut(){
		$type = get_param('type');
		$type = $type ? $type : 4;

		$user_arr = array();
						$sql = "SELECT o.*,m1.username AS username ,m1.nickname AS nickname , m2.username AS dousername,m2.nickname AS donickname
				FROM `".TABLE_PREFIX."force_out` o
				LEFT JOIN `".TABLE_PREFIX."members` m1 ON m1.uid= o.uid
				LEFT JOIN `".TABLE_PREFIX."members` m2 ON m2.uid = douid
				WHERE o.role_id = '$type'
				ORDER BY o.dateline DESC";

		$query = DB::query($sql);
		while ($rs = DB::fetch($query)) {
			$user_arr[$rs['uid']] = $rs;
		}

		include template('admin/force_out_memberlist');
	}

		function LeaderList(){
		$leader_list = jconf::get("leader_list");

		include template('admin/leader_list');
	}

		function SetLeaderList(){
		$leader_list = array();
		$chk = (array) $this->Post['chk'];
		$name = (array) $this->Post['name'];
		$email = (array) $this->Post['email'];

		foreach ($chk as $key => $value) {
			unset($name[$key]);
			unset($email[$key]);
		}

		foreach ($name as $key => $value) {
			$value = trim($value);
			$email[$key] = trim($email[$key]);
			if($value && $email[$key]){
				$leader_list[$key]['name'] = $value;
				$leader_list[$key]['email'] = $email[$key];
			}else{
				continue;
			}
		}
		jconf::set("leader_list",$leader_list);
		$this->Messager("修改成功");
	}

	
	function Add()
	{
		$sql = "select id,name
		 FROM
			 " . TABLE_PREFIX.'role' . "
		 WHERE
			 `id`>1" . (true === JISHIGOU_FOUNDER ? "" : " and `type`='normal'");
		$query = $this->DatabaseHandler->Query($sql);
		$role_list = array();
		while(false != ($row = $query->GetRow())) {
			$role_list[$row['id']] = array('name' => $row['name'], 'value' => $row['id']);
		}

		$role_select = $this->jishigou_form->Select('role_id', $role_list, $this->Config['normal_default_role_id']);
		$action = "admin.php?mod=member&code=doadd";
		$title = "添加";
		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$companyselect = $this->CpLogic->get_cp_html();
			if ($this->Config['department_enable']){
				$departmentselect = $this->CpLogic->get_cp_html(0,'department');
			}
			$jobselect = jlogic('job')->get_job_select();
		}
		include template('admin/member_add');
	}

	
	function DoAdd()
	{
		$nickname = ($this->Post['nickname'] ? $this->Post['nickname'] : $this->Post['username']);
		$password = $this->Post['password'];
		$email = $this->Post['email'];
		if(!$nickname || !$password || !$email) {
			$this->Messager('帐户、密码、Email 不能为空');
		}


		define('JISHIGOU_FORCED_REGISTER', true);
		$uid = jsg_member_register($nickname, $password, $email);
		if($uid < 1) {
			$regconf = jconf::get('register');
			$rets = array(
				'0' => '【注册失败】有可能是站点关闭了注册功能',
				'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
				'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
				'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
				'-4' => 'Email 不合法，请输入正确的Email地址。',
				'-5' => 'Email 不允许注册，请尝试更换一个。',
				'-6' => 'Email 已经存在了，请尝试更换一个。',
				'-7' => '您的IP地址 ' . $GLOBALS['_J']['client_ip'] . ' 已经被限制注册了（一个IP地址 '.$regconf['time_html'].' 之内，最多只能注册 '.$regconf['limit'].' 个用户），请稍后再试或联系管理员',
			);

			$this->Messager($rets[$uid], null);
		}


		$role_id = (int) $this->Post['role_id'];
		if($role_id > 1) {
			$data = array();
			$data['role_id'] = $role_id;

						$sql="select * from ".TABLE_PREFIX."role where id='{$data['role_id']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$role=$query->GetRow();
			if ($role) {
				$data['role_type']=$role['type'];

				if(true===JISHIGOU_FOUNDER || 'normal'==$role['type']) {
					DB::update('members', $data, "`uid`='$uid'");
				}
			}

			if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
								if($this->Post['companyid']){
					$data['companyid'] = (int)$this->Post['companyid'];					$data['company'] = $this->CpLogic->Getone($data['companyid'],'company','name');
					if($data['companyid']>0){
						$this->CpLogic->update('company',$data['companyid'],1,0);
					}
				}
				if($this->Config['department_enable'] && $this->Post['departmentid']){
					$data['departmentid'] = (int)$this->Post['departmentid'];					$data['department'] = $this->CpLogic->Getone($data['departmentid'],'department','name');
					if($data['departmentid']>0){
						$this->CpLogic->update('department',$data['departmentid'],1,0);
					}
				}
				if($this->Post['jobid']){
					$datas['jobid'] = (int)$this->Post['jobid'];					$datas['job'] = jlogic('job')->id2subject($arr['jobid']);
				}
			}

			DB::update('members', $data, "`uid`='$uid'");
		}


		$this->Messager("添加成功", 'admin.php?mod=member');
	}

	
	function Search()
	{
		$action = "admin.php?mod=member&code=dosearch";
				$sql = "select id,name
		 FROM
			 " . TABLE_PREFIX.'role' . "
		 WHERE
			 id>1";
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow()))
		{
			$role_list[] = $row;
		}
		$role_count = count($role_list) + 1;

				$credit_list = array();
		if($this->Config['extcredits_enable'])
		{
			$credit_list[] = array('name' => "lower[credits]", 'describe' => "总积分 低于");
			$credit_list[] = array('name' => "higher[credits]", 'describe' => "总积分 高于");

			foreach($this->Config['credits']['ext'] as $key => $val)
			{
				$credit_list[] = array('name' => "lower[{$key}]", 'describe' => "{$val[name]} 低于");
				$credit_list[] = array('name' => "higher[{$key}]", 'describe' => "{$val[name]} 高于");
			}
		}

				$sql = "select * from `".TABLE_PREFIX."medal` ";
		$query = $this->DatabaseHandler->Query($sql);
		$medal = array();
		while (false != ($rs = $query->GetRow())){
			$medal[$rs['id']] = $rs['medal_name'];
		}
		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$companyselect = $this->CpLogic->get_cp_html();
			if ($this->Config['department_enable']){
				$departmentselect = $this->CpLogic->get_cp_html(0,'department');
			}
			$jobselect = jlogic('job')->get_job_select();
		}

		include template('admin/member_search');
	}

	
	function DoSearch()
	{
				$sql = "select * from `".TABLE_PREFIX."medal` ";
		$query = $this->DatabaseHandler->Query($sql);
		$medal = array();
		while (false != ($rs = $query->GetRow())){
			$medal[$rs['id']] = $rs['medal_name'];
		}

				$credit_search_list = array();
		if($this->Config['extcredits_enable'])
		{
			foreach($this->Config['credits']['ext'] as $key => $val)
			{
				$credit_search_list["l[{$key}]"] = array('name' => "lower[{$key}]", 'describe' => "{$val[name]} 低于");
				$credit_search_list["h[{$key}]"] = array('name' => "higher[{$key}]", 'describe' => "{$val[name]} 高于");
			}
		}


		$where_list = array();
		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$companyid = (int)get_param('companyid');
			if($companyid > 0){
				$where_list['companyid'] = " m.companyid = '$companyid'";
				$cp_user_ids = $this->CpLogic->get_cp_user_uids($companyid);
			}
			$jobid = (int)get_param('jobid');
			if($jobid > 0){
				$where_list['jobid'] = " m.jobid = '$jobid'";
			}
			if($this->Config['department_enable']){
				$departmentid = (int)get_param('departmentid');
				if($departmentid > 0){
					$where_list['departmentid'] = " m.departmentid = '$departmentid'";
					$cp_user_ids = $this->CpLogic->get_cp_user_uids($departmentid,'department');
				}
			}
		}
		$uid = (int) get_param('uid');
		if($uid){
			$where_list['uid'] = " m.uid = '$uid'";
		}
		$nickname = get_param('nickname');
		if($nickname != '')
		{
			$where_list['nickname'] = build_like_query('m.nickname', $nickname);
		}
		$username = get_param('username');
		if($username != '')
		{
			$where_list['username'] = build_like_query('m.username', $username);
		}
		$email = get_param('email');
		if($email != '')
		{
			$where_list['email'] = build_like_query('m.email', $email);
		}
		$regip = get_param('regip');
		if($regip != '')
		{
			$where_list['regip'] = " m.regip like '{$regip}%' ";
		}
		$lastip = get_param('lastip');
		if($lastip != '')
		{
			$where_list['lastip'] = " m.lastip like '{$lastip}%' ";
		}
		$invite_uid = max(0, (int) get_param('invite_uid'));
		$invite_nickname = get_param('invite_nickname');
		if(''!=$invite_nickname) {
			$invite_uid = DB::result_first("select `uid` from `".TABLE_PREFIX."members` where `nickname`='$invite_nickname'");
		}
		if($invite_uid > 0) {
			$where_list['invite_uid'] = "`invite_uid`='$invite_uid'";
		}
		$role_id = get_param('role_id');
		$role_ids = get_param('role_ids');
		if(is_array($role_id) and count($role_id))
		{
			if($role_id[0] != 'all')
			{
				$where_list['role_id'] = " `role_id` IN (" . jimplode($role_id) . ") ";

				$_GET['role_ids']=implode(",", $role_id);
			}
			else
			{
				unset($where_list['role_id']);
			}
		}
		elseif(is_string($role_ids) and $role_ids)
		{
			$where_list['role_id'] =" m.role_id in($role_ids)";
		}

		$order_arr = array();
		$lower = get_param('lower');
		if(is_array($lower))
		{
			foreach($lower as $field => $val)
			{
				if($val != '')
				{
					$where_list[$field . '_lower'] = " m.{$field}<=" . (int)$val;
					$list["l[$field]"] = $val;
					$order_arr[$field] = ' m.'.$field.' desc ';
				}
			}
		}

		$higher = get_param('higher');
		if(is_array($higher))
		{
			foreach($higher as $field => $val)
			{
				if($val != '')
				{
					$where_list[$field . '_higher'] = " m.{$field}>=" . (int)$val;
					$list["h[$field]"] = $val;
					$order_arr[$field] = ' m.'.$field.' desc ';
				}
			}
		}

				$earned = get_param('earned');
		if($earned){
			$medal_arr[$earned] = " selected ";
			$medal_where = " LEFT JOIN ".TABLE_PREFIX."user_medal um ON um.uid = m.uid and um.medalid = '$earned' ";
			$select_sql = " ,um.dateline  ";
			$where_list['earned'] = " um.`dateline` IS NULL ";
		}

				$sql = "select id,name,`type`
		 FROM
			 " . TABLE_PREFIX.'role' . "
		 WHERE
			 id>1";
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow()))
		{
			$role_list[$row['id']] = $row;
		}
		$where = (empty($where_list)) ? null : ' WHERE '.implode(' AND ',$where_list).' ';
		if($where && $cp_user_ids){			$where .= ' OR uid IN('.jimplode($cp_user_ids).') ';
		}
		$order_by_list = array(
			'order_by_default' => 'uid',
			'uid' => array(
				'name' => '最新注册',
				'order_by' => 'm.`uid`',
		),
			'lastactivity' => array(
				'name' => '最近活跃',
				'order_by' => 'm.`lastactivity`',
		),
			'credits' => array(
				'name' => '最多积分',
				'order_by' => 'm.`credits`',
		),
		);
		$query_link = 'admin.php?' . ((is_array($_POST) and count($_POST)) ? http_build_query(array_merge($_GET,$_POST)) : $_SERVER['QUERY_STRING']);
		$order_arr = order($order_by_list, $query_link);
		$order_html = $order_arr['html'];
		$query_link = $order_arr['query_link'];
		$order = $order_arr['order'];



				$sql = "select count(1) total
		  FROM
			  " . TABLE_PREFIX.'members' . " m
			  $medal_where
			  $where";
			  $total = DB::result_first($sql);

			  $page_num=20;
			  $pages=page($total,$page_num,$query_link,array('return'=>'array'));
			  $limit = $pages['limit'];
			  $sql = "select m.* $select_sql
		  FROM

			  " . TABLE_PREFIX.'members' . " m
			  $medal_where
			  $where
			  {$order}
			  {$limit}";


			  $query = $this->DatabaseHandler->Query($sql);
			  $uids = array();
			  $invite_uids = array();
			  while(false != ($row = $query->GetRow()))
			  {
				$uids[$row['uid']] = $row['uid'];

				if($row['invite_uid'] > 0) {
					$invite_uids[$row['invite_uid']] = $row['invite_uid'];
				}

				if($this->Config['credits']['ext']) {
					$credit_list = array();
					$row['totle_credit'] = 0;
					foreach($this->Config['credits']['ext'] as $key => $val) {
						$credit_list[] = array('credit' => $row[$key], 'name' => "$val[name]");
						$row['totle_credit'] = $row['totle_credit'] + $row[$key];
					}
					$row['credit'] = $credit_list;
				}

				$role = $role_list[$row['role_id']];
				if($role != false)
				{
					if($role['is_system'] == 1)
					{
						$row['role_name'] = "<B>{$role['name']}</B>";
					}
					else
					{
						$row['role_name'] = $role['name'];
					}
				}
				$member_list[$row['uid']] = $row;
			  }

			  			  $sql = "select `uid`,`validate_remark`
		  FROM
			  " . TABLE_PREFIX.'memberfields' . "
		  WHERE
			  `uid` in ('".implode("','",$uids)."') ";
			  $query = $this->DatabaseHandler->Query($sql);
			  $memberfields = array();
			  while (false != ($row = $query->GetRow()))
			  {
				$member_list[$row['uid']]['validate_remark'] = $row['validate_remark'];
				$memberfields[$row['uid']] = $row;
			  }

			  if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
				  $companyselect = $this->CpLogic->get_cp_html($companyid);
				  if ($this->Config['department_enable']){
					  $departmentselect = $this->CpLogic->get_cp_html($departmentid,'department',$companyid);
				  }
				  $jobselect = jlogic('job')->get_job_select($jobid);
			  }

			  			  if($invite_uids && is_array($invite_uids)) {
				$sql = "select `uid`, `nickname`, `username` from `".TABLE_PREFIX."members` where `uid` in (".jimplode($invite_uids).") limit " . count($invite_uids);
				$query = $this->DatabaseHandler->Query($sql);
				$invite_members = array();
				while (false != ($row = $query->GetRow())) {
					$invite_members[$row['uid']] = $row;
				}
			  }

			  $action = 'admin.php?mod=member&code=doact';

			  include template('admin/member_search_list');
	}

	
	function doAct(){
		$uids = array();
		$ids = get_param('ids');
		$uids = $ids;
		$act = get_param('act');
		$msg = jpost('msg', 'txt');
		$medal_id = get_param('medal_id');
		if($act == 'sendmsg'){
			if($msg == ''){
				$this->Messager("请输入私信的内容",-1);
			}

			$admin_nickname = DB::result_first("select `nickname` from `".TABLE_PREFIX."members` where uid = 1 ");
			load::logic("pm");
			$PmLogic = new PmLogic();

			if($uids){
				$query = $this->DatabaseHandler->Query("select `nickname` from `".TABLE_PREFIX."members` where uid in (".jimplode($uids).")");
				$nickname_arr = array();
				while (false != ($rs = $query->GetRow())){
					$nickname_arr[] = $rs['nickname'];
				}
			}
			if($nickname_arr){
				$post['to_user'] = implode(",",$nickname_arr);
				$post['message'] = $msg;
				$adminInfo = DB::fetch_first("select `username`,`nickname` from `".TABLE_PREFIX."members` where `uid` = '1'");
				$PmLogic->pmSend($post);
			}
		}elseif($act == 'setmedal'){
			if($medal_id == ''){
				$this->Messager("请选择要发放的勋章",-1);
			}

			load::logic('other');
			$OtherLogic = new OtherLogic();

			foreach($uids as $val){
								$sql = "select * from `".TABLE_PREFIX."members` Where  `uid` = '".$val."' ";
				$query = $this->DatabaseHandler->Query($sql);
				$members=$query->GetRow();
				$OtherLogic->giveUserMedal($medal_id,$members);
			}

		}elseif($act == 'deluser'){
			$this->DoDelete($uids);
		}else{
			$this->Messager("请选择要执行的操作",-1);
		}

		$this->Messager("操作成功");
	}

	
	function DoDelete($uid=0) {
		$ids = array();
		if($uid){
			$ids = $uid;
		}else{
			$this->IDS = (array) ($this->IDS ? $this->IDS : $this->ID);

			foreach ($this->IDS as $v) {
				$v = is_numeric($v) ? $v : 0;
				if($v > 0) $ids[$v] = $v;
			}
		}
		if (!$ids) {
			$this->Messager("请先指定一个要删除的用户ID",-1);
		}
		$rets = jsg_member_delete($ids);

		$member_ids_count = $rets['member_ids_count'];
		$admin_list = $rets['admin_list'];


		$msg = '';
		$msg .= "成功删除 <b>{$member_ids_count}</b> 位会员";
		if($admin_list) {
			$msg .= "，其中 <b>".implode(' , ',$admin_list)."</b> 是管理员，不能直接删除";
		}
		$this->Messager($msg, '', 10);
	}

	
	function Modify()
	{
        $this->ID = ($this->ID>0 ? $this->ID : MEMBER_ID);
        if($this->ID < 1) {
			$this->Messager("请指定一个正确的UID");
		}

		$this->Title="编辑用户";

        $member_info = DB::fetch_first("SELECT *
		 FROM
			 ".TABLE_PREFIX.'members'." M LEFT JOIN ".TABLE_PREFIX.'memberfields'." MF ON(M.uid=MF.uid)
		 WHERE
			 M.uid='{$this->ID}'");
		if(!$member_info) {
			$this->Messager("用户已经不存在");
		}


				$admin_check_allow = admin_check_allow($member_info['uid']);


		$role_id = $gender = $bday = '';
		extract($member_info);

		$member_face = face_get($uid);


				$sql = "select id,name
		 FROM
			 " . TABLE_PREFIX.'role' . "
		 WHERE
			 `id`>1 ".(true===JISHIGOU_FOUNDER ? "" : " AND `type`='normal'");
		$query = $this->DatabaseHandler->Query($sql);
		while(false != ($row = $query->GetRow()))
		{
			$role_list[$row['id']] = array('name' => $row['name'], 'value' => $row['id']);
		}

		$role_select = $this->jishigou_form->Select('role_id', $role_list,$role_id," onchange=\"showcause();\" ");

		$role_name = ($role_list[$role_id]['name'] ? $role_list[$role_id]['name'] : (DB::result_first("select `name` from ".DB::table('role')." where `id`='{$role_id}'")));
		$gender_radio=$this->jishigou_form->Radio('gender',array(array('name'=>"男",'value'=>'1'),array('name'=>"女",'value'=>'2'),),$gender);

		$_options = array(
			'0' => array(
				'name' => '请选择',
				'value' => '0',
			),
			'身份证' => array(
				'name' => '身份证',
				'value' => '身份证',
			),
			'学生证' => array(
				'name' => '学生证',
				'value' => '学生证',
			),
			'军官证' => array(
				'name' => '军官证',
				'value' => '军官证',
			),
			'护照' => array(
				'name' => '护照',
				'value' => '护照',
			),
			'营业执照' => array(
				'name' => '营业执照',
				'value' => '营业执照',
			),
			'官方公函' => array(
				'name' => '官方公函',
				'value' => '官方公函',
			),
			'驾驶证' => array(
				'name' => '驾驶证',
				'value' => '驾驶证',
			),
			'其他' => array(
				'name' => '其他',
				'value' => '其他',
			),
		);
		$validate_card_type_select = $this->jishigou_form->Select('validate_card_type',$_options,$member_info['validate_card_type']);

		$login_enable = jconf::get('login_enable');
		$cause = $login_enable[$role_id][$member_info['uid']]['cause'];

		$uid = $this->ID;

		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$companyselect = $this->CpLogic->get_cp_html($member_info['companyid']);
			if ($this->Config['department_enable']){
				$departmentselect = $this->CpLogic->get_cp_html($member_info['departmentid'],'department',$member_info['companyid']);
			}
			$jobselect = jlogic('job')->get_job_select($member_info['jobid']);
			$morcompanys = $this->CpLogic->get_cp_users($member_info['uid']);
		}

		#附加项目
		$groupProfile = jconf::get('groupprofile');
		$member = $member_info;

		#附加信息
		$member_profile = jlogic('member_profile')->getMemberProfileInfo($member['uid']);
		if($member_profile){
            $member['address'] = $member_profile['address'];
			$member = array_merge($member_profile,$member);
		}
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
		while ($rsdb = $query->GetRow()){
			$province_arr[$rsdb['id']]['value']  = $rsdb['id'];
			$province_arr[$rsdb['id']]['name']  = $rsdb['name'];
			if($province == $rsdb['name']){
				$province_id = $rsdb['id'];
			}
		}
		$province_list = $this->jishigou_form->Select("province",$province_arr,$province_id,"onchange=\"changeProvince();\"");

		if($province_id){
			if($city){
				$hid_city = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$city' and upid = '$province_id'");			}
			if($hid_city){
				if($area){
					$hid_area = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$area' and upid = '$hid_city'");				}
				if($hid_area){
					if($street){
						$hid_street = DB::result_first("select id from ".TABLE_PREFIX."common_district where name = '$street' and upid = '$hid_area'");					}
				}
			}
		}

		$action = 'admin.php?mod=member&code=domodify';
		include template('admin/member_info');
	}

	
	function doForceOut(){
		$uid = (int) ($this->Get['uid']);
		if($uid < 1)
		{
			$this->Messager("请指定一个正确的UID");
		}
		load::logic('topic_manage');
		$TopicManageLogic = new TopicManageLogic();
		$TopicManageLogic->doUserFree($uid);

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set role_id = 3 where uid = '$uid'");
		$this->Messager("解除封杀成功");
	}

	
	function DoModify()
	{
		$_update = false;
		$op = jget('op');
		$uid = (int) ($this->Post['uid']);

		if($uid < 1) {
			$this->Messager("请指定一个正确的UID");
		}
        $this->ID = $uid;

		$member_info = jsg_member_info($uid);
		if(!$member_info) {
			$this->Messager("您要编辑的用户已经不存在了");
		}

				if(!admin_check_allow($uid)) {
			$this->Messager("为安全起见，您没有编辑 <b>{$member_info['nickname']}</b> 用户信息的权限，请使用网站创始人的身份登录后再进行编辑操作。", '', 10);
		}

		if(($this->Post['role_id'] == 4 || $this->Post['role_id'] == 118) && !trim($this->Post['cause'])){
			$this->Messager("请输入封杀理由",-1);
		}

		$password = $op ? '' : get_param('password');
		if($password=='') {
			unset($this->Post['password']);
		} else {
			$this->Post['password_unhash'] = $password;
			$this->Post['password']=jsg_member_password($password, $member_info['salt']);
			$_update = true;
		}


		$rets = array(
			'0' => '【注册失败】有可能是站点关闭了注册功能',
			'-1' => '不合法',
			'-2' => '不允许注册',
			'-3' => '已经存在了',
			'-4' => 'Email 不合法，请输入正确的Email地址。',
			'-5' => 'Email 不允许注册，请尝试更换一个。',
			'-6' => 'Email 已经存在了，请尝试更换一个。',
		);

		$nickname = $op ? $member_info['nickname'] : get_param('nickname');
		if($nickname!=$member_info['nickname'])
		{
			$ret = jsg_member_checkname($nickname, 1, 0, $uid);
			if($ret < 1)
			{
				$this->Messager("帐户/昵称 " . $rets[$ret]);
			}
			unset($this->Post['nickname']); 			$_update = true;
		}
		$username = $op ? $member_info['username'] : get_param('username');
		if($username!=$member_info['username'])
		{
			$ret = jsg_member_checkname($username, 0, 0, $uid);
			if($ret < 1)
			{
				$this->Messager("个性域名/微博地址 " . $rets[$ret]);
			}
			unset($this->Post['username']);
			$_update = true;
		}
		$email_update = false;
		$email = $op ? $member_info['email'] : get_param('email');
		if($email != $member_info['email'])
		{
			$ret = jsg_member_checkemail($email);
			if($ret < 1)
			{
				$this->Messager($rets[$ret]);
			}
			unset($this->Post['email']);
			$_update = true;
		}

				$this->Post['role_id'] = (int) $this->Post['role_id'];
		if ($this->Post['role_id'] > 0) {
			$role = DB::fetch_first("SELECT * FROM ".DB::table('role')." WHERE `id`='{$this->Post['role_id']}'");
			if($role) {
								if(!admin_check_allow($this->Post['role_id'], 1) || (true === jsg_member_is_founder($uid) && 'admin' != $role['type'])) {
					unset($this->Post['role_id'], $this->Post['role_type']);
				} else {
					$this->Post['role_type'] = $role['type'];
				}
				$login_enable = jconf::get('login_enable');
				if(!$role['privilege'] || $role['privilege'] == '') {
					$login_enable[$uid] = $uid;
				}else{
					unset($login_enable[$uid]);
				}
				jconf::set('login_enable',$login_enable);
			} else {
				$this->messager("角色已经不存在");
			}
		} else {
			unset($this->Post['role_id'], $this->Post['role_type']);
		}

				if($op){
			$phone = $this->Post['phone'] = $this->Post['mobile'];
		}else{
			$phone = $this->Post['mobile'] = $this->Post['phone'];
		}
		if($phone) {
			if(!jsg_is_mobile($phone)) {
				$this->Messager("手机号 $phone 格式不对哦，请重新输入正确的号码。");
			} else {
				if(($member_phone_info = jtable('members')->info(array('phone' => $phone))) && $uid != $member_phone_info['uid']) {
					$this->Messager("手机号 $phone 已经被使用在了 【{$member_phone_info['uid']}】{$member_phone_info['nickname']} 帐户上，请返回重新输入！");
				}
			}
		} else {
			$phone = '';
		}
				if(sms_init()) {
			if($phone) {
				sms_bind($uid, $phone);
			} else {
				sms_unbind($uid);
			}
		}

		if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable'] && !$op){
						$this->Post['companyid'] = max(0,(int)$this->Post['companyid']);
			if($this->Post['companyid'] == $member_info['companyid']){
				unset($this->Post['companyid']);
			}else{
				if($member_info['companyid'] == 0 && $this->Post['companyid'] > 0){
					$this->CpLogic->update('company',$this->Post['companyid'],1,$member_info['topic_count']);
				}elseif($member_info['companyid'] > 0 && $this->Post['companyid'] == 0){
					$this->CpLogic->update('company',$member_info['companyid'],-1,-$member_info['topic_count']);
				}else{
					$this->CpLogic->update('company',$member_info['companyid'],-1,-$member_info['topic_count']);
					$this->CpLogic->update('company',$this->Post['companyid'],1,$member_info['topic_count']);
				}
				$this->Post['company'] = $this->CpLogic->Getone($this->Post['companyid'],'company','name');
			}
			if ($this->Config['department_enable']){
				$this->Post['departmentid'] = max(0,(int)$this->Post['departmentid']);
				if($this->Post['departmentid'] == $member_info['departmentid']){
					unset($this->Post['departmentid']);
				}else{
					if($member_info['departmentid'] == 0 && $this->Post['departmentid'] > 0){
						$this->CpLogic->update('department',$this->Post['departmentid'],1,$member_info['topic_count']);
					}elseif($member_info['departmentid'] > 0 && $this->Post['departmentid'] == 0){
						$this->CpLogic->update('department',$member_info['departmentid'],-1,-$member_info['topic_count']);
					}else{
						$this->CpLogic->update('department',$member_info['departmentid'],-1,-$member_info['topic_count']);
						$this->CpLogic->update('department',$this->Post['departmentid'],1,$member_info['topic_count']);
					}
					$this->Post['department'] = $this->CpLogic->Getone($this->Post['departmentid'],'department','name');
				}
			}

			if($this->Post['jobid'] == $member_info['jobid']){
				unset($this->Post['jobid']);
			}else{
				$this->Post['job'] = jlogic('job')->id2subject($this->Post['jobid']);
			}

			if($this->Post['companyid']){				$chx_cid = $this->Post['companyid'];
				$chx_uid = $member_info['uid'];
				$chx_did = $this->Post['departmentid'] ? $this->Post['departmentid'] : 0;
				$is_cp_usered = DB::fetch_first("SELECT * FROM ".DB::table('cp_user')." WHERE uid = '$chx_uid' AND companyid = '$chx_cid' AND departmentid = '$chx_did'");
				if($is_cp_usered){
					DB::query("DELETE FROM ".DB::table('cp_user')." WHERE id = '".$is_cp_usered['id']."'");				}
			}

		}
		if(!$op){
			$this->Post['province'] = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['province']."'")); 			$this->Post['city'] = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['city']."'"));			if($this->Post['area']){
				$this->Post['area'] = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['area']."'"));			}
			if($this->Post['street']){
				$this->Post['street'] = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['street']."'"));			}
		}
		$table1 = jtable('members')->update($this->Post, $uid);

		$memberfields = array(
			'site' => $this->Post['site'],
			'validate_true_name' => $this->Post['validate_true_name'],
			'validate_card_type' => $this->Post['validate_card_type'],
			'validate_card_id' => $this->Post['validate_card_id'],
			'validate_remark' => $this->Post['validate_remark'],
		);
		$table2 = jtable('memberfields')->update($memberfields, $uid);

		#附表2信息(members_profile)
		$arr2 = array();
		$profileField = array('constellation','zodiac','telephone','address','zipcode','nationality','education','birthcity','graduateschool','pcompany','occupation'
								 ,'position','revenue','affectivestatus','lookingfor','bloodtype','height','weight','alipay','icq','yahoo','taobao','site','interest'
								 ,'linkaddress','field1','field2','field3','field4','field5','field6','field7','field8','mobile');
		foreach ($profileField as $k => $v) {
			isset($this->Post[$v]) && $arr2[$v] = trim(strip_tags($this->Post[$v]));
		}
		if($arr2){
			$this->_updateMemberProfile($arr2, $uid);
		}
		$email_checked = $this->Post['email_checked'] ? $this->Post['email_checked'] : 0;
		if($email_checked != $member_info['email_checked']){
			$_update = true;
		}


				if($_update)
		{
			$ret = jsg_member_edit($member_info['nickname'], '', $nickname, $this->Post['password_unhash'], $email, $username, 1, 1, $email_checked);
			$rets = array(
				'0' => '没有做任何修改',
				'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
				'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
				'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
				'-4' => 'Email 不合法，请输入正确的Email地址。',
				'-5' => 'Email 不允许注册，请尝试更换一个。',
				'-6' => 'Email 已经存在了，请尝试更换一个。',
						);
			if($ret < 1 && isset($rets[$ret]))
			{
				$this->Messager($rets[$ret]);
			}
		}

			load::logic('topic_manage');
			$TopicManageLogic = new TopicManageLogic();

			$role_id = get_param('role_id');
			$cause = get_param('cause');

			if($role_id == 4 || $role_id == 118){
				$TopicManageLogic->doForceOut((array)$nickname,$cause,$role_id);
			}elseif(($role_id != 4 && $role_id != 118) && ($member_info['role_id']==4 ||$member_info['role_id']==118)){
				$TopicManageLogic->doUserFree($uid);
			}

			if ($this->Config['extcredits_enable'] && $this->Post['validate'] && $this->Post['uid']>0)
			{
				
				update_credits_by_action('vip',$this->Post['uid']);
			}


			Load::logic('credits');
			$CreditsLogic = new CreditsLogic();
			$CreditsLogic->CountCredits($this->Post['uid']);
			
						$data = array('uid' => $uid, 'rid'=>0, 'relatedid'=>MEMBER_ID, 'dateline'=>time());
			foreach ($GLOBALS['_J']['config']['credits']['ext'] as $key => $value) {
				$data[$key] = $_POST[$key] - $member_info[$key];
				$remark .= $value['name'].' ： '.$_POST[$key.'_remark'].' , ';
			}
			$data['remark'] = trim($remark,', ');
			jtable('credits_log')->insert($data);

			$this->Messager("编辑成功");
		}

	function _updateMemberProfile($arr2, $uid = 0){
		return jlogic('member_profile')->set_member_profile_info($uid, $arr2);
		
	}

	

	function Follow_User_Recommend()
	{
		$this->Title="设置关注用户";

		$button_title = '添加';

		$action="admin.php?mod=member&code=do_follow_user_recommend";


		$per_page_num = min(500, max(10, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));

		if($_GET['pn']) $pn = '&pn='.$_GET['pn'];


		$query_link = 'admin.php?mod=member&code=follow_user_recommend'.$pn;

				$_follow_info = jconf::get('follow_user_recommend');


		if(!$this->Get['type'] || $this->Get['type'] == 'recommend'){
			$type = 'recommend';
			$uids = explode(',',$_follow_info['recommend_uid']);
			$where = " `uid`  in ('".implode("','",$uids)."')  ";
		} else{
			$type = 'default';
			$uids = explode(',',$_follow_info['default_uid']);
			$where = " `uid`  in ('".implode("','",$uids)."')  ";
		}

		$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members`  where {$where} ";
		$total_record = DB::result_first($sql);

		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'10 20 50 100 200,500');


		
		$sql = "select `uid`,`nickname`,`username` from `".TABLE_PREFIX."members` where  {$where} order by `uid` {$page_arr['limit']} ";
		$query = $this->DatabaseHandler->Query($sql);
		$members = array();
		while (false != ($row = $query->GetRow())) {
			$members[] = $row;
		}

		include template('admin/follow_user_recommend');
	}

	function Do_Follow_User_Recommend()
	{

		$this->Title="设置关注用户";

		$nickname = trim($this->Post['nickname']);
		$follow_type = $this->Post['follow_type'];


				$sql = "select `uid` from `".TABLE_PREFIX."members`  where  `nickname` = '{$nickname}'";
		$query = $this->DatabaseHandler->Query($sql);
		$member = $query->GetRow();

		if(empty($member))
		{
			$this->Messager("用户 {$nickname} 不存在",-1);
		}

				if($follow_type == 'recommend'){
			$recommend_uids = $member['uid'];
		} else {
			$default_uids = $member['uid'];
		}

				$_follow_info = jconf::get('follow_user_recommend');

				$follow_uid_recommend = array_unique(explode(',',$_follow_info['recommend_uid'].','.$recommend_uids));
		$follow_uid_recommend = array_filter($follow_uid_recommend);

				$follow_uid_default = array_unique(explode(',',$_follow_info['default_uid'].','.$default_uids));
		$follow_uid_default = array_filter($follow_uid_default);

						$set['recommend_uid']	=	implode(',',$follow_uid_recommend);

				$set['default_uid']		=	implode(',',$follow_uid_default);

		$set = jstripslashes($set);
		jconf::set('follow_user_recommend',$set);


		$this->Messager("编辑成功");

	}

		function Del_Follow_User_Recommend()
	{
				$type = $this->Post['type'];

		$ids = (array) ($this->Post['ids'] ? $this->Post['ids'] : $this->Get['ids']);


				$_follow_info = jconf::get('follow_user_recommend');


		if($type == 'recommend')
		{
			$recommend_uids = $_follow_info['recommend_uid'].',';

			for ($i = 0; $i < count($ids); $i++)
			{
				$recommend_uids = str_replace($ids[$i].',','',$recommend_uids);
			}

		}

		if($type == 'default')
		{
			$default_uids = $_follow_info['default_uid'].',';

			for ($i = 0; $i < count($ids); $i++)
			{
				$default_uids = str_replace($ids[$i].',','',$default_uids);
			}

		}

				$follow_uid_recommend = explode(',',$recommend_uids ? $recommend_uids : $_follow_info['recommend_uid']);
		$follow_uid_recommend = array_filter($follow_uid_recommend);


				$follow_uid_default = array_unique(explode(',',$default_uids ? $default_uids : $_follow_info['default_uid']));
		$follow_uid_default = array_filter($follow_uid_default);

		
				$set['recommend_uid']	=	implode(',',$follow_uid_recommend);

				$set['default_uid']		=	implode(',',$follow_uid_default);

		$set = jstripslashes($set);
		jconf::set('follow_user_recommend',$set);


		$this->Messager("取消成功");
	}


	function ExportAllUser()
	{
		$limit = '';
		$per_page_num = 3000;
		$start = (int) get_param('start');
		if($start < 1) {
			$total_record = (int) get_param('total_record');
			if($total_record < 1) {
				$total_record = DB::result_first("select count(1) as total_record from ".DB::table('members'));
			}
			$html = '';
			if($total_record > $per_page_num) {
				$j = 1;
				$html .= "由于用户数据较多，请点击以下链接进行分批导出：<br />";
				for ($i=1; $i<=$total_record; $i=$i+$per_page_num) {
					$html .= "<a target='_blank' href='admin.php?mod=member&code=export_all_user&total_record={$total_record}&start={$i}'>{$j}、点此导出第 {$j} 批用户({$i} ~ ".($i + $per_page_num).")</a><br />";
					$j = $j+1;
				}

				$this->Messager($html, null);
			}
		}
		$limit = " limit ".(max(0, (int) ($start - 1))).", $per_page_num ";

		$query = $this->DatabaseHandler->Query("select M.`uid`, M.`username`, M.`nickname`, M.`email`, M.`phone`, M.`gender`, M.`credits`, M.`province`, M.`city`, M.`regdate`, M.`regip`, M.`lastip`, M.`aboutme`, M.`validate`, MF.`validate_remark`, MF.`validate_true_name`, MF.`validate_card_type`, MF.`validate_card_id` from ".TABLE_PREFIX."members M left join ".TABLE_PREFIX."memberfields MF on MF.`uid`=M.`uid` order by M.uid ASC {$limit} ");
		$list = array();
		$list[] = array('用户ID', '个性URL', '用户昵称', 'Email 邮箱', '手机号码', '性别', '用户积分', '省份', '城市', '注册时间', '注册IP', '最后登录IP', '一句话介绍', 'V身份认证', 'V认证备注', '真实姓名', '证件类型', '证件号码');
		$genders = array('1'=>'男', '2'=>'女');
		while(false != ($row = $query->GetRow()))
		{
			$row['regdate'] = my_date_format($row['regdate']);
			$row['gender'] = isset($genders[$row['gender']]) ? $genders[$row['gender']] : '未知';
			$row['validate'] = $row['validate'] ? "是" : "否";

			$list[] = $row;
		}


		$this->_excel($list, "all-user({$start}-".($start + $per_page_num).")-".date("YmdH"));

	}


	function SendPm() {
		if(!$this->IDS) {
			$this->Messager('请选择要发送私信的对象', -1);
		}

		$pm_content = $this->Post['pm_content'];
		if(!$pm_content) {
			$this->Messager('私信内容不能为空', -1);
		}

		$nickname_arr = array();
		$sql = "select `nickname` from `".TABLE_PREFIX."members` where `uid` in ('".implode("','", $this->IDS)."')";
		$query = DB::query($sql);
		while(false != ($row = DB::fetch($query))) {
			$nickname_arr[] = $row['nickname'];
		}

		load::logic("pm");
		$PmLogic = new PmLogic();

		$post = array();
		if($nickname_arr){
			$post['to_user'] = implode(",",$nickname_arr);
			$post['message'] = $pm_content;
			$PmLogic->pmSend($post);
		}

		$this->Messager('发送成功');
	}


	function _excel($list, $filename = '')
	{
		if(!$filename)
		{
			$filename = date('YmdHis');
		}

		
		jext('php-excel');
		$XLS = new Excel_XML($this->Config['charset']);
				$XLS->addArray($list);
		$XLS->generateXML($filename);
	}

	function user_login_log()
	{
		$nickname = $this->Get['nickname'];
		$time     = strtotime($this->Get['time']) ? $this->Get['time']: '';
		$ip       = $this->Get['ip'] ? $this->Get['ip']: '';
		$uid	  = jget('uid', 'int');

		if ($nickname && ($member_info = jsg_member_info($nickname, 'nickname'))) {
			$uid = $member_info['uid'];
		}
		if($uid > 0) {
			$sql['uid'] = $uid;
		}
		if ($time) {
			$sql['time'] = $time;
		}
		if ($ip) {
			$sql['ip'] = $ip;
		}

		$sql['sql_order'] = ' `id` DESC ';
		$sql['page_num']  = 20;

		$data = jtable('login_log')->get($sql);
		foreach ($data['list'] as $key => $value) {
			$data['list'][$key]['time'] = my_date_format($value['dateline'], "Y-m-d H:i:s");
		}

		include template('admin/user_login_log');
	}

	function cp_user(){
		$do = jget('do');
		$id = jget('id');
		$uid = jget('uid');
		if($uid > 0){
			$userinfo = $this->CpLogic->get_cp_user($uid);
		}
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable'] && $userinfo){
			if(in_array($do,array('add','mod'))){
				$this->Title = '编辑用户';
				$nickname = $userinfo['nickname'];
				if($do == 'add'){
					$action = 'admin.php?mod=member&code=cpuser&do=added&uid='.$uid;
				}elseif($do == 'mod' && $id > 0){
					$cpinfo = $this->CpLogic->get_cp_row($id);
					$action = 'admin.php?mod=member&code=cpuser&do=moded&id='.$id.'&uid='.$uid;
				}
				if ($this->Config['company_enable']){
					$companyselect = $this->CpLogic->get_cp_html($cpinfo['companyid']);
					if ($this->Config['department_enable']){
						$departmentselect = $this->CpLogic->get_cp_html($cpinfo['departmentid'],'department',$cpinfo['companyid']);
					}
					$jobselect = jlogic('job')->get_job_select($cpinfo['jobid']);
				}
				include template('admin/member_cp_add');
			}elseif(in_array($do,array('added','moded'))){
				$data = array('uid' => $uid);
				if($this->Post['companyid']){
					$data['companyid'] = (int)$this->Post['companyid'];					$data['company'] = $this->CpLogic->Getone($data['companyid'],'company','name');
				}
				if($this->Config['department_enable'] && $this->Post['departmentid']){
					$data['departmentid'] = (int)$this->Post['departmentid'];					$data['department'] = $this->CpLogic->Getone($data['departmentid'],'department','name');
				}
				if($this->Post['jobid']){
					$data['jobid'] = (int)$this->Post['jobid'];					$data['job'] = jlogic('job')->id2subject($data['jobid']);
				}
				if($do == 'added'){
					$this->CpLogic->add_cp_user($data);
				}elseif($do == 'moded' && $id > 0){
					$this->CpLogic->mod_cp_user($id,$data);
				}
				$this->Messager("操作成功",'admin.php?mod=member&code=modify&id='.$uid);
			}elseif($do == 'set' && $id > 0){
				$this->CpLogic->set_cp_user($id,$uid);
				$this->Messager("操作成功",'admin.php?mod=member&code=modify&id='.$uid);
			}elseif($do == 'del' && $id > 0){
				$cpinfo = $this->CpLogic->get_cp_row($id);
				if($cpinfo && $cpinfo['uid'] == $uid){
					$this->CpLogic->del_cp_user($id);
				}
				$this->Messager("操作成功",'admin.php?mod=member&code=modify&id='.$uid);
			}
		}else{
			$this->Messager("无法进行该操作");
		}
	}
}

?>