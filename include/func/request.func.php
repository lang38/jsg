<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename request.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1381031302 7086 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：request 相关
 * @version $Id: request.func.php 5589 2014-02-26 05:51:49Z wuliyong $
 */


function __jhttp($url, $post = array(), $args = array()) {
	$http = jext('http_client', 'Http_Client');
	if($post) {
		$args['post_field'] = $post;
	}
	$ops = array('head' => 'setHeader', 'cookie' => 'setCookie', 'post_field' => 'addPostField', 'post_file' => 'addPostFile', );
	foreach($ops as $opt => $opv) {
		if($args[$opt] && is_array($args[$opt])) {
			foreach($args[$opt] as $k => $v) {
				$http->$opv($k, $v);
			}
		}
	}
	$redir = (isset($args['redir']) ? (bool) $args['redir'] : true);
	if($args['post_field'] || $args['post_file']) {
		return $http->Post($url, $redir);
	} else {
		return $http->Get($url, $redir);
	}
}


function __request($action, $post=array(), &$error) {
	settype($post,"array");
	$post['system_env'] = ($post['system_env'] ? array_merge((array) $post['system_env'],(array) get_system_env()) : (array) get_system_env());
		$aclData = upsCtrl()->Account();
	$post['__acl__']['account'] = $aclData['account'];
	$post['__acl__']['token'] = $aclData['token'];
		$data='_POST='.urlencode(base64_encode(serialize($post)));
	$config = jconf::get();
	$charset = strtolower(str_replace('-', '', $config['charset']));
	$version = urlencode(SYS_VERSION);
	$pid = 2;
	#if NEDU
	if (defined('NEDU_MOYO'))
	{
		$pid = 3;
	}
	#endif
	$server_url = base64_decode('aHR0cDovL3VwZGF0ZS5jZW53b3IuY29tL3NlcnZlci5yZXF1ZXN0LnBocA==')."?do=$action&pid=$pid&charset=$charset&iver=$version";
	$response=dfopen($server_url,5000000,$data);
	$error_msg=array(1=>"error_nodata",2=>"error_format",);
	if($response == "") {
		$result = $error_msg[($error = 1)];
	}else{
		$int = preg_match("/<DATA>(.*)<\/DATA>/s", $response, $m);
		if($int < 1){
			$result = $error_msg[($error = 2)];
		}else{
						if(false!==strpos($m[1],"\n")) {
				$m[1] = preg_replace('~\s+\w{1,10}\s+~','',$m[1]);
			}
			$response = unserialize(base64_decode($m[1]));
			$result = $response['data'];
			if($response['type']) {
				$error = 3;
			}
		}
	}

	return $result;
}

function __dfopen($url, $limit = 10485760 , $post = '', $cookie = '', $bysocket = false,$timeout=5,$agent="") {
	if(ini_get('allow_url_fopen') && !$bysocket && !$post) {
		$fp = @fopen($url, 'r');
		$s = $t = '';
		if($fp) {
			while ($t=@fread($fp,2048)) {
				$s.=$t;
			}
			fclose($fp);
		}
		if($s) {
			return $s;
		}
	}

	$return = '';
	$agent=$agent?$agent:"Mozilla/5.0 (compatible; Googlebot/2.1; +http:/"."/www.google.com/bot.html)";
	$matches = parse_url($url);
	$host = $matches['host'];
	$script = $matches['path'].($matches['query'] ? '?'.$matches['query'] : '').($matches['fragment'] ? '#'.$matches['fragment'] : '');
	$script = $script ? $script : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $script HTTP/1.1\r\n";
		$out .= "Accept: */"."*\r\n";
		$out .= "Referer: $url\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: $agent\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $script HTTP/1.1\r\n";
		$out .= "Accept: */"."*\r\n";
		$out .= "Referer: $url\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Accept-Encoding: none\r\n";
		$out .= "User-Agent: $agent\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}

	$fp = jfsockopen($host, $port, $errno, $errstr, $timeout);

	if(!$fp) {
		return false;
	} else {
		fwrite($fp, $out);
		$return = '';
		while(!feof($fp) && $limit > -1) {
			$limit -= 8192;
			$return .= @fread($fp, 8192);
			if(!isset($status)) {
				preg_match("|^HTTP/[^\s]*\s(.*?)\s|",$return, $status);
				$status=$status[1];
				if($status!=200) {
					return false;
				}
			}
		}
		fclose($fp);
				preg_match("/^Location: ([^\r\n]+)/m",$return,$match);
		if(!empty($match[1]) && $location=$match[1]) {
			if(strpos($location,":/"."/")===false) {
				$location=dirname($url).'/'.$location;
			}
			$args=func_get_args();
			$args[0]=$location;
			return call_user_func_array("dfopen",$args);
		}
		if(false!==($strpos = strpos($return, "\r\n\r\n"))) {
			$return = substr($return,$strpos);
			$return = preg_replace("~^\r\n\r\n(?:[\w\d]{1,8}\r\n)?~","",$return);
			if("\r\n\r\n"==substr($return,-4)) {
				$return = preg_replace("~(?:\r\n[\w\d]{1,8})?\r\n\r\n$~","",$return);
			}
		}

		return $return;
	}
}

