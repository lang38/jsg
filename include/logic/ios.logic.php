<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename ios.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 2055297446 2514 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class IosLogic
{
		var $passphrase;
		var $pem;

	function IosLogic() {
		$this->passphrase = $GLOBALS['_J']['config']['iphone_passphrase'];
		$this->pem = $GLOBALS['_J']['config']['iphone_pem_file'];
	}

		function push_msg($uids,$msg=''){
		if($GLOBALS['_J']['config']['iphone_push_enable'] && $this->passphrase && $this->pem && $uids && $msg){
			$tokens = $this->get_token($uids);
		}
		if($tokens){
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', ROOT_PATH.$this->pem);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
			$fp = stream_socket_client('ssl:/' . '/gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
						if($fp){
				$body['aps'] = array(
					'alert' => array_iconv(strtolower($GLOBALS['_J']['charset']), 'utf-8', $msg),
					'sound' => 'default'
				);
				$payload = json_encode($body);
				foreach($tokens as $token){
					$pushmsg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
					fwrite($fp, $pushmsg, strlen($pushmsg));
					file_put_contents("ios_log.txt",array_iconv(strtolower($GLOBALS['_J']['charset']), 'utf-8', $msg).'('.date('Y-m-d H:i:s',TIMESTAMP).")\n",FILE_APPEND);
				}
				fclose($fp);
			}
		}
	}

		function get_token($uids){
		$token = array();
		$uids = is_array($uids) ? $uids : (is_numeric($uids) && $uids > 0 ? array($uids) : ('all'==$uids ? $uids : array()));
		if($uids){
			if('all'==$uids){				$query = DB::query("SELECT `token` FROM ".DB::table('ios'));
			}else{
				$query = DB::query("SELECT `token` FROM ".DB::table('ios')." where uid IN (".jimplode($uids).")");
			}
			while ($value = DB::fetch($query)){
				$token[] = $value['token'];
			}
		}
		return $token;
	}

		function loginout($uid){
		if($uid > 0){
			DB::query("DELETE FROM ".DB::table('ios')." WHERE `uid`='$uid'");
		}
	}
}