<?php
/**
 *
 * 核心函数
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: global.func.php 5558 2014-02-19 10:53:06Z yupengfei $
 */



if(!function_exists('jaddslashes')) {
	function jaddslashes($string) {
		if(is_array($string)) {
			$keys = array_keys($string);
			foreach($keys as $key) {
				$val = $string[$key];
				unset($string[$key]);
				$string[jjaddslashes($key)] = jaddslashes($val);
			}
		} else {
			$string = jjaddslashes($string);
		}
		return $string;
	}
}
if(!function_exists('jstripslashes')) {
	function jstripslashes($string) {
				if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = jstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}
}

function jjaddslashes($str) {
			if(MAGIC_QUOTES_GPC) $str = stripslashes($str);
		if('gbk'==$GLOBALS['_J']['charset']) {
		$str = gbk_addslashes($str);
	} else {
		$str = addslashes($str);
	}
	return $str;
}

function gbk_addslashes($text) {
	for ( ; ; ) {
		$i = mb_strpos($text, chr(92), 0, "GBK");
		if ($i === false) break;
		$T = mb_substr($text, 0, $i, "GBK") . chr(92) . chr(92);
		$text = substr($text, strlen($T) - 1);
		$OK .= $T;
	}
	$text = $OK . $text;
	$text = str_replace(chr(39), chr(92) . chr(39), $text);
	$text = str_replace(chr(34), chr(92) . chr(34), $text);
	$text = str_replace("\x00", '\0', $text);
	return $text;
}


function error_404($msg = '') {
	include ROOT_PATH . 'include/error_404.php';
	exit;
}

if(!function_exists('file_put_contents')) {
	!defined('FILE_APPEND') && define('FILE_APPEND', 8);
	
	function file_put_contents($filename, $data, $flag = false) {
		$mode = ($flag == FILE_APPEND || strtoupper ( $flag ) == 'FILE_APPEND') ? 'ab' : 'wb';
		if ( is_array ( $data )){
			$data = implode ( '', $data );
		}
		return jio()->WriteFile($filename, $data, $mode);
	}
}

if(!function_exists('jfsockopen')) {
	function jfsockopen($hostname, $port, $errno, $errstr, $timeout) {
		$fp = false;
		if(function_exists('fsockopen')) {
			@$fp = fsockopen($hostname, $port, $errno, $errstr, $timeout);
		} elseif(function_exists('pfsockopen')) {
			@$fp = pfsockopen($hostname, $port, $errno, $errstr, $timeout);
		}
		return $fp;
	}
}

if(!function_exists('jstrpos')) {
	function jstrpos($haystack, $needle, $offset = null) {
		$jstrpos = false;

		if(function_exists('mb_strpos')) {
			$jstrpos = mb_strpos($haystack, $needle, $offset, $GLOBALS['_J']['charset']);
		} elseif(function_exists('strpos')) {
			$jstrpos = strpos($haystack, $needle, $offset);
		}

		return $jstrpos;
	}
}

function jlog($type='',$log='',$halt=1) {
	static $gets;
	$type = dir_safe($type);
	$logfile = ROOT_PATH . 'data/log/'.$type . '-' . date('Y-m').'.log.php';
	if(is_array($log)) {
		$log = var_export($log, true);
	}
	$log = str_replace(array('<?'), array('\< \?'), $log);
	clearstatcache();
	if (!is_file($logfile)) {
		$log = "<?php exit; ?>\r\n" . $log;
	} else {
				if(filesize($logfile) > 2048000) {
			copy($logfile, ROOT_PATH . 'data/log/'.$type . '-' . date('Y-m-d-H-i').'.log.php');
			jio()->WriteFile($logfile, "<?php exit; ?>\r\n");
		}
	}
	$getid = md5($type . serialize($_GET));
	if(!isset($gets[$getid])) {
		$gets[$getid] = 1;
		$log .= "\r\n[_GET]" . var_export($_GET, true) . "\r\n";
		if('POST' == $_SERVER['REQUEST_METHOD']) {
			$log .= "\r\n[_POST]" . var_export($_POST, true) . "\r\n";
		}
		if(jget('debug')) {
			$log .= "\r\n[_SERVER]" . var_export($_SERVER, true) . " \r\n";
		}
		$log .= "[". my_date_format(TIMESTAMP, "Y-m-d H:i:s ") . $GLOBALS['_J']['client_ip']."] \r\n";
	}
	$log .= "\r\n\r\n";
	if (!is_dir(dirname($logfile))) {
		jio()->MakeDir(dirname($logfile));
	}
	jio()->WriteFile($logfile,$log,'a');
	if($halt) {
		exit();
	}
}


function jclass($class_name) {
	return Load::C($class_name);
}
function jext($filename, $class_name = '') {
	return Load::ext($filename, $class_name);
}
function jfunc($name) {
	return Load::func($name);
}
function jlogic($logic_name, $init = 1) {
	return Load::logic($logic_name, $init);
}
function jtable($table_name) {
	return Load::table($table_name);
}
function jform() {
	return jclass('jishigou/form');
}
function jio() {
	return jclass('jishigou/io');
}
function jupload() {
	return jclass('jishigou/upload');
}


function jaccess($mod, $code = '', $uid = MEMBER_ID, $is_admin = 0) {
	$ret = false;
	if(($mh = & Obj::registry('MemberHandler'))) {
		$ret = (($mh->access($mod, $code, $uid, $is_admin)) ? true : false);
	}
	return $ret;
}

function jdisallow($uid = null) {
		if(MEMBER_ID < 1) {
		return true;
	}
	if('admin' != MEMBER_ROLE_TYPE) {
		if(isset($uid)) {
						if(MEMBER_ID != $uid) {
				return true;
			}
		} else {
			return true;
		}
	}
		return false;
}
function jallow($uid = null) {
	return jdisallow($uid) ? false : true;
}


function jget($key, $filter='', $method='PG') {
	return get_param($key, $method, $filter);
}
function jpost($key, $filter = '', $method = 'P') {
    return get_param($key, $method, $filter);
}

function jfilter($val, $filter) {
	$filter = strtolower($filter);
	switch ($filter) {
		case 'int': $val = (int) $val; break;
		case 'float': $val = (float) $val; break;
		case 'bool': case 'boolean': $val = (bool) $val; break;
		case 'num': case 'number': $val = (is_numeric($val) ? $val : 0); break;
		case 'trim': $val = trim($val); break;
		case 'txt': $val = trim(jhtmlspecialchars(strip_tags($val))); break;
		case 'html': $val = jhtmlspecialchars($val); break;
				case 'url': $val = (($val && preg_match("/^(https?\:\/\/|www\.)([A-Za-z0-9_\-]+\.)+[A-Za-z]{2,4}(\/[\w\d\/=\?%\-\&_~`@\[\]\:\+\#]*([^<>\'\"\n])*)?$/", $val)) ? $val : false); break;
				case 'email': $val = (($val && false !== strpos($val, '@') && preg_match('~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i', $val)) ? $val : false); break;
				case 'zip': $val = (($val && is_numeric($val) && preg_match('~^\d{6}$~', $val)) ? $val : false); break;
				case 'qq': $val = (($val && is_numeric($val) && preg_match('~^[1-9]\d{4,10}$~', $val)) ? $val : false); break;
				case 'mobile': $val = (($val && is_numeric($val) && preg_match('~^((?:13|15|18)\d{9}|0(?:10|2\d|[3-9]\d{2})[1-9]\d{6,7})$~', $val)) ? $val : false); break;
				case 'chinese': $val = (($val && preg_match('~^(?:[\x7f-\xff][\x7f-\xff])+$~', $val)) ? $val : false); break;
				case 'english': $val = (($val && preg_match('~^[A-Za-z]+$~', $val)) ? $val : false); break;
				case 'username': $val = (($val && !is_numeric($val) && preg_match('~^[\w\d_]{1, 30}$~', $val)) ? $val : false); break;
		default:
						break;
	}
	return $val;
}

function get_param($key, $method='PG', $filter='') {
	
	$method = strtoupper($method);
	switch ($method) {
		case 'POST': case 'P': $var = &$_POST; break;
		case 'GET': case 'G': $var = &$_GET; break;
		case 'COOKIE': case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_POST[$key])) {
				$var = &$_POST;
			} else {
				$var = &$_GET;
			}
			break;
	}
	$val = (isset($var[$key]) ? $var[$key] : null);
	
	if($filter) {
		$val = jfilter($val, $filter);
	}
	return $val;
}

function array_remove_empty($array){
	if(!$array){
		return array();
	}
	foreach($array as $key=>$val){
		if (!$val) {
			unset($array[$key]);
		}
	}
	return $array;
}




function cache($name,$lifetime=null,$only_get=false)
{
	static $S_filelist=null, $S_lastfile=null, $S_file=null, $S_caches=null;

	$path = (defined('TEMPLATE_ROOT_PATH') ? TEMPLATE_ROOT_PATH : ROOT_PATH) . "data/cache/";

	if($lifetime!==null)
	{
		if($S_file!==null) $S_lastfile = $S_file;
		$S_file = $path.$name.'.cache.php';
		$S_filelist[$S_file] = $S_lastfile;
		$file=$S_file;
		if($only_get) $S_file=null;
		if ($lifetime==0) return @unlink($file);
		if($S_caches[$name.$lifetime]!==null) return $S_caches[$name.$lifetime];
		@include($file);
		if(null!==$cache && (-1==$lifetime || @filemtime($file)+$lifetime>time())) return ($S_caches[$name.$lifetime]=$cache);
	}
	else
	{
		if($S_file===null)if($S_lastfile===null)return false;else $S_lastfile=$S_filelist[$S_file=$S_lastfile];
		if(is_writeable($path)===false && is_dir($path))return trigger_error("缓存目录 $path 不可写",E_USER_WARNING);
		if(is_dir($cache_dir=dirname($S_file))==false) jmkdir($cache_dir);
		$data=var_export($name,true);
		$data="<?php if(!defined('IN_JISHIGOU')) exit('invalid request'); \r\n\$cache=$data;\r\n?>";
		$len = jio()->WriteFile($S_file, $data);
		@chmod($S_file, 0777);
		$S_file=null;
		return $len;
	}
	return false;
}

