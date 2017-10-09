<?php

/**
 * clientUser ，用于oauth、绑定状态等相关的session管理
 * 本例不提供单例化方法，请自行保证单例性
 * 本类在使用前，必须自行开启session_start();
 * 
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @since 2010-06-08
 * @copyright [JishiGou] (C)2005 - 2099 Cenwor Inc.
 * @version $Id: clientUser.class.php 3699 2013-05-27 07:26:39Z wuliyong $
 */

class clientUser 
{
	var $uidField;
	var $dk = '';
	
	/**
	 * 构造方法
	 * @param $uidField
	 * @uses XWB_CLIENT_SESSION常量
	 */
	function clientUser($uidField='uid'){
		$this->uidField = $uidField;
		$this->dk = XWB_CLIENT_SESSION;
	}
	//-----------------------------------------------------------------------
	function setOAuthKey($keys,$is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		$this->setInfo(array("$k"=>$keys));
	}
	//-----------------------------------------------------------------------
	function getOAuthKey($is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		return $this->getInfo($k);
	}
	//-----------------------------------------------------------------------
	function getToken(){
		$key2 = $this->getOAuthKey(true);
		return empty($key2) ? $this->getOAuthKey(false) : $key2;
	}
	//-----------------------------------------------------------------------
	function clearToken(){
		$this->setOAuthKey(array(),true);
		$this->setOAuthKey(array(),false);
	}
	//-----------------------------------------------------------------------
	function clearInfo(){
		$_SESSION[$this->dk] = array();
	}
	
	function setInfo($k,$v=false){
		if( is_array($k) ){
			$_SESSION[$this->dk] = array_merge($_SESSION[$this->dk],$k);
		}else{
			$_SESSION[$this->dk][$k] = $v;
		}
	}
	//-----------------------------------------------------------------------
	function getInfo($key=false){
		if($key){
			return isset($_SESSION[$this->dk][$key]) ? $_SESSION[$this->dk][$key] : null;
		}else{
			return $_SESSION[$this->dk];
		}
	}
	//-----------------------------------------------------------------------
	function delInfo($k){
		if ( !isset($_SESSION[$this->dk]) || empty($_SESSION[$this->dk]) ){
			return true;
		}
		if(!is_array($k)) {$k = array($k);}
		foreach($k as $kv ){
			if (isset($_SESSION[$this->dk][$kv])) unset($_SESSION[$this->dk][$kv]);
		}
		return true;
	}
	//-----------------------------------------------------------------------
	function isLogin(){
		$r = $this->getInfo($this->uidField);
		return !empty($r);
	}
	//-----------------------------------------------------------------------
	
	/**
	 * 统计上报session数组内容添加
	 * @param string $type
	 * @param array $args
	 * @return bool
	 */
	function appendStat( $type, $args = array() ){
		$this->_checkStat();
		$args['xt'] = $type;
//		$_SESSION[$this->dk]['STAT'][] = $args;
		return true;
	}
	
	/**
	 * 统计上报session数组获取
	 * @return array
	 */
	function getStat(){
		return $this->_checkStat();
	}
	
	/**
	 * 统计上报session数组清除
	 * @return array
	 */
	function clearStat(){
		$this->setInfo( 'STAT', array() );
		return array();
	}
	
	/**
	 * 检查统计上报session数组的正确性，并返回统计上报数组session
	 * @return array
	 */
	function _checkStat(){
		$statInfo = $this->getInfo('STAT');
		if( empty( $statInfo ) || !is_array($statInfo) ){
			$statInfo = array();
			$this->setInfo( 'STAT', $statInfo );
		}
		
		return $statInfo;
		
	}
	
	
}
?>