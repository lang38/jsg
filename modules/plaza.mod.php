<?php
/**
 *
 * 广场模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: plaza.mod.php 5377 2014-01-08 10:28:59Z yupengfei $
 */

if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

	
	function ModuleObject($config) {
		$this->MasterObject($config, 1);
	}

	public function index() {
		$this->Title = '广场首页';
		$rets = jlogic('plaza')->index_topic();
				$recommend_pic = $rets['recommend_pic'];
		$recommend_topic = $rets['recommend'];
				$dig_pic = $rets['dig_pic'];
		$dig_topic = $rets['dig'];
				$reply_pic = $rets['reply_pic'];
		$reply_topic = $rets['reply'];
				$newreply_pic = $rets['newreply_pic'];
		$newreply_topic = $rets['newreply'];
				$newdig_pic = $rets['newdig_pic'];
		$newdig_topic = $rets['newdig'];
				$top_fans_member = jlogic('member')->get_member_by_top_fans(12);
		foreach($top_fans_member as $key => $val){
			$top_fans_member[$key]['aboutme'] = $val['aboutme'] ? $val['aboutme'] : '粉丝'.$val['fans_count'].'人';
		}
		$GLOBALS['_J']['navcss']['index'] = 'current';
		include template('plaza_index');
	}

	public function recommend() {
		$this->Title = '最新推荐';
		$p = array(
			'where' => ' tr.recd > 0 ',
			'perpage' => 20
		);
		$rets = jlogic('topic_list')->get_recd_list($p);
		$GLOBALS['_J']['navcss']['recommend'] = 'current';
		include template();
	}

	public function new_topic() {
		$this->Title = '最新微博';

		$p = array(
			'cache_time' => (int) jconf::get('cache', 'topic_new', 'topic'),

			'perpage' => (int) jconf::get('show', 'topic_new', 'topic'),
			'page_url' => 'index.php?mod=plaza&code=new_topic',
		);
		$rets = jlogic('plaza')->new_topic($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['newtopic'] = 'current';
		include template();
	}

	public function new_reply() {
		$this->Title = '最新评论';

		$p = array(
			'cache_time' => (int) jconf::get('cache', 'new_reply', 'reply'),

			'perpage' => (int) jconf::get('show', 'new_reply', 'reply'),
			'page_url' => 'index.php?mod=plaza&code=new_reply',
		);
		$rets = jlogic('plaza')->new_reply($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['newreply'] = 'current';
		include template();
	}

	public function new_dig() {
		$this->Title = '最新赞';

		$p = array(
			'cache_time' => (int) jconf::get('cache', 'new_dig', 'dig'),

			'perpage' => (int) jconf::get('show', 'new_dig', 'dig'),
			'page_url' => 'index.php?mod=plaza&code=new_dig',
		);
		$rets = jlogic('plaza')->new_dig($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['newdig'] = 'current';
		include template();
	}

	public function new_forward() {
		$this->Title = '最新转发';

		$p = array(
			'cache_time' => (int) jconf::get('cache', 'new_forward', 'forward'),

			'perpage' => (int) jconf::get('show', 'new_forward', 'forward'),
			'page_url' => 'index.php?mod=plaza&code=new_forward',
		);
		$rets = jlogic('plaza')->new_forward($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['newforward'] = 'current';
		include template();
	}

	public function hot_reply() {
		$this->Title = '热门评论';

		$day_list = $this->_day_list();
		$day = jget('day', 'int');
		$day = isset($day_list[$day]) ? $day : 0;
		$p = array(
			'cache_time' => (int) jconf::get('cache', 'reply_hot', 'day'. $day),

			'perpage' => (int) jconf::get('show', 'reply_hot', 'day' . $day),
			'page_url' => 'index.php?mod=plaza&code=hot_reply&day=' . $day,

			'day' => $day,
		);
		$rets = jlogic('plaza')->hot_reply($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['hotreply'] = 'current';
		include template();
	}

	public function hot_dig() {
		$this->Title = '热门赞';

		$day_list = $this->_day_list();
		$day = jget('day', 'int');
		$day = isset($day_list[$day]) ? $day : 0;
		$p = array(
			'cache_time' => (int) jconf::get('cache', 'dig_hot', 'day' . $day),

			'perpage' => (int) jconf::get('show', 'dig_hot', 'day' . $day),
			'page_url' => 'index.php?mod=plaza&code=hot_dig&day=' . $day,

			'day' => $day,
		);
		$rets = jlogic('plaza')->hot_dig($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['hotdig'] = 'current';
		include template();
	}

	public function hot_forward() {
		$this->Title = '热门转发';

		$day_list = $this->_day_list();
		$day = jget('day', 'int');
		$day = isset($day_list[$day]) ? $day : 0;
		$p = array(
			'cache_time' => (int) jconf::get('cache', 'topic_hot', 'day' . $day),

			'perpage' => (int) jconf::get('show', 'topic_hot', 'day' . $day),
			'page_url' => 'index.php?mod=plaza&code=hot_forward&day=' . $day,

			'day' => $day,
		);
		$rets = jlogic('plaza')->hot_forward($p);

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['hotforward'] = 'current';
		include template();
	}

	public function new_tc() {
                if(!$this->Config['same_city']) {
                        $this->Messager('管理员已关闭同城微博的功能。', 'index.php?mod=plaza');
                }
		$this->Title = '最新同城微博';
		
		$province_id = jget('province', 'int');
		$city_id = jget('city', 'int');
		$area_id = jget('area', 'int');
		
		$p = array(
			'cache_time' => (int) jconf::get('cache', 'topic_new', 'topic'),

			'perpage' => (int) jconf::get('show', 'topic_new', 'topic'),
			'page_url' => 'index.php?mod=plaza&code=new_tc&province=' . $province_id . '&city=' . $city_id . '&area=' . $area_id,
		
			'province_id' => $province_id,
			'city_id' => $city_id,
			'area_id' => $area_id,
		);
		$rets = jlogic('plaza')->new_tc($p);
                
                $province = $rets['province'];
                $city = $rets['city'];
                $area = $rets['area'];
                $province_id = $rets['province_id'];
                $city_id = $rets['city_id'];
                $area_id = $rets['area_id'];
                $province_list = jlogic('common_district')->get_province_list(true);
                $province_list_select = jform()->Select("tc_province", $province_list, $province_id, " onchange=\"changeProvince();\" style=\"width:150px\" ");

		$ajax_load = $this->_ajax_load($rets);
		$GLOBALS['_J']['navcss']['newtc'] = 'current';
		include template();
	}

	public function new_pic() {
		$this->Title = '最新微博';

		$p = array(
			'cache_time' => (int) jconf::get('cache', 'topic_new', 'topic'),

			'perpage' => (int) jconf::get('show', 'topic_new', 'topic'),
			'page_url' => 'index.php?mod=plaza&code=new_pic',
		);
		$rets = jlogic('plaza')->new_topic($p);
                if($rets['page']['html']) {
                        $ajax_num = ceil($rets['count']/$p['perpage']);
                }
                foreach ($rets['list'] as $key => $row) {                        if($row['parent_id'] || $row['top_parent_id']) {
                                unset($rets['list'][$key]);
                        }
                }
                $topic_pic_keys = array('ji','shi','gou','img');

		include template();
	}

	private function _day_list() {
		return array(1=>'近一天', 7=>'近一周', 14=>'近两周', 30=>'近一月', );
	}

	private function _ajax_load(&$rets, $ajaxnum = 10) {
		$ajaxnum = (int) $ajaxnum;
		if($ajaxnum < 1) {
			return false;
		}
		if(!is_array($rets['list'])) {
			return false;
		}
		if(count($rets['list']) <= $ajaxnum) {
			return false;
		}
		$topic_keys = array_keys($rets['list']);
		$rets['list'] = array_slice($rets['list'],0,$ajaxnum);
		array_splice($topic_keys,0,$ajaxnum);
		$num = ceil(count($topic_keys)/$ajaxnum);
		$ajaxkey = array();
		for($i=0;$i<$num;$i++) {
			if(count($topic_keys) > $ajaxnum) {
				$topic_key = array_splice($topic_keys,0,$ajaxnum);
			} else {
				$topic_key = $topic_keys;
			}
			$ajaxkey[] = base64_encode(serialize($topic_key));
		}
		return array(
			'isloading' => true,
			'ajaxkey' => $ajaxkey,
		);
	}

}