function jcache($cmd, $key='', $val='', $life=0, $type='file') {
	$cmds = array('get'=>1, 'mget'=>1, 'set'=>1, 'mset'=>1, 'rm'=>1, 'mrm'=>1, 'del'=>1, 'clear'=>1, 'clean'=>1);
	if(isset($cmds[$cmd])) {
		$type = ('db' == $type ? 'db' : 'file');
		switch ($cmd) {
			case 'get': return jclass('cache/' . $type)->get($key); break;
			case 'mget': return jclass('cache/' . $type)->get($key, 1); break;
			case 'set': return jclass('cache/' . $type)->set($key, $val, $life); break;
			case 'mset': return jclass('cache/' . $type)->set($key, $val, $life, 1); break;
			case 'rm' : case 'del': return jclass('cache/' . $type)->rm($key, $val); break;
			case 'mrm' : return jclass('cache/' . $type)->rm($key, $val, 1); break;
			case 'clear': case 'clean': return jclass('cache/' . $type)->clear(); break;
		}
	}
	return null;
}

function cache_file($cmd, $key='', $val='', $life=0) {
	return jcache($cmd, $key, $val, $life, 'file');
}

function cache_db($cmd, $key='', $val='', $life=0) {
	return jcache($cmd, $key, $val, $life, 'db');
}
function cache_mem($cmd, $key='', $val='', $life=0) {
	if($GLOBALS['_J']['config']['memory_enable']) {
		if($GLOBALS['_J']['config']['cache_file_to_memory'] || $GLOBALS['_J']['config']['cache_db_to_memory']) {
			if('m' != substr($cmd, 0, 1) && in_array($cmd, array('get', 'set', 'rm'))) {
				$cmd = 'm' . $cmd;
			}
			return jcache($cmd, $key, $val, $life, ($GLOBALS['_J']['config']['cache_file_to_memory'] ? 'file' : 'db'));
		}
	}
	return false;
}

function cache_clear() {
	$dirs = array(
		'data/cache/',
		'wap/data/cache/',
		'mobile/data/cache/',
		'images/temp/face_images/',
		'api/uc_client/data/cache/',
	);
	foreach($dirs as $dir) {
		@jio()->ClearDir(ROOT_PATH . $dir);
	}

	cache_file('clear');
}


function &Tag($type)
{
	include_once(ROOT_PATH . 'include/logic/tag.logic.php');

	return new TagLogic($type);
}

function order($order_by_list,$query_link='',$config=array())
{
	include_once(ROOT_PATH . 'include/func/order.func.php');

	return __order($order_by_list,$query_link,$config);
}

function pre($string)
{
	$string=nl2br($string);
	$string = str_replace(array("&amp;","&gt;","&lt;","&quot;","&#39;","\s","\t",),
	array("&", ">","<","\"","'","&nbsp;","&nbsp;&nbsp;&nbsp;&nbsp;",),  $string);
	return $string;
}

if(false == function_exists('http_build_query'))
{
	
	function http_build_query($form_data, $numeric_prefix = null)
	{
		static $_query = '';

		if(is_array($form_data)==false)Return false;
		foreach($form_data as $key => $values)
		{
			if(is_array($values))
			{
				$_query = http_build_query($values, isset($numeric_prefix)?sprintf('%s[%s]', $numeric_prefix, urlencode($key)):$key);
			}
			else
			{
				$key = isset($numeric_prefix)?sprintf('%s[%s]', $numeric_prefix, urlencode($key)):$key;
				$_query .= (isset($_query) ? '&' : null) . $key . '=' . urlencode(stripslashes($values));
			}
		}
		Return $_query;
	}

}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	if(!$key) {
		$sys_config = jconf::get();
		$key = $sys_config['auth_key'];
	}
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}


function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().var_export($_SERVER, true)), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}



function build_like_query($fields,$keywords,$binary=false)
{
	if(trim($keywords)==false)Return '';
	$binary = ($binary ?' binary ' : '');
	$keywords=preg_replace('~[\t\s　]+and[\t\s　]+~i','\%',$keywords);
	$keyword_list=preg_split('~([\t\s　]+or[\t\s　]+)|\|~i',$keywords);
	if(count($keyword_list)>1 and $fields==false)die("搜索多个关键字其中部分，必须指定参数\$field");
	
	foreach($keyword_list as $key=>$keyword)
	{
						$keyword = addcslashes($keyword, '_%"\'\\');
				$keyword = str_replace(array('*', ), array('%', ), $keyword);
		$temp_list[] = $keyword;
	}

	$keywords = '';
	if(strpos($fields,',')!==false)
	{
		$field_list=explode(',',$fields);
		foreach($field_list as $field)
		{
			$keywords_list[]=$binary." ".$field.'  like "%'.implode("%\" OR \r\n".$binary.' '.$field.'  like "%',$temp_list)."%\"";
		}
		$keywords='(('.implode(') or (',$keywords_list).'))';
	}
	else
	{
		$keywords=$binary." ".$fields.' like "%'.implode("%\" OR \r\n".$binary.' '.$fields.' like "%',$temp_list)."%\"";
	}
	$keywords=preg_replace("~[%]+~",'%',$keywords);

	return $keywords;
}


function response_text($response)
{
	ob_clean();

	echo $response; exit;
}


function debug($mixed,$halt=true)
{
	static $num=1;
	if (function_exists("debug_backtrace"))
	{
		$debug=debug_backtrace();
		echo "<div style=\"background:#FF6666;color:#fff;margin-top:5px;padding:5px\">".$num++.".debug position: {$debug[0]["file"]}({$debug[0]["line"]})</div>";
	}
	echo "<div style=\"border:1px solid #ff6666;background:#fff;padding:10px\"><pre>";
	if (is_array($mixed))
	{
		echo str_replace(array("&lt;?php","?&gt;"),"",highlight_string("<?php\r\n".var_export($mixed,true).";\r\n?>",true));
	}
	else
	{
		var_dump($mixed);
	}
	echo "</pre></div>";
	$halt && exit;
}

if (false == function_exists('iconv')) {
	
	function iconv($in_charset,$out_charset,$str) {
		if($str && strtoupper($in_charset)!=strtoupper($out_charset)) {
			if(false!==strpos($out_charset,'/'.'/')) {
				$out_charset = str_replace(array('/'.'/IGNORE','/'.'/TRANSLIT'),'',strtoupper($out_charset));
			}
			$obj = jclass('encoding/chinese');
			$obj->init($in_charset,$out_charset);
			return $obj->Convert($str);
		}
		return $str;
	}
}


function array_iconv($in_charset,$out_charset,$array,$addsl=0) {
	if($array && strtoupper($in_charset)!=strtoupper($out_charset) && (function_exists('mb_convert_encoding') || function_exists('iconv'))) {
		if(is_array($array)) {
			foreach($array as $key=>$val) {
				$key = lconv($in_charset, $out_charset, $key);
				$array[$key] = array_iconv($in_charset,$out_charset,$val);
			}
		} else {
			$array = lconv($in_charset,$out_charset,$array);
		}
		if($addsl) {
			$array = jaddslashes($array);
		}
	}
	return $array;
}
function lconv($in_charset,$out_charset,$string) {
	$return = '';

	if($string) {
		if (!is_numeric($string) && !is_bool($string) && is_string($string)) {
			if (function_exists('iconv')) {
				$return = iconv($in_charset,$out_charset . (false!==strpos($out_charset,'/'.'/') ? '' : "/"."/TRANSLIT"), $string);
			} elseif(function_exists('mb_convert_encoding')) {
				$return = mb_convert_encoding($string, $out_charset, $in_charset);
			}
		} else {
			$return = $string;
		}
	}

	if(!$return) {
		$return = $string;
	}

	return $return;
}



