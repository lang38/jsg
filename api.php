<?php
/**
 *
 * API入口
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: api.php 2123 2012-12-20 06:56:08Z wuliyong $
 */


define('IN_JISHIGOU_API', true);

require './include/jishigou.php';
$jishigou = new jishigou();

$jishigou->run('api');

?>