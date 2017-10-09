<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename passport_client.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1207357215 5811 $
 */




require('./include/jishigou.php');
$jishigou = new jishigou();

global $_J;
if(!$_J['config']['phpwind_enable']) {
	exit('phpwind is invalid');
}

$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
$userdb = isset($_POST['userdb']) ? $_POST['userdb'] : $_GET['userdb'];
$forward = isset($_POST['forward']) ? $_POST['forward'] : $_GET['forward'];
$verify = isset($_POST['verify']) ? $_POST['verify'] : $_GET['verify'];
$forward = str_replace('&#61;', '=', $forward);
$config['phpwind'] = jconf::get('phpwind');
$key = $config['phpwind']['pw_pptkey'];
if(md5($action.$userdb.urldecode($forward).$key) == $verify){
	$config = jconf::get();
	$db_charset = strtolower(str_replace('-','',$config['charset']));
	if(!defined('JSG_DB_CHARSET')) define("JSG_DB_CHARSET",$db_charset);
	$db_prefix = $config['db_table_prefix'];
	if(!defined('JSG_DB_PRE')) define("JSG_DB_PRE",$db_prefix);
	parse_str(StrCode($userdb,$key,'DECODE'),$userdb);
	if($action=='login'){
		$userdb = escapeChar($userdb);
		if(is_array($userdb) && $userdb['username'] && $userdb['password']){
			synlogin($userdb['username'], $userdb['password']);
		}
	}
	if($action=='quit'){
		synlogout();
	}
}
header('Location: '.$forward);exit;
function escapeChar($mixed, $isint = false, $istrim = false) {
	if (is_array($mixed)) {
		foreach ($mixed as $key => $value) {
			$mixed[$key] = escapeChar($value, $isint, $istrim);
		}
	} elseif ($isint) {
		$mixed = (int) $mixed;
	} elseif (!is_numeric($mixed) && ($istrim ? $mixed = trim($mixed) : $mixed) && $mixed) {
		$mixed = escapeStr($mixed);
	}
	return $mixed;
}
function escapeStr($string) {
	$string = str_replace(array("\0","%00","\r"), '', $string);
	$string = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $string);
	$string = str_replace(array("%3C",'<'), '&lt;', $string);
	$string = str_replace(array("%3E",'>'), '&gt;', $string);
	$string = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $string);
	return $string;
}
function StrCode($string, $key, $action = 'ENCODE') {
	$action != 'ENCODE' && $string = base64_decode($string);
	$code = '';
	$key = substr(md5($_SERVER['HTTP_USER_AGENT'].$key), 8, 18);
	$keyLen = strlen($key);
	$strLen = strlen($string);
	for ($i = 0; $i < $strLen; $i++) {
		$k = $i % $keyLen;
		$code .= $string[$i] ^ $key[$k];
	}
	return ($action != 'DECODE' ? base64_encode($code) : $code);
}
function synlogout() {
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	jsg_setcookie('sid', '', -311040000);
	jsg_setcookie('auth', '', -311040000);
}
function synlogin($username, $password) {
	@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	$config = jconf::get();
	global $db;
	$db = new jsg_db;
	$db->connect($config['db_host'], $config['db_user'],$config['db_pass'],$config['db_name']);
	$query = $db->query("SELECT `uid`, `password` FROM `".JSG_DB_PRE."members` WHERE `nickname`='$username' AND `password`='$password'");
	$UserFields = $db->fetch_array($query);
	if($UserFields)
	{
		$auth = authcode("{$UserFields['password']}\t{$UserFields['uid']}","ENCODE",'',1209600);
        jsg_setcookie('sid', '', -311040000);
        jsg_setcookie('auth',$auth,311040000);
	}
}
class jsg_db {
	var $querynum = 0;
	var $link;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE) {
		if($pconnect) {
			if(!$this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = @mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				$halt && $this->halt('Can not connect to MySQL server');
			}
		}
		if($this->version() > '4.1') {
			@mysql_query("SET character_set_connection=".JSG_DB_CHARSET.", character_set_results=".JSG_DB_CHARSET.", character_set_client=binary", $this->link);
			if($this->version() > '5.0.1') {
				@mysql_query("SET sql_mode=''", $this->link);
			}
		}
		if($dbname) {
			@mysql_select_db($dbname, $this->link);
		}
	}
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}
	function query($sql, $type = '') {
		global $debug, $starttime, $sqldebug, $sqlspenttimes;
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ?
			'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();
				$config = array();
				require(ROOT_PATH . 'setting/settings.php');
				$this->connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_persist']);
				$this->query($sql, 'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				$this->halt('MySQL Query Error', $sql);
			}
		}
		$this->querynum++;
		return $query;
	}
	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}
	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}
	function version() {
		return mysql_get_server_info($this->link);
	}
	function close() {
		return mysql_close($this->link);
	}
	function halt($msg = '', $sql = '') {
		echo('<br>JishiGou Login : <br>'.$msg."<br>".$sql.'<br><hr><br>');
	}
}
?>