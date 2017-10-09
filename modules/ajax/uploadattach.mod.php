<?php
/**
 * 文件名：uploadattach.mod.php
 * @version $Id: uploadattach.mod.php 5338 2014-01-02 06:35:00Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 附件上传模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $AttachLogic;

	var $type;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->AttachLogic = jlogic('attach');

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'attach':
				$this->Attach();
				break;

			case 'modify_attach':
				$this->ModAttach();
				break;

			case 'delete_attach':
				$this->DeleteAttach();
				break;

			case 'html':
				$this->Html();
				break;

			case 'down':
				$this->Down();
				break;

			case 'score':
				$this->Score();
				break;

			case 'modify':
				$this->Modify();
				break;

			default:
				$this->Main();
				break;
		}
	}

	function Main()
	{
		response_text('正在建设中……');

	}
	function Down()
	{
		global $_J;
		$attach_config = jconf::get('attach');

				if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
		{
			$response = '0,0,0,0,0';
		}
		else
		{
			$uid = MEMBER_ID;
			$aid = jget('aid','int');
			if($aid > 0){
				$down_attach_info = DB::fetch_first("SELECT * FROM ".DB::table('topic_attach')." WHERE id = '{$aid}' AND tid != 0");
			}
			if(empty($down_attach_info) || empty($down_attach_info['file']))
			{
				$response = '0,1,0,0,0';
			}
			else
			{
				$score = $down_attach_info['score'];
				$dfurl = base64_encode($down_attach_info['filesize'].'|'.$aid.'|'.$down_attach_info['dateline'].'|'.$down_attach_info['download']);
				$url = 'index.php?mod=attach&code=download&downfile='.$dfurl;
				if(in_array($_J['member']['role_id'],explode(',',$attach_config['no_score_user']))){
					$points = 1;
					$score = $score > 0 ? -1 : 0;
				}else{
					$credits = DB::result_first("SELECT credits FROM ".DB::table('members')." WHERE uid = '{$uid}'");
					$points = (int)$credits - (int)$score;
					$points = ($points == 0) ? 1 : $points;				}
				$ftptype = getftptype($down_attach_info['site_url']);
				$isaliyun = ($ftptype == 'Aliyun') ? 1 : 0;
				$target = $down_attach_info['site_url'] ? ($isaliyun ? 2 : 1) : 0;				$self = ($down_attach_info['uid'] == MEMBER_ID) ? 1 : 0;
				$response = $points . ',' . $score. ',' . $target . ',' . $self . ',' . $url;
			}
		}
		response_text($response);
	}

	function Score()
	{
		$attach_config = jconf::get('attach');
		$attach_config['score_min'] = $attach_config['score_min'] ? $attach_config['score_min'] : 1;
		$attach_config['score_max'] = $attach_config['score_max'] ? $attach_config['score_max'] : 20;
		$id = jget('id','int');
		$score = jget('score','int');
		if($score != 0){
			$score = min($attach_config['score_max'],max($attach_config['score_min'],$score));
		}
				if($id > 0 && MEMBER_ID > 0) {
			DB::query("update `".DB::table('topic_attach')."` set `score` = '$score'  where `id`='{$id}'" . ('admin' == MEMBER_ROLE_TYPE ? '' : " and `uid`='".MEMBER_ID."'"));
		}
		response_text($score);
	}

	function Html()
	{
		if($this->MemberHandler->HasPermission($this->Module,'attach')==false)
		{
			$msg = '<success></success>您没有上传附件的操作权限！';
			echo "<script type='text/javascript'>MessageBox('warning', '{$msg}');</script>";
			exit;
		}
		$tid = jget('tid','int');
		$this->item = $this->Post['item'] ? $this->Post['item'] : $this->Get['item'];
		$this->item_id = jget('itemid','int');
		$attach_uploadify_topic = array();
		if($tid > 0)
		{

			$TopicLogic = jlogic('topic');

			$attach_uploadify_topic = $TopicLogic->Get($tid);
		}


		$from = (get_param('attach_uploadify_from') ? get_param('attach_uploadify_from') : get_param('from'));
		$attach_uploadify_from = '';
		if('topic_publish' == $from)
		{
			$attach_uploadify_from = $from;
		}


		$only_js = (get_param('attach_uploadify_only_js') ? get_param('attach_uploadify_only_js') : get_param('only_js'));
		$attach_uploadify_only_js = 0;
		if($only_js)
		{
			$attach_uploadify_only_js = 1;
		}


		$topic_uid = max(0, (int) (get_param('attach_uploadify_topic_uid') ? get_param('attach_uploadify_topic_uid') : get_param('topic_uid')));
		$attach_uploadify_topic_uid = 0;
		if($topic_uid)
		{
			$attach_uploadify_topic_uid = $attach_uploadify_topic['uid'];
		}

		$attach_list_siz = max(0, (int) (get_param('attach_img_siz') ? get_param('attach_img_siz') : get_param('attach_list_siz')));
		$attach_img_siz = 32;
		if($attach_list_siz)
		{
			$attach_img_siz = $attach_list_siz;
		}


		$attach_uploadify_new = (get_param('attach_uploadify_new') ? get_param('attach_uploadify_new') : get_param('new'));

		$attach_uploadify_modify = (get_param('attach_uploadify_modify') ? get_param('attach_uploadify_modify') : get_param('modify'));

		$attach_uploadify_type = (get_param('attach_uploadify_type') ? get_param('attach_uploadify_type') : get_param('type'));

		$topic_textarea_id = (get_param('topic_textarea_id') ? get_param('topic_textarea_id') : get_param('content_id'));

		if(!is_null(get_param('topic_textarea_empty_val')))
		{
			$topic_textarea_empty_val = get_param('topic_textarea_empty_val');
		}
		include(template('attach_uploadify.inc'));
	}

	function Attach()
	{
				$item = $this->Get['aitem'];
		$itemid = max(0, (int)($this->Get['aitemid']));
				$this->_init_auth();

				$field = 'topic';
		if (empty($_FILES) || !$_FILES[$field]['name'])
		{
			$this->_attach_error('FILES is empty');
		}
		
		$_FILES[$field]['name'] = get_safe_code($_FILES[$field]['name']);  		$_FILES[$field]['name'] =  jaddslashes($_FILES[$field]['name']);

				$uid = jget('topic_uid','int')>0 ? jget('topic_uid','int') : MEMBER_ID;
		$username = jget('topic_uid','int')>0 ? '' : MEMBER_NICKNAME;

				$category = '';
		if(jget('attch_category')>0){
			$category = jlogic('attach_category')->get_attacht_cat(jget('attch_category'));
		}
				$attach_id = $this->AttachLogic->add($uid, $username, $item, $itemid, $category);
        if(jget('attch_category')>0){
            			$this->auto_add_weibo($attach_id,$_FILES[$field]['name']);
		}
		if($attach_id < 1)
		{
			$this->_attach_error('write database is invalid');
		}

		
		$this->AttachLogic->clear_invalid();
		$attach_size = min((is_numeric($this->Config['attach_size_limit']) ? $this->Config['attach_size_limit'] : 1024),51200);

		

		$attach_path = RELATIVE_ROOT_PATH . 'data/attachs/' . $field . '/' . face_path($attach_id);
		$attach_type = strtolower(end(explode('.', $_FILES[$field]['name'])));
		$attach_name = $attach_id . '.' . $attach_type;
		$attach_file = $attach_path . $attach_name;
		if (!is_dir($attach_path))
		{
			jio()->MakeDir($attach_path);
		}

		
		jupload()->init($attach_path,$field,false,true);
		jupload()->setMaxSize($attach_size);
		jupload()->setNewName($attach_name);
		$ret = jupload()->doUpload();
		if($ret)
		{
						$ret = true;
		}

				if(!$ret)
		{
			jio()->DeleteFile($attach_file);
			$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."topic_attach where `id`='$attach_id'");
			$rets = jupload()->getError();
			$ret = ($rets ? implode(" ", (array) $rets) : 'attach file is invalid');

			$this->_attach_error($ret);
		}

		$attach_size = filesize($attach_file);
				$site_url = '';
		if($this->Config['ftp_on'])
		{
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];

			$ftp_result = ftpcmd('upload',$attach_file,'',$ftp_key);
			if($ftp_result > 0)
			{
				jio()->DeleteFile($attach_file);
			}
		}

				$name = addslashes($_FILES[$field]['name']);
		$p = array(
			'id' => $attach_id,
			'site_url' => $site_url,
			'file' => $attach_file,
			'name' => $name,
			'filetype' => $attach_type,
			'filesize' => $attach_size,
		);
		$this->AttachLogic->modify($p);
				update_credits_by_action('attach_add',$uid);

				$retval = array(
			'id' => $attach_id,
			'src' => 'images/filetype/'.$attach_type.'.gif',
			'name' => $name,
		);
		$this->_attach_result('ok',$retval);
	}

		function ModAttach()
	{

		if($this->MemberHandler->HasPermission($this->Module,'attach')==false){
			$this->_mod_attach_error('您没有上传文件的权限，无法继续操作！');
		}
		$id = max(0, (int) $this->Post['id']);
		if($id < 1)
		{
			$this->_mod_attach_error('附件ID 错误');
		}
		$attach_info = $this->AttachLogic->get_info($id);

		if(!$attach_info)
		{
			$this->_mod_attach_error('您要更新的附件已经不存在了');
		}
		if(MEMBER_ROLE_TYPE != 'admin')
		{
			if(MEMBER_ID != $attach_info['uid'])
			{
				$this->_mod_attach_error('您没有权限更新该附件');
			}
		}

				if (empty($_FILES) || !$_FILES['mafile']['name'])
		{
			$this->_mod_attach_error('没有文件上传');
		}

		$attach_size = min((is_numeric($this->Config['attach_size_limit']) ? $this->Config['attach_size_limit'] : 1024),51200);

		
		$attach_path = str_replace($id.'.'.$attach_info['filetype'],'',$attach_info['file']);
		$attach_type = strtolower(end(explode('.', $_FILES['mafile']['name'])));
		$attach_name = $id . '.' . $attach_type;
		$attach_file = $attach_path . $attach_name;
		if (!is_dir($attach_path))
		{
			jio()->MakeDir($attach_path);
		}

		
		jupload()->init($attach_path,'mafile',false,true);
		jupload()->setMaxSize($attach_size);
		jupload()->setNewName($attach_name);
		$ret = jupload()->doUpload();
		if($ret)
		{
						$ret = true;
		}

				if(!$ret)
		{
			jio()->DeleteFile($attach_file);
			$rets = jupload()->getError();
			$ret = ($rets ? implode(" ", (array) $rets) : 'attach file is invalid');
			$this->_mod_attach_error($ret);
		}

				$site_url = '';
		if($this->Config['ftp_on'])
		{
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];

			$ftp_result = ftpcmd('upload',$attach_file,'',$ftp_key);
			if($ftp_result > 0)
			{
								jio()->DeleteFile($attach_file);
			}
		}

				$attach_size = filesize($attach_file);
		$name = addslashes($_FILES['mafile']['name']);

		$p = array(
			'id' => $id,
			'site_url' => $site_url,
			'file' => $attach_file,
			'name' => $name,
			'filetype' => $attach_type,
			'filesize' => $attach_size,
		);
		$this->AttachLogic->modify($p);

				$retval = array('src' => 'images/filetype/'.$attach_type.'.gif','name' => $name,'size' => ($attach_size > 1024*1024) ? round($attach_size/(1024*1024),2).'MB' : ($attach_size == 0 ? '未知' : round($attach_size/1024,1).'KB'));
		echo "<script type='text/javascript'>window.parent.aupcomplete({$id},'{$retval['src']}','{$retval['name']}','{$retval['size']}');</script>";
	}

		function DeleteAttach()
	{

		if(MEMBER_ID < 1)
		{
			json_error("请先登录或者注册一个帐号");
		}

		$id = jget('id','int');
		$topic_attach = $this->AttachLogic->get_info($id);
		if(!$topic_attach)
		{
			json_error('请指定一个正确的文件ID');
		}

		if(jdisallow($topic_attach['uid']))
		{
			json_error('您无权删除该文件');
		}


		$ret = $this->AttachLogic->delete($id);
				update_credits_by_action('attach_del',$topic_attach['uid']);
		if(!$ret)
		{
			json_error('删除失败');
		}


		json_result('删除成功');
	}

		function Modify()
	{
		if(MEMBER_ID < 1){
			response_text("您没有权限编辑该附件，请您先登陆！");
		}
		$id = $modify_id = max(0, (int) $this->Post['id']);
		if($id < 1)
		{
			js_alert_output('附件ID 错误');
		}
		$attach_info = $this->AttachLogic->get_info($modify_id);
		if(!$attach_info)
		{
			response_text('您要编辑的附件已经不存在了');
		}
		if(MEMBER_ROLE_TYPE != 'admin')
		{
			if(MEMBER_ID != $attach_info['uid'])
			{
				response_text("您没有权限编辑该附件，只有后台管理员和附件上传者可进行该操作！");
			}
		}
		include(template('modify_attach_ajax'));
	}

	function _init_auth()
	{
		$type = ($this->Post['type'] ? $this->Post['type'] : $this->Get['type']);
		$this->Type = $type;

		if('normal' == $type)
		{

						if($this->MemberHandler->HasPermission($this->Module,$this->Code)==false)
			{
				$this->_attach_error('您没有上传文件的权限，无法继续操作！');
			}
		}
		else
		{
			$uid = 0;
			$password = '';
			$members = array();

			$cookie_auth = ($this->Post['cookie_auth'] ? $this->Post['cookie_auth'] : $this->Get['cookie_auth']);

			list($password,$uid) = ($cookie_auth ? explode("\t", authcode(str_replace(' ', '+', $cookie_auth), 'DECODE')) : array('', 0));

			if($uid > 0)
			{
				$members = DB::fetch_first("select `uid`, `username`, `nickname`, `role_type`, `role_id` from ".TABLE_PREFIX."members where `uid`='$uid'");
			}

			if(!$members)
			{
				json_error('auth is invalid');
			}
			else
			{
				$role_id = $members['role_id'];
				$role_privilege = DB::result_first("select `privilege` from ".TABLE_PREFIX."role where `id`='$role_id'");
				$current_action_id = DB::result_first("select `id` from ".TABLE_PREFIX."role_action where `module`='uploadattach' and `action`='attach'");
				if(strpos(",".$role_privilege.",",",".$current_action_id.",")===false)
				{
					json_error('forbidden');
				}
				else
				{
					$topic_uid = jget('topic_uid','int');
					if($topic_uid > 0 && $topic_uid != $uid && 'admin'==$members['role_type'])
					{
						$members = DB::fetch_first("select `uid`, `username`, `nickname`, `role_type` from ".TABLE_PREFIX."members where `uid`='$topic_uid'");
					}
					define('MEMBER_ID', $members['uid']);
					define('MEMBER_NAME', $members['username']);
					define('MEMBER_NICKNAME', $members['nickname']);
					define('MEMBER_ROLE_TYPE', $members['role_type']);
				}
			}
		}
	}

	function _mod_attach_error($msg)
	{
		echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');window.parent.dmat();</script>";
		exit ;
	}

	function _attach_error($msg)
	{
		if('normal' == $this->Type)
		{
						echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');
			window.parent.attachUploadifyAllComplete{$attach_uploadify_id}();</script>";
			exit ;
		}
		else
		{
			json_error($msg);
		}
	}
	function _attach_result($msg, $retval=null)
	{
		if('normal' == $this->Type)
		{
						$attach_uploadify_id = ($this->Post['attach_uploadify_id'] ? $this->Post['attach_uploadify_id'] : $this->Get['attach_uploadify_id']);

			echo "<script type='text/javascript'>
			window.parent.attachUploadifyComplete{$attach_uploadify_id}('{$retval['id']}', '{$retval['src']}', '{$retval['name']}');
			window.parent.attachUploadifyAllComplete{$attach_uploadify_id}('{$retval['name']}');
			</script>";
			exit ;
		}
		else
		{
			json_result($msg, $retval);
		}
	}

    
    public function auto_add_weibo($aid,$filename){
        $weibo = "分享文件:{$filename}";
        $topic = jlogic('topic')->Add($weibo);
        $r = jlogic('attach')->modify(array('id' => $aid,'tid' => $topic['tid'],));
        return $r;
    }

}

?>
