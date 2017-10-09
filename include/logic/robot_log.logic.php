<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * 对搜索引擎的操作
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 * @version $Id: robot_log.logic.php 2777 2013-02-01 04:49:56Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

Class RobotLogLogic
{
	var $robotName="";
	var $tableName="";
	var $dateFormat="Y-m-d";
	var $date="";
	var $fieldList=array();
	function RobotLogLogic($robot_name='')
	{		
		$this->tableName=TABLE_PREFIX."robot_log";
		$this->date=date($this->dateFormat);
		if($robot_name) {
			$this->setRobotName($robot_name);
		}
	}
	
	function setRobotName($name)
	{
		$this->robotName=$name;
	}
	
	function statistic()
	{
		if(empty($this->tableName))return false;
		$timestamp=time();
		$sql="UPDATE ".$this->tableName." 
			set 
			`times`=`times`+1,
			`last_visit`=$timestamp
			where `name`='$this->robotName' and `date`='$this->date'";
		DB::query($sql, 'SILENT');
		$result = DB::affected_rows();
		if($result > 0) {
			return true;
		}
				$sql="insert into $this->tableName(`name`,`times`,`date`,`first_visit`,`last_visit`) 
		values('{$this->robotName}','1','$this->date','$timestamp','$timestamp')";
		DB::query($sql, 'SILENT');
		$result = (bool) DB::affected_rows();
		return $result;
	}
	
	
	function getRobotName()
	{
		return $this->robotName;
	}
}
?>