function referer($default = '?', $ignore_domain = 0) {
	$ignore_domain = (isset($_POST['ignore_domain']) ? $_POST['ignore_domain'] : (isset($_GET['ignore_domain']) ? $_GET['ignore_domain'] : $ignore_domain));
	$referer = jget('referer');
	if(empty($referer)) {
		$referer = $_SERVER['HTTP_REFERER'];
	}
	if($referer=="" ||
	(true !== IN_JISHIGOU_ADMIN &&
	(strpos($referer,'register')!==false ||
	strpos($referer,'login')!==false ||
	strpos($referer,'logout')!==false)) ||
	(!$ignore_domain &&
	strpos($referer,":/"."/")!==false &&
	($DOMAIN = preg_replace('~^www\.~','',strtolower(getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST']))) &&
	strpos($referer,$DOMAIN)===false)) {
		global $jishigou_rewrite;
		if($jishigou_rewrite) {
			$default = $jishigou_rewrite->formatURL($default,false);
		}
		return $default;
	}
	return $referer;
}



function my_date_format($timestamp,$format="Y-m-d H:i:s") {
	return gmdate($format,($timestamp+$GLOBALS['_J']['config']['timezone']*3600));
}

function cut_str($string, $length, $dot = ' ...')
{
	if(strlen($string) <= $length) {
		return $string;
	}

	
	$strcut = '';
	if(strtolower($GLOBALS['_J']['charset']) == 'utf-8') {
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	
	return $strcut.$dot;
}
function cutstr($string,$length,$dot=''){Return cut_str($string,$length,$dot);};

function strip_selected_tags(&$str,$disallowable="<script><iframe><style><link>")
{
	$disallowable=trim(str_replace(array(">","<"),array("","|"),$disallowable),'|');
		$str=preg_replace("~<({$disallowable})[^>]*>(.*?<\s*\/(\\1)[^>]*>)?~is",'',$str);
	return $str;
}

function page($total_record, $per_page_num,$url='', $_config = array(), $per_page_nums = "") {
	if(true===IN_JISHIGOU_INDEX || true===IN_JISHIGOU_AJAX) {
		global $jishigou_rewrite;
	}

	$sys_config = jconf::get();
		if(true === IN_JISHIGOU_ADMIN && isset($sys_config['total_page_default'])) {
		unset($sys_config['total_page_default']);
	}

	$result = array();

	$total_record = intval($total_record);
	$per_page_num = intval($per_page_num);
	if($per_page_num < 1) $per_page_num = 10;
	$config['total_page'] = max(0,(int) (isset($_config['total_page']) ? $_config['total_page'] : $sys_config['total_page_default']));	$config['page_display'] = isset($_config['page_display']) ? (int) $_config['page_display'] : 5;	$config['char'] = isset($_config['char']) ? (string) $_config['char'] : ' ';	$config['url_postfix'] = isset($_config['url_postfix']) ? (string) $_config['url_postfix'] : '';	$config['extra'] = isset($_config['extra']) ? (string) $_config['extra'] : '';	$config['idencode'] = (bool) $_config['idencode'];	$config['var'] = isset($_config['var']) ? (string) $_config['var'] : 'page';	$config['return'] = isset($_config['return']) ? (string) $_config['return'] : 'html';	if(!$url) {
		$config['url'] = $_config['page_url'] ? $_config['page_url'] : ($_config['page_link'] ? $_config['page_link'] : $_config['url']);
	}
	$config['per_page_nums'] = ($per_page_nums ? $per_page_nums : $_config['per_page_nums']);

	extract($config);

	$total_page = ceil($total_record / $per_page_num);
	if($config['total_page']>1 && $total_page > $config['total_page']) {
		$total_page = $config['total_page'];
	}

	$result['total_record'] = $total_record;
	$result['total_page'] = $total_page;
	$current_page=$_GET[$var]?$_GET[$var]:$_POST[$var];
	$current_page = max(1,(int) ((true == $idencode) ? iddecode($current_page) :$current_page));
	$current_page = ($total_page > 0 && $current_page > $total_page) ? $total_page : $current_page;
	$result['current_page'] = $current_page;
	$result['title_postfix'] = $current_page > 1 ? "_第{$current_page}页" : "";
	$result['offset'] = (int) (($current_page - 1) * $per_page_num);

	$result['limit'] = " LIMIT ".$result['offset'].",{$per_page_num} ";

	if(isset($result[$return])) {
		return $result[$return];
	}

	if('' == $url) {
		$request = count($_POST) ? array_merge($_GET,$_POST) : $_GET;
		$query_string = '';
		foreach($request as $_var => $_val) {
			if(is_string($_val) && $var!==$_var) $query_string .= "&{$_var}=" . urlencode($_val);
		}
		$url = '?'.($result['query_string'] = trim($query_string,'&'));
	}

	$p_val = "V01001page10010V";
	if('/#'!=$url) {
		$url = ('' == $url) ? "?$var={$p_val}" : (($url_no_page = (false !== strpos($url,"&{$var}=") ? preg_replace("/\&?{$var}\=[^\&]*/i",'',$url) : $url)) . "&{$var}={$p_val}");
		if($jishigou_rewrite) {
			$url_no_page = $jishigou_rewrite->formatURL($url_no_page,false);
			$url=$jishigou_rewrite->formatURL($url,false);
		}
	} else {
		$url_no_page = $url;
	}
	$result['url'] = $url;

	if(isset($result[$return])) {
		return $result[$return];
	}

	$html = '';
	if($total_record > $per_page_num) {
		$halfper = (int) ($config['page_display'] / 2);

		$html=($current_page - 1 >= 1) ? "\n<a href='{$url_no_page}{$url_postfix}' title=1 {$extra}>首页</a>{$char}\n<a href='".(1 == ($previous_page = ($current_page - 1)) ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($previous_page):$previous_page),$url))."{$url_postfix}' title=$previous_page {$extra}>上一页</a>{$char}" : "首页{$char}上一页{$char}";

		for ($i=$current_page-$halfper,$i>0 or $i=1,$j=$current_page + $halfper,$j<$total_page or $j=$total_page;$i<=$j;$i++) {
			$html.=($i==$current_page)?"\n<B>".($i)."</B>{$char}":"\n<a href='".(1 == $i ? $url_no_page : str_replace($p_val,(true===$idencode?idencode($i):$i),$url))."{$url_postfix}' title=$i {$extra}>".($i)."</a>{$char}";
		}

		$html.=(($next_page=($current_page + 1)) > $total_page)?"下一页{$char}尾页":"\n<a href='".str_replace($p_val,(true===$idencode?idencode($next_page):$next_page),$url)."{$url_postfix}' title=$next_page {$extra}>下一页</a>{$char}\n<a href='".str_replace($p_val,(true===$idencode?idencode($total_page):$total_page),$url)."{$url_postfix}' title=$total_page {$extra}>尾页</a>";

		$html .= "<input type='text' id='htmlpagenum' value='".$current_page."' style='width:25px;margin:0 2px 0 5px;' onKeyDown=\"if(event.keyCode==13) window.location='".str_replace($p_val,'',$url)."'+this.value\"><input type='button' value='确定' class='u-btn button' onclick=\"window.location='".str_replace($p_val,'',$url)."'+document.getElementById('htmlpagenum').value\">";

		if(!empty($per_page_nums)) {
			$per_page_num_list=is_array($per_page_nums)?$per_page_nums:explode(" ",$per_page_nums);
			$current_url=str_replace($p_val,(true===$idencode?idencode($current_page):$current_page),$url).$url_postfix;
			$pn_postfix=$jishigou_rewrite?$jishigou_rewrite->argSeparator."pn".$jishigou_rewrite->varSeparator:"&pn=";
			$per_page_num_select="<select name='per_page_num' onchange=\"window.location='{$current_url}{$pn_postfix}'+this.value\">";
			foreach ($per_page_num_list as $_per_page_num) {
				$selected=$_per_page_num==$per_page_num?"selected":"";
				$per_page_num_select.="<option value={$_per_page_num} $selected>{$_per_page_num}";
			}
			$per_page_num_select.="</select>";
		} else {
			$per_page_num_select="<i>{$per_page_num}</i>";
		}

		$html ="<div id='page'> {$html} &nbsp;每页${per_page_num_select}条/共<i>{$total_record}</i>条</div>";	}
	$result['html'] = $html;

	if(isset($result[$return])) {
		return $result[$return];
	}

	return $result;
}


function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}


function makethumb($srcfile,$dstfile,$thumbwidth,$thumbheight,$maxthumbwidth=0,$maxthumbheight=0,$src_x=0,$src_y=0,$src_w=0,$src_h=0, $thumb_cut_type=0, $thumb_quality = 100) {
	if(!function_exists('__makethumb')) {
		jfunc('thumb');
	}
	return __makethumb($srcfile,$dstfile,$thumbwidth,$thumbheight,$maxthumbwidth,$maxthumbheight,$src_x,$src_y,$src_w,$src_h, $thumb_cut_type, $thumb_quality);
}



function filter(&$string, $verify=1, $replace=1,$shield=0) {
	if(!function_exists('__filter')) {
		jfunc('filter');
	}
	return __filter($string, $verify, $replace, $shield);
}

function request($action, $post=array(), &$error) {
	if(!function_exists('__request')) {
		jfunc('request');
	}
	return __request($action, $post, $error);
}

function dfopen($url, $limit = 10485760 , $post = '', $cookie = '', $bysocket = false,$timeout=5,$agent="") {
	if(!function_exists('__dfopen')) {
		jfunc('request');
	}
	return __dfopen($url, $limit , $post, $cookie, $bysocket, $timeout, $agent);
}

function str_exists($haystack,$needle) {
	$arg_list = func_get_args();
	while(($needle=$arg_list[++$i])!==null) {
		if(strpos($haystack,$needle)!==false)return true;
	}
	return false;
}

function client_ip() {
	$ipd = '127.0.0.1';
	$vs = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
	foreach($vs as $v) {
		$ip = (getenv($v) ? getenv($v) : $_SERVER[$v]);
		if($ip && strcasecmp($ip, 'unknown') && preg_match('~^([0-9]{1,3}\.){3}[0-9]{1,3}$~', $ip)) {
			break;
		} else {
			$ip = $ipd;
		}
	}
		$ips = explode('.', $ip);
	for($i = 0; $i < 4; $i++) {
		$ipi = (is_numeric($ips[$i]) ? (int) $ips[$i] : -1);
		if($ipi < 0 || $ipi > 255) {
			$ip = $ipd;
			break;
		}
	}
	return $ip;
}
function client_ip_port() {
	return (int) (getenv('REMOTE_PORT') ? getenv('REMOTE_PORT') : $_SERVER['REMOTE_PORT']);
}

function face_path($uid) {
	$key = "ww"."w."."ji"."s"."hi"."go"."u.c"."om"; 	$hash = md5($key."\t".$uid."\t".strlen($uid)."\t".$uid % 10);
	$path = $hash{$uid % 32} . "/" . abs(crc32($hash) % 100) . "/";
		return $path;
}
function jsg_uc_face_path($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}
function face_get($users=array(), $type='small', $check_exists = 0) {
	if(is_numeric($users)) {
		$users = jsg_member_info($users);
	}

	if(is_array($users)) {
		$uid = $users['uid'];
		$ucuid = $users['ucuid'];
		$face_url = $users['face_url'];
		$face = $users['face'];

		unset($users);
	}

		$file = $GLOBALS['_J']['site_url'] . '/images/noavatar.gif';
	if($uid < 1) {
		return $file;
	}

	$mods = array('share'=>1, 'show'=>1, 'output'=>1, 'member'=>1, );

		if(true === UCENTER_FACE && true === UCENTER) {
		if(null === $ucuid) {
			$ucuid = DB::result_first("select `ucuid` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}

		if($ucuid > 0) {
			if('small'!=$type) {
								$type = 'big';
			}

						if(!$check_exists && !isset($mods[$_GET['mod']]) && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX)) {
				$file = UC_API . '/data/avatar/' . jsg_uc_face_path($ucuid, $type, 'virtual');
							} else {
				$file = UC_API . "/avatar.php?uid={$ucuid}&type=virtual&size={$type}";
							}

			return $file;
		}
	}

		if(true === UCENTER_FACE && true === PWUCENTER)
	{
		if(null === $ucuid)
		{
			$ucuid = DB::result_first("select `ucuid` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}

		if($ucuid > 0)
		{
			if('small'!=$type)
			{
				$type = 'middle';
			}

						$phpwind_config = jconf::get('phpwind');
			if($phpwind_config['face'] && $phpwind_config['enable']){
				Load::logic("topic_bbs");
				$PwBbsLogic = new TopicBbsLogic();
				$icon = $PwBbsLogic->get_pw_uicon($ucuid);
			}
			if($icon && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX))
			{
				$file = strncmp($icon,'http',4) == 0 ? $icon : UC_API . $icon;
			}
			else
			{
				$file = UC_API . '/images/face/none.gif';
			}

			return $file;
		}
	}

		$type = ('small' == $type ? 's' : 'b');
	$file = 'images/face/' . face_path($uid) . $uid . "_{$type}.jpg";


		if($GLOBALS['_J']['config']['ftp_on']) {
		if($face && null === $face_url) {
			$face_url = DB::result_first("select `face_url` from ".TABLE_PREFIX."members where `uid`='$uid'");
		}
	} else {
		if(!$check_exists && !isset($mods[$_GET['mod']]) && (TRUE===IN_JISHIGOU_INDEX || TRUE===IN_JISHIGOU_AJAX)) {
			;
		} else {
			if(!file_exists(ROOT_PATH . $file)) {
				$file = 'images/noavatar.gif';
			}
		}
	}

	if(!$face_url) {
		$face_url = $GLOBALS['_J']['site_url'];
	}

	$file = ($face_url . "/" . $file);

	return $file;
}

function topic_image($id,$type='small',$relative=true)
{
	$type = ('photo' == $type ? 'p' : ('small' == $type ? 's' : 'o'));
	$file = 'images/topic/' . face_path($id) . $id . "_{$type}.jpg";
	if($relative)
	{
		$file = RELATIVE_ROOT_PATH . $file;
	}
	else
	{
		static $sys_config=null;
		if(is_null($sys_config)) {
			$sys_config = jconf::get();
		}

				if($sys_config['ftp_on'])
		{
			if(!($site_url = $GLOBALS['ftp_site_urls'][$id]))
			{
				$site_url = jlogic('image')->get_site_url($id);
				$GLOBALS['ftp_site_urls'][$id] = $site_url;
			}
		}
		if(!$site_url)
		{
			$site_url = $sys_config['site_url'];
		}

		$file = $site_url . '/' . $file;
	}

	return $file;
}
function topic_attach($id,$str='file',$relative=true)
{
		$file = DB::result_first("select `$str` from " . TABLE_PREFIX . "topic_attach where `id`='$id'");
	if('file' == $str){
		if($relative)
		{
			$file = RELATIVE_ROOT_PATH . $file;
		}
		else
		{
			static $sys_config=null;
			if(is_null($sys_config)) {
				$sys_config = jconf::get();
			}

						if($sys_config['ftp_on'])
			{
				if(!($site_url = $GLOBALS['ftp_site_urls'][$id]))
				{
					$site_url = DB::result_first("select `site_url` from " . TABLE_PREFIX . "topic_attach where `id`='$id'");

					$GLOBALS['ftp_site_urls'][$id] = $site_url;
				}
			}
			if(!$site_url)
			{
				$site_url = $sys_config['site_url'];
			}

			$file = $site_url . '/' . $file;
		}
	}
	return $file;
}

function get_safe_code($value, $addsl=1) {
	if(empty($value) || is_numeric($value)) {
		return $value;
	}
	if(preg_match('~^[\x01-\x7f]+$~',$value)) {
		return $value;
	}
	$is_utf8 = 0;
	if(preg_match('~^([\x01-\x7f]|[\xc0-\xdf][\xa0-\xbf])+$~',$value)) {
		;
	} else {
		if(preg_match('~^([\x01-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5})+$~',$value)) {
			$is_utf8 = 1;
		}
	}
	if('utf-8'==strtolower($GLOBALS['_J']['charset'])) {
		return ($is_utf8 ? $value : array_iconv("gbk", "utf-8", $value, $addsl));
	} else {
		return ($is_utf8 ? array_iconv("utf-8", "gbk", $value, $addsl) : $value);
	}
}

function buddy_follow_html($member_list, $uid_field='uid', $follow_func='follow_html', $is_one_row = 0) {
	return jlogic('buddy')->follow_html($member_list, $uid_field, $follow_func, $is_one_row);
}

function follow_html($uid,$follow=0,$follow_me=0,$addhtml=true,$refresh=0) {
	$html = "";
	if(MEMBER_ID>0 && MEMBER_ID!=$uid && $uid>0) {
		if ($follow) {
			if($follow_me) { 				$html = "<a href='javascript:void(0)' title=\"已互相关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','');return false;\" class='follow_html2_3'></a>";
			} else {
								$html = "<a href='javascript:void(0)' title=\"已关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','');return false;\" class='follow_html2_2'></a>";
			}
		} else {
						$html = "<a href='javascript:void(0)' title=\"加关注\" onclick=\"follow({$uid},'follow_{$uid}','add');return false;\" class='follow_html2_1'></a>";
		}

		if($addhtml) $html = "<span id='follow_{$uid}' class='follow_{$uid}'>{$html}</span>";
	}

	return $html;
}

function follow_html2($uid,$follow=0,$follow_me=0,$addhtml=true,$refresh=0) {
	$html = "";
	if(MEMBER_ID>0 && MEMBER_ID!=$uid && $uid>0) {
		if ($follow) {
			if($follow_me) { 				$html = "<a href='javascript:void(0)' class=\"follow_html2_0\" title=\"已互相关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','','xiao');return false;\"></a>";
			} else {
								$html = "<a href='javascript:void(0)' class=\"follow_html2_2\" title=\"已关注，点击取消关注\" onclick=\"follow({$uid},'follow_{$uid}','','xiao');return false;\"></a>";
			}
		} else {
						$html = "<a href='javascript:void(0)' class=\"follow_html2_1\" title=\"加关注\" onclick=\"follow({$uid},'follow_{$uid}','add','xiao',$refresh);return false;\"></a>";
		}

		if($addhtml) $html = "<span id='follow_{$uid}' class='follow_{$uid}'>{$html}</span>";
	}

	return $html;
}

function follow_department($did,$follow=0)
{
	$html = '';
	if(MEMBER_ID>0){
		if ($follow) {
			$html = "<a href='javascript:void(0)' class='follow_html_d' onclick=\"follow({$did},'follow_d_{$did}','','department');return false;\">√已关注</a>";
		}else{
			$html = "<a href='javascript:void(0)' class='follow_html_n' onclick=\"follow({$did},'follow_d_{$did}','add','department');return false;\">＋关注</a>";
		}
	}
	return $html;
}

function follow_channel($ch_id,$follow=0)
{
	$html = '';
	if(MEMBER_ID>0){
		if ($follow) {
			$html = "<a href='javascript:void(0)' class='follow_html2_2' onclick=\"follow({$ch_id},'follow_c_{$ch_id}','','channel');return false;\"></a>";
		}else{
			$html = "<a href='javascript:void(0)' class='follow_html2_1' onclick=\"follow({$ch_id},'follow_c_{$ch_id}','add','channel');return false;\"></a>";
		}
	}else{
		$html = "<a href='javascript:void(0)' class='follow_html2_1' onclick=\"ShowLoginDialog();return false;\"></a>";
	}
	return $html;
}


function user_exp($user_level=0,$user_credits=0)
{
		$experience = jconf::get('experience');
	$exp_list = $experience['list'];

		$my_exp = $user_level;

		$my_credits = $user_credits;

		$next_exp = $my_exp + 1;

		$next_exp_credits = max(1, (int)$exp_list[$next_exp]['start_credits']);

		$percent = round($my_credits/$next_exp_credits, 2);

		$exp_width = round($percent * 100);

		$liter_exp  = $next_exp_credits - $my_credits;

	$exp_arr = array(
					'exp_width' => $exp_width,

					'nex_exp_credit' => $liter_exp,

				'nex_exp_level' => $next_exp ,

	);
	return $exp_arr;
}

function my_date_format2($time) {
	if(empty($time)) return '';
	$t = TIMESTAMP - $time;
	$r = '';
	if ($t >= 3600) {
		$f = 'm月d日 H时i分';
		if($t >= 31536000 || date('Y', TIMESTAMP)>date('Y', $time)) {
			$f = 'Y年m月d日 H时i分';
		}
		$r = my_date_format($time, $f);
	} elseif ($t < 3600 && $t >= 60) {
		$r = floor($t / 60) . '分钟前';
	} elseif ($t < 60) {
		$r = '刚刚';
	}
	return $r;
}

function buddy_add($buddyid, $uid=0, $delete_if_exists=0) {
	$p = array(
		'buddyid' => (int) $buddyid,
		'uid' => (int) $uid,
	);
	$ret = jlogic('buddy')->add($p, $delete_if_exists);

	return $ret;
}
function buddy_del($buddyid, $uid) {
	$ret = jlogic('buddy')->del_info($buddyid, $uid);

	return $ret;
}


function is_blacklist($touid, $uid=0) {
	$ret = array();

	$touid = (int) $touid;
	$uid = (int) ($uid ? $uid : MEMBER_ID);

	if($uid > 0 && $touid > 0 && $touid != $uid) {
		$ret = jlogic('buddy')->blacklist($touid, $uid);
	}

	return $ret;
}


function is_image($filename,$allow_types=array('gif'=>1,'jpg'=>1,'png'=>1,'bmp'=>1,'jpeg'=>1)) {
	if(!function_exists('__is_image')) {
		jfunc('image');
	}
	return __is_image($filename,$allow_types);
}

function get_full_url($site_url='',$url='',$rewrite_url_postfix='')
{
	if(false !== strpos($url, ':/'.'/')) {
		return $url;
	}

	global $jishigou_rewrite;

	if(!$site_url) {
		$site_url = $GLOBALS['_J']['site_url'];
	} else {
		if('/'==substr($site_url,-1)) {
			$site_url = rtrim($site_url,'/');
		}
	}


	$full_url = "{$site_url}/{$url}";

	if($jishigou_rewrite && $url) {
		$url = ltrim($jishigou_rewrite->formatURL($url),'/');

		$full_url = (((false!==($_tmp_pos = strpos($site_url,'/',10))) ? substr($site_url,0,$_tmp_pos) : $site_url) . '/' . $url) . $rewrite_url_postfix;
	}

	return $full_url;
}

function get_invite_url($url='',$site_url='')
{
	return get_full_url($site_url,$url,'/');
}

function jurl($url, $site_url='', $rewrite_url_postfix='') {
	return get_full_url($site_url, $url, $rewrite_url_postfix);
}

function grayJpeg($imgname) {
	if(!function_exists('__grayJpeg')) {
		jfunc('image');
	}
	return __grayJpeg($imgname);
}





class Load {
	static function func($name) {
		return @include_once(ROOT_PATH . 'include/func/' .$name.'.func.php');
	}
	static function logic($name, $init=0) {
		if(!$init) {
			return @include_once(ROOT_PATH . 'include/logic/' .$name.'.logic.php');
		} else {
			static $S_logics = array();
			if(is_null($S_logics[$name])) {
				$class_name = '';
				if(false !== strpos($name, '_')) {
					$ns = explode('_', $name);
					foreach($ns as $n) {
						$class_name .= ucfirst($n);
					}
				} else {
					$class_name = ucfirst($name);
				}
				$class_name .= 'Logic';
				if(!(@include_once ROOT_PATH . 'include/logic/' . $name . '.logic.php') && !class_exists($class_name)) {
					exit('logic ' . $name . ' is not exists');
				}
				$S_logics[$name] = new $class_name();
			}
			return $S_logics[$name];
		}
	}
	static function ext($filename, $class_name = '') {
		if(empty($class_name)) {
			return @include_once(ROOT_PATH . 'include/ext/' .$filename . '.class.php');
		} else {
			static $S_exts = array();
			if(is_null($S_exts[$filename])) {
				if(!(@include_once ROOT_PATH . 'include/ext/' . $filename . '.class.php') && !class_exists($class_name)) {
					exit('ext ' . $filename . ' is not exists');
				}
				$S_exts[$filename] = new $class_name();
			}
			return $S_exts[$filename];
		}
	}
	static function file($name) {
		$folder = 'include/class/';
		$class_name = str_replace(array('/'), '_', $name);
		if(!(@include_once ROOT_PATH . $folder . $name . '.class.php') && !class_exists($class_name)) {
			return false;
		}
		return $class_name;
	}
	static function C($name) {
		static $S_class = array();
		if(is_null($S_class[$name])) {
			if(false === ($class_name = Load::file($name))) {
				exit('class ' . $name . ' is not exists');
			}
			$S_class[$name] = new $class_name();
		}
		return $S_class[$name];
	}
	static function table($name) {
		static $S_tables = array();
		if(is_null($S_tables[$name])) {
			if(false === ($class_name = Load::file('table/' . $name))) {
				if(($obj = jclass('table'))) {
					$obj->init($name);
					
					

					return $obj;
				}
				exit('table class ' . $name . ' is not exists');
			}
			$S_tables[$name] = new $class_name();
		}
		return $S_tables[$name];
	}
}




class Obj
{
	function &Obj($name=null)
	{
		Return Obj::_share($name,$null,'get');
	}

	static function &_share($name=null,&$mixed,$type='set')
	{
		static $_register=array();

		if($name==null)
		{
			Return $_register;
		}

		if('get' == $type)
		{
			if(isset($_register[$name]))
			{
				Return $_register[$name];
			}

			return null;
		}

		if('set' == $type)
		{
			$_register[$name]=&$mixed;
		}

		return true;
	}
	
	static function register($name,&$obj)
	{
		Obj::_share($name,$obj,"set");
	}
	
	static function &registry($name=null)
	{
		Return Obj::_share($name,$null,'get');
	}
	
	static function isRegistered($name)
	{
		Return isset($_register[$name]);
	}
}

/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * 配置的读取及写入
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 */

class ConfigHandler {

	function ConfigHandler() {
		;
	}

	static function file($type=null) {
		if($type) $type = str_replace(array('.','\\','/',),'',$type);

		return ROOT_PATH . 'setting/' .($type===null?'settings':$type).'.php';
	}

	static function core_settings($local = null, $db = null, $act = 'merge') {
		$_local = array(
			'acceleration_mode' => 1,
			'auth_key' => 1,
			'charset' => 1,
			'cache_file_to_memory' => 1,
			'cache_db_to_memory' => 1,
			'cache_table_num' => 1,
			'cookie_domain' => 1,
			'cookie_expire' => 1,
			'cookie_path' => 1,
			'cookie_prefix' => 1,
			'db_host' => 1,
			'db_port' => 1,
			'db_name' => 1,
			'db_user' => 1,
			'db_pass' => 1,
			'db_table_prefix' => 1,
			'db_slave' => 1,
			'last_archive_topic_tid' => 1,
			'last_archive_topic_relation_tid' => 1,
			'last_archive_member_uid' => 1,
			'install_lock_time' => 1,
			'jishigou_founder' => 1,
			'memory_enable' => 1,
			'memory' => 1,
			'no_record_guest' => 0,
			'safe_key' => 1,
			'setting_from_db' => 1,
			'site_url' => 1,
			'site_domain' => 1,
			'upgrade_lock_time' => 1,
			'upgrade_to_lock_version' => 1,
		);
		if(is_null($local)) {
			include(ConfigHandler::file());
			$local = $config;
		}
		settype($local, 'array');
		if($local['setting_from_db']) {
			if(is_null($db)) {
				$db = ConfigHandler::db();
			}
		} else {
			if(!$local['site_admin_email'] && !$local['site_name']) {
				$db = (is_null($db) ? ConfigHandler::db() : $db);
				if($db) {
					$local = array_merge((array) $db, $local);
									}
			}
			return $local;
		}
		settype($db, 'array');
		$rets = array_merge($local, $db);
		if('merge' == $act) {
			foreach($_local as $k=>$v) {
				$rets[$k] = $local[$k];
			}
		} elseif ('local' == $act) {
			$_rets = array();
			foreach($_local as $k=>$v) {
				$_rets[$k] = $rets[$k];
			}
			$rets = $_rets;
		} elseif ('db' == $act) {
			foreach($_local as $k=>$v) {
				unset($rets[$k]);
			}
		}
		return $rets;
	}

	static function db($key = null, $val = null) {
		$ret = false;
		$type = $key;
		if(is_null($key)) {
			$key = 'core_settings';
			if($val) {
				$val = ConfigHandler::core_settings(array(), $val, 'db');
			}
		} else {
			$key = (string) $key;
		}
		$db_prefix = (defined('TABLE_PREFIX') ? TABLE_PREFIX : $GLOBALS['_J']['config']['db_table_prefix']);
		$cache_id = 'setting/' . $key;
		if(is_null($val)) {
			if(false === ($ret = cache_file('get', $cache_id))) {
				$row = array();
								$query = DB::query("select * from {$db_prefix}setting where `key`='$key'", 'SILENT');
				if(false == $query) {
					$ret = array();
				} else {
					$row = DB::fetch($query);
				}
				if($row) {
					$ret = unserialize(base64_decode($row['val']));
					$ret = ($ret ? $ret : array());
				} else {
					@include(ConfigHandler::file($type));
					$ret = (is_null($type) ? $config : $config[$type]);
					$ret = ($ret ? $ret : array());
					ConfigHandler::db($type, $ret);
					if(is_null($type) && $ret) {
						ConfigHandler::set($ret);
					}
				}
				cache_file('set', $cache_id, $ret);
			}
		} else {
			$ret = DB::query("replace into {$db_prefix}setting (`key`, `val`) values ('$key', '".(base64_encode(serialize($val)))."')", 'SILENT');
			cache_file('rm', $cache_id);
		}
		return $ret;
	}

	
	static function get() {
		global $_J;

		$config = array();
		$type = null;
		$func_num_args = func_num_args();
		if(0 === $func_num_args || (($func_args = func_get_args()) && is_null(($type = $func_args[0])))) {
			if(!$_J['config']['auth_key']) {
				@include(ConfigHandler::file());
				if($config) {
					$_J['config'] = $config;
					$_J['config'] = ConfigHandler::core_settings($_J['config']);
				} else {
					$_J['config']['auth_key'] = random(64);
				}
			}
			return $_J['config'];
		} else {
			if(!isset($_J['config'][$type])) {
				$_local = array('modules'=>1, 'theme'=>1, 'table'=>1, 'search_admin_menu_index'=>1, );
				if($_J['config']['setting_from_db'] && !isset($_local[$type])) {
										$config[$type] = ConfigHandler::db($type);
				} else {
					@include(ConfigHandler::file($type));
				}
				if(!isset($config[$type])) {
					$config[$type] = array();
									}
				$_J['config'][$type] = $config[$type];
			}

			if($func_num_args===1) {
				return $_J['config'][$type];
			}

			if(isset($_J['config'][$type])) {
				$path_str = '';
				foreach($func_args as $arg) {
					$arg = str_replace(array(';', '"', "'", ), '', $arg);
					$path_str.="['$arg']";
				}
				return eval('return $_J["config"]' . $path_str . ";");
			}
		}

		return null;
	}

	
	static function set() {
		$func_args=func_get_args();
		$value=array_pop($func_args);
		$type=array_shift($func_args);

		ConfigHandler::backup($type);

		$file=ConfigHandler::file($type);
		$data = '';
		if($type===null) {
			if($value && $value['auth_key']) {
				ksort($value);
				$keeps = array(
					'acceleration_mode' => 1,
					'charset' => 1,
					'db_host' => 1,
					'db_port' => 1,
					'db_name' => 1,
					'db_user' => 1,
					'db_pass' => 1,
					'db_table_prefix' => 1,
					'db_slave' => 1,
					'jishigou_founder' => 1,
					'setting_from_db' => 1,
				);
								foreach($keeps as $k=>$v) {
					if($v && isset($value[$k]) && $value[$k] != $GLOBALS['_J']['config'][$k]) {
						$value[$k] = $GLOBALS['_J']['config'][$k];
					}
				}
				
				ConfigHandler::db(null, $value);
				$data="<?php \r\n 
				\r\n \$config = ".var_export(ConfigHandler::core_settings($value, array(), 'local'), true)."; \r\n?>";
			}
		} else {
			global $_J;

			$config = ConfigHandler::get($type);
			$path_str = '';
			foreach($func_args as $arg) {
				$arg = str_replace(array(';', '"', "'", ), '', $arg);
				$path_str.="['$arg']";
			}
			eval($value===null?'unset($config'.$path_str.');':'$config'.$path_str.'=$value;');
			if(!is_null($config) && $_J['config'][$type] != $config) {
				$_J['config'][$type] = $config;

								ConfigHandler::db($type, $config);
				$data="<?php \r\n 
				\r\n \$config['{$type}'] = ".var_export($config, true)."; \r\n?>";
			}
		}

		if($data) {
			$len = jio()->WriteFile($file, $data);
			if(false === $len) {
				die($file." 文件无法写入,请检查是否有可写权限。");
			}
		}

		return $len;
	}

		static function backup($type=null) {
		if(null===$type) {
			$config = ConfigHandler::core_settings();
		} else {
			$config = ConfigHandler::get($type);
		}
		if($config) {
			$dir = ROOT_PATH . 'data/backup/setting/';
			if(!is_dir($dir)) {
				jmkdir($dir);
			}

			return jio()->WriteFile(($dir . (null===$type ? 'settings' : $type) . '.php'), '<?php $config'.(null===$type ? '' : "['$type']").' = '.var_export($config,true).'; ?>');
		}
	}

	
	static function update($var, $val=null) {
		if(!$var) {
			return array();
		}

		$arrs = array();
		if(is_array($var)) {
			$arrs = $var;
		} else {
			$arrs[$var] = $val;
		}

		$update = 0;
		$config = ConfigHandler::core_settings();
		if($config) {
			foreach($arrs as $var=>$val) {
				if($config[$var] != $val) {
					$update = 1;
					$config[$var] = $val;
				}
			}
		}
		if($update && $config) {
			ConfigHandler::set($config);
		}

		return $config;
	}
}

class jconf extends ConfigHandler {}



function update_credits_by_action($action,$uid=0,$coef=1,$score=0) {
	return jlogic('credits')->ExecuteRule($action,$uid,$coef,$score);
}


function sina_weibo_init($sys_config=array()) {
	return init_item_func($sys_config, 'sina');
}
function sina_init($sys_config=array()) {
	return sina_weibo_init($sys_config);
}


function qqwb_init($sys_config=array())
{
	return init_item_func($sys_config, 'qqwb');
}


function yy_init($sys_config=array())
{
	return init_item_func($sys_config, 'yy');
}


function renren_init($sys_config=array())
{
	return init_item_func($sys_config, 'renren');
}


function kaixin_init($sys_config=array())
{
	return init_item_func($sys_config, 'kaixin');
}


function imjiqiren_init($sys_config=array())
{
	return init_item_func($sys_config, 'imjiqiren');
}


function sms_init($sys_config=array())
{
	return init_item_func($sys_config, 'sms');
}


function fjau_init($sys_config=array()) {
	return init_item_func($sys_config, 'fjau');
}


function hzswb_init($sys_config=array()) {
	return init_item_func($sys_config, 'hzswb');
}


function init_item_func($sys_config = array(), $item)
{
	$func = "{$item}_enable";
	if(!function_exists($func))
	{
		jfunc($item);

		clearstatcache();

		if(function_exists($func))
		{
			return $func($sys_config);
		}
	}
	else
	{
		return $func($sys_config);
	}

	return false;
}


function js_alert_output($alert_msg, $msg_func='MessageBox') {
	echo "<script language='javascript'>";
	if('alert' == $msg_func) {
		echo "alert('{$alert_msg}');";
	} elseif('show_message' == $msg_func) {
		echo "show_message('{$alert_msg}');";
	} else {
		echo "MessageBox('notice', '{$alert_msg}');";
	}
	echo "</script>";
	exit;
}
function js_alert_showmsg($alert_msg) {
	js_alert_output($alert_msg, 'show_message');
}



function jsg_setcookie($var, $value, $life = 0, $httponly = false) {
	global $_J;

	$prefix = 1;
	$var = ($prefix ? $_J['config']['cookie_prefix'] : '') . $var;
	$_COOKIE[$var] = $value;

	if('' == $value || $life < 0) {
		$value = '';
		$life = -1;
	}

	if(true === IN_JISHIGOU_MOBILE || true === IN_JISHIGOU_WAP) {
		$httponly = false;
	}

	$life = ($life > 0 ? (TIMESTAMP + $life) : ($life < 0 ? (TIMESTAMP - 86400000) : 0));
	$path = (($httponly && PHP_VERSION < '5.2.0') ? $_J['config']['cookie_path'] . '; HttpOnly' : $_J['config']['cookie_path']);
	$domain = ($_J['config']['cookie_domain'] ? $_J['config']['cookie_domain'] : '');
	$secure = ($_SERVER['SERVER_PORT'] == 443 ? 1 : 0);

	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $domain, $secure);
	} else {
		setcookie($var, $value, $life, $path, $domain, $secure, $httponly);
	}
}
function jsg_getcookie($var, $prefix = 1) {
	if($prefix) {
		global $_J;

		$var = $_J['config']['cookie_prefix'] . $var;
	}

	return $_COOKIE[$var];
}

function jsg_schedule($vars=array(), $type='', $uid=0) {
	if(!function_exists('schedule_add')) {
		jfunc('schedule');
	}

	if($vars) {
		return schedule_add($vars, $type, $uid);
	} else {
		return schedule_html();
	}
}

function randgetftp(){
	if(!function_exists('__randgetftp')) {
		jfunc('ftp');
	}
	return __randgetftp();
}
function getftpkey($ftpurl=''){
	if(!function_exists('__getftpkey')) {
		jfunc('ftp');
	}
	return __getftpkey($ftpurl);
}
function getftptype($ftpurl=''){
	if(!function_exists('__getftptype')) {
		jfunc('ftp');
	}
	return __getftptype($ftpurl);
}
function ftpcmd($cmd, $arg1 = '', $arg2 = '',$ftpkey=null) {
	if(!function_exists('__ftpcmd')) {
		jfunc('ftp');
	}
	return __ftpcmd($cmd, $arg1, $arg2,$ftpkey);
}




class DB
{

	
	static function table($table)
	{
		$table_name = TABLE_PREFIX.$table;
		return $table_name;
	}

	static function where($condition, $glue=' AND ') {
		if(empty($condition)) {
			$where = '';
		} elseif(is_array($condition)) {
			$where = ' WHERE ' . DB::field($condition, $glue, 1);
		} else {
			$where = ' ' . (false!==strpos(strtoupper($condition), 'WHERE ') ? $condition : 'WHERE ' . $condition);
		}
		$where .= ' ';
		return $where;
	}

	
	static function delete($table, $condition, $limit = 0, $unbuffered = true)
	{
		$sql = "DELETE FROM ".DB::table($table).DB::where($condition).($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	
	static function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false)
	{
		$sql = DB::field($data);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

		$table = DB::table($table);
		$silent = $silent ? 'SILENT' : '';

		$return = DB::query("$cmd $table SET $sql", $silent);

		return $return_insert_id ? DB::insert_id() : $return;

	}

	
	static function update($table, $data, $condition, $unbuffered = false, $low_priority = false)
	{
		$sql = DB::field($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$table = DB::table($table);
		$res = DB::query("$cmd $table SET $sql ".DB::where($condition), $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	
	static function field($array, $glue = ',', $is_where=0) {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$k = DB::_check_field_key($k);
			$s = '';
			if(is_array($v)) {
				$g = (string) $v['glue'];
				if($g) {
					if($v['key']) {
						$kk = DB::_check_field_key($v['key']);
					} else {
						$kk = $k;
					}
					$kk = ($v['key'] ? $v['key'] : $k);
					$vv = (string) $v['val'];
										switch ($g) {
						case '=':
						case '>':
						case '<':
						case '<>':
						case '>=':
						case '<=':
							$s = "`{$kk}`{$g}'{$vv}'";
							break;
						case '-':
						case '+':
						case '|':
						case '&':
						case '^':
							$s = "`{$kk}`=`{$kk}`{$g}'{$vv}'";
							break;
						case 'like':
							$s = "`{$kk}` LIKE('{$vv}')";
							break;
						case 'in':
						case 'notin':
							$s = "`{$kk}`".('notin'==$g ? ' NOT' : '')." IN(".jimplode($v['val']).")";
							break;
						default:
							exit("glue $g is invalid");
					}
				} else {
					if($is_where) {
						$s = "`{$k}` IN(".jimplode($v).")";
					}
				}
			} else {
				$v = (string) $v;
								$s = "`{$k}`='$v'";
			}

			if($s) {
				$sql .= $comma . $s;
				$comma = $glue;
			}
		}
		return $sql;
	}
	
	static function _check_field_key($k) {
		$k = (string) $k;
		$cks = array('`', ',', '=', '(', ')', '<', '>', );
		foreach($cks as $ck) {
			if(false !== strpos($k, $ck)) {
				echo("DB field key is invalid");
				jlog('db__check_field_key', $k);
			}
		}
		return $k;
	}
	
	static function filter_in_num($val) {
		if(!is_numeric($val)) {
			$tmps = explode(',', $val);
			foreach($tmps as $k=>$v) {
				if(!is_numeric($v)) {
					unset($tmps[$k]);
				}
			}
			$val = implode(',', $tmps);
		}
		return $val;
	}

		static function insert_id()
	{
		return DB::_execute('Insert_ID');
	}

	
	static function fetch($resourceid, $type = 'assoc')
	{
		return DB::_execute('GetRow', $resourceid, $type);
	}

	
	static function fetch_first($sql)
	{
		return DB::_execute('FetchFirst', $sql);
	}

	static function fetch_all($sql, $keyfield='') {
		return DB::_execute('FetchAll', $sql, $keyfield);
	}

	static function result($resourceid, $row = 0)
	{
		return DB::_execute('result', $resourceid, $row);
	}

	static function result_first($sql)
	{
		$query = DB::query($sql);
		return DB::result($query);
	}

	static function query($sql, $type = '')
	{
		return DB::_execute('Query', $sql, $type);
	}

	static function num_rows($resourceid)
	{
		return DB::_execute('GetNumRows', $resourceid);
	}

	static function affected_rows()
	{
		return DB::_execute('AffectedRows');
	}

	static function free_result($query)
	{
		return DB::_execute('FreeResult', $query);
	}

	static function error()
	{
		return DB::_execute('GetLastErrorString');
	}

	static function errno() {
		return DB::_execute('GetLastErrorNo');
	}

	static function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db=null;
		if(empty($db)) {
			$db = & DB::object();
		}
		if ($cmd == 'GetRow') {
			if(is_object($arg1)) {
				$res = $arg1->GetRow($arg2);
			}
		} else if ($cmd == 'result') {
			if(is_object($arg1)) {
				$res = $arg1->result($arg2);
			}
		} else if ($cmd == 'GetNumRows') {
			if(is_object($arg1)) {
				$res = $arg1->GetNumRows();
			}
		} else if ($cmd == 'FreeResult') {
			if(is_object($arg1)) {
				$res = $arg1->FreeResult();
			}
		} else {
			if(is_object($db)) {
				$res = $db->$cmd($arg1, $arg2);
			}
		}
		return $res;
	}

	static function &object() {
		static $db=null;
		if(empty($db)) {
			$db = & Obj::registry('DatabaseHandler');
			if (empty($db)) {
								include(ROOT_PATH . 'setting/settings.php');
				$db = jclass('jishigou/mysql');
				$db->do_connect($config['db_host'], $config['db_port'],
					$config['db_user'], $config['db_pass'],
					$config['charset'], $config['db_name'],
					isset($config['db_persist']) ? $config['db_persist'] : $config['db_pconnect']);
				Obj::register('DatabaseHandler', $db);
			}
		}
		return $db;
	}

	
	static function checkquery($sql) {
		return DB::_execute('CheckQuery');
	}
}


function template($tpl_name = null) {
	if(empty($tpl_name)) {
		$tpl_name = (defined('APP_ID') ? constant('APP_ID') :
			($GLOBALS['_J']['config']['jishigou_run_tpl_default'] .
				jget('mod') . '_' .
				(($c = jget('code')) ? $c : 'index')));
	}
	return jclass('jishigou/template')->Template($tpl_name);
}

function unfilterHtmlChars($str)
{
	return str_replace(array('&lt;', '&gt;'), array('<', '>'), $str);
}


function getstr($string, $length, $in_slashes=0, $out_slashes=0,  $html=0)
{
	$string = trim($string);
	if($in_slashes) {
		$string = jstripslashes($string);
	}
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = jhtmlspecialchars($string);
	}

	if($length) {
		$string = cut_str($string, $length);
	}
	filter($string);
	if($out_slashes) {
		$string = addslashes($string);
	}
	return trim($string);
}

function jstrtotime($string)
{
	$time = '';
	if($string) {
		$time = strtotime($string);
		$timezone = $GLOBALS['_J']['config']['timezone'];
		if(gmdate('H:i', TIMESTAMP + $timezone * 3600) != date('H:i', TIMESTAMP)) {
			$time = $time - $timezone * 3600;
		}
	}
	return $time;
}

function url_implode($gets)
{
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode(jstripslashes($value));
		}
	}
	return implode('&', $arr);
}

function jimplode($array)
{
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

function chk_follow($uid, $buddyid) {
	$info = jlogic('buddy')->info($buddyid, $uid);

	return ($info ? 1 : 0);
}


function mk_time_select($type = 'hour', $def_val = false,$name='')
{
	$html = '';
	$time = 0;
	if (defined(TIMESTAMP)) {
		$time = TIMESTAMP;
	} else {
		$time = time();
	}

	if ($type == 'hour') {
		$range = 24;
		if ($def_val === false) {
			$def_val = my_date_format($time, 'H');
		}
	} else if ($type == 'min') {
		$range = 60;
		if ($def_val === false) {
			$def_val = my_date_format($time, 'i');
		}
	} else {
		return '';
	}

	$name = $name ? $name : $type;
	$html = "<select name=\"{$name}\" id=\"{$name}\" defaultvalue=\"{$def_val}\">";
	for ($i=0;$i<$range;++$i) {
		$selected = '';
		$value = $i;
		if (strlen($value) < 2) {
			$value = '0'.$value;
		}
		if ($value == $def_val) {
			$selected = 'selected="selected"';
		}
		$html .= " <option value=\"{$value}\" {$selected} >{$value}</option>";
	}
	$html .= '</select>';
	return $html;
}


function get_buddyids($uid, $uptime_limit=0) {
	$ret = jlogic('buddy')->get_buddyids($uid, $uptime_limit);

	return $ret;
}


function table_exists($table_name) {
	$row = DB::fetch_first("SHOW TABLES LIKE '".DB::table($table_name)."'");
	if (empty($row)) {
		return false;
	}
	return true;
}



function jsg_json_encode($value)
{
	if(!class_exists('servicesJSON')) {
		jext('servicesJSON');
	}
	$json = new servicesJSON(0, false);
	return $json->encode($value);
}

function jsg_json_decode($value)
{
	if(!class_exists('servicesJSON')) {
		jext('servicesJSON');
	}
	$json = new servicesJSON(0, false);
	return $json->decode($value);
}


function json_error ($msg = '', $retval = null, $jqremote = false)
{
	$result = array("done" => false , "msg" => $msg);
	if (isset($retval)) $result["retval"] = $retval;

	json_header();
	$json = jsg_json_encode($result);
	if ($jqremote === false) {
		$jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
	}
	if ($jqremote) {
		$json = $jqremote . '(' . $json . ')';
	}
	echo $json;
	exit;
}

function js_show_login($msg='')
{
	echo "<script language='Javascript'>";
	echo "show_message('{$msg}',1);";
	echo "ShowLoginDialog();";
	echo "</script>";
	exit;
}

function json_result($msg = '', $retval = '', $jqremote = false)
{
	json_header();
	$json = jsg_json_encode(array("done" => true , "msg" => $msg , "retval" => $retval));
	if ($jqremote === false) {
		$jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
	}
	if ($jqremote) {
		$json = $jqremote . '(' . $json . ')';
	}
	echo $json;
	exit;
}


function json_header()
{
	ob_clean();

	@header("Cache-Control: no-cache, must-revalidate");
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}


function ajax_page($count, $perpage, $page, $js_code, $parma = '')
{
	$multi = '';
	$str_parma = '{}';
	if (!empty($parma)) {
		$str_parma = jsg_json_encode($parma);
	}

	if ($count > $perpage) {
		if ($page > 1) {
			$prev = $page - 1;
			$multi .= '<a href=\'javascript:;\' onclick=\''.$js_code.'('.$prev.','.$str_parma.')\'>上一页</a>';
		}

		if ($page * $perpage < $count) {
			$next = $page + 1;
			$multi .= '&nbsp;&nbsp;<a href=\'javascript:;\' onclick=\''.$js_code.'('.$next.','.$str_parma.')\'>下一页</a>';
		}
	}
	return $multi;
}


function image_thumb($source, $target, $thumbwidth, $thumbheight, $thumbtype = 1, $nosuffix = 0, $ignored_animation = 1) {
	jclass('image')->param['ignored_animation'] = $ignored_animation;
	return jclass('image')->Thumb($source, $target, $thumbwidth, $thumbheight, $thumbtype, $nosuffix);
}



if(!function_exists('json_encode'))
{
	function json_encode($value)
	{
		if(!class_exists('servicesJSON'))
		{
			jext('servicesJSON');
		}
		$json = new servicesJSON();
		return $json->encode($value);
	}
}

if(!function_exists('json_decode'))
{
	function json_decode($json_value,$bool = false)
	{
		if(!class_exists('servicesJSON'))
		{
			jext('servicesJSON');
		}
		$assoc = ($bool ? 16 : 32);
		$json = new servicesJSON($assoc);
		return $json->decode($json_value);
	}
}


function topic_type()
{
	$types = array(
		'first',
		'forward',
		'both',
	);
	return $types;
}


function get_topic_type($type = '')
{
	$topic_types = array(
		'first',
		'forward',
		'both',
	);

	$not_visible_topic_types = array(
		'reply',
		'qun',
		'vote',

	);

	if ($type == 'personal') {
		$topic_types[] = 'personal';
	} else if ($type == 'forward') {
		$topic_types[] = 'reply';
		$topic_types[] = 'qun';
		$topic_types[] = 'channel';
	} else if ($type == 'sys_not_visible') {
		$topic_types = $not_visible_topic_types;
	}
	return $topic_types;
}


function get_def_follow_group()
{
	$g = array(
	1 => '同事',
	2 => '好友',
	3 => '特别关注',
	4 => '其他',
	);
	return $g;
}

function mkseccode()
{
	$seccode = random(6, 1);
	$s = sprintf('%04s', base_convert($seccode, 10, 24));
	$seccode = '';
	$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	for($i = 0; $i < 4; $i++) {
		$unit = ord($s{$i});
		$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
	}
	return $seccode;
}

function ckseccode($seccode)
{
	$check = true;
	$c = jsg_getcookie('seccode');
	$cookie_seccode = empty($c)?'':authcode($c, 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		$check = false;
	}
	return $check;
}


$__TMP_OBJ_OF_UPS_CTRL = null;
function upsCtrl()
{
	global $__TMP_OBJ_OF_UPS_CTRL;
	if (is_null($__TMP_OBJ_OF_UPS_CTRL))
	{
		include_once(ROOT_PATH.'include/logic/ups.ctrl.moyo.php');
		$__TMP_OBJ_OF_UPS_CTRL = new xUpdateControlLogic();
	}
	return $__TMP_OBJ_OF_UPS_CTRL;
}

function jmkdir($dir, $mode = 0777, $makeindex = TRUE)
{
	if(!is_dir($dir)) {
		clearstatcache();
		jmkdir(dirname($dir));
		@mkdir($dir, $mode);
		if(!empty($makeindex)) {
			$ret = @touch($dir.'/index.html');
			@chmod($dir.'/index.html', 0777);
			return $ret;
		}
	}
	return true;
}

function process_url($content)
{
	if(false != strpos($content, ':/'.'/'))
	{
		$pattern = '~((?:https?\:\/\/)(?:[A-Za-z0-9\_\-]+\.)+[A-Za-z0-9]{1,4}(?:\:\d{1,6})?(?:\/[\w\d\/=\?%\-\&_\~\`\:\+\#\.]*(?:[^\;\@\[\]\<\>\'\"\n\r\t\s\x7f-\xff])*)?)~i';
		$replacement = '<a target="_blank" href="\\1">\\1</a>';

		$content = preg_replace($pattern, $replacement, $content);
	}

	return $content;
}

function sys_version($v = null) {
	$v = ($v ? $v : SYS_VERSION);

	return $v;

	$srp = strrpos($v, '.');
	if(false !== $srp) {
		$v = substr($v, 0, $srp);
	}

	return $v;
}

function writelog($file, $log) {
	$logdir = ROOT_PATH.'./data/log/';
	$file = dir_safe($file);
	$logfile = $logdir.$file.'.php';
	if(!is_dir($logdir)){
		jmkdir($logdir);
	}
	$log = is_array($log) ? $log : array($log);
	return jio()->WriteFile($logfile, '<?php $log='.var_export($log,'true').'?>');
}

function rewriteDisable() {
	global $jishigou_rewrite;
	$jishigou_rewrite = null;
}

function dir_safe($dir, $safe=1) {
	if($safe) {
		$search1 = array('..', '*', '?', '"', '<', '>', '|',  );
		$dir = str_replace($search1, '', $dir);
		$dir = str_replace($search1, '', $dir);
	}

	if(false !== strpos($dir, '/')) {
		$search2 = array('\\', '/./', '/'.'/'.'/'.'/', '/'.'/'.'/', '/'.'/', );
		$dir = str_replace($search2, '/', $dir);
		$dir = str_replace($search2, '/', $dir);
	}

	return $dir;
}

function str_safe($str) {
	$str = trim(strip_tags($str));
	if($str) {
		return jhtmlspecialchars(trim(str_replace(array('&gt;','<','&lt;','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'), '', $str)));
	}
	return '';
}

function jstrlen($str) {
	global $_J;

	$l = strlen($str);
	if(strtolower($_J['charset']) != 'utf-8') {
		return $l;
	}
	$count = 0;
	for($i = 0; $i < $l; $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
			}
			$count++;
	}
	return $count;
}


