<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename seccode.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1707828600 3391 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class SeccodeLogic
{
	function SeccodeLogic()
	{
	}

	
	function GetYXM()
	{
					$html = '<style>.tips{border:none;height:16px;line-height:16px;margin:0px;}.msgBox{z-index:65535;}.YXM-title h3.title{font-size:12px;}</style>';
			$html .= '<input type="hidden" id="YXM_here" />';
			$html .= '<script type="text/javascript" charset="gbk" id="YXM_script" async src="'.YXMCURL.'yzm.yinxiangma.php?pk='.PUBLIC_KEY.'&m=live&v='.VERSION.'&a=1&j=2"></script>';
			$html .= '<script type="text/javascript" src="'.$GLOBALS['_J']['site_url'].'/static/js/seccode.js"></script>';
								return $html;
	}

	
	function CheckYXM($YinXiangMaToken,$level,$YXM_input_result)
	{
		$result = true;
					if($level == '4'){
				if($YXM_input_result== "true"){
					$result= "true";
				}else{
					$result= "false4";
				}
			}else{
				if($YXM_input_result==md5("true".PRIVATE_KEY.$YinXiangMaToken)){
					$result= "true";
				}else{
					$result= "false3";
				}
			}
								return $result;
	}

	
	function TitleYXM(){
				$yxm_title = '';
			global $_J;
			if($_J['member']['uid']){
				if($_J['config']['seccode_purview'] && isset($_J['config']['seccode_purviews'][$_J['member']['role_id']])) {
					$yxm_title = '提示：您所在用户组需要输入验证码';
				}else{
					if($_J['config']['seccode_no_email'] && !$_J['member']['email_checked']){
						$yxm_title = '提示：E-mail未验证用户需要输入验证码';
					}elseif($_J['config']['seccode_no_photo'] && strpos($_J['member']['face'],'noavatar.gif')){
						$yxm_title = '提示：未上传图象用户需要输入验证码';
					}elseif($_J['config']['seccode_no_vip'] && !$_J['member']['validate']){
						$yxm_title = '提示：非V认证用户需要输入验证码';
					}
				}
			}else{
				$yxm_title = '提示：请输入验证码';
			}
								return $yxm_title;
	}

	
	function topiccheckYXM($type='first'){
		$check = false;
					global $_J;
			if(($type=='first' && $_J['config']['seccode_publish']) || ($type=='reply' && $_J['config']['seccode_comment']) || ($type=='forward' && $_J['config']['seccode_forward']) || ($type=='both' && ($_J['config']['seccode_forward'] || $_J['config']['seccode_comment']))){
				if($_J['config']['seccode_purview'] && isset($_J['config']['seccode_purviews'][$_J['member']['role_id']])) {
					$check = true;
				}else{
					if($_J['config']['seccode_no_email'] && !$_J['member']['email_checked']){
						$check = true;
					}elseif($_J['config']['seccode_no_photo'] && strpos($_J['member']['face'],'noavatar.gif')){
						$check = true;
					}elseif($_J['config']['seccode_no_vip'] && !$_J['member']['validate']){
						$check = true;
					}
				}
			}
				return $check;
	}
}
?>