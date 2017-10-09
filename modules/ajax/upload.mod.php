<?php

/**
 *
 * upload 模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: upload.mod.php 5094 2013-11-25 02:11:19Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);

		$this->Execute();
	}

	function Execute() {
		switch($this->Code) {
			case 'face':
				$this->Face();
				break;

			default : {
					$this->Main();
					break;
				}
		}
	}

	function Main() {
		response_text('page is not exits');
	}

	
	function Face() {
		if (MEMBER_ID < 1)
		{
			js_alert_output("请先登录或者注册一个帐号",'alert');
		}
		$uid = jget('uid','int','G');
		$uid = $uid ? $uid : MEMBER_ID;
	    $member = jsg_member_info($uid);

	    if('admin' != MEMBER_ROLE_TYPE) {
	        if(!$this->Config['edit_face_enable'] && $member['__face__']){
	        	js_alert_output('本站不允许用户修改头像。','alert');
	        }
	    	if($uid != MEMBER_ID) {
	    		js_alert_output('您没有权限修改此头像');
	    	}
	    }

		$field = 'face';

				$temp_img_size = intval($_FILES[$field]['size']/1024);
		if($temp_img_size >= 2048)
		{
			js_alert_output('图片文件过大,2MB以内','alert');
		}


		$type = trim(strtolower(end(explode(".",$_FILES[$field]['name']))));
		if($type != 'gif' && $type != 'jpg' && $type != 'png' && $type != 'jpeg')
		{
			js_alert_output('图片格式不对','alert');
		}

		$image_name = substr(md5($_FILES[$field]['name']),-10).".{$type}";
		$image_path = RELATIVE_ROOT_PATH . 'images/temp/face_images/'.$image_name{0}.'/';
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			jio()->MakeDir($image_path);
		}


		jupload()->init($image_path,$field,true,false);

		jupload()->setNewName($image_name);
		$result=jupload()->doUpload();
		if($result)
		{
			$result = is_image($image_file);
		}


		if(!$result)
		{
			js_alert_output('图片上载失败','alert');
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
				js_alert_output('大图片缩略失败','alert');
			}
		}


		$up_image_path = addslashes($image_file);

		echo "<script language='Javascript'>";
		if($this->Post['temp_face'])
		{
			echo "window.parent.location.href='{$this->Config[site_url]}/index.php?mod=settings&code=face&temp_face={$up_image_path}'";
		}
		else
		{
			echo "parent.document.getElementById('cropbox').src='{$up_image_path}';";
			echo "parent.document.getElementById('img_path').value='{$up_image_path}';";
			echo "parent.document.getElementById('temp_face').value='{$up_image_path}';";
			echo "parent.document.getElementById('jcrop_init_id').onclick();";
			echo "parent.document.getElementById('cropbox_img1').value='{$up_image_path}';";
					}
		echo "</script>";
	}

}

?>
