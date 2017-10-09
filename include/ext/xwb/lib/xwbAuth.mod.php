<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwbAuth.mod.php $
 *
 * @Author 狐狸<foxis@qq.com> $
 *
 * @version $Id: xwbAuth.mod.php 3965 2013-07-03 03:07:57Z wuliyong $
 */

class xwbAuth {

	var $redirect_to = '';

	function xwbAuth (){
		$rto = jsg_getcookie('referer');
        $rto = ($rto ? $rto : 'index.php');
        $this->redirect_to = $rto;
	}

	function default_action(){$this->login();}


	function login(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}

		if (jsg_getcookie("referer")=="") {
			jsg_setcookie("referer", referer('?', 1));
		}

		$aurl = $this->_getOAuthUrl();
		$sess = XWB_plugin::getUser();
		//$sess->clearToken();
		$sess->setInfo('waiting_site_bind',	1);
		XWB_plugin::redirect($aurl, 3);
	}

	/// 测试方法
	function show(){
		exit('DEBUG IS DISABLED');
		$wbApi		= XWB_plugin::getWB();
		echo '<pre>';
		print_r($wbApi->verifyCredentials());
		echo '</pre>';
	}

	// 从OAUTH登录时的回调模块
	function authCallBack(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('新浪微博绑定功能已经关闭！');
		}

		//--------------------------------------------------------------------
		$sess = XWB_plugin::getUser();
		$waiting_site_bind = $sess->getInfo('waiting_site_bind');
		if (empty($waiting_site_bind)){
			//XWB_plugin::deny();
			$siteUrl = XWB_plugin::siteUrl(0);
			XWB_plugin::redirect($siteUrl, 3);
		}
		//--------------------------------------------------------------------
		$wbApi		= XWB_plugin::getWB();
		$db			= XWB_plugin::getDB();
		$last_key	= $wbApi->getAccessToken(XWB_plugin::V('r:oauth_verifier'));

		$sess->setOAuthKey(array(),true);

