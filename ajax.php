<?php
/**
 *
 * AJAX入口
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: ajax.php 2478 2013-01-12 03:09:01Z wuliyong $
 */


define('IN_JISHIGOU_AJAX', true);

require './include/jishigou.php';
$jishigou = new jishigou();

$jishigou->run('ajax');

?>