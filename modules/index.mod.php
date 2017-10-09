<?php
/**
 *
 * 首页模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: index.mod.php 3789 2013-06-03 09:48:36Z yupengfei $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject(& $config) {
		$this->MasterObject($config);
		
		ob_start();
		if($this->Code && method_exists($this, $this->Code)) {
			$this->{$this->Code}();
		}   else {
						$this->only_login();
		}
		$this->ShowBody(ob_get_clean());
	}
	
    
	function only_login() {
		if(MEMBER_ID > 0) {
			$this->Messager(null, 'index.php?mod=topic');
		}
		
		include template('login/login_index_simplest');
	}
    
    
    function other_login(){
        
        
        if(MEMBER_ID > 0) {
			$this->show_user();
            return FALSE;
		}
		include template('login/login_index_other');exit();
    }
    
    function do_other_login(){
        
        
        $username = $this->Post['username'];
        $password = $this->Post['password'];
        $savelogin = $this->Post['savelogin'];
        if(!$username||!$password){
            $this->other_login();
            return false;
        }
        $member = jsg_member_login($username,$password);
        if($member['uid']<0){
            include template('login/login_index_other');
            return FALSE;
        }  else {
            if($savelogin){
                jsg_member_login_set_status($member);
            }
            $member = jsg_member_info($member['uid']);
            $this->show_user($member);
        }
		
		
    }
    
    function show_user($member =array()){
        
        if(MEMBER_ID < 0) {
			$this->other_login();
            return FALSE;
        }
        if(empty($member)){
            
            $uid = MEMBER_ID;
            $member = jsg_member_info($uid);
        }
        include template('login/login_index_other');exit();
        
    }
    
    
    function other_logout(){
        jsg_member_logout();
        include template('login/login_index_other');exit();
    }

}