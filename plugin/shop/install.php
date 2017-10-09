<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename install.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1332207896 814 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}
$sql = <<<EOF

DROP TABLE IF EXISTS {jishigou}topic_shop;
CREATE TABLE {jishigou}topic_shop (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`tid` int(10) unsigned NOT NULL DEFAULT '0',
	`uid` int(10) unsigned NOT NULL DEFAULT '0',
	`username` varchar(50) NOT NULL DEFAULT '',
	`goods` varchar(150) NOT NULL DEFAULT '',
	`seller` varchar(50) NOT NULL DEFAULT '',
	`imageid` int(10) unsigned NOT NULL DEFAULT '0',
	`image` varchar(100) NOT NULL DEFAULT '',
	`url` varchar(150) NOT NULL DEFAULT '',
	`surl` varchar(100) NOT NULL DEFAULT '',
	`price` varchar(30) NOT NULL DEFAULT '0.00',
	`dateline` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `tid` (`tid`),
	KEY `uid` (`uid`)
) ENGINE=MyISAM;
EOF;
?>