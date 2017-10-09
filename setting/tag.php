<?php
/**
 *
 * TAG配置文件
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag.php 3320 2013-04-11 03:42:14Z wuliyong $
 */

/*------------------------- 基本配置开始 --------------------------------*/

/*　tag表名称　*/
$config['tag']['table_name'] = TABLE_PREFIX.'tag';

/* user表及主键设置 */
$config['tag']['user_table_name'] = TABLE_PREFIX.'members';
$config['tag']['user_table_pri'] = 'uid';

/* my_tag表名称 */
$config['tag']['my_tag_table_name'] = TABLE_PREFIX.'my_tag';

/*------------------------- 基本配置结束 --------------------------------*/

/*　页面默认的标题　*/
$config['tag']['page_title_default'] = "话题";
$config['tag']['per_page_num'] = 200;
$config['tag']['total_record'] = 1000;
$config['tag']['cache_time'] = 1800;

$config['tag']['list_similar_tag_count'] = 10;

$config['tag']['user_list_per_page_num'] = 100;
$config['tag']['item_list_per_page_num'] = 20;
$config['tag']['item_default'] = 'topic';
$config['tag']['item_list'] = array(
	
	'topic' => array(
		'table_name' => TABLE_PREFIX . 'topic',
		'table_pri' => 'tid',
		'name' => $GLOBALS['_J']['config']['changeword']['n_weibo'],
		'value' => 'topic',
		'url' => 'index.php?mod=topic',
		'disable_update_item_table' => 1, //暂时禁止更新此topic表中的tag_count和tag字段信息，因为这两个字段没有使用到。
	),
);
?>