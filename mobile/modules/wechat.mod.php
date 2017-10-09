<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename wechat.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1880963766 2137 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    var $WLogic;

    public function ModuleObject($config) {

        $wechat_conf = jconf::get("wechat");
        if (!$wechat_conf || (!$wechat_conf['on']) || !$wechat_conf['token']) {
            echo "未开启……";
            exit;
        }
                define("TOKEN", $wechat_conf['token']);

        $this->MasterObject($config);
                $this->WLogic = jlogic("wechat");

        $code = $this->Code;
        if ($code && method_exists($this, $code)) {
            $this->$code();
        } else {
            $this->main();
        }
    }

    
    public function main() {
        $openid = $this->Get["openid"];
        include(template('wechat_login'));
    }

    public function login() {
        $username = jget('username', 'txt');
        $password = jget('password');
        $openid = jget('openid');

        if ($username == "" || $password == "") {
            json_error("无法登录,用户名或密码不能为空");
        }


        
        if ($this->Config['login_by_uid']) {
            is_numeric($username) && json_error("禁止使用UID登录");
        }

        if ($GLOBALS['_J']['plugins']['func']['login']) {
            hookscript('login', 'funcs', array('param' => $this->Post, 'step' => 'check'), 'login');
        }

        $rets = jsg_member_login($username, $password);
        $uid = (int) $rets['uid'];
        if ($uid < 1) {
            json_error(array_iconv($this->Config['charset'], 'utf-8', $rets['error']));
        }
        

        $r = false;
        if ($openid && $uid) {
            $r = jlogic('wechat')->do_bind($openid, $uid);
        }
        if ($r) {
            json_result("绑定成功！");
        } else {
            json_error("绑定失败！");
        }
    }

}

?>