function jerror($msg, $code=0) {
	$rets = jreturn($msg, 'error', $code);
	$rets['msg'] = $msg;
	return $rets;
}

function jreturn($result, $status = '', $code = null) {
	$rets = array();
	if($status) {
		$rets['status'] = $status;
		$rets[$status] = true;
	}
	$rets['code'] = (is_null($code) ? '200' : "{$code}");
	$rets['result'] = $result;
	if(('utf-8' != $GLOBALS['_J']['charset']) && (true === IN_JISHIGOU_WAP || true === IN_JISHIGOU_MOBILE)) {
		$rets = array_iconv($GLOBALS['_J']['charset'], 'utf-8', $rets);
	}
	return $rets;
}

function jdefine($name, $value, $case_insensitive = false) {
	if(defined($name) && $value !== constant($name)) {
		exit($name . ' is defined');
	}
	define($name, $value, $case_insensitive);
}


function jhtmlspecialchars($string, $flags = null) {
	if(is_array($string)) {
		foreach($string as $k=>$v) {
			$string[$k] = jhtmlspecialchars($string, $flags);
		}
	} else {
		if(null === $flags) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if(strtolower($GLOBALS['_J']['charset']) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}


function jwidget($item, $position = NULL) {
	if($item && is_string($item)) {
		$wtpls = array();
		
		if($position && is_string($position)) {
			$wtpls = jconf::get('widget', $item, $position);
		}
		
		if(!$wtpls) {
			$wtpls = array($item => $position);
		}
		if(is_array($wtpls) && count($wtpls)) {
			foreach($wtpls as $wtpl => $options) {
				include template('widget/' . (string) $wtpl);
			}
		}
	}
}

#设置广告位
function SetADV($page,$op) {
	if (!$GLOBALS['_J']['config']['ad_enable']) {
		echo '';
		return ;
	}
	$ad_list = $GLOBALS['_J']['config']['ad']['ad_list'][$page][$op];

	if ($op == 'header' || $op == 'footer') {
		$div_class="banner_ad";
		
	} else if ($op == 'middle_right' || 'middle_right_top' == $op || 'middle_right_center' == $op){
		$div_class="sidetop_ad";
	} else if ($op == 'middle_center1') {
		$div_class="middle_ad";
	} else if ('middle_left_top'==$op || 'middle_left'==$op) {
		$div_class="Ir_AD";
	} else if ('middle' == $op) {
		$div_class="banner_ad";
	}
	$adhtml = "";

	if(is_array($ad_list) && count($ad_list)) {
		foreach ($ad_list as $k=>$adv) {
			if($adv['ftime'] && $adv['ftime'] > TIMESTAMP){
				continue;
			}
			if($adv['ttime'] && $adv['ttime'] < TIMESTAMP){
				continue;
			}
			$adhtml .= "<div class='$div_class'>".stripslashes($adv['html'])."</div>";
		}
	}
	
	
	echo $adhtml;
	return '';
}

function add_credits_log($uid = 0, $relatedid = 0, $action = '',$score = 0,$remark = ''){
	if ($uid === 0 || empty($action)) {
		return false;
	}

		$valid_filed = array();
	foreach ($GLOBALS['_J']['config']['credits']['ext'] as $key => $value) {
		if ($value['enable'] == 1) {
			$valid_filed[] = $key;
		}
	}

	$rule = $GLOBALS['_J']['config']['credits_rule'][$action];
	if( empty($rule) ) {
		return false;
	}
	foreach ($valid_filed as $k => $v) {
		$crl_data[$v] = ($score && $rule[$v]) ? $score : $rule[$v];
	}

	$remark = empty($remark) ? $rule['rulename'] : $remark;
	$data = array('uid' => $uid, 'rid'=>$rule['rid'], 'relatedid'=>$relatedid, 'dateline'=>time(), 'remark'=>$remark);
	foreach ($crl_data as $k => $val) {
		$data[$k] = $val;
	}
	return jtable('credits_log')->insert($data, true);
}

function send_mail($to,$subject,$message,$nickname='',$email='',$attachments=array(),$priority=3,$html=true,$smtp_config=array()) {
	if(!function_exists('_send_mail')) {
		jfunc('mail');
	}
	return _send_mail($to,$subject,$message,$nickname,$email,$attachments,$priority,$html,$smtp_config);
}

function message($message, $url_forward = '', $options = array()) {
	if(!function_exists('jmessage')) {
		jfunc('message');
	}
	return jmessage($message, $url_forward, $options);
}

function runhooks($script = 'global') {
	jlogic('plugin')->runhooks($script);
}

function hookscript($script, $type = 'funcs', $param = array(), $func = '') {
	jlogic('plugin')->hookscript($script, $type, $param, $func);
}

function hookscriptoutput() {
	jlogic('plugin')->hookscriptoutput();
}

function postpmsms($uid=0,$tid=0,$msg='您的微博状态已做变更'){
	$uid = (int) $uid;
	if($uid > 0) {
		$info = jsg_member_info($uid);
		if($info) {
			return jlogic('pm')->pmSend(array('message'=>$msg.'【ID='.$tid.'】！请<a href="index.php?mod=topic&code='.$tid.'">单击这里</a>查看','to_user'=>$info['nickname']));
		}
	}
}

function filter_tids($tids = array()){
	return jlogic('topic_list')->filter_tids($tids);
}
function feed_msg($item='channel',$action='post',$tid=0,$msg='',$item_id=0, $anonymous=0){
	return jlogic('feed')->addfeed($item,$action,$tid,$msg,$item_id, $anonymous);
}
function channel_topic_num(){
	return jlogic('channel')->get_channel_topic_num();
}
function ios_push_msg($uids,$msg=''){
	return jlogic('ios')->push_msg($uids,$msg);
}


function nav_url($url, $top_nav_key = '', $side_nav_key = '') {
	if($url && false === strpos($url, ':/' . '/')) {
		if($top_nav_key) {
			$url .= (false !== strpos($url, '?') ? '&' : '?') . 'top_nav=' . $top_nav_key;
		}
		if($side_nav_key) {
			$url .= (false !== strpos($url, '?') ? '&' : '?') . 'side_nav=' . $side_nav_key;
		}
		$url = jurl($url);
	}
	return $url;
}

if(!defined('JISHIGOU_GLOBAL_FUNCTION')) {
	define('JISHIGOU_GLOBAL_FUNCTION', true);

	if(!defined('IN_JISHIGOU')) {
		if(!defined('ROOT_PATH')) {
			define('ROOT_PATH', substr(dirname(__FILE__), 0, -17) . '/');
		}
		require_once ROOT_PATH . 'include/jishigou.php';
		$jishigou = new jishigou();
	}
}

jfunc('member');

#if NEDU
$__nedu_file = ROOT_PATH.'nedu/nedu.load.php';
is_file($__nedu_file) && require_once $__nedu_file;
#endif
?>