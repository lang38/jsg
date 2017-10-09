DROP TABLE IF EXISTS `jishigou_api_oauth2_code`;
CREATE TABLE `jishigou_api_oauth2_code` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`client_id` varchar(32) NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`code` varchar(40) NOT NULL,
`redirect_uri` varchar(200) NOT NULL,
`expires` int(11) NOT NULL,
`scope` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `code` (`code`),
KEY `expires` (`expires`),
KEY `uid-client_id` (`uid`,`client_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_api_oauth2_token`;
CREATE TABLE `jishigou_api_oauth2_token` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`client_id` varchar(32) NOT NULL,
`uid` mediumint(8) NOT NULL,
`access_token` varchar(40) NOT NULL,
`refresh_token` varchar(40) NOT NULL,
`expires` int(11) NOT NULL,
`scope` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `access_token` (`access_token`),
KEY `expires` (`expires`),
KEY `uid-client_id` (`uid`,`client_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_app`;
CREATE TABLE `jishigou_app` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`app_name` char(100) NOT NULL DEFAULT '',
`source_url` char(255) NOT NULL DEFAULT '',
`show_from` tinyint(1) NOT NULL DEFAULT '0',
`app_desc` text NOT NULL,
`app_key` char(32) NOT NULL,
`app_secret` char(32) NOT NULL DEFAULT '',
`allows_ip` text NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0',
`request_times` bigint(20) unsigned NOT NULL DEFAULT '0',
`request_times_day` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_last_day` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_week` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_last_week` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_month` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_last_month` int(10) unsigned NOT NULL DEFAULT '0',
`request_times_year` bigint(20) unsigned NOT NULL DEFAULT '0',
`request_times_last_year` bigint(20) unsigned NOT NULL DEFAULT '0',
`last_request_time` int(10) unsigned NOT NULL DEFAULT '0',
`redirect_uri` char(255) NOT NULL,
`create_time` int(11) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `app_key_secret` (`app_key`,`app_secret`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_blacklist`;
CREATE TABLE `jishigou_blacklist` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`touid` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid_touid` (`uid`,`touid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans`;
CREATE TABLE `jishigou_buddy_fans` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_1`;
CREATE TABLE `jishigou_buddy_fans_1` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_10`;
CREATE TABLE `jishigou_buddy_fans_10` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_2`;
CREATE TABLE `jishigou_buddy_fans_2` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_3`;
CREATE TABLE `jishigou_buddy_fans_3` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_4`;
CREATE TABLE `jishigou_buddy_fans_4` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_5`;
CREATE TABLE `jishigou_buddy_fans_5` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_6`;
CREATE TABLE `jishigou_buddy_fans_6` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_7`;
CREATE TABLE `jishigou_buddy_fans_7` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_8`;
CREATE TABLE `jishigou_buddy_fans_8` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_9`;
CREATE TABLE `jishigou_buddy_fans_9` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('2','3') NOT NULL DEFAULT '2',
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_fans_table_id`;
CREATE TABLE `jishigou_buddy_fans_table_id` (
`uid` mediumint(8) unsigned NOT NULL,
`table_id` smallint(4) unsigned NOT NULL,
PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow`;
CREATE TABLE `jishigou_buddy_follow` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_1`;
CREATE TABLE `jishigou_buddy_follow_1` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_10`;
CREATE TABLE `jishigou_buddy_follow_10` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_2`;
CREATE TABLE `jishigou_buddy_follow_2` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_3`;
CREATE TABLE `jishigou_buddy_follow_3` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_4`;
CREATE TABLE `jishigou_buddy_follow_4` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_5`;
CREATE TABLE `jishigou_buddy_follow_5` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_6`;
CREATE TABLE `jishigou_buddy_follow_6` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_7`;
CREATE TABLE `jishigou_buddy_follow_7` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_8`;
CREATE TABLE `jishigou_buddy_follow_8` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_9`;
CREATE TABLE `jishigou_buddy_follow_9` (
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`relation` enum('1','3') NOT NULL DEFAULT '1',
`remark` char(255) NOT NULL,
`gids` char(255) NOT NULL,
PRIMARY KEY (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group`;
CREATE TABLE `jishigou_buddy_follow_group` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`name` char(100) NOT NULL,
`remark` char(255) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`count` int(10) unsigned NOT NULL,
`order` int(10) NOT NULL DEFAULT '99',
`mode` enum('private','friend','public') NOT NULL DEFAULT 'private',
PRIMARY KEY (`id`),
UNIQUE KEY `uid-name` (`uid`,`name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation`;
CREATE TABLE `jishigou_buddy_follow_group_relation` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_1`;
CREATE TABLE `jishigou_buddy_follow_group_relation_1` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_10`;
CREATE TABLE `jishigou_buddy_follow_group_relation_10` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_2`;
CREATE TABLE `jishigou_buddy_follow_group_relation_2` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_3`;
CREATE TABLE `jishigou_buddy_follow_group_relation_3` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_4`;
CREATE TABLE `jishigou_buddy_follow_group_relation_4` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_5`;
CREATE TABLE `jishigou_buddy_follow_group_relation_5` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_6`;
CREATE TABLE `jishigou_buddy_follow_group_relation_6` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_7`;
CREATE TABLE `jishigou_buddy_follow_group_relation_7` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_8`;
CREATE TABLE `jishigou_buddy_follow_group_relation_8` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_group_relation_9`;
CREATE TABLE `jishigou_buddy_follow_group_relation_9` (
`gid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
KEY `gid-uid` (`gid`,`uid`),
KEY `uid-touid` (`uid`,`touid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_follow_table_id`;
CREATE TABLE `jishigou_buddy_follow_table_id` (
`uid` mediumint(8) unsigned NOT NULL,
`table_id` smallint(4) unsigned NOT NULL,
PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddys`;
CREATE TABLE `jishigou_buddys` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`buddyid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`grade` tinyint(1) unsigned NOT NULL DEFAULT '1',
`remark` char(30) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`description` char(255) NOT NULL DEFAULT '',
`buddy_lastuptime` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `uid_buddyid` (`uid`,`buddyid`),
KEY `buddyid` (`buddyid`),
KEY `dateline` (`dateline`),
KEY `buddy_lastuptime` (`buddy_lastuptime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache`;
CREATE TABLE `jishigou_cache` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_1`;
CREATE TABLE `jishigou_cache_1` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_2`;
CREATE TABLE `jishigou_cache_2` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_3`;
CREATE TABLE `jishigou_cache_3` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_4`;
CREATE TABLE `jishigou_cache_4` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_5`;
CREATE TABLE `jishigou_cache_5` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_6`;
CREATE TABLE `jishigou_cache_6` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_7`;
CREATE TABLE `jishigou_cache_7` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_8`;
CREATE TABLE `jishigou_cache_8` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_9`;
CREATE TABLE `jishigou_cache_9` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_10`;
CREATE TABLE `jishigou_cache_10` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_11`;
CREATE TABLE `jishigou_cache_11` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_12`;
CREATE TABLE `jishigou_cache_12` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_13`;
CREATE TABLE `jishigou_cache_13` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_14`;
CREATE TABLE `jishigou_cache_14` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cache_15`;
CREATE TABLE `jishigou_cache_15` (
`key` char(255) NOT NULL,
`val` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_common_district`;
CREATE TABLE `jishigou_common_district` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`name` char(255) NOT NULL,
`level` tinyint(4) unsigned NOT NULL DEFAULT '0',
`upid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`list` smallint(6) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `name` (`name`),
KEY `upid` (`upid`),
KEY `list` (`list`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_credits_log`;
CREATE TABLE `jishigou_credits_log` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`rid` tinyint(2) NOT NULL,
`relatedid` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`remark` varchar(255) NOT NULL DEFAULT '',
`extcredits1` int(10) NOT NULL DEFAULT '0',
`extcredits2` int(10) NOT NULL DEFAULT '0',
`extcredits3` int(10) NOT NULL DEFAULT '0',
`extcredits4` int(10) NOT NULL DEFAULT '0',
`extcredits5` int(10) NOT NULL DEFAULT '0',
`extcredits6` int(10) NOT NULL DEFAULT '0',
`extcredits7` int(10) NOT NULL DEFAULT '0',
`extcredits8` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `operation` (`rid`),
KEY `relatedid` (`relatedid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_credits_rule`;
CREATE TABLE `jishigou_credits_rule` (
`rid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`rulename` varchar(20) NOT NULL DEFAULT '',
`action` varchar(20) NOT NULL DEFAULT '',
`cycletype` tinyint(1) NOT NULL DEFAULT '0',
`cycletime` int(10) NOT NULL DEFAULT '0',
`rewardnum` tinyint(2) NOT NULL DEFAULT '1',
`norepeat` tinyint(1) NOT NULL DEFAULT '0',
`extcredits1` int(10) NOT NULL DEFAULT '0',
`extcredits2` int(10) NOT NULL DEFAULT '0',
`extcredits3` int(10) NOT NULL DEFAULT '0',
`extcredits4` int(10) NOT NULL DEFAULT '0',
`extcredits5` int(10) NOT NULL DEFAULT '0',
`extcredits6` int(10) NOT NULL DEFAULT '0',
`extcredits7` int(10) NOT NULL DEFAULT '0',
`extcredits8` int(10) NOT NULL DEFAULT '0',
`related` char(20) NOT NULL DEFAULT '',
PRIMARY KEY (`rid`),
UNIQUE KEY `action` (`action`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_credits_rule_log`;
CREATE TABLE `jishigou_credits_rule_log` (
`clid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`rid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`total` mediumint(8) unsigned NOT NULL DEFAULT '0',
`cyclenum` mediumint(8) unsigned NOT NULL DEFAULT '0',
`extcredits1` int(10) NOT NULL DEFAULT '0',
`extcredits2` int(10) NOT NULL DEFAULT '0',
`extcredits3` int(10) NOT NULL DEFAULT '0',
`extcredits4` int(10) NOT NULL DEFAULT '0',
`extcredits5` int(10) NOT NULL DEFAULT '0',
`extcredits6` int(10) NOT NULL DEFAULT '0',
`extcredits7` int(10) NOT NULL DEFAULT '0',
`extcredits8` int(10) NOT NULL DEFAULT '0',
`starttime` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`relatedid` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`clid`),
KEY `uid` (`uid`,`rid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cron`;
CREATE TABLE `jishigou_cron` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`touid` int(11) NOT NULL DEFAULT '0',
`toemail` char(40) NOT NULL DEFAULT '',
`at_content` text NOT NULL,
`pm_content` text NOT NULL,
`reply_content` text NOT NULL,
`sendtime` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_event`;
CREATE TABLE `jishigou_event` (
`id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,
`type_id` mediumint(10) NOT NULL,
`title` char(100) NOT NULL,
`fromt` int(10) NOT NULL,
`tot` int(10) NOT NULL,
`content` text NOT NULL,
`image` char(255) NOT NULL,
`province_id` mediumint(7) NOT NULL,
`area_id` mediumint(7) NOT NULL,
`city_id` mediumint(7) NOT NULL,
`address` char(255) NOT NULL,
`money` int(10) NOT NULL DEFAULT '0',
`app_num` int(10) NOT NULL DEFAULT '0',
`play_num` int(10) NOT NULL DEFAULT '0',
`postman` int(7) NOT NULL,
`posttime` int(10) NOT NULL,
`lasttime` int(10) NOT NULL,
`qualification` text NOT NULL,
`need_app_info` text NOT NULL,
`recd` tinyint(1) NOT NULL DEFAULT '0',
`verify` tinyint(1) NOT NULL DEFAULT '1',
`postip` char(15) NOT NULL DEFAULT '',
`item` char(15) NOT NULL DEFAULT '',
`item_id` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_event_favorite`;
CREATE TABLE `jishigou_event_favorite` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`type_id` mediumint(10) NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `type_id` (`type_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_event_member`;
CREATE TABLE `jishigou_event_member` (
`oid` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,
`id` mediumint(10) NOT NULL,
`title` char(100) NOT NULL,
`fid` mediumint(8) NOT NULL,
`app` mediumint(1) NOT NULL DEFAULT '0',
`play` mediumint(1) NOT NULL DEFAULT '0',
`app_info` text NOT NULL,
`store` mediumint(1) NOT NULL DEFAULT '0',
`app_time` int(10) NOT NULL,
`play_time` int(10) NOT NULL,
`store_time` int(10) NOT NULL,
PRIMARY KEY (`oid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_event_sort`;
CREATE TABLE `jishigou_event_sort` (
`id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,
`type` char(50) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_failedlogins`;
CREATE TABLE `jishigou_failedlogins` (
`ip` char(15) NOT NULL DEFAULT '',
`count` int(10) unsigned NOT NULL DEFAULT '0',
`lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`ip`),
KEY `count` (`count`),
KEY `lastupdate` (`lastupdate`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_force_out`;
CREATE TABLE `jishigou_force_out` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`role_id` smallint(4) unsigned NOT NULL,
`douid` int(10) unsigned NOT NULL DEFAULT '0',
`cause` char(100) NOT NULL DEFAULT '',
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_group`;
CREATE TABLE `jishigou_group` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`group_name` char(15) NOT NULL DEFAULT '',
`group_count` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_groupfields`;
CREATE TABLE `jishigou_groupfields` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`gid` int(11) NOT NULL DEFAULT '0',
`uid` mediumint(8) NOT NULL DEFAULT '0',
`touid` int(11) NOT NULL DEFAULT '0',
`g_name` char(15) NOT NULL DEFAULT '',
`display` tinyint(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `gid_uid` (`gid`,`uid`),
KEY `uid_touid` (`uid`,`touid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_invite`;
CREATE TABLE `jishigou_invite` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`code` char(16) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`fuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`fusername` char(15) NOT NULL DEFAULT '',
`femail` char(50) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `uidcode` (`uid`,`code`),
KEY `femail` (`femail`),
KEY `fuid` (`fuid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_item_sms`;
CREATE TABLE `jishigou_item_sms` (
`sid` int(11) NOT NULL AUTO_INCREMENT,
`item` varchar(10) NOT NULL,
`itemid` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`sid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_item_user`;
CREATE TABLE `jishigou_item_user` (
`iid` int(11) NOT NULL AUTO_INCREMENT,
`item` varchar(10) NOT NULL,
`itemid` int(10) unsigned NOT NULL DEFAULT '0',
`type` varchar(10) NOT NULL DEFAULT '',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`description` varchar(254) NOT NULL,
PRIMARY KEY (`iid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_kaixin_bind_info`;
CREATE TABLE `jishigou_kaixin_bind_info` (
`uid` mediumint(8) unsigned NOT NULL,
`kaixin_uid` char(32) NOT NULL default '',
`kaixin_name` char(32) NOT NULL default '',
`kaixin_gender` char(64) NOT NULL default '',
`kaixin_logo50` char(32) NOT NULL default '',
`token` char(128) NOT NULL default '',
`token_time` int(10) NOT NULL,
`token_expire` int(10) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY  (`uid`),
UNIQUE KEY `renren_uid` (`kaixin_uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_kaixin_bind_topic`;
CREATE TABLE `jishigou_kaixin_bind_topic` (
`tid` int(10) unsigned NOT NULL,
`kaixin_id` bigint(20) unsigned NOT NULL,
KEY `tid` (`tid`),
KEY `kaixin_id` (`kaixin_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_live`;
CREATE TABLE `jishigou_live` (
`lid` int(11) NOT NULL AUTO_INCREMENT,
`livename` varchar(60) NOT NULL,
`description` text NOT NULL,
`starttime` int(10) unsigned NOT NULL DEFAULT '0',
`endtime` int(10) unsigned NOT NULL DEFAULT '0',
`image` varchar(100) NOT NULL,
PRIMARY KEY (`lid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_log`;
CREATE TABLE `jishigou_log` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL,
`nickname` char(30) NOT NULL,
`role_action_id` int(10) unsigned NOT NULL DEFAULT '0',
`role_action_name` char(15) NOT NULL,
`mod` char(50) NOT NULL,
`code` char(255) NOT NULL DEFAULT '',
`ip` char(15) NOT NULL DEFAULT '',
`ip_port` CHAR(6) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`request_method` enum('POST','GET') NOT NULL DEFAULT 'GET',
`data_length` int(10) unsigned NOT NULL,
`uri` char(255) NOT NULL,
PRIMARY KEY (`id`),
KEY `role_action_id` (`role_action_id`),
KEY `mod_code` (`mod`,`code`),
KEY `uid` (`uid`),
KEY `ip` (`ip`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_log_data`;
CREATE TABLE `jishigou_log_data` (
`log_id` int(10) NOT NULL,
`user_agent` char(255) NOT NULL,
`log_data` longblob NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`log_id`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_manage_detail`;
CREATE TABLE `jishigou_manage_detail` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`type` char(8) NOT NULL DEFAULT '',
`tuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tusername` char(15) NOT NULL DEFAULT '',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`postip` char(15) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_medal`;
CREATE TABLE `jishigou_medal` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`medal_img` char(255) NOT NULL,
`medal_img2` char(200) NOT NULL,
`medal_name` char(30) NOT NULL,
`medal_depict` char(50) NOT NULL,
`medal_count` int(11) NOT NULL DEFAULT '0',
`is_open` tinyint(4) NOT NULL DEFAULT '1',
`conditions` varchar(250) NOT NULL,
`dateline` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_medal_apply`;
CREATE TABLE `jishigou_medal_apply` (
`apply_id` int(10) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`nickname` char(30) NOT NULL DEFAULT '',
`medal_id` smallint(6) NOT NULL DEFAULT '0',
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`apply_id`),
KEY `uid` (`uid`),
KEY `medal_id` (`medal_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_media`;
CREATE TABLE `jishigou_media` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`media_name` char(20) NOT NULL DEFAULT '',
`media_count` smallint(6) NOT NULL DEFAULT '0',
`order` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_member_relation`;
CREATE TABLE `jishigou_member_relation` (
`touid` mediumint(8) unsigned NOT NULL,
`totid` int(11) unsigned NOT NULL,
`tid` int(11) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`type` enum('reply','forward','both') NOT NULL DEFAULT 'reply',
PRIMARY KEY (`touid`,`tid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_member_table_id`;
CREATE TABLE `jishigou_member_table_id` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_member_topic`;
CREATE TABLE `jishigou_member_topic` (
`uid` mediumint(8) unsigned NOT NULL,
`tid` int(11) unsigned NOT NULL,
`type` enum('first','reply','forward','both') NOT NULL DEFAULT 'first',
`totid` int(11) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`replys` int(10) unsigned NOT NULL,
`forwards` int(10) unsigned NOT NULL,
`lastupdate` int(10) unsigned NOT NULL,
`digcounts` int(10) unsigned NOT NULL,
`lastdigtime` int(10) unsigned NOT NULL,
PRIMARY KEY (`uid`,`tid`),
UNIQUE KEY `tid` (`tid`),
KEY `dateline` (`dateline`),
KEY `replys` (`replys`),
KEY `forwards` (`forwards`),
KEY `lastupdate` (`lastupdate`),
KEY `digcounts` (`digcounts`),
KEY `lastdigtime` (`lastdigtime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_member_validate`;
CREATE TABLE `jishigou_member_validate` (
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`email` char(50) NOT NULL DEFAULT '',
`role_id` smallint(4) unsigned NOT NULL DEFAULT '0',
`key` char(16) NOT NULL DEFAULT '',
`status` tinyint(1) unsigned NOT NULL DEFAULT '0',
`verify_time` int(10) unsigned NOT NULL DEFAULT '0',
`regdate` int(10) unsigned NOT NULL DEFAULT '0',
`type` enum('email','admin') NOT NULL DEFAULT 'email',
UNIQUE KEY `key` (`key`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_mailqueue`;
CREATE TABLE `jishigou_mailqueue` (
`qid` int(10) NOT NULL auto_increment,
`uid` mediumint(8) unsigned NOT NULL default '0',
`email` char(50) NOT NULL default '',
`msg` text NOT NULL,
`dateline` int(10) NOT NULL default '0',
PRIMARY KEY  (`qid`),
UNIQUE KEY `uid` (`uid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_memberfields`;
CREATE TABLE `jishigou_memberfields` (
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`site` varchar(75) NOT NULL DEFAULT '',
`location` varchar(30) NOT NULL DEFAULT '',
`authstr` varchar(50) NOT NULL DEFAULT '',
`auth_try_times` TINYINT(1) UNSIGNED NOT NULL,
`question` varchar(255) NOT NULL DEFAULT '',
`answer` varchar(255) NOT NULL DEFAULT '',
`address` varchar(40) NOT NULL DEFAULT '',
`validate_true_name` varchar(50) NOT NULL DEFAULT '',
`validate_card_type` varchar(10) NOT NULL DEFAULT '',
`validate_card_id` varchar(50) NOT NULL DEFAULT '',
`validate_remark` varchar(100) NOT NULL DEFAULT '',
`validate_card_pic` char(100) NOT NULL,
`validate_extra` char(200) NOT NULL,
`account_bind_info` text NOT NULL,
`profile_set` text NOT NULL,
PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_members_profile`;
CREATE TABLE `jishigou_members_profile` (
`uid` mediumint(8) unsigned NOT NULL default '0',
`constellation` char(3) NOT NULL default '',
`zodiac` char(1) NOT NULL default '',
`telephone` char(50) NOT NULL default '',
`address` char(150) NOT NULL default '',
`zipcode` char(15) NOT NULL default '',
`nationality` char(50) NOT NULL default '',
`education` char(15) NOT NULL default '',
`birthcity` char(100) NOT NULL default '',
`graduateschool` char(50) NOT NULL default '',
`pcompany` char(50) NOT NULL default '',
`occupation` char(50) NOT NULL default '',
`position` char(50) NOT NULL default '',
`revenue` char(50) NOT NULL default '',
`affectivestatus` char(50) NOT NULL default '',
`lookingfor` char(50) NOT NULL default '',
`bloodtype` char(10) NOT NULL default '',
`height` char(50) NOT NULL default '',
`weight` char(50) NOT NULL default '',
`alipay` char(50) NOT NULL default '',
`icq` char(50) NOT NULL default '',
`yahoo` char(50) NOT NULL default '',
`taobao` char(50) NOT NULL default '',
`site` char(150) NOT NULL default '',
`interest` char(255) NOT NULL default '',
`linkaddress` char(50) NOT NULL default '',
`field1` char(255) NOT NULL default '',
`field2` char(255) NOT NULL default '',
`field3` char(255) NOT NULL default '',
`field4` char(255) NOT NULL default '',
`field5` char(255) NOT NULL default '',
`field6` char(255) NOT NULL default '',
`field7` char(255) NOT NULL default '',
`field8` char(255) NOT NULL default '',
`last_update` INT(10) UNSIGNED NOT NULL default 0,
`realname` char(50) DEFAULT '',
`mobile` char(50) DEFAULT '0',
PRIMARY KEY  (`uid`),
KEY `last_update` (`last_update`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_common_member_profile_setting`;
CREATE TABLE `jishigou_common_member_profile_setting` (
`fieldid` varchar(255) NOT NULL default '',
`title` varchar(255) NOT NULL default '',
`displayorder` smallint(6) unsigned NOT NULL default '0',
`formtype` varchar(255) NOT NULL,
`size` smallint(6) unsigned NOT NULL default '0',
`choices` text NOT NULL,
PRIMARY KEY  (`fieldid`),
KEY `order` (`displayorder`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_members`;
CREATE TABLE `jishigou_members` (
`uid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`username` char(15) NOT NULL DEFAULT '',
`nickname` char(50) NOT NULL DEFAULT '',
`password` char(32) NOT NULL DEFAULT '',
`medal_id` char(20) NOT NULL DEFAULT '',
`media_id` int(11) NOT NULL DEFAULT '0',
`media_order_id` int(11) NOT NULL DEFAULT '0',
`gender` tinyint(1) NOT NULL DEFAULT '0',
`regip` char(15) NOT NULL DEFAULT '',
`reg_ip_port` CHAR(6) NOT NULL DEFAULT '',
`regdate` int(10) unsigned NOT NULL DEFAULT '0',
`lastip` char(15) NOT NULL DEFAULT '',
`last_ip_port` CHAR(6) NOT NULL DEFAULT '',
`lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
`lastpost` int(10) unsigned NOT NULL DEFAULT '0',
`credits` int(10) NOT NULL DEFAULT '0',
`extcredits1` int(10) NOT NULL DEFAULT '0',
`extcredits2` int(10) NOT NULL DEFAULT '0',
`extcredits3` int(10) NOT NULL DEFAULT '0',
`extcredits4` int(10) NOT NULL DEFAULT '0',
`extcredits5` int(10) NOT NULL DEFAULT '0',
`extcredits6` int(10) NOT NULL DEFAULT '0',
`extcredits7` int(10) NOT NULL DEFAULT '0',
`extcredits8` int(10) NOT NULL DEFAULT '0',
`email` char(50) NOT NULL DEFAULT '',
`email_checked` tinyint(1) NOT NULL DEFAULT 0,
`bday` date NOT NULL DEFAULT '0000-00-00',
`newpm` tinyint(1) NOT NULL DEFAULT '0',
`face_url` char(60) NOT NULL DEFAULT '',
`face` char(60) NOT NULL DEFAULT '',
`tag_count` mediumint(6) NOT NULL DEFAULT '0',
`role_id` smallint(4) unsigned NOT NULL DEFAULT '0',
`role_type` enum('admin','normal') NOT NULL DEFAULT 'normal',
`tag` char(255) NOT NULL DEFAULT '',
`phone` char(15) NOT NULL DEFAULT '',
`use_tag_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
`create_tag_count` smallint(4) unsigned NOT NULL DEFAULT '0',
`image_count` int(10) unsigned NOT NULL DEFAULT '0',
`ucuid` mediumint(8) NOT NULL DEFAULT '0',
`invite_uid` mediumint(8) unsigned NOT NULL,
`invite_count` smallint(4) unsigned NOT NULL DEFAULT '0',
`invitecode` char(16) NOT NULL DEFAULT '0',
`topic_count` mediumint(6) unsigned NOT NULL DEFAULT '0',
`at_count` mediumint(6) unsigned NOT NULL DEFAULT '0',
`follow_count` mediumint(6) unsigned NOT NULL DEFAULT '0',
`fans_count` mediumint(6) unsigned NOT NULL DEFAULT '0',
`email2` char(50) NOT NULL DEFAULT '',
`qq` char(10) NOT NULL DEFAULT '',
`msn` char(50) NOT NULL DEFAULT '',
`aboutme` char(255) NOT NULL DEFAULT '',
`aboutmetime` int(10) NOT NULL DEFAULT '0',
`signature` char(30) NOT NULL DEFAULT '',
`signtime` int(10) NOT NULL DEFAULT '0',
`at_new` smallint(4) NOT NULL DEFAULT '0',
`comment_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`event_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`fans_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`vote_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`qun_new` smallint(4) NOT NULL DEFAULT '0',
`topic_new` smallint(4) NOT NULL DEFAULT '0',
`topic_favorite_count` smallint(4) unsigned NOT NULL DEFAULT '0',
`tag_favorite_count` smallint(4) unsigned NOT NULL DEFAULT '0',
`disallow_beiguanzhu` tinyint(1) NOT NULL DEFAULT '0',
`validate` tinyint(1) NOT NULL DEFAULT '0',
`validate_category` tinyint(1) NOT NULL DEFAULT '0',
`favoritemy_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`notice_at` tinyint(1) NOT NULL default '0',
`notice_pm` tinyint(1) NOT NULL default '0',
`notice_reply` tinyint(1) NOT NULL default '0',
`notice_fans` tinyint(1) NOT NULL default '0',
`notice_event` tinyint(1) NOT NULL default '0',
`user_notice_time` tinyint(1) NOT NULL default '0',
`last_notice_time` int(10) NOT NULL default '0',
`companyid` int(6) NOT NULL default '0',
`company` char(50) NOT NULL default '',
`jobid` int(6) NOT NULL default '0',
`job` char(50) NOT NULL default '',
`departmentid` int(6) NOT NULL default '0',
`department` char(50) NOT NULL default '',
`theme_id` char(6) NOT NULL DEFAULT '',
`theme_bg_image` char(60) NOT NULL DEFAULT '',
`theme_bg_color` char(7) NOT NULL DEFAULT '',
`theme_text_color` char(7) NOT NULL DEFAULT '',
`theme_link_color` char(7) NOT NULL DEFAULT '',
`theme_bg_image_type` enum('repeat','center','left','right','bottom') NOT NULL DEFAULT 'repeat',
`theme_bg_repeat` tinyint(1) NOT NULL,
`theme_bg_fixed` tinyint(1) NOT NULL,
`profile_image` char(120) NOT NULL default './images/art_bg.jpg',
`last_topic_content_id` int(10) NOT NULL DEFAULT '0',
`level` int(10) NOT NULL,
`style_three_tol` tinyint(3) NOT NULL DEFAULT '0',
`province` char(16) NOT NULL DEFAULT '',
`city` char(16) NOT NULL DEFAULT '',
`area` char(16) NOT NULL DEFAULT '',
`street` char(16) NOT NULL DEFAULT '',
`qmd_url` char(60) NOT NULL DEFAULT '',
`event_post_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`fenlei_post_new` smallint(4) unsigned NOT NULL DEFAULT '0',
`qmd_img` char(30) NOT NULL,
`open_extra` tinyint(1) NOT NULL DEFAULT '0',
`digcount` int(10) UNSIGNED NOT NULL DEFAULT '0',
`dig_new` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
`channel_new` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
`company_new` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
`close_recd_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
`salt` char(10) NOT NULL,
PRIMARY KEY (`uid`),
KEY `username` (`username`),
KEY `nickname` (`nickname`),
KEY `email` (`email`),
KEY `role_id` (`role_id`),
KEY `ucuid` (`ucuid`),
KEY `companytid` (`companyid`),
KEY `departmentid` (`departmentid`),
KEY `jobid` (`jobid`),
KEY `phone` (`phone`),
KEY `regdate` (`regdate`),
KEY `regip` (`regip`),
KEY `lastactivity` (`lastactivity`),
KEY `last_notice_time` (`last_notice_time`),
KEY `invite_uid` (`invite_uid`),
KEY `province_city` (`province`,`city`),
KEY `credits` (`credits`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_members_verify`;
CREATE TABLE `jishigou_members_verify` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`nickname` char(30) NOT NULL DEFAULT '',
`face_url` char(60) NOT NULL DEFAULT '',
`face` char(60) NOT NULL DEFAULT '',
`signature` char(30) NOT NULL DEFAULT '',
`is_sign` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_my_tag`;
CREATE TABLE `jishigou_my_tag` (
`user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tag_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`total_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
`topic_count` smallint(6) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`user_id`,`tag_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_my_topic_tag`;
CREATE TABLE `jishigou_my_topic_tag` (
`user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`item_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tag_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`count` smallint(4) unsigned NOT NULL DEFAULT '1',
PRIMARY KEY (`user_id`,`item_id`,`tag_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_notice`;
CREATE TABLE `jishigou_notice` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`title` char(200) NOT NULL DEFAULT '',
`content` text NOT NULL,
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_output`;
CREATE TABLE `jishigou_output` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` char(100) NOT NULL,
`hash` char(32) NOT NULL,
`lock_host` text NOT NULL,
`per_page_num` int(10) unsigned NOT NULL DEFAULT '20',
`content_default` char(100) NOT NULL,
`type_first` tinyint(1) NOT NULL,
`open_times` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`tpl_enable` tinyint(1) unsigned NOT NULL,
`tpl_file` char(100) NOT NULL,
`width` char(10) NOT NULL,
`height` char(10) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_plugin`;
CREATE TABLE `jishigou_plugin` (
`pluginid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`available` tinyint(1) NOT NULL DEFAULT '0',
`adminid` tinyint(1) unsigned NOT NULL DEFAULT '0',
`name` varchar(40) NOT NULL DEFAULT '',
`identifier` varchar(40) NOT NULL DEFAULT '',
`description` varchar(255) NOT NULL DEFAULT '',
`datatables` varchar(255) NOT NULL DEFAULT '',
`directory` varchar(100) NOT NULL DEFAULT '',
`copyright` varchar(100) NOT NULL DEFAULT '',
`modules` text NOT NULL,
`version` varchar(20) NOT NULL DEFAULT '',
PRIMARY KEY (`pluginid`),
UNIQUE KEY `identifier` (`identifier`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_pluginvar`;
CREATE TABLE `jishigou_pluginvar` (
`pluginvarid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`pluginid` smallint(6) unsigned NOT NULL DEFAULT '0',
`displayorder` tinyint(3) NOT NULL DEFAULT '0',
`title` varchar(100) NOT NULL DEFAULT '',
`description` varchar(255) NOT NULL DEFAULT '',
`variable` varchar(40) NOT NULL DEFAULT '',
`type` varchar(20) NOT NULL DEFAULT 'text',
`value` text NOT NULL,
`extra` text NOT NULL,
PRIMARY KEY (`pluginvarid`),
KEY `pluginid` (`pluginid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_pms`;
CREATE TABLE `jishigou_pms` (
`pmid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`msgfrom` char(15) NOT NULL DEFAULT '',
`msgnickname` char(15) NOT NULL DEFAULT '',
`msgfromid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`msgto` char(15) NOT NULL DEFAULT '',
`tonickname` char(15) NOT NULL DEFAULT '',
`msgtoid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`folder` enum('inbox','outbox') NOT NULL DEFAULT 'inbox',
`new` tinyint(1) NOT NULL DEFAULT '0',
`subject` varchar(75) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`imageids` char(100) NOT NULL default '',
`attachids` char(100) NOT NULL default '',
`message` text NOT NULL,
`delstatus` tinyint(1) unsigned NOT NULL DEFAULT '0',
`is_hi` tinyint(1) NOT NULL DEFAULT '0',
`topmid` int(11) NOT NULL DEFAULT '0',
`plid` mediumint(8) NOT NULL DEFAULT '0',
PRIMARY KEY (`pmid`),
KEY `msgtoid` (`msgtoid`,`folder`,`dateline`),
KEY `msgfromid` (`msgfromid`,`folder`,`dateline`),
KEY `dateline` (`dateline`),
KEY `plid` (`plid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_pms_index`;
CREATE TABLE `jishigou_pms_index` (
`plid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`uids` char(17) NOT NULL,
PRIMARY KEY (`plid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_pms_list`;
CREATE TABLE `jishigou_pms_list` (
`plid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`pmnum` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`lastmessage` text NOT NULL,
`is_new` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`plid`,`uid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qqwb_bind_info`;
CREATE TABLE `jishigou_qqwb_bind_info` (
`uid` mediumint(8) unsigned NOT NULL,
`qqwb_username` char(20) NOT NULL DEFAULT '',
`token` char(32) NOT NULL,
`tsecret` char(32) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`synctoqq` tinyint(1) NOT NULL,
`last_read_time` int(10) unsigned NOT NULL,
`last_read_id` char(30) NOT NULL,
`sync_weibo_to_jishigou` tinyint(1) NOT NULL,
`sync_reply_to_jishigou` tinyint(1) NOT NULL,
`code` char(32) NOT NULL,
`openid` char(32) NOT NULL,
`openkey` char(32) NOT NULL,
`access_token` char(32) NOT NULL,
`refresh_token` char(32) NOT NULL,
`expires_in` int(10) unsigned NOT NULL,
`name` char(100) NOT NULL,
`nick` char(100) NOT NULL,
`state` char(100) NOT NULL,
`expires_time` int(10) unsigned NOT NULL,
`last_update` int(10) unsigned NOT NULL,
PRIMARY KEY (`uid`),
UNIQUE KEY `qqwb_username` (`qqwb_username`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qqwb_bind_topic`;
CREATE TABLE `jishigou_qqwb_bind_topic` (
`tid` int(10) unsigned NOT NULL,
`qqwb_id` bigint(20) unsigned NOT NULL,
`last_read_time` int(10) unsigned NOT NULL,
KEY `tid` (`tid`),
KEY `qqwb_id` (`qqwb_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun`;
CREATE TABLE `jishigou_qun` (
`qid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`cat_id` smallint(6) unsigned NOT NULL DEFAULT '0',
`name` char(50) NOT NULL,
`icon` char(100) NOT NULL,
`desc` char(200) NOT NULL,
`founderuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`foundername` char(15) NOT NULL,
`level` smallint(6) unsigned NOT NULL DEFAULT '0',
`credits` int(10) unsigned NOT NULL DEFAULT '0',
`province` char(16) NOT NULL,
`city` char(16) NOT NULL,
`recd` tinyint(1) unsigned NOT NULL DEFAULT '0',
`join_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
`gview_perm` tinyint(1) unsigned NOT NULL DEFAULT '0',
`member_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`topic_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`thread_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
`qun_theme_id` char(6) NOT NULL DEFAULT '',
`postip` char(15) NOT NULL DEFAULT '0',
`lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`qid`),
KEY `cat_id` (`cat_id`),
KEY `founderuid` (`founderuid`),
KEY `lastactivity` (`lastactivity`),
KEY `dateline` (`dateline`),
KEY `member_num` (`member_num`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_announcement`;
CREATE TABLE `jishigou_qun_announcement` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`author` char(15) NOT NULL,
`qid` int(10) unsigned NOT NULL DEFAULT '0',
`author_id` mediumint(9) unsigned NOT NULL DEFAULT '0',
`message` text NOT NULL,
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `qid` (`qid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_apply`;
CREATE TABLE `jishigou_qun_apply` (
`qid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`username` varchar(15) NOT NULL,
`message` varchar(255) NOT NULL,
`apply_time` int(10) unsigned NOT NULL,
PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_category`;
CREATE TABLE `jishigou_qun_category` (
`cat_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`cat_name` char(15) NOT NULL,
`qun_num` int(10) unsigned NOT NULL DEFAULT '0',
`parent_id` smallint(6) unsigned NOT NULL DEFAULT '0',
`display_order` smallint(6) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_event`;
CREATE TABLE `jishigou_qun_event` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`qid` int(10) unsigned NOT NULL DEFAULT '0',
`eid` int(10) NOT NULL DEFAULT '0',
`recd` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `q_e_id` (`qid`,`eid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_level`;
CREATE TABLE `jishigou_qun_level` (
`level_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`level_name` char(20) NOT NULL,
`credits_higher` int(10) NOT NULL DEFAULT '0',
`credits_lower` int(10) NOT NULL DEFAULT '0',
`member_num` int(10) unsigned NOT NULL DEFAULT '0',
`admin_num` smallint(6) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`level_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_ploy`;
CREATE TABLE `jishigou_qun_ploy` (
`id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`fans_num_min` int(10) unsigned NOT NULL DEFAULT '0',
`fans_num_max` int(10) unsigned NOT NULL DEFAULT '0',
`topics_higher` int(10) unsigned NOT NULL DEFAULT '0',
`topics_lower` int(10) unsigned NOT NULL DEFAULT '0',
`qun_num` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_tag`;
CREATE TABLE `jishigou_qun_tag` (
`tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tag_name` char(30) NOT NULL,
`count` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_tag_fields`;
CREATE TABLE `jishigou_qun_tag_fields` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tag_id` int(10) unsigned NOT NULL DEFAULT '0',
`qid` int(10) unsigned NOT NULL DEFAULT '0',
`tag_name` char(30) NOT NULL,
PRIMARY KEY (`id`),
KEY `qid-tag_id` (`qid`,`tag_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_user`;
CREATE TABLE `jishigou_qun_user` (
`qid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`username` char(15) NOT NULL,
`level` tinyint(3) unsigned NOT NULL DEFAULT '0',
`join_time` int(10) unsigned NOT NULL DEFAULT '0',
`lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`qid`,`uid`),
KEY `uid` (`uid`),
KEY `join_time` (`join_time`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_qun_vote`;
CREATE TABLE `jishigou_qun_vote` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`qid` int(10) unsigned NOT NULL DEFAULT '0',
`vid` int(10) NOT NULL DEFAULT '0',
`recd` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `q_vid` (`qid`,`vid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_renren_bind_info`;
CREATE TABLE `jishigou_renren_bind_info` (
`uid` mediumint(8) unsigned NOT NULL,
`renren_uid` char(32) NOT NULL default '',
`renren_name` char(32) NOT NULL default '',
`renren_sex` char(10) NOT NULL default '',
`renren_star` char(10) NOT NULL default '',
`renren_headurl` char(64) NOT NULL default '',
`token` char(128) NOT NULL,
`token_time` int(10) NOT NULL,
`token_expire` int(10) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY  (`uid`),
UNIQUE KEY `renren_uid` (`renren_uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_renren_bind_topic`;
CREATE TABLE `jishigou_renren_bind_topic` (
`tid` int(10) unsigned NOT NULL,
`renren_id` bigint(20) unsigned NOT NULL,
KEY `tid` (`tid`),
KEY `renren_id` (`renren_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_report`;
CREATE TABLE `jishigou_report` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`ip` char(15) NOT NULL DEFAULT '',
`type` tinyint(1) NOT NULL DEFAULT '0',
`reason` tinyint(1) NOT NULL DEFAULT '0',
`content` text NOT NULL,
`tid` int(10) NOT NULL DEFAULT '0',
`dateline` int(10) NOT NULL DEFAULT '0',
`process_user` char(15) NOT NULL DEFAULT '',
`process_time` int(10) NOT NULL DEFAULT '0',
`process_result` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_robot`;
CREATE TABLE `jishigou_robot` (
`name` char(50) NOT NULL DEFAULT '',
`times` int(10) unsigned NOT NULL DEFAULT '0',
`first_visit` int(10) NOT NULL DEFAULT '0',
`last_visit` int(10) NOT NULL DEFAULT '0',
`agent` char(255) NOT NULL DEFAULT '',
`disallow` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_robot_ip`;
CREATE TABLE `jishigou_robot_ip` (
`ip` char(15) NOT NULL DEFAULT '',
`name` char(50) NOT NULL DEFAULT '',
`times` int(10) unsigned NOT NULL DEFAULT '0',
`first_visit` int(10) NOT NULL DEFAULT '0',
`last_visit` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`ip`),
KEY `name` (`name`),
KEY `times` (`times`),
KEY `last_visit` (`last_visit`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_robot_log`;
CREATE TABLE `jishigou_robot_log` (
`name` char(50) NOT NULL DEFAULT '',
`date` date NOT NULL DEFAULT '0000-00-00',
`times` int(10) unsigned NOT NULL DEFAULT '0',
`first_visit` int(10) unsigned NOT NULL DEFAULT '0',
`last_visit` int(10) unsigned NOT NULL DEFAULT '0',
UNIQUE KEY `date-name` (`date`,`name`),
KEY `name` (`name`),
KEY `times` (`times`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_role`;
CREATE TABLE `jishigou_role` (
`id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(50) NOT NULL DEFAULT '',
`creditshigher` int(10) NOT NULL DEFAULT '0',
`creditslower` int(10) NOT NULL DEFAULT '0',
`privilege` mediumtext NOT NULL,
`type` enum('normal','admin') NOT NULL DEFAULT 'normal',
`rank` tinyint(1) unsigned NOT NULL DEFAULT '0',
`icon` char(100) NOT NULL,
`allow_sendpm_to` text NOT NULL,
`allow_sendpm_from` text NOT NULL,
`allow_topic_forward_to` text NOT NULL,
`allow_topic_forward_from` text NOT NULL,
`allow_topic_reply_to` text NOT NULL,
`allow_topic_reply_from` text NOT NULL,
`allow_topic_at_to` text NOT NULL,
`allow_topic_at_from` text NOT NULL,
`allow_follow_to` text NOT NULL,
`allow_follow_from` text NOT NULL,
`system` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_role_action`;
CREATE TABLE `jishigou_role_action` (
`id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL DEFAULT '',
`module` varchar(50) NOT NULL DEFAULT 'index',
`action` varchar(255) NOT NULL DEFAULT '',
`describe` varchar(255) NOT NULL DEFAULT '',
`message` varchar(255) NOT NULL DEFAULT '',
`allow_all` tinyint(1) NOT NULL DEFAULT '0',
`credit_require` varchar(255) NOT NULL DEFAULT '',
`credit_update` varchar(255) NOT NULL DEFAULT '',
`log` tinyint(1) unsigned NOT NULL DEFAULT '0',
`is_admin` tinyint(1) unsigned DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `action` (`module`,`name`,`is_admin`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_role_module`;
CREATE TABLE `jishigou_role_module` (
`module` varchar(50) NOT NULL DEFAULT '',
`name` varchar(255) NOT NULL DEFAULT '',
UNIQUE KEY `module` (`module`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_schedule`;
CREATE TABLE `jishigou_schedule` (
`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`type` char(255) NOT NULL DEFAULT '',
`vars` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sessions`;
CREATE TABLE `jishigou_sessions` (
`sid` char(6) NOT NULL DEFAULT '',
`ip1` tinyint(1) unsigned NOT NULL DEFAULT '0',
`ip2` tinyint(1) unsigned NOT NULL DEFAULT '0',
`ip3` tinyint(1) unsigned NOT NULL DEFAULT '0',
`ip4` tinyint(1) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`action` smallint(4) unsigned NOT NULL DEFAULT '0',
`slastactivity` int(10) unsigned NOT NULL DEFAULT '0',
UNIQUE KEY `sid` (`sid`),
KEY `uid` (`uid`),
KEY `slastactivity` (`slastactivity`)
) ENGINE=HEAP;

DROP TABLE IF EXISTS `jishigou_setting`;
CREATE TABLE `jishigou_setting` (
`key` char(255) NOT NULL,
`val` text NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_share`;
CREATE TABLE `jishigou_share` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` char(50) NOT NULL,
`type` char(20) NOT NULL,
`topic_style` text NOT NULL,
`show_style` text NOT NULL,
`condition` text NOT NULL,
`nickname` char(255) NOT NULL,
`tag` char(255) NOT NULL,
`dateline` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sign_tag`;
CREATE TABLE `jishigou_sign_tag` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tag` text NOT NULL,
`credits` char(20) NOT NULL DEFAULT '',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_site`;
CREATE TABLE `jishigou_site` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`host` char(50) NOT NULL,
`name` char(50) NOT NULL,
`description` char(255) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`url_count` int(10) unsigned NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `host` (`host`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sms_client_user`;
CREATE TABLE `jishigou_sms_client_user` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(30) NOT NULL DEFAULT '',
`user_im` char(20) NOT NULL DEFAULT '',
`bind_key` char(10) NOT NULL DEFAULT '',
`bind_key_time` int(10) unsigned NOT NULL DEFAULT '0',
`try_bind_times` int(10) unsigned NOT NULL DEFAULT '0',
`last_try_bind_time` int(10) unsigned NOT NULL DEFAULT '0',
`send_times` int(10) unsigned NOT NULL DEFAULT '0',
`last_send_time` int(10) unsigned NOT NULL DEFAULT '0',
`last_send_message_id` int(10) unsigned NOT NULL DEFAULT '0',
`stop_receive` tinyint(1) unsigned NOT NULL DEFAULT '0',
`receive_times` int(10) unsigned NOT NULL DEFAULT '0',
`last_receive_time` int(10) unsigned NOT NULL DEFAULT '0',
`last_receive_message_id` int(10) unsigned NOT NULL DEFAULT '0',
`reset_password_times` int(10) unsigned NOT NULL DEFAULT '0',
`last_reset_password_time` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`enable` tinyint(1) unsigned NOT NULL DEFAULT '1',
`t_enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
`p_enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
`m_enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
`f_enable` tinyint(1) unsigned NOT NULL DEFAULT '0',
`share_time` int(10) unsigned NOT NULL DEFAULT '0',
`verify_key` char(10) NOT NULL DEFAULT '',
`verify_key_time` int(10) unsigned NOT NULL DEFAULT '0',
`try_verify_times` int(10) unsigned NOT NULL DEFAULT '0',
`last_try_verify_time` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `user_im` (`user_im`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sms_failedlogins`;
CREATE TABLE `jishigou_sms_failedlogins` (
`ip` char(15) NOT NULL DEFAULT '',
`count` tinyint(1) unsigned NOT NULL DEFAULT '0',
`lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`ip`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sms_receive_log`;
CREATE TABLE `jishigou_sms_receive_log` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`username` char(15) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`mobile` char(20) NOT NULL DEFAULT '',
`message` text NOT NULL,
`msg_id` char(20) NOT NULL,
`status` tinyint(1) NOT NULL,
`tid` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `dateline` (`dateline`),
KEY `uid` (`uid`),
KEY `mobile` (`mobile`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sms_send_log`;
CREATE TABLE `jishigou_sms_send_log` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`username` char(15) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`mobile` char(20) NOT NULL DEFAULT '',
`message` text NOT NULL,
`msg_id` char(20) NOT NULL,
`status` tinyint(1) NOT NULL,
PRIMARY KEY (`id`),
KEY `dateline` (`dateline`),
KEY `uid` (`uid`),
KEY `mobile` (`mobile`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_sms_send_queue`;
CREATE TABLE `jishigou_sms_send_queue` (
`to` int(10) unsigned NOT NULL DEFAULT '0',
`message` text NOT NULL,
`salt` char(10) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
UNIQUE KEY `to` (`to`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_tag`;
CREATE TABLE `jishigou_tag` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`name` char(30) NOT NULL DEFAULT '',
`user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`last_post` int(10) unsigned NOT NULL DEFAULT '0',
`total_count` int(10) unsigned NOT NULL DEFAULT '0',
`user_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
`topic_count` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tag_count` mediumint(8) NOT NULL DEFAULT '0',
`status` tinyint(1) NOT NULL DEFAULT '0',
`extra` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `name` (`name`),
KEY `total_count` (`total_count`),
KEY `last_post` (`last_post`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_tag_extra`;
CREATE TABLE `jishigou_tag_extra` (
`id` int(10) unsigned NOT NULL,
`name` char(50) NOT NULL,
`data` longtext NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_tag_favorite`;
CREATE TABLE `jishigou_tag_favorite` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tag` char(64) NOT NULL DEFAULT '',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `tag` (`tag`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_tag_recommend`;
CREATE TABLE `jishigou_tag_recommend` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`name` char(50) NOT NULL,
`desc` text NOT NULL,
`uid` mediumint(8) NOT NULL,
`username` char(30) NOT NULL,
`dateline` int(10) NOT NULL,
`last_update` int(10) NOT NULL,
`order` int(10) NOT NULL,
`enable` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`),
KEY `order` (`order`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_talk`;
CREATE TABLE `jishigou_talk` (
`lid` int(11) NOT NULL AUTO_INCREMENT,
`cat_id` int(11) unsigned NOT NULL,
`talkname` varchar(60) NOT NULL,
`description` text NOT NULL,
`starttime` int(10) unsigned NOT NULL DEFAULT '0',
`endtime` int(10) unsigned NOT NULL DEFAULT '0',
`image` varchar(100) NOT NULL,
PRIMARY KEY (`lid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_talk_category`;
CREATE TABLE `jishigou_talk_category` (
`cat_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`cat_name` char(15) NOT NULL,
`talk_num` int(10) unsigned NOT NULL DEFAULT '0',
`parent_id` smallint(6) unsigned NOT NULL DEFAULT '0',
`display_order` smallint(6) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`cat_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_task`;
CREATE TABLE `jishigou_task` (
`id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`available` tinyint(1) NOT NULL DEFAULT '0',
`type` enum('user','system') NOT NULL DEFAULT 'user',
`name` char(50) NOT NULL DEFAULT '',
`filename` char(50) NOT NULL DEFAULT '',
`lastrun` int(10) unsigned NOT NULL DEFAULT '0',
`nextrun` int(10) unsigned NOT NULL DEFAULT '0',
`weekday` tinyint(1) NOT NULL DEFAULT '0',
`day` tinyint(1) NOT NULL DEFAULT '0',
`hour` tinyint(1) NOT NULL DEFAULT '0',
`minute` char(36) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `nextrun` (`available`,`nextrun`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_task_log`;
CREATE TABLE `jishigou_task_log` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`task_id` int(10) unsigned NOT NULL DEFAULT '0',
`exec_time` float unsigned NOT NULL DEFAULT '0',
`message` text NOT NULL,
`error` int(10) NOT NULL DEFAULT '0',
`dateline` int(10) NOT NULL DEFAULT '0',
`ip` varchar(16) NOT NULL DEFAULT '0',
`username` varchar(15) NOT NULL DEFAULT '',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`agent` varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `task_id` (`task_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic`;
CREATE TABLE `jishigou_topic` (
`tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`content` char(255) NOT NULL DEFAULT '',
`content2` char(255) NOT NULL DEFAULT '',
`imageid` char(100) NOT NULL DEFAULT '',
`videoid` int(10) unsigned NOT NULL DEFAULT '0',
`musicid` int(10) unsigned NOT NULL DEFAULT '0',
`longtextid` int(10) unsigned NOT NULL DEFAULT '0',
`attachid` char(100) NOT NULL DEFAULT '',
`roottid` int(10) unsigned NOT NULL DEFAULT '0',
`replys` smallint(4) unsigned NOT NULL DEFAULT '0',
`forwards` smallint(6) NOT NULL DEFAULT '0',
`totid` int(10) unsigned NOT NULL DEFAULT '0',
`touid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tousername` char(15) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
`from` enum('web','wap','mobile','qq','msn','api','sina','qqwb','vote','qun','event','android','iphone','ipad','sms','androidpad','fenlei','wechat','reward') NOT NULL DEFAULT 'web',
`type` char(15) NOT NULL DEFAULT '',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`item` char(15) NOT NULL DEFAULT '',
`postip` char(15) NOT NULL DEFAULT '',
`post_ip_port` char(6) NOT NULL DEFAULT '',
`managetype` tinyint(1) NOT NULL DEFAULT '0',
`digcounts` int(10) unsigned NOT NULL DEFAULT '0',
`lastdigtime` int(10) unsigned NOT NULL DEFAULT '0',
`lastdiguid` int(10) unsigned NOT NULL DEFAULT '0',
`lastdigusername` char(15) NOT NULL DEFAULT '',
`tag_count` smallint(4) unsigned NOT NULL default '0',
`tag` char(255) NOT NULL default '',
`recommend` tinyint(1) unsigned NOT NULL default '0',
`featureid` smallint(4) NOT NULL DEFAULT '0',
`relateid` int(10) NOT NULL DEFAULT '0',
`anonymous` tinyint(1) unsigned NOT NULL default '0',
PRIMARY KEY (`tid`),
KEY `uid_type` (`uid`,`type`),
KEY `dateline` (`dateline`),
KEY `lastdigtime` (`lastdigtime`),
KEY `managetype` (`managetype`),
KEY `item_id_item` (`item_id`,`item`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_api`;
CREATE TABLE `jishigou_topic_api` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_table_id`;
CREATE TABLE `jishigou_topic_table_id` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` bigint(20) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_attach`;
CREATE TABLE `jishigou_topic_attach` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) NOT NULL DEFAULT '0',
`site_url` char(255) NOT NULL DEFAULT '',
`file` char(255) NOT NULL DEFAULT '',
`name` char(255) NOT NULL DEFAULT '',
`description` char(255) NOT NULL DEFAULT '',
`filesize` int(10) unsigned NOT NULL DEFAULT '0',
`filetype` varchar(10) NOT NULL DEFAULT '',
`uid` mediumint(8) unsigned DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`file_url` char(255) NOT NULL DEFAULT '',
`item` char(100) NOT NULL DEFAULT '',
`itemid` int(10) unsigned NOT NULL DEFAULT '0',
`download` int(10) unsigned NOT NULL DEFAULT '0',
`score` int(10) unsigned NOT NULL DEFAULT '0',
`category` char(100) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_attach_category`;
CREATE TABLE `jishigou_attach_category` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` char(20) NOT NULL,
`parent_id` int(11) NOT NULL DEFAULT '0',
`upid` char(60) NOT NULL,
`order` int(11) NOT NULL DEFAULT '0',
`count_num` int(11) NOT NULL DEFAULT '0',
`total_count_num` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_event`;
CREATE TABLE `jishigou_topic_event` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_favorite`;
CREATE TABLE `jishigou_topic_favorite` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`tuid` mediumint(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `uid-tid` (`uid`,`tid`),
KEY `tuid` (`tuid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_image`;
CREATE TABLE `jishigou_topic_image` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) NOT NULL DEFAULT '0',
`site_url` char(255) NOT NULL DEFAULT '',
`photo` char(255) NOT NULL DEFAULT '',
`name` char(255) NOT NULL DEFAULT '',
`description` char(255) NOT NULL DEFAULT '',
`filesize` int(10) unsigned NOT NULL DEFAULT '0',
`width` smallint(4) unsigned NOT NULL DEFAULT '0',
`height` smallint(4) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`item` char(100) NOT NULL DEFAULT '',
`itemid` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`image_url` char(255) NOT NULL,
`albumid` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `uid` (`uid`),
KEY `albumid` (`albumid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_album`;
CREATE TABLE `jishigou_album` (
`albumid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`albumname` varchar(50) NOT NULL default '',
`uid` mediumint(8) unsigned NOT NULL default '0',
`username` varchar(50) NOT NULL default '',
`dateline` int(10) unsigned NOT NULL default '0',
`picnum` smallint(6) unsigned NOT NULL default '0',
`pic` varchar(255) NOT NULL default '',
`depict` varchar(255) NOT NULL default '',
`purview` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`albumid`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_live`;
CREATE TABLE `jishigou_topic_live` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_longtext`;
CREATE TABLE `jishigou_topic_longtext` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`username` char(15) NOT NULL DEFAULT '',
`longtext` longtext NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`last_modify` int(10) unsigned NOT NULL,
`modify_times` int(10) unsigned NOT NULL,
`views` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_mention`;
CREATE TABLE `jishigou_topic_mention` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`tuid` mediumint(8) unsigned NOT NULL default '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `uid-tid` (`uid`,`tid`),
KEY `tid` (`tid`),
KEY `tuid` (`tuid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_more`;
CREATE TABLE `jishigou_topic_more` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`parents` text NOT NULL,
`replyids` longtext NOT NULL,
`replyidscount` smallint(4) unsigned NOT NULL DEFAULT '0',
`diguids` longtext NOT NULL,
`longtext` longtext NOT NULL,
PRIMARY KEY (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_music`;
CREATE TABLE `jishigou_topic_music` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`tid` int(11) NOT NULL DEFAULT '0',
`username` varchar(50) NOT NULL DEFAULT '',
`music_url` varchar(255) NOT NULL DEFAULT '',
`dateline` int(11) NOT NULL DEFAULT '0',
`xiami_id` int(20) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_qun`;
CREATE TABLE `jishigou_topic_qun` (
`tid` int(10) unsigned NOT NULL,
`item_id` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`),
KEY `uid` (`uid`),
KEY `item_id` (`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_recommend`;
CREATE TABLE `jishigou_topic_recommend` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item` char(15) NOT NULL,
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`recd` tinyint(1) unsigned NOT NULL,
`display_order` smallint(6) unsigned NOT NULL DEFAULT '0',
`expiration` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`r_title` char(200) NOT NULL default '',
`r_uid` int(10) unsigned NOT NULL default '0',
`r_nickname` char(15) NOT NULL default '',
PRIMARY KEY (`tid`),
KEY `expiration` (`expiration`),
KEY `recd` (`recd`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_relation`;
CREATE TABLE `jishigou_topic_relation` (
`totid` int(11) unsigned NOT NULL,
`touid` mediumint(8) unsigned NOT NULL,
`tid` int(11) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`type` enum('reply','forward','both') NOT NULL DEFAULT 'reply',
`digcounts` int(10) unsigned NOT NULL,
`lastdigtime` int(10) unsigned NOT NULL,
PRIMARY KEY (`totid`,`tid`),
KEY `dateline` (`dateline`),
KEY `digcounts` (`digcounts`),
KEY `lastdigtime` (`lastdigtime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_relation_table_id`;
CREATE TABLE `jishigou_topic_relation_table_id` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` bigint(20) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_reply`;
CREATE TABLE `jishigou_topic_reply` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`replyid` int(10) unsigned NOT NULL DEFAULT '0',
KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_show`;
CREATE TABLE `jishigou_topic_show` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`stylevalue` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_tag`;
CREATE TABLE `jishigou_topic_tag` (
`item_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tag_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`count` mediumint(6) NOT NULL DEFAULT '0',
PRIMARY KEY (`item_id`,`tag_id`),
KEY `tag_id` (`tag_id`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_talk`;
CREATE TABLE `jishigou_topic_talk` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`touid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`totid` int(10) unsigned NOT NULL DEFAULT '0',
`istop` tinyint(1) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`tid`,`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_url`;
CREATE TABLE `jishigou_topic_url` (
`tid` int(10) unsigned NOT NULL,
`item_id` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_verify`;
CREATE TABLE `jishigou_topic_verify` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`tid` int(10) unsigned NOT NULL,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL DEFAULT '',
`content` char(255) NOT NULL DEFAULT '',
`content2` char(255) NOT NULL DEFAULT '',
`imageid` char(100) NOT NULL DEFAULT '',
`videoid` int(10) unsigned NOT NULL DEFAULT '0',
`musicid` int(10) unsigned NOT NULL DEFAULT '0',
`longtextid` int(10) unsigned NOT NULL DEFAULT '0',
`attachid` char(100) NOT NULL DEFAULT '',
`roottid` int(10) unsigned NOT NULL DEFAULT '0',
`replys` smallint(4) unsigned NOT NULL DEFAULT '0',
`forwards` smallint(6) NOT NULL DEFAULT '0',
`totid` int(10) unsigned NOT NULL DEFAULT '0',
`touid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`tousername` char(15) NOT NULL DEFAULT '',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
`from` enum('web','wap','mobile','qq','msn','api','sina','qqwb','vote','qun','event','android','iphone','ipad','sms','androidpad','fenlei','wechat','reward') NOT NULL DEFAULT 'web',
`type` char(15) NOT NULL DEFAULT '',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`item` char(15) NOT NULL DEFAULT '',
`postip` char(15) NOT NULL DEFAULT '',
`post_ip_port` char(6) NOT NULL DEFAULT '',
`managetype` tinyint(1) NOT NULL DEFAULT '0',
`digcounts` int(10) unsigned NOT NULL DEFAULT '0',
`lastdigtime` int(10) unsigned NOT NULL DEFAULT '0',
`lastdiguid` int(10) unsigned NOT NULL DEFAULT '0',
`lastdigusername` char(15) NOT NULL DEFAULT '',
`tag_count` smallint(4) unsigned NOT NULL default '0',
`tag` char(255) NOT NULL default '',
`recommend` tinyint(1) unsigned NOT NULL default '0',
`featureid` smallint(4) NOT NULL DEFAULT '0',
`relateid` int(10) NOT NULL DEFAULT '0',
`anonymous` tinyint(1) unsigned NOT NULL default '0',
PRIMARY KEY (`id`),
KEY (`tid`),
KEY `uid_type` (`uid`,`type`),
KEY `dateline` (`dateline`),
KEY `lastdigtime` (`lastdigtime`),
KEY `managetype` (`managetype`),
KEY `item_id_item` (`item_id`,`item`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_video`;
CREATE TABLE `jishigou_topic_video` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`tid` int(11) NOT NULL DEFAULT '0',
`username` varchar(50) NOT NULL DEFAULT '',
`video_hosts` varchar(255) NOT NULL DEFAULT '',
`video_link` varchar(255) NOT NULL DEFAULT '',
`video_url` varchar(255) NOT NULL DEFAULT '',
`video_img_url` varchar(255) NOT NULL DEFAULT '',
`video_img` varchar(255) NOT NULL DEFAULT '',
`dateline` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `tid` (`tid`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_vote`;
CREATE TABLE `jishigou_topic_vote` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_url`;
CREATE TABLE `jishigou_url` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`key` varchar(10) COLLATE gbk_bin NOT NULL DEFAULT '',
`url` text NOT NULL,
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`open_times` mediumint(8) unsigned NOT NULL DEFAULT '0',
`url_hash` char(32) NOT NULL DEFAULT '',
`title` varchar(100) NOT NULL,
`description` varchar(255) NOT NULL,
`site_id` int(10) unsigned NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `url_hash` (`url_hash`),
UNIQUE KEY `key` (`key`),
KEY `dateline` (`dateline`),
KEY `open_times` (`open_times`),
KEY `site_id` (`site_id`),
FULLTEXT KEY `url` (`url`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_user_medal`;
CREATE TABLE `jishigou_user_medal` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL,
`nickname` char(50) NOT NULL,
`medalid` int(11) NOT NULL,
`is_index` tinyint(4) NOT NULL DEFAULT '1',
`dateline` int(11) NOT NULL,
PRIMARY KEY (`id`),
KEY `uidmedalid` (`uid`,`medalid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_user_tag`;
CREATE TABLE `jishigou_user_tag` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` char(30) NOT NULL DEFAULT '',
`count` int(11) NOT NULL DEFAULT '0',
`type` char(20) NOT NULL DEFAULT '',
`dateline` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `name` (`name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_user_tag_fields`;
CREATE TABLE `jishigou_user_tag_fields` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`tag_id` int(11) NOT NULL DEFAULT '0',
`uid` mediumint(8) NOT NULL DEFAULT '0',
`tag_name` char(50) NOT NULL DEFAULT '',
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_validate_category`;
CREATE TABLE `jishigou_validate_category` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`category_id` int(10) NOT NULL DEFAULT '0',
`category_name` char(20) NOT NULL,
`category_pic` char(200) NOT NULL,
`num` tinyint(4) NOT NULL DEFAULT '0',
`dateline` int(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_validate_category_fields`;
CREATE TABLE `jishigou_validate_category_fields` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) NOT NULL,
`category_fid` int(10) NOT NULL,
`category_id` int(10) NOT NULL,
`province` char(20) NOT NULL,
`city` char(20) NOT NULL,
`validate_info` char(200) NOT NULL,
`is_audit` tinyint(1) NOT NULL DEFAULT '0',
`audit_info` char(200) NOT NULL,
`order` int(10) NOT NULL,
`is_push` tinyint(1) NOT NULL DEFAULT '0',
`dateline` int(10) NOT NULL,
PRIMARY KEY (`id`),
KEY `uid` (`uid`),
KEY `category_fid` (`category_fid`),
KEY `category_id` (`category_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_validate_extra`;
CREATE TABLE `jishigou_validate_extra` (
`id` int(10) NOT NULL,
`data` longtext NOT NULL
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote`;
CREATE TABLE `jishigou_vote` (
`vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`username` char(15) NOT NULL,
`subject` char(80) NOT NULL,
`voter_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`reply_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`maxchoice` tinyint(3) unsigned NOT NULL DEFAULT '0',
`multiple` tinyint(1) unsigned NOT NULL DEFAULT '0',
`is_view` tinyint(1) unsigned NOT NULL DEFAULT '1',
`recd` tinyint(1) unsigned NOT NULL DEFAULT '0',
`expiration` int(10) unsigned NOT NULL DEFAULT '0',
`lastvote` int(10) unsigned NOT NULL DEFAULT '0',
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`postip` char(15) NOT NULL DEFAULT '',
`item` char(15) NOT NULL DEFAULT '',
`item_id` int(10) NOT NULL DEFAULT '0',
`verify` tinyint(1) NOT NULL DEFAULT '1',
`tab` tinyint(1) NOT NULL default '0',
`time_val` int(10) unsigned NOT NULL DEFAULT '0',
`time_unit` enum('s','i','h','d','m','y') NOT NULL DEFAULT 'h',
`vote_limit` tinyint(1) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`vid`),
KEY `uid` (`uid`),
KEY `dateline` (`dateline`),
KEY `lastvote` (`lastvote`),
KEY `voter_num` (`voter_num`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote_image`;
CREATE TABLE `jishigou_vote_image` (
`id` int(10) NOT NULL auto_increment,
`uid` mediumint(8) NOT NULL default '0',
`vid` int(10) NOT NULL default '0',
`picurl` char(255) NOT NULL,
`picurl_big` char(255) NOT NULL,
`dateline` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `vid` (`vid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote_field`;
CREATE TABLE `jishigou_vote_field` (
`vid` int(10) unsigned NOT NULL,
`message` text NOT NULL,
`option` text NOT NULL,
`img` char(250) NOT NULL DEFAULT '',
PRIMARY KEY (`vid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote_option`;
CREATE TABLE `jishigou_vote_option` (
`oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`vid` int(10) unsigned NOT NULL DEFAULT '0',
`vote_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
`option` varchar(100) NOT NULL,
`pid` int(10) NOT NULL default '0',
PRIMARY KEY (`oid`),
KEY `vid` (`vid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote_user`;
CREATE TABLE `jishigou_vote_user` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`username` varchar(50) NOT NULL,
`vid` int(10) unsigned NOT NULL,
`option` text NOT NULL,
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`follow_vote` tinyint(1) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `uid_vid` (`uid`,`vid`),
KEY `dateline` (`dateline`),
KEY `username` (`username`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_vote_user_lottery`;
CREATE TABLE `jishigou_vote_user_lottery` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`vid` int(10) unsigned NOT NULL DEFAULT '0',
`uids` text NOT NULL,
`dateline` int(10) unsigned NOT NULL DEFAULT '0',
`option` text NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_wall`;
CREATE TABLE `jishigou_wall` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`status` tinyint(1) unsigned NOT NULL DEFAULT '1',
`show_mod` tinyint(1) NOT NULL,
`wall_reload_time` tinyint(1) unsigned NOT NULL DEFAULT '5',
`last_load_time` int(10) unsigned NOT NULL,
`last_load_tid` int(10) unsigned NOT NULL,
`auto_wall_tid` int(10) unsigned NOT NULL,
`auto_wall_tag` char(255) NOT NULL,
`screen_ad_top` text NOT NULL,
`screen_ad_left` text NOT NULL,
`screen_ad_right` text NOT NULL,
PRIMARY KEY (`id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_wall_draft`;
CREATE TABLE `jishigou_wall_draft` (
`wall_id` int(10) unsigned NOT NULL,
`tid` int(10) unsigned NOT NULL,
`mark` tinyint(1) unsigned NOT NULL,
PRIMARY KEY (`wall_id`,`tid`,`mark`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_wall_material`;
CREATE TABLE `jishigou_wall_material` (
`wall_id` int(10) unsigned NOT NULL,
`type` tinyint(1) unsigned NOT NULL,
`key` char(255) NOT NULL,
PRIMARY KEY (`wall_id`,`type`,`key`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_wall_playlist`;
CREATE TABLE `jishigou_wall_playlist` (
`wall_id` int(10) unsigned NOT NULL,
`tid` int(10) unsigned NOT NULL,
`order` int(10) unsigned NOT NULL,
PRIMARY KEY (`wall_id`,`tid`),
KEY `order` (`order`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_xwb_bind_info`;
CREATE TABLE `jishigou_xwb_bind_info` (
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
`sina_uid` bigint(20) unsigned NOT NULL DEFAULT '0',
`token` char(32) NOT NULL DEFAULT '',
`tsecret` char(32) NOT NULL DEFAULT '',
`profile` text NOT NULL,
`share_time` int(10) NOT NULL DEFAULT '0',
`last_read_time` int(10) unsigned NOT NULL DEFAULT '0',
`last_read_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`name` char(100) NOT NULL,
`screen_name` char(100) NOT NULL,
`domain` char(100) NOT NULL,
`avatar_large` char(100) NOT NULL,
`access_token` char(32) NOT NULL,
`expires_in` int(10) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL,
`last_pm_time` int(10) unsigned NOT NULL,
PRIMARY KEY (`uid`),
UNIQUE KEY `sina_uid` (`sina_uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_xwb_bind_topic`;
CREATE TABLE `jishigou_xwb_bind_topic` (
`tid` bigint(20) unsigned NOT NULL DEFAULT '0',
`mid` char(50) NOT NULL DEFAULT '',
`last_read_time` int(10) unsigned NOT NULL DEFAULT '0',
KEY `tid` (`tid`),
KEY `mid` (`mid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_yy_bind_info`;
CREATE TABLE `jishigou_yy_bind_info` (
`uid` mediumint(8) unsigned NOT NULL,
`yy_uid` char(32) NOT NULL default '',
`yy_no` char(32) NOT NULL,
`yy_nick` char(64) NOT NULL,
`yy_email` char(64) NOT NULL,
`yy_real_id` char(32) NOT NULL,
`yy_real_name` char(64) NOT NULL,
`token` char(32) NOT NULL,
`token_time` int(10) NOT NULL,
`token_expire` int(10) NOT NULL,
`dateline` int(10) unsigned NOT NULL,
PRIMARY KEY  (`uid`),
UNIQUE KEY `yy_uid` (`yy_uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_reward`;
CREATE TABLE `jishigou_reward` (
`id` int(10) unsigned NOT NULL auto_increment,
`tid` int(10) unsigned NOT NULL default '0',
`uid` mediumint(8) unsigned NOT NULL default '0',
`event_image` int(10) unsigned default '0',
`title` char(50) NOT NULL default '',
`fromt` int(10) unsigned NOT NULL default '0',
`tot` int(10) unsigned NOT NULL default '0',
`content` text NOT NULL,
`prize` text NOT NULL,
`rules` text NOT NULL,
`image` int(10) NOT NULL default '0',
`posttime` int(10) unsigned NOT NULL default '0',
`postip` int(10) unsigned NOT NULL default '0',
`recd` tinyint(1) NOT NULL default '0',
`verify` tinyint(1) NOT NULL default '1',
`f_num` int(10) unsigned NOT NULL default '0',
`a_num` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `uid` (`uid`,`posttime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_reward`;
CREATE TABLE `jishigou_topic_reward` (
`tid` int(10) unsigned NOT NULL default '0',
`item_id` int(10) unsigned NOT NULL default '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY  (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_reward_win_user`;
CREATE TABLE `jishigou_reward_win_user` (
`id` int(10) unsigned NOT NULL auto_increment,
`uid` mediumint(8) unsigned NOT NULL,
`rid` int(10) unsigned NOT NULL default '0',
`pid` int(3) unsigned NOT NULL,
`dateline` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `uid` (`uid`),
KEY `rid` (`rid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_reward_image`;
CREATE TABLE `jishigou_reward_image` (
`id` int(10) unsigned NOT NULL auto_increment,
`uid` mediumint(8) unsigned NOT NULL default '0',
`rid` int(10) unsigned NOT NULL default '0',
`image` char(255) NOT NULL default '',
PRIMARY KEY  (`id`),
KEY `rid` (`rid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_reward_user`;
CREATE TABLE `jishigou_reward_user` (
`id` int(10) unsigned NOT NULL auto_increment,
`uid` mediumint(8) unsigned NOT NULL,
`tid` int(10) unsigned NOT NULL default '0',
`rid` int(10) unsigned NOT NULL default '0',
`on` tinyint(1) NOT NULL default '0',
`dateline` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;





DROP TABLE IF EXISTS `jishigou_bulletin`;
CREATE TABLE `jishigou_bulletin` (
`id` int(10) unsigned NOT NULL auto_increment,
`type` tinyint(3) NOT NULL default '0',
`cpid` int(10) unsigned NOT NULL default '0',
`uid` mediumint(8) unsigned NOT NULL default '0',
`username` char(100) NOT NULL default '',
`message` text NOT NULL,
`dateline` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_ad`;
CREATE TABLE `jishigou_ad` (
`adid` int(10) unsigned NOT NULL auto_increment,
`location` char(20) NOT NULL default '',
`title` char(100) NOT NULL default '',
`type` tinyint(1) NOT NULL default '1',
`ftime` int(10) NOT NULL default '0',
`ttime` int(10) NOT NULL default '0',
`hcode` text NOT NULL,
PRIMARY KEY  (`adid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_feed_log`;
CREATE TABLE `jishigou_feed_log` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(10) NOT NULL DEFAULT '0',
`nickname` char(50) NOT NULL DEFAULT '',
`item` char(30) NOT NULL DEFAULT '',
`item_id` int(10) NOT NULL DEFAULT '0',
`tid` int(10) NOT NULL DEFAULT '0',
`action` char(20) NOT NULL DEFAULT '',
`msg` char(200) NOT NULL DEFAULT '',
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_channel`;
CREATE TABLE `jishigou_channel` (
`ch_id` smallint(6) unsigned NOT NULL auto_increment,
`ch_name` char(15) NOT NULL default '',
`feed` tinyint(1) NOT NULL DEFAULT '0',
`recommend` tinyint(1) NOT NULL DEFAULT '0',
`topic_num` int(10) unsigned NOT NULL default '0',
`total_topic_num` int(10) unsigned NOT NULL default '0',
`parent_id` smallint(6) unsigned NOT NULL default '0',
`description` text NOT NULL,
`purview` text NOT NULL,
`purpostview` text NOT NULL,
`verify` tinyint(1) NOT NULL default '0',
`filter` text NOT NULL,
`display_order` smallint(6) unsigned NOT NULL default '0',
`display_list` varchar(4) NOT NULL,
`display_view` varchar(4) NOT NULL,
`in_home` tinyint(1) unsigned NOT NULL default '0',
`picture` char(200) NOT NULL default '',
`buddy_numbers` int(10) unsigned NOT NULL default '0',
`manageid` char(200) NOT NULL DEFAULT '',
`managename` char(250) NOT NULL DEFAULT '',
`template` char(50) NOT NULL DEFAULT '',
`channel_typeid` smallint(4) NOT NULL DEFAULT '0',
`topictype` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY  (`ch_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_channel`;
CREATE TABLE `jishigou_topic_channel` (
`tid` int(10) unsigned NOT NULL default '0',
`item_id` int(10) unsigned NOT NULL default '0',
`uid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY  (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_buddy_channel`;
CREATE TABLE `jishigou_buddy_channel` (
`id` int(10) NOT NULL auto_increment,
`uid` mediumint(8) unsigned NOT NULL,
`ch_id` int(10) unsigned NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_feature`;
CREATE TABLE `jishigou_feature` (
`featureid` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`featurename` char(100) NOT NULL DEFAULT '',
PRIMARY KEY (`featureid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_job`;
CREATE TABLE `jishigou_job` (
`jobid` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`jobname` char(100) NOT NULL DEFAULT '',
PRIMARY KEY (`jobid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_channel_type`;
CREATE TABLE `jishigou_channel_type` (
`channel_typeid` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`channel_type` char(20) NOT NULL DEFAULT '',
`channel_typename` char(100) NOT NULL DEFAULT '',
`template` char(100) NOT NULL DEFAULT '',
`child_template` char(100) NOT NULL,
`topic_template` char(100) NOT NULL,
`featureid` char(250) NOT NULL DEFAULT '',
`default_feature` char(100) NOT NULL DEFAULT '',
PRIMARY KEY (`channel_typeid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cms_category`;
CREATE TABLE `jishigou_cms_category` (
`catid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
`catname` char(50) NOT NULL DEFAULT '',
`articles` int(10) unsigned NOT NULL DEFAULT '0',
`parentid` smallint(6) unsigned NOT NULL DEFAULT '0',
`upid` char(200) NOT NULL DEFAULT '0',
`likecatid` char(200) NOT NULL DEFAULT '0',
`description` varchar(250) NOT NULL DEFAULT '',
`purview` char(250) NOT NULL DEFAULT '',
`verify` tinyint(1) NOT NULL DEFAULT '0',
`filter` char(250) NOT NULL DEFAULT '',
`displayorder` smallint(6) unsigned NOT NULL DEFAULT '0',
`manageid` char(200) NOT NULL DEFAULT '',
`managename` char(250) NOT NULL DEFAULT '',
`template` char(50) NOT NULL DEFAULT '',
PRIMARY KEY (`catid`),
KEY `parentid` (`parentid`),
KEY `likecatid` (`likecatid`),
KEY `displayorder` (`displayorder`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cms_article`;
CREATE TABLE `jishigou_cms_article` (
`aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`catid` smallint(6) unsigned NOT NULL DEFAULT '0',
`likecatid` char(200) NOT NULL DEFAULT '0',
`title` char(200) NOT NULL DEFAULT '',
`description` char(250) NOT NULL DEFAULT '',
`content` text,
`uid` mediumint(8) NOT NULL DEFAULT '0',
`username` char(50) NOT NULL DEFAULT '',
`imageid` char(100) NOT NULL DEFAULT '',
`attachid` char(100) NOT NULL DEFAULT '',
`dateline` int(10) NOT NULL DEFAULT '0',
`lastupdate` int(10) NOT NULL DEFAULT '0',
`check` tinyint(1) NOT NULL DEFAULT '0',
`checkname` char(50) NOT NULL DEFAULT '',
`checktime` int(10) NOT NULL DEFAULT '0',
`replys` smallint(4) NOT NULL DEFAULT '0',
`likemanageid` char(200) NOT NULL DEFAULT '',
PRIMARY KEY (`aid`),
KEY `likecatid` (`likecatid`),
KEY `likemanageid` (`likemanageid`),
KEY `check` (`check`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_cms_reply`;
CREATE TABLE `jishigou_cms_reply` (
`rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
`aid` int(10) unsigned NOT NULL DEFAULT '0',
`content` char(250) NOT NULL DEFAULT '',
`uid` mediumint(8) NOT NULL DEFAULT '0',
`username` char(50) NOT NULL DEFAULT '',
`dateline` int(10) NOT NULL DEFAULT '0',
PRIMARY KEY (`rid`),
KEY `aid` (`aid`),
KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_dig`;
CREATE TABLE `jishigou_topic_dig` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`tid` int(10) UNSIGNED NOT NULL default '0',
`uid` mediumint(8) UNSIGNED NOT NULL default '0',
`touid` int(10) UNSIGNED NOT NULL default '0',
`dateline` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `tid` (`tid`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_members_vest`;
CREATE TABLE `jishigou_members_vest` (
`uid` mediumint(8) unsigned NOT NULL,
`useruid` mediumint(8) unsigned NOT NULL,
PRIMARY KEY  (`uid`,`useruid`),
KEY `useruid` (`useruid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_wechat`;
CREATE TABLE `jishigou_wechat` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`wechat_id` char(60) NOT NULL,
`jsg_id` int(11) NOT NULL,
`dateline` INT(10) UNSIGNED NOT NULL,
PRIMARY KEY (`id`),
KEY `wechat_id` (`wechat_id`),
KEY `jsg_id` (`jsg_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_mall_goods`;
CREATE TABLE `jishigou_mall_goods` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`sn` char(30) NOT NULL,
`name` char(200) NOT NULL,
`price` int(10) unsigned NOT NULL,
`credit` int(10) unsigned NOT NULL,
`desc` text NOT NULL,
`image` char(200) NOT NULL,
`dateline` int(10) NOT NULL,
`last_uid` int(10) unsigned NOT NULL,
`last_update` int(10) NOT NULL,
`total` int(10) unsigned NOT NULL,
`order_count` int(10) unsigned NOT NULL,
`seal_count` int(10) unsigned NOT NULL,
`click_count` int(10) unsigned NOT NULL,
`order` int(10) unsigned NOT NULL DEFAULT '100',
`expire` int(10) unsigned NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_mall_order`;
CREATE TABLE `jishigou_mall_order` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`sn` CHAR(30) NOT NULL,
`uid` INT(10) UNSIGNED NOT NULL,
`username` varchar(50) NOT NULL DEFAULT '',
`goods_id` INT(10) UNSIGNED NOT NULL,
`goods_name` varchar(100) NOT NULL DEFAULT '',
`goods_num` INT(10) UNSIGNED NOT NULL,
`goods_price` DECIMAL(10,2) NOT NULL,
`goods_credit` INT(10) UNSIGNED NOT NULL,
`pay_price` DECIMAL(10,2) NOT NULL,
`pay_credit` INT(10) UNSIGNED NOT NULL,
`address` CHAR(250) NOT NULL,
`tel` CHAR(100) NOT NULL,
`qq` CHAR(100) NOT NULL,
`mobile` CHAR(100) NOT NULL,
`add_time` INT(10) UNSIGNED NOT NULL,
`confirm_time` INT(10) UNSIGNED NOT NULL,
`pay_time` INT(10) UNSIGNED NOT NULL,
`status` TINYINT(1) UNSIGNED NOT NULL,
PRIMARY KEY (`id`),
KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_mall_order_action`;
CREATE TABLE `jishigou_mall_order_action` (
`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`uid` INT(10) UNSIGNED NOT NULL,
`order_id` INT(10) UNSIGNED NOT NULL,
`status` TINYINT(1) UNSIGNED NOT NULL,
`msg` CHAR(250) NOT NULL,
`dateline` INT(10) UNSIGNED NOT NULL,
PRIMARY KEY (`id`),
KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_login_log`;
CREATE TABLE `jishigou_login_log` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`uid` int(10) unsigned NOT NULL,
`user_nickname` char(100) NOT NULL,
`ip` char(15) NOT NULL,
`ip_port` char(6) DEFAULT NULL,
`dateline` int(11) NOT NULL,
`time` char(30) NOT NULL,
`type` char(100) NOT NULL,
PRIMARY KEY (`id`),
KEY `time` (`time`),
KEY `ip` (`ip`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_topic_image`;
CREATE TABLE `jishigou_topic_topic_image` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_topic_mall`;
CREATE TABLE `jishigou_topic_mall` (
`tid` int(10) unsigned NOT NULL DEFAULT '0',
`item_id` int(10) unsigned NOT NULL DEFAULT '0',
`uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
PRIMARY KEY (`tid`,`item_id`),
KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `jishigou_ios`;
CREATE TABLE `jishigou_ios` (
`id` bigint(20) NOT NULL AUTO_INCREMENT,
`uid` mediumint(8) unsigned NOT NULL,
`token` varchar(64) NOT NULL,
PRIMARY KEY (`id`),
KEY `token` (`token`)
) ENGINE=MyISAM;

REPLACE INTO `jishigou_role`(`id`,`name`,`creditshigher`,`creditslower`,`privilege`,`type`,`rank`,`icon`,`allow_sendpm_to`,`allow_sendpm_from`,`allow_topic_forward_to`,`allow_topic_forward_from`,`allow_topic_reply_to`,`allow_topic_reply_from`,`allow_topic_at_to`,`allow_topic_at_from`,`allow_follow_to`,`allow_follow_from`,`system`) values (1,'游客',0,0,'273,274,275,281,282,283,284,285,286,288,289,290,291,292,293,295,297,299,300,303,448,472','normal',0,'','','','','','','','','','','',1),(2,'管理员',0,0,'7,8,9,11,13,84,88,90,91,92,93,101,102,106,107,108,111,115,123,125,126,132,133,134,135,136,137,138,139,154,158,159,169,172,176,182,183,184,186,187,188,189,190,191,192,193,194,195,196,197,198,200,201,202,204,205,206,208,210,211,212,213,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,267,268,269,270,271,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,304,305,306,307,308,311,315,317,318,319,320,321,323,324,325,327,328,329,330,340,361,363,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,400,401,403,404,405,406,407,408,409,410,411,412,413,415,416,417,418,419,420,421,422,424,425,426,427,428,429,430,431,432,435,443,445,446,447,448,449,450,451,452,456,461,464,466,470,471,472,474,475,476,477,478,479,480,481,482,483,484,485,486,487,488,489,490,492,493,494,495,496,497,502,503,504,505,506,507,508,509,510,511,512,513,514,516,517,518,519,520,521,523,524,525,526,527,528,529,530,531,533,534,535,536,537,538,539,540,541,542,543,544,545,546,547,548,551,553,554,561,566,567,570,571,572,573,574,575,576,577,578,579,580,581,582,583,584,585,586,587,588,589,590,591,592,593,594,595,596,597,598,599,600,601,602,603,604,605,606,607,608,609,610,611,612,613,614,615,616,617,618,619,620,621,622,623,624,625,626,627,628,629','admin',0,'./images/role/2.gif','0','','0','','0','','0','','0','',1),(3,'普通会员',0,20,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,448,472','normal',0,'./images/role/3.gif','0','','0','','0','','0','','0','',1),(4,'禁言组',0,0,'','normal',0,'./images/role/4.gif','0','','0','','0','','0','','0','',1),(5,'待验证会员',0,0,'273,274,275,281,282,283,284,285,288,289,290,291,292,293,294,295,297,299,300,303,448,472','normal',0,'./images/role/5.gif','0','','0','','0','','0','','0','',1),(7,'后台查看',0,0,'7,8,11,13,88,92,125,126,133,135,137,158,176,193,194,195,196,197,198,200,202,205,211,213,215,216,217,218,219,220,221,222,223,226,228,230,235,236,238,241,246,250,251,252,253,254,255,264,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,304,305,306,311,315,319,323,330,340,361,374,377,378,379,380,381,382,383,384,386,387,388,391,392,393,394,395,401,403,404,405,406,407,410,411,413,418,421,424,425,429,435,443,446,447,448,449,450,470,471,472,474,477,478,481,484,486,488,492,497,502,503,504,505,506,507,508,509,510,511,512,513,514,516,517,518,519,520,521,523','admin',0,'','0','','0','','0','','0','','0','',1),(118,'封号',0,0,'','normal',0,'./images/role/108.gif','','','','','','','','','','',1),(108,'LV1会员',20,300,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,377,378,448,472','normal',1,'','0','','0','','0','','0','','0','',1),(109,'LV2会员',300,1000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',2,'','0','','0','','0','','0','','0','',1),(110,'LV3会员',1000,3000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',3,'','','','','','','','','','','',1),(111,'LV4会员',3000,6000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',4,'','','','','','','','','','','',1),(112,'LV5会员',6000,12000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',5,'','','','','','','','','','','',1),(113,'LV6会员',12000,20000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',6,'','','','','','','','','','','',1),(114,'LV7会员',20000,30000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',7,'','','','','','','','','','','',1),(115,'LV8会员',30000,45000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',8,'','','','','','','','','','','',1),(116,'LV9会员',45000,60000,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',9,'','','','','','','','','','','',1),(117,'LV10会员',60000,0,'7,176,193,194,195,196,197,198,273,274,275,276,281,282,283,284,285,286,288,289,290,291,292,293,294,295,296,297,298,299,300,302,303,330,340,377,378,447,448,471,472','normal',10,'','','','','','','','','','','',1);
REPLACE INTO `jishigou_role_action`(`id`,`name`,`module`,`action`,`describe`,`message`,`allow_all`,`credit_require`,`credit_update`,`log`,`is_admin`) values (7,'发新微博','topic','add|do_add','','',0,'','',0,0),(8,'查看角色列表','role','list','','',0,'','',0,1),(9,'修改角色权限','role','domodify','','',0,'','',0,1),(11,'所有动作列表','role_action','list','','',0,'','',0,1),(13,'查看核心设置','setting','modify_normal','','',0,'','',0,1),(84,'修改友情链接设置','link','domodify','','',0,'','',0,1),(88,'进入后台','index','*','','',0,'','',0,1),(90,'提交数据库备份','db','doexport','','',0,'','',0,1),(91,'修改核心设置','setting','domodify_normal','','',0,'','',0,1),(92,'查看URL地址设置','setting','modify_rewrite','','',0,'','',0,1),(93,'修改URL地址设置','setting','domodify_rewrite','','',0,'','',0,1),(101,'修改动作设置','role_action','domodify','','',0,'','',0,1),(102,'删除动作','role_action','delete','','',0,'','',0,1),(106,'修改界面显示设置','show','domodify','','',0,'','',0,1),(107,'解压缩数据备份包','db','importzip','','',0,'','',0,1),(108,'开关蜘蛛统计','robot','domodify','','',0,'','',0,1),(111,'修改内容过滤设置','setting','domodify_filter','','',0,'','',0,1),(115,'修改IP访问控制','setting','domodify_access','','',0,'','',0,1),(123,'执行数据库优化','db','dooptimize','','',0,'','',0,1),(125,'查看内容过滤设置','setting','modify_filter','','',0,'','',0,1),(126,'查看IP访问控制','setting','modify_access','','',0,'','',0,1),(132,'导入数据恢复','db','doimport','','',0,'','',0,1),(133,'查看数据恢复页面','db','import','','',0,'','',0,1),(134,'删除数据库备份','db','dodelete','','',0,'','',0,1),(135,'查看UC整合配置','ucenter','ucenter','','',0,'','',0,1),(136,'修改UC整合配置','ucenter','do_setting','','',0,'','',0,1),(137,'查看角色权限','role','modify','','',0,'','',0,1),(138,'分模块动作列表','role_action','list_action','','',0,'','',0,1),(139,'动作设置','role_action','modify|domodify','','',0,'','',0,1),(154,'修改邮件发送设置','setting','do_modify_smtp','','',0,'','',0,1),(158,'清空缓存','cache','*','','',0,'','',0,1),(159,'系统后台升级','upgrade','*','','',0,'','',0,1),(169,'禁止搜索引擎','robot','disallow0|disallow1','','',0,'','',0,1),(172,'执行举报管理','report','batch_process','','',0,'','',0,1),(176,'发送私信','pm','send|dosend','','',0,'','',0,0),(182,'执行编辑微博','topic','domodify','','',0,'','',0,1),(183,'执行删除微博','topic','delete','','',0,'','',0,1),(184,'删除私信','pm','delete','','',0,'','',0,1),(186,'执行删除话题操作','tag','delete','','',0,'','',0,1),(187,'执行添加用户操作','member','doadd','','',0,'','',0,1),(188,'搜索用户页面','member','search','','',0,'','',0,1),(189,'修改广告设置','income','domodify','','',0,'','',0,1),(190,'添加公告','notice','add','','',0,'','',0,1),(191,'执行编辑公告操作','notice','domodify','','',0,'','',0,1),(192,'添加角色','role','add|doadd','','',0,'','',0,1),(193,'读取新浪微博内容','xwb','__synctopic','','',0,'','',0,0),(194,'读取新浪微博评论','xwb','__syncreply','','',0,'','',0,0),(195,'发起新的投票','vote','create','','',0,'','',0,0),(196,'进行投票','vote','vote','','',0,'','',0,0),(197,'转发微博','topic','forward','','',0,'','',0,0),(198,'评论微博','topic','reply','','',0,'','',0,0),(200,'查看积分设置','setting','modify_credits','','',0,'','',0,1),(201,'修改积分设置','setting','do_modify_credits','','',0,'','',0,1),(202,'查看积分规则列表','setting','list_credits_rule','','',0,'','',0,1),(204,'修改积分规则','setting','do_modify_credits_rule','','',0,'','',0,1),(205,'查看某项积分规则','setting','modify_credits_rule','','',0,'','',0,1),(206,'删除站外调用','share','delete','','',0,'','',0,1),(208,'添加站外调用','share','add|do_add','','',0,'','',0,1),(210,'编辑站外调用','share','modify|domodify','','',0,'','',0,1),(211,'查看QQ微博设置','setting','modify_qqwb','','',0,'','',0,1),(212,'修改QQ微博设置','setting','do_modify_qqwb','','',0,'','',0,1),(213,'查看新浪微博设置','setting','modify_sina','','',0,'','',0,1),(215,'查看邮件发送设置','setting','modify_smtp','','',0,'','',0,1),(216,'查看远程附件设置','setting','modify_ftp','','',0,'','',0,1),(217,'查看推荐话题','setting','modify_hot_tag_recommend','','',0,'','',0,1),(218,'查看首页幻灯管理','setting','modify_slide_index','','',0,'','',0,1),(219,'查看内页幻灯管理','setting','modify_slide','','',0,'','',0,1),(220,'编辑用户页面','member','modify','','',0,'','',0,1),(221,'添加用户页面','member','add','','',0,'','',0,1),(222,'查看数据备份页面','db','export','','',0,'','',0,1),(223,'查看数据表优化页面','db','optimize','','',0,'','',0,1),(224,'修改新浪微博设置','setting','do_modify_sina','','',0,'','',0,1),(225,'修改QQ机器人设置','imjiqiren','do_modify_setting','','',0,'','',0,1),(226,'查看短信发送记录','sms','send_log','','',0,'','',0,1),(227,'修改手机短信设置','sms','do_modify_setting','','',0,'','',0,1),(228,'查看短信接收记录','sms','receive_log','','',0,'','',0,1),(229,'删除短信收发记录','sms','delete_log','','',0,'','',0,1),(230,'查看微博列表','topic','modifylist','','',0,'','',0,1),(231,'修改推荐话题','setting','do_modify_hot_tag_recommend','','',0,'','',0,1),(232,'修改首页幻灯设置','setting','do_modify_slide_index','','',0,'','',0,1),(233,'修改内页幻灯设置','setting','do_modify_slide','','',0,'','',0,1),(234,'修改多个计划任务','task','dobatchmodify','','',0,'','',0,1),(235,'查看计划任务','task','list','','',0,'','',0,1),(236,'查看单个计划任务','task','modify','','',0,'','',0,1),(237,'修改单个计划任务','task','domodify','','',0,'','',0,1),(238,'查看计划任务记录','task','log_list','','',0,'','',0,1),(239,'删除计划任务','task','log_delete','','',0,'','',0,1),(240,'修改远程附件设置','setting','do_modify_ftp','','',0,'','',0,1),(241,'查看投票','vote','edit','','',0,'','',0,1),(242,'修改投票','vote','doedit','','',0,'','',0,1),(243,'删除投票','vote','delete','','',0,'','',0,1),(244,'添加个人标签','user_tag','add','','',0,'','',0,1),(245,'删除个人标签','user_tag','delete','','',0,'','',0,1),(246,'查看个人标签','user_tag','modify','','',0,'','',0,1),(247,'修改个人标签','user_tag','domodify','','',0,'','',0,1),(248,'修改关于我们设置','web_info','domodify','','',0,'','',0,1),(249,'执行删除公告操作','notice','delete','','',0,'','',0,1),(250,'查看编辑公告页面','notice','modify','','',0,'','',0,1),(251,'查看微群分类','qun','category','','',0,'','',0,1),(252,'查看微群等级','qun','level','','',0,'','',0,1),(253,'查看微群策略','qun','ploy','','',0,'','',0,1),(254,'查看微群管理','qun','manage','','',0,'','',0,1),(255,'查看微群设置','qun','setting','','',0,'','',0,1),(256,'修改微群设置','qun','dosetting','','',0,'','',0,1),(257,'修改微群分类','qun','docategory','','',0,'','',0,1),(258,'修改微博等级','qun','dolevel','','',0,'','',0,1),(259,'修改微群策略','qun','doploy','','',0,'','',0,1),(260,'执行微群管理','qun','domanage','','',0,'','',0,1),(261,'用户搜索结果','member','dosearch','','',0,'','',0,1),(262,'修改用户信息','member','domodify','','',0,'','',0,1),(263,'执行删除用户操作','member','delete','','',0,'','',0,1),(264,'查看在线用户','sessions','search','','',0,'','',0,1),(265,'添加勋章','medal','add','','',0,'','',0,1),(267,'修改勋章','medal','modify|domodify','','',0,'','',0,1),(268,'删除勋章','medal','delete','','',0,'','',0,1),(269,'添加V认证','vipintro','add','','',0,'','',0,1),(270,'取消V认证','vipintro','open_validate','','',0,'','',0,1),(271,'修改V认证','vipintro','modify','','',0,'','',0,1),(273,'查看我的首页','topic','myhome','','',0,'','',0,0),(274,'查看TA关注的用户','topic','follow','','',0,'','',0,0),(275,'查看关注TA的用户','topic','fans','','',0,'','',0,0),(276,'设置我的模板（样式、换肤）','skin','do_modify','','',0,'','',0,0),(281,'查看昵称找人页面','search','user','','',0,'','',0,0),(282,'查看标签找人页面','search','usertag','','',0,'','',0,0),(283,'查看关键词找微博页面','search','topic','','',0,'','',0,0),(284,'查看话题找微博页面','search','tag','','',0,'','',0,0),(285,'查看关键词找投票页面','search','vote','','',0,'','',0,0),(286,'个人微博秀调用','show','show','','',0,'','',0,0),(288,'查看媒体汇页面','other','media','','',0,'','',0,0),(289,'查看达人榜页面','topic','top','','',0,'','',0,0),(290,'查看最新发布的微博','topic','new','','',0,'','',0,0),(291,'查看最新评论的微博','topic','newreply','','',0,'','',0,0),(292,'查看我的勋章页面','settings','user_medal','','',0,'','',0,0),(293,'查看我的资料页面','settings','base','','',0,'','',0,0),(294,'执行修改个人资料操作','settings','do_modify_profile','','',0,'','',0,0),(295,'查看修改头像页面','settings','face','','',0,'','',0,0),(296,'执行头像修改操作','settings','do_modify_face','','',0,'','',0,0),(297,'查看修改密码页面','settings','secret','','',0,'','',0,0),(298,'执行修改密码操作','settings','do_modify_password','','',0,'','',0,0),(299,'查看我的积分','settings','extcredits','','',0,'','',0,0),(300,'查看私信列表','pm','list','','',0,'','',0,0),(302,'设置私信为未读','pm','markunread','','',0,'','',0,0),(303,'查看投票详情','vote','view','','',0,'','',0,0),(304,'查看话题扩展','tag','extra','','',0,'','',0,1),(305,'查看话题推介','tag','recommend','','',0,'','',0,1),(306,'查看话题列表','tag','list','','',0,'','',0,1),(307,'编辑话题推介','tag','do_recommend','','',0,'','',0,1),(308,'编辑话题扩展','tag','add_extra','','',0,'','',0,1),(311,'查看导航设置','setting','navigation','','',0,'','',0,1),(315,'查看活动管理','event','manage','','',0,'','',0,1),(317,'修改导航设置','setting','do_navigation','','',0,'','',0,1),(318,'设置默认关注和推介','setting','do_regfollow','','',0,'','',0,1),(319,'查看短信用户','sms','list','','',0,'','',0,1),(320,'导出短信用户','sms','export_to_excel','','',0,'','',0,1),(321,'删除活动','event','delete','','',0,'','',0,1),(323,'查看插件列表','plugin','main','','',0,'','',0,1),(324,'插件安装','plugin','add','','',0,'','',0,1),(325,'设计插件','plugin','design|adddesign','','',0,'','',0,1),(327,'启用插件','plugin','action','','',0,'','',0,1),(328,'卸载插件','plugin','uninstall','','',0,'','',0,1),(329,'导出所有用户','member','export_all_user','','',0,'','',0,1),(330,'使用微博墙功能','wall','control','','',0,'','',0,0),(340,'创建微群','qun','create|docreate','','',0,'','',0,0),(361,'查看分类管理','fenlei','manage','','',0,'','',0,1),(363,'修改API设置','api','do_modify_setting','','',0,'','',0,1),(374,'查看待审核列表','verify','edit|doedit','','',0,'','',0,1),(375,'审核用户信息','verify','doverify','','',0,'','',0,1),(376,'审核微博','topic','doverify','','',0,'','',0,1),(377,'附件上传','uploadattach','attach','','',0,'','',0,0),(378,'附件下载','uploadattach','down','','',0,'','',0,0),(379,'查看注册控制选项','setting','modify_register','','',0,'','',0,1),(380,'查看自动关注设置','setting','regfollow','','',0,'','',0,1),(381,'查看Discuz论坛调用设置','dzbbs','discuz_setting','','',0,'','',0,1),(382,'查看皮肤风格设置','show','modify_theme','','',0,'','',0,1),(383,'查看名人堂设置','vipintro','people_setting','','',0,'','',0,1),(384,'查看微博评论调用列表','output','output_setting','','',0,'','',0,1),(385,'管理微博列表','topic','topic_manage','','',0,'','',0,1),(386,'查看待审微博','topic','verify','','',0,'','',0,1),(387,'查看微博回收站','topic','del','','',0,'','',0,1),(388,'查看微博审核工作','topic','manage','','',0,'','',0,1),(389,'用户签名管理','topic','signature','','',0,'','',0,1),(390,'自我介绍管理','topic','aboutme','','',0,'','',0,1),(391,'查看用户列表','member','newm','','',0,'','',0,1),(392,'查看禁言用户','member','force_out','','',0,'','',0,1),(393,'查看上报领导','member','leaderlist','','',0,'','',0,1),(394,'查看投票列表','vote','index','','',0,'','',0,1),(395,'查看活动主题','event','index','','',0,'','',0,1),(396,'直播管理','live','index','','',0,'','',0,1),(400,'添加动作权限','role_action','add','','',0,'','',0,1),(401,'查看验证码设置','setting','modify_seccode','','',0,'','',0,1),(403,'查看默认关注分组','setting','follow','','',0,'','',0,1),(404,'查看图片上传设置','setting','modify_image','','',0,'','',0,1),(405,'查看发布来源设置','setting','modify_topic_from','','',0,'','',0,1),(406,'查看图片签名档设置','setting','modify_qmd','','',0,'','',0,1),(407,'查看邀请文字说明','setting','invite','','',0,'','',0,1),(408,'修改内容发布来源','setting','do_modify_topic_from','','',0,'','',0,1),(409,'添加直播','live','add','','',0,'','',0,1),(410,'查看直播设置','live','config','','',0,'','',0,1),(411,'查看微博评论调用设置','output','modify','','',0,'','',0,1),(412,'添加微博评论调用','output','add','','',0,'','',0,1),(413,'查看分类信息基本设置','fenlei','setting','','',0,'','',0,1),(415,'私信群发列表','pm','pmsend','','',0,'','',0,1),(416,'执行私信群发','pm','dopmsend','','',0,'','',0,1),(417,'删除群发私信','pm','delmsg','','',0,'','',0,1),(418,'查看私信列表','pm','pm_manage','','',0,'','',0,1),(419,'批量用户V认证操作','vipintro','tuijian','','',0,'','',0,1),(420,'后台操作记录','logs','*','','',0,'','',0,1),(421,'查看模板风格设置','show','modify_template','','',0,'','',0,1),(422,'网站LOGO设置','show','editlogo','','',0,'','',0,1),(424,'查看DeDeCMS调用设置','dedecms','dedecms_setting','','',0,'','',0,1),(425,'查看PHPWind调用设置','phpwind','phpwind_setting','','',0,'','',0,1),(426,'编辑站外调用设置','output','do_modify','','',0,'','',0,1),(427,'删除微博评论调用','output','delete','','',0,'','',0,1),(428,'修改皮肤风格设置','show','domodifytheme','','',0,'','',0,1),(429,'查看站外调用列表','share','share_setting','','',0,'','',0,1),(430,'修改DeDeCMS调用设置','dedecms','dedecms_save','','',0,'','',0,1),(431,'修改PHPWind调用设置','phpwind','phpwind_save','','',0,'','',0,1),(432,'修改Discuz论坛调用设置','dzbbs','dzbbs_save','','',0,'','',0,1),(435,'查看界面显示设置','show','show','','',0,'','',0,1),(443,'后台功能搜索','search','*','','',0,'','',0,1),(445,'后台短信发送','sms','do_send','','',0,'','',0,1),(446,'查看待验证用户','member','waitvalidate','','',0,'','',0,1),(447,'发起新活动','event','create','','',0,'','',0,0),(448,'查看活动','event','detail','','',0,'','',0,0),(449,'查看手机应用设置','setting','modify_mobile','','',0,'','',0,1),(450,'查看V认证分类','vipintro','categorylist','','',0,'','',0,1),(451,'编辑V认证分类','vipintro','domodify','','',0,'','',0,1),(452,'删除V认证分类','vipintro','delcategory','','',0,'','',0,1),(456,'新建群','qun','add','','',0,'','',0,1),(461,'手动添加V认证','vipintro','addvip|doaddvip','','',0,'','',0,1),(464,'马甲管理','member','vest','','',0,'','',0,1),(466,'重置APP SECRET','api','reset_app_secret','','',0,'','',0,1),(470,'推荐微博','topic','do_recd','','',0,'','',0,0),(471,'发起有奖转发','reward','add','','',0,'','',0,0),(472,'查看有奖转发详情','reward','detail','','',0,'','',0,0),(474,'查看通知中的邮件队列','notice','mailq','','',0,'','',0,1),(475,'用户访问记录','member','login','','',0,'','',0,1),(476,'查看用户栏目可选必填项','member','profile','','',0,'','',0,1),(477,'进入访谈管理页面','talk','*','','',0,'','',0,1),(478,'查看具体频道信息','channel','index','','',0,'','',0,1),(479,'修改签到设置','sign','dosetting','','',0,'','',0,1),(480,'签到积分排行','sign','sign_list','','',0,'','',0,1),(481,'查看签到设置','sign','*','','',0,'','',0,1),(482,'设置用户栏目可选必填项','member','setprofile','','',0,'','',0,1),(483,'修改具体频道信息','channel','docategory','','',0,'','',0,1),(484,'查看频道设置','channel','config','','',0,'','',0,1),(485,'修改频道设置','channel','doset','','',0,'','',0,1),(486,'查看访谈设置','talk','config','','',0,'','',0,1),(487,'修改访谈设置','talk','doconfig','','',0,'','',0,1),(488,'查看访谈分类','talk','category','','',0,'','',0,1),(489,'修改访谈分类','talk','docategory','','',0,'','',0,1),(490,'添加访谈','talk','add|doadd','','',0,'','',0,1),(492,'查看单个访谈的具体信息','talk','edit','','',0,'','',0,1),(493,'修改单个访谈的具体信息','talk','doedit','','',0,'','',0,1),(494,'删除访谈','talk','delete','','',0,'','',0,1),(495,'删除邮件队列中的内容','notice','delMailQueue','','',0,'','',0,1),(496,'系统负载设置','setting','modify_sysload|do_wqueue','','',0,'','',0,1),(497,'查看群模板设置','qun','module','','',0,'','',0,1),(502,'查看投票设置','vote','setting','','',0,'','',0,1),(503,'查看待审核投票','vote','verify','','',0,'','',0,1),(504,'查看活动设置','event','setting','','',0,'','',0,1),(505,'查看待审核活动','event','verify','','',0,'','',0,1),(506,'查看用户绑定情况','account','index','','',0,'','',0,1),(507,'查看YY绑定设置','account','yy','','',0,'','',0,1),(508,'查看人人绑定设置','account','renren','','',0,'','',0,1),(509,'查看开心绑定设置','account','kaixin','','',0,'','',0,1),(510,'查看QQ机器人设置','imjiqiren','imjiqiren_setting','','',0,'','',0,1),(511,'查看积分排行用户列表','sign','credits_top','','',0,'','',0,1),(512,'查看网站关键词设置','setting','modify_meta','','',0,'','',0,1),(513,'查看发布框设置','setting','topic_publish','','',0,'','',0,1),(514,'查看前台文字替换设置','setting','changeword','','',0,'','',0,1),(516,'查看所有广告','income','ad_list','','',0,'','',0,1),(517,'查看头像签名待审核用户','verify','fs_verify','','',0,'','',0,1),(518,'查看个人标签管理页面','user_tag','user_tag_manage','','',0,'','',0,1),(519,'查看举报管理','report','report_manage','','',0,'','',0,1),(520,'进入官方推荐微博管理页面','recdtopic','*','','',0,'','',0,1),(521,'查看V认证设置','vipintro','validate_setting','','',0,'','',0,1),(523,'查看马甲设置','member','config','','',0,'','',0,1),(524,'设置第三方帐号绑定开关','account','on_off','','',0,'','',0,1),(525,'删除广告','income','dodeladv','','',0,'','',0,1),(526,'修改前台文字替换设置','setting','dochangeword','','',0,'','',0,1),(527,'修改发布框设置','setting','do_topic_publish','','',0,'','',0,1),(528,'修改马甲设置','member','setvest','','',0,'','',0,1),(529,'管理待审核投票','vote','doverify','','',0,'','',0,1),(530,'修改投票设置','vote','dosetting','','',0,'','',0,1),(531,'批量投票管理','vote','batch','','',0,'','',0,1),(533,'编辑一个活动','event','editevent|doeditevent','','',0,'','',0,1),(534,'删除推荐的微博','recdtopic','delete','','',0,'','',0,1),(535,'执行一键清理过期推荐的微博','recdtopic','onekey','','',0,'','',0,1),(536,'编辑一条官方推荐的微博','recdtopic','edit|doedit','','',0,'','',0,1),(537,'修改YY绑定设置','account','do_modify_yy','','',0,'','',0,1),(538,'修改人人绑定设置','account','do_modify_renren','','',0,'','',0,1),(539,'修改开心绑定设置','account','do_modify_kaixin','','',0,'','',0,1),(540,'修改群模板设置','qun','add_module','','',0,'','',0,1),(541,'查看左侧导航设置','setting','left_navigation','','',0,'','',0,1),(542,'查看帖子同步发微博设置','setting','bbs_plugin','','',0,'','',0,1),(543,'修改左侧导航设置','setting','do_left_navigation','','',0,'','',0,1),(544,'查看底部导航设置','setting','footer_navigation','','',0,'','',0,1),(545,'微博管理','topic','domanage','','',0,'','',0,1),(546,'查看审核开关设置','setting','check_switch','','',0,'','',0,1),(547,'修改审核开关设置','setting','do_check_switch','','',0,'','',0,1),(548,'查看站点状态设置','setting','visit_state','','',0,'','',0,1),(551,'微博推荐','topic','do_recd','','',0,'','',0,1),(553,'勋章审核','medal','verify','','',0,'','',0,1),(554,'勋章会员列表','medal','user','','',0,'','',0,1),(561,'删除用户','member','dodelete','','',0,'','',0,1),(566,'清空所有登录错误的IP','failedlogins','clean','','',0,'','',0,1),(567,'登录错误IP设置','failedlogins','modify','','',0,'','',0,1),(570,'URL链接地址设置','url','setting','','',0,'','',0,1),(571,'URL链接地址列表','url','manage','','',0,'','',0,1),(572,'URL链接地址管理（删除）','url','do_manage','','',0,'','',0,1),(573,'左侧导航设置','navigation','left_navigation','','',0,'','',0,1),(574,'底部导航设置','navigation','footer_navigation','','',0,'','',0,1),(575,'删除角色','role','delete','','',0,'','',0,1),(578,'POST_vipintro_check_category','vipintro','check_category','','',0,'','',0,1),(586,'POST_setting_do_invite','setting','do_invite','','',0,'','',0,1),(594,'POST_mall_do_add_goods','mall','do_add_goods','','',0,'','',0,1),(596,'POST_mall_modify_order_status','mall','modify_order_status','','',0,'','',0,1),(598,'POST_mall_do_modify_goods','mall','do_modify_goods','','',0,'','',0,1),(604,'POST_contest_save_index','contest','save_index','','',0,'','',0,1),(606,'POST_contest_do_create_project','contest','do_create_project','','',0,'','',0,1),(616,'修改站点状态','setting','do_visit_state','','',0,'','',0,1),(620,'导航图标设定','navigation','icon','','',0,'','',0,1),(621,'导航编辑','navigation','new_save','','',0,'','',0,1),(625,'微信设置','wechat','do_setting','','',0,'','',0,1),(627,'POST_feed_doleader','feed','doleader','','',0,'','',0,1),(629,'频道编辑','channel','doedit','','',0,'','',0,1);
REPLACE INTO `jishigou_role_module`(`module`,`name`) values ('account','用户绑定'),('api','API应用'),('cache','缓存管理'),('channel','频道'),('db','数据库管理'),('dedecms','DeDeCMS调用'),('dzbbs','Discuz论坛调用'),('event','活动'),('fenlei','分类信息'),('imjiqiren','QQ机器人'),('income','广告管理'),('index','后台访问'),('link','友情链接设置'),('live','微直播'),('login','登录退出'),('logs','后台操作记录'),('medal','勋章(用户荣誉)'),('member','用户管理'),('module','模块'),('notice','网站公告'),('other','其他'),('output','微博评论站外调用'),('phpwind','PHPWind调用'),('plugin','插件模块'),('plugindesign','插件设计'),('pm','私信管理'),('profile','个人资料'),('qun','微群'),('recdtopic','官方推荐微博'),('report','举报管理'),('reward','有奖转发'),('robot','蜘蛛爬行记录'),('role','角色设置'),('role_action','动作权限管理'),('role_module','动作模块设置'),('search','搜索'),('sessions','在线用户'),('setting','系统设置'),('settings','设置'),('share','站外调用'),('show','界面与显示设置'),('sign','签到'),('skin','前台换肤'),('sms','手机短信'),('tag','话题管理'),('talk','微访谈'),('task','计划任务'),('tools','工具'),('topic','微博管理'),('ucenter','Ucenter整合'),('upgrade','系统升级'),('uploadattach','附件'),('user_tag','个人标签'),('verify','头像签名审核'),('vipintro','用户V认证'),('vote','投票'),('wall','微博墙'),('web_info','关于我们'),('xwb','新浪微博'),('ldap','ldap'),('company','company'),('failedlogins','登录错误IP'),('media','media'),('url','URL链接地址'),('navigation','导航'),('cms','cms'),('mall','积分商城'),('contest','contest'),('wechat','微信'),('feed','重要动态');

REPLACE INTO `jishigou_credits_rule` (`rid`,`rulename`,`action`,`cycletype`,`cycletime`,`rewardnum`,`norepeat`,`extcredits1`,`extcredits2`,`extcredits3`,`extcredits4`,`extcredits5`,`extcredits6`,`extcredits7`,`extcredits8`,`related`) values (1,'发布原创微博','topic',1,0,10,0,0,2,0,0,0,0,0,0,''),(7,'发送短消息','pm',1,0,1,0,0,1,0,0,0,0,0,0,''),(3,'关注好友','buddy',1,0,10,0,0,1,0,0,0,0,0,0,''),(8,'设置头像','face',0,0,1,0,0,10,0,0,0,0,0,0,''),(9,'VIP认证','vip',0,0,1,0,0,20,0,0,0,0,0,0,''),(6,'每天登录','login',1,0,1,0,0,2,0,0,0,0,0,0,''),(2,'评论或转发微博','reply',1,0,10,0,0,1,0,0,0,0,0,0,''),(4,'邀请注册','register',1,0,10,0,0,10,0,0,0,0,0,0,''),(10,'发布指定话题','_T84202031',1,0,2,0,0,5,0,0,0,0,0,0,'新人报到'),(11,'关注指定用户','_U-2012344970',0,0,1,0,0,5,0,0,0,0,0,0,'admin'),(12,'删除微博','topic_del',4,0,0,0,0,-5,0,0,0,0,0,0,''),(13,'取消关注好友','buddy_del',4,0,0,0,0,-5,0,0,0,0,0,0,''),(15,'发布指定话题','_T',1,0,1,0,0,0,0,0,0,0,0,0,''),(16,'关注指定用户','_U',1,0,1,0,0,0,0,0,0,0,0,0,''),(17,'发起投票','vote_add',1,0,10,0,0,2,0,0,0,0,0,0,''),(18,'删除投票','vote_del',4,0,0,0,0,-5,0,0,0,0,0,0,''),(19,'上传附件','attach_add',1,0,10,0,0,0,0,0,0,0,0,0,''),(20,'删除附件','attach_del',4,0,10,0,0,0,0,0,0,0,0,0,''),(21,'下载附件','attach_down',4,0,0,0,0,1,0,0,0,0,0,0,''),(22,'附件被下载','down_my_attach',4,0,0,0,0,1,0,0,0,0,0,0,''),(23,'赞微博','topic_dig',1,0,10,0,0,1,0,0,0,0,0,0,''),(24,'微博被赞','my_dig',4,0,0,0,0,1,0,0,0,0,0,0,''),(25,'微博被推荐','recommend',4,0,0,0,0,1,0,0,0,0,0,0,''),(26,'兑换商品','convert',4,0,0,0,0,1,0,0,0,0,0,0,''),(27,'取消商品兑换','unconvert',4,0,0,0,0,1,0,0,0,0,0,0,'');

REPLACE INTO `jishigou_event_sort` (`id`, `type`) VALUES (1, '演出/电影'), (2, '生活/聚会'), (3, '旅行/户外'), (4, '展览/沙龙'), (5, '体育/健身'), (6, '公益/环保'), (7, '派对/夜店'), (8, '作品征集'), (9, '市集/游园'), (10, '打折/促销'), (11, '其他');

REPLACE INTO `jishigou_medal` (`id`, `medal_img`, `medal_img2`, `medal_name`, `medal_depict`, `medal_count`, `is_open`, `conditions`, `dateline`) VALUES (1, './images/medal/1301651267/1_o.jpg', './images/medal/1301651267/1_s.jpg', '原创达人', '连续3天发布原创内容，就能获得微博达人勋章', 0, 1, 'a:4:{s:4:"type";s:5:"topic";s:3:"day";s:1:"3";s:6:"endday";s:0:"";s:7:"tagname";N;}', 1301651267), (2, './images/medal/1301651359/2_o.jpg', './images/medal/1301651359/2_s.jpg', '评论专家', '连续3天对他人内容进行评论，就能获得”评论专家“勋章', 0, 1, 'a:4:{s:4:"type";s:5:"reply";s:3:"day";s:1:"3";s:6:"endday";N;s:7:"tagname";N;}', 1301651359);
REPLACE INTO `jishigou_validate_category`(`id`,`category_id`,`category_name`,`category_pic`,`num`,`dateline`) values (1,0,'个人','',0,1322040871),(2,1,'站长','',0,1322040878),(3,1,'其他','',0,1324547052),(24,4,'其他','',0,1362037393),(7,1,'娱乐高管','',0,1324547013),(11,1,'娱乐活动','',0,1324547043),(12,1,'其他','',0,1324547052),(14,1,'经济学人','',0,1324547185),(19,1,'投资','',0,1324547219),(4,0,'企业','',0,1333156076),(13,1,'商界名人','',0,1324547176),(23,4,'媒体','',0,1333291705),(22,4,'政府','',0,1333291687),(21,4,'学校','',0,1333291678),(8,1,'娱评人','',0,1324547021),(9,1,'娱记','',0,1324547029),(10,1,'经纪人','',0,1324547037),(15,1,'股票','',0,1324547192),(16,1,'基金','',0,1324547199),(17,1,'外汇','',0,1324547204),(18,1,'期货','',0,1324547210);
REPLACE INTO `jishigou_common_member_profile_setting` (`fieldid`, `title`, `displayorder`, `formtype`, `size`, `choices`) values ('realname','真实姓名','4','text','0',''),('gender','性别','3','select','0','1|男\r\n2|女'),('bday','出生年月日','2','date','0',''),('constellation','星座','1','select','0','白羊座\r\n金牛座\r\n双子座\r\n巨蟹座\r\n狮子座\r\n处女座\r\n天秤座\r\n天蝎座\r\n射手座\r\n摩羯座\r\n水瓶座\r\n双鱼座'),('zodiac','生肖','0','select','0','鼠\r\n牛\r\n虎\r\n兔\r\n龙\r\n蛇\r\n马\r\n羊\r\n猴\r\n鸡\r\n狗\r\n猪'),('telephone','固定电话','0','text','0',''),('mobile','手机','0','text','0',''),('idcardtype','证件类型','0','select','0','身份证\r\n学生证\r\n军官证\r\n护照\r\n营业执照\r\n官方公函\r\n驾驶证\r\n其他'),('idcard','证件号','0','text','0',''),('address','邮寄地址','0','text','0',''),('zipcode','邮编','0','text','0',''),('nationality','国籍','0','text','0',''),('residecity','所在地','0','select','0',''),('education','学历','0','select','0','博士\r\n硕士\r\n本科\r\n专科\r\n中学\r\n小学\r\n其它'),('birthcity','出生地','0','select','0',''),('graduateschool','毕业学校','0','text','0',''),('pcompany','公司','0','text','0',''),('occupation','职业','0','text','0',''),('position','职位','0','text','0',''),('revenue','年收入','0','text','0',''),('affectivestatus','情感状态','0','text','0',''),('lookingfor','交友目的','0','text','0',''),('bloodtype','血型','0','select','0','A\r\nB\r\nAB\r\nO\r\n其他'),('height','身高','0','text','0',''),('weight','体重','0','text','0',''),('alipay','支付宝','0','text','0',''),('icq','ICQ','0','text','0',''),('qq','QQ','0','text','0',''),('yahoo','YAHOO帐号','0','text','0',''),('msn','MSN','0','text','0',''),('taobao','阿里旺旺','0','text','0',''),('site','个人主页','0','text','0',''),('aboutme','自我介绍','0','textarea','0',''),('linkaddress','联系地址','6','text','0',''),('interest','兴趣爱好','0','textarea','0',''),('field1','自定义','0','text','0',''),('field2','自定义字','0','text','0',''),('field3','自定义字段','0','text','0',''),('field4','自定义字段4','0','text','0',''),('field5','自定义字段5','0','text','0',''),('field6','自定义字段6','0','text','0',''),('field7','自定义字段7','0','text','0',''),('field8','自定义字段8','0','text','0','');
REPLACE INTO `jishigou_sign_tag`(`id`,`tag`,`credits`) values (1,'签到','');

REPLACE INTO `jishigou_qun_category`(`cat_id`,`cat_name`,`qun_num`,`parent_id`,`display_order`) values (2,'IT互联网',0,1,0),(3,'商业财经',0,1,0),(4,'传媒公关',0,1,0),(5,'兴趣爱好',0,0,0),(6,'动漫',0,5,0),(7,'游戏',0,5,0),(8,'体育',0,5,0),(27,'记事狗微博系统',0,0,0),(1,'行业交流',0,0,0),(10,'电影',0,5,0),(11,'购物',0,5,0),(12,'旅游',0,5,0),(13,'摄影',0,5,0),(9,'音乐',0,5,0),(16,'动漫',0,14,0),(18,'体育',0,14,0),(20,'电影',0,14,0),(19,'音乐',0,14,0),(14,'兴趣爱好',0,0,0),(17,'游戏',0,14,0),(15,'囧笑话',0,14,0),(21,'购物',0,14,0),(22,'旅游',0,14,0),(23,'摄影',0,14,0),(24,'科教人文',0,0,0),(25,'科学技术',0,24,0),(26,'教育考试',0,24,0);
REPLACE INTO `jishigou_qun_level`(`level_id`,`level_name`,`credits_higher`,`credits_lower`,`member_num`,`admin_num`) values (1,'初级群',-999999999,999999999,100,3);
REPLACE INTO `jishigou_qun_ploy`(`id`,`fans_num_min`,`fans_num_max`,`topics_higher`,`topics_lower`,`qun_num`) values (1,10,999999999,999999999,10,3),(2,11,100,100,22,1);

REPLACE INTO `jishigou_channel`(`ch_id`,`ch_name`,`feed`,`recommend`,`topic_num`,`total_topic_num`,`parent_id`,`description`,`purview`,`purpostview`,`verify`,`filter`,`display_order`,`display_list`,`display_view`,`in_home`,`picture`,`buddy_numbers`,`manageid`,`managename`,`template`,`channel_typeid`,`topictype`) values (1,'官方站务',0,0,0,0,0,'','','',0,'',0,'','',0,'',0,'','','',0,0),(2,'网友交流',0,0,0,0,0,'','','',0,'',0,'','',0,'',0,'','','',0,0),(3,'站务处理',0,0,0,0,1,'','','',0,'',0,'','',0,'',0,'','','',0,0),(4,'官方公告',1,0,0,0,1,'','','',0,'',0,'','',0,'',0,'','','',0,0),(5,'新人报到',0,0,0,0,2,'','','',0,'',0,'','',0,'',0,'','','',0,0),(6,'好图分享',0,0,0,0,2,'','','',0,'',0,'','',0,'',0,'','','',0,0),(7,'提问中心',0,1,1,1,0,'','','',0,'',0,'','',0,'',0,'','','',1,0),(8,'建议中心',0,1,1,1,0,'','','',0,'',0,'','',0,'',0,'','','',2,0);
REPLACE INTO `jishigou_channel_type`(`channel_typeid`,`channel_type`,`channel_typename`,`template`,`child_template`,`topic_template`,`featureid`,`default_feature`) values (1,'ask','问答模型','','','','1,2,3','待处理'),(2,'idea','建议模型','','','','4,5,6','待处理');
REPLACE INTO `jishigou_feature`(`featureid`,`featurename`) values (1,'已确认'),(2,'处理中'),(3,'已解决'),(4,'已被采纳'),(5,'不予采纳'),(6,'等待评估');
