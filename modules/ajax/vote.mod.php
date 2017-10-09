<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename vote.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 2022797237 23304 $
 */





if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

	function ModuleObject($config)
	{
		$this->MasterObject($config);


		Load::logic('vote');
		$this->VoteLogic = new VoteLogic();


		$this->TopicLogic = jlogic('topic');

		$code = &$this->Code;
		if (empty($code)) {
			$code = 'index';
		}

		if (!in_array($code, array('joined', 'daren','onLoadPic','voteWeekHot','daren','voteMonthHot'))) {
						$this->_check_login();
		}

		if (method_exists('ModuleObject', $code)) {
			$this->$code();
		} else {
			exit;
		}
	}

	
	function create()
	{
		        if(!($this->MemberHandler->HasPermission($this->Module,$this->Code)))
        {
            json_error($this->MemberHandler->GetError());
        }

		$data = $this->Post;
				$member = $GLOBALS['_J']['member'];
		$data['username'] = addslashes($member['username']);
		$data['uid'] = $member['uid'];

				$data['item'] = $this->Get['item'];
		$data['item_id'] = (int) $this->Get['item_id'];

		if($this->Config['vote_verify']){
			$data['verify'] = 0;
		}

		$result = array();
		$ret = $this->VoteLogic->create($data, $result);
		if ($ret > 0) {
			$sys_config = jconf::get();
			$value = '我发起了一个投票【'.$result['subject'].'】，地址：' . get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$result['vid']);
						$values = array(
				'content' => $value,
				'vid' => $result['vid'],
				'item' => 'vote',
			);
			json_result('发布成功，顺便评论下', $values);
		} else {
			if ($ret == -1) {
				json_error("投票主题长度不能小于两个字节。");
			} else if ($ret == -2) {
				json_error("只有一个投票项不允许发布。");
			} else if ($ret == -3) {
				json_error("投票截止时间小于当前时间。");
			}
		}
	}

		function edit()
	{
		$vid = intval($this->Post['vid']);
		$options = $this->Post['old_option'];
		$new_options = $this->Post['option'];
		$old_pic = jget('old_pic');
		$new_pic = jget('pic_id');
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote)|| $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if (jdisallow($vote['uid'])) {
			json_error('你没有权限');
		}
        load::logic('vote');
        $VoteLogic = new VoteLogic();
		        $is_voted = $VoteLogic->get_count_vote_user($vid);

		$no_chk_maxchoice = false;
		if ($is_voted) {
			$no_chk_maxchoice = true;
		}

		$post_data = $this->Post;
		$params = array(
			'no_chk_option' => true,
			'no_chk_maxchoice' => $no_chk_maxchoice,
		);
		$ret = $this->VoteLogic->chk_post($post_data, 'modfiy', $params);
		if ($ret == -1) {
			json_error("投票主题长度不能小于两个字节。");
		} else if ($ret == -3) {
			json_error("投票截止时间小于当前时间。");
		}

		$where_ary = array('vid' => $vid);
				if ($no_chk_maxchoice) {
			$set_ary = array(
				'subject' => $post_data['subject'],
				'is_view' => $post_data['is_view'],
				'expiration' => $post_data['expiration'],
			);
		} else {
			$set_ary = array(
				'subject' => $post_data['subject'],
				'maxchoice' => $post_data['maxchoice'],
				'multiple' => $post_data['maxchoice'] > 1 ? 1 : 0,
				'is_view' => $post_data['is_view'],
				'expiration' => $post_data['expiration'],
			);
		}
				$set_ary['time_val'] = max(0, (int) $post_data['time_val']);
		$set_ary['time_unit'] = in_array($post_data['time_unit'], array('y', 'm', 'd', 'h', 'i', 's', )) ? $post_data['time_unit'] : 'h';
		$set_ary['vote_limit'] = max(0, min(100, (int) $post_data['vote_limit']));

        $VoteLogic->update_vote($vid, $set_ary);
		        $VoteLogic->update_vote_field($vid, array('message'=>$post_data['message']));

		$this->VoteLogic->update_options($vid, $options, $new_options, $is_voted , $old_pic, $new_pic);
		json_result('编辑投票项成功',$vid);
	}

	
	function vote()
	{
	            if(!($this->MemberHandler->HasPermission($this->Module,$this->Code)))
        {
            json_error($this->MemberHandler->GetError());
        }

                $chk_topic_type = true;

	   	$tid = empty($this->Post['tid']) ? 0 : trim($this->Post['tid']);
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		$vote = $this->VoteLogic->id2voteinfo($vid);
		$member = $GLOBALS['_J']['member'];
		if(empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}

		$toweibo = $this->Post['toweibo'] == 1 ? true : false;

				if (TIMESTAMP >= $vote['expiration']) {
			json_error('当前投票已经过期了');
		}

				$option = $this->Post['option'];
		if (empty($option)) {
			json_error('你还没有选择呢');
		}

		$anonymous = $this->Post['anonymous'];

		$param = array(
			'vid' => $vid,
			'uid' => $member['uid'],
			'username' => $member['username'],
			'maxchoice' => $vote['maxchoice'],
			'option' => $option,
			'anonymous' => $anonymous,
			'follow_vote' => $this->Post['follow_vote'],
			'create_uid' => $vote['uid'],			);

		$result = array();
		$ret = $this->VoteLogic->do_vote($param, $result);
		switch ($ret) {
			case 1:
				$msg = "投票成功，顺便评论下";

								if ($toweibo && empty($anonymous)) {
					if (!empty($tid)) {
						$__handle_key = $tid;
					}
					$sys_config = $this->Config;
					
                    $item = "vote";
					$item_id = $vid;
					
					include template('vote/vote_toweibo');
					exit;
				} else {
					$retval = array(
						'toweibo' => false,
						'vid' => $vote['vid'],
					);
				}
				json_result($msg, $retval);
				break;
			case -1:
				json_error('您已经投过票了，不允许重复投票');
				break;
			case -2:
				json_error("至多允许选择{$vote['maxchoice']}项目");
				break;
			case -3:
				json_error("投票项不存在");
				break;
		}
	}

		function del()
	{
		$id = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		if ($id) {
			$ret = $this->VoteLogic->delete($id);
		}
		if (!empty($ret)) {
			json_result('删除投票成功');
		} else {
			json_error('删除投票失败');
		}
	}

		function modify_date()
	{
		$vid = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		$expiration = empty($this->Post['expiration']) ? '' : trim($this->Post['expiration']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}

		if (jdisallow($vote['uid'])) {
			json_error("你没有权限");
		}

		$ret = $this->VoteLogic->modify_expiration($vid, $expiration);
		if ($ret == 1) {
			json_result('修改截止日期成功');
		} else if ($ret == -1){
			json_error('截止时间不能小于当前时间');
		}
	}

			function add_opt()
	{
		$vid = empty($this->Post['vid']) ? 0 : intval($this->Post['vid']);
		$option = empty($this->Post['option']) ? '' : trim($this->Post['option']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if (jdisallow($vote['uid'])) {
			json_error('你没有权限');
		}
		$old_options = unserialize($vote['option']);
		$ret = $this->VoteLogic->add_opt($vid, $option);
		if ($ret == 1) {
			json_result('增加投票项成功');
		} else if ($ret == -1){
			json_error('超过了最大的投票项');
		} else if ($ret == -2) {
			json_error('新投票项的长度不符合要求');
		}
	}

			function edit_opt()
	{
		$vid = intval($this->Post['vid']);
		$options = $this->Post['option'];
		$new_options = $this->Post['new_option'];
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}
		if (jdisallow($vote['uid'])) {
			json_error('你没有权限');
		}
		$old_options = unserialize($vote['option']);
		$preview_updata_flg = false;

				if (!empty($options)) {
						$count = 0;
			if (MEMBER_ROLE_TYPE != 'admin') {

                                $count = $this->VoteLogic->get_count_vote_user($vid);
			}

			if (!$count) {
				$preview = array();
				$keys = array_keys($options);
				$options = array_unique($options);

								if (count($options) > 1) {
					foreach ($keys as $i) {
						if (!empty($options[$i])) {
														$val = $options[$i];
							$p = getstr(trim($val), 40, 1, 1);
							if (empty($p)) {
								continue;
							}
							$this->VoteLogic->update_options($i, array('option'=>$p));
						} else {
							                            $this->VoteLogic->delete_vote_option($i);
						}
					}
					$preview_updata_flg = true;
				}
			}
		}

				if (!empty($new_options)) {
			$new_options = array_unique($new_options);
			foreach ($new_options as $val) {
				$ret = $this->VoteLogic->add_opt($vid, $val);
			}
		}

		if ($preview_updata_flg) {
						$preview = array();
			$options = $this->VoteLogic->get_option_by_vid($vid);
			foreach ($options['option'] as $val) {
				if(count($preview) < 2 ) {
					$preview[] = $val['option'];
				}
			}
			$str_options = addslashes(serialize($preview));
            $this->VoteLogic->update_vote_field(array('vid'=>$vid), array('option'=>$str_options));
		}
		json_result('编辑投票项成功');
	}

	
	function vote_publish($tab='word')
	{
		if(MEMBER_ID < 1){
			$is_allowed = "请先登录或者注册一个帐号";
		}

				if (MEMBER_ROLE_TYPE != 'admin' && !$is_allowed) {
			load::logic('vote');
			$VoteLogic = new VoteLogic();
			$is_allowed = $VoteLogic->allowedCreate(MEMBER_ID);
		}
		if($is_allowed){
			exit($is_allowed);
		}

		$max_option = 50;
		$perpage = 5;
		$options = range(1, $perpage);
				for($i=0;$i<$perpage;$i++){
			$opts[$i]['picurl'] = 'images/none.png';
		}
		$exp_info = $this->VoteLogic->get_publish_form_param();
		extract($exp_info);
		include (template('vote/vote_publish'));
	}

    function pic_vote_publish(){
        $tab = jget('tab') ? jget('tab') : 'word';
        $this->vote_publish($tab);
    }

		function follow_vote(){
        $uid = jget('uid','int','P');
        $vid = jget('vid','int','P');
		$follow = jget('follow','int','P');
		if($vid > 0 && (jallow($uid))) {

			$this->VoteLogic->update_vote_user(array('vid'=>$vid , 'uid'=>$uid),array('follow_vote'=>$follow));
		}
    }

	
	function my_vote()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;

		$uid = MEMBER_ID;
        $where_sql = " uid='{$uid}' AND verify = 1 ";
		$count = $this->VoteLogic->count_vote(array('uid'=>$uid,'verif'=>1));
		if ($count) {


            $list = $this->VoteLogic->get_vote_subject($where_sql,$start,$perpage);

			$multi = ajax_page($count, $perpage, $page, 'getMyVoteList');
		}
		include(template('vote/vote_list_my_ajax'));
	}

	
	function my_join()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$perpage = 8;
		if ($page == 0) {
			$page = 1;
		}
		$start = ($page - 1) * $perpage;

		$uid = MEMBER_ID;
		$where_sql = " vu.uid='{$uid}' ";
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." AS vu WHERE {$where_sql}");
		if ($count) {
			$query = DB::query("SELECT v.vid,v.subject
					   FROM ".DB::table('vote_user')." AS vu
					   LEFT JOIN ".DB::table("vote")." AS v
					   USING (vid)
					   WHERE $where_sql
					   ORDER BY vu.dateline DESC
					   LIMIT {$start},{$perpage}");
			while ($value = DB::fetch($query)) {
				$value['vote_url'] = get_full_url($sys_config['site_url'],'index.php?mod=vote&code=view&vid='.$value['vid']);
				$value['radio_value'] = str_replace(array('"', '\''), '', $value['subject']).' - '.$value['vote_url'];
				$list[] = $value;
			}
			$multi = ajax_page($count, $perpage, $page, 'getMyJoinList');
		}
		include(template('vote/vote_list_my_ajax'));
	}

	
	function joined()
	{
		$page = empty($this->Get['page']) ? 0 : intval($this->Get['page']);
		$type = trim($this->Get['type']);
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		if ($page == 0) {
			$page = 1;
		}
		$prepage = 6;
		$start = ($page - 1) * $prepage;
		$where_sql = " 1 ";
		$page_param = array();
		if ($type == 'follow') {
			$this->_check_login();
			$buddy_ids = get_buddyids(MEMBER_ID);
			$where_sql .= " AND vu.vid='{$vid}' AND uid IN(".jimplode($buddy_ids).") ";
			$page_param = array('c'=>2);
		} else {
			$type = 'all';
			$where_sql .= " AND vu.vid='{$vid}' ";
			$page_param = array('c'=>1);
		}
		$order_sql = " vu.dateline DESC ";
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('vote_user')." AS vu WHERE {$where_sql}");
		if ($count) {
			$query = DB::query("SELECT vu.*,m.nickname
					   FROM ".DB::table('vote_user')." AS vu
					   LEFT JOIN ".DB::table("members")." AS m
					   USING (uid)
					   WHERE $where_sql
					   ORDER BY $order_sql
					   LIMIT {$start},{$prepage}");
			while ($value = DB::fetch($query)) {
				$value['option'] = unserialize($value['option']);
				$value['option'] = '"'.implode('","', $value['option']).'"';
				$value['dateline'] = my_date_format2($value['dateline']);
								if (empty($value['username'])) {
					$value['nickname'] = '**';
				}
				$list[] = $value;
			}
			$multi = ajax_page($count, $prepage, $page, 'getVoteJoined', $page_param);
		}
		include template('vote/vote_ajax_joined');
	}

	
	function daren()
	{
		$uids = array();
		$param = array(
			'where' => ' voter_num>0 ',
			'order' => ' voter_num DESC ',
			'limit' => ' 12 ',
		);
		$info = $this->VoteLogic->get_list($param);
		if (!empty($info)) {
			foreach ($info as $val) {
				$uids[] = $val['uid'];
			}
			if (!empty($uids)) {
				$vote_darens = $this->TopicLogic->GetMember($uids);
				include(template('vote/vote_daren_list_ajax'));
			}
		}
		exit;
	}

	
	function voteWeekHot(){
		$range_day = 7*24;
		$timerange = TIMESTAMP - $range_day*3600;
		$where_sql = " v.lastvote >= '{$timerange}' ";
		$order_sql = " ORDER BY v.voter_num DESC";
		$param = array(
			'where' => $where_sql,
			'order' => $order_sql,
			'limit' => ' limit 12 ',
		);

		$vote_info = $this->VoteLogic->find($param);
		$vote_list = $vote_info['vote_list'];
		if($vote_list){
			include(template('vote/vote_daren_list_ajax'));
		}
		exit;
	}

	
	function voteMonthHot(){
		$range_day = 30*24;
		$timerange = TIMESTAMP - $range_day*3600;
		$where_sql = " v.lastvote >= '{$timerange}' ";
		$order_sql = " ORDER BY v.voter_num DESC";
		$param = array(
			'where' => $where_sql,
			'order' => $order_sql,
			'limit' => ' limit 12 ',
		);

		$vote_info = $this->VoteLogic->find($param);
		$vote_list = $vote_info['vote_list'];
		if($vote_list){
			include(template('vote/vote_daren_list_ajax'));
		}
		exit;
	}

	
	function manage()
	{
		$tab = 'word';
		$op = empty($this->Get['op']) ? '' : $this->Get['op'];
		if (empty($op)) {
			exit;
		}
		$vid = empty($this->Get['vid']) ? 0 : intval($this->Get['vid']);
		$vote = $this->VoteLogic->id2voteinfo($vid, 'm');
		if($vote['tab']){
			$tab = 'pic';
		}
		if (empty($vote) || $vote['verify'] == 0) {
			json_error('当前投票不存在或正在审核中');
		}

		if (jdisallow($vote['uid'])) {
			json_error("你没有权限");
		}

		if ($op == 'modify_date') {
			$exp_info = $this->VoteLogic->get_publish_form_param($vote['expiration']);
			extract($exp_info);
		} else if ($op == 'edit_opt') {
						$info = $this->VoteLogic->get_option_by_vid($vid);
			$options = $info['option'];
			$option_num = count($info['option']);

						if (MEMBER_ROLE_TYPE != 'admin') {
				$is_voted = $this->VoteLogic->get_count_vote_user($vid);
			}
		} else if ($op == 'edit') {
						$max_option = 50;
			$this->Get['arf'] = "edit";
			$opt_info = $this->VoteLogic->get_option_by_vid($vid);
			$opts= $opt_info['option'];

						$vote['message'] = $this->VoteLogic->get_vote_field_message($vid);

						$options_num = count($opts);
			$maxchoice = array();
			if ($options_num > 1) {
				$maxchoice = range(1, $options_num);
			}

						
			$perpage = ceil($options_num/5)*5;
			$options = range(1, $perpage);
						for($i=$options_num;$i<$perpage;$i++){
				$opts[$i]['picurl'] = 'images/none.png';
			}

						$is_voted = $this->VoteLogic->get_count_vote_user($vid);

			$checked = array();
			$checked['is_view'][$vote['is_view']] = 'checked="checked"';
			$checked['recd']= $vote['recd'] ? 'checked="checked"' : '';
			$selected[$vote['maxchoice']] = 'selected="selected"';
			$expiration = my_date_format($vote['expiration'], 'Y-m-d');
			$hour_select = mk_time_select('hour', my_date_format($vote['expiration'], 'H'));
			$min_select = mk_time_select('min', my_date_format($vote['expiration'], 'i'));
			include(template('vote/vote_edit'));
			exit;
		}
		include(template('vote/vote_manage'));
	}

	
	function detail()
	{
		$vid = intval($this->Post['vid']);
		$uid = MEMBER_ID;
		$tid = trim($this->Post['tid']);
		$vote = $this->VoteLogic->id2voteinfo($vid);
		if(empty($vote) || $vote['verify'] == 0) {
			response_text('当前投票不存在或正在审核中!');
		}
		$ret = $this->VoteLogic->process_detail($vote, MEMBER_ID);
		extract($ret);
				$member = $this->TopicLogic->GetMember($vote['uid']);
		include(template('widget/widget_vote_view'));
	}

	
	function toweibo()
	{
		include (template('vote/vote_toweibo'));
	}

	
	function _check_login()
	{
		if (MEMBER_ID < 1) {
			json_error("你需要先登录才能继续本操作");
		}
	}

	
	function loadpic(){
		if(!empty($_FILES['image']['name'])){
			$vid = jget('vid','int');
			$this->VoteLogic->upload_pic($vid);
		}
	}
	function onLoadPic(){
		if(!($this->MemberHandler->HasPermission($this->Module, 'create'))) {
            js_alert_output($this->MemberHandler->GetError());
        }

        $id = jget('id','int');
        $file_name = 'pic'.$id;

				if (MEMBER_ROLE_TYPE != 'admin' && !$is_allowed) {
			load::logic('vote');
			$VoteLogic = new VoteLogic();
			$is_allowed = $VoteLogic->allowedCreate(MEMBER_ID);
		}
		if($is_allowed){
			js_alert_output($is_allowed);
		}

        



        if($_FILES[$file_name]['name']){
			$name = time().MEMBER_ID;
			$image_name = $name."_b.jpg";
			$image_path = RELATIVE_ROOT_PATH . 'images/vote/';
			$image_file = $image_path . $image_name;
            $image_th_file = $image_path . $name."_th.jpg";

			if (!is_dir($image_path))
			{
				jio()->MakeDir($image_path);
			}

			jupload()->init($image_path,$file_name,true);

			jupload()->setNewName($image_name);
			$result=jupload()->doUpload();

			if($result)
	        {
				$result = is_image($image_file);
			}
			if(!$result)
	        {
				unlink($image_file);
				echo "<script language='Javascript'>";
				echo "parent.document.getElementById('message').style.display='block';";
				echo "parent.document.getElementById('uploading').style.display='none';";
				echo "parent.document.getElementById('message').innerHTML='图片上载失败'";
				echo "</script>";
				exit;
			}
			image_thumb($image_file,$image_th_file,100,100,1,0,0);

            if($this->Config['ftp_on']) {
	            $ftp_key = randgetftp();
				$get_ftps = jconf::get('ftp');
	            $face_url = $get_ftps[$ftp_key]['attachurl'];
	            $ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
	            if($ftp_result > 0) {
	                jio()->DeleteFile($image_file);

	                $image_file = $face_url .'/'. str_replace('./','',$image_file);
	            }
                                $ftp_result = ftpcmd('upload',$image_th_file,'',$ftp_key);
	            if($ftp_result > 0) {
	                jio()->DeleteFile($image_th_file);

	                $image_th_file = $face_url .'/'. str_replace('./','',$image_th_file);
	            }

	        }

			#插入数据库

			$image_id = $this->VoteLogic->insert_vote_img(MEMBER_ID,$image_th_file,$image_file);

			echo "<script language='Javascript'>";
			echo "parent.document.getElementById('pic_show_$id').src='{$image_th_file}';";
			echo "parent.document.getElementById('pic_id_$id').value='$image_id';";
			echo "</script>";
			exit;
        }
	}
}
?>
