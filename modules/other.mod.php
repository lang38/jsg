<?php
/**
 * other.mod.php
 * @version $Id: other.mod.php 5462 2014-01-18 01:12:59Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 网站杂项，其他模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ShowConfig;

	var $CacheConfig;

	var $TopicLogic;

	var $ID = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ID = jget('id', 'int');


		$this->TopicLogic = jlogic('topic');

		$this->CacheConfig = jconf::get('cache');

		$this->ShowConfig = jconf::get('show');

		$this->InfoConfig = jconf::get('web_info');

		$this->Execute();

	}

	
	function Execute()
	{
		ob_start();
		if (in_array($this->Code,array('wap','sms','wechat','mobile','iphone','android','pad'))) {
			$this->Wap();
		} elseif ('test' == $this->Code) {
			$this->Test();
		}  elseif ('about' == $this->Code) {
			$this->About();
		} elseif ('contact' == $this->Code) {
			$this->Contact();
		} elseif ('joins' == $this->Code) {
			$this->Joins();
		} elseif ('media' == $this->Code) {
			$this->Media();
		} elseif ('groupdelete' == $this->Code) {
			$this->GroupDelete();
		} elseif ('vip_intro'==$this->Code) {
			$this->VipIntro();
		} elseif ('medal'==$this->Code) {
			$this->Medal();
		} elseif ('notice'==$this->Code) {
			$this->Notice();
		} elseif ('checkmedal'==$this->Code) {
			$this->CheckMedal();
		} elseif ('media_more'==$this->Code) {
			$this->Media_More();
		} elseif ('add_favor_tag'==$this->Code) {
			$this->addFavoriteTag();
		} elseif ('regagreement' == $this->Code) {
						$this->regagreement();
		} elseif ('seccode' == $this->Code) {
			$this->Seccode();
		} elseif ('navigation' == $this->Code) {
			$this->Navigation();
		} elseif ('usergroup' == $this->Code) {
			$this->UserGroupList();
		} elseif ('qmd' == $this->Code) {
			$this->Qmd();
		} else {
			$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

    function Main()
    {
        $this->Messager("页面不存在",null);
    }

	function Test()
	{
		exit('test ok');
	}


		function Media()
	{
		if (!$this->Config['media_open']) {
			$this->Messager('本站点未开启推荐用户功能。',-1);
		}
				$sql = "select `id`,`media_name`,`media_count` from `".TABLE_PREFIX."media`  order by `order` asc";
				$query = $this->DatabaseHandler->Query($sql);
		$media_list = array();
		$media_ids = array();
		while (false != ($row = $query->GetRow()))
		{
			$media_ids[$row['id']] = $row['id'];
			$media_list[] = $row;
		}

				$limit = $this->ShowConfig['media']['user'];

				$media_user = array();
		foreach ($media_list as $row) {
			$user_media_id = $row['id'];

			$where_list['media_id'] = " `media_id` = '$user_media_id'";
			$where = ' where '.implode(' AND ',$where_list).'order by `fans_count` desc limit 0,'.$limit;

			$_list = $this->TopicLogic->GetMember($where,"`uid`,`ucuid`,`media_id`,`username`,`aboutme`,`nickname`,`face_url`,`face`,`validate`");
			if($_list){
				foreach ($_list as $row) {
					$row['validate_html'] = $row['validate_html'];
					$media_user[] = $row;
				}
			}
		}

		$this->Title = "推荐用户 ";
		include(template('other/media'));
	}

		function Media_More()
	{
		if (!$this->Config['media_open']) {
			$this->Messager('本站点未开启推荐用户功能。',-1);
		}
		$ids = (int) $this->Get['ids'];

		$media_info = DB::fetch_first("SELECT `id`,`media_name` FROM ".DB::table('media')." WHERE id='{$ids}'");


				$sql = "select `id`,`media_name`,`media_count` from `".TABLE_PREFIX."media`  order by `id` desc";
		$query = $this->DatabaseHandler->Query($sql);
		$media_list = array();
		$media_ids = array();
		while (false != ($row = $query->GetRow()))
		{
			$media_ids[$row['id']] = $row['id'];
			$media_list[] = $row;
		}

		$per_page_num = $this->ShowConfig['media_view']['user'] ? $this->ShowConfig['media_view']['user'] :40;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");

				$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."members` where `media_id` = '{$ids}'";
		$total_record = DB::result_first($sql);

				$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));

		$where = " where `media_id` = '{$ids}' order by `topic_count` desc {$page_arr['limit']} ";

		$member_list = $this->TopicLogic->GetMember($where,"`uid`,`ucuid`,`media_id`,`aboutme`,`username`,`nickname`,`face_url`,`face`,`validate`");

		
		$this->Title = "推荐用户";
		include(template('other/media_more'));


	}

    function Wap()
  {
  	$topic_from = jconf::get('topic_from');
  	$member = jsg_member_info(MEMBER_ID);
  	  	if('wap' == $this->Code){
		$this->Title = "手机访问 {$this->Config['site_name']}";
		$this->MetaKeywords = "手机访问,wap,{$this->Config['site_name']}";
		$this->MetaDescription = $this->Title."，可登录、查看、发微博、评论转发等";

	  	} elseif ('mobile' == $this->Code){
  		$this->Title = "3G手机访问 {$this->Config['site_name']}";
  		$this->MetaKeywords = "手机访问,wap,{$this->Config['site_name']}";
		$this->MetaDescription = $this->Title."，可登录、查看、发微博、评论转发等";

	  	} elseif ('sms' == $this->Code){
  		$this->Title = "短·彩信版";
        define('IN_SMS_MOD',      true);
        $sms_msg_return = 1;
        include(ROOT_PATH . 'modules/sms.mod.php');
  	} elseif ('iphone' == $this->Code){
  		$this->Title = "iPone客户端";
  	} elseif ('android' == $this->Code){
  		$this->Title = "Android客户端";
  	} elseif ('pad' == $this->Code){
  		$this->Title = "Android平板客户端";
    } elseif ('wechat' == $this->Code){
        $wechat_conf = jconf::get("wechat");
        $this->Title = "微信";
    }

	include(template('other/topic_wap'));

  }

    function About()
  {
  	$this->Title = "关于我们";

	$member = jsg_member_info(MEMBER_ID);

  	include(template('other/topic_about'));

  }



		function Medal()
	{
		$act_list = jlogic('other')->act_list();

		$act = $this->Code;

		$uid = MEMBER_ID;

				$member = $this->TopicLogic->GetMember(MEMBER_ID);
		if ($member['medal_id']) {
			$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
		}
		$view = $this->Get['view'];
		$all_medal = array();
		if($view == 'my'){
			$sql = "select u.medalid as medal_id,u.is_index,u.dateline,
						   m.medal_img,m.medal_name,m.medal_depict,m.conditions
				    from `".TABLE_PREFIX."user_medal` u
					left join `".TABLE_PREFIX."medal` m on m.id = u.medalid
					where u.uid = '$uid'
					and m.is_open  = 1
					order by u.dateline desc";
						if($this->Config[sina_enable] && sina_weibo_init($this->Config)){
    			$sina = sina_weibo_has_bind(MEMBER_ID);
    		}
    		    		if($this->Config[imjiqiren_enable] && imjiqiren_init($this->Config)){
    			$imjiqiren = imjiqiren_has_bind(MEMBER_ID);
    		}
    		    		if($this->Config[sms_enable] && sms_init($this->Config)){
    			$sms = sms_has_bind(MEMBER_ID);
    		}
    					if($this->Config[qqwb_enable] && qqwb_init($this->Config)){
				$qqwb = qqwb_bind_icon(MEMBER_ID);
			}
		}else{
			$sql = "SELECT m.id as medal_id,m.medal_img,m.medal_name,m.medal_depict,m.conditions,u.dateline,y.apply_id
					FROM ".TABLE_PREFIX."medal m
					LEFT JOIN ".TABLE_PREFIX."user_medal u ON (u.medalid = m.id AND u.uid = '$uid')
					LEFT JOIN ".TABLE_PREFIX."medal_apply y ON (y.medal_id = m.id AND y.uid = '$uid')
					WHERE m.is_open = 1
					ORDER BY u.dateline DESC,m.id";

			$query = $this->DatabaseHandler->Query($sql);
			while ($rs = $query->GetRow()){
				$rs['conditions'] = unserialize($rs['conditions']);
				if(in_array($rs['conditions']['type'],array('topic','reply','tag','invite','fans','sign')) && !$rs['dateline']){
					$result = jlogic('other')->autoCheckMedal($rs['medal_id']);
				}
			}
		}

		$query = $this->DatabaseHandler->Query($sql);
		while ($rsdb = $query->GetRow()){
			$rsdb['conditions'] = unserialize($rsdb['conditions']);
			if($rsdb['is_index']){
				$rsdb['show'] = 'checked';
			}
			$all_medal[$rsdb['medal_id']] = $rsdb;
		}

		$count = count($all_medal);

		$this->Title = "{$this->Config['site_name']}勋章";
		include(template('other/topic_medal'));
	}


		function CheckMedal()
	{
		$medalid = (int)$this->Get['medal_id'];
		Load::logic('other');
		$otherLogic = new OtherLogic();
		$result = $otherLogic->autoCheckMedal($medalid);
		if($result == '1'){
			$this->Messager("成功点亮",'index.php?mod=other&code=medal');
		}else if($return == '3'){
			$this->Messager("你已获得此勋章了哦",-1);
		}else{
			$this->Messager("未达成获取勋章的条件",-1);
		}
	}


	 	function GroupDelete()
	{
		$gid = (int) $this->Get['gid'];

		$rets = jtable('buddy_follow_group')->del($gid);
		if($rets && $rets['error']) {
			$this->Messager($rets['msg']);
		}

		$this->Messager(NULL,'index.php?mod='.MEMBER_NAME.'&code=follow',0);
	}


    function Notice()
  {
  	$ids = (int) $this->Get['ids'];

  	  	if($ids)
  	{
	  	$sql="Select * From ".TABLE_PREFIX.'notice'." Where id = '{$ids}' ";
		$query = $this->DatabaseHandler->Query($sql);
		$view_notice=$query->GetRow();

		$title		 =  $view_notice['title'];
		$content  =  $view_notice['content'];
		$dateline =  my_date_format2($view_notice['dateline']);

		  		$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `dateline` desc  ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$list_notice = array();
    	while (false != ($row = $query->GetRow()))
    	{

    		$row['titles'] 	= cutstr($row['title'],26);
    		$list_notice[] 	= $row;
    	}

		$this->Title = "网站公告 - {$view_notice['title']}";
	}
	else{

    	    	$this->Title = '网站公告';

    	$per_page_num = $this->ShowConfig['notice']['list'] ? $this->ShowConfig['notice']['list'] : 10;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}" : "");

		    	$sql = "select count(*) as `total_record` from `".TABLE_PREFIX."notice`";
		$total_record = DB::result_first($sql);

				$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',));

    	$sql="select `id`,`title` from ".TABLE_PREFIX.'notice'." order by `dateline` desc {$page_arr['limit']} ";
    	$query = $this->DatabaseHandler->Query($sql);
    	$list_notice = array();
    	while (false != ($row = $query->GetRow()))
    	{
    		$row['titles'] 	= cutstr($row['title'],26);
    		$list_notice[] 	= $row;
    	}
	}

	include(template('other/view_notice'));

  }

 	function Contact()
    {
    	$this->Title = "联系我们";

		$member = jsg_member_info(MEMBER_ID);

    	include(template('other/topic_about'));
    }

    function Joins()
    {
      	$this->Title = "加入我们";

		$member = jsg_member_info(MEMBER_ID);

      	include(template('other/topic_about'));
    }

    function CheckVipCpnditions(){
    	$member = jsg_member_info(MEMBER_ID);
    	$vipConditions = jconf::get('vipcondition');
    	if($vipConditions['email']['enable']){
    		if($member['email_checked'] != 1){
	    		return $vipConditions['email']['message'] .
	    		($vipConditions['email']['forward'] ? '，<a href="'.$vipConditions['email']['forward'].'" target="_blank">点击进行Email认证。</a>' : '');
    		}
    	}
    	if ($vipConditions['topic_num']['enable']) {
    		if($member['topic_count'] <  $vipConditions['topic_num']['enable']){
    			return $vipConditions['topic_num']['message'] .
    			($vipConditions['topic_num']['forward'] ? '，<a href="'.$vipConditions['topic_num']['forward'].'" target="_blank">点击去发微博。</a>' : '');
    		}
    	}
    	if ($vipConditions['face']['enable']) {
    		if(!$member['__face__']){
    			return $vipConditions['face']['message'] .
    			($vipConditions['face']['forward'] ? '，<a href="'.$vipConditions['face']['forward'].'" target="_blank">点击上传头像。</a>' : '');
    		}
    	}
    	if ($vipConditions['fans_num']['enable']) {
    		if($member['fans_count'] <  $vipConditions['fans_num']['enable']){
    			return $vipConditions['fans_num']['message'] .
    			($vipConditions['fans_num']['forward'] ? '，<a href="'.$vipConditions['fans_num']['forward'].'" target="_blank">点击邀请更多的人。</a>' : '');
    		}
    	}
    	if ($vipConditions['city']['enable']) {
    		if(!$member['province']){
	    		return $vipConditions['city']['message'] .
	    		($vipConditions['city']['forward'] ? '，<a href="'.$vipConditions['city']['forward'].'" target="_blank">点击设置所在区域。</a>' : '');
    		}
    	}

    	return '';
    }


        function VipIntro()
    {
    	if(MEMBER_ID < 1)
    	{
    		$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
    	}

		$member = jsg_member_info(MEMBER_ID);
		$notUpToStandardVipConditions = $this->CheckVipCpnditions();
		if(!$notUpToStandardVipConditions){


    	Load::logic('validate_category');
		$this->ValidateLogic = new ValidateLogic($this);
		$is_card_pic = $this->Config['card_pic_enable']['is_card_pic'];
    	    	if($this->Post['postFlag'])
    	{
    		    		$validate_info = $this->Post['validate_remark'];
    		    		$validate_info = trim(strip_tags((string) $validate_info));
    	    if(empty($validate_info)){
	    		$this->Messager('认证说明不能为空',-1);
	    	}
	    		    	$f_rets = filter($validate_info);
	    	if($f_rets && $f_rets['error'])
	    	{
	    		$this->Messager($f_rets['msg'],-1);
	    	}

	    	$category_fid = $this->Post['category_fid'];
	    	$category_id = $this->Post['category_id'];
    		if(empty($category_fid) || empty($category_id)){
    			$this->Messager('认证类别不能为空',-1);
    		}

    		$city  = (int) $this->Post['city'];
    		if($city < 1){
    			$this->Messager('请填写所在区域',-1);
    		}

    		$validate_true_name = strip_tags(jpost('validate_true_name', 'txt'));
    	    if(empty($validate_true_name)){
    			$this->Messager('真实姓名不能为空',-1);
    		}

    	    $validate_card_type = jpost('validate_card_type', 'txt');
    	    if(empty($validate_card_type)){
    			$this->Messager('证件类型不能为空',-1);
    		}

    	    $validate_card_id = strip_tags(jpost('validate_card_id', 'txt'));
    	    if(empty($validate_card_id)){
    			$this->Messager('证件号码不能为空',-1);
    		}
    		if($is_card_pic){
    			$field = 'card_pic';
    			if(empty($_FILES) || !$_FILES[$field]['name'])
				{
					$this->Messager("请上传证件图片",-1);
				}
    		}

	    				$data = array(
				'uid' 			=> MEMBER_ID,
				'category_fid'  => (int) $this->Post['category_fid'],
				'category_id'   => (int) $this->Post['category_id'],
				'province' 		=> jpost('province', 'txt'),
				'city'			=> jpost('city', 'txt'),
				'is_audit'		=> 0,
				'dateline'	    => TIMESTAMP,

			);

			$return_info = $this->ValidateLogic->Member_Validate_Add($data);

			if($return_info['ids'])
			{
							    if($is_card_pic)
		    	{
		    		$image_id = $return_info['ids'];


					if(empty($_FILES) || !$_FILES[$field]['name'])
					{
						$this->Messager("请上传证件图片",-1);
					}

					$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/'.$image_id.'/';
					$image_name = $image_id . "_o.jpg";
					$image_file = $image_path . $image_name;
					$image_file_small = $image_path.$image_id . "_s.jpg";

					if (!is_dir($image_path)) {
						jio()->MakeDir($image_path);
					}


					jupload()->init($image_path,$field,true);

					jupload()->setNewName($image_name);
					$result=jupload()->doUpload();

					if($result) {
						$result = is_image($image_file);
					}

					if (!$result) {
						$this->Messager("上传图片失败",-1);
					}


			    	list($w,$h) = getimagesize($image_file);
			        if($w > 601)
			        {
			            $tow = 599;
			            $toh = round($tow * ($h / $w));

			            $result = makethumb($image_file,$image_file,$tow,$toh);

			            if(!$result)
			            {
			                jio()->DeleteFile($image_file);
			                js_alert_output('大图片缩略失败');
			            }
			        }

		        	$image_file = addslashes($image_file);

		        	$validate_card_pic = " `validate_card_pic` = '{$image_file}' ,";

		    	}

												$sql = "update ".TABLE_PREFIX."memberfields
						set {$validate_card_pic}
							`validate_remark` = '" . (jpost('validate_remark', 'txt')) . "' ,
							`validate_true_name`='" . (jpost('validate_true_name', 'txt')) . "' ,
							`validate_card_id` = '" . (jpost('validate_card_id', 'txt')) . "' ,
							`validate_card_type` = '" . (jpost('validate_card_type', 'txt')) . "'
						where `uid`='".MEMBER_ID."'";
				$this->DatabaseHandler->Query($sql);

				if($notice_to_admin = $this->Config['notice_to_admin']){
					$message = "用户".MEMBER_NICKNAME."申请了身份认证，<a href='admin.php?mod=vipintro&code=vipintro_manage' target='_blank'>点击</a>进入审核。";
					$pm_post = array(
						'message' => $message,
						'to_user' => str_replace('|',',',$notice_to_admin),
					);
										$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
					load::logic('pm');
					$PmLogic = new PmLogic();
					$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
				}
			}

			if($return_info['msg_info'])
			{
				$this->Messager($return_info['msg_info']);
			}

    	}

    	    	$sql = "select * from `".TABLE_PREFIX."validate_category_fields` where `uid`='".MEMBER_ID."' ";
		$query = $this->DatabaseHandler->Query($sql);
		$validate_info = $query->GetRow();

		   		$sql = "select * from `".TABLE_PREFIX."memberfields` where `uid`='".MEMBER_ID."'";
		$query = $this->DatabaseHandler->Query($sql);
		$memberfields = $query->GetRow();
		$memberfields['validate_card_type'] = $memberfields['validate_card_type'] ? $memberfields['validate_card_type'] : '未知';
		$dateline = date('Y-m-d',$validate_info['dateline']);

				if(empty($validate_info['uid']) || $validate_info['is_audit'] == -1)
		{
	    				if(!$memberfields) {
				$memberfields = array();
				$memberfields['uid'] = $member['uid'];

				$sql = "insert into `".TABLE_PREFIX."memberfields` (`uid`) values ('{$member['uid']}')";
				$this->DatabaseHandler->Query($sql);
			}

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
					'其他' => array(
						'name' => '其他',
						'value' => '其他',
					),
				);

			$select_value = $memberfields['validate_card_type'] ? $memberfields['validate_card_type'] : "身份证";
			$validate_card_type_select = jform()->Select('validate_card_type',$_options,$select_value);

						$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."common_district where `upid` = '0' order by list");
			while ($rsdb = $query->GetRow()){
				$province[$rsdb['id']]['value']  = $rsdb['id'];
				$province[$rsdb['id']]['name']  = $rsdb['name'];
				if($member['province'] == $rsdb['name']){
						$province_id = $rsdb['id'];
					}
			}
			$province_list = jform()->Select("province",$province,$province_id,"onchange=\"changeProvince();\"");
			$member_city = DB::fetch_first("SELECT * FROM ".DB::table('common_district')." WHERE `name`='{$member['city']}'");
		}

    	    	$where_list = " `category_id` = '' ";
    	$query = DB::query("SELECT *
							FROM ".DB::table('validate_category')."
							where {$where_list}  ORDER BY id ASC");
		$category_list = array();
		while ($value = DB::fetch($query)) {
			$category_list[] = $value;
		}

    	    	if($this->Post['category_fid'])
    	{
    	  $sub_category_list = $this->ValidateLogic->Small_CategoryList($this->Post['category_fid']);
    	}
		}
    	$this->Title = "{$this->Config['site_name']}身份验证";
    	include(template('other/topic_vip'));
    }

	function Navigation()
    {

    	$slide_config = jconf::get('navigation');
        $slide_list = $slide_config['list'];


    	include(template('other/test_navigation'));
    }



    
	function regagreement()
  	{
  		$this->Title = '用户使用协议';
		include(template('other/register_member_agreement'));
	}

	
	function Seccode() {
		$seccode = mkseccode();
		jsg_setcookie('seccode', authcode($seccode, 'ENCODE'));
		$s = jclass('jishigou/seccode');
		$s->code = $seccode;
		$s->datapath = ROOT_PATH."images/seccode/";

		$s->display();
		exit ;
	}



	
	function UserGroupList()
	{
		if(MEMBER_ID < 1)
		{
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}
		$member = jsg_member_info(MEMBER_ID);
		if(!$member) {
			$this->Messager('用户已经不存在了');
		}

		$page_arr = array();
		$grouplist = array();
		$total_record = 0;
		$per_page_num = 15;
		$query_link = "index.php?mod=" . ($_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module) . ($this->Code ? "&amp;code={$this->Code}&ids={$this->Get['ids']}" : "");
		$p = array(
			'uid' => MEMBER_ID,
			'sql_order' => ' `id` DESC ',
			'per_page_num' => $per_page_num,
			'page_link' => $page_link,
		);
		$rets = jtable('buddy_follow_group')->get($p);
		if($rets) {
			$page_arr = $rets['page'];
			$total_record = $rets['count'];
			$grouplist = $rets['list'];
		}

		$this->Title = '管理分组';
		include(template('other/group'));
	}

	function Qmd() {
		$image_file = $this->Config['site_url'] .'/images/qmd_error.gif';

		$uid = (int) ($this->Get['ids'] ? $this->Get['ids'] : $this->Get['id']);

		/**
		 * @author 狐狸<foxis@qq.com>
		 * @todo 优化签名档生成机制、不再频繁生成
		 */
        if($this->Config['is_qmd'] && $uid > 0 && (false != ($member = jsg_member_info($uid)))) {
			if(!$member['qmd_url'] || ($this->Config['ftp_on'] && (time() > $member['lastpost'] + 1800)) || (!$this->Config['ftp_on'] && $member['lastpost'] > @filemtime($member['qmd_url']))) {
        		        		$member_qmd = ($member['qmd_img'] ? $member['qmd_img'] : 'images/qmd.jpg');

	    		$member['qmd_url'] = jlogic('other')->qmd_list($uid, $member_qmd);
			}

			if($member['qmd_url']) {
				$image_file = ($this->Config['ftp_on'] ? $member['qmd_url'] : ($this->Config['site_url'] . '/' . $member['qmd_url']));
			}
        }

        header("Location: $image_file");
	}
}

?>