//		print_r($last_key);
		if( !isset($last_key['oauth_token']) || !isset($last_key['oauth_token_secret']) ){
			$api_error_origin = isset($last_key['error']) ? $last_key['error'] : 'UNKNOWN ERROR. MAYBE SERVER CAN NOT CONNECT TO SINA API SERVER';
			$api_error = ( isset($last_key['error_CN']) && !empty($last_key['error_CN']) && 'null' != $last_key['error_CN'] ) ? $last_key['error_CN'] : '';

			XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$wbApi->req_error_count}\t{$api_error}\t{$wbApi->last_req_url}\tERROR ARRAY:\r\n".print_r($last_key, 1));
			XWB_plugin::showError("服务器获取Access Token失败；请稍候再试。<br />错误原因：{$api_error}[{$api_error_origin}]");
		}

		$sess->setOAuthKey($last_key, true);
		$wbApi->setConfig();
		$uInfo = $wbApi->verifyCredentials();
		$sess->setInfo('sina_uid', $uInfo['id']);
		$sess->setInfo('sina_name', $uInfo['screen_name']);
		//print_r($uInfo);
		//--------------------------------------------------------------------
		/// 此帐号是否已经在当前站点中绑定
		$sinaHasBinded = false;
		if(defined('XWB_S_UID') && XWB_S_UID > 0){
			$bInfo = $db->fetch_first("SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info WHERE sina_uid='".$uInfo['id']."' OR uid='".XWB_S_UID ."'");
		}else{
			$bInfo = $db->fetch_first("SELECT * FROM ".XWB_S_TBPRE."xwb_bind_info WHERE sina_uid='".$uInfo['id']."'");
		}

		if ( !empty($bInfo) && is_array($bInfo) ){
			$sinaHasBinded = true;
			//核查存储的access token是否有更新，有更新则进行自动更新
			if( $bInfo['token'] != $last_key['oauth_token'] || $bInfo['tsecret'] != $last_key['oauth_token_secret'] ){
				$db->query("UPDATE ". XWB_S_TBPRE. "xwb_bind_info SET token='". (string)$last_key['oauth_token']. "', tsecret='". (string)$last_key['oauth_token_secret']. "' WHERE sina_uid='".$uInfo['id']."'");
			}
		}

		//--------------------------------------------------------------------
		/// 决定在首页中显示什么浮层
		$tipsType = '';
		//xwb_tips_type
		//已在论坛登录

		if (defined('XWB_S_UID') &&  XWB_S_UID ){
			if ($sinaHasBinded){
				$tipsType = 'hasBinded';
				$sess->clearToken();
			}else{

				$inData = array();
				$inData['uid'] 		= XWB_S_UID;
				$inData['sina_uid'] = $uInfo['id'];
				$inData['token']	= $last_key['oauth_token'];
				$inData['tsecret']	= $last_key['oauth_token_secret'];
				$inData['profile']	= '[]';
				$sqlF = array();
				$sqlV = array();
				foreach ($inData as $k=>$v){
					$sqlF[] = "`".$k."`";
					$sqlV[] = "'".mysql_real_escape_string($v)."'";
				}
				$sql = "REPLACE INTO ".XWB_S_TBPRE."xwb_bind_info  (".implode(",",$sqlF).") VALUES (".implode(",",$sqlV).") ;";
				$rst = $db->query($sql, 'UNBUFFERED');

				jclass('misc')->update_account_bind_info(XWB_S_UID, '', '', 1);

				if(!$rst){echo "DB ERROR";exit;return false;}
				$tipsType = 'bind';

				//正向绑定统计上报
				$sess->appendStat('bind', array( 'uid' => $uInfo['id'], 'type' => 1 ));

			}

		}else{
			//从 wb 登录后 检查用户是否绑定，如果绑定了 则在附属站点自
			if ($sinaHasBinded){
				require_once XWB_P_ROOT. '/lib/xwbSite.inc.php';
				$result = xwb_setSiteUserLogin((int)$bInfo['uid']);
				if( !$result ){
					$db->query("DELETE FROM ". XWB_S_TBPRE. "xwb_bind_info WHERE sina_uid='".$uInfo['id']."'");
					$tipsType = 'siteuserNotExist';
				}else{
					$tipsType = 'autoLogin';
				}

			}else{
				//已登录WB，没有附属站点的帐号 引导注册
				$sess->setInfo('waiting_site_reg', '1');
				$tipsType = 'reg';
			}
		}
		//--------------------------------------------------------------------

		//bind的页面需要跳转，故需要使用cookies记录
		if( $tipsType == 'bind' ){
			setcookie('xwb_tips_type', $tipsType, 0);
		}
		//$sess->setInfo('xwb_tips_type', $tipsType);
		$sess->setInfo('waiting_site_bind',	0);

		//使用sina微博帐号登录成功（不管是否绑定）统计上报
		$sess->appendStat('login', array( 'uid' => $uInfo['id'] ));

		//所有跟站点相关的对接，必须放到_showBinging
		$this->_showBinging( $tipsType );

	}


	/**
	 * 根据在首页中显示的浮层显示对应的操作（内部函数，被authCallback最后调用）
	 * 所有跟站点相关的对接，必须放到_showBinging
	 * @param string $tipsType 类型
	 * @uses showmessage（dz函数）
	 */
	function _showBinging( $tipsType ){

		//用于启动浮层
		$GLOBALS['xwb_tips_type'] = $tipsType;

		//不完美解决方案
		if('UTF-8' != strtoupper($GLOBALS['_J']['config']['charset'])){
	    	@header("Content-type: text/html; charset=utf-8");
	    }

		if( 'autoLogin' == $tipsType ) {
			$synlogin_result = '';
			if (true===UCENTER) {
				//加载Ucenter客户端文件
				include_once(ROOT_PATH . './api/uc_client/client.php');

				$ucuid = (int) $GLOBALS['_J']['config']['login_user']['ucuid'];
				if ($ucuid > 0) {
					$synlogin_result = uc_user_synlogin($ucuid);
				}
			}

			//直接跳转到首页
			jsg_showmessage("登录成功{$synlogin_result}", $this->redirect_to, 5);

		}elseif( 'siteuserNotExist' == $tipsType ){
			jsg_showmessage( XWB_plugin::L('xwb_site_user_not_exist'));

		}elseif( 'reg' == $tipsType ){
			jsg_showmessage(XWB_plugin::L('xwb_process_binding', 'openReg4jsg' ));

		}elseif( 'hasBinded' == $tipsType ){
			jsg_showmessage(XWB_plugin::L('xwb_process_binding', 'hasBind' ));

		//直接跳转到bind页面
		}else{
			XWB_plugin::redirect(XWB_plugin::siteUrl(0).'index.php?mod=account&code=sina', 3);
		}

	}


	/// 获取 OAUTH 认证URL
	function _getOAuthUrl(){
		static $aurl = null;
		if (!empty($aurl)) {return $aurl; }

		$sess = XWB_plugin::getUser();
		$sess->clearToken();

		$wbApi = XWB_plugin::getWB();
		$keys = $wbApi->getRequestToken();

		if( !isset($keys['oauth_token']) || !isset($keys['oauth_token_secret']) ){
			$api_error_origin = isset($keys['error']) ? $keys['error'] : 'UNKNOWN ERROR. MAYBE SERVER CAN NOT CONNECT TO SINA API SERVER';
			$api_error = ( isset($keys['error_CN']) && !empty($keys['error_CN']) && 'null' != $keys['error_CN'] ) ? $keys['error_CN'] : '';

			XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$wbApi->req_error_count}\t{$api_error}\t{$wbApi->last_req_url}\tERROR ARRAY:\r\n".print_r($keys, 1));
			XWB_plugin::showError("服务器获取Request Token失败；请稍候再试。<br />错误原因：{$api_error}[{$api_error_origin}]");
		}

		//print_r($keys);
		$aurl = $wbApi->getAuthorizeURL($keys['oauth_token'] ,false , XWB_plugin::baseUrl(). XWB_plugin::URL('xwbAuth.authCallBack'));

		//echo  XWB_plugin::baseUrl().XWB_plugin::URL('xwbAuth.authCallBack')."\n";
		$sess->setOAuthKey($keys, false);
		return rtrim($aurl, '&');//."&from=xweibo&xtype=1";
	}
}
?>
