<?php
/**
 * 文件名：setting.mod.php
 * @version $Id: settings.mod.php 5637 2014-03-10 08:57:11Z chenxianfeng $
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

	var $CpLogic;

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		$this->TopicLogic = jlogic('topic');

				if ($this->Config['company_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$this->CpLogic = jlogic('cp');
		}

		$this->ID = jget('id', 'int');

		$this->Member = $this->_member();


		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'editEmail':
				$this->editAndCheckEmail();
				break;
			case 'setvest':
				$this->setVest();
				break;
			case 'vestcancel':
				$this->vestCancel();
				break;
			case 'setSendEmail':
				$this->SetSendEmail();
				break;
			case 'email_check':
				$this->doEmailCheck();
				break;
			case 'do_modify_password':
				$this->DoModifyPassword();
				break;
			case 'do_modify_face':
				$this->DoModifyFace();
				break;
			case 'do_modify_profile':
				$this->DoModifyProfile();
				break;
			case 'user_share':
				$this->DoUserShare();
				break;
			case 'invite_by_email':
				$this->InviteByEmail();
				break;
			case 'modify_email':
				$this->DoModifyEmail();
				break;
			case 'do_validate_extra':
				$this->DoValidateExtra();
				break;
            case 'profile':
                $this->profileImage();
                break;
            case 'doprofile':
                $this->doProfileImage();
                break;
            case 'cutprofile':
                $this->cutProfile();
                break;

			default:
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

	
	function editAndCheckEmail(){
		$uid = MEMBER_ID;
		if($uid < 1){
			$this->Messager('请登录','index.php');
		}
		$email = jget('email','email');
		if(!$email){
			$this->Messager('请输入邮箱的Email地址','index.php?mod=settings&code=sendmail');
		}

		$member = DB::fetch_first("SELECT `uid`,`ucuid`,`nickname`,`username`,`email`,`role_id`,`email_checked` from `".TABLE_PREFIX."members` where `uid` = '{$uid}'  LIMIT 0,1");

		if(!$member){
			$this->Messager('请登录','index.php');
		}

		
		$jsg_result = jsg_member_checkemail($email, $members['ucuid']);

		if($jsg_result < 1)
		{
			$rets = array(
	        	'0' => '【注册失败】有可能是站点关闭了注册功能',
	        	'-4' => 'Email 不合法，请输入正确的Email地址。',
	        	'-5' => 'Email 不允许注册，请尝试更换一个。',
	        	'-6' => 'Email 已经存在了，请尝试更换一个。',
	        );
	        $this->Messager($rets[$jsg_result],'index.php?mod=settings&code=sendmail');
		}

		jfunc('my');
		$ret = my_member_validate(MEMBER_ID,$email,$member['role_id'],1);

		if($ret){
						if($members['email_checked'] == 0){
				$sql = "update `".TABLE_PREFIX."members` set  `email`='{$email}' where `uid`='{$uid}'";
			}else{
				$sql = "update `".TABLE_PREFIX."members` set  `email2`='{$email}' where `uid`='{$uid}'";
			}
			$this->DatabaseHandler->Query($sql);
			$this->Messager('发送成功，请到你填写的邮箱中确认。','index.php?mod=settings&code=sendmail');
		} else {
			$this->Messager('发送确认邮件失败，请填写有效的邮箱地址或联系管理员。','index.php?mod=settings&code=sendmail');
		}
	}

	
	function setVest(){
		if(MEMBER_ID < 1){
			$this->Messager('请登录。。。','index.php');
		}
		if(!$this->Config['vest_enable']){
			$this->Messager('未开启马甲功能',-1);
		}
		$member = jsg_member_info(MEMBER_ID);
		if($this->Config['vest_role'] && false == jsg_find($this->Config['vest_role'], $member['role_id'])) {
			$this->Messager('你所在的用户组没有添加马甲的权限', -1);
		}

		$username = jget('username');
		$password = jget('password');

		$ret = jsg_member_login_check($username,$password);

		if($ret['uid'] == MEMBER_ID){
			$this->Messager('不能将自己添加添加为马甲。',-1);
		}
		if($ret['uid'] < 1){
			$msg_arr = array('0'=>'未知错误' ,
							 '-1' => '用户不存在',
							 '-2' => '密码错误',
							 '-3' => 'IP错误');
			$this->Messager($msg_arr[$ret['uid']],-1);
		}
		$return = jlogic('member_vest')->setVest($ret['uid'],MEMBER_ID);
		$return_arr = array('0'=>'设置成功','1'=>'用户不存在','2'=>'添加失败：其他人已添加此帐户作为马甲');
		if($return){
			$this->Messager($return_arr[$return],-1);
		}
		$this->Messager($return_arr[$return],'index.php?mod=settings&code=vest');
	}

	
	function vestCancel(){
		if(MEMBER_ID < 1){
			$this->Messager('请登录。。。','index.php');
		}
		if(!$this->Config['vest_enable']){
			$this->Messager('未开启马甲功能',-1);
		}
		$uid = jget('uid','int');
		if($uid < 1){
			$this->Messager('请选择要取消的马甲',-1);
		}

		$return = jlogic('member_vest')->cancelVest($uid,MEMBER_ID);
		$ret_arr = array(1=>'用户不存在'
						,2=>'不能取消自己'
						,3=>'没有权利取消马甲');
		if ($return) {
			$this->Messager($ret_arr[$return],-1);
		} else {
			$this->Messager('取消马甲成功','index.php?mod=settings&code=vest');
		}
	}

	
	function SetSendEmail(){
		$uid = MEMBER_ID;
		if($uid < 1){
			$this->Messager('请先登录','index.php?');
		}

		if(!$this->Config['sendmailday']){
			$this->Messager('本站点尚未开启邮件提醒功能。',-1);
		}

		$sendmail = jpost('sendmail');

		$acceptemail = array('notice_pm'
							,'notice_reply'
							,'notice_at'
							,'notice_fans'
							,'notice_event'
							,'user_notice_time');
		$data = array();
		foreach ($acceptemail as $k) {
			$data[$k] = max(0, (int) $sendmail[$k]);
		}
		if($sendmail['notice_email']){
			$data['email_checked'] = 1;
		}else{
			$data['email_checked'] = 2;
		}

		jtable('members')->update($data, $uid);

		$this->Title = '邮件提醒';
		$this->Messager('设置成功','index.php?mod=settings&code=sendmail');
	}

	function doEmailCheck(){
		if(MEMBER_ID < 1){
			$this->Messager('请登录',-1);
		}

		jfunc('my');
		$member = jsg_member_info(MEMBER_ID);
		$checkemail = $member['email2'] ? $member['email2'] : $member['email'];
		$ret = my_member_validate(MEMBER_ID,$checkemail,$member['role_id'],1);

		if($ret){
			$this->Messager('发送成功，请关闭此页面，并到您填写的邮箱中进行确认。',null);
		} else {
			$this->Messager('发送确认邮件失败，请填写有效的邮箱地址或联系管理员。','index.php?mod=settings');
		}
	}

	function Main()
	{
		$member = jsg_member_info(MEMBER_ID);

        $_act_list = array('imjiqiren'=>1,'qqrobot'=>1,'sms'=>1,'sina'=>1,'qqwb'=>1,);
        if(isset($_act_list[$this->Code]))
        {
            $this->Messager(null,"index.php?mod=tools&code={$this->Code}");
        }
        if('email'==$this->Code)
        {
            $this->Messager(null,'index.php?mod=settings&code=base#modify_email_area');
        }

		$act_list = array('base'=>'我的资料',
						  'face'=>'我的头像',
						  'secret'=>'修改密码',
						  'user_tag'=>array('name'=>'我的标签','link_mod'=>'user_tag',),
						  'vip_intro'=>array('name'=>'申请V认证','link_mod'=>'other','link_code'=>'vip_intro'));

		if ($member['validate'] && $member['validate_extra'])
		{
			$act_list['validate_extra'] = '专题设置';
		}

		if($this->Config['sendmailday'] && $this->Config['sendmailday'] > 0){
			$act_list['sendmail'] = '邮件提醒';
		}

		if($this->Config['vest_enable']){
			$act_list['vest'] = '我的马甲';
		}

		$this->Code = $act = $this->Code ? $this->Code : 'base';

		$member_nickname = $member['nickname'];



		
		if('face' == $act)
		{
						if(true === UCENTER_FACE && true === UCENTER)
            {
			     include_once(ROOT_PATH . './api/uc_client/client.php');

								$uc_avatarflash = uc_avatar(MEMBER_UCUID,'avatar','returnhtml');

                $query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`='{$member['uid']}'");
                $_member_info = $query->GetRow();
                if($member['uid'] > 0 && MEMBER_UCUID > 0 && !($_member_info['face']))
                {
                    $uc_check_result = uc_check_avatar(MEMBER_UCUID);
                    if($uc_check_result)
                    {
                        $this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set `face`='./images/noavatar.gif' where `uid`='{$member['uid']}'");
                    }
                }
			}
			elseif(true === UCENTER_FACE && true === PWUCENTER)
            {
			    												$pwuc_avatarflash = true;
				$pwurl_setuserface = UC_API .'/profile.php?action=modify&info_type=face';
			}
            else
            {
                $temp_face = '';
                if($this->Get['temp_face'] && is_image($this->Get['temp_face']))
                {
                    $temp_face = $this->Get['temp_face'];

                    $member['face_original'] = $temp_face;
                }
            }
		}

		
		elseif('base' == $act)
		{
			$op = jget('op');
			$groupProfile = jconf::get('groupprofile');
			$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='{$member['uid']}'";
			$query = $this->DatabaseHandler->Query($sql);
			$memberfields = $query->GetRow();

			if(!$memberfields) {
				$memberfields = array();
				$memberfields['uid'] = $member['uid'];

				$sql = "insert into `".TABLE_PREFIX."memberfields` (`uid`) values ('{$member['uid']}')";
				$this->DatabaseHandler->Query($sql);
			}
			$privacy = array();
			if($memberfields['profile_set']){
				$privacy = unserialize($memberfields['profile_set']);
			}

			#附加信息
			$member_profile = DB::fetch_first("select * from `".TABLE_PREFIX."members_profile` where `uid` = '{$member['uid']}'");
			if($member_profile){
				if($member_profile['birthcity']){
					$birthcity = explode('-',$member_profile['birthcity']);
					$b_province = $birthcity[0];
					$b_city = $birthcity[1];
					$b_area = $birthcity[2];
					$b_street = $birthcity[3];
				}
				$member = array_merge($member_profile,$member);
			}

						$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
					$province_id = $rsdb['id'];
				}
			}

			$b_province_list = jform()->Select("b_province",$province,$b_province,"onchange=\"changeProvince('b');\"");

									if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
				if($member['companyid']){$canmod = false;}else{$canmod = true;}
				$companyselect = $this->CpLogic->get_cp_html($member['companyid'],'company',0,$canmod);
				if($this->Config['department_enable']){
					if($member['departmentid']){$danmod = false;}else{$danmod = true;}
					$departmentselect = $this->CpLogic->get_cp_html($member['departmentid'],'department',$member['companyid'],$danmod);
				}
				if($member['jobid']){$janmod = false;}else{$janmod = true;}
				$jobselect = jlogic('job')->get_job_select($member['jobid'],$janmod);
				$morcompanys = $this->CpLogic->get_cp_users();
			}

			$gender_radio = jform()->Radio('gender',array(1=>array('name'=>'男','value'=>1,),2=>array('name'=>'女','value'=>2,),),$member['gender']);
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
			$validate_card_type_select = jform()->Select('validate_card_type',$_options,$memberfields['validate_card_type']);

			$province_list = jform()->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");
			if($province_id){
				if($member['city']){
					$hid_city = DB::result_first("select `id` from ".TABLE_PREFIX."common_district where name = '$member[city]' and upid = '$province_id'");				}

				if($hid_city){
					if($member['area']){
						$hid_area = DB::result_first("select `id` from ".TABLE_PREFIX."common_district where name = '$member[area]' and upid = '$hid_city'");					}

					if($hid_area){
							if($member['street']){
							$hid_street = DB::result_first("select `id` from ".TABLE_PREFIX."common_district where name = '$member[street]' and upid = '$hid_area'");						}
					}
				}
			}
		}
		
		elseif('user_medal'==$act){

    		if($this->Config[sina_enable] && sina_weibo_init($this->Config))
    		{
    			$sina = sina_weibo_has_bind(MEMBER_ID);
    		}

    		if($this->Config[imjiqiren_enable] && imjiqiren_init($this->Config))
    		{
    			$imjiqiren = imjiqiren_has_bind(MEMBER_ID);
    		}
    		if($this->Config[sms_enable] && sms_init($this->Config))
    		{
    			$sms = sms_has_bind(MEMBER_ID);
    		}
			if($this->Config[qqwb_enable] && qqwb_init($this->Config))
			{
				$qqwb = qqwb_bind_icon(MEMBER_ID);
			}

			$sql = "select  MD.medal_img , MD.medal_name ,  UM.* from `".TABLE_PREFIX."user_medal` UM left join `".TABLE_PREFIX."medal` MD on UM.medalid=MD.id where UM.uid='".MEMBER_ID." ' ";
			$query = $this->DatabaseHandler->Query($sql);
			$medal_list = array();
            $medal_ids = array();
			while(false != ($row = $query->GetRow()))
			{
				$medal_list[] = $row;
                $medal_ids[$row['medalid']] = $row['medalid'];
			}

            $medal_ids_str = implode(",",$medal_ids);
            $_member = DB::fetch_first("select * from ".TABLE_PREFIX."members where `uid`='".MEMBER_ID."'");
            if($medal_ids_str != $_member['medal_id'])
            {
                $this->DatabaseHandler->Query("update ".TABLE_PREFIX."members set medal_id='$medal_ids_str' where `uid`='".MEMBER_ID."'");
            }
		}
		elseif('exp'==$act){


						$experience = jconf::get('experience');
			$exp_list = $experience['list'];

						$mylevel = $member['level'];

						$my_credits = $member['credits'];

						foreach ($exp_list as $v) {
				if($my_credits >= $v['start_credits'])
				{
					$my_level = $v['level'];
				}
			}

			if($mylevel !=  $my_level)
			{
								$sql = "update `" . TABLE_PREFIX . "members` set `level`='{$my_level}' where `uid`='".MEMBER_ID."'";
           		$this->DatabaseHandler->Query($sql);

           		$sql = "select `level` from `" . TABLE_PREFIX .
                "members` where `uid`='" . MEMBER_ID . "' ";
	            $query = $this->DatabaseHandler->Query($sql);
	            $members = $query->GetRow();

	            $member['level'] = $members['level'];


			}

						$my_level_begin_credits = $exp_list[$my_level]['start_credits'];


						$next_level = $my_level + 1;
			$next_level_begin_credits = $exp_list[$next_level]['start_credits'];


						$my_exp = $my_credits - $my_level_begin_credits;


						$nex_exp = $next_level_begin_credits - $my_level_begin_credits;

			
						$level_width_arr = array(

					'1' => '27',
					'2' => '31',
					'3' => '45',
					'4' => '51',
					'5' => '62',
					'6' => '68',
					'7' => '77',
					'8' => '82',
					'9' => '93',
					'10' => '107',

			);
						$level_width = $my_exp * $level_width_arr[$my_level] / $nex_exp;

						$exp_width_arr = array(

					'1'  => '15',
					'2'  => '41',
					'3'  => '72',
					'4'  => '116',
					'5'  => '166',
					'6'  => '229',
					'7'  => '296',
					'8'  => '372',
					'9'  => '451',
					'10' => '545',
			);

			$exp_width = 100*(($level_width + $exp_width_arr[$my_level])/569);

			$nex_exp_credit = $next_level_begin_credits - $my_credits;

		}
		
		elseif('validate_extra' == $act)
		{

									$sql = "select `validate_extra` from `".TABLE_PREFIX."memberfields` where `uid` = '".MEMBER_ID."' ";
	 	 	$query = $this->DatabaseHandler->Query($sql);
	  		$memberfields = $query->GetRow();
	  		$meb_fields = @unserialize($memberfields['validate_extra']);


	  			  		if($meb_fields['vote'])
	  		{
		  				  		$sql = "select * from `".TABLE_PREFIX."vote` where `uid` = '".MEMBER_ID."' order by `vid` desc limit 0,10 ";
		 	 	$query = $this->DatabaseHandler->Query($sql);
		  		$vote_list = array();
		  		while (false != ($row = $query->GetRow()))
		  		{
		  			$vote_list[] = $row;
		  		}
	  		}

	  		
	  		Load::logic('validate_extra');
			$this->ValidateExtraLogic = new ValidateExtraLogic();

			$uid = MEMBER_ID;
			$extra = $this->ValidateExtraLogic->get_info($uid);
			$id = $extra['id'];
			$data = $extra['data'];
		}

		
		elseif ('qqrobot' == $act)
		{
			if( empty($member['qq'])) {
				$qq_code = $member['uid']."j".md5($member['password'].$member['username']);
			}

		}

		
		elseif ('extcredits'==$act)
		{
			if (!$this->Config['extcredits_enable'])
			{
				$this->Messager("积分功能未启用",null);
			}
			$this->Title = '积分管理';
			$this->MetaKeywords = '积分兑换,积分管理,积分规则,积分详情';
			$this->MetaDescription = '积分兑换,积分管理,积分规则,积分详情';
			$top_credit_members = jlogic('mall')->get_top_member_credits();
			$feeds = jlogic('feed')->get_feed(5,"`action`='兑换了'");
			$config  = jconf::get('mall');
			$css['rule'] = ' class="current"';

			$credits_config = $this->Config['credits'];

			$_default_credits = array();
			$_enable = false;
			if(is_array($credits_config) && count($credits_config))
			{
				foreach ($credits_config['ext'] as $_k=>$_v)
				{
					if ($_v['enable'])
					{
						$_enable = true;

						if ($_v['default'])
						{
							$_default_credits[$_k]=$_v['default'];
						}
					}
				}
			}
			if (!$_enable)
			{
				$this->Messager("积分未设置",null);
			}

			$op = $this->Get['op'];
			$op_lists = array(
				'base'=>'我的积分',
								'detail'=>'积分详情',
				'rule'=>'积分规则',
			);
			$op = (isset($op_lists[$op]) ? $op : 'base');


			if ('base'==$op)
			{
				$_search = $_replace = array();
				for ($i=1;$i<=8;$i++)
				{
					$k = 'extcredits'.$i;
					$_search[$k] = '$member['.$k.']';
					$_replace[$k] = ' 0 ';
					if (isset($credits_config['ext'][$k]) && $credits_config['ext'][$k]['enable'])
					{
						$_replace[$k] = $credits_config['ext'][$k]['name'];
					}
				}
				$_search['topic_count'] = '$member[topic_count]';
				$_replace['topic_count'] = '发微博数量';

				$credits_config_formula = str_replace($_search,$_replace,$credits_config['formula']);

				;
			}
			elseif('log'==$op)
			{
				$query = $this->DatabaseHandler->Query("select R.rulename,R.action,R.related,RL.* from ".TABLE_PREFIX."credits_rule_log RL left join ".TABLE_PREFIX."credits_rule R on R.rid=RL.rid where RL.`uid`='".MEMBER_ID."'");
				$log_list = array();
				while ($row=$query->GetRow())
				{
					$log_list[$row['action']] = $row;
				}

				if ($_default_credits)
				{
					$log_list['default_credits'] = $_default_credits;
					$log_list['default_credits']['rulename'] = '注册时的初始积分';
					$log_list['default_credits']['total'] = $log_list['default_credits']['cyclenum'] = 1;
				}

				$_counts = array();
				foreach ($log_list as $k=>$row)
				{
					$row['dateline'] = ($row['dateline'] ? my_date_format($row['dateline'],'m-d H:i') : ' - ');

					foreach ($credits_config['ext'] as $_k=>$_v)
					{
						if(!in_array($k,array('attach_down','down_my_attach','convert','unconvert'))){							$row[$_k] = $row[$_k] * $row['total'];
						}
						$_counts[$_k] += $row[$_k];
					}

					if(strpos($row['action'],'_C')!==false || strpos($row['action'],'_D')!==false){						$row['related'] = jlogic('channel')->id2subject($row['related']);
					}

					$log_list[$k] = $row;
				}
			}
			elseif ('detail'==$op)
			{
				$uid = MEMBER_ID;

				$rule =  jconf::get('credits_rule');
				foreach ($rule as $key => $value) {
					$rule_id[$value['rid']] = $value['rulename'];
				}

				$credits_field = array();
				foreach ($GLOBALS['_J']['config']['credits']['ext'] as $key => $value) {
					$credits_field[] = $key;
				}

				$list = jtable('credits_log')->get(array('sql_where' => "uid = $uid", 'sql_order'=>'id desc','page_num'=>20));
				foreach ($list['list'] as $key => $value) {
					$log_list[$key]['rid'] = $value['rid'];
					$log_list[$key]['rulename'] = $rule_id[$value['rid']];
					$log_list[$key]['dateline'] =  $value['dateline'] ? my_date_format($value['dateline'],'m-d H:i') : ' - ';
					foreach ($credits_field as $k => $v) {
						$log_list[$key][$v] = $value[$v];
					}
					if (strpos($value['remark'],'[a]') && strpos($value['remark'],'发布') === 0) {
						$t = explode('[a]', $value['remark']);
						$t1 = $t[1];
						$t = parse_url($t[1]);
						$t = $t['query'];
						parse_str($t,$out);

												$log_list[$key]['remark'] ="发布微博【微博ID:<a href='{$t1}' target='_blank'>{$out[code]}</a>】";
						$log_list[$key]['detail_remark'] = "发布微博【微博ID:$out[code]】";
					}else{
						$log_list[$key]['remark'] = strlen($value['remark']) > 30 ? mb_substr($value['remark'], 0,30,$GLOBALS['_J']['charset']).'...' : $value['remark'];
						$log_list[$key]['detail_remark'] = $value['remark'];
					}
				}
			}
			elseif ('rule'==$op)
			{
				if(!($credits_rule = jconf::get('credits_rule')))
				{
					$sql = "select * from ".TABLE_PREFIX."credits_rule order by rid";
					$query = $this->DatabaseHandler->Query($sql);
					$credits_rule = array();
					while (false != ($row = $query->GetRow()))
					{
						$v = false;
						foreach ($credits_config['ext'] as $_k=>$_v)
						{
							if ($row[$_k])
							{
								$v = true;
								break;
							}
						}

						if($v)
						{
							foreach ($row as $k=>$v)
							{
								if (!$v)
								{
									unset($row[$k]);
								}
							}

							$credits_rule[$row['action']] = $row;
						}
					}
				}

				$_cycletypes = array
				(
					0 => '一次性',
					1 => '每天',
					2 => '整点',
					3 => '间隔分钟',
					4 => '不限周期',
				);
				if ($_default_credits)
				{
					$credits_rule['default_credits'] = $_default_credits;
					$credits_rule['default_credits']['rulename'] = '注册时的初始积分';
					$credits_rule['default_credits']['cycletype'] = 0;
					$credits_rule['default_credits']['rewardnum'] = 1;
				}
				$mall_enable = (int) jconf::get('mall', 'enable');
				foreach ($credits_rule as $k=>$v)
				{
					if('unconvert'==$k || 'convert'==$k && empty($mall_enable)){
						unset($credits_rule[$k]);
					}else{
						$v['cycletype'] = $_cycletypes[(int) $v['cycletype']];
						if (!$v['rewardnum']){
							$v['rewardnum'] = '不限次数';
						}
						$credits_rule[$k] = $v;
					}
				}

				;
			}
			else
			{
				$this->Messager("未定义的操作");
			}
		}


		
		elseif ('imjiqiren' == $act)
		{
            define('IN_IMJIQIREN_MOD',      true);

            include(ROOT_PATH . 'modules/imjiqiren.mod.php');
		}

        elseif('sms' == $act)
        {
            define('IN_SMS_MOD',      true);

            include(ROOT_PATH . 'modules/sms.mod.php');
        }

        
        elseif('qqwb' == $act)
        {
            if(!qqwb_init($this->Config))
            {
                $this->Messager('腾讯微博功能未启用，请联系管理员',null);
            }



            $qqwb = jconf::get('qqwb');

            $qqwb_bind_info = qqwb_bind_info(MEMBER_ID);

            if($qqwb_bind_info)
            {
                if($qqwb['is_synctopic_toweibo'])
                {
                    $synctoqq_radio = jform()->YesNoRadio('synctoqq',(int) $qqwb_bind_info['synctoqq']);
                }
            }

            ;
        }

		
		elseif ('sina' == $act)
		{
			$profile_bind_message = '';

			$xwb_start_file = ROOT_PATH . 'include/ext/xwb/sina.php';

			if (!is_file($xwb_start_file))
			{
				$profile_bind_message = '&#25554;&#20214;&#25991;&#20214;&#20002;&#22833;&#65292;&#26080;&#27861;&#21551;&#21160;&#65281;';
			}
			else
			{
				require($xwb_start_file);

				$profile_bind_message = '<a href="javascript:XWBcontrol.bind()">&#22914;&#26524;&#30475;&#19981;&#21040;&#26032;&#28010;&#24494;&#21338;&#32465;&#23450;&#35774;&#32622;&#31383;&#21475;&#65292;&#35831;&#28857;&#20987;&#36825;&#37324;&#21551;&#21160;&#12290;</a>';

				$GLOBALS['xwb_tips_type'] = 'bind';

				$profile_bind_message .= jsg_sina_footer();
			}

			;
		}
		elseif ('email' == $act)
		{
            ;
		}

		else if ('sendmail' == $act) {
			if(!$this->Config['sendmailday']){
				$this->Messager('本站点尚未开启邮件提醒功能。',-1);
			}
						$member['user_notice_time'] = $member['user_notice_time'] ? $member['user_notice_time'] : 3;
			$sendtime[$member['user_notice_time']] = ' selected ';
		}

		else if ('vest' == $act) {

			if(!$this->Config['vest_enable']){
				$this->Messager('本站点尚未开启马甲功能。',-1);
			}

			if($this->Config['vest_role'] && false == jsg_find($this->Config['vest_role'], $member['role_id'])) {
				$this->Messager('你所在的用户组没有添加马甲的权限', -1);
			}

			$vest = jlogic('member_vest')->get_member_vest(MEMBER_ID);
		}
		elseif('plugin' == $act){
			global $_J;
			$pluginid = jget('id');
			if(!empty($pluginid)) {
				list($identifier, $module) = explode(':', $pluginid);
				$module = $module !== NULL ? $module : $identifier;
			}
			$plugin = jlogic('plugin')->pluginmodule($pluginid);
			if($plugin[0]){
				include $plugin[2];
				$plugintemplate = $identifier.':'.$module;
				$act_list['plugin'] = $plugin[1];
			}else{
				$this->Messager($plugin[1]);
			}
		}

		if(empty($this->Title)) {
			$this->Title = $act_list[$act];
		}
		if('plugin' == $act){
			include(template('setting/plugin'));
		}elseif('extcredits' == $act){
			include(template('mall_rule'));
		}else{
			include(template('setting/setting_main'));
		}
	}

    
    function DoModifyFace() {
        
        if(MEMBER_ID < 1) {
           $this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
        }
        $pic_file = $this->Post['img_path'];
        if(empty($pic_file) || !is_image($pic_file)) {
        	$this->Messager('请上传正确的图片文件');
        }

        
        $p = array(
        	'uid' => MEMBER_ID,

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

        
        if($this->Config['face_verify']) {
	        $this->Messager("头像设置成功,管理员审核中...");
        } else {
        	$this->Messager("头像设置成功");
        }
    }

	function DoModifyPassword()
	{
		if ($this->Config['seccode_enable']>1 && $this->Config['seccode_password'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['add_YinXiangMa_challenge'],@$_POST['add_YXM_level'][0],@$_POST['add_YXM_input_result']);
			if($YinXiangMa_response != "true"){
				$this->Messager("验证码输入错误",-1);
			}
		}
		$arr = array();
		$resendEmail = false;

		$password_old = $this->Post['password_old'];
		$password_new1 = $this->Post['password_new1'];
		$password_new2 = $this->Post['password_new2'];

		

		if(!$password_new1)
		{
			$this->Messager("请输入新密码",-1);
		}
		if ($password_new1!=$password_new2) {
			$this->Messager("两次输入的密码不一致",-1);
		}
		if($password_new1 && $password_new1!=$password_old) {
			if(strlen($password_new1) < 5) {
				$this->Messager("为了您帐户的安全，请设置5位以上的密码",-1);
			}
		}


		$ret = jsg_member_edit($this->Member['nickname'], $password_old, '', $password_new1);
		if($ret < 1)
		{
			$rets = array(
				'0' => '没有做任何修改',
	        	'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
	        	'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
	        	'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
	        	'-4' => 'Email 不合法，请输入正确的Email地址。',
	        	'-5' => 'Email 不允许注册，请尝试更换一个。',
	        	'-6' => 'Email 已经存在了，请尝试更换一个。',
				'-7' => '没有做任何修改',
			);

			$message = $rets[$ret] ? $rets[$ret] : "密码输入错误，请重试";

			$this->Messager($message);
		}


		$message[] = "密码修改成功，需要重新登录";
		$this->Messager($message,'index.php',1);
	}

	function DoModifyProfile()
	{
		$op = jget('op');

		$member_info = DB::fetch_first("SELECT * FROM ".DB::table('members')." where `uid`='".MEMBER_ID."'");
		if(!$member_info)
		{
			$this->Messager('用户已经不存在了', null);
		}

		$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$memberfields = $query->GetRow();

		if($op){
			#主表信息(members)
			$arr = array();
			#性别
			$this->Post['gender'] && $arr['gender'] = (int) $this->Post['gender'];
			isset($this->Post['qq']) && $arr['qq'] = (($qq = is_numeric($this->Post['qq']) ? $this->Post['qq'] : 0) > 10000 && strlen((string) $qq) < 11) ? $qq : '';
			isset($this->Post['msn']) && $arr['msn'] = trim(strip_tags($this->Post['msn']));
			isset($this->Post['bday']) && $arr['bday'] = $this->Post['bday'];
			isset($this->Post['mobile']) && $arr['phone'] = trim($this->Post['mobile']);
			isset($this->Post['aboutme']) && $arr['aboutme'] = trim(strip_tags($this->Post['aboutme']));
			if($arr['phone']) {
				if(!jsg_is_mobile($arr['phone'])) {
					$this->Messager("手机号 $phone 格式不对哦，请重新输入正确的号码。");
				} else {
					if(($member_phone_info = jtable('members')->info(array('phone' => $arr['phone']))) && MEMBER_ID != $member_phone_info['uid']) {
						$this->Messager("手机号 ".$arr['phone']." 已经被其它用户使用，请返回重新输入！");
					}
				}
			}
			if($arr){
				$this->_update($arr);
			}
			#附表信息(memberfield的字段profile_set)
			$member_profile_set = array();
			if($memberfields['profile_set']){
				$member_profile_set = unserialize($memberfields['profile_set']);
			}
			$privacy = ($privacy=jget('privacy')) ? $privacy : array();
			$member_profile_set = array_merge($member_profile_set,$privacy);
			$this->_updateMemberField(array('profile_set'=>serialize($member_profile_set)));

			#附表2信息(members_profile)
			$arr2 = array();
			$profileField = array('constellation','zodiac','telephone','address','zipcode','nationality','education','birthcity','graduateschool','pcompany','occupation'
								 ,'position','revenue','affectivestatus','lookingfor','bloodtype','height','weight','alipay','icq','yahoo','taobao','site','interest'
								 ,'linkaddress','field1','field2','field3','field4','field5','field6','field7','field8','mobile');
			foreach ($profileField as $k => $v) {
				if($v == 'birthcity'){
					$this->Post['b_province'] && $birthcity['b_province'] = $this->Post['b_province'];
					$this->Post['b_city'] && $birthcity['b_city'] = $this->Post['b_city'];
					$this->Post['b_area'] && $birthcity['b_area'] = $this->Post['b_area'];
					$this->Post['b_street'] && $birthcity['b_street'] = $this->Post['b_street'];
					if($birthcity){
						$arr2[$v] = implode('-',$birthcity);
					}
				}else {
					isset($this->Post[$v]) && $arr2[$v] = trim(strip_tags($this->Post[$v]));
				}
			}
			if($arr2){
				$this->_updateMemberProfile($arr2);
			}
		} else {
	        foreach($this->Post as $key=>$val)
	        {
	            $key = strip_tags($key);
	            $val = strip_tags($val);

	            $this->Post[$key] = $val;
	        }

	        	        if($member_info['invite_uid'] < 1 && $this->Post['invite_nickname'] && $this->Config['register_invite_input2']) {
	        	$_invite_member = jsg_member_info($this->Post['invite_nickname'], 'nickname');
	        	if($_invite_member) {
	        		jsg_member_register_by_invite($_invite_member['uid'], $member_info['uid']);
	        	}
	        }


			$province = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['province']."'")); 			$city = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['city']."'"));			if($this->Post['area']){
				$area = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['area']."'"));			}
			if($this->Post['street']){
				$street = trim(DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['street']."'"));			}

			$gender = in_array(($gender = (int) $this->Post['gender']),array(1,2)) ? $gender : 0;
			$email2 = preg_match("~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i",($email2 = trim($this->Post['email2']))) ? $email2 : '';
			$qq = (($qq = is_numeric($this->Post['qq']) ? $this->Post['qq'] : 0) > 10000 && strlen((string) $qq) < 11) ? $qq : '';
			$msn = trim(strip_tags($this->Post['msn']));

						$aboutme = trim(strip_tags($this->Post['aboutme']));
						$f_rets = filter($aboutme);
			if($f_rets && $f_rets['error'])
	        {
	            $this->Messager($f_rets['msg'],null);
	        }

									$signature = trim(strip_tags($this->Post['signature']));
						$f_rets = filter($signature);
			if($f_rets && $f_rets['error'])
	        {
	            $this->Messager($f_rets['msg'],null);
	        }

			if (!$gender)
	        {
				$this->Messager("性别不能为空，请返回修改",-1);
			}

			#修改主表(members)
			$arr = array (
				'province' => addslashes($province),
				'city' => addslashes($city),
				'area' => addslashes($area),
				'street' => addslashes($street),
				'gender' => $gender,
								'aboutme' => addslashes($aboutme),
				'aboutmetime' => 0,
			);
						if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
				if($this->Post['companyid'] && $member_info['companyid'] ==0){
					$arr['companyid'] = (int)$this->Post['companyid'];					$arr['company'] = $this->CpLogic->Getone($arr['companyid'],'company','name');
					if($arr['companyid']>0){
						$this->CpLogic->update('company',$arr['companyid'],1,$member_info['topic_count']);
					}
				}
				if($this->Config['department_enable'] && $this->Post['departmentid'] && $member_info['departmentid'] ==0){
					$arr['departmentid'] = (int)$this->Post['departmentid'];					$arr['department'] = $this->CpLogic->Getone($arr['departmentid'],'department','name');
					if($arr['departmentid']>0){
						$this->CpLogic->update('department',$arr['departmentid'],1,$member_info['topic_count']);
					}
				}
				if($this->Post['jobid'] && $member_info['jobid'] ==0){
					$arr['jobid'] = (int)$this->Post['jobid'];					$arr['job'] = jlogic('job')->id2subject($arr['jobid']);
				}
			}
			$this->_update($arr);

			#修改附表(memberfiled)
			$arr1 = array();
			if (!$memberfields['validate_true_name'] && $this->Post['validate_true_name'])
	        {
				$arr1['validate_true_name'] = $this->Post['validate_true_name'];
			}
			if (!$memberfields['validate_card_type'] && $this->Post['validate_card_type'])
	        {
				$arr1['validate_card_type'] = $this->Post['validate_card_type'];
			}
			if (!$memberfields['validate_card_id'] && $this->Post['validate_card_id'])
	        {
				$arr1['validate_card_id'] = $this->Post['validate_card_id'];
			}
			if ($arr1)
	        {
				$sets = array();
				if (is_array($arr1))
	            {
					foreach ($arr1 as $key=>$val)
	                {
	                	$val = jfilter($val, 'txt');
						$val = addslashes($val);
						$sets[$key] = "`{$key}`='{$val}'";
					}
				}
				$sql = "update `".TABLE_PREFIX."memberfields` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";

				$this->DatabaseHandler->Query($sql);
			}
		}

		$this->Messager("修改成功",'',1);
	}

		function DoModifyEmail()
	{
		$password_old = $this->Post['password_old'];
		$email_new = $this->Post['email_new'];
		$nickname_new = $this->Post['nickname_new'];
		$username_new = '';
		if(!$this->Member['username'] || is_numeric($this->Member['username']))
		{
			$username_new = $this->Post['username_new'];
		}


		$ret = jsg_member_edit($this->Member['nickname'], $password_old, $nickname_new, '', $email_new, $username_new);
		if($ret < 1)
		{
			$rets = array(
				'0' => '没有做任何修改',
	        	'-1' => '帐户/昵称 不合法，含有不允许注册的字符，请尝试更换一个。',
	        	'-2' => '帐户/昵称 不允许注册，含有被保留的字符，请尝试更换一个。',
	        	'-3' => '帐户/昵称 已经存在了，请尝试更换一个。',
	        	'-4' => 'Email 不合法，请输入正确的Email地址。',
	        	'-5' => 'Email 不允许注册，请尝试更换一个。',
	        	'-6' => 'Email 已经存在了，请尝试更换一个。',
				'-7' => '没有做任何修改。',
				'-8' => '网站后台已设置了帐户昵称不允许修改，请联系网站管理员。',
			);

			$message = $rets[$ret] ? $rets[$ret] : "密码输入错误，请重试";

			$this->Messager($message);
		}

		jfunc('my');
		my_member_validate(MEMBER_ID,$email_new,$this->Member['role_id'],1);
		if($this->Config['reg_email_verify'] and $email_new != $this->Member['email'])
		{
			$message=array();
			$message[]="Email 重新激活验证的方法已经发送到注册邮箱 <b>".$email_new."</b>，请用邮件中提供的方法进行激活。";
			$message[]="如果24小时内仍没有收到系统发送的系统邮件，请在个人设置/修改密码页面中重新提交或尝试更换成其他的email地址";

			$this->Messager($message,null);
		}
		else
		{
			$this->Messager('确认 Email 已发送，请用邮件中提供的方法激活您的帐号。<br>如果您尚未收到我们发送的系统邮件，请点击个人资料中的“重新接收验证邮件”，或尝试更换另外一个地址','index.php?mod=settings&code=base');
		}
	}

		function DoValidateExtra()
	{
		Load::logic('validate_extra');
		$this->ValidateExtraLogic = new ValidateExtraLogic();


		
		$uid = MEMBER_ID;
		$extra = $this->ValidateExtraLogic->get_info($uid);
		$id = $extra['id'];
		$data = $extra['data'];

		if(empty($id))
		{
			$sql = "insert into `".TABLE_PREFIX."validate_extra` (`id`) values('".MEMBER_ID."')";
			$this->DatabaseHandler->Query($sql);
			$id = MEMBER_ID;
		}

				$this->Post['open_extra'] = ($this->Post['open_extra'] ? 1 : 0);
		$sql = "update `".TABLE_PREFIX."members` set `open_extra`='{$this->Post['open_extra']}' where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);

		if($this->Post['submit'])
		{
			$_data = $this->Post['data'];

			if($_data['right_top_image']['list'])
			{
				$rets = array();
				foreach($_data['right_top_image']['list'] as $v)
				{
					$v = trim($v);

					if($v)
					{
						$rets[] = $v;
					}
				}
				$_data['right_top_image']['list'] = $rets;
			}

			if($_data['validate_video']['list'])
			{
				$rs = array();
				$rets = array();
				foreach($_data['validate_video']['list'] as $v)
				{
					$v = trim($v);

					if($v)
					{
												$r = $this->_extra_video($v, $data);

						if($r)
						{
							$rs[] = $r;
							$rets[] = $v;
						}
					}
				}
				$_data['validate_video']['rlist'] = $_data['validate_video']['vlist'] = $rs;
				$_data['validate_video']['list'] = $rets;
			}

			if($_data['right_top_user']['list'])
			{
				$rs = array();
				$rets = array();
				foreach($_data['right_top_user']['list'] as $v)
				{
					$v = trim($v);

					if($v)
					{
												$r = $this->_extra_user($v);

						if($r)
						{
							$rs[] = $r;
							$rets[] = $v;
						}
					}
				}
				$_data['right_top_user']['rlist'] = $rs;
				$_data['right_top_user']['list'] = $rets;
			}

			$ret = $this->ValidateExtraLogic->modify($id, $_data);

			$this->Messager('编辑成功');
		}

	}


	function _extra_video($url, $data)
	{
		$vid = abs(crc32($url));

		if(NULL === ($ret = $data['right_top_video']['vlist'][$vid]))
		{
			$ret = $this->TopicLogic->_parse_video($url);
			if($ret)
			{
				$ret['vid'] = $vid;

				if($ret['image_src'])
				{
					;
				}
			}
		}

		return $ret;
	}
	function _extra_user($username)
	{
		$username = addslashes(trim($username));

		$rets = array();
		if($username)
		{
			$sql_where = " where `username`='{$username}' or `nickname`='{$username}' limit 1 ";
			$sql_fields = " `uid`,`ucuid`,`username`,`face_url`,`face`,`province`,`city`,`fans_count`,`topic_count`,`validate`,`gender`,`face`,`nickname` ";

			$rets = $this->TopicLogic->GetMember($sql_where, $sql_fields);
		}

		$ret = array();
		if($rets)
		{
			foreach($rets as $row)
			{
				if($row)
				{
					$ret = $row;
				}
			}
		}

		return $ret;
	}


	function _update($arr)
	{
		$sets = array();
		if (is_array($arr)) {
			foreach ($arr as $key=>$val) {
				$val = addslashes($val);
				$sets[$key] = "`{$key}`='{$val}'";
			}

			if ($sets) {
				$sql = "update `".TABLE_PREFIX."members` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);
			}
		}
	}

	
	function _updateMemberField($arr1){
		$sets = array();
		if (is_array($arr1))
            {
			foreach ($arr1 as $key=>$val)
                {
				$val = addslashes($val);
				$sets[$key] = "`{$key}`='{$val}'";
			}
		}
		if($sets){
			$sql = "update `".TABLE_PREFIX."memberfields` set ".implode(" , ",$sets)." where `uid`='".MEMBER_ID."'";
			$this->DatabaseHandler->Query($sql);
		}
	}
	function _updateMemberProfile($arr2){
		return jlogic('member_profile')->set_member_profile_info(MEMBER_ID, $arr2);
		
	}

	function _member()
	{
		if (MEMBER_ID < 1) {
			$this->Messager(null,$this->Config['site_url'] . "/index.php?mod=login");
		}

		$member = $this->TopicLogic->GetMember(MEMBER_ID);

		return $member;
	}

    
    public function profileImage(){
        $tpl = 'setting/profile_image';
        if($this->Get['home']){
            $is_home = TRUE;
            $tpl = 'setting/profile_image_home';
        }
        include(template($tpl));exit;    }



    public function doProfileImage() {
    	$member = $this->_member();
    	if(!$member) {
    		$this->Messager('您需要登录后才能继续操作', null);
    	}

        $image_path = RELATIVE_ROOT_PATH . 'images/temp/profile/' . face_path(MEMBER_ID);
		$image_name = MEMBER_ID . "_o.jpg";
		$image_file = $image_path . $image_name;
		
		if ($_FILES && $_FILES['image']['name'])
		{
            $temp_img_size = intval($_FILES['image']['size']/1024);
            if($temp_img_size >= 2048)
            {
                if($this->Get['home']){
                     echo '<script>parent.show_message("图片文件过大,2MB以内");parent.closeDialog("showuploadform");</script>';exit;
                }else{
                    $this->Messager('图片文件过大,2MB以内');
                }
            }


            $_ts = explode(".",$_FILES['image']['name']);
            $type = trim(strtolower(end($_ts)));
            if($type != 'gif' && $type != 'jpg' && $type != 'png' && $type != 'jpeg')
            {
                if($this->Get['home']){
                     echo '<script>parent.show_message("图片格式不对");parent.closeDialog("showuploadform");</script>';exit;
                }else{
                    $this->Messager('图片格式不对');
                }
            }

			if (!is_dir($image_path))
			{
				jio()->MakeDir($image_path);
			}


			jupload()->init($image_path,'image',true);

			jupload()->setNewName($image_name);
			$result=jupload()->doUpload();

			if($result)
			{
				$result = is_image($image_file);
			}

			if (!$result)
			{
				jio()->DeleteFile($image_file);
                if($this->Get['home']){
                     echo '<script>parent.show_message("图片上传失败");parent.closeDialog("showuploadform");</script>';exit;
                }else{
                    $this->Messager("[图片上载失败]".implode(" ",(array) jupload()->getError()),null);
                }
			}
			else
			{
                
               list($w,$h) = getimagesize($image_file);
               if($w > 960)
               {
                   $tow = 960;
                   $toh = round($tow * ($h / $w));

                   $result = makethumb($image_file,$image_file,$tow,$toh);

                   if(!$result)
                   {
                       jio()->DeleteFile($image_file);
                       if($this->Get['home']){
                            echo '<script>parent.show_message("缩略图片失败");parent.closeDialog("showuploadform");</script>';exit;
                       }else{
                           $this->Messager("缩略图片失败".implode(" ",(array) jupload()->getError()),null);
                       }
                   }
               }
               $image_file_src = $this->Config['site_url'].ltrim($image_file,".");

				$tpl = 'setting/profile_image';
                if($this->Get['home']){
                    $is_home = TRUE;
                    $tpl = 'setting/profile_image_home';
                }
                include(template($tpl));exit;
			}

        }else{
            if($this->Get['home']){
                echo 'parent.show_message("请上传图片！");parent.closeDialog("showuploadform");</script>';exit;
            }else{
                $this->Messager("请上传图片！".implode(" ",(array) jupload()->getError()),null);
            }
        }
    }

    
    public function cutProfile(){
        $w = (int)$this->Post['w'];
        $h = (int)$this->Post['h'];
        $x = (int)$this->Post['x'];
        $y = (int)$this->Post['y'];
        

                
        $image_path = RELATIVE_ROOT_PATH . 'images/temp/profile/' . face_path(MEMBER_ID);
		$image_name = MEMBER_ID . "_o.jpg";
		$image_file = $image_path . $image_name;
        if(!$image_file){
            if($this->Get['home']){
                echo '<script>parent.show_message("剪切错误！请重新上传图片");parent.closeDialog("showuploadform");</script>';exit;
            }else{
                $this->Messager("剪切错误！请重新上传图片");
            }
        }
        $member = $this->_member();

        $image_thumb_file = RELATIVE_ROOT_PATH . 'images/profile/' . face_path($member['uid'])."_o.jpg";
                if(!is_dir(($image_thumb_dir = dirname($image_thumb_file)))) {
        	jmkdir($image_thumb_dir);
        }


        list($w_src,$h_src) = getimagesize($image_file);
        if(!$w_src){
                        if($this->Get['home']){
                echo '<script>parent.show_message("剪切图片已不存在！");parent.closeDialog("showuploadform");</script>';exit;
            }else{
                $this->Messager("剪切图片已不存在",jurl('index.php?mod=settings&code=profile'));
            }
        }
                if((!$w||!$h)){
            if($w_src < 750){
                $w = $w_src;
                $h=(int)($w*(5/16));
            }else{
                $w = 750;
                $h=(int)($w*(5/16));
            }

        }
		if($w_src > 750)
		{
            $bili = $w_src/750;
			round($w*$bili);

            $result = makethumb($image_file,$image_thumb_file,round($w*$bili),round($h*$bili),0,0,round($x*$bili),round($y*$bili),round($w*$bili),round($h*$bili));
            if($result)
			{
				jio()->DeleteFile($image_file);
			}
        }else{
            $result = makethumb($image_file,$image_thumb_file,$w,$h,0,0,$x,$y,$w,$h);
            if($result)
			{
				jio()->DeleteFile($image_file);
			}
        }
        if($this->Config['ftp_on']) {
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];
			$ftp_result = ftpcmd('upload',$image_thumb_file,'',$ftp_key);
			if($ftp_result > 0) {
				jio()->DeleteFile($image_thumb_file);
				$image_thumb_file = $site_url .'/'. str_replace('./','',$image_thumb_file);
			}
		}
        if($result){
            $sql = "update `".TABLE_PREFIX."members` set profile_image='".$image_thumb_file."' where `uid`='".MEMBER_ID."'";
			$this->DatabaseHandler->Query($sql);
            if($this->Get['home']){
                echo '<script>parent.show_message("已保存！");parent.location.replace(parent.location.href);parent.closeDialog("showuploadform");</script>';exit;
            }else{
                $this->Messager("已保存",jurl('index.php?mod=settings&code=profile'));
            }
        }else{
            if($this->Get['home']){
                echo '<script>parent.show_message("保存失败！");parent.closeDialog("showuploadform");</script>';exit;
            }else{
                $this->Messager("保存失败",jurl('index.php?mod=settings&code=profile'));
            }
        }
    }
}
?>
