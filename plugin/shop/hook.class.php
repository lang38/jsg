<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename hook.class.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-04-25 14:36:59 1509074913 1379120136 4869 $
 */





if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}
class plugin_shop{

		function global_publish_menu(){
		include template('shop:publish');
		return $shophtml;
	}

		function printtopic(&$topic){
		global $list_topic_tids,$list_topic_ptids;
		$list_topic_tids[] = $topic['tid'];
		if($topic['roottid']){
			$list_topic_ptids[$topic['tid']] = $topic['roottid'];
		}
	}

		function global_item_extra2_output(){
		global $list_topic_tids;
		if($list_topic_tids && is_array($list_topic_tids)){
			$shop_list = $this->_get_shops($list_topic_tids);
		}
		if($shop_list){
			foreach($shop_list as $v){
				$return[$v['tid']] = '<div class="topic_goods_intro"><a href="'.$v['url'].'" target="_blank"><img src="'.$v['image'].'" onerror="javascript:faceError(this);"></a><div class="topic_goods_intro_txt"><a href="'.$v['url'].'" target="_blank"><b>'.$v['goods'].'</b></a><br>价格：<a href="'.$v['url'].'" target="_blank">￥'.$v['price'].'元</a><br>卖家：<a href="'.$v['surl'].'" target="_blank">'.$v['seller'].'</a></div></div>';
			}
		}
		return $return;
	}
		function global_item_parent_extra_output(){
		global $list_topic_tids,$list_topic_ptids;
		if($list_topic_ptids && is_array($list_topic_ptids)){
			$shop_list = $this->_get_shops(array_unique($list_topic_ptids));
		}
		if($shop_list){
			foreach($list_topic_ptids as $key => $val){
				foreach($shop_list as $v){
					if($v['tid'] == $val){
						$return[$key] = '<div class="topic_goods_intro" style="width:440px;"><a href="'.$v['url'].'" target="_blank"><img src="'.$v['image'].'" onerror="javascript:faceError(this);"></a><div class="topic_goods_intro_txt"><a href="'.$v['url'].'" target="_blank"><b>'.$v['goods'].'</b></a><br>价格：<a href="'.$v['url'].'" target="_blank">￥'.$v['price'].'元</a><br>卖家：<a href="'.$v['surl'].'" target="_blank">'.$v['seller'].'</a></div></div>';
					}
				}
			}
		}
		return $return;
	}

		function global_item_view_extra_output(){
		global $list_topic_tids;
		if(is_array($list_topic_tids) && $list_topic_tids[0]){
			$shop_list = $this->_get_shops($list_topic_tids[0]);
		}
		if($shop_list){
			$v = $shop_list[0];
			$return = '<div class="topic_goods_intro"><a href="'.$v['url'].'" target="_blank"><img src="'.$v['image'].'" onerror="javascript:faceError(this);"></a><div class="topic_goods_intro_txt"><a href="'.$v['url'].'" target="_blank"><b>'.$v['goods'].'</b></a><br>价格：<a href="'.$v['url'].'" target="_blank">￥'.$v['price'].'元</a><br>卖家：<a href="'.$v['surl'].'" target="_blank">'.$v['seller'].'</a></div></div>';
		}
		return $return;
	}
		function global_item_view_parent_extra_output(){
		global $list_topic_tids,$list_topic_ptids;
		if(is_array($list_topic_ptids) && $list_topic_ptids[$list_topic_tids[0]]){
			$shop_list = $this->_get_shops($list_topic_ptids[$list_topic_tids[0]]);
		}
		if($shop_list){
			$v = $shop_list[0];
			$return = '<div class="topic_goods_intro" style="width:440px;"><a href="'.$v['url'].'" target="_blank"><img src="'.$v['image'].'" onerror="javascript:faceError(this);"></a><div class="topic_goods_intro_txt"><a href="'.$v['url'].'" target="_blank"><b>'.$v['goods'].'</b></a><br>价格：<a href="'.$v['url'].'" target="_blank">￥'.$v['price'].'元</a><br>卖家：<a href="'.$v['surl'].'" target="_blank">'.$v['seller'].'</a></div></div>';
		}
		return $return;
	}

		function posttopic($value){
		global $pluginshopid;
		if($value['step']=='check'){
			$pluginshopid = $value['param']['plugindata']['shopid'];		}
		if($value['step']=='post'){
			DB::query("UPDATE ".DB::table('topic_shop')." SET `tid` = '".$value['param'][0]."' WHERE id = '".$pluginshopid."'");			$imageid = DB::result_first("SELECT `imageid` FROM ".DB::table('topic_shop')." WHERE id = '".$pluginshopid."'");
			if($imageid){
				DB::query("UPDATE ".DB::table('topic_image')." SET `tid` = '".$value['param'][0]."',item = 'plugin_shop', itemid = '".$pluginshopid."' WHERE id = '".$imageid."'");			}
		}
	}

		function deletetopic($tids){
		DB::query("DELETE FROM ".DB::table('topic_shop')." WHERE tid IN(".jimplode($tids).")");
		DB::query("DELETE FROM ".DB::table('topic_image')." WHERE tid IN(".jimplode($tids).") AND item = 'plugin_shop'");
	}

		function deletemember($uids){
		DB::query("DELETE FROM ".DB::table('topic_shop')." WHERE uid IN(".jimplode($uids).")");
	}

		function _get_shops($tids){
		$shops = array();
		$query = DB::query("SELECT * FROM ".DB::table('topic_shop')." WHERE tid IN(".jimplode($tids).")");
		while($shop = DB::fetch($query)) {
			$shops[] = $shop;
		}
		return $shops;
	}
}
?>