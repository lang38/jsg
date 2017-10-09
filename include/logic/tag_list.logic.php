<?php
/**
 *
 * 标签逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: tag_list.logic.php 3831 2013-06-07 08:18:28Z wuliyong $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TagListLogic {

	public $db = null;

	public function __construct() {
		$this->db = jtable('tag');
	}

	
	public function get($p) {
		return $this->db->get($p);
	}

	
	public function delete($p) {
		return $this->db->delete($p);
	}
	
	public function get_tag_by_top_topic_count($limit, $dateline=0, $cache_time = 0) {
		$tags = array();
		$limit = max(0, (int) $limit);
		if($limit > 0) {
			$dateline = max(43200, (int) $dateline);
			$cache_time = max(300, (int) $cache_time);
			$cache_id = "misc/topic-new-tag-{$limit}-{$dateline}";

			if(!$cache_time || false === ($tags = cache_file('get', $cache_id))) {
				$tags = DB::fetch_all("select `id`,`name`,`topic_count`,`last_post` from `".TABLE_PREFIX."tag` 
					where `last_post` > '" . (TIMESTAMP - $dateline) . "' 
					order by `topic_count` desc 
					limit {$limit}");
				if($cache_time) {
					cache_file('set', $cache_id, $tags, $cache_time);
				}
			}
		}
		return $tags;
	}

}