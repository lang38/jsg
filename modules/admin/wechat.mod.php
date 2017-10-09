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
 * @Date 2013-11-11 379921973 5613 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    var $WLogic;

    public function ModuleObject($config) {
        $this->MasterObject($config);
                $this->WLogic = jlogic("wechat");
        $code = $this->Code;

        ob_start();
        if ($code && method_exists($this, $code)) {
            $this->$code();
        } else {
            $this->setting();
        }
		$this->ShowBody(ob_get_clean());
    }

    
    public function setting() {
        $wechat = jconf::get("wechat");
        $wechat['help_key'] = implode('|', $wechat['help_key']);
        $wechat['bind_key'] = implode('|', $wechat['bind_key']);
        $wechat['3g_key'] = implode('|', $wechat['3g_key']);
        $wechat['wap_key'] = implode('|', $wechat['wap_key']);
        include template("admin/wechat_setting");
    }

    
    public function do_setting() {
        $wechat = $this->Post['wechat'];
        $diy_key = $this->Post['diy_key'];
        $diy_reply = $this->Post['diy_reply'];
        $delete = $this->Post['delete'];

        $wechat_conf = jconf::get("wechat");

                if ($_FILES['qrcode']['name']) {

            
            $image_name = "my_wechat_qrcode.jpg";
            $image_path = RELATIVE_ROOT_PATH . 'images/';
            $image_file = $image_path . $image_name;

            jupload()->init($image_path, 'qrcode', true);
            jupload()->setMaxSize(512);
            jupload()->setNewName($image_name);
            $result = jupload()->doUpload();

            if ($result) {
                $result = is_image($image_file);
            }
            if (!$result) {
                unlink($image_file);
            }
            image_thumb($image_file, $image_file, 150, 150);
            if ($this->Config['ftp_on']) {
                $ftp_key = randgetftp();
                $get_ftps = jconf::get('ftp');
                $site_url = $get_ftps[$ftp_key]['attachurl'];
                $ftp_result = ftpcmd('upload', $image_file, '', $ftp_key);
                if ($ftp_result > 0) {
                    jio()->DeleteFile($image_file);
                    $image_file = $site_url . '/' . str_replace('./', '', $image_file);
                }
            }
            $wechat['my_qrcode'] = $image_file;
        } else {
            $wechat['my_qrcode'] = $wechat_conf['my_qrcode'];
        }

                $wechat['help_key'] = explode('|', $wechat['help_key']);
        $wechat['bind_key'] = explode('|', $wechat['bind_key']);
        $wechat['3g_key'] = explode('|', $wechat['3g_key']);
        $wechat['wap_key'] = explode('|', $wechat['wap_key']);
        if ($diy_key && $diy_reply) {
            $wechat['diy_reply']['diy_key'][] = $diy_key;
            $temp_key = array_search($diy_key, $wechat['diy_reply']['diy_key']);
            $wechat['diy_reply']['diy_reply'][$temp_key] = $diy_reply;
        }
                if (is_array($delete)) {
            foreach ($delete as $diy_key_del) {
                if ($wechat['diy_reply']['diy_key'][$diy_key_del]) {
                    unset($wechat['diy_reply']['diy_key'][$diy_key_del]);
                }
                if ($wechat['diy_reply']['diy_reply'][$diy_key_del]) {
                    unset($wechat['diy_reply']['diy_reply'][$diy_key_del]);
                }
            }
        }
        if ($wechat['on'] && !$wechat['token']) {
            $this->Messager("开启微信必须设置Token");
        } else {
            $r = jconf::update("wechat_enable", $wechat['on']);
                        $r = jconf::update("wechat_qrcode", $wechat['my_qrcode']);
            $r = jconf::update("my_wechat", $wechat['mywechat']);
            $r = jconf::set("wechat", $wechat);
            $this->Messager("设置成功！");
        }
    }
    
    public function do_list() {    	
    	$p = array(
		    		'page_num' => 50,
		    		'sql_order' => ' `id` ASC ', 
		    	);
    	
    	$s_nickname = jget('s_nickname', 'txt');
    	if($s_nickname) {
    		$info = jsg_member_info($s_nickname, 'nickname');
    		if($info) {
    			$p['jsg_id'] = $info['uid'];
    		} else {
    			$this->Messager("您搜索的用户 $s_nickname 不存在");
    		}
    	}
    	
    	$rets = jtable('wechat')->get($p);
    	
    	$uids = array();
    	if($rets) {
	    	foreach($rets['list'] as $key=>$row) {
	    		$uids[$row['jsg_id']] = $row['uid'] = $row['jsg_id'];
	    		if($row['dateline']) {
	    			$row['wechat_bind_time'] = my_date_format($row['dateline'], 'Y-m-d H:i:s');
	    		}	
	    		$rets['list'][$key] = $row;
	    	}
    	}
    	$members = array();
    	if($uids) {
    		$members = jlogic('member')->get($uids);
    	}
    	
    	include template('admin/wechat_do_list');
    }
    
    public function unbind() {
    	$id = jget('id', 'int');
    	$jsg_id = jget('jsg_id', 'txt');
    	$wechat_id = jget('wechat_id', 'txt');
    	
    	if($id > 0) {
    		jtable('wechat')->delete(array('id' => $id));
    	}
    	if($jsg_id) {
    		jtable('wechat')->delete(array('jsg_id' => $jsg_id));
    	}
    	if($wechat_id) {
    		jtable('wechat')->delete(array('wechat_id' => $wechat_id));
    	}
    	
    	$this->Messager('解绑成功！');
    }

}

?>
