<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename verify.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 952665651 6531 $
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

	
	function Execute(){
		ob_start();
		switch($this->Code)
		{
			case 'edit':
				$this->edit();
				break;
			case 'doedit':
				$this->doEdit();
				break;
			case 'deletepic':
				$this->deletePic();
				break;
			case 'doverify':
				$this->doVerify();
				break;
			default:
				$this->Main();
				break;
		}
		$body = ob_get_clean();
		$this->ShowBody($body);
	}

		function Main(){
		$code = $this->Code;
		$per_page_num = min(500, max(20, (int) (isset($_GET['pn']) ? $_GET['pn'] : $_GET['per_page_num'])));
		$query_link = 'admin.php?mod=topic&code='.$this->Code;

		$total_record = DB::result_first("select count(*) from ".TABLE_PREFIX."members_verify");
		$page_arr = page($total_record,$per_page_num,$query_link,array('return'=>'array',),'20 50 100 200,500');

		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members_verify order by uid $page_arr[limit]");
		$members_verify = array();
		while ($rsdb = $query->GetRow()){
			$rsdb['face'] = $rsdb['face_url'].'/'.$rsdb['face'];
			$members_verify[$rsdb['id']] = $rsdb;
		}

		$this->Code = 'fs_verify';
		include(template('admin/face_sign_verify'));
	}

	
	function doVerify(){
		$act = $this->Get['act'];
		$uids = array();
		$uid = (int) $this->Get['uid'];
		$uids = $this->Post['uids'];
		if($uid){
			$uids[$uid] = $uid;
		}
		$msg = jget('msg');

				if($act == 'yes'){
			if($uids){
				foreach ($uids as $uid) {
					if($uid < 1) continue;
					$message = '';
					$nickname = DB::result_first("select `nickname` from `".TABLE_PREFIX."members` where `uid` = '$uid'");
					$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members_verify where `uid` = '$uid'");
					$member_verify = $query->GetRow();
					if($member_verify){
						if($member_verify['face'] || $member_verify['face_url']){
														$image_path = RELATIVE_ROOT_PATH . 'images/face/' . face_path($uid);
							if(!is_dir($image_path))
							{
								jio()->MakeDir($image_path);
							}
														$image_file_b = $dst_file = $image_path . $uid . '_b.jpg';
														$image_file_s = $dst_file = $image_path . $uid . '_s.jpg';
														$image_verify_path = RELATIVE_ROOT_PATH . 'images/face_verify/' . face_path($uid);
														$image_verify_file_b = $dst_file = $image_verify_path . $uid . '_b.jpg';
														$image_verify_file_s = $dst_file = $image_verify_path . $uid . '_s.jpg';

							 
							if ($member_verify['face_url']) {
								$ftp_key = getftpkey($member_verify['face_url']);
								if($ftp_key < 0){
									$this->Messager('请检查FTP是否可用');
								}
																ftpcmd('get',$image_file_b, $image_verify_file_b,$ftp_key);
								ftpcmd('get',$image_file_s, $image_verify_file_s,$ftp_key);
																$ftp_result = ftpcmd('upload',$image_file_b,'',$ftp_key);
								$ftp_result = ftpcmd('upload',$image_file_s,'',$ftp_key);

								$sql = "update `".TABLE_PREFIX."members` set `face`='{$image_file_s}', `face_url`='{$member_verify['face_url']}' where `uid`='".$uid."'";
								$this->DatabaseHandler->Query($sql);
							} else if ($member_verify['face']){
								@copy($image_verify_file_b,$image_file_b);
								@copy($image_verify_file_s,$image_file_s);
								$sql = "update `".TABLE_PREFIX."members` set `face`='{$image_file_s}' where `uid`='".$uid."'";
								$this->DatabaseHandler->Query($sql);
							}

						    
					        if($this->Config['extcredits_enable'] && $member_verify['uid'] > 0)
							{
								
								update_credits_by_action('face',$member_verify['uid']);
							}
							$message .= '你更新的头像已经通过审核，可以通过ctrl+f5强制刷新来查看新头像;';
						}

						if($member_verify["signature"]){
														$sql = "update ".TABLE_PREFIX."members set signature = '$member_verify[signature]',signtime = '".time()."' where uid = '$uid' ";
							$this->DatabaseHandler->Query($sql);
							$message .= '你更新的签名已经更过审核;';
						}
						$this->DatabaseHandler->Query("delete from ".TABLE_PREFIX."members_verify where uid = '$uid'");
						$pm_post = array(
							'message' => $message,
							'to_user' => $nickname,
						);
						jlogic('pm')->pmSend($pm_post);
					}
				}
			}
		}
				else{
			if($msg){
				$to_user = DB::result_first("select `nickname` from `".TABLE_PREFIX."members` where `uid` = '$uid'");
				if($to_user){
					$pm_post = array(
						'message' => $msg,
						'to_user' => $to_user,
					);
					jlogic('pm')->pmSend($pm_post);
				}
			}
			$this->DatabaseHandler->Query("delete from `".TABLE_PREFIX."members_verify` where `uid` = '$uid'");
		}
		$this->Messager("操作成功");
	}

	
	function edit(){
		$uid = (int) $this->Get['uid'];
		if($uid < 0){
			$this->Messager("请选择要编辑的用户资料");
		}
		$sql = "select * from ".TABLE_PREFIX."members_verify where uid = '$uid'";
		$query = $this->DatabaseHandler->Query($sql);
		$member_verify = $query->GetRow();

		include(template('admin/setting_verify'));
	}

	function doEdit(){
		$uid = (int) $this->Post['uid'];
		$signature = $this->Post['signature'];
		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members_verify set signature = '$signature' where uid = '$uid'");
		$this->Messager("编辑成功");
	}

		function deletePic(){
		$uid = (int) $this->Get['uid'];
		if($uid < 0){
			$this->Messager("请选择要编辑的用户资料");
		}

		$this->DatabaseHandler->Query("update ".TABLE_PREFIX."members_verify set face = '', face_url = '' where uid = '$uid'");
		$this->Messager("图片删除成功");
	}
}