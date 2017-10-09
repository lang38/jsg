<?php
/**
 *
 * MYSQL处理,连接MYSQL,取得,修改,添加,删除数据
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mysql.class.php 5646 2014-03-20 02:57:47Z wuliyong $
 * @todo 增加SQL安全过滤 2012年2月2日
 * @todo 支持主从数据库的查询更新操作 2012年9月14日
 * @todo 内容整理，清简
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

define("QUERY_SAFE", true);

if(!class_exists('jishigou_database')) {
	Load::file('jishigou/database');
}

class jishigou_mysql extends jishigou_database
{
	var $TableName; 	var $FieldList; 
	
	var $Charset='gbk';

	var $Links = array();
	var $CurLink = '';
	var $slave_id = null;
	var $cur_db = '';

	
	function jishigou_mysql($server_host='', $server_port = '3306') {
		$this->jishigou_database($server_host, $server_port);
	}

		
	function DoConnect($db_user, $db_pass, $db_name, $db_pconnect = true) {
		return $this->do_connect($this->ServerHost, $this->ServerPort, $db_user, $db_pass, $db_charset, $db_name, $db_pconnect);
	}

	function connect() { global $_J; if(1) {
			$server_id = 'db_master';
			$db_host = $_J['config']['db_host'];
			$db_port = $_J['config']['db_port'];
			$db_user = $_J['config']['db_user'];
			$db_pass = $_J['config']['db_pass'];
			$db_charset = $_J['config']['db_charset'];
			$db_name = $_J['config']['db_name'];
			$db_pconnect = isset($_J['config']['db_persist']) ? $_J['config']['db_persist'] : $_J['config']['db_pconnect'];
		}
		if(!$db_charset) {
			$db_charset = $_J['config']['charset'];
		}
		if(!isset($this->Links[$server_id])) {
			$this->Links[$server_id] = $this->do_connect($db_host, $db_port, $db_user, $db_pass, $db_charset, $db_name, $db_pconnect);
		}
		$_J['config']['current_db_name'] = $db_name;
		$this->cur_db = $db_host . '.' . $db_name;
		$this->CurLink = $this->Links[$server_id];
	}

	function do_connect($db_host, $db_port, $db_user, $db_pass, $db_charset, $db_name, $db_pconnect) {
		if(!$db_host || !$db_user || !$db_name) {
			exit('db config is empty');
		}
		if($db_port && false===strpos($db_host, ':')) {
			$db_host .= ':' . $db_port;
		}
		if($db_pconnect) {
			$link = @mysql_pconnect($db_host, $db_user, $db_pass, MYSQL_CLIENT_COMPRESS);
		} else {
			$link = @mysql_connect($db_host, $db_user, $db_pass, 1, MYSQL_CLIENT_COMPRESS);
		}
		if(!$link) {
			exit(mysql_errno() . ':' . mysql_error());
		} else {
			$GLOBALS['_J']['config']['current_db_name'] = $db_name;
			$this->cur_db = $db_host . '.' . $db_name;
			$this->CurLink = $link;
			if($this->GetVersion() > '4.1') {
				$dbcharset = $db_charset ? $this->Charset($db_charset) : $this->Charset;
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->GetVersion() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$db_name && @mysql_select_db($db_name, $link);
		}
		return $link;
	}

	
	function Charset($charset) {
		$this->Charset = ((false!==strpos($charset,'-')) ? str_replace("-", "", strtolower($charset)) : $charset);
		return $this->Charset;
	}

	

	function Query($sql,$type='')
	{
		$this->CheckQuery($sql);

		
		$func=$type==='UNBUFFERED'?'mysql_unbuffered_query':'mysql_query';
		$result = $func($sql, $this->CurLink);
		if($result==false) {
			if(in_array($this->GetLastErrorNo(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->CloseConnection();
				$this->connect();
				$result = $this->Query($sql, 'RETRY'.$type);
			} elseif (in_array($this->GetLastErrorNo(), array(1040)) && substr($type,0,4) != "WAIT" && substr($type,0,5) < "WAIT3") {
				usleep(100000 * max(1,min(6,2 * ((int) substr($type,4,1) + 1))));

				static $WAITTIMES = 0;
				$result = $this->Query($sql, 'WAIT'.++$WAITTIMES.$type);
			} elseif (($type != 'SKIP_ERROR' && 'SILENT' != $type && substr($type, 5) != 'SKIP_ERROR')) {
				if('admin' === MEMBER_ROLE_TYPE || true === JISHIGOU_FOUNDER || true === IN_JISHIGOU_UPGRADE) {
										jlog('mysql_query_error', $this->GetLastError($sql, $file, $line), 0);
				}
				return false;
							} else {
				return false;
			}
		}

		

		return new MySqlIterator($result);
	}
	function CheckQuery($sql) {
		static $checkcmd = array('SEL'=>1, 'UPD'=>1, 'INS'=>1, 'REP'=>1, 'DEL'=>1), $static_query_safes = array();
		$status = 1;
		if($status) {
			$check = 1;
			$cmd = strtoupper(substr(trim($sql), 0, 3));
			if(isset($checkcmd[$cmd])) {
				
				$cache_id = md5($sql);
				if(false==($check = $static_query_safes[$cache_id])) {
					if(isset($GLOBALS['_J']['query_safes'][$cache_id]) && $GLOBALS['_J']['query_safes'][$cache_id] == md5($cache_id . $GLOBALS['_J']['config']['auth_key'])) {
						$check = 1;
					} else {
						$check = $this->_do_query_safe($sql);
					}
					$static_query_safes[$cache_id] = $check;
				}
			} elseif(substr($cmd, 0, 2) === '/' . '*') {
				$check = -1;
			}
			if($check < 1) {
				jlog('mysql_query_check', "[{$check}] {$sql}", true !== DEBUG);
				exit();
			}
		}
		return true;
	}

	
	function _do_query_safe($sql) {
		$_CONF = array();
		$_CONF['status'] = 1;
		$_CONF['dfunc'][] = 'load_file';
		$_CONF['dfunc'][] = 'hex';
		$_CONF['dfunc'][] = 'substring';
		$_CONF['dfunc'][] = 'substr';
		$_CONF['dfunc'][] = 'ord';
		$_CONF['dfunc'][] = 'char';
		$_CONF['dfunc'][] = 'benchmark';
		$_CONF['dact'][] = '@';
		$_CONF['dact'][] = 'intooutfile';
		$_CONF['dact'][] = 'intodumpfile';
		$_CONF['dact'][] = 'unionselect';
		$_CONF['dact'][] = 'unionall';
		$_CONF['dact'][] = 'uniondistinct';
		$_CONF['dnote'][] = '/' . '*';
		$_CONF['dnote'][] = '*' . '/';
		$_CONF['dnote'][] = '#';
		$_CONF['dnote'][] = '--';
		$_CONF['dlikehex'] = 1;
		$_CONF['afullnote'] = 0;

		$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
		$mark = $clean = '';
		if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false) {
			$clean = preg_replace("/'(.+?)'/s", '', $sql);
		} else {
			$len = strlen($sql);
			$mark = $clean = '';
			for ($i = 0; $i < $len; $i++) {
				$str = $sql[$i];
				switch ($str) {
					case '`':
						if(!$mark) {
							$mark = '`';
							$clean .= $str;
						} elseif ($mark == '`') {
							$mark = '';
						}
						break;
					case '\'':
						if (!$mark) {
							$mark = '\'';
							$clean .= $str;
						} elseif ($mark == '\'') {
							$mark = '';
						}
						break;
					case '/':
						if (empty($mark) && $sql[$i + 1] == '*') {
							$mark = '/' . '*';
							$clean .= $mark;
							$i++;
						} elseif ($mark == '/' . '*' && $sql[$i - 1] == '*') {
							$mark = '';
							$clean .= '*';
						}
						break;
					case '#':
						if (empty($mark)) {
							$mark = $str;
							$clean .= $str;
						}
						break;
					case "\n":
						if ($mark == '#' || $mark == '--') {
							$mark = '';
						}
						break;
					case '-':
						if (empty($mark) && substr($sql, $i, 3) == '-- ') {
							$mark = '-- ';
							$clean .= $mark;
						}
						break;

					default:

						break;
				}
				$clean .= $mark ? '' : $str;
			}
		}

		if(strpos($clean, '@') !== false) {
			return '-3';
		}

		$clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

		if ($_CONF['afullnote']) {
			$clean = str_replace('/' . '**' . '/', '', $clean);
		}

		if (is_array($_CONF['dfunc'])) {
			foreach ($_CONF['dfunc'] as $fun) {
				if (strpos($clean, $fun . '(') !== false)
					return '-1';
			}
		}

		if (is_array($_CONF['dact'])) {
			foreach ($_CONF['dact'] as $act) {
				if (strpos($clean, $act) !== false)
					return '-3';
			}
		}

		if ($_CONF['dlikehex'] && strpos($clean, 'like0x')) {
			return '-2';
		}

		if (is_array($_CONF['dnote'])) {
			foreach ($_CONF['dnote'] as $note) {
				if (strpos($clean, $note) !== false)
					return '-4';
			}
		}

		return 1;
	}

	function FetchAll($sql, $keyfield='') {
		$list = false;
		$query = $this->Query($sql);
		if($query) {
			$list = array();
			while (false != ($row = $query->GetRow())) {
				if($keyfield && isset($row[$keyfield])) {
					$list[$row[$keyfield]] = $row;
				} else {
					$list[] = $row;
				}
			}
			$query->FreeResult();
		}
		return $list;
	}

    function fetch_first($sql) {
        return $this->FetchFirst($sql);
    }
    function FetchFirst($sql) {
        $ret = array();
        $query = $this->Query($sql);
        if($query) {
        	$ret = $query->GetRow();
        	$query->FreeResult();
        }
        return $ret;
    }
    function ResultFirst($sql) {
        $ret = '';
        $query = $this->Query($sql);
        if($query) {
            $ret = $query->result(0);
            $query->FreeResult();
        }
        return $ret;
    }

	
	function GetVersion()
	{
		return mysql_get_server_info($this->CurLink);
	}

	
	function GetLastError($sql, $file = '', $line = 0)
	{
		$error = mysql_error($this->CurLink);

		return ($this->GetLastErrorNo() . " | $error | $sql | $file | $line");
	}
	function GetLastErrorString()
	{
		return mysql_error($this->CurLink);
	}
	function GetLastErrorNo()
	{
		return mysql_errno($this->CurLink);
	}

	
	function Insert_ID()
	{
		return ($id = mysql_insert_id($this->CurLink)) >= 0 ? $id : $this->ResultFirst("SELECT last_insert_id()");
	}

	function LastInsertId()
	{
		return $this->Insert_ID();
	}

	
	function AffectedRows()
	{
		return mysql_affected_rows($this->CurLink);
	}

	
	function CloseConnection() {
		$ret = mysql_close($this->CurLink);
		if($this->Links) {
			foreach($this->Links as $link) {
				$ret = mysql_close($link);
			}
		}
		$this->CurLink = null;
		$this->Links = array();
		return $ret;
	}

}



class MySqlIterator
{
	
	var $_resource_id;

	
	var $_current_row;

	
	var $_total_rows;

	
	function MySqlIterator($resource_id)
	{
		$this->_resource_id = $resource_id;
		$this->_total_rows = 0;
		$this->_current_row = 0;
	}

	
	function GetNumRows()
	{
		$this->_total_rows = mysql_num_rows($this->GetResourceId());

		return $this->_total_rows;
	}

	function GetNumFields()
	{
		return mysql_num_fields($this->GetResourceId());
	}

	
	function GetResourceId()
	{
		return $this->_resource_id;
	}

	
	function GetCurrentRow()
	{
		return $this->_current_row;
	}

	
	function isSuccess()
	{
		return $this->GetResourceId() ? true : false;
	}

	
	function FreeResult() {
		return mysql_free_result($this->GetResourceId());
	}

	
	function GetRow($result_type = 'assoc') {
		$this->_current_row++;

		switch($result_type) {
			case 'assoc':
				return mysql_fetch_assoc($this->GetResourceId());
				break;
			case 'row':
				return mysql_fetch_row($this->GetResourceId());
				break;
			case 'both':
				return mysql_fetch_array($this->GetResourceId());
				break;
			case 'object':
				return mysql_fetch_object($this->GetResourceId());
				break;
		}
	}
	function result($row)
	{
		return @mysql_result($this->GetResourceId(),$row);
	}

	
	function GetAll($result_type = 'assoc') {
		$list = array();
		while(false != ($row = $this->GetRow($result_type))) {
			$list[] = $row;
		}
		$this->FreeResult();
		return $list;
	}
}

?>