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
 * @Date 2014 69208010 9403 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    
    var $WLogic;

    
    var $wechatConfig;

    
    var $request;

    public function ModuleObject($config) {

        $wechat_conf = jconf::get("wechat");
        if (!$wechat_conf || (!$wechat_conf['on']) || !$wechat_conf['token']) {
        	$msg = "后台未开启或微信Token值没有设定……";
        	jlog('wechat', $msg, 0);
            exit($msg);
        }
        $this->wechatConfig = $wechat_conf;
        define("TOKEN", $wechat_conf['token']);

        $this->MasterObject($config);
                $this->WLogic = jlogic("wechat");

        $this->main();
    }

    
    public function main() {
        if ($this->check_signature()) {
                        if (jget('echostr')) {
                echo jget('echostr');
                exit;
            } else {
                $this->get_input();
                $this->execute();
                exit;
            }
        } else {
            jlog('wechat', 'check_signature is invalid');
        }
        exit;
    }

    
    public function on_subscribe() {

        $contentStr = $this->wechatConfig['subscribe'];
        $this->reply($contentStr);
    }

    
    public function do_help() {
        $return = $this->wechatConfig['help'];
        $this->reply($return);
    }

    
    public function do_bind() {
        $contentStr = '<a href="' . $this->Config['site_url'] . '/mobile/index.php?mod=wechat&openid=' . $this->get_argument('FromUserName') . '">您可以点此进行帐号绑定！</a>';
        $this->reply($contentStr);
    }

    
    public function do_3g() {
        $return = '<a href="' . $this->Config['site_url'] . '/mobile/index.php?openid=' . $this->get_argument('FromUserName') . '">您可以点此访问3G页面！</a>';
        $this->reply($return);
    }

    
    public function do_wap() {
        $return = '<a href="' . $this->Config['site_url'] . '/wap/index.php?openid=' . $this->get_argument('FromUserName') . '">您可以点此访问Wap页面！</a>';
        $this->reply($return);
    }

    
    public function do_diy() {
        $return = $this->WLogic->diy_reply($this->get_argument('content'));
        $this->reply($return);
    }

    
    public function do_add() {
        if ($this->wechat2weibo()) {
            if ($this->get_argument('MsgType') == 'image') {
                $post_data['content'] = '分享图片';
            } elseif ($this->wechatConfig['add_weibo']) {
                $post_data['content'] = preg_replace('/^' . preg_quote($this->wechatConfig['add_weibo'], '/') . '/i', "", $this->get_argument('content'), 1);
            } else {
				$post_data['content'] = $this->get_argument('content');
			}
            if($post_data['content']) {
            	$this->request['content'] = $post_data['content'];
            	$contentStr = $this->WLogic->add($this->request);
            } else {
            	$this->do_help();
            	exit;
            }            
        } else {
            $contentStr = '你需要先<a href="' . $this->Config['site_url'] . '/mobile/index.php?mod=wechat&openid=' . $this->get_argument('FromUserName') . '">绑定</a>帐号';
        }
        $this->reply($contentStr);
    }

    
    private function get_input() {
                $postStr = file_get_contents('php:/' . '/input');
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($postObj) {
	            $postData = (array) $postObj;
	            	            $postData = array_iconv('utf-8', $this->Config['charset'], $postData, 1);
	            foreach($postData as $k=>$v) {
	            	$v = trim($v);
	            	$postData[$k] = $postData[strtolower($k)] = $v;
	            }
	            if('debug' == $postData['content']) {
	            	jlog('wechat', $postData, 0);
	            }
	            $this->request = $postData;
            } else {
            	jlog('wechat', $postStr, 0);
            }
        } else {
            jlog('wechat', 'php input is empty');
        }
    }

    
    private function execute() {
        $msg_type = $this->get_argument('MsgType');
        switch ($msg_type) {
            case 'location':                break;
            case 'event':                switch ($this->get_argument('Event')) {
                    case 'subscribe':
                        $this->on_subscribe();
                        break;
                    default:
                        break;
                }
                break;
            case 'image':
                $this->do_add();
                break;
            default:                $this->parsing();
                break;
        }
    }

    
    private function parsing() {
        $content = $this->get_argument('Content');
                if ($content) {
                        if (in_array($content, $this->wechatConfig['help_key'])) {
                                $this->do_help();
            } elseif (in_array($content, $this->wechatConfig['bind_key'])) {
                                $this->do_bind();
            } elseif (in_array($content, $this->wechatConfig['3g_key'])) {
                                $this->do_3g();
            } elseif (in_array($content, $this->wechatConfig['wap_key'])) {
                                $this->do_wap();
            } elseif (in_array($content, $this->wechatConfig['diy_reply']['diy_key'])) {
                                $this->do_diy();
            } elseif (!$this->wechatConfig['add_weibo'] || (0 === strpos($content, $this->wechatConfig['add_weibo']))) {
                                $this->do_add();
            } else {
                                if ($this->wechatConfig['reply']) {
                    $this->reply($this->wechatConfig['reply']);
                }
            }
        } else {
                        if ($this->wechatConfig['reply']) {
                $this->reply($this->wechatConfig['reply']);
            }
        }
    }

    private function reply($contentStr) {
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";

        $msgType = "text";
        if (!$this->wechat2weibo() && false === strpos($contentStr, '/mobile/index.php?mod=wechat&openid=')) {
            $contentStr .= '<a href="' . $this->Config['site_url'] . '/mobile/index.php?mod=wechat&openid=' . $this->get_argument('FromUserName') . '">绑定帐号</a>后，更多互动哦！';
        }
        $resultStr = sprintf($textTpl, $this->get_argument('FromUserName'), $this->get_argument('ToUserName'), TIMESTAMP, $msgType, $contentStr);
        $resultStr = array_iconv($this->Config['charset'], 'utf-8', $resultStr);
        echo $resultStr;
        exit;
    }

    
    public function get_argument($key) {
        $key = strtolower($key);
        if (isset($this->request[$key])) {
            return $this->request[$key];
        } else {
            return NULL;
        }
    }

    
    private function check_signature() {
        $signature = $this->Get["signature"];
        $timestamp = $this->Get["timestamp"];
                $nonce = trim($this->Get["nonce"], "Vary:");

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
        	jlog('wechat', "$tmpStr $signature", 0);
            return false;
        }
    }

    
    protected function wechat2weibo() {
        return $this->WLogic->is_jsg($this->get_argument('FromUserName'));
    }

}

?>
