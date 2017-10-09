<?php
/**
 * 文件名：members.mod.php
 * @version $Id: member.mod.php 5673 2014-05-06 09:42:21Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：用户组操作模块
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	var $sms_register = null;

	var $CpLogic;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);


				if (@is_file(ROOT_PATH . 'include/logic/cp.logic.php') && $this->Config['company_enable']){
			$this->CpLogic = jlogic('cp');
		}


		if(MEMBER_ID > 0) {
			$this->IsAdmin = ('admin' == MEMBER_ROLE_TYPE);
		}

		$this->Execute();
	}

	
	function Execute()
	{

		ob_start();
		switch($this->Code)
		{
			case 'delete':
				$this->Delete();
				break;

                        case 'follow_channel':
                $this->followChannel();
                break;
                        case 'follow_member':
                $this->followMember();
                break;
                        case 'add_face':
                $this->addFace();
                break;
                        case 'member_profile':
                $this->memberProfile();
                break;
            case 'do_member_profile':
                $this->doMemberProfile();
                break;
                        case 'do_first_topic':
                $this->doFirstTopic();
                break;
			case 'do_step2':
				$this->DoStep2();
				break;
			case 'verify':
				$this->Verify();
				break;
			case 'doregister':
				$this->DoRegister();
				break;

		 				case 'setverify':
				$this->DoSetVerify();
				break;
						case 'check_modify_email':
				$this->DoModifyEmail();
				break;

			case 'face':
				$this->face();
				break;

			default:
				$this->Register();
				break;
		}
		$Contents=ob_get_clean();
		$this->ShowBody($Contents);
	}

	function verify()
	{
		$key=(string) trim($this->Get['key']);
		$uid=(int) $this->Get['uid'];
		if (empty($key)) $this->Messager("验证字符串不能为空",null);
		if(strlen($key)!=16)$this->Messager("验证字符长度不符合标准，请检查。",null);

		$p = array('uid'=>$uid, 'key'=>$key);
		$row = jtable('member_validate')->info($p);
		if ($row==false)$this->messager("验证已过期或者验证信息不符合要求",null);
		if ($row['uid']!=$uid)$this->messager("验证用户ID和你的用户ID不符合，验证失败。",null);
		if($row['status']=='1')$this->Messager('您已经验证过了，不需要重复验证。',null);
		$data=array();
		$data['verify_time']=time();
		$data['status']=1;
		jtable('member_validate')->update($data, $p);

				jtable('member_validate')->delete(array('uid'=>$uid, 'status'=>0));

		$data=array();
		$data['role_id']=$row['role_id'];
		$data['email_checked'] = '1';
        $data['email'] = $row['email'];
		$data['email2'] = '';
		if($GLOBALS['_J']['role_id']=='5'){			$data['role_id'] = '3';
		}

		jtable('members')->update($data, $row['uid']);

				$data = array('notice_pm'=>1,'notice_reply'=>1,'notice_at'=>1,'notice_fans'=>1,'notice_event'=>1,'user_notice_time'=>3);
		jtable('members')->update($data, $uid);

		
		if('reg' == $this->Get['from']){
	        if ($this->Config['reg_step3_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=follow_channel');
	        } elseif ($this->Config['reg_step4_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=follow_member');
	        } elseif ($this->Config['reg_step5_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=add_face');
	        } elseif ($this->Config['reg_step6_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=member_profile');
	        } elseif ($this->Config['reg_step7_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
	        } else {
	            $redirect_to = jurl('index.php?mod=topic');
	        }
			$this->Messager(NULL, $redirect_to, 0);
		}else{
			$this->Messager('您的邮箱验证成功，现在将带您进入微博首页，体验丰富多采的微博之旅！', jurl('index.php?mod=topic'));
		}
	}

    
    function followChannel(){
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
        $menuHTML = $this->getRegiterMenu(3);
        $channelList = array();
        if($this->Config['channel_enable'] && $this->Config['reg_step3_radio']){
            $cachefile = jconf::get('channel');
            $channelFirst = is_array($cachefile['first']) ? $cachefile['first'] : array();
            $channelSecond = is_array($cachefile['second']) ? $cachefile['second'] : array();
            $my_channels = jlogic('channel')->mychannel(MEMBER_ID);            if($my_channels){
                $my_channels_keys = array_keys($my_channels);
            }

            if($channelFirst){
                foreach($channelFirst as $k => $v){
                    $v['follow_html'] = follow_channel($v['ch_id'],$my_channels_keys ? in_array($val['parent_id'],$my_channels_keys) : 0);
                    $channelList[$k] = $v;
                }
            }
            if ($channelSecond) {
                foreach ($channelSecond as $key => $val) {
                    if ($val['ch_id'] < 0) return '';
                    if (isset($channelFirst[$val['parent_id']]) && $channelFirst[$val['parent_id']] > 0 ) {
                        $val['follow_html'] = follow_channel($val['ch_id'],$my_channels_keys ? in_array($val['ch_id'],$my_channels_keys) : 0);
                        $channelList[$val['parent_id']]['second'][$key] = $val;
                    }
                }
            }
        }

        if ($this->Config['reg_step4_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=follow_member');
        } elseif ($this->Config['reg_step5_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=add_face');
        } elseif ($this->Config['reg_step6_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=member_profile');
        } elseif ($this->Config['reg_step7_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
        } else {
            $redirect_to = jurl('index.php?mod=topic');
        }

        if(!$channelList){
            $this->Messager(NULL, $redirect_to, 0);
        }

        $this->Title = '关注频道';
        include template('register/register_member_channel');
    }

    
    function followMember() {
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
        $menuHTML = $this->getRegiterMenu(4);
        if ($this->Config['reg_step5_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=add_face');
        } elseif ($this->Config['reg_step6_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=member_profile');
        } elseif ($this->Config['reg_step7_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
        } else {
            $redirect_to = jurl('index.php?mod=topic');
        }

        $regiter_tuijian1 = $this->Config['regiter_tuijian'];

        if(!$this->Config['reg_step4_radio'] || !$regiter_tuijian1){
            $this->Messager(NULL, $redirect_to, 0);
        }

        $tuijianList = array();

        foreach ($regiter_tuijian1 as $key => $val) {
                        if($val == 'channel'){
                $memberList[$val] = jlogic('user')->getChannelUserTop();
                if($memberList[$val]){
                    $regiter_tuijian[$key] = array('type'=>'channel','name'=>'频道达人');
                }
                        } else if ($val == 'dig') {
                $memberList[$val] = jlogic('user')->getDigUser();
                if($memberList[$val]){
                    $regiter_tuijian[$key] = array('type'=>'dig','name'=>'常被赞的人');
                }
                        } else if ($val == 'recd') {
                $memberList[$val] = $this->getRecommendUser();
                if($memberList[$val]){
                    $regiter_tuijian[$key] = array('type'=>'recd','name'=>'官方推荐');
                }
            }
        }

        if(!$this->Config['reg_step4_radio'] || !$regiter_tuijian){
            $this->Messager(NULL, $redirect_to, 0);
        }

        $this->Title = '关注达人';
        include template('register/register_member_follow');
    }

    
    function addFace(){
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
        $menuHTML = $this->getRegiterMenu(5);
        if ($this->Config['reg_step6_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=member_profile');
        } elseif ($this->Config['reg_step7_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
        } else {
            $redirect_to = jurl('index.php?mod=topic');
        }
        if(!$this->Config['reg_step5_radio']){
            $this->Messager(NULL, $redirect_to, 0);
        }

        $member = jsg_member_info(MEMBER_ID);
        $temp_face = '';
        if($this->Get['temp_face'] && is_image($this->Get['temp_face']))
        {
        	$temp_face = $this->Get['temp_face'];

         	$member['face_original'] = $temp_face;
        }
		if(true === UCENTER_FACE && true === UCENTER){
			include_once(ROOT_PATH . './api/uc_client/client.php');
			$uc_avatarflash = uc_avatar(MEMBER_UCUID,'avatar','returnhtml');
		}
        $this->Title = '上传头像';
        include template('register/register_member_face');
    }

    
    function memberProfile(){
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
                 if($this->Post['img_path'] && strpos($this->Post['old_face'],"noavatar.gif")){
            $pic_file = $this->Post['img_path'];
            if(empty($pic_file) || !is_image($pic_file)) {
                $this->Messager(null,jurl('index.php?mod=member&code=add_face'),0);
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
        }
        
        $menuHTML = $this->getRegiterMenu(6);
        if ($this->Config['reg_step7_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
        } else {
            $redirect_to = jurl('index.php?mod=topic');
        }
        if(!$this->Config['reg_step6_radio']){
            $this->Messager(NULL, $redirect_to, 0);
        }


        if($this->Config['city_status']){
                        $query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			$province_list = array(array('value'=>0,'name'=>'请选择...'));
            while (false != ($rsdb = $query->GetRow())){
                $province_list[$rsdb['id']]['value']  = $rsdb['id'];
                $province_list[$rsdb['id']]['name']  = $rsdb['name'];
            }
            $province = jform()->Select("province",$province_list,null,"onchange=\"changeProvince();\"");
        }

        		if ($this->Config['company_enable'] && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$companyselect = $this->CpLogic->get_cp_html();
			if($this->Config['department_enable']){
				$departmentselect = $this->CpLogic->get_cp_html(0,'department');
			}
			$jobselect = jlogic('job')->get_job_select();
		}

		#增加项目
		$profileRegister = jconf::get('profileregister');

		$b_province_list = jform()->Select("b_province",$province_list,null,"onchange=\"changeProvince('b');\"");

        $this->Title = '补全资料';
        include template('register/register_member_profile');
    }
    
    function doMemberProfile(){
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
        if ($this->Config['reg_step7_radio']){
                        $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
        } else {
            $redirect_to = jurl('index.php?mod=topic');
        }
		if($this->Config['city_status'] && (empty($this->Post['province']) || empty($this->Post['city']))){
			$this->Messager(NULL, jurl('index.php?mod=member&code=member_profile'), 0);
		}

		
		$datas = array();
		$datas['uid'] = MEMBER_ID;
		if($this->Post['province']){
			$datas['province'] = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['province']."'"); 		}
		if($this->Post['city']){
			$datas['city'] = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['city']."'");		}
		if($this->Post['area']){
			$datas['area'] = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['area']."'");		}
		if($this->Post['street']){
			$datas['street'] = DB::result_first("select name from ".TABLE_PREFIX."common_district where id = '".(int) $this->Post['street']."'");		}

		if($this->_sms_register()) {
			$datas['phone'] = $sms_bind_num;
		}
				if ($this->Config['company_enable']){
			if($this->Post['companyid']){
				$datas['companyid'] = (int)$this->Post['companyid'];				$datas['company'] = $this->CpLogic->Getone($datas['companyid'],'company','name');
				if($datas['companyid']>0){
					$this->CpLogic->update('company',$datas['companyid'],1,0);
				}
			}
			if($this->Config['department_enable'] && $this->Post['departmentid']){
				$datas['departmentid'] = (int)$this->Post['departmentid'];				if($datas['departmentid']>0){
					$this->CpLogic->update('department',$datas['departmentid'],1,0);
				}
				$datas['department'] = $this->CpLogic->Getone($datas['departmentid'],'department','name');
			}
			if($this->Post['jobid']){
				$datas['jobid'] = (int)$this->Post['jobid'];				$datas['job'] = jlogic('job')->id2subject($arr['jobid']);
			}
		}

		$this->Post['gender'] && $datas['gender'] = (int) $this->Post['gender'];
		isset($this->Post['qq']) && $datas['qq'] = (($qq = is_numeric($this->Post['qq']) ? $this->Post['qq'] : 0) > 10000 && strlen((string) $qq) < 11) ? $qq : '';
		isset($this->Post['msn']) && $datas['msn'] = trim(strip_tags($this->Post['msn']));
		isset($this->Post['bday']) && $datas['bday'] = $this->Post['bday'];
		isset($this->Post['phone']) && $datas['phone'] = trim($this->Post['phone']);
		isset($this->Post['aboutme']) && $datas['aboutme'] = trim(strip_tags($this->Post['aboutme']));

		jtable('members')->update($datas);

		#附表信息(memberfield的字段profile_set)
		$arr1 = array();
		isset($this->Post['realname']) && $arr1['validate_true_name'] = trim(strip_tags($this->Post['realname']));
		isset($this->Post['idcardtype']) && $arr1['validate_card_type'] = $this->Post['idcardtype'];
		isset($this->Post['idcard']) && $arr1['validate_card_id'] = trim(strip_tags($this->Post['idcard']));
		$profile_set = array();

		#附表2信息(members_profile)
		$arr2 = array();
		$profileField = array('constellation','zodiac','telephone','address','zipcode','nationality','education','birthcity','graduateschool','pcompany','occupation'
							 ,'position','revenue','affectivestatus','lookingfor','bloodtype','height','weight','alipay','icq','yahoo','taobao','site','interest'
							 ,'linkaddress','field1','field2','field3','field4','field5','field6','field7','field8');
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
				if(isset($this->Post[$v])) {
					$arr2[$v] = trim(strip_tags($this->Post[$v]));
					$profile_set[$v] = 0;				}
			}
		}
		if($arr2){
			$this->_updateMemberProfile($arr2);
		}

		if($arr1){
						if($profile_set) {
				$arr1['profile_set'] = serialize($profile_set);
			}
			$this->_updateMemberField($arr1);
		}

        $this->Messager(NULL, $redirect_to, 0);
    }

    
    function doFirstTopic(){
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
		if($this->Post['img_path'] && strpos($this->Post['old_face'],"noavatar.gif")){
            $pic_file = $this->Post['img_path'];
            if(empty($pic_file) || !is_image($pic_file)) {
                $this->Messager(null,jurl('index.php?mod=member&code=add_face'),0);
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
        }
        $menuHTML = $this->getRegiterMenu(7);
                if(MEMBER_ID < 1 || !$this->Config['reg_step7_radio']){
            $this->Messager(NULL,jurl('index.php?mod=topic'));
        }

		$this->Title = '发布第一条微博';
         		global $_J;
        $content_dstr = $content = '#新人报到# 我是'.$this->Config[site_name].'新加入的成员@'.MEMBER_NICKNAME.' ，欢迎大家来关注我！';
        if($this->Config['first_topic_to_channel']){
            $this->item = $item = 'channel';
            $this->item_id = $itemid = $this->Config['first_topic_to_channel'];
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
        }
        $member = jsg_member_info(MEMBER_ID);

		include(template('register/register_member_issue'));
    }

	
	function Register()
	{
				if(MEMBER_ID > 0 && false == $this->IsAdmin) {
			$this->Messager('您已经是注册用户，无需再注册！', -1);
		}
				$regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			$this->Messager($regstatus['error'], null);
		}

				$inviter_member = array();
		$action="index.php?mod=member&code=doregister";

		$check_result = jsg_member_register_check_invite($this->Code);

		if($regstatus['invite_enable'] && !$regstatus['normal_enable']) 		{
			if(!$this->Code)
			{
				$this->Messager("本站目前需要有邀请链接才能注册。" . jsg_member_third_party_reg_msg(), null);
			}

			if(!$check_result)
			{
				$this->Messager("对不起，您访问的邀请链接不正确或者因邀请数已满而失效，请重新与邀请人索取链接。", null);
			}
		}

		if($check_result['uid'] > 0)
		{
			$inviter_member = jsg_member_info($check_result['uid']);
		}
		$action .= "&invite_code=" . urlencode($this->Code);

				$referer = jget('referer');
		if(jsg_getcookie('referer')=='')
		{
			jsg_setcookie('referer', $referer);
		}

		$noemail = 0;
		if($this->_sms_register())
		{
			$noemail = jconf::get('sms', 'register_verify', 'noemail');
		}
		$email = '';
		$_email = get_param('email');
		if(false != (jclass('passport')->_is_email($_email))) {
			$email = $_email;
		}

		$this->Title="注册新用户";
		include template('register/register_member');
	}


	
	function DoRegister()
	{
				if(MEMBER_ID != 0 AND false == $this->IsAdmin)
		{
			$this->Messager('您已经是注册用户，无需再注册！', -1);
		}

				$regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			$this->Messager($regstatus['error'], null);
		}

		$message = array();
		$timestamp = time();

		$noemail = 0;
		$sms_ckret = 0;
		if($this->_sms_register())
		{
						$sms_bind_num = $this->Post['sms_bind_num'];
			$sms_bind_key = $this->Post['sms_bind_key'];

			$sms_ckret = sms_check_bind_key($sms_bind_num, $sms_bind_key);
			if($sms_ckret)
			{
				$this->Messager($sms_ckret, -1);
			}

			$noemail = jconf::get('sms', 'register_verify', 'noemail');
			if($noemail)
			{
				$this->Post['email'] = $sms_bind_num . '@139.com';
			}
		}

		
		if ($this->Config['seccode_enable']==1 && $this->Config['seccode_register']) {
			if (!ckseccode(@$_POST['seccode'])) {
				$this->Messager("验证码输入错误",-1);
			}
		}elseif ($this->Config['seccode_enable']>1 && $this->Config['seccode_register'] && $this->yxm_title && $this->Config['seccode_pub_key'] && $this->Config['seccode_pri_key']) {
			$YinXiangMa_response=jlogic('seccode')->CheckYXM(@$_POST['add_YinXiangMa_challenge'],@$_POST['add_YXM_level'][0],@$_POST['add_YXM_input_result']);
			if($YinXiangMa_response != "true"){
				$this->Messager("验证码输入错误",-1);
			}
		}
		
		$inviter_member = array();
		$invite_code = ($this->Post['invite_code'] ? $this->Post['invite_code'] : $this->Get['invite_code']);
		$check_result = jsg_member_register_check_invite($invite_code);

		if($regstatus['invite_enable'] && !$regstatus['normal_enable'])
		{
			if(!$invite_code)
			{
				$this->Messager("本站目前需要有好友邀请链接才能注册。<br><br>看看<a href=\"?mod=topic&code=top\">达人榜</a>中有没有你认识的人，让他给你发一个好友邀请。", null);
			}

			if(!$check_result)
			{
				$this->Messager("对不起，您访问的邀请链接不正确或者因邀请数已满而失效，请重新与邀请人索取链接。", null);
			}
		}

		if($check_result['uid'] > 0)
		{
			$inviter_member = jsg_member_info($check_result['uid']);
		}
		if(!$inviter_member && $this->Config['register_invite_input'])
		{
			$inviter_member = jsg_member_info($this->Post['inviter_nickname'], 'nickname');
		}

		
		$password = $this->Post['password'];
		$email = $this->Post['email'];
		$username = $nickname = $this->Post['nickname'];

		
		if(strlen($password) < 5) {
			$this->Messager("密码过短，请设置至少5位",-1);
		}
		if($password != $this->Post['password2']) {
			$this->Messager("两次输入的密码不相同",-1);
		}

		if($GLOBALS['_J']['plugins']['func']['reg']) {
			hookscript('reg', 'funcs', array('param' => $this->Post, 'step' => 'check'), 'reg');
		}

		
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

		
		$datas = array();
		$datas['uid'] = $uid;

		if($this->_sms_register()) {
			$datas['phone'] = $sms_bind_num;
		}
		jtable('members')->update($datas);

				if($this->_sms_register()) {
			$_sms_info = _sms_client_user($sms_bind_num);

						$_sms_sets = array(
				'uid' => $uid,
				'username' => $username,
				'bind_key' => 0,
	            'bind_key_time' => 0,
	            'try_bind_times' => '+1',
	            'last_try_bind_time' => $timestamp,
			);

			sms_client_user_update($_sms_sets, $_sms_info);
		}

				if($inviter_member) {
			jsg_member_register_by_invite($inviter_member['uid'], $uid, $check_result);
		}

		
		$rets = jsg_member_login($uid, $password, 'uid');

		$redirect_to = jget('referer');
					
	        if($this->Config['reg_email_verify']){
	            	            $redirect_to = jurl('index.php?mod=member&code=setverify&ids='.$uid.'&from=reg');
	        } elseif ($this->Config['reg_step3_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=follow_channel');
	        } elseif ($this->Config['reg_step4_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=follow_member');
	        } elseif ($this->Config['reg_step5_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=add_face');
	        } elseif ($this->Config['reg_step6_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=member_profile');
	        } elseif ($this->Config['reg_step7_radio']){
	            	            $redirect_to = jurl('index.php?mod=member&code=do_first_topic');
	        } else {
	            $redirect_to = jurl('index.php?mod=topic');
	        }
		
		$this->Messager(NULL, $redirect_to, 0);
	}

	
	function _updateMemberField($arr1){
		$uid = MEMBER_ID;
		if($uid < 1){return ;}
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
			$count = DB::result_first("select count(*) from `".TABLE_PREFIX."memberfields` where `uid` = '$uid'");
			if($count){
				$sql = "update `".TABLE_PREFIX."memberfields` set ".implode(" , ",$sets)." where `uid`='$uid'";
				DB::query($sql);
			} else {
				$sets['uid'] = " `uid` = '$uid' ";
				DB::query("insert into `".TABLE_PREFIX."memberfields` set ".implode(" , ",$sets));
			}
		}
	}
	function _updateMemberProfile($arr2) {
		return jlogic('member_profile')->set_member_profile_info(MEMBER_ID, $arr2);
		
	}

		function DoSetVerify()
	{
        if($GLOBALS['_J']['config']['ldap_enable']){
			$this->Messager('网站启用AD域帐号登录，禁止用户修改邮箱地址！');
		}
		$menuHTML = $this->getRegiterMenu(2);
				$uid = MEMBER_ID;

		if(jdisallow($uid)){
						$this->Messager('你无权验证他人的邮箱，点击进入验证自己的邮箱', null);
		}

		$action = "index.php?mod=member&code=check_modify_email";

		$role = jtable('role')->info($this->Config['no_verify_email_role_id']);

		$sql = "SELECT `uid`,`ucuid`,`nickname`,`username`,`email`,`email2`,`email_checked`,`role_id` from `".TABLE_PREFIX."members` where `uid` = '{$uid}'  LIMIT 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$members = $query->GetRow();

		$member_validate = DB::fetch_first("select * from `".TABLE_PREFIX."member_validate` where `uid` = '$uid' and `type` = 'email'");

		if($members['email_checked'] == 1){
			$this->Messager('你的邮箱已验证，无需再进行验证',jurl('index.php?mod=topic&code=myhome'));
		}
				if(!$member_validate || $member_validate['regdate'] + 24*3600 < time()){
						jfunc('my');
			my_member_validate(MEMBER_ID,$members['email'],($members['role_id'] != $this->Config['normal_default_role_id'] ? $members['role_id'] : (int)$this->Config['normal_default_role_id']));
		}
				$email_url = $this->_email_url($members['email']);

		$this->Title = '帐户激活（验证我的邮箱）';
		include(template('register/register_member_verify'));
	}

		function DoModifyEmail()
	{
				$uid = MEMBER_ID;

		if(jdisallow($uid)) {
			exit('你无权验证他人的邮箱，点击进入验证自己的邮箱');
		}

				$email = jget('email','email');

				$checktype = $this->Post['checktype'];

		$sql = "SELECT `uid`,`ucuid`,`nickname`,`username`,`email`,`role_id`,`email_checked` from `".TABLE_PREFIX."members` where `uid` = '{$uid}'  LIMIT 0,1";
		$query = $this->DatabaseHandler->Query($sql);
		$members = $query->GetRow();

		if($email)
		{
			if($checktype == 'modify')
			{
				
				$jsg_result = jsg_member_checkemail($email, $members['ucuid']);

				if($jsg_result < 1)
				{
					$rets = array(
			        	'0' => '【注册失败】有可能是站点关闭了注册功能',
			        	'-4' => 'Email 不合法，请输入正确的Email地址。',
			        	'-5' => 'Email 不允许注册，请尝试更换一个。',
			        	'-6' => 'Email 已经存在了，请尝试更换一个。',
			        );

			        			        echo $rets[$jsg_result];
			        die;
				}
								if($members['email_checked'] == 0){
					$sql = "update `".TABLE_PREFIX."members` set  `email`='{$email}' where `uid`='{$uid}'";
				}else{
					$sql = "update `".TABLE_PREFIX."members` set  `email2`='{$email}' where `uid`='{$uid}'";
				}
				DB::query($sql);
			}

			jfunc('my');

			$ret = my_member_validate(MEMBER_ID,$email,($members['role_id'] != $this->Config['normal_default_role_id'] ? $members['role_id'] : (int)$this->Config['normal_default_role_id']),1);

			if($ret){
				echo "邮件已重新发送成功";
			}else{
				echo "邮件发送失败，请填写有效的邮箱地址或联系管理员。";
			}
			echo "<script language='Javascript'>";
			echo "parent.document.getElementById('user_email').innerHTML='{$email}';";
			echo "</script>";
		    die;
		} else {
			echo "请输入正确的邮箱";
			die;
		}
	}

	function getRecommendUser()
	{
				$follow_type = 'recommend';
		$this->ShowConfig = jconf::get('show');

		$day = 7;
		$time = $day * 86400;
		$limit = (int) $this->ShowConfig['reg_follow']['user'];
		if($limit < 1) $limit = 20;

		
		$TopicLogic = jlogic('topic');
		$regfollow = jconf::get('regfollow');

				for ($i = 0; $i < count($regfollow); $i++)
		{
			if($regfollow[$i] == '')
			{
				unset($regfollow[$i]);
			}
		}

		if (!empty($regfollow)) {
			$count = count($regfollow);
			if ($count > $limit) {
				$keys = array_rand($regfollow, $limit);
				foreach ($keys as $k) {
					$uids[] = $regfollow[$k];
				}
			} else {
				$uids = $regfollow;
			}
		} else {
						$cache_id = "misc/RTU-{$day}-{$limit}";
			if (false === ($uids = cache_file('get', $cache_id))) {
				$dateline = time() - $time;
				$sql = "SELECT DISTINCT(uid) AS uid, COUNT(tid) AS topics FROM `".TABLE_PREFIX."topic` WHERE dateline>=$dateline GROUP BY uid ORDER BY topics DESC LIMIT {$limit}";
				$query = $this->DatabaseHandler->Query($sql);
				$uids = array();
				while (false != ($row = $query->GetRow()))
				{
					$uids[$row['uid']] = $row['uid'];
				}

				cache_file('set', $cache_id, $uids, 900);
			}
		}

		if(!$uids) {
			$uids[] = 1;
		}

		$list = array();
		if($uids) {
			$list = $TopicLogic->GetMember($uids,"`uid`,`ucuid`,`username`,`face`,`validate`,`validate_category`,`nickname`");
		}

		return $list;
	}

	function Step2()
	{
		$this->Title = '发布第一条微博';
		include(template('register/register_member_issue'));
	}
	function DoStep2()
	{
		if(MEMBER_ID < 1){
            $this->Messager(NULL,jurl('index.php?mod=member'));
        }
		if (($content = $this->Post['content'])) {

			$TopicLogic = jlogic('topic');
			$return = $TopicLogic->Add($content);

			if ($return['tid'] < 1) {
				$this->Messager(is_string($return) ? $return : "未知错误",'?');
			}
		}

		$this->Messager("注册成功",jurl('index.php?mod=topic&code=myhome'),0);
	}

	function _sms_register()
	{
		if(!isset($this->sms_register))
		{
			$this->sms_register = ($this->Config['sms_enable'] && $this->Config['sms_register_verify_enable'] && sms_init($this->Config));
		}

		return $this->sms_register;
	}

	function Delete()
	{
		if(MEMBER_ID < 1 || 'admin' != MEMBER_ROLE_TYPE)
		{
			$this->Messager('您没有权限访问该页面', null);
		}

		$ids = get_param('ids');

		$ids = $ids ? $ids : jget('id', 'int');
		if(!$ids)
		{
			$this->Messager("请指定要删除的用户ID");
		}

		$rets = jsg_member_delete($ids);

		$member_ids_count = $rets['member_ids_count'];
		$admin_list = $rets['admin_list'];


		$msg = '';
		$msg .= "成功删除<b>{$member_ids_count}</b>位会员";
		if($admin_list)
		{
			$msg .= "，其中<b>".implode(' , ',$admin_list)."</b>是管理员，不能直接删除";
		}

		$this->Messager($msg, "?");
	}

    
    function getRegiterMenu($nowStep=2){
        $menu = array();
        $menuHTML = '';
                                                                        if ($this->Config['reg_step3_radio']){
                        $menu[3]['name'] = '关注频道';
            $menu[3]['url'] = jurl('index.php?mod=member&code=follow_channel');
        }
        if ($this->Config['reg_step4_radio']){
                        $menu[4]['name'] = '关注达人';
            $menu[4]['url'] = jurl('index.php?mod=member&code=follow_member');
        }
        if ($this->Config['reg_step5_radio']){
                        $menu[5]['name'] = '上传头像';
            $menu[5]['url'] = jurl('index.php?mod=member&code=add_face');
        }
        if ($this->Config['reg_step6_radio']){
                        $menu[6]['name'] = '资料补全';
            $menu[6]['url'] = jurl('index.php?mod=member&code=member_profile');
        }
        if ($this->Config['reg_step7_radio']){
                        $menu[7]['name'] = '我的第一次';
            $menu[7]['url'] = jurl('index.php?mod=member&code=do_first_topic');
        }
        if ($menu) {
            $i = 1;
            foreach ($menu as $key => $val) {
                if($key < $nowStep){
                    $menuHTML .= '<li class="t1"><em><a href="'.$val['url'].'">'.$i.'、'.$val['name'].'</a></em></li>';
                } else if ($key == $nowStep) {
                    $menuHTML .= '<li class="t1 on"><em>'.$i.'、'.$val['name'].'</em></li>';
                } else {
                    $menuHTML .= '<li class="t1"><em>'.$i.'、'.$val['name'].'</em></li>';
                }
                $i++;
            }
        }
        $menuHTML = '<ol class="m-regStep">'.$menuHTML.'</ol>';
        return $menuHTML;
    }

		function _email_url($email='')
	{
		$url = "";

		$email_array = explode("@",$email);

		$email_value = $email_array[1];

		switch($email_value)
		{
			case "163.com":
				$url = "mail.163.com";
				break;
			case "vip.163.com":
				$url = "vip.163.com/?b08abh1";
				break;
			case "sina.com":
				$url = "mail.sina.com.cn";
				break;
			case "sina.cn":
				$url = "mail.sina.com.cn/cnmail/index.html";
				break;
			case "vip.sina.com":
				$url = "vip.sina.com.cn";
				break;
			case "2008.sina.com":
				$url = "mail.2008.sina.com.cn";
				break;
			case "sohu.com":
				$url = "mail.sohu.com";
				break;
			case "vip.sohu.com":
				$url = "vip.sohu.com";
				break;
			case "tom.com":
				$url = "mail.tom.com";
				break;
			case "vip.sina.com":
				$url = "vip.tom.com";
				break;
			case "sogou.com":
				$url = "mail.sogou.com";
				break;
			case "126.com":
				$url = "www.126.com";
				break;
			case "vip.126.com":
				$url = "vip.126.com/?b09abh1";
				break;
			case "139.com":
				$url = "mail.10086.cn";
				break;
			case "gmail.com":
				$url = "www.google.com/accounts/ServiceLogin?service=mail";
				break;
			case "hotmail.com":
				$url = "www.hotmail.com";
				break;
			case "189.cn":
				$url = "webmail2.189.cn/webmail/";
				break;
			case "qq.com":
				$url = "mail.qq.com/cgi-bin/loginpage";
				break;
			case "yahoo.com":
				$url = "mail.cn.yahoo.com";
				break;
			case "yahoo.cn":
				$url = "mail.cn.yahoo.com";
				break;
			case "yahoo.com.cn":
				$url = "mail.cn.yahoo.com";
				break;
			case "21cn.com":
				$url = "mail.21cn.com";
				break;
			case "eyou.com":
				$url = "www.eyou.com";
				break;
			case "188.com":
				$url = "www.188.com";
				break;
			case "yeah.net":
				$url = "www.yeah.net";
				break;
			case "foxmail.com":
				$url = "mail.qq.com/cgi-bin/loginpage?t=fox_loginpage";
				break;
			case "wo.com.cn":
				$url = "mail.wo.com.cn/smsmail/login.html";
				break;
			case "263.net":
				$url = "www.263.net";
				break;
			case "x263.net":
				$url = "www.263.net";
				break;
			case "263.net.cn":
				$url = "www.263.net";
				break;
			default:
				$url = "mail.".$email_value;
		}
		if($url)
		{
			return $url;
		}
		else
		{
			return false;
		}
	}

	
	function face() {
		$info = array();
		$uid = jget('uid', 'int');
		if($uid < 1) {
			$keys = array('nickname', 'username', 'email', 'id', 'phone');
			$val = '';
			foreach($keys as $key) {
				$val = jget($key, 'txt');
				if($val) {
					break;
				}
			}
			if($val) {
				$info = jsg_member_info($val, '');
			}
		} else {
			$info = jsg_member_info($uid);
		}
		$type = jget('type', 'txt');
		$type = in_array($type, array('small', 'middle', 'big')) ? $type : 'small';
		$face = face_get($info, $type);
		if(jget('echo')) {
			exit($face);
		} else {
			$this->Messager(null, $face);
		}
	}

}

?>
