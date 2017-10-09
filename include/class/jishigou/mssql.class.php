<?php
/**
 *
 * MSSQL处理
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mssql.class.php 4294 2013-09-02 01:01:10Z wuliyong $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class jishigou_mssql {
	
	public static $_instance;
	
	public $link;
	
	public function __construct($param) {
		if(!$param['db_host'] || !$param['db_user'] || !$param['db_pass'] || !$param['db_name']) {
			jlog('mssql', 'db name or host or user or password is empty');
		}
		
		if($param['db_port']) {
			$pd = (defined('PHP_OS') && substr(PHP_OS, 0, 3) == 'WIN') ? ',' : ':';
			$param['db_host'] .= $pd . $param['db_port'];
		}
		
		if(!function_exists('mssql_connect')) {
			jlog('mssql', 'function mssql_connect is invalid');
		}
		
		@ini_set('mssql.charset', $param['db_charset']);
        @ini_set('mssql.textlimit', 2147483647);
        @ini_set('mssql.textsize', 2147483647);
        
        $this->link = mssql_connect($param['db_host'], $param['db_user'], $param['db_pass']);
        
        if(!$this->link) {
        	jlog('mssql', 'connect is invalid<br />error message: ' . $this->error());
        }
        
		if(!mssql_select_db($param['db_name'], $this->link)) {
        	mssql_close($this->link);
        	jlog('mssql', 'db name select is invalid');
        }
		
        return true;
	}
	
	public function query($sql) {
		if(empty($sql)) {
			return false;
		}
		
		$ret = mssql_query($sql, $this->link);
		
		return $ret;
	}

    
    public function error() {
        return mssql_get_last_message();
    }

    
    public function __destruct() {
        if($this->link == true) {
            @mssql_close($this->link);
        }
    }

    
    public static function get_instance($param) {
        if (!self::$_instance) {
            self::$_instance = new self($param);
        }

        return self::$_instance;
    }    
}