<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename wqueue-daemon.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 526288675 335 $
 */





$_GET['mod'] = 'async';
$_GET['code'] = 'wqueue';
$_GET['do'] = 'run';
$_SERVER['HTTP_USER_AGENT'] = 'JISHIGOU.WQUEUE.DAEMON.AGENT/1.0';
$_SERVER['JSG-PHP-RUN-MODE'] = 'PHP-CLI';

require './include/jishigou.php';
$jishigou = new jishigou();

$jishigou->run('index');

?>