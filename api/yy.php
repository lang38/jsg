<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * YY 应用程序开发 API BY JishiGou
 *
 * 此文件为 api/yy.php ，处理 YY 通知给JishiGou的任务
 *
 * @author 狐狸<foxis@qq.com>
 *
 * @version $Id: yy.php 3622 2013-05-15 08:22:35Z wuliyong $
 */

error_reporting(E_ERROR);

if(empty($_POST) && empty($_GET))
{
	exit('invalid request');
}


$_GET['mod'] = $_POST['mod'] = 'yy';


include('../index.php');


?>