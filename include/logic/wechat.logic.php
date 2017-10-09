<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename wechat.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-09-17 16:01:50 100392794 1845425033 3738 $
 */







if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class wechatLogic {

        var $wechatConfig;

    public function wechatLogic() {

        $this->wechatConfig = jconf::get("wechat");
    }

    
    public function add($postData) {

    	$member = $this->is_jsg($postData['fromusername']);
        if ($member) {
        	
	        $cache_id = 'wechat/' . $member['jsg_id'] . '_' . $postData['fromusername'] . '_' . substr(md5($postData['content']), -12);
	        if(false !== cache_db('mget', $cache_id)) {
	             return ;
	        } 
	        cache_db('mset', $cache_id, time(), 30);
	        
        	$imageID = 0;
            if ($postData['msgtype'] == "image") {
                $p = array(
                    'pic_url' => $postData['picurl'],
                    'uid' => $member['jsg_id'],
                );

                $rets = jlogic('image')->upload($p);

                if ($rets['code'] < 0 && $rets['error']) {
                	jlog('wechat', $postData, 0);
                	jlog('wechat', $rets, 0);
                }
                $imageID = $rets['id'];
            }

            $r = jlogic("topic")->Add($postData['content'], 0, $imageID, 0, "wechat", "first", $member['jsg_id']);

            if(is_array($r) && $r['tid'] > 0) {
            	;
            } else {
            	jlog('wechat', $postData, 0);
            }

            if ($r) {
				if(is_array($r)){
					return $this->wechatConfig['add_weibo_success'];
				}else{
					return $r;
				}
            } else {
                return $this->wechatConfig['add_weibo_false'];
            }
        }
    }

    
    public function diy_reply($content) {
        if (in_array($content, $this->wechatConfig['diy_reply']['diy_key'])) {
            $temp_key = array_search($content, $this->wechatConfig['diy_reply']['diy_key']);
            if ($this->wechatConfig['diy_reply']['diy_key'][$temp_key]) {
                return $this->wechatConfig['diy_reply']['diy_reply'][$temp_key];
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    
    public function is_jsg($openid) {
        if (empty($openid)) {
            return FALSE;
        }
        $r = jtable('wechat')->info(array('wechat_id' => $openid));
        if ($r) {
            return $r;
        } else {
            return FALSE;
        }
    }

    
    public function do_bind($openid, $jsg_id = MEMBER_ID) {
        if (!$jsg_id) {
            return FALSE;
        }
                jtable('wechat')->delete(array('wechat_id' => $openid));
        jtable('wechat')->delete(array('jsg_id' => $jsg_id));
                $r = jtable('wechat')->insert(array('wechat_id' => $openid, 'jsg_id' => $jsg_id, 'dateline' => TIMESTAMP), 1);
        return $r;
    }

	
	public function jsg_get_wechat_openid($openid){
	    $openid = trim($openid);
	    if($openid){
	        if (empty($openid)) {
	            return FALSE;
	        }

	        $r = DB::query("select * from `" . TABLE_PREFIX . "wechat` where wechat_id = '" . $openid . "'");
	        $r = DB::fetch($r);
	        if ($r) {
	            $member = jsg_member_info($r['jsg_id']);
	            return array($member['uid'], $member['password']);
	        } else {
	            return FALSE;
	        }
	    }  else {
	        return FALSE;
	    }
	}

}

?>