function get_system_env() {
	$e = array();
	$e['time'] = gmdate( "Y-m-d", time( ) );
	$e['os'] = PHP_OS;
	$e['ip'] = gethostbyname($_SERVER['SERVER_NAME']) or ($e['ip'] = getenv( "SERVER_ADDR" )) or ($e['ip'] = getenv('LOCAL_ADDR'));
	$e['sapi'] = php_sapi_name( );
	$e['host'] = strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']);
	$e['path'] = substr(dirname(__FILE__),0,-17);
	$e['cpu'] = $_ENV['PROCESSOR_IDENTIFIER']."/".$_ENV['PROCESSOR_REVISION'];
	$e['name'] = $_ENV['COMPUTERNAME'];
	if(defined('SYS_VERSION')) $e['sys_version']=SYS_VERSION;
	if(defined('SYS_BUILD')) $e['sys_build']=SYS_BUILD;
	$sys_conf = jconf::get();
	if($sys_conf['site_name']) $e['sys_name'] = $sys_conf['site_name'];
	if($sys_conf['site_admin_email']) $e['sys_email'] = $sys_conf['site_admin_email'];
	if($sys_conf['site_url']) $e['sys_url'] = $sys_conf['site_url'];
	if($sys_conf['charset']) $e['sys_charset'] = $sys_conf['charset'];

	return get_system_count($e);
}
function get_system_count($data) {
	$cache_id = 'misc/system_count';
	if(false === ($cdata = cache_file('get', $cache_id))) {
		$ctbs = array('api_oauth2_token', 'app', 'buddys', 'cache', 'event', 'event_member', 'failedlogins', 'force_out', 'group', 'invite', 'live', 'log', 'medal', 'media', 'members', 'notice', 'output', 'plugin', 'pms', 'qqwb_bind_info', 'qun', 'qun_category', 'qun_user', 'report', 'robot', 'sessions', 'share', 'site', 'sms_client_user', 'tag', 'talk', 'topic', 'topic_attach', 'topic_image', 'topic_music', 'topic_video', 'url', 'validate', 'vote', 'wall', 'xwb_bind_info');
		$cdata = array();
		foreach($ctbs as $ctb) {
			$TCT = 0;
			$sql = 'SELECT COUNT(1) AS `TCT` FROM ' . DB::table($ctb);
						$query = DB::query($sql, 'SILENT');
			if($query) {
				$row = DB::fetch($query);
				$TCT = $row['TCT'];
			}
			$cdata['count_' . $ctb] = $TCT;
		}
		$cdata['count_data_length'] = cache_file('get', 'misc/data_length');

		cache_file('set', $cache_id, $cdata, 86400);
	}
	return array_merge($data, $cdata);
}

?>