<?php
/**
 *
 * 后台系统设置
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: setting.mod.php 5640 2014-03-11 03:34:19Z chenxianfeng $
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



		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'changeword':
				$this->ChengWord();
				break;
			case 'dochangeword':
				$this->DoChangeWord();
				break;
            case 'topic_publish':
                $this->ModifyPublishBox();
                break;
            case 'do_topic_publish':
                $this->DoModifyPublishBox();
                break;
			case 'setmailmsg':
				$this->SetMailMsg();
				break;
			case 'dosetmailmsg':
				$this->DoSetMailMsg();
				break;
			case 'modify_normal':
				$this->ModifyNormal();
				break;
			case 'domodify_normal':
				$this->DoModifyNormal();
				break;
			case 'modify_image':
				$this->ModifyImage();
				break;
			case 'modify_qmd':
				$this->ModifyQMD();
				break;

			case 'modify_credits':
				$this->ModifyCredits();
				break;
			case 'do_modify_credits':
				$this->DoModifyCredits();
				break;
			case 'list_credits_rule':
			case 'modify_credits_rule':
				$this->ModifyCreditsRule();
				break;
			case 'do_modify_credits_rule':
				$this->DoModifyCreditsRule();
				break;

			case 'modify_sina':
				$this->ModifySina();
				break;
			case 'do_modify_sina':
				$this->DoModifySina();
				break;
			case 'bbs_plugin':
				$this->BbsPlugin();
				break;
			case 'cp_ad':
				$this->Cpad();
				break;
			case 'modify_qqwb':
				$this->ModifyQQWB();
				break;
			case 'do_modify_qqwb':
				$this->DoModifyQQWB();
				break;


			case 'modify_header_menu':
				$this->ModifyHeaderMenu();
				break;
			case 'domodify_header_menu':
				$this->DoModifyHeaderMenu();
				break;

			case 'modify_header_sub_menu':
				$this->ModifyHeaderSubMenu();
				break;
			case 'modify_header_sub_menu':
				$this->DoModifyHeaderSubMenu();
				break;

			case 'modify_meta':
				$this->ModifyMeta();
				break;
			case 'modify_rewrite':
				$this->ModifyRewrite();
				break;
			case 'domodify_rewrite':
				$this->DoModifyRewrite();
				break;

			case 'modify_remote':
				$this->ModifyRemote();
				break;
			case 'domodify_remote':
				$this->DoModifyRemote();
				break;

			case 'modify_filter':
				$this->ModifyFilter();
				break;
			case 'domodify_filter':
				$this->DoModifyFilter();
				break;

			case 'modify_register':
				$this->ModifyRegister();
				break;
			case 'modify_register_guide':
				$this->modifyRegisterGuide();
				break;
			case 'modify_access':
				$this->ModifyAccess();
				break;
			case 'domodify_access':
				$this->DoModifyAccess();
				break;
			case 'modify_seccode':
				$this->ModifySeccode();
				break;

			case 'modify_smtp':
				$this->ModifySmtp();
				break;
			case 'do_modify_smtp':
				$this->DoModifySmtp();
				break;

			case 'modify_shortcut':
				$this->ModifyShortcut();
				break;
			case 'do_modify_shortcut':
				$this->DoModifyShortcut();
				break;

			case 'email_notice':
				$this->Email_notice();
				break;
			case 'do_email_notice':
				$this->DoEmail_notice();
				break;

			case 'modify_slide':
				$this->ModifySlide();
				break;
			case 'do_modify_slide':
				$this->DoModifySlide();
				break;
			case 'modify_slide_index':
				$this->ModifySlideIndex();
				break;
			case 'do_modify_slide_index':
				$this->DoModifySlideIndex();
				break;


			case 'modify_hot_tag_recommend':
				$this->ModifyHotTagRecommend();
				break;
			case 'do_modify_hot_tag_recommend':
				$this->DoModifyHotTagRecommend();
				break;

			case 'modify_ftp':
				$this->ModifyFtp();
				break;
			case 'do_modify_ftp':
				$this->DoModifyFtp();
				break;

							case 'follow':
				$this->Follow();
				break;
			case 'do_follow':
				$this->Do_Follow();
				break;

							case 'regfollow':
				$this->regfollow();
				break;
			case 'do_regfollow':
				$this->do_regfollow();
				break;

							case 'experience':
				$this->experience();
				break;
							case 'do_experience':
				$this->do_Experience();
				break;

						case 'check_switch':
				$this->checkSwitch();
				break;
			case 'do_check_switch':
				$this->doCheckSwitch();
				break;
						case 'visit_state':
				$this->visitState();
				break;
			case 'do_visit_state':
				$this->doVisitState();
				break;

							case 'invite':
				$this->invite();
				break;
			case 'do_invite':
				$this->do_invite();
				break;

							case 'modify_topic_from':
				$this->ModifyTopicFrom();
				break;
			case 'do_modify_topic_from':
				$this->DoModifyTopicFrom();
				break;
			case 'modify_mobile':
				$this->modifyMobile();
				break;

			case 'modify_sysload':
				$this->ModifySysload();
				break;
			case 'do_wqueue':
				$this->do_modify_wqueue();
				break;
			default:
				$this->ModifyNormal();
				break;
		}

		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function ChengWord(){
		$word = jconf::get('changeword');
		if(!$word){
			$word = array
            (
                'n_weibo' => '微博',
            	'p_weibo' => '微博',
				'weiqun' => '微群',
				'dig' => '赞',
            	'username' => '昵称、Email、个性域名',
            	'account' => '帐号昵称',
            );

            jconf::set('changeword',$word);
		}
		include template('admin/setting_changeword');
	}
	function DoChangeWord(){
		$word = jget('word');

		jconf::set('changeword',$word);

		$this->Messager('设置成功',-1);
	}

    
    function ModifyPublishBox(){
		#匿名
		$anonymous_enable = $this->jishigou_form->YesNoRadio('config[anonymous_enable]', (int) $this->Config['anonymous_enable']);

        #签到
        $sign_enable = $this->jishigou_form->YesNoRadio('config[sign][sign_enable]', (int) $this->Config['sign']['sign_enable']);

        #表情
        $face_enable = $this->jishigou_form->YesNoRadio('config[face_enable]', (int) $this->Config['face_enable']);

        #图片
        $image_enable = $this->jishigou_form->YesNoRadio('config[image_enable]', (int) $this->Config['image_enable']);

        #视频
        $video_enable = $this->jishigou_form->YesNoRadio('config[video_enable]', (int) $this->Config['video_enable']);

        #音乐
        $music_enable = $this->jishigou_form->YesNoRadio('config[music_enable]', (int) $this->Config['music_enable']);

        #附件
        $attach_enable = $this->jishigou_form->YesNoRadio('config[attach_enable]', (int) $this->Config['attach_enable']);

        #附件
        $qun_attach_enable = $this->jishigou_form->YesNoRadio('config[qun_attach_enable]', (int) $this->Config['qun_attach_enable']);

        #话题
        $tag_enable = $this->jishigou_form->YesNoRadio('config[tag_enable]', (int) $this->Config['tag_enable']);

        #投票
        $vote_open = $this->jishigou_form->YesNoRadio('config[vote_open]', (int) $this->Config['vote_open']);

        #活动
        $event_open = $this->jishigou_form->YesNoRadio('config[event_open]', (int) $this->Config['event_open']);

        #短微博样式
        $clear_format_open = $this->jishigou_form->YesNoRadio('config[clear_format_open]', (int) $this->Config['clear_format_open']);
        #微群
        $qun_open = $this->jishigou_form->YesNoRadio('config[qun_setting][qun_open]', (int) $this->Config['qun_setting']['qun_open']);

        $word_type_enable = $this->jishigou_form->YesNoRadio('config[word_type_enable]', (int) $this->Config['word_type_enable']);

        include template('admin/topic_publish_box');
    }

    function DOModifyPublishBox(){
        $config = jget('config');

        jconf::update($config);

        $this->Messager('设置成功','admin.php?mod=setting&code=topic_publish');
    }

	function modifyMobile(){
		$iphone_file_msg = $iphone_join_msg = '';
		if($this->Config['iphone_pem_file']){
			if(!$this->_check_ipone_file($this->Config['iphone_pem_file'])){
				$iphone_file_msg = '（错误：文件命名不正确）';
			}elseif(!is_file(ROOT_PATH . $this->Config['iphone_pem_file'])){
				$iphone_file_msg = '（错误：该文件不存在）';
			}
		}
		if($this->Config['iphone_pem_file'] && $this->Config['iphone_passphrase'] && !$this->Config['iphone_download_url']){
			$iphone_join_msg = '（iPhone客户端下载地址未填写，无法开启）';
		}elseif($this->Config['iphone_pem_file'] && $this->Config['iphone_download_url'] && !$this->Config['iphone_passphrase']){
			$iphone_join_msg = '（证书密钥未填写，无法开启）';
		}elseif(!$this->Config['iphone_push_enable'] && $this->Config['iphone_pem_file'] && $this->Config['iphone_passphrase'] && $this->_check_ipone_file($this->Config['iphone_pem_file']) && function_exists("openssl_open") && !$this->_check_iphone_join($this->Config['iphone_pem_file'],$this->Config['iphone_passphrase'])){
			$iphone_join_msg = '（连接失败，无法开启）';
		}
		$options = array(
			'tpl' => 'admin/setting_mobile',
			'iphone_file_msg' => $iphone_file_msg,
			'iphone_join_msg' => $iphone_join_msg,
		);
		$this->ModifyNormal($options);
	}

	
	function ModifyNormal($options = array())
	{
		$action="admin.php?mod=setting&code=domodify_normal";
		$iphone_file_msg = $options['iphone_file_msg'] ? $options['iphone_file_msg'] : '';
		$iphone_join_msg = $options['iphone_join_msg'] ? $options['iphone_join_msg'] : '';
		$cache_id = "misc/role/admin_role_list";
		if(false === ($role_list = cache_file('get', $cache_id))) {
			$sql="select id,name,`type`
			FROM
				".TABLE_PREFIX.'role';
			$query = $this->DatabaseHandler->Query($sql);
			while(false!=($row=$query->getRow()))
			{
				$role_list[$row['type']][]=array('name'=>$row['name'],'value'=>$row['id']);
			}

			cache_file('set', $cache_id, $role_list);
		}


                $login_by_uid = $this->jishigou_form->YesNoRadio('config[login_by_uid]', (int) $this->Config['login_by_uid']);

                $register_link_display_radio = $this->jishigou_form->YesNoRadio('config[register_link_display]', (int) $this->Config['register_link_display']);
        $regiter3_radio = $this->jishigou_form->YesNoRadio('config[reg_step3_radio]', (int) $this->Config['reg_step3_radio']);
        $regiter4_radio = $this->jishigou_form->YesNoRadio('config[reg_step4_radio]', (int) $this->Config['reg_step4_radio']);
        $regiter_tuijian = array(
            array("name"=>"所选频道达人","value"=>"channel"),
            array('name'=>'常被赞的人','value'=>'dig'),
            array("name"=>"官方推荐用户","value"=>"recd")
        );
        $regiter_tuijian_select = $this->jishigou_form->Checkbox("config[regiter_tuijian][]",$regiter_tuijian,$this->Config['regiter_tuijian']);

        $regiter5_radio = $this->jishigou_form->YesNoRadio('config[reg_step5_radio]', (int) $this->Config['reg_step5_radio']);
        $regiter6_radio = $this->jishigou_form->YesNoRadio('config[reg_step6_radio]', (int) $this->Config['reg_step6_radio']);
        $regiter7_radio = $this->jishigou_form->YesNoRadio('config[reg_step7_radio]', (int) $this->Config['reg_step7_radio']);
                if($this->Config['channel_enable']){
            $cachefile = jconf::get('channel');
            $channelFirst = is_array($cachefile['first']) ? $cachefile['first'] : array();
            $channelSecond = is_array($cachefile['second']) ? $cachefile['second'] : array();
            $channels = $cachefile['channels'];
            $channel_list = array();
            if($channels){
                $channel_list[0] = array('name'=>'请选择','value'=>'');
                foreach ($channels as $key => $val) {
                    if($key > 0){
                        $channel_list[$key]['valeu'] = $key;
                        $channel_list[$key]['name'] = $channelFirst[$key]['ch_name'] ? $channelFirst[$key]['ch_name'] : $channelSecond[$key]['ch_name'];
                    }
                }
                $first_topic_to_channel = $this->jishigou_form->Select('config[first_topic_to_channel]',$channel_list,$this->Config['first_topic_to_channel']);
            }
        }

		$website_home_page_list=array(
					array("name"=>"经典首页（微博和登录框）","value"=>"topic|normal"),
					array('name'=>'简易首页（频道和登录框）','value'=>'topic|simple'),
					array("name"=>"极简首页（仅登录框）","value"=>"topic|only_login"),
					array("name"=>"广场（最新微博）","value"=>"topic|new"),
					array("name"=>"图片墙（微博图片）","value"=>"topic|photo")
					);
		$channels = jlogic('channel')->get_pub_channel();
		if($channels['channel_enable'] && $this->Config['channel_enable']) {
			$website_home_page_list[] = array("name"=>"频道页面（频道微博）","value"=>"topic|channellogin");
		}
		$website_home_page =  $this->jishigou_form->Select('config[website_home_page]',$website_home_page_list,$this->Config['default_module'].'|'.$this->Config['default_code']);

		$normal_role_select=$this->jishigou_form->Select('config[normal_default_role_id]',
		$role_list['normal'],
		$this->Config['normal_default_role_id']);

		$no_verify_email_role_select=$this->jishigou_form->Select('config[no_verify_email_role_id]',
		$role_list['normal'],
		$this->Config['no_verify_email_role_id']);

		$email_verify_radio=$this->jishigou_form->YesNoRadio('config[reg_email_verify]', (int) $this->Config['reg_email_verify']);

		$user_forbid = jconf::get('user','forbid');

		$email_white_list = jconf::get('email_white_list');
		if ($email_white_list) {
			$email_white_list = implode("\r\n",$email_white_list);
		} else {
			$email_white_list = '';
		}

		$register = jconf::get('register');
		$time_unit_config = jconf::get('time_unit');
		$register_ip_time_unit_select = $this->jishigou_form->Select('register[ip][time_unit]', $time_unit_config, $register['ip']['time_unit']);
		$register_ip_white_list = ((is_array($register['ip']['white_list']) && $register['ip']['white_list']) ? implode("\n", $register['ip']['white_list']) : "");
		
		$_config = array(
			"-12" => array("value"=>"-12","name"=>"(GMT -12:00) Eniwetok, Kwajalein"),
			"-11" => array("value"=>"-11","name"=>"(GMT -11:00) Midway Island, Samoa"),
			"-10" => array("value"=>"-10","name"=>"(GMT -10:00) Hawaii"),
			"-9" => array("value"=>"-9","name"=>"(GMT -09:00) Alaska"),
			"-8" => array("value"=>"-8","name"=>"(GMT -08:00) Pacific Time (US &amp; Canada), Tijuana"),
			"-7" => array("value"=>"-7","name"=>"(GMT -07:00) Mountain Time (US &amp; Canada), Arizona"),
			"-6" => array("value"=>"-6","name"=>"(GMT -06:00) Central Time (US &amp; Canada), Mexico City"),
			"-5" => array("value"=>"-5","name"=>"(GMT -05:00) Eastern Time (US &amp; Canada), Bogota, Lima, Quito"),
			"-4" => array("value"=>"-4","name"=>"(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz"),
			"-3.5" => array("value"=>"-3.5","name"=>"(GMT -03:30) Newfoundland"),
			"-3" => array("value"=>"-3","name"=>"(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is"),
			"-2" => array("value"=>"-2","name"=>"(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena"),
			"-1" => array("value"=>"-1","name"=>"(GMT -01:00) Azores, Cape Verde Islands"),
			"0"  =>array("value"=>"0","name"=>"(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia"),
			"1" => array("value"=>"1","name"=>"(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome"),
			"2" => array("value"=>"2","name"=>"(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa"),
			"3" => array("value"=>"3","name"=>"(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi"),
			"3.5" => array("value"=>"3.5","name"=>"(GMT +03:30) Tehran"),
			"4" => array("value"=>"4","name"=>"(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi"),
			"4.5" => array("value"=>"4.5","name"=>"(GMT +04:30) Kabul"),
			"5" => array("value"=>"5","name"=>"(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent"),
			"5.5" => array("value"=>"5.5","name"=>"(GMT +05:30) Bombay, Calcutta, Madras, New Delhi"),
			"5.75" => array("value"=>"5.75","name"=>"(GMT +05:45) Katmandu"),
			"6" => array("value"=>"6","name"=>"(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk"),
			"6.5" => array("value"=>"6.5","name"=>"(GMT +06:30) Rangoon"),
			"7" => array("value"=>"7","name"=>"(GMT +07:00) Bangkok, Hanoi, Jakarta"),
			"8" => array("value"=>"8","name"=>"(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei"),
			"9" => array("value"=>"9","name"=>"(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk"),
			"9.5" => array("value"=>"9.5","name"=>"(GMT +09:30) Adelaide, Darwin"),
			"10" => array("value"=>"10","name"=>"(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok"),
			"11" => array("value"=>"11","name"=>"(GMT +11:00) Magadan, New Caledonia, Solomon Islands"),
			"12" => array("value"=>"12","name"=>"(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island"),
		);
		$timezone_select = $this->jishigou_form->Select("config[timezone]",$_config,(int) $this->Config['timezone']);

		$gzip_radio=$this->jishigou_form->YesNoRadio("config[gzip]",(int)$this->Config['gzip']);
		$wap_radio=$this->jishigou_form->YesNoRadio("config[wap]",(int)$this->Config['wap']);
		$iphone_push_enable=$this->jishigou_form->YesNoRadio("config[iphone_push_enable]",(int)$this->Config['iphone_push_enable']);

								$edit_nickname_enable = $this->jishigou_form->YesNoRadio('config[edit_nickname_enable]',(int) $this->Config['edit_nickname_enable']);
				$edit_face_enable = $this->jishigou_form->YesNoRadio('config[edit_face_enable]',(int) $this->Config['edit_face_enable']);
				$same_city = $this->jishigou_form->YesNoRadio('config[same_city]',(int) $this->Config['same_city']);
				$verify_radio = $this->jishigou_form->YesNoRadio('config[verify]',(int) $this->Config['verify']);
				$alert_radio = $this->jishigou_form->YesNoRadio('config[verify_alert]',(int) $this->Config['verify_alert']);
				$face_verify_radio = $this->jishigou_form->YesNoRadio('config[face_verify]',(int) $this->Config['face_verify']);
				$sign_verify_radio = $this->jishigou_form->YesNoRadio('config[sign_verify]',(int) $this->Config['sign_verify']);
				$widget_radio = $this->jishigou_form->YesNoRadio('config[widget_enable]',(int) $this->Config['widget_enable']);

				$qmd_radio=$this->jishigou_form->YesNoRadio("config[is_qmd]",(int) $this->Config['is_qmd']);
				$qmd_link_display_radio = $this->jishigou_form->YesNoRadio("config[qmd_link_display]", (int) $this->Config['qmd_link_display']);

				$video_radio = $this->jishigou_form->YesNoRadio("config[video_status]",(int)$this->Config['video_status']);

				$open_city_radio = $this->jishigou_form->YesNoRadio("config[city_status]",(int)$this->Config['city_status']);

				$open_signature_radio = $this->jishigou_form->YesNoRadio("config[is_signature]",(int)$this->Config['is_signature']);

				$open_level_radio = $this->jishigou_form->YesNoRadio("config[level_radio]",(int)$this->Config['level_radio']);

				$open_topic_level_radio = $this->jishigou_form->YesNoRadio("config[topic_level_radio]",(int)$this->Config['topic_level_radio']);

				$is_topic_user_follow = $this->jishigou_form->YesNoRadio("config[is_topic_user_follow]",(int)$this->Config['is_topic_user_follow']);

				$open_wap_reg_radio = $this->jishigou_form->YesNoRadio("config[wap_reg_radio]",(int)$this->Config['wap_reg_radio']);

						$topic_only_vip = (int)$this->Config['topic_vip'];
		$topic_only_vip_checked[$topic_only_vip ? $topic_only_vip : 0] = 'checked';

				$email_must_be_true[$this->Config['email_must_be_true'] ? $this->Config['email_must_be_true'] : 0] = 'checked';

		#发微博需要上传头像
		$add_topic_need_face = $this->jishigou_form->YesNoRadio("config[add_topic_need_face]",(int)$this->Config['add_topic_need_face']);

				
		$_config = array(
		0 => array('name'=>'从中心向四周截图（多图排列更平整），生成上述设定宽高的小图，如120x120（默认）<br />', 'value'=>0),
		1 => array('name'=>'全图等比例缩放（将最长一边缩小到120，另一边等比例缩小），如60x120，120x100<br />', 'value'=>1),
						);
		$thumb_cut_type_radio = $this->jishigou_form->Radio("config[thumb_cut_type]", $_config, (int) $this->Config['thumb_cut_type']);
		$watermark_enable_radio = $this->jishigou_form->YesNoRadio("config[watermark_enable])",(bool) $this->Config['watermark_enable']);

				$only_show_vip_topic = $this->jishigou_form->YesNoRadio("config[only_show_vip_topic])",(bool) $this->Config['only_show_vip_topic']);

		$_config = array(
		1=>array('name'=>'左上角','value'=>1,),
		2=>array('name'=>'左下角','value'=>2,),
		3=>array('name'=>'右上角','value'=>3,),
		4=>array('name'=>'右下角','value'=>4,),
		-1=>array('name'=>'随机位置','value'=>-1,),
		);
		$watermark_position_radio = $this->jishigou_form->Radio("config[watermark_position]",$_config,(int) $this->Config['watermark_position']);
		$close_second_verify_enable_radio = $this->jishigou_form->YesNoRadio("config[close_second_verify_enable]",(bool) $this->Config['close_second_verify_enable']);
		$jump_to_enable_radio = $this->jishigou_form->YesNoRadio("config[jump_to_enable]",(bool) $this->Config['jump_to_enable']);

		$contents = array(
			0=>array('name'=>'个性域名','value'=>'url'),
			1=>array('name'=>'@用户昵称','value'=>'nickname'),
				);
		$watermark_contents_radio = $this->jishigou_form->Checkbox("config[watermark_contents][]",$contents,$this->Config['watermark_contents']);

				$sec_options=array(
			array("name"=>"不开启(关闭)","value"=>"0",'extra'=>''),
			array("name"=>"开启普通验证码","value"=>"1",'extra'=>''),
			array("name"=>"开启云验证码","value"=>"2",'extra'=>'')
		);
		$seccode_enable_radio = $this->jishigou_form->Radio("seccode_enable", $sec_options, (int)$this->Config['seccode_enable']);
		$seccode_1_style = $this->Config['seccode_enable']=='1' ? '' : 'none';
		$seccode_2_style = $this->Config['seccode_enable']=='2' ? '' : 'none';
		$checked = array();
		$checked['seccode_login'] = $this->Config['seccode_login'] ? 'checked="checked"' : '';
		$checked['seccode_register'] = $this->Config['seccode_register'] ? 'checked="checked"' : '';
		$checked['seccode_password'] = $this->Config['seccode_password'] ? 'checked="checked"' : '';
		$checked['seccode_publish'] = $this->Config['seccode_publish'] ? 'checked="checked"' : '';
		$checked['seccode_comment'] = $this->Config['seccode_comment'] ? 'checked="checked"' : '';
		$checked['seccode_forward'] = $this->Config['seccode_forward'] ? 'checked="checked"' : '';
		$checked['seccode_sms'] = $this->Config['seccode_sms'] ? 'checked="checked"' : '';
		$checked['seccode_no_email'] = $this->Config['seccode_no_email'] ? 'checked="checked"' : '';
		$checked['seccode_no_photo'] = $this->Config['seccode_no_photo'] ? 'checked="checked"' : '';
		$checked['seccode_no_vip'] = $this->Config['seccode_no_vip'] ? 'checked="checked"' : '';


		@$site_enable = file_get_contents(ROOT_PATH . './data/cache/site_enable.txt');


		$_options = array(
			'normal' => array('name'=>'普通注册', 'value'=>'normal',),
			'invite' => array('name'=>'邀请注册', 'value'=>'invite',),
		);
		$regstatus_checkbox = $this->jishigou_form->Checkbox('config[regstatus][]', $_options, $this->Config['regstatus']);

				$third_party_regstatus = array();
		if(sina_weibo_init($this->Config)) {
			$third_party_regstatus['sina'] = array('name'=>'新浪微博', 'value'=>'sina');
		}
		if(qqwb_init($this->Config)) {
			$third_party_regstatus['qqwb'] = array('name'=>'腾讯微博', 'value'=>'qqwb');
		}
		if(kaixin_init($this->Config)) {
			$third_party_regstatus['kaixin'] = array('name'=>'开心帐户', 'value'=>'kaixin');
		}
		if(renren_init($this->Config)) {
			$third_party_regstatus['renren'] = array('name'=>'人人帐户', 'value'=>'renren');
		}
		if(yy_init($this->Config)) {
			$third_party_regstatus['yy'] = array('name'=>'YY帐户', 'value'=>'yy');
		}
		if($third_party_regstatus) {
			$third_party_regstatus_checkbox = $this->jishigou_form->Checkbox('config[third_party_regstatus][]', $third_party_regstatus, $this->Config['third_party_regstatus']);
		}

		$register_invite_input_radio = $this->jishigou_form->YesNoRadio("config[register_invite_input]", (int) $this->Config['register_invite_input']);
		$register_invite_input2_radio = $this->jishigou_form->YesNoRadio("config[register_invite_input2]", (int) $this->Config['register_invite_input2']);
		$company_enable = $this->jishigou_form->YesNoRadio("config[company_enable]", (int) $this->Config['company_enable']);
		$department_enable = $this->jishigou_form->YesNoRadio("config[department_enable]", (int) $this->Config['department_enable']);
		if(@is_file(ROOT_PATH . 'include/logic/cp.logic.php')){$cp_enable = true;}else{$cp_enable = false;};

				$reply_mode_normal_radio = $this->jishigou_form->YesNoRadio("config[reply_mode_normal]", (int) $this->Config['reply_mode_normal']);


		$tpl = $options['tpl'] ? $options['tpl'] : 'admin/setting_normal';
		$purviewhtml = $options['purviewhtml'] ? $options['purviewhtml'] : '';
		$pub_key = $options['pub_key'] ? $options['pub_key'] : $this->yxm_pri_key;
		$pri_key = $options['pri_key'] ? $options['pri_key'] : $this->yxm_pub_key;

		include template($tpl);
	}

	
	function checkSwitch(){



				$verify_radio = $this->jishigou_form->YesNoRadio('config[verify]',(int) $this->Config['verify']);
				$alert_radio = $this->jishigou_form->YesNoRadio('config[verify_alert]',(int) $this->Config['verify_alert']);
				$face_verify_radio = $this->jishigou_form->YesNoRadio('config[face_verify]',(int) $this->Config['face_verify']);
				$sign_verify_radio = $this->jishigou_form->YesNoRadio('config[sign_verify]',(int) $this->Config['sign_verify']);

						$topic_only_vip = (int)$this->Config['topic_vip'];
		$topic_only_vip_checked[$topic_only_vip ? $topic_only_vip : 0] = 'checked';

		include template('admin/check_switch');

	}

	function doCheckSwitch(){

				$admin_list = '';
		if(isset($this->Post['config']['notice_to_admin'])){
			$notice_to_admin = explode('|',$this->Post['config']['notice_to_admin']);
			$new_notice_to_admin = array();
			foreach ($notice_to_admin as $val){
				$val = trim($val);
				if($val && !in_array($val,$new_notice_to_admin)){
					$new_notice_to_admin[] = $val;
				}
			}

			if($new_notice_to_admin){
				$admin_list = implode('|',$new_notice_to_admin);
			}
			$this->Post['config']['notice_to_admin'] = $admin_list;
		}
		$new_config = $config = jconf::core_settings();

		foreach($this->Post['config'] as $k=>$v)
		{
			if(isset($this->Post['config'][$k]) && !is_null($v))
			{
				$new_config[$k] = $v;
			}
		}
		$result = jconf::set($new_config);

		if($result!=false)
		{
			$this->Messager("配置修改成功");
		}
		else
		{
			$this->Messager("配置修改失败");
		}


	}

	
	function visitState(){
		if(false === ($role_list = cache_file('get', $cache_id))) {
			$sql="select id,name,`type`
			FROM
				".TABLE_PREFIX.'role';
			$query = $this->DatabaseHandler->Query($sql);
			while(false!=($row=$query->getRow()))
			{
				$role_list[$row['type']][]=array('name'=>$row['name'],'value'=>$row['id']);
			}

			cache_file('set', $cache_id, $role_list);
		}

		if($this->Config['allowed_visit_role_list']){
			$allowed_visit_role_list = explode(',',$this->Config['allowed_visit_role_list']);
			foreach ($allowed_visit_role_list as $k => $v) {
				$allowed_visit_role_check_list[$v] = "checked";
			}
		}

		$site_enable = @file_get_contents(ROOT_PATH . 'data/cache/site_enable.txt');

		include template('admin/visit_state');
	}

	
	function doVisitState(){

		if(isset($this->Post['config']['visitState'])){
			if($this->Post['config']['visitState'] != 2){
				$this->Post['site_enable'] = '';
			} else {
				$this->Post['site_enable'] = $this->Post['site_enable'] ? $this->Post['site_enable'] : '完全关闭站点';
			}
			if(isset($this->Post['allowed_visit_role_list'])){
				$this->Post['config']['allowed_visit_role_list'] = implode(',',$this->Post['allowed_visit_role_list']);
			} else {
				$this->Post['config']['allowed_visit_role_list'] = '';
			}
		}

		if(isset($this->Post['site_enable']))
		{
			if($this->Post['site_enable']) {
				$f_rets = filter($this->Post['site_enable'], 0, 0, 0);
				if($f_rets['error']) {
					$this->Messager($f_rets['msg'], -1);
				}

				$this->Post['config']['site_closed'] = time();
				jio()->WriteFile(ROOT_PATH . './data/cache/site_enable.txt',$this->Post['site_enable']);
			} else {
				$this->Post['config']['site_closed'] = '0';
				jio()->DeleteFile(ROOT_PATH . './data/cache/site_enable.txt');
			}
			unset($this->Post['site_enable']);
		}

		$result = jconf::update($this->Post['config']);

		if($result!=false)
		{
			$this->Messager("配置修改成功");
		}
		else
		{
			$this->Messager("配置修改失败");
		}
	}


	function ModifySysload()
	{
				$wqueue = array(
			'enabled' => $this->Config['wqueue_enabled'] ? true : false,
			'host' => $this->Config['wqueue']['host'],
			'name' => $this->Config['wqueue']['name'],
			'auth' => $this->Config['wqueue']['auth']
		);
		if ($wqueue['enabled'])
		{
			$urlStatus = 'http:/'.'/'.$wqueue['host'].'/?name='.$wqueue['name'].'&opt=status_json&auth='.$wqueue['auth'];
			$jsonStatus = dfopen($urlStatus);
			$wqueue['status'] = json_decode($jsonStatus, true);
		}
		include template('admin/setting_sysload');
	}

	public function do_modify_wqueue()
	{
		jconf::update('wqueue_enabled', $this->Post['enabled'] == 'true' ? true : false);
		jconf::update('wqueue', array(
			'host' => $this->Post['host'],
			'name' => $this->Post['name'],
			'auth' => $this->Post['auth']
		));
		if ($this->Post['enabled'] == 'true')
		{
			$url_test = 'http:/'.'/'.$this->Post['host'].'/?name='.$this->Post['name'].'&opt=status_json&auth='.$this->Post['auth'];
			$r = dfopen($url_test);
		}
		else
		{
			$r = $this->Post['name'];
		}
		if (strstr($r, $this->Post['name']))
		{
			$this->Messager("配置修改成功");
		}
		else
		{
			jconf::update('wqueue_enabled', false);
			$this->Messager('配置修改失败（请检查队列服务端）');
		}
	}

		function _check_ipone_file($str){
		preg_match("/^[a-zA-Z]+[a-zA-Z0-9_]*[a-zA-Z0-9]+(\.pem)$/",$str, $matches);
		$res = isset($matches[1]) && $matches[1] != '' ? true : false;
		return $res;
	}

		function _check_iphone_join($pem='',$passphrase=''){
		$return = false;
		if($pem && $passphrase){
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', ROOT_PATH.$pem);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
			$fp = stream_socket_client('ssl:/' . '/gateway.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			if($fp){
				$return = true;
			}
		}
		return $return;
	}

	
	function DoModifyNormal()
	{
        if('11111111' == $this->Post['config']['iphone_passphrase']){
			$this->Post['config']['iphone_passphrase'] = $this->Config['iphone_passphrase'];
		}
				if(!($this->Post['config']['iphone_push_enable'] > 0 && $this->Post['config']['iphone_download_url'] && $this->Post['config']['iphone_passphrase'] && $this->Post['config']['iphone_pem_file'] && $this->_check_ipone_file($this->Post['config']['iphone_pem_file']) && is_file(ROOT_PATH . $this->Post['config']['iphone_pem_file']) && function_exists("openssl_open") && $this->_check_iphone_join($this->Post['config']['iphone_pem_file'],$this->Post['config']['iphone_passphrase']))){
			$this->Post['config']['iphone_push_enable'] = 0;
		}
		if (isset($this->Post['config']['nickname_length'])) {
            $this->Post['config']['nickname_length'] = (int) $this->Post['config']['nickname_length'];
            if($this->Config['ucenter_enable']){
                $this->Post['config']['nickname_length'] = $this->Post['config']['nickname_length'] > 15 ? 15 : $this->Post['config']['nickname_length'];
            }elseif($this->Post['config']['nickname_length'] > 50 ){
                $this->Post['config']['nickname_length'] = 50;
            }
        }
		if(isset($this->Post['config']['website_home_page'])){
			$website_home_page = explode('|',$this->Post['config']['website_home_page']);
			$this->Post['config']['default_module'] = $website_home_page[0];
			$this->Post['config']['default_code'] = $website_home_page[1] ? $website_home_page[1] : $website_home_page[0];
			unset($this->Post['config']['website_home_page']);
		}


		if(isset($this->Post['user_forbid'])) {
			if($this->Post['user_forbid']) {
				$forbid_list = explode("\r\n",$this->Post['user_forbid']);
				$forbid_list = array_unique($forbid_list);
				$forbid = implode("\r\n",$forbid_list);
				jconf::set('user',array('forbid'=>$forbid));
			}
			unset($this->Post['user_forbid']);

			$this->Post['config']['regstatus'] = (isset($this->Post['config']['regstatus']) ? $this->Post['config']['regstatus'] : array());
			$this->Post['config']['third_party_regstatus'] = (isset($this->Post['config']['third_party_regstatus']) ? $this->Post['config']['third_party_regstatus'] : array());
		}

        isset($this->Post['config']['regiter_tuijian']) && $this->Post['config']['regiter_tuijian'] = $this->Post['config']['regiter_tuijian'] ? $this->Post['config']['regiter_tuijian'] : array();

		if(isset($this->Post['config']['watermark_contents_size'])) {
			$this->Post['config']['watermark_contents'] = (isset($this->Post['config']['watermark_contents']) ? $this->Post['config']['watermark_contents'] : array());
		}

		if(isset($this->Post['email_white_list'])){
			if($this->Post['email_white_list']){
				$email_white_list = explode("\r\n",$this->Post['email_white_list']);
				$email_white_list = array_remove_empty(array_unique($email_white_list));
								jconf::set('email_white_list',$email_white_list);
			} else {
				jconf::set('email_white_list','');
			}
			unset($this->Post['email_white_list']);
		}

		if(isset($this->Post['register']['ip'])) {
			$register = jconf::get('register');
			$register['ip']['time_val'] = max(0, (int) $this->Post['register']['ip']['time_val']);
			$register['ip']['time_unit'] = $this->Post['register']['ip']['time_unit'];
			$register['ip']['limit'] = max(0, (int) $this->Post['register']['ip']['limit']);
			$time_unit_config = jconf::get('time_unit');
			$register['ip']['time'] = (int) ($register['ip']['time_val'] * ($time_unit_config[$register['ip']['time_unit']]['unit']));
			$register['ip']['time_html'] = ($register['ip']['time_val']) . ($time_unit_config[$register['ip']['time_unit']]['name']);
			$ip_white_list = '';
			if($this->Post['register']['ip']['white_list']) {
				$ip_white_list = explode("\n", $this->Post['register']['ip']['white_list']);
				foreach($ip_white_list as $k=>$v) {
					$ip_white_list[$k] = trim($v);
				}
				$ip_white_list = array_remove_empty(array_unique($ip_white_list));
			}
			$register['ip']['white_list'] = $ip_white_list;
			jconf::set('register', $register);

			$this->Post['config']['register_check_ip_enable'] = ($register['ip']['time'] > 0 && $register['ip']['limit'] > 0) ? 1 : 0;

			unset($this->Post['register']['ip']);
		}

		if(isset($this->Post['config']['site_name']) && empty($this->Post['config']['site_name'])) {
			$this->Messager("修改出现错误,站点名称不能为空");
		}

		if(isset($this->Post['config']['normal_default_role_id']) && empty($this->Post['config']['normal_default_role_id'])) {
			$this->Messager("修改出现错误,请先选择一个角色");
		}


				if(isset($this->Post['config']['thumbwidth'])) $this->Post['config']['thumbwidth'] = min(300,max(30,(int) $this->Post['config']['thumbwidth']));
		if(isset($this->Post['config']['thumbheight'])) $this->Post['config']['thumbheight'] = min(300,max(30,(int) $this->Post['config']['thumbheight']));
		if(isset($this->Post['config']['watermark_position'])) $this->Post['config']['watermark_position'] = (int) $this->Post['config']['watermark_position'];
				if(isset($this->Post['config']['image_size'])) {
			$this->Post['config']['image_size'] = max(10, min(51200, (int) $this->Post['config']['image_size']));
			$this->Post['config']['image_size_limit'] = $this->Post['config']['image_size'] * 1024;
		}
				if(isset($this->Post['config']['image_uploadify_queue_size_limit'])) $this->Post['config']['image_uploadify_queue_size_limit'] = min(9,max(2,(int) $this->Post['config']['image_uploadify_queue_size_limit']));

		if(isset($this->Post['seccode_setting']))
		{
			$this->Post['config']['seccode_login'] = $this->Post['config']['seccode_login'] ? 1 : 0;
			$this->Post['config']['seccode_register'] = $this->Post['config']['seccode_register'] ? 1 : 0;
			$this->Post['config']['seccode_password'] = $this->Post['config']['seccode_password'] ? 1 : 0;
			$this->Post['config']['seccode_publish'] = $this->Post['config']['seccode_publish'] ? 1 : 0;
			$this->Post['config']['seccode_comment'] = $this->Post['config']['seccode_comment'] ? 1 : 0;
			$this->Post['config']['seccode_forward'] = $this->Post['config']['seccode_forward'] ? 1 : 0;
			$this->Post['config']['seccode_sms'] = $this->Post['config']['seccode_sms'] ? 1 : 0;
			$this->Post['config']['seccode_no_email'] = $this->Post['config']['seccode_no_email'] ? 1 : 0;
			$this->Post['config']['seccode_no_photo'] = $this->Post['config']['seccode_no_photo'] ? 1 : 0;
			$this->Post['config']['seccode_no_vip'] = $this->Post['config']['seccode_no_vip'] ? 1 : 0;

						$this->Post['config']['seccode_purviews'] = array();
			if(is_array($this->Post['config']['seccode_purview']) && count($this->Post['config']['seccode_purview'])) {
				foreach($this->Post['config']['seccode_purview'] as $__spid) {
					$__spid = jfilter($__spid, 'int');
					if($__spid > 0) {
						$this->Post['config']['seccode_purviews'][$__spid] = $__spid;
					}
				}
			}
						$this->Post['config']['seccode_enable'] = $this->Post['seccode_enable'] ? (int)$this->Post['seccode_enable'] : 0;
			if($this->Post['config']['seccode_enable'] == 1){
				$this->Post['config']['seccode_login'] = $this->Post['config']['seccode_logins'] ? 1 : 0;
				$this->Post['config']['seccode_register'] = $this->Post['config']['seccode_registers'] ? 1 : 0;
				$this->Post['config']['seccode_password'] = $this->Post['config']['seccode_passwords'] ? 1 : 0;
			}

			$this->Post['config']['seccode_purview'] = is_array($this->Post['config']['seccode_purview']) ? implode(',',$this->Post['config']['seccode_purview']) : '';
			$this->Post['config']['seccode_pri_key'] = $this->Post['config']['seccode_pri_key'] ? $this->Post['config']['seccode_pri_key'] : $this->yxm_pri_key;
			$this->Post['config']['seccode_pub_key'] = $this->Post['config']['seccode_pub_key'] ? $this->Post['config']['seccode_pub_key'] : $this->yxm_pub_key;

			unset($this->Post['seccode_setting']);
		}


		if(isset($this->Post['config']['is_qmd'])) {
			$this->Post['config']['is_qmd'] = $this->Post['config']['is_qmd'] ? 1 : 0;
			if($this->Post['config']['is_qmd']) {
				if(!$this->Post['config']['qmd_file_url'] || !jmkdir($this->Post['config']['qmd_file_url'])) {
					$this->Post['config']['qmd_file_url'] = 'images/qmd/';
				}
				if(!$this->Post['config']['qmd_fonts_url'] || !file_exists($this->Post['config']['qmd_fonts_url'])) {
					$this->Post['config']['is_qmd'] = 0;

					$this->Messager('请上传签名档必须的字体文件 ' . $this->Post['config']['qmd_fonts_url']);
				}
			}
			$this->Post['config']['qmd_link_display'] = ($this->Post['config']['qmd_link_display'] ? 1 : 0);
		}


				if(isset($this->Post['config']['follow_limit'])) {
			$this->Post['config']['follow_limit'] = max(0, (int) $this->Post['config']['follow_limit']);
		}


				if(isset($this->Post['config']['topic_input_length'])) {
			$this->Post['config']['topic_input_length'] = max(0, (int) $this->Post['config']['topic_input_length']);
		}


				if(isset($this->Post['config']['gzip'])) {
			$this->Post['config']['gzip'] = ($this->Post['config']['gzip'] && function_exists('ob_gzhandler') ? 1 : 0);
		}


				if(isset($this->Post['config']['reply_mode_normal'])) {
			$this->Post['config']['reply_mode_normal'] = ($this->Post['config']['reply_mode_normal'] ? 1 : 0);
		}




		$new_config = $config = jconf::core_settings();

		foreach($this->Post['config'] as $k=>$v)
		{
			if(isset($this->Post['config'][$k]) && !is_null($v))
			{
				$new_config[$k] = $v;
			}
		}

		$new_config['topic_cut_length'] = (int) $new_config['topic_cut_length'];
		if($new_config['topic_cut_length'] > 200 || $new_config['topic_cut_length'] < 10) {
			$new_config['topic_cut_length'] = 140;
		}

		if(!$new_config['wap_url']) {
			$new_config['wap_url'] = $new_config['site_url'] . '/wap';
		}

		if(!$new_config['mobile_url']) {
			$new_config['mobile_url'] = $new_config['site_url'] . '/mobile';
		}

		$new_config['extra_domains'] = array();
		if($new_config['extra_domain']) {
			$_arrs = explode("\n", $new_config['extra_domain']);
			foreach($_arrs as $v) {
				$v = trim($v);
				$vl = strlen($v);
				if($vl > 3 && $vl < 100) {
					$new_config['extra_domains'][] = strtolower($v);
				}
			}
			$new_config['extra_domain'] = implode("\n", $new_config['extra_domains']);
		}

		$new_config['copyright'] = jstripslashes($new_config['copyright']);
		$new_config['tongji'] = jstripslashes($new_config['tongji']);
		$new_config['topic_view_share_code'] = jstripslashes($new_config['topic_view_share_code']);
		$new_config['regclosemessage'] = jstripslashes($new_config['regclosemessage']);

		$result = jconf::set($new_config);

		if($result!=false)
		{
			$this->Messager("配置修改成功");
		}
		else
		{
			$this->Messager("配置修改失败");
		}

	}

	function ModifyImage()
	{
		$options = array(
			'tpl' => 'admin/setting_image',
		);
		$this->ModifyNormal($options);
	}
	function ModifyQMD()
	{
		$options = array(
			'tpl' => 'admin/setting_qmd',
		);
		$this->ModifyNormal($options);
	}

	
	function ModifyCredits()
	{
		$action="admin.php?mod=setting&code=domodify_credits";

				$_extcredits = array
		(
			'extcredits1'=>1,
			'extcredits2'=>1,
			'extcredits3'=>1,
			'extcredits4'=>1,
			'extcredits5'=>1,
			'extcredits6'=>1,
			'extcredits7'=>1,
			'extcredits8'=>1,
		);

		$credits = jconf::get('credits');

		if (!$credits['ext'])
		{
			$credits['ext']['extcredits1']['name'] = '威望';
			$credits['ext']['extcredits2']['name'] = '金钱';
			$credits['ext']['extcredits3']['name'] = '贡献';
		}


				if (!$credits['formula'])
		{
			$credits['formula'] = 'topic_count+extcredits1*2+extcredits2+extcredits3';
		}
		else
		{
			$credits['formula'] = str_replace(array('$member[topic_count]','$member[extcredits1]','$member[extcredits2]','$member[extcredits3]','$member[extcredits4]','$member[extcredits5]','$member[extcredits6]','$member[extcredits7]','$member[extcredits8]',),array('topic_count','extcredits1','extcredits2','extcredits3','extcredits4','extcredits5','extcredits6','extcredits7','extcredits8',),$credits['formula']);
		}


				include template('admin/setting_credits');
	}
	
	function DoModifyCredits()
	{
		$_credits = $this->Post['credits'];

		$credits = array();

				$_extcredits = array
		(
			'extcredits1'=>1,
			'extcredits2'=>1,
			'extcredits3'=>1,
			'extcredits4'=>1,
			'extcredits5'=>1,
			'extcredits6'=>1,
			'extcredits7'=>1,
			'extcredits8'=>1,
		);

		$extcredits_enable = 0;

		$credits['ext'] = array();

		if ($_credits['ext'])
		{
			foreach ($_credits['ext'] as $_k=>$_v)
			{
				if (isset($_extcredits[$_k]))
				{
					$_ico = $_v['ico'];
					$_name = trim($_v['name']);
					$_unit = $_v['unit'];
					$_default = (int) $_v['default'];
					$_enable = (($_v['enable'] && $_name) ? 1 : 0);

										if ($_enable)
					{
						$row = array
						(
							'enable' => $_enable,
							'ico' => $_ico,
							'name' => $_name,
							'unit' => $_unit,
							'default' => $_default,
						);

						$credits['ext'][$_k] = $row;
					}

					$extcredits_enable += $_enable;
				}
			}
		}

		$extcredits_enable = ($extcredits_enable ? 1 : 0);


		$credits['formula'] = str_replace(array('topic_count','extcredits1','extcredits2','extcredits3','extcredits4','extcredits5','extcredits6','extcredits7','extcredits8',),array('$member[topic_count]','$member[extcredits1]','$member[extcredits2]','$member[extcredits3]','$member[extcredits4]','$member[extcredits5]','$member[extcredits6]','$member[extcredits7]','$member[extcredits8]',),$_credits['formula']);
		$credits['formula'] = str_replace(array(';', '"', "'"), '', $credits['formula']);


		jconf::set('credits',$credits);

				if ($extcredits_enable!=$this->Config['extcredits_enable'])
		{
			$config = array();
			$config['extcredits_enable'] = $extcredits_enable;

			jconf::update($config);
		}


		$this->Messager("修改成功");

	}

	function ModifyCreditsRule()
	{
		if (!$this->Config['extcredits_enable'])
		{
			$this->Messager("请先开启扩展积分功能","admin.php?mod=setting&code=modify_credits");
		}

		$credits = jconf::get('credits');

		$_extcredits = array();
		foreach ($credits['ext'] as $_k=>$_v)
		{
			if($_v['enable'] && $_v['name'])
			{
				$_extcredits[$_k] = $_v;
			}
		}
		$_extcredits_count = count($_extcredits);

		$_cycletypes = array
		(
		0 => '一次性',
		1 => '每天',
		2 => '整点',
		3 => '间隔分钟',
		4 => '不限周期',
		);

		if ('list_credits_rule'==$this->Code)
		{
			$sql = "select * from ".TABLE_PREFIX."credits_rule where action<>'down_my_attach' and action<>'unconvert' order by rid";
			$query = $this->DatabaseHandler->Query($sql);
			while (false != ($row = $query->GetRow()))
			{
				$row['cycletype'] = $_cycletypes[$row['cycletype']];

				if(!$row['rewardnum'])
				{
					$row['rewardnum'] = '不限次数';
				}
				if(strpos($row['action'],'_C')!==false || strpos($row['action'],'_D')!==false){					$row['related'] = jlogic('channel')->id2subject($row['related']);
				}

				$rules[$row['rid']] = $row;
			}
		}
		elseif ('modify_credits_rule'==$this->Code)
		{
			$rid = max(0,(int) $this->Get['rid']);
			$sql = "select * from ".TABLE_PREFIX."credits_rule where action<>'down_my_attach' and action<>'unconvert' and rid='$rid'";
			$query = $this->DatabaseHandler->Query($sql);
			$rule_info = $query->GetRow();
			if($rule_info){
				$_related = substr($rule_info['action'],0,2);
				$rule_info["cycletype_{$rule_info['cycletype']}_checked"] = " checked ";
				if('_C'==$_related || '_D'==$_related){					$channels = jlogic('channel')->get_select_channel();
				}
			}
		}


		include(template('admin/setting_credits_rule'));
	}
	function DoModifyCreditsRule()
	{
		if (!$this->Config['extcredits_enable'])
		{
			$this->Messager("请先开启扩展积分功能","admin.php?mod=setting&code=modify_credits");
		}

		$credits = jconf::get('credits');

		$_extcredits = array();
		foreach ($credits['ext'] as $_k=>$_v)
		{
			if($_v['enable'] && $_v['name'])
			{
				$_extcredits[$_k] = $_v;
			}
		}

		$rid = max(0,(int) get_param('rid'));
		if ($rid>0)
		{
			$sql = "select * from ".TABLE_PREFIX."credits_rule where action<>'down_my_attach' and action<>'unconvert' and `rid`='$rid'";
			$query = $this->DatabaseHandler->Query($sql);
			$rule_info = $query->GetRow();
			if (!$rule_info)
			{
				$this->Messager("积分规则已经不存在了");
			}

			if($this->Post['rulesubmit'])
			{
				$_rule = $this->Post['rule'];

				$rule = array();
				
				$rule['cycletype'] = min(4,max(0,(int) $_rule['cycletype']));
				$rule['cycletime'] = (int) $_rule['cycletime'];
				$rule['rewardnum'] = (int) $_rule['rewardnum'];
				foreach ($_extcredits as $__k=>$__v)
				{
					$rule[$__k] = min(999,max(-999,(int) $_rule[$__k]));
				}
				if($_rule['extcredits']){
					foreach ($_extcredits as $__k=>$__v)
					{
						if($__k==$_rule['extcredits']){
							$rule[$__k] = 1;
						}else{
							$rule[$__k] = 0;
						}
					}
				}else{
					foreach ($_extcredits as $__k=>$__v)
					{
						$rule[$__k] = min(999,max(-999,(int) $_rule[$__k]));
					}
				}

								if ('_'==$rule_info['action']{0}) {
					$rule['related'] = '';
					$rule['action'] = "_" . $rule_info['action']{1};

					if($_rule['related']) {
						$sql = false;
						if ('T'==$rule_info['action']{1}) {
							$sql = "select `id`,`name` from `".TABLE_PREFIX."tag` where `name`='{$_rule['related']}'";
						} elseif ('U'==$rule_info['action']{1}) {
							$sql = "select `uid`,`username` from `".TABLE_PREFIX."members` where `nickname`='{$_rule['related']}'";
						} elseif ('C'==$rule_info['action']{1} || 'D'==$rule_info['action']{1}) {
							$sql = "select `ch_name`,`ch_id` from `".TABLE_PREFIX."channel` where `ch_id`='{$_rule['related']}'";
						}

						if($sql && (DB::fetch_first($sql))) {
							$rule['related'] = $_rule['related'];
							$rule['action'] = "_".$rule_info['action']{1}.crc32($rule['related']);
						} else {
							$this->Messager($_rule['related'] . ' 不存在，请返回换一个。');
						}
					}
				}

				$sets = array();
				foreach ($rule as $_k=>$_v)
				{
					if (isset($rule_info[$_k]) && $_v!=$rule_info[$_k])
					{
						$sets[$_k] = "`$_k`='$_v'";
					}
				}

				if ($sets)
				{
					$sql = "update ".TABLE_PREFIX."credits_rule set ".implode(" , ",$sets)." where `rid`='$rid'";
					$this->DatabaseHandler->Query($sql);
										if('attach_down'==$rule_info['action']){
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."credits_rule set ".implode(" , ",$sets)." where `action`='down_my_attach'");
					}
										if('convert'==$rule_info['action']){
						$this->DatabaseHandler->Query("update ".TABLE_PREFIX."credits_rule set ".implode(" , ",$sets)." where `action`='unconvert'");
					}
										if($rule_info['action']=='convert'){
						$de_credits_rule = array('extcredits1','extcredits2','extcredits3','extcredits4','extcredits5','extcredits6','extcredits7','extcredits8');
						foreach ($rule as $_mk=>$_mv){
							if(in_array($_mk,$de_credits_rule) && $_mv > 0){
								$set_mall_credits = $_mk;
							}
						}
						$setmall = jconf::get('mall');$setcredits = jconf::get('credits');
						$setmall['credits'] = $set_mall_credits;
						$setmall['credits_name'] = $setcredits['ext'][$set_mall_credits]['name'];
						jconf::set('mall', $setmall);
					}
				}
			}
			elseif('del'==get_param('act'))
			{
				if('_'!=$rule_info['action']{0} && $rule_info['system'])
				{
					$this->Messager("不允许删除此规则",null);
				}

				$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."credits_rule where `rid`='{$rid}'");
			}
		}
		else
		{
			$_new_rule_var = $this->Post['new_rule_var'];
			$_accpet_rules = array('_T'=>'发布指定话题','_U'=>'关注指定用户','_S'=>'签到祝福','_C'=>'发到指定频道','_D'=>'删除频道微博');
			if($this->Post['newrulesubmit'] && isset($_accpet_rules[$_new_rule_var]))
			{
				$sql = "select * from ".TABLE_PREFIX."credits_rule where `action`='{$_new_rule_var}' limit 1";
				$query = $this->DatabaseHandler->Query($sql);
				$row = $query->GetRow();
				if(!$row)
				{
					$sql = "insert into ".TABLE_PREFIX."credits_rule (`action`,`rulename`,`cycletype`,`rewardnum`) values ('{$_new_rule_var}','{$_accpet_rules[$_new_rule_var]}','1','1')";
					$this->DatabaseHandler->Query($sql);
					$_new_rule_id = $this->DatabaseHandler->Insert_ID();
					if($_new_rule_id < 1)
					{
						$this->Messager("新规则增加失败");
					}
				}
				else
				{
					$_new_rule_id = $row['rid'];
				}


				$this->Messager(null,"admin.php?mod=setting&code=modify_credits_rule&rid={$_new_rule_id}");
			}


			$_rule = $this->Post['rule'];

			foreach ($_rule as $_k=>$_v)
			{
				$_sets = array();
				foreach ($_v as $__k=>$__v)
				{
					if (isset($_extcredits[$__k]))
					{
						$_sets[$__k] = "`$__k`=".min(999,max(-999,(int) $__v));
					}
				}

				$_k = (int) $_k;
				if ($_sets && $_k>0)
				{
					$sql = "update ".TABLE_PREFIX."credits_rule set ".implode(" , ",$_sets)." where `rid`='$_k'";
					$this->DatabaseHandler->Query($sql);
				}
			}
		}


		jlogic('credits')->rule_conf(true);


		$this->Messager("操作成功","admin.php?mod=setting&code=list_credits_rule");

	}


	function ModifySina()
	{
		$sina = jconf::get('sina');

		if (!$sina)
		{
			$sina = array
			(
				'enable' => 1,
				'oauth2_enable' => 0,
				'app_key' => '3015840342',
				'app_secret' => '484175eda3cf0da583d7e7231c405988',
				'is_account_binding' => 1,
				'is_synctopic_toweibo' => 1,
				'is_syncreply_toweibo' => 1,
				'is_rebutton_display' => 1,
				'reg_pwd_display' => 1,
				'is_sync_face' => 1,
				'is_upload_image' => 1,
				'wbx_share_time' => 15,
                'is_synctopic_tojishigou' => 0,
                'is_syncreply_tojishigou' => 0,
                'is_syncimage_tojishigou' => 0,
                'syncweibo_tojishigou_time' => 180,
			);
		}

		if(!isset($sina['syncweibo_tojishigou_time'])) $sina['syncweibo_tojishigou_time'] = 180;

		$sina_enable_radio = $this->jishigou_form->YesNoRadio('sina[enable]', (int) ($sina['enable'] && $this->Config['sina_enable']));
		$sina_oauth2_enable_radio = $this->jishigou_form->YesNoRadio('sina[oauth2_enable]', (int) ($sina['oauth2_enable']));
		$sina_is_account_binding_radio = $this->jishigou_form->YesNoRadio('sina[is_account_binding]', (int) $sina['is_account_binding']);
		$sina_is_synctopic_toweibo_radio = $this->jishigou_form->YesNoRadio('sina[is_synctopic_toweibo]', (int) $sina['is_synctopic_toweibo']);
		$sina_is_syncreply_toweibo_radio = $this->jishigou_form->YesNoRadio('sina[is_syncreply_toweibo]', (int) $sina['is_syncreply_toweibo']);
		$sina_reg_pwd_display_radio = $this->jishigou_form->YesNoRadio('sina[reg_pwd_display]', (int) $sina['reg_pwd_display']);
		$sina_is_sync_face_radio = $this->jishigou_form->YesNoRadio('sina[is_sync_face]', (int) $sina['is_sync_face']);
		$sina_is_upload_image_radio = $this->jishigou_form->YesNoRadio('sina[is_upload_image]', (int) $sina['is_upload_image']);

		$sina_is_synctopic_tojishigou_radio = $this->jishigou_form->YesNoRadio('sina[is_synctopic_tojishigou]', (int) $sina['is_synctopic_tojishigou']);
		$sina_is_syncreply_tojishigou_radio = $this->jishigou_form->YesNoRadio('sina[is_syncreply_tojishigou]', (int) $sina['is_syncreply_tojishigou']);
		$sina_is_syncimage_tojishigou_radio = $this->jishigou_form->YesNoRadio('sina[is_syncimage_tojishigou]', (int) $sina['is_syncimage_tojishigou']);


		include(template('admin/setting_sina'));
	}

	function DoModifySina()
	{
		
		$check_result = $this->_sinaCheckEnv();
		if($check_result)
		{
			jconf::update('sina_enable', 0);
			
			$this->Messager($check_result,null);
		}

		$sina_default = jconf::get('sina');

		$sina = array();
		$_tmps = $_POST['sina'];

		
		$sina['enable'] = ($_tmps['enable'] ? 1 : 0);
		$sina['oauth2_enable'] = ($_tmps['oauth2_enable'] ? 1 : 0);


		
		$sina['app_key'] = trim($_tmps['app_key']);
		$sina['app_secret'] = trim($_tmps['app_secret']);
		if (!(preg_match('~^[a-z0-9]+$~i',$sina['app_key'].$sina['app_secret'])))
		{
			$this->Messager("请填写正确的 APP KEY 和 APP SECRET",null);
		}


		
		$__XWB_SET = array();
		$_set_file = ROOT_PATH . 'include/ext/xwb/set.data.php';
		include($_set_file);
		$__XWB_SET_DEFAULT = $__XWB_SET;
		foreach ($__XWB_SET as $__k=>$__v)
		{
			if (isset($_tmps[$__k]))
			{
				$sina[$__k] = (int) $_tmps[$__k];

				$__XWB_SET[$__k] = $sina[$__k];
			}
		}
		if ($__XWB_SET!=$__XWB_SET_DEFAULT)
		{
			$set_data = "<?php\n%s=%s;\n?>";
			if(!(jio()->WriteFile($_set_file, sprintf($set_data, '$__XWB_SET', var_export($__XWB_SET,true)))))
			{
				$this->Messager("配置文件<b>set.data.php</b>写入失败",null);
			}
		}


		
		if ($sina['enable']!=$this->Config['sina_enable']) {
			jconf::update('sina_enable', $sina['enable']);
		}

		if ($sina != $sina_default) {
			jconf::set('sina',$sina);

			if($sina['enable'] && $sina['app_key'] != $sina_default['app_key']) {
				DB::query('TRUNCATE TABLE ' . DB::table('xwb_bind_info'));
				jclass('misc')->update_account_bind_info(0, '', '', 1);
			}
		}


		$this->Messager("配置修改成功");
	}

	function _sinaCheckEnv()
	{
		jfunc('sina_env');

		return sina_env();
	}

	function ModifyQQWB()
	{
		$qqwb = jconf::get('qqwb');

		if (!$qqwb)
		{
			$qqwb = array
			(
				'enable' => 1,
				'app_key' => '8c84c76e55d6491a991d8b568ad15209',
				'app_secret' => '949d659ce5e89230c5bac10e17bc2ba8',
				'is_account_binding' => 1,
				'is_synctopic_toweibo' => 1,
				'is_syncreply_toweibo' => 1,
				'is_rebutton_display' => 1,
				'reg_pwd_display' => 1,
				'is_sync_face' => 1,
				'is_upload_image' => 1,
				'wbx_share_time' => 15,
                'is_synctopic_tojishigou' => 0,
                'is_syncreply_tojishigou' => 0,
                'is_syncimage_tojishigou' => 0,
                'syncweibo_tojishigou_time' => 180,
			);
		}

		if(!isset($qqwb['syncweibo_tojishigou_time'])) $qqwb['syncweibo_tojishigou_time'] = 180;

		$qqwb_enable_radio = $this->jishigou_form->YesNoRadio('qqwb[enable]', (int) ($qqwb['enable'] && $this->Config['qqwb_enable']));
		$qqwb_is_account_binding_radio = $this->jishigou_form->YesNoRadio('qqwb[is_account_binding]', (int) $qqwb['is_account_binding']);
		$qqwb_is_synctopic_toweibo_radio = $this->jishigou_form->YesNoRadio('qqwb[is_synctopic_toweibo]', (int) $qqwb['is_synctopic_toweibo']);
		$qqwb_is_syncreply_toweibo_radio = $this->jishigou_form->YesNoRadio('qqwb[is_syncreply_toweibo]', (int) $qqwb['is_syncreply_toweibo']);
		$qqwb_reg_pwd_display_radio = $this->jishigou_form->YesNoRadio('qqwb[reg_pwd_display]', (int) $qqwb['reg_pwd_display']);
		$qqwb_is_sync_face_radio = $this->jishigou_form->YesNoRadio('qqwb[is_sync_face]', (int) $qqwb['is_sync_face']);
		$qqwb_is_upload_image_radio = $this->jishigou_form->YesNoRadio('qqwb[is_upload_image]', (int) $qqwb['is_upload_image']);

		$qqwb_is_synctopic_tojishigou_radio = $this->jishigou_form->YesNoRadio('qqwb[is_synctopic_tojishigou]', (int) $qqwb['is_synctopic_tojishigou']);
		$qqwb_is_syncreply_tojishigou_radio = $this->jishigou_form->YesNoRadio('qqwb[is_syncreply_tojishigou]', (int) $qqwb['is_syncreply_tojishigou']);
		$qqwb_is_syncimage_tojishigou_radio = $this->jishigou_form->YesNoRadio('qqwb[is_syncimage_tojishigou]', (int) $qqwb['is_syncimage_tojishigou']);


		include(template('admin/setting_qqwb'));
	}
	function DoModifyQQWB()
	{
		
		$check_result = $this->_qqwbCheckEnv();
		if($check_result)
		{
			jconf::update('qqwb_enable', 0);
			
			$this->Messager($check_result,null);
		}

		$qqwb_default = jconf::get('qqwb');

		$qqwb = $_POST['qqwb'];

		
		$qqwb['enable'] = ($qqwb['enable'] ? 1 : 0);


		
		if (!(preg_match('~^[a-z0-9]+$~i',$qqwb['app_key'].$qqwb['app_secret'])))
		{
			$this->Messager("请填写正确的 APP KEY 和 APP SECRET",null);
		}


		
		if ($qqwb['enable']!=$this->Config['qqwb_enable'])
		{
			jconf::update('qqwb_enable', $qqwb['enable']);
		}


		if ($qqwb != $qqwb_default) {
			jconf::set('qqwb',$qqwb);

			if($qqwb['enable'] && $qqwb['app_key'] != $qqwb_default['app_key']) {
				DB::query('TRUNCATE TABLE ' . DB::table('qqwb_bind_info'));
				jclass('misc')->update_account_bind_info(0, '', '', 1);
			}
		}


		$this->Messager("配置修改成功");
	}
	function _qqwbCheckEnv()
	{
		jfunc('qqwb_env');

		return qqwb_env();
	}


	function ModifyMeta()
	{
		$options = array(
			'tpl' => 'admin/setting_meta',
		);
		$this->ModifyNormal($options);
	}
	
	function ModifyRewrite()
	{
				$mode_list=array
		(
			''=>array('name'=>"不启用静态化",'value'=>""),
			'stand'=>array('name'=>"标准Rewrite模式",'value'=>"stand"),
			'apache_path'=>array('name'=>"路径模式",'value'=>"apache_path"),
				);

				$_rewrite = jconf::get('rewrite');
		if(!$_rewrite) {
			$_rewrite = array (
			  'mode' => '',
			  'abs_path' => '/',
			  'arg_separator' => '/',
			  'var_separator' => '-',
			  'prepend_var_list' =>
			array (
			0 => 'mod',
			1 => 'code',
			),
			  'var_replace_list' =>
			array (
			    'mod' =>
			array (
			),
			),
			  'value_replace_list' =>
			array (
			    'mod' =>
			array (
					'topic' => array_rand(array('miniblog'=>1,'myblog'=>1,'blog'=>1,'topics'=>1,'weibo'=>1,)),
					'tag' => array_rand(array('keywords'=>1,'channels'=>1,'class'=>1,'tags'=>1,)),
					'profile' => array_rand(array('profiles'=>1,'personals'=>1,)),
					'member' => array_rand(array('users'=>1,'members'=>1,)),
					'plugin' => array_rand(array('plugins'=>1, 'extends'=>1, 'expands'=>1, 'applications'=>1, 'packages'=>1, )),
					'channel' => array_rand(array('category'=>1,'subject'=>1,'sort'=>1,'channels'=>1,)),
			),
			),
			  'gateway' => '',
			);
			jconf::set('rewrite', $_rewrite);
		}

				$mode_select=$this->jishigou_form->Select('mode',$mode_list,$_rewrite['mode']);

		$mod_list=array(
			"<b>徽博</b><br>（可以填写微博相关的词，<br>如：miniblog weibo myblog等）" =>"topic",
			"<b>标签</b><br>（可以填写标签相关的词，<br>如：keywords tags class等）" =>"tag",
			"<b>设置页</b><br>(个人设置页面)" => "profile",
			"<b>注册页</b><br>（可以填写member相关的词，<br>如：members users等）" => "member",
			"<b>频道页</b><br>（可以填写相关的词，<br>如：category sort subject）" => "channel",
			"<b>插件</b><br>（如开启标准模式，<br>请设置一个不是plugin的值<br>如：plugins extends expands 等）" => "plugin",
		);
		$mod_alias=$_rewrite['value_replace_list']['mod'];

		include template('admin/setting_rewrite');

	}

	function DoModifyRewrite()
	{
		$reserved_keys = array(
            'api' => 1,
            'backup' => 1,
            'cache' => 1,
            'data' => 1,
            'log' => 1,
            'iis_rewrite' => 1,
            'images' => 1,
            'include' => 1,
            'install' => 1,
            'modules' => 1,
            'setting' => 1,
            'templates' => 1,
            'uc_server' => 1,
            'uc_client' => 1,
            'wap' => 1,
            'blacklist' => 1,
            'get_password' => 1,
            'imjiqiren' => 1,
            'login' => 1,
            'master' => 1,
            'member' => 1,
            'other' => 1,
            'pm' => 1,
            'profile' => 1,
            'report' => 1,
            'search' => 1,
            'settings' => 1,
            'share' => 1,
            'show' => 1,
            'tag' => 1,
            'theme' => 1,
            'topic' => 1,
            'url' => 1,
            'user_tag' => 1,
            'weather' => 1,
            'xwb' => 1,
            'htaccess' => 1,
            'admin' => 1,
            'ajax' => 1,
            'changelog' => 1,
            'favicon' => 1,
            'index' => 1,
            'license' => 1,
            'public' => 1,
            'robots' => 1,
            'server' => 1,
            'test' => 1,
            'upgrade' => 1,
        	'plugin' => 1,
			'channel' => 1,
			'attach' => 1,
			'live' => 1,
			'talk' => 1,
			'company' => 1,
			'department' => 1,
			'job' => 1,
		);

		$mod_alias=array();
		foreach ((array)$this->Post['mod_alias'] as $old_name=>$new_name)
		{
			$new_name=trim($new_name);
			if(!empty($new_name) && $old_name!=$new_name && preg_match("~^[A-Za-z0-9_]+$~",$new_name) && !isset($reserved_keys[$new_name]))
			{
				$mod_alias[$old_name]=$new_name;
			}
		}
				$_rewrite = jconf::get('rewrite');

				if(isset($this->Post['_rewrite_extention']) && (!$this->Post['_rewrite_extention'] || preg_match('~^[\w\d\/\-\_\.]+$~', $this->Post['_rewrite_extention']))) {
			$_rewrite['extention'] = $this->Post['_rewrite_extention'];
		}
		$_rewrite['mode']=$this->Post['mode'];
		$_rewrite['abs_path']=preg_replace("/\/+/",'/',str_replace("\\",'/',dirname($_SERVER['PHP_SELF']))."/");

		$gateway=array("stand"=>"","apache_path"=>"index.php/","normal"=>"?",""=>"");
		$_rewrite['gateway']=$gateway[$_rewrite['mode']];

		if(!empty($mod_alias))
		{
			$_rewrite['value_replace_list']['mod']=$mod_alias;
		}
		else
		{
			unset($_rewrite['value_replace_list']['mod']);
		}

		jconf::set('rewrite', $_rewrite);

		if($_rewrite['mode']=='stand') {
			$this->_writeHtaccess($_rewrite['abs_path']);
		}


		jconf::update('rewrite_enable', ($_rewrite['mode'] ? 1 : 0));

		cache_clear();

		$this->Messager("修改成功,正在更新缓存");
	}

	function ModifyFilter()
	{
		$filter=jconf::get('filter');
		$enable_radio=$this->jishigou_form->YesNoRadio("filter[enable]",(int)$filter['enable']);
		$keyword_disable_radio = $this->jishigou_form->YesNoRadio("filter[keyword_disable]", (int) $filter['keyword_disable']);



		include(template("admin/setting_filter"));
	}
	function DoModifyFilter()
	{
				$this->Post['filter']['keywords']=trim($this->Post['filter']['keywords']);
		$keywords=str_replace(array("\s","\r\n","\n","\\|"),"|",$this->Post['filter']['keywords']);
		if ($keywords)
		{
			$tmp_keyword_list=explode("|",$keywords);
			$keyword_list = array();
			foreach($tmp_keyword_list as $k=>$v) {
				$v = trim($v);
				if(($vl=strlen($v))>2 && $vl<40) {
					$keyword_list[$v] = $v;
				}
			}
			sort($keyword_list);
			$this->Post['filter']['keyword_list'] = $keyword_list;
			$this->Post['filter']['keywords']=implode("\r\n", $keyword_list);
		}

				$this->Post['filter']['verifys']=trim($this->Post['filter']['verifys']);
		$verifys=str_replace(array("\s","\r\n","\n","\\|"),"|",$this->Post['filter']['verifys']);
		if ($verifys)
		{
			$tmp_verify_list=explode("|",$verifys);
			$verify_list = array();
			foreach($tmp_verify_list as $k=>$v) {
				$v = trim($v);
				if(($vl=strlen($v))>2 && $vl<40) {
					$verify_list[$v] = $v;
				}
			}
			sort($verify_list);
			$this->Post['filter']['verify_list'] = $verify_list;
			$this->Post['filter']['verifys']=implode("\r\n", $verify_list);
		}

				$this->Post['filter']['replaces']=trim($this->Post['filter']['replaces']);
		$replaces=str_replace(array("\s","\r\n","\n","\\|"),"|",$this->Post['filter']['replaces']);
		if ($replaces)
		{
			$tmp_replace_list=explode("|",$replaces);
			$replaces = $replace_list = array();
			foreach($tmp_replace_list as $k=>$v) {
				$v = trim($v);
				if(($vl=strlen($v))>2 && $vl<180) {
					$_s = $_r = '';
					list($_s, $_r) = explode("=", $v);
					if($_s)
					{
						$replace_list[$_s] = $_r;

						$v = "{$_s}";
						if($_r)
						{
							$v = $v . '=' . $_r;
						}
						$replaces[$v] = $v;
					}
				}
			}
			sort($replaces);
			$this->Post['filter']['replace_list'] = $replace_list;
			$this->Post['filter']['replaces']=implode("\r\n", $replaces);
		}

						$this->Post['filter']['shield']=trim($this->Post['filter']['shield']);
		$shield=str_replace(array("\s","\r\n","\n","\\|"),"|",$this->Post['filter']['shield']);
		if ($shield)
		{
			$tmp_shield_list=explode("|",$shield);
			$shield_list = array();
			foreach($tmp_shield_list as $k=>$v) {
				$v = trim($v);
				if(($vl=strlen($v))>2 && $vl<40) {
					$shield_list[$v] = $v;
				}
			}
			sort($shield_list);
			$this->Post['filter']['shield_list'] = $shield_list;
			$this->Post['filter']['shield']=implode("\r\n", $shield_list);
		}

		jconf::set('filter',$this->Post['filter']);
		$this->Messager("修改成功");
	}

	function ModifyRegister()
	{
		$options = array(
			'tpl' => 'admin/setting_register',
		);
		$this->ModifyNormal($options);
	}

	
	function modifyRegisterGuide(){
		$options = array(
			'tpl' => 'admin/register_guide',
		);
		$this->ModifyNormal($options);
	}
	function ModifyAccess()
	{
		$access=(array)jconf::get('access');
		foreach ($access as $type =>$ips)
		{
			if(!empty($ips))
			{
				$ips=str_replace("|","\n",$ips);
				$access[$type]=stripslashes($ips);
			}
		}
		$action="admin.php?mod=setting&code=domodify_access";

		include template('admin/setting_access');
	}
	function DoModifyAccess()
	{
		$access=(array)$this->Post['access'];
		$access['ipbanned']=trim($access['ipbanned']);
		$access['admincp']=trim($access['admincp']);
		foreach ($access as $type =>$ips)
		{
			if(!empty($ips))
			{
				$ips=preg_replace("/[\r\n]+/i","\n",$ips);
				$_ip_list=explode("\n",$ips);
				$access[$type]=$sep="";
				foreach ($_ip_list as $_ip)
				{
					if(preg_match("/^(\d{1,3}\.){1,3}\d{1,3}\.?$/",$_ip))
					{
						$access[$type].=$sep.str_replace(".","\.",$_ip);
						$sep="|";
					}
				}
			}
		}
				if(!empty($access['ipbanned']) && preg_match("~^({$access['ipbanned']})~",$GLOBALS['_J']['client_ip']))
		{
			$this->Messager("您当前的IP在禁止IP里，无法设置。");
		}
				if(!empty($access['admincp']) && !preg_match("~^({$access['admincp']})~",$GLOBALS['_J']['client_ip']))
		{
			$this->Messager("您当前的IP在不在后台允许的IP里，无法设置。",-1);
		}

		jconf::set('access',$access);


		$config = array();
		$config['access_enable'] = (($access['ipbanned'] || $access['admincp']) ? 1 : 0);
		$config['ipbanned_enable'] = ($access['ipbanned'] ? 1 : 0);

		jconf::update($config);


		$this->Messager("设置成功");
	}

	function ModifySeccode()
	{
		$role_list = array();
		$query = DB::query("select `name`, `id` as `value` from ".DB::table('role')." where `id`!='1' order by `type` desc, `id` asc");
		$v = 0;
		while (false != ($row = DB::fetch($query))) {
			$v = $row['value'];
			$role_list[$v] = $row;
		}
		$purviewhtml = $this->jishigou_form->CheckBox('config[seccode_purview][]', $role_list, explode(',',$this->Config['seccode_purview']));
		$pri_key = $this->Config['seccode_pri_key'] ? $this->Config['seccode_pri_key'] : $this->yxm_pri_key;
		$pub_key = $this->Config['seccode_pub_key'] ? $this->Config['seccode_pub_key'] : $this->yxm_pub_key;
		$options = array(
			'tpl' => 'admin/setting_seccode',
			'purviewhtml' => $purviewhtml,
			'pri_key' =>$pri_key,
			'pub_key' =>$pub_key,
		);
		$this->ModifyNormal($options);
	}

	function ModifySmtp()
	{
		$smtp = jconf::get('smtp');

		$enable[$smtp['enable']] = " checked ";
		
		$action = "admin.php?mod=setting&code=do_modify_smtp";

		include(template('admin/setting_smtp'));
	}
	function DoModifySmtp()
	{
		$smtp_config = jconf::get('smtp');
		$newsmtp = jget('newsmtp');
		#新添加的smtp
		$newsmtp_arr = array();
		if($newsmtp['host']){
			foreach ($newsmtp['host'] as $k => $v) {
				$newsmtp_arr[$k]['host'] = $v;
				$newsmtp_arr[$k]['port'] = $newsmtp['port'][$k];
				$newsmtp_arr[$k]['mail'] = $newsmtp['mail'][$k];
				$newsmtp_arr[$k]['port'] = $newsmtp['port'][$k];
				$newsmtp_arr[$k]['username'] = $newsmtp['username'][$k];
				$newsmtp_arr[$k]['password'] = $newsmtp['password'][$k];
			}
		}

		$smtp = jget('smtp');
		$oldsmtp_arr = array();
		if($smtp){
			foreach ($smtp as $k => $v) {
				$oldsmtp_arr[$k]['host'] = $v['host'];
				$oldsmtp_arr[$k]['port'] = $v['port'];
				$oldsmtp_arr[$k]['mail'] = $v['mail'];
				$oldsmtp_arr[$k]['port'] = $v['port'];
				$oldsmtp_arr[$k]['username'] = $v['username'];

				if('请输入SMTP服务器的帐户密码'==$v['password']) {
					$oldsmtp_arr[$k]['password'] = $smtp_config['smtp'][$k]['password'];
				} else {
					$oldsmtp_arr[$k]['password'] = $v['password'];
				}
			}
		}

		$ids = jget('ids');
		if($ids){
			foreach ($ids as $k => $v) {
				unset($oldsmtp_arr[$v]);
			}
		}

		$new_smtp_config['smtp'] = array_merge($newsmtp_arr,$oldsmtp_arr);
		$new_smtp_config['enable'] = jget('enable');

		jconf::set('smtp',$new_smtp_config);

		$this->Messager("设置成功");

	}

	function ModifyShortcut()
	{
		$action = 'admin.php?mod=setting&code=do_modify_shortcut';
		unset($menu_list);
		include(ROOT_PATH . 'setting/admin_left_menu.php');

		include(template('admin/setting_shortcut'));
	}
	function DoModifyShortcut()
	{
		unset($menu_list);
		$cfg_file = ROOT_PATH . 'setting/admin_left_menu.php';
		include($cfg_file);

		foreach ($menu_list as $m_key=>$m_val) {
			if($m_val['sub_menu_list'] && is_array($m_val['sub_menu_list']) && count($m_val['sub_menu_list'])) {
				foreach ($m_val['sub_menu_list'] as $s_m_key=>$s_m_val) {
					$menu_list[$m_key]['sub_menu_list'][$s_m_key]['shortcut'] = (bool) $this->Post['menu_list'][$m_key][$s_m_key]['shortcut'];
				}
			}
		}

		jio()->WriteFile($cfg_file,'<?php $menu_list = '.var_export($menu_list,true).'; ?>');

		$this->Messager("修改成功");
	}
	function _writeHtaccess($abs_path)
	{
		$un_writes = array();

		$is_local=preg_match("~^localhost|127\.0\.0\.1|192\.168\.\d+\.\d+$~",$_SERVER['SERVER_ADDR']);
		$str="# BEGIN JishiGou
<IfModule mod_rewrite.c>
RewriteEngine On
".
		($is_local?"Options FollowSymLinks":"")
		."
RewriteBase $abs_path
RewriteCond %{REQUEST_URI}	!\.(gif|jpeg|png|jpg|bmp)$
RewriteCond %{REQUEST_URI}  !^{$abs_path}(wap|mobile)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</IfModule>
# END JishiGou";

		@$len = jio()->WriteFile(ROOT_PATH . ".htaccess", $str);

		if(!$len) {
			$un_writes[] = '.htaccess';
		}


				$_dirs = array(
					'images/',
					'templates/',
					'theme/',
										'wap/templates/',
		);
		foreach ($_dirs as $_dir) {
			$_path = ROOT_PATH . $_dir;
			if(!is_dir($_path)) {
				jmkdir($_path);
			} else {
				if(!file_exists($_path . 'index.html')) {
					@touch($_path . 'index.html');
				}
			}

			$str="# BEGIN JishiGou dir safe protect for {$_dir}
<IfModule mod_rewrite.c>
RewriteEngine On
".
			($is_local?"Options FollowSymLinks":"")
			."
RewriteBase {$abs_path}{$_dir}
RewriteRule ^.*\.(php|php3|php4|asp|aspx|jsp|cgi)$ index.html [NC,L]
</IfModule>
# END JishiGou ".date("Y-m-d H:i:s");

			@$len = jio()->WriteFile($_path . '.htaccess', $str);

			if(!$len) {
				$un_writes[] = "{$_dir}.htaccess";
			}
		}

				$_dirs = array(
					'api/pw_api/',
					'api/pw_client/',
										'include/',
					'install/',
					'modules/',
					'setting/',
					'wap/data/',
					'wap/include/',
					'wap/modules/',
		);
		foreach ($_dirs as $_dir) {
			$_path = ROOT_PATH . $_dir;
			if(!is_dir($_path)) {
				jmkdir($_path);
			} else {
				if(!file_exists($_path . 'index.html')) {
					@touch($_path . 'index.html');
				}
			}

			$str="# BEGIN JishiGou dir safe protect for {$_dir}
<IfModule mod_rewrite.c>
RewriteEngine On
".
			($is_local?"Options FollowSymLinks":"")
			."
RewriteBase {$abs_path}{$_dir}
RewriteRule . index.html [L]
</IfModule>
# END JishiGou ".date("Y-m-d H:i:s");

			@$len = jio()->WriteFile($_path . '.htaccess', $str);

			if(!$len) {
				$un_writes[] = "{$_dir}.htaccess";
			}
		}

		if($un_writes) {
			$this->Messager("以下文件无法写入，请检查相应的目录是否有可写权限。<br /><br />" . implode("<br />", $un_writes), null);
		}
	}


		function Email_notice()
	{
		$notice = jconf::get('email_notice');
		$open_notice_email = $this->jishigou_form->YesNoRadio("notice[is_email]",(int)$notice['is_email']);  
		$action = "admin.php?mod=setting&code=do_email_notice";
		include(template('admin/email_notice'));
	}

	function DoEmail_notice()
	{
		jconf::set('email_notice',$this->Post['notice']);
		$this->Messager("修改成功",'admin.php?mod=setting&code=email_notice');
	}

	function ModifySlide()
	{
		$slide_config = jconf::get('slide');

		$slide_enable_radio = $this->jishigou_form->YesNoRadio('slide[enable]',$slide_config['enable']);

		$slide_list = $slide_config['list'];


		include(template('admin/setting_slide'));
	}
	function DoModifySlide()
	{
		$slide_config = jconf::get('slide');

		$_slide = $this->Post['slide'];
		$slide_new = $this->Post['slide_new'];

		$slide = array();
		if($_FILES){


		}

		$slide_order_max = 0;
		$slide_list = array();
		$slide_enable = 0;
		if($_slide['list'])
		{
			foreach($_slide['list'] as $key=>$v)
			{
				$picname = "slide_pic_$key";
				$name = time().'_'.$key;
				if($_FILES[$picname]['name']){
					$return = jlogic('image')->loadImage($picname,$name);
					if($return){
						$v['enable'] = ($v['enable'] ? 1 : 0);
						if($v['enable'])
						{
							$slide_enable = 1;
						}

						$v['order'] = max(0, (int) $v['order']);
						$v['src'] = $return;
						$slide_order_max = max($slide_order_max,$v['order']);

						$slide_list[] = $v;
					}
				}else if($v['src'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);

					$slide_order_max = max($slide_order_max,$v['order']);

					$slide_list[] = $v;
				}
			}
		}

		if($slide_new)
		{
			foreach($slide_new as $key=>$v)
			{
				$picname = "slide_new_pic_$key";
				$name = time().'_new_'.$key;
				if($_FILES[$picname]['name']){
					$return = jlogic('image')->loadImage($picname,$name);
					if($return){
						$v['enable'] = ($v['enable'] ? 1 : 0);
						if($v['enable'])
						{
							$slide_enable = 1;
						}

						$v['order'] = max(0, (int) $v['order']);
						$v['src'] = $return;
						$slide_order_max = max($slide_order_max,$v['order']);

						$slide_list[] = $v;
					}
				}elseif($v['src'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);
					if($v['order'] < 1)
					{
						$slide_order_max += 1;

						$v['order'] = $slide_order_max;
					}

					$slide_list[] = $v;
				}
			}
		}

		if($slide_list)
		{
			usort($slide_list,create_function('$a,$b','if($a[order]==$b[order])return 0;return $a[order]<$b[order]?-1:1;'));
		}

		$slide['list'] = $slide_list;
		$slide['enable'] = ($slide_enable && $_slide['enable'] ? 1 : 0);

		if($slide != $slide_config)
		{
			jconf::set('slide',$slide);
		}


				if($slide['enable'] != $this->Config['slide_enable'])
		{
			$config = array();
			$config['slide_enable'] = $slide['enable'];

			jconf::update($config);
		}


		$this->Messager("设置成功");
	}
	function ModifySlideIndex()
	{
		$slide_config = jconf::get('slide_index');
		if(!$slide_config)
		{
			$slide_config = array (
              'list' =>
			array (
			0 =>
			array (
                  'enable' => 1,
                  'src' => 'static/image/index/ad.jpg',
                  'href' => 'index.php?mod=member',
                  'order' => 1,
			),
			1 =>
			array (
                  'enable' => 1,
                  'src' => 'static/image/index/ad_2.jpg',
                  'href' => 'index.php?mod=member',
                  'order' => 2,
			),
			),
              'enable' => 0,
			);

			jconf::set('slide_index',$slide_config);
		}

		$slide_enable_radio = $this->jishigou_form->YesNoRadio('slide[enable]',$slide_config['enable']);

		$slide_list = $slide_config['list'];



		include(template('admin/setting_slide_index'));
	}
	function DoModifySlideIndex()
	{
		$slide_config = jconf::get('slide_index');

		$_slide = $this->Post['slide'];
		$slide_new = $this->Post['slide_new'];

		$slide = array();

		if($_FILES){


		}

		$slide_order_max = 0;
		$slide_list = array();
		$slide_enable = 0;
		$return = false;
		if($_slide['list'])
		{
			foreach($_slide['list'] as $key => $v)
			{
				$picname = "slide_pic_$key";
				$name = time().'_'.$key;
				if($_FILES[$picname]['name']){
					$return = jlogic('image')->loadImage($picname,$name);
					if($return){
						$v['enable'] = ($v['enable'] ? 1 : 0);
						if($v['enable'])
						{
							$slide_enable = 1;
						}

						$v['order'] = max(0, (int) $v['order']);
						$v['src'] = $return;
						$slide_order_max = max($slide_order_max,$v['order']);

						$slide_list[] = $v;
					}
				}elseif($v['src'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);

					$slide_order_max = max($slide_order_max,$v['order']);

					$slide_list[] = $v;
				}
			}
		}

		if($slide_new)
		{
			foreach($slide_new as $key=>$v)
			{
				$picname = "slide_new_pic_$key";
				$name = time().'_new_'.$key;
				if($_FILES[$picname]['name']){
					$return = jlogic('image')->loadImage($picname,$name);
					if($return){
						$v['src'] = $return;

						$v['enable'] = ($v['enable'] ? 1 : 0);
						if($v['enable'])
						{
							$slide_enable = 1;
						}

						$v['order'] = max(0, (int) $v['order']);
						if($v['order'] < 1)
						{
							$slide_order_max += 1;

							$v['order'] = $slide_order_max;
						}
						$slide_list[] = $v;
					}
				}elseif($v['src'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);
					if($v['order'] < 1)
					{
						$slide_order_max += 1;

						$v['order'] = $slide_order_max;
					}

					$slide_list[] = $v;
				}
			}
		}

		if($slide_list)
		{
			usort($slide_list,create_function('$a,$b','if($a[order]==$b[order])return 0;return $a[order]<$b[order]?-1:1;'));
		}

		$slide['list'] = $slide_list;
		$slide['enable'] = ($slide_enable && $_slide['enable'] ? 1 : 0);

		if($slide != $slide_config)
		{
			jconf::set('slide_index',$slide);
		}


				if($slide['enable'] != $this->Config['slide_index_enable'])
		{
			$config = array();
			$config['slide_index_enable'] = $slide['enable'];

			jconf::update($config);
		}


		$this->Messager("设置成功");
	}

	function ModifyHotTagRecommend()
	{
		$hot_tag_recommend = jconf::get('hot_tag_recommend');
		if(!$hot_tag_recommend)
		{
			$hot_tag_recommend = array(
                'enable' => 0,
                'name' => '热门话题推荐',
                'num' => 10,
                'list' => array(),
			);

			jconf::set('hot_tag_recommend',$hot_tag_recommend);
		}

		$hot_tag_recommend_enable_radio = $this->jishigou_form->YesNoRadio('hot_tag_recommend[enable]',$hot_tag_recommend['enable']);
		$_options = array();
		for($i=1;$i<=20;$i++)
		{
			$_options[$i] = array('name'=>$i,'value'=>$i);
		}
		$hot_tag_recommend_num_select = $this->jishigou_form->Select('hot_tag_recommend[num]',$_options,$hot_tag_recommend['num']);

		$query_link = "admin.php?mod=setting&code=modify_hot_tag_recommend";

		$per_page_num = min(200,max(20,(int) $this->Get['pn']));

		$total_record = DB::result_first("select count(*) as `count` from ".TABLE_PREFIX."tag_recommend");

		if($total_record > 0)
		{
			$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'Array'),"20 30 50 100 200");

			$sql = "select tr.*,t.topic_count from ".TABLE_PREFIX."tag_recommend tr left join ".TABLE_PREFIX."tag t on t.name=tr.name order by `order` desc , `id` desc {$page_arr[limit]}";
			$query = $this->DatabaseHandler->Query($sql);
			$hot_tag_recommend_list = array();
			while(false != ($row = $query->GetRow()))
			{
				$hot_tag_recommend_list[$row['id']] = $row;
			}
		}


		include(template('admin/setting_hot_tag_recommend'));
	}
	function DoModifyHotTagRecommend()
	{
		$act = ($this->Post['act'] ? $this->Post['act'] : $this->Get['act']);
		$timestamp = time();
		$uid = MEMBER_ID;
		$username = MEMBER_NAME;

		$messager = "";
		if('delete' == $act)
		{
			$id = max(0, (int) get_param('id'));

			$info = DB::fetch_first("select * from ".TABLE_PREFIX."tag_recommend where `id`='$id'");
			if(!$info)
			{
				$this->Messager("你要删除的内容已经不存在了");
			}

			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."tag_recommend where `id`='$id'");

			$this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`=0 where `name`='$name'");

			$messager = "删除成功";
		}
		else
		{
			$hot_tag_recommend_config = jconf::get('hot_tag_recommend');

			$_arr = $this->Post['hot_tag_recommend'];

			$name = ($_arr['name'] ? $_arr['name'] : "热门话题推荐");
			$num = min(20,max(1,(int) $_arr['num']));

			$hot_tag_recommend = array(
                'enable' => ($_arr['enable'] ? 1 : 0),
                'name' => $name,
                'num' => $num,
			);


			if($_arr['list'])
			{
				$_list = $this->Post['_list'];
				foreach($_arr['list'] as $k=>$v)
				{
					if($v != $_list[$k])
					{
						$v['enable'] = $v['enable'] ? 1 : 0;

						$_sets = array();
						foreach($v as $_k=>$_v)
						{
							if($_v != $_list[$k][$_k])
							{
								$_sets[$_k] = "`{$_k}`='{$_v}'";
							}
						}

						if($_sets)
						{
							$_sets['last_update'] = "`last_update`='$timestamp'";

							$this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag_recommend set ".implode(" , ",$_sets)." where `id`='$k'");
							if($v['enable']!=$_list[$k]['enable'])
							{
								$this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`='{$v['enable']}' where `name`='$name'");
							}
						}
					}
				}
			}

			$_new_arr = $this->Post['hot_tag_recommend_new'];
			foreach($_new_arr as $k=>$v)
			{
				if(($name = $v['name']) && (DB::fetch_first("select * from ".TABLE_PREFIX."tag where `name`='$name'")))
				{
					$_enable = $v['enable'] ? 1 : 0;
					$desc = $v['desc'];
					$order = (int) $v['order'];

					$this->DatabaseHandler->Query("insert into ".TABLE_PREFIX."tag_recommend (`enable`,`name`,`desc`,`order`,`dateline`,`uid`,`username`) values ('$_enable','$name','$desc','$order','$timestamp','$uid','$username')");

					$this->DatabaseHandler->Query("update ".TABLE_PREFIX."tag set `status`='$_enable' where `name`='$name'");
				}
			}


			$__list = array();
			if($hot_tag_recommend[num] > 0)
			{
				$sql = "select tr.*,t.topic_count from ".TABLE_PREFIX."tag_recommend tr left join ".TABLE_PREFIX."tag t on t.name=tr.name where tr.enable=1 order by `order` desc , `id` desc limit {$hot_tag_recommend['num']}";
				$query = $this->DatabaseHandler->Query($sql);
				while(false != ($row = $query->GetRow()))
				{
					$__list[$row['id']] = $row;
				}
			}
			$hot_tag_recommend['list'] = $__list;


			if($hot_tag_recommend_config != $hot_tag_recommend)
			{
				jconf::set('hot_tag_recommend',$hot_tag_recommend);

				if($hot_tag_recommend['enable'] != $this->Config['hot_tag_recommend_enable'])
				{
					$config = array();
					$config['hot_tag_recommend_enable'] = $hot_tag_recommend['enable'];

					jconf::update($config);
				}
			}


			$messager = "设置成功";
		}


		$this->Messager($messager);
	}

	function ModifyFtp()
	{
		$do = jget('do');
		$ftp_key = jget('key','int');
		$ftp = jconf::get('ftp');
		if($ftp){
			if(!is_array($ftp[0])){				$ftp = array($ftp);
			}
			foreach($ftp as $k => $v){				if(!isset($v['type'])){
					$ftp[$k]['type'] = 'FTP';
				}
			}
			jconf::set('ftp',$ftp);
		}
		if($do=='edit' && isset($ftp_key)){
			$ftp = $ftp[$ftp_key];
			$ftp_on_radio = $this->jishigou_form->YesNoRadio('ftp[on]',$ftp['on']);
			$ftp_ssl_radio = $this->jishigou_form->YesNoRadio('ftp[ssl]',$ftp['ssl']);
			$ftp_pasv_radio = $this->jishigou_form->YesNoRadio('ftp[pasv]',$ftp['pasv']);
			$ftp_language = array(
				'FTP'=>array('host'=>array('FTP地址','请填写FTP服务器的域名或者IP地址，例如：ftp.jishigou.net或64.195.258.89'),'port'=>array('FTP端口','默认为21'),'username'=>array('FTP帐号','该帐号必需具有以下权限：读取文件、写入文件、删除文件、创建目录、子目录继承'),'password'=>array('FTP密码','帐号对应的密码'),'dir'=>array('FTP远程目录','远程附件目录的绝对路径或相对于 FTP 主目录的相对路径，结尾不要加斜杠“/”，“.”表示 FTP 主目录'),'url'=>array('远程访问URL','支持 HTTP 和 FTP 协议，结尾不要加斜杠“/”；如果使用 FTP 协议，FTP 服务器必需支持 PASV 模式，为了安全起见，使用 FTP 连接的帐号不要设置可写权限和列表权限')),
				'Aliyun'=>array('host'=>array('OSS服务器地址','阿里云主机请填写oss-internal.aliyuncs.com，非主机请填写oss.aliyuncs.com'),'port'=>array('OSS服务器端口','请填写80'),'username'=>array('OSS ID','请填写您的 Access Key ID'),'password'=>array('OSS Secret','请填写您的 Access Key Secret'),'dir'=>array('OSS Bucket','存放附件的Bucket名称，如果没有，请到控制台去建立，填写后不得修改'),'url'=>array('远程访问URL','如果您使用了域名指向请填写转发域名，规则为：http:/'.'/你的域名/你创建的bucket名称，结尾不要加“/”符号，例如：http:/'.'/oss.jishigou.net/jishigou，否则请留空，系统会自动匹配OSS标准路径')),
				'Upyun'=>array('host'=>array('',''),'port'=>array('',''),'username'=>array('',''),'password'=>array('',''),'dir'=>array('',''),'url'=>array('','')),
				'99Pan'=>array('host'=>array('',''),'port'=>array('',''),'username'=>array('',''),'password'=>array('',''),'dir'=>array('',''),'url'=>array('',''))
				);
		}else{
			foreach($ftp as $k => $v){
				if($v['on'] == 0){
					$ftp[$k]['status'] = '<font color=red>×</font>';
				}else{
					$ftp[$k]['status'] = '<font color=green>√</font>';
				}
			}
		}

		include(template('admin/setting_ftp'));
	}
	function DoModifyFtp()
	{
		$ftp_key = jget('ftp_key','int');
		$do = jget('do');
		$mod_new_ftp = $ftp_config = jconf::get('ftp');
		if($ftp_key >=0 && $do=='edit'){
			$ftp = $this->Post['ftp'];
			$ftp['on'] = ($ftp['on'] && $ftp['host'] ? 1 : 0);
			$ftp['ssl'] = $ftp['ssl'] ? 1 : 0;
			$ftp['port'] = $ftp['port'] ? $ftp['port'] : ($ftp['type']=='FTP' ? 21 : 80);
			$ftp['attachurl'] = $ftp['attachurl'] ? $ftp['attachurl'] : (($ftp['type']=='Aliyun' && $ftp['attachdir']) ? 'http:/'.'/'.$ftp['attachdir'].'.oss.aliyuncs.com' : '');
			$ftp['pasv'] = $ftp['pasv'] ? 1 : 0;
			$ftp['timeout'] = max(0, (int) $ftp['timeout']);
			$ftp['priority'] = max(1, (int) $ftp['priority']);
			if('请输入FTP密码' == $ftp['password'])
			{
				$ftp['password'] = $ftp_config[$ftp_key]['password'];
			}
			$mod_new_ftp[$ftp_key] = $ftp;
			$ftp_on_error = false;
			jconf::set('ftp',$mod_new_ftp);
			if($ftp['on'])
			{
				if(!($ftp['host'] && $ftp['port'] && $ftp['username'] && $ftp['password'] && $ftp['attachdir'] && $ftp['attachurl'])){
					$mod_new_ftp[$ftp_key]['on'] = $ftp['on'] = 0;
					jconf::set('ftp',$mod_new_ftp);
					$this->Messager('<font color=red>数据填写不完整</font>');
				}
				$ftp_result = ftpcmd('upload','images/noavatar.gif','',$ftp_key);
				if($ftp_result < 1)
				{
										$ftp_error = $ftp_result;
					$mod_new_ftp[$ftp_key]['on'] = $ftp['on'] = 0;
					$ftp_on_error = true;
					jconf::set('ftp',$mod_new_ftp);
				}
			}
			$ftp_sys_on = 0;
			foreach($mod_new_ftp as $val){
				if($val['on'] == 1){
					$ftp_sys_on = 1;
					break;
				}
			}
			if($ftp_sys_on != $this->Config['ftp_on'])
			{
				$config = array();
				$config['ftp_on'] = $ftp_sys_on;
				jconf::update($config);
			}
			$errors = array(
				'-100' => '服务器禁止了FTP功能',
				'-101' => '配置中没有开启FTP（请在配置中启用FTP功能）',
				'-102' => '连接到FTP服务器错误（请检查FTP地址或者端口号是否正确）',
				'-103' => '登录FTP服务器错误（请检查FTP用户名和密码是否正确）',
				'-104' => '更改FTP目录错误（请检查FTP用户的权限）',
				'-105' => '创建FTP目录错误（请检查FTP用户的权限）',
				'-106' => '文件本地读取错误',
				'-107' => '文件上传到FTP服务器错误（请检查FTP的权限）<br>如果您是第一次开启，出现该提示，则说明您的FTP无写入权限<br>如果您以前已经正常开启使用过，则说明您的FTP无修改与删除权限<br>',
			);
			if($ftp_error && isset($errors[$ftp_error]))
			{
				$this->Messager($errors[$ftp_error],null);
			}elseif($ftp_on_error)
			{
				$this->Messager('<font color=red>服务开启失败，请检查数据填写是否正确</font>');
			}
		}else{
			$newftp = jget('newftp');
			$newftp_arr = array();
			if($newftp['host']){
				foreach ($newftp['host'] as $k => $v) {
					if($v && $newftp['port'][$k]){
						$newftp_arr[$k]['host'] = $v;
						$newftp_arr[$k]['port'] = $newftp['port'][$k];
						$newftp_arr[$k]['username'] = $newftp['username'][$k];
						$newftp_arr[$k]['password'] = $newftp['password'][$k];
						$newftp_arr[$k]['type'] = $newftp['type'][$k];
						$newftp_arr[$k]['on'] = 0;
						$newftp_arr[$k]['ssl'] = 0;
						$newftp_arr[$k]['pasv'] = 0;
						$newftp_arr[$k]['timeout'] = 0;
						$newftp_arr[$k]['priority'] = 1;
						$newftp_arr[$k]['attachdir'] = ($newftp['type'][$k]=='FTP') ? '.' : '';
					}
				}
			}
			$ids = jget('ids');
			if($ids){
				foreach ($ids as $k => $v) {
					unset($ftp_config[$v]);
				}
			}
			$new_ftp_config = array_merge($ftp_config,$newftp_arr);
			jconf::set('ftp',$new_ftp_config);
		}
		$this->Messager("设置成功");
	}

	
	function Follow()
	{
		if(!$GLOBALS['_J']['config']['acceleration_mode']) {
			$follow_ary = get_def_follow_group();
		}

				$tmp = jconf::get('follow');
		if (empty($tmp) && !$GLOBALS['_J']['config']['acceleration_mode']) {
			jconf::set('follow', $follow_ary);
		} else {
			$follow_ary = $tmp;
		}


		include(template('admin/setting_follow'));
	}

	
	function Do_Follow()
	{
		$followgroup = $this->Post['follow_name'];

				$del_ids  = $this->Post['del_ids'];

		if (!empty($followgroup)) {
			foreach ($followgroup as $key => $val) {
				if (in_array($key, $del_ids)) {
					unset($followgroup[$key]);
					continue;
				}
				if (trim($val) !== '') {
					$followgroup[$key] = getstr($val, 20, 1, 1);
				}
			}
		}

				$new_ary = $this->Post['new_follow'];
		if (!empty($new_ary)) {
			foreach ($new_ary as $val) {
				if (trim($val) !== '') {
					$val = getstr($val, 20, 1, 1);
					array_push($followgroup, $val);
				}
			}
		}

		if (!empty($followgroup)) {
			jconf::set('follow', $followgroup);
		}
		$this->Messager('操作成功了');
	}

		function regfollow()
	{
				$uids = jconf::get('regfollow');
		if (!empty($uids)) {

			$TopicLogic = jlogic('topic');
			$info = $TopicLogic->GetMember($uids, "`uid`,`nickname`");
			$nicknames = array();
			if (!empty($info)) {
				foreach ($info as $val) {
					$nicknames[] = $val['nickname'];
				}
				$str_nickname = implode("\n", $nicknames);
			}
		}

				$default_uids = jconf::get('default_regfollow');
		if (!empty($default_uids)) {

			$TopicLogic = jlogic('topic');
			$info = $TopicLogic->GetMember($default_uids, "`uid`,`nickname`");
			$nicknames = array();
			if (!empty($info)) {
				foreach ($info as $val) {
					$nicknames[] = $val['nickname'];
				}
				$default_nickname = implode("\n", $nicknames);
			}
		}


		include(template('admin/setting_regfollow'));
	}

	function do_regfollow()
	{
				$regfollow_type = $this->Post['regfollow_type'];

				$str_nick = $this->Post['nicks'];


		$uids = array();
		if($str_nick) {
			$nicks = explode("\n", $str_nick);
			foreach ($nicks as $val) {
				$val = trim($val);
				if (empty($val)) {
					continue;
				}
								$uid = DB::result_first("SELECT uid FROM ".DB::table('members')." WHERE nickname='{$val}'");
				if ($uid < 1) {
					continue;
				}
								$uids[] = $uid;
			}
		}

				if($regfollow_type == 'recommend')
		{
			jconf::set('regfollow', $uids);
		}

		if($regfollow_type == 'default')
		{
			jconf::set('default_regfollow', $uids);
		}


		$this->Messager('操作成功了');
	}

		function Experience()
	{

		
		$slide_config = jconf::get('experience');
		$slide_list = $slide_config['list'];


		include(template('admin/experience'));
	}

	function do_Experience()
	{

		$slide_config = jconf::get('experience');

		$_slide = $this->Post['slide'];
		$slide_new = $this->Post['slide_new'];

		$slide = array();


		$slide_order_max = 0;
		$slide_list = array();
		$slide_enable = 0;
		if($_slide['list'])
		{
			foreach($_slide['list'] as $v)
			{
				if($v['level'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);

					$slide_order_max = max($slide_order_max,$v['order']);

					$slide_list[$v['level']] = $v;
				}
			}
		}

		if($slide_new)
		{
			foreach($slide_new as $v)
			{
				if($v['level'])
				{
					$v['enable'] = ($v['enable'] ? 1 : 0);
					if($v['enable'])
					{
						$slide_enable = 1;
					}

					$v['order'] = max(0, (int) $v['order']);
					if($v['order'] < 1)
					{
						$slide_order_max += 1;

						$v['order'] = $slide_order_max;
					}

					$slide_list[$v['level']] = $v;
				}
			}
		}

		$slide['list'] = $slide_list;
		$slide['enable'] = ($slide_enable && $_slide['enable'] ? 1 : 0);

		if($slide != $slide_config)
		{
			jconf::set('experience',$slide);
		}


		$this->Messager('设置成功');
	}




		function BbsPlugin()
	{
		include(template('admin/bbs_plugin'));
	}

		function Cpad()
	{
		if(!is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
			$cp_not_install = true;
		}
		include(template('admin/cp_ad'));
	}

	function invite()
	{
		$invite = jconf::get('invite');
		$invite_msg = empty($invite) ? '' : jstripslashes($invite['invite_msg']);
		$link_display_none_radio = $this->jishigou_form->YesNoRadio('link_display_none', (int) $invite['link_display_none']);


		include(template('admin/setting_invite'));
	}

	function do_invite()
	{
		$invite_msg = trim($this->Post['invite_msg']);
		if (empty($invite_msg)) {
					}

		$invite = array(
			'link_display_none' => ($this->Post['link_display_none'] ? 1: 0),
    		'invite_msg' => $invite_msg,
		);
		jconf::set('invite', $invite);
		$this->Messager('操作成功了');
	}

	function ModifyTopicFrom() {
		$topic_from_config = jconf::get('topic_from');
		$topic_from_config_default = $this->_topic_from_config();
		if(!$topic_from_config) {
			$topic_from_config = $topic_from_config_default;

			jconf::set('topic_from', $topic_from_config);
		}

		include(template('admin/setting_topic_from'));
	}
	function DoModifyTopicFrom() {
		$act_rest = $this->Get['act'] ? 1 : 0;

		$topic_from_default = $this->_topic_from_config();
		if($act_rest) {
			$topic_from = $topic_from_default;
		} else {
			$topic_from = $this->Post['topic_from'];
			if(!$topic_from) {
				$this->Messager('配置内容不能为空', null);
			}
			foreach($topic_from as $ks=>$vs) {
				if(!$vs['name']) {
					$topic_from[$ks]['name'] = $topic_from_default[$ks]['name'];
				}
			}
		}

		jconf::set('topic_from', $topic_from);


		$this->Messager("设置成功");
	}
	function _topic_from_config() {
		global $jishigou_rewrite;

		$_config = array (
			'wap' => array(
				'name' => '手机WAP',
				'value' => 'wap',
				'link' => 'index.php?mod=other&code=wap',
		),
			'mobile' => array(
				'name' => '手机3G',
				'value' => 'mobile',
				'link' => 'index.php?mod=other&code=mobile',
		),
			'sms' => array(
				'name' => '手机短信',
				'value' => 'sms',
				'link' => 'index.php?mod=other&code=sms',
		),
			
			'sina' => array(
				'name' => '新浪微博',
				'value' => 'sina',
				'link' => 'index.php?mod=account&code=sina',
		),
			'qqwb' => array(
				'name' => '腾讯微博',
				'value' => 'qqwb',
				'link' => 'index.php?mod=account&code=qqwb',
		),
		
			'iphone' => array(
				'name' => 'iPhone客户端',
				'value' => 'iphone',
				'link' => 'index.php?mod=other&code=iphone',
		),
			'ipad' => array(
				'name' => 'iPad客户端',
				'value' => 'ipad',
		),
			'androidpad' => array(
				'name' => 'Android平板',
				'value' => 'androidpad',
		),
		);
		foreach($_config as $k=>$v) {
			if($v['link']) {
				if($jishigou_rewrite) {
					$v['link'] = $jishigou_rewrite->formatURL($v['link']);
				}
			}

			$_config[$k] = $v;
		}

		return $_config;
	}

	
	function SetMailMsg(){
		$msg = jconf::get('mail_msg');
		$msg || $msg = array('subject'=>'nickname，您在site_name有新的消息','msg'=>'尊敬的nickname：您好！<br>在未登录site_name的load期间，您收到一些信息<br>site_new_data。<br>点击<a href="site_url">site_url</a>查看。<br><br><font color="gray">如此邮件提醒对您产生了干扰，请<a href="$site_url/index.php?mod=settings&code=sendmail" targegt="_blank">点击修改提醒设置</a></font><br>time');
		include template('admin/set_mail_msg');
	}

	function DoSetMailMsg(){
		$mail_msg = jget('msg');
		$mail_msg['msg'] = stripcslashes($mail_msg['msg']);

		jconf::set('mail_msg',$mail_msg);

		$this->Messager("设置成功",-1);
	}

}

?>