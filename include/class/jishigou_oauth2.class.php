<?php

/**
 *
 * 记事狗OAUTH2服务端操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: jishigou_oauth2.class.php 5267 2013-12-16 05:11:28Z wuliyong $
 */

if(!class_exists('OAuth2')) {
	jext('oauth2');
}

class jishigou_oauth2 extends OAuth2 {

	private $_uid = 0;

	
	public function __construct() {
		parent::__construct();

		$this->_init_conf();
	}

	
	function __destruct() {

	}

	private function _init_conf() {
				$confs = array(
			'access_token_lifetime' => mt_rand(2592000, 8640000),
			'auth_code_lifetime' => 180,
		);
		foreach($confs as $k=>$v) {
			$this->setVariable($k, $v);
		}
		$this->_init_uid();
	}

	private function _init_uid($uid = 0) {
		if($uid) {
			$uid = (is_numeric($uid) ? $uid : 0);
		}
		if($uid < 1) {
			$uid = $GLOBALS['_J']['uid'];
		}
		$uid = max(0, (int) $uid);
		$this->_uid = $uid;
		return $this->_uid;
	}

	
	public function addClient($client_id, $client_secret, $redirect_uri) {
			}

	public function getClient($client_id) {
		static $datas=null;
		if(!isset($datas[$client_id])) {
			$row = DB::fetch_first("SELECT *, `app_key` as `client_id`, `app_secret` as `client_secret` FROM ".DB::table('app')." WHERE `app_key`='$client_id'");
			if($row) {
				$datas[$client_id] = $row;
			}
		}
		return $datas[$client_id];
	}

	
	protected function checkClientCredentials($client_id, $client_secret = NULL) {
		$row = $this->getClient($client_id);
		if(null === $client_secret) {
			return (false != $row);
		}
		return ($client_secret == $row['client_secret']);
	}

	
	protected function getRedirectUri($client_id) {
		$row = $this->getClient($client_id);
		if(!$row) {
			return false;
		}
		return ((isset($row['redirect_uri']) && $row['redirect_uri']) ? $row['redirect_uri'] : null);
	}

	
	protected function getAccessToken($access_token) {
		$row = DB::fetch_first("SELECT * FROM ".DB::table('api_oauth2_token')." WHERE `access_token`='$access_token'");
		return ($row ? $row : null);
	}

	
	protected function setAccessToken($access_token, $client_id, $expires, $scope = NULL) {
		$this->_init_uid($this->_uid);
		DB::query("DELETE FROM ".DB::table('api_oauth2_token')." WHERE `expires`<'".TIMESTAMP."'");
		DB::query("DELETE FROM ".DB::table('api_oauth2_token')." WHERE `uid`='{$this->_uid}' AND `client_id`='$client_id'");
		DB::query("INSERT INTO ".DB::table('api_oauth2_token')." (`access_token`, `client_id`, `expires`, `scope`, `uid`) VALUES ('$access_token', '$client_id', '$expires', '$scope', '{$this->_uid}')");
		DB::query("DELETE FROM ".DB::table('api_oauth2_code')." WHERE `uid`='{$this->_uid}' AND `client_id`='$client_id'");
	}

	
	protected function getSupportedGrantTypes() {
		return array(
			OAUTH2_GRANT_TYPE_AUTH_CODE,
			OAUTH2_GRANT_TYPE_USER_CREDENTIALS,
		);
	}

	
	protected function getAuthCode($code) {
		$row = DB::fetch_first("SELECT * FROM ".DB::table('api_oauth2_code')." WHERE `code`='$code'");
		$this->_init_uid($row['uid']);
		return ($row ? $row : null);
	}

	
	protected function setAuthCode($code, $client_id, $redirect_uri, $expires, $scope = NULL) {
		$uid = $this->_init_uid();
		if($uid > 0) {
			DB::query("DELETE FROM ".DB::table('api_oauth2_code')." WHERE `expires`<'".TIMESTAMP."'");
			DB::query("INSERT INTO ".DB::table('api_oauth2_code')." (`code`, `client_id`, `redirect_uri`, `expires`, `scope`, `uid`) VALUES ('$code', '$client_id', '$redirect_uri', '$expires', '$scope', '$uid')");
		}
	}

	
	protected function checkUserCredentials($client_id, $username, $password) {
		$client_info = $this->getClient($client_id);
		if($client_info) {
			define('IN_JISHIGOU_LOGIN_TYPE', $client_info['app_name']);
		}
		$username = array_iconv('utf-8', $GLOBALS['_J']['charset'], $username, 1);
		$password = array_iconv('utf-8', $GLOBALS['_J']['charset'], $password, 1);
		$rets = jsg_member_login($username, $password);
		$uid = $rets['uid'];
		$this->_init_uid($uid);
		if($uid > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function get_oauth2_token($uid, $client_id) {
		return DB::fetch_first("SELECT * FROM ".DB::table('api_oauth2_token')." WHERE `uid`='$uid' AND `client_id`='$client_id'");
	}

}