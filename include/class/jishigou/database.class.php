<?php
/**
 * 文件名：database.class.php
 * @version $Id: database.class.php 4212 2013-08-26 07:59:04Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：各种数据库的标准接口类
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class jishigou_database
{
	
	var $ServerHost;
	
	var $ServerPort;

	
	var $SQL_Store;

	
	function jishigou_database($server_host, $server_port)
	{
		$this->ServerHost = $server_host;
		$this->ServerPort = $server_port;
		$this->SQL_Store = array();
	}

	
	function GetQueryCount()
	{
		return sizeof($this->SQL_Store);
	}

	
	function SetSqlStore($sql) {
		$this->SQL_Store[] = $sql;
				if(true === DEBUG) {
			jlog('mysql_query_log', jstripslashes($sql['SQL']), 0);
		}
	}

	
	function Debug()
	{
		
	}
}

?>