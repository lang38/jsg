<?php
/**
 * 文件名：upload_json.php
 * 作  者：狐狸<foxis@qq.com>
 * @version $Id: upload_json.php 965300748 2014-04-25 3092 foxis@qq.com $
 * 功能描述: 接收编辑器文件上传
 */

define('IN_JISHIGOU_UPLOAD_JSON', true);
define('ROOT_PATH',substr(dirname(__FILE__),0,-9) . '/');

require(ROOT_PATH . 'include/jishigou.php');
$jishigou = new jishigou();

error_reporting(E_ALL ^ E_NOTICE);

require_once 'JSON.php';

function alert($msg) {
	@header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 1, 'message' => $msg));
	exit;
}

global $_J;
$auth = $_COOKIE["{$_J['config']['cookie_prefix']}auth"];
list($password,$uid) = ($auth ? explode("\t",authcode($auth,'DECODE')) : array('',0));
$uid = (int) $uid;

$enable = false;
if($uid > 0)
{
	$row = DB::fetch_first("select `uid`,`password`,`role_id`,`role_type` from `{$_J['config']['db_table_prefix']}members` where `uid`='{$uid}'");
    if($row && $row['password']==$password)
    {
        if(2==$row['role_id'] || 'admin'==$row['role_type'])
        {
            $enable = true;
        }
    }
}
if(!$enable)
{
    alert("您无权上载文件。");
}




$save_path = ROOT_PATH . 'images/kind/';
$save_url = $_J['config']['site_url'] . '/images/kind/';
$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
$max_size = 1000000;

if (empty($_FILES) === false) {
		$file_name = $_FILES['imgFile']['name'];
		$tmp_name = $_FILES['imgFile']['tmp_name'];
		$file_size = $_FILES['imgFile']['size'];
		if (!$file_name) {
		alert("请选择文件。");
	}
		if (@is_dir($save_path) === false) {
		alert("上传目录不存在。");
	}
		if (@is_writable($save_path) === false) {
		alert("上传目录没有写权限。");
	}
		if (@is_uploaded_file($tmp_name) === false) {
		alert("临时文件可能不是上传文件。");
	}
		if ($file_size > $max_size) {
		alert("上传文件大小超过限制。");
	}
	$temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);
		if (in_array($file_ext, $ext_arr) === false) {
		alert("上传文件扩展名是不允许的扩展名。");
	}
		$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
		$file_path = $save_path . $new_file_name;
	if (move_uploaded_file($tmp_name, $file_path) === false) {
		alert("上传文件失败。");
	}
    if(!is_image($file_path))
    {
        alert("请上传正确的图片格式。");
    }
	@chmod($file_path, 0644);
	$file_url = $save_url . $new_file_name;
	if($_J['ftp_on']) {
		$ftp_key = randgetftp();
		$get_ftps = jconf::get('ftp');
		$site_url = $get_ftps[$ftp_key]['attachurl'];
		$ftp_result = ftpcmd('upload',$file_path,'',$ftp_key);
		if($ftp_result > 0) {
			jio()->DeleteFile($file_path);
			$file_url = $site_url .'images/kind/'. $new_file_name;
		}
	}

	@header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 0, 'url' => $file_url));
	exit;
}

?>