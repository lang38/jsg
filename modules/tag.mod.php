<?php
/**
 * 文件名：tag.mod.php
 * @version $Id: tag.mod.php 5268 2013-12-16 08:28:12Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：标签操作模块
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ID = 0;

	var $ModuleConfig;

	var $TagLogic;

	var $Item;
	var $ItemConfig;
	var $ItemName;
	var $ItemUrl;

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ModuleConfig = jconf::get('tag');

		$this->ID = (int) ($this->Get['id'] ? $this->Get['id'] : $this->Post['id']);

		$this->Item = isset($this->Get['item']) ? $this->Get['item'] : $this->Post['item'];
		if(false == isset($this->ModuleConfig['item_list'][$this->Item]))
		{
			$this->Item = $this->ModuleConfig['item_default'];
		}

		$this->ItemConfig = $this->ModuleConfig['item_list'][$this->Item];
		$this->ItemName = $this->ItemConfig['name'];
		$this->ItemUrl = $this->ItemConfig['url'];
		global $jishigou_rewrite;
		if($jishigou_rewrite) {
			$this->ItemUrl = $jishigou_rewrite->formatURL($this->ItemUrl);
		}

		$this->TagLogic = Tag($this->Item);
		$this->CacheConfig = jconf::get('cache');
		$this->ShowConfig = jconf::get('show');


		$this->TopicLogic = jlogic('topic');

		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		if ($this->Code) {
			$this->View();
		} else {
			$this->Main();
		}
		$Contents=ob_get_clean();

		$this->ShowBody($Contents);

	}

	function Main()
	{
		$this->Code = 'tag';

				$tag_list = jlogic('hot_tag')->get_hot_tag($this->ShowConfig['tag_index']['hot'], $this->CacheConfig['tag_index']['hot']);

				$tag_guanzu = jlogic('hot_tag')->get_tag_by_top_tag_count($this->ShowConfig['tag_index']['guanzhu'], $this->CacheConfig['tag_index']['guanzhu'], 30);

				$tag_r_day7 = jlogic('hot_tag')->get_tag_by_top_topic_count($this->ShowConfig['tag_index']['day7'], $this->CacheConfig['tag_index']['day7'], 7);

				$day7_guanzhu = jlogic('hot_tag')->get_tag_by_top_tag_count($this->ShowConfig['tag_index']['day7_guanzhu'], $this->CacheConfig['tag_index']['day7_guanzhu'], 7);

				$tag_tuijian = jlogic('hot_tag')->get_tag_by_recommend($this->ShowConfig['tag_index']['tag_tuijian'], $this->CacheConfig['tag_index']['tag_tuijian']);


		$this->Title = "话题榜";
		include(template('tag/tag_index'));

	}

	function View()
	{
		$params = array();
		$content_dstr = $this->Config['in_publish_notice_str'];
		$content_ostr = $this->Config['on_publish_notice_str'];

		$tag = get_safe_code($this->Code);

		if (!$tag) {
			$this->Messager("请输入正确的链接地址",null);
		}

		$f_rets = filter($tag, 0, 0);
		if($f_rets && $f_rets['error']) {
			$this->Messager("输入的话题  " . $f_rets['msg'], null);
		}

		$sql = "select * from `".TABLE_PREFIX."tag` where `name`='".addslashes($tag)."'";
		$query = $this->DatabaseHandler->Query($sql);
		$tag_info = $query->GetRow();

		$tag_id = $tag_info['id'];
		$total_record = $tag_info['topic_count'];
		$tag_count = $tag_info['tag_count'];


		$TopicLogic = jlogic('topic');
		Load::logic("topic_list");
		$TopicListLogic = new TopicListLogic();

		$params['tag_id'] = $tag_id;

		$this->Get['type'] = (in_array($this->Get['type'], array('pic', 'video', 'music')) ? $this->Get['type'] : '');

		$gets = array(
			'mod' =>  $_GET['mod_original'] ? get_safe_code($_GET['mod_original']) : $this->Module,
			'code' => $this->Code ? $tag : "",
			'type' => $this->Get['type'],
			'view' => $this->Get['view'],
		);
		$query_link = "index.php?".url_implode($gets);
		unset($gets['type']);
		$type_url = "index.php?".url_implode($gets);

		$per_page_num = max(0, (int) $this->ShowConfig['tag_view']['tag']);

		$options = array (
			'type' => get_topic_type(),
			'filter' => $this->Get['type'],
		);

		$view = trim($this->Get['view']);

		if($tag_id > 0 && $per_page_num > 0) {
			if ($view == 'recd') {
				$p = array(
					'where' => " tr.recd <= 2 AND tr.item='tag' AND tr.item_id='{$tag_id}' ",
					'perpage' => $per_page_num ,
					'filter' => $this->Get['type'],
				);
				$info = $TopicListLogic->get_recd_list($p);
				if (!empty($info)) {
					$total_record = $info['count'];
					$topic_list = $info['list'];
					$page_arr = $info['page'];
				}
			} else {
								if (empty($this->Get['type'])) {
					$rets = jtable('topic_tag')->get_ids(array(
						'tag_id' => $tag_id,
						'sql_order' => ' `item_id` DESC ',
						'result_count' => $total_record,
						'per_page_num' => $per_page_num,
						'page_url' => $query_link,
					), 'item_id', 1);
					$total_record = $rets['count'];
					$page_arr = $rets['page'];
					$topic_list = (($total_record > 0 && $rets['ids']) ? $TopicLogic->Get($rets['ids']) : array());
				} else {
					$sql = "select `item_id` from `".TABLE_PREFIX."topic_tag` where `tag_id`='{$tag_id}' order by `item_id` desc LIMIT 2000 ";
					$query = $this->DatabaseHandler->Query($sql);
					$topic_ids = array();
					while (false != ($row = $query->GetRow())) {
						$topic_ids[$row['item_id']] = $row['item_id'];
					}
					$options['tid'] = $topic_ids;
					$options['filter'] = trim($this->Get['type']);
					$options['page_url'] = $query_link;
					$options['perpage'] = $per_page_num;

					$info = $TopicListLogic->get_data($options);
					$topic_list = array();
					if (!empty($info)) {
						$topic_list = $info['list'];
						if (isset($info['page'])) {
							$page_arr = $info['page'];
							$total_record = $info['count'];
						}
					}
				}
			}
						if($topic_list) {
				$parent_list = $TopicLogic->GetParentTopic($topic_list);
			}
		}
		if(!$topic_list) {
			$total_record = 0;
		}

		$show_config = jconf::get('show');
		$day1_r_tags = cache_file('get', "misc/recommendTopicTag-1-{$show_config['topic_index']['hot_tag']}");
		$day7_r_tags = cache_file('get', "misc/recommendTopicTag-7-{$show_config['topic_index']['hot_tag']}");

		$is_favorite = false;
		if($tag_info) {
			if(MEMBER_ID > 0) {
				$is_favorite = jlogic('tag_favorite')->info($tag, MEMBER_ID);
			}

			$tag_favorite_count = jtable('tag_favorite')->count(array('tag'=>$tag));
			if($tag_favorite_count > 0) {
				$tag_favorite_list = jlogic('tag_favorite')->favorite_users($tag, 12);
			}
		}

		$my_favorite_tags = jlogic('tag_favorite')->my_favorite(MEMBER_ID, 12);

		$tag_extra = array();
		if($tag_info && $tag_info['extra'])
		{
			Load::logic('tag_extra');
			$TagExtraLogic = new TagExtraLogic();

			$tag_extra_info = $TagExtraLogic->get_info($tag_info['id']);
			$tag_extra = $tag_extra_info['data'];
		}


		$_GET['searchKeyword'] = $this->Title = $tag;
		$this->MetaKeywords = $tag;

		$content = "#{$tag}#";



		if(MEMBER_ID > 0)
		{
			$member = jsg_member_info(MEMBER_ID);

			if ($member['medal_id']) {
				$medal_list = $this->TopicLogic->GetMedal($member['medal_id'],$member['uid']);
			}
		}

		include(template('tag/tag_list_topic_box'));

	}

	function _topicListBox($data) {
		$topic_list = jlogic('topic')->MakeAll($data);

		include(template('tag/tag_list_topic_box'));
	}

}

?>