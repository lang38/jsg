<?php
/**
 * 文件名：qqwb.mod.php
 * @version $Id: qqwb.mod.php 3773 2013-06-03 03:45:01Z yupengfei $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 腾讯微博接口模块
 */

/**
 * ModuleObject
 *
 * @package www.jishigou.com
 * @author 狐狸<foxis@qq.com>
 * @copyright 2010
 * @version $Id: qqwb.mod.php 3773 2013-06-03 03:45:01Z yupengfei $
 * @access public
 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{

    var $qqwb_config = array();

    var $QQWBApi = array();

    var $UserInfo = array();

    var $redirect_to = '';
    
    var $callback_url = '';


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->_init_qqwb();

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code)
        {
            case 'login':
                $this->Login();
                break;

            case 'login_check':
                $this->LoginCheck();
                break;

            case 'do_login':
                $this->DoLogin();
                break;

            case 'reg_check':
                $this->RegCheck();
                break;

            case 'do_reg':
                $this->DoReg();
                break;

            case 'unbind':
                $this->UnBind();
                break;

            case 'sync_weibo':
            	$this->sync_weibo();
            	break;

            case 'sync_reply':
            	$this->sync_reply();
            	break;

            case 'do_modify_bind_info':
                $this->DoModifyBindInfo();
                break;

			default:
				$this->AuthCallback();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);
	}

    function Login()
    {
    	if (jsg_getcookie("referer")=="") {
			jsg_setcookie("referer", referer('?', 1));
		}
		
		$aurl = $this->oauth->getAuthorizeURL($this->callback_url);

		$this->Messager(null, $aurl);
    }

    function AuthCallback()
    {
    	$openid = jget('openid', 'txt');
    	$openkey = jget('openkey', 'txt');
    	
    	if(!$this->Code) {
			$this->Messager('未定义的操作', null);
		}

		$token_info = $this->_get_token_info();
		if(!$token_info) {
			$this->Messager("返回内容为空，启用OAuth2.0接口，需要您的服务器支持OpenSSL，请检查……");
		}
    	if($token_info['errorCode']) {
    		$this->Messager("[{$token_info['errorCode']}]{$token_info['errorMsg']}", null);
    	}
    	if(!$token_info['access_token'] || !$token_info['openid']) {
    		$this->Messager('[请求错误]返回的access_token或openid为空', null);
    	}
    	if(strtolower($openid) != strtolower($token_info['openid'])) {
    		$this->Messager('openid不一致', null);
    	}
    	
    	    	$qqwb_username = $token_info['qqwb_username'] = $token_info['name'];
    	$token_info['code'] = $this->Code;
    	$token_info['openkey'] = $openkey;
    	$token_info['last_update'] = TIMESTAMP;
    	$token_info['expires_time'] = TIMESTAMP + $token_info['expires_in'];
    	$p = array('qqwb_username' => $qqwb_username);

        $qqwb_bind_info = jtable('qqwb_bind_info')->info($p);

        if($qqwb_bind_info)
        {
            jtable('qqwb_bind_info')->update($token_info, $p);

                        if(false != ($user_info = $this->_user_login($qqwb_bind_info['uid'])))
            {
                if(true === UCENTER && ($ucuid = (int) $user_info['ucuid']) > 0)
                {
                    include_once(ROOT_PATH . './api/uc_client/client.php');

                    $uc_syn_html = uc_user_synlogin($ucuid);

                    $this->Messager("登录成功{$uc_syn_html}", $this->redirect_to, 5);
                }

                $this->Messager(null, $this->redirect_to);
            }
            else
            {
                jtable('qqwb_bind_info')->delete($p);
                jtable('qqwb_bind_info')->delete(array('uid' => $qqwb_bind_info['uid']));

                $this->Messager("绑定的用户已经不存在了", $this->redirect_to);
            }
        }
        else
        {
        	$this->_bind($token_info);
        	
            if(MEMBER_ID > 0)
            {
                $this->Messager(null, 'index.php?mod=account&code=qqwb');
            }
            else
            {
            	$this->third_party_regstatus();

            	                $hash = authcode(md5($qqwb_username), 'ENCODE');

                $reg = array();
                $reg['nickname'] = $token_info['nick'];
                if($this->qqwb_config['is_sync_face']) {
                	$qqwb_user_info = $this->_get_user_info($token_info['access_token'], $token_info['openid']);
                    if($qqwb_user_info['head']) {
                    	$reg['face'] = $qqwb_user_info['head'] . '/180';
                    }
                }


                $this->Title = '腾讯微博帐号绑定';
                include(template('bind/bind_info_qqwb'));
            }
        }
    }

    function RegCheck()
    {
        exit($this->_reg_check());
    }
    function _reg_check()
    {
    	$this->third_party_regstatus();

        $regstatus = jsg_member_register_check_status();
		if($regstatus['error'])
		{
			Return $regstatus['error'];
		}
		if(true!==JISHIGOU_FORCED_REGISTER && $regstatus['invite_enable'])
		{
			if(!$regstatus['normal_enable'])
			{
				Return '本站目前需要有邀请链接才能注册。' . jsg_member_third_party_reg_msg();
			}
		}

        $in_ajax = get_param('in_ajax');
        if($in_ajax)
        {
            $this->Post = array_iconv('utf-8',$this->Config['charset'],$this->Post, 1);
        }

        $nickname = trim($this->Post['nickname']);
        $email = trim($this->Post['email']);

        $rets = array(
        	'0' => '[未知错误] 有可能是站点关闭了注册功能',
        	'-1' => '不合法',
        	'-2' => '不允许注册',
        	'-3' => '已经存在了',
        	'-4' => '不合法',
        	'-5' => '不允许注册',
        	'-6' => '已经存在了',
        );


        $ret = jsg_member_checkname($nickname, 1);
        if($ret < 1)
        {
        	return "帐户/昵称 " . $rets[$ret];
        }

        $ret = jsg_member_checkemail($email);
        if($ret < 1)
        {
        	return "Email " . $rets[$ret];
        }

        $password = trim($this->Post['password']);
        if(strlen($password) < 6) {
        	return "密码至少5位以上";
        }

        return '';
    }
    function DoReg()
    {
        $this->_hash_check();

    	if(false != ($check_result = $this->_reg_check())) {
            $this->Messager($check_result,null);
        }

        $nickname = trim($this->Post['nickname']);
        $password = trim($this->Post['password']);
        $email = trim($this->Post['email']);
        $face = trim($this->Post['face']);
        $synface = ($this->Post['synface'] ? 1 : 0);


        $uid = $ret = jsg_member_register($nickname, $password, $email);
        if($ret < 1) {
        	$this->Messager("注册失败{$ret}",null);
        }

        $this->_bind_uid($uid);

        $rets = jsg_member_login($uid, $password, 'uid');

		if($this->qqwb_config['is_sync_face'] && $synface && $face) {
						jsg_schedule(array('uid'=>$uid, 'face'=>$face), 'syn_qqwb_face', $uid);
		}


        if($this->qqwb_config['reg_pwd_display'])
        {
            $this->Messager("您的帐户 <strong>{$rets['nickname']}</strong> 已经注册成功，请牢记您的密码 <strong>{$password}</strong> {$rets['uc_syn_html']}", $this->redirect_to, 15);
        }
        else
        {
            $this->Messager("注册成功{$rets['uc_syn_html']}", $this->redirect_to, 10);
        }
    }

    function LoginCheck()
    {
        exit($this->_login_check());
    }
    function _login_check()
    {
        $in_ajax = get_param('in_ajax');
        if($in_ajax)
        {
            $this->Post = array_iconv('utf-8',$this->Config['charset'],$this->Post, 1);
        }

        $username = trim($this->Post['username']);
        $password = trim($this->Post['password']);


        $rets = jsg_member_login_check($username, $password);
		$ret = $rets['uid'];
        if($ret < 1)
        {
        	$rets = array(
        		'0' => '未知错误 ',
        		'-1' => '用户名或者密码错误',
        		'-2' => '用户名或者密码错误',
        		'-3' => '累计 ' . jconf::get('failedlogins', 'limit') . ' 次错误尝试，' . jconf::get('failedlogins', 'time') . ' 分钟内您将不能登录',
        	);

        	return $rets[$ret];
        }

        $this->UserInfo = DB::fetch_first("select * from ".TABLE_PREFIX."members where `username`='{$username}'");

        return '';
    }
    function DoLogin()
    {
        $this->_hash_check();

    	if(false != ($check_result = $this->_login_check())) {
            $this->Messager($check_result,null);
        }

        $timestamp = TIMESTAMP;
        $username = trim($this->Post['username']);
        $password = trim($this->Post['password']);


        $rets = jsg_member_login($username, $password);

        $this->_bind_uid($rets['uid']);

    	if($rets['uc_syn_html'])
        {
            $this->Messager("登录成功{$rets['uc_syn_html']}", $this->redirect_to, 5);
        }
        else
        {
        	$this->Messager(null, $this->redirect_to);
        }
    }

    function UnBind() {
        $uid = max(0, (int) MEMBER_ID);
        if($uid < 1) {
            $this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
        }

        jtable('qqwb_bind_info')->delete(array('uid' => $uid));

        $this->_update($uid);


        $this->Messager("已经成功解除绑定");
    }

    function DoModifyBindInfo() {
        $uid = max(0, (int) MEMBER_ID);
        if($uid < 1) {
            $this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
        }

        $synctoqq = (get_param('synctoqq') ? 1 : 0);
        $sync_weibo_to_jishigou = (jget('sync_weibo_to_jishigou') ? 1 : 0);
        $sync_reply_to_jishigou = (jget('sync_reply_to_jishigou') ? 1 : 0);

        $this->DatabaseHandler->Query("update ".TABLE_PREFIX."qqwb_bind_info set `synctoqq`='$synctoqq',
        	`sync_weibo_to_jishigou`='$sync_weibo_to_jishigou', `sync_reply_to_jishigou`='$sync_reply_to_jishigou' where `uid`='$uid'");

        $this->_update($uid);

        $this->Messager("设置成功");
    }

    function sync_weibo() {
    	$qqwb = jconf::get('qqwb');
        if(!$qqwb['is_synctopic_tojishigou']) {
            return ;
        }

        $info = array();
        $uid = jget('uid', 'int');
        if($uid < 1) {
            $uid = MEMBER_ID;
        }
        if($uid < 1) {
        	return ;
        }

        $info = qqwb_bind_info($uid);
        if(!$info) {
            return ;
        }

        if(!qqwb_bind($uid)) {
        	return ;
        }

        $uid = (int) $info['uid'];
        if($uid < 1) {
            return ;
        }

        $qqwb_username = $info['qqwb_username'];
        if(!$qqwb_username) {
            return ;
        }

        if(!(qqwb_syncweibo_tojishigou($uid))) {
            return ;
        }

        if($qqwb['syncweibo_tojishigou_time'] > 0 && ($info['last_read_time'] + $qqwb['syncweibo_tojishigou_time'] > TIMESTAMP)) {
            return ;
        }

        $member = DB::fetch_first("select * from ".TABLE_PREFIX."members where `uid`='{$uid}'");
        if(!$member) {
            return ;
        }

        

        $rs = qqwb_api('statuses/user_timeline', array(
			'openid' => $info['openid'],
			'access_token' => $info['access_token'],
			'pageflag' => 0,
			'pagetime' => 0,
			'reqnum' => 100,
			'lastid' => 0,
			'name' => $info['name'],
			'type' => 3,
			'contenttype' => 0,
		), $this->oauth);
		$datas = $rs['info'];


        if($datas) {
            krsort($datas);
            $TopicLogic = jlogic('topic');
            foreach($datas as $data) {
                $qqwb_id = ($data['idstr'] ? $data['idstr'] : ($data['qqwb_id'] ? $data['qqwb_id'] : $data['id']));

                if($qqwb_id && !(DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_topic where `qqwb_id`='{$qqwb_id}'")) &&
                	($content = trim(strip_tags(array_iconv('utf-8',$this->Config['charset'],$data['origtext'] . (isset($data['source']['origtext']) ?
                		" /"."/@{$data['source']['name']}: {$data['source']['origtext']}" : "")))))
                ) {
                	DB::query("insert into ".TABLE_PREFIX."qqwb_bind_topic (`qqwb_id`) values ('{$qqwb_id}')");

                    $_t = $data['timestamp'] ? $data['timestamp'] : TIMESTAMP;
                    $add_datas = array(
                        'content' => $content,
                        'from' => 'qqwb',
                        'type' => 'first',
                        'uid' => $uid,
                        'timestamp' => $_t,
                    );
                    $add_result = $TopicLogic->Add($add_datas);

                    if(is_array($add_result) && count($add_result)) {
                        $tid = max(0, (int) $add_result['tid']);
                        if($tid > 0) {
                        	DB::query("replace into ".DB::table('qqwb_bind_topic')." (`tid`, `qqwb_id`) values ('$tid', '$qqwb_id')");

                            if($qqwb['is_syncimage_tojishigou'] && $data['image']) {
                                $TopicLogic->_parse_url_image($add_result, $this->_img($data['image']));
                            }
                            if($qqwb['is_syncimage_tojishigou'] && $data['source']['image']) {
                                $TopicLogic->_parse_url_image($add_result, $this->_img($data['source']['image']));
                            }
                        }
                    }
                }
            }
        }

        DB::query("update ".TABLE_PREFIX."qqwb_bind_info set `last_read_time`='".TIMESTAMP."',`last_read_id`='{$qqwb_id}' where `qqwb_username`='{$qqwb_username}'");

        $this->_update($uid);

        exit;
    }

   	function sync_reply() {
   		$qqwb = jconf::get('qqwb');
        if(!$qqwb['is_syncreply_tojishigou']) {
            return ;
        }

        $tid = jget('tid', 'int');
        if($tid < 1) {
            return ;
        }

        $info = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_topic where `tid`='$tid'");
        if(!$info || !$info['qqwb_id']) {
            return ;
        }

        if($qqwb['syncweibo_tojishigou_time'] > 0 && ($info['last_read_time'] + $qqwb['syncweibo_tojishigou_time'] > TIMESTAMP)) {
            return ;
        }

        if(!($topic_info = DB::fetch_first("select * from ".TABLE_PREFIX."topic where `tid`='$tid'"))) {
            return ;
        }
        $uid = (int) $topic_info['uid'];
        if($uid < 1) {
        	return ;
        }

        $qqwb_bind_info = qqwb_bind_info($uid);
        if(!$qqwb_bind_info) {
        	return ;
        }

        if(!qqwb_bind($uid)) {
        	return ;
        }

        if(!(qqwb_syncreply_tojishigou($uid))) {
            return ;
        }

        

        $rs = qqwb_api('t/re_list', array(
			'openid' => $qqwb_bind_info['openid'],
			'access_token' => $qqwb_bind_info['access_token'],
        	'flag' => 2,
        	'rootid' => $info['qqwb_id'],
			'pageflag' => 0,
			'pagetime' => 0,
			'reqnum' => 100,
			'twitterid' => 0,
		), $this->oauth);
		$datas = $rs['info'];

        if($datas) {
            krsort($datas);
            $TopicLogic = jlogic('topic');
            foreach($datas as $data) {
                $qqwb_id = ($data['idstr'] ? $data['idstr'] : ($data['qqwb_id'] ? $data['qqwb_id'] : $data['id']));

                $qqwb_username = $data['name'];

                $_type = (2 == $data['type'] ? 'forward' : 'reply');

                if($qqwb_id && ($bind_info = DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_info where `qqwb_username`='$qqwb_username'")) &&
                	!(DB::fetch_first("select * from ".TABLE_PREFIX."qqwb_bind_topic where `qqwb_id`='{$qqwb_id}'")) &&
                	($content = trim(strip_tags(array_iconv('utf-8',$this->Config['charset'],$data['origtext'] .
                		(2==$data['type'] && isset($data['source']['origtext']) ?
                		" /"."/@{$data['source']['name']}: {$data['source']['origtext']}" : "")))))
                ) {
                	DB::query("insert into ".TABLE_PREFIX."qqwb_bind_topic (`qqwb_id`) values ('{$qqwb_id}')");

                    $_t = $data['timestamp'] ? $data['timestamp'] : TIMESTAMP;
                    $add_datas = array(
                    	'totid' => $tid,
                        'content' => $content,
                        'from' => 'qqwb',
                        'type' => $_type,
                        'uid' => $bind_info['uid'],
                        'timestamp' => $_t,
                    );
                    $add_result = $TopicLogic->Add($add_datas);

                    if(is_array($add_result) && count($add_result)) {
                        $_tid = max(0, (int) $add_result['tid']);
                        if($_tid > 0) {
                        	DB::query("replace into ".DB::table('qqwb_bind_topic')." (`tid`, `qqwb_id`) values ('$_tid', '$qqwb_id')");
                            if($qqwb['is_syncimage_tojishigou'] && $data['image']) {
                                $TopicLogic->_parse_url_image($add_result, $this->_img($data['image']));
                            }
                        }
                    }
                }
            }
        }

        DB::query("update `".TABLE_PREFIX."qqwb_bind_topic` set `last_read_time`='".TIMESTAMP."' where `tid`='{$tid}'");

        exit;
   	}

    function _init_qqwb()
    {
        if ($this->Config['qqwb_enable'] && qqwb_init($this->Config)) {
            $this->qqwb_config = jconf::get('qqwb');
            
            $this->callback_url = $this->Config['site_url'] . "/index.php?mod=qqwb&code=auth_callback";

            $rto = jsg_getcookie('referer');
            $rto = ($rto ? $rto : $this->Config['site_url'] . '/index.php');
            $this->redirect_to = $rto;

	        if(MEMBER_ID > 0 && 'POST' == $_SERVER['REQUEST_METHOD']) {
	    		$this->_update();
	    	}
	    	$this->oauth = qqwb_oauth();
		} else {
			$this->Messager("整合腾讯微博的功能未启用，请联系管理员开启", null);
		}
    }

    function _user_login($uid)
    {
    	return jsg_member_login_set_status($uid);
    }

    function third_party_regstatus() {
    	if($this->Config['third_party_regstatus'] && in_array('qqwb', $this->Config['third_party_regstatus'])) {
    		define('JISHIGOU_FORCED_REGISTER', true);
    	}
    }

    function _syn_face($uid, $face='') {
    	return qqwb_sync_face($uid, $face);
    }


    
    function _hash_check() {
    	$hash = '';
    	if($this->Post['hash']) {
    		$hash = authcode($this->Post['hash'], 'DECODE');
    	}

    	$md5 = md5($this->Post['qqwb_username']);

    	if($hash != $md5) {
    		$this->Messager("非法请求", null);
    	}
    	
    	if(false == ($info = jtable('qqwb_bind_info')->info(array('qqwb_username' => $this->Post['qqwb_username'])))) {
    		$this->Messager("用户 {$this->Post['qqwb_username']} 已经不存在了", null);
    	}
    	
    	if($info['uid'] > 0) {
    		$this->Messager("用户 {$this->Post['qqwb_username']} 已经绑定过了", null);
    	}
    }

    function _bind($p = array(), $uid = MEMBER_ID) {
    	if(empty($p['name'])) {
    		return 0;
    	}
    	
    	$uid = (is_numeric($uid) ? $uid : 0);
    	
    			settype($p, 'array');
    	$p['uid'] = $uid;
    	$p['qqwb_username'] = $p['name'];
    	$p['last_update'] = $p['dateline'] = TIMESTAMP;    	
    	
    	jtable('qqwb_bind_info')->delete(array('qqwb_username' => $p['qqwb_username']));
    	if($uid > 0) {
    		jtable('qqwb_bind_info')->delete(array('uid' => $p['uid']));
    	}
    	$ret = jtable('qqwb_bind_info')->replace($p);

    	if($uid > 0) {
    		$this->_update($uid);
    	}

    	return $ret;
    }
    
    function _bind_uid($uid) { 
    	$uid = (is_numeric($uid) ? $uid : 0);   	
    	
    	$ret = jtable('qqwb_bind_info')->update(array('uid' => $uid), array('qqwb_username' => $this->Post['qqwb_username']));
    	
    	if($uid > 0) {
    		$this->_update($uid);
    	}

    	return $ret;
    }

    function _update($uid=0) {
    	$uid = ($uid ? $uid : MEMBER_ID);

    	jclass('misc')->update_account_bind_info($uid, '', '', 1);
    }

        function _img($is) {
    	if(!empty($is)) {
	    	if(is_array($is)) {
	    		foreach($is as $k=>$i) {
	    			$is[$k] = $i . '/2000';
	    		}
	    	} else {
	    		$is .= '/2000';
	    	}
    	}
    	return $is;
    }

	function _get_token_info() {
    	return array_iconv('utf-8', $this->Config['charset'], $this->oauth->getAccessToken($this->Code, $this->callback_url));
    }

    function _get_user_info($access_token=null, $openid = null) {
    	if($access_token) {
    		$p['access_token'] = $access_token;
    	}
    	if($openid) {
    		$p['openid'] = $openid;
    	}

    	return qqwb_api('user/info', $p, 'GET', $this->oauth);
    }

}
