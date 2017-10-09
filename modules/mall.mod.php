<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename mall.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-20 1104293463 4117 $
 */





if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}


class ModuleObject extends MasterObject {
    var $auto_run = true;
    
    function ModuleObject($config) {    	         
        $mall_enable = (int) jconf::get('mall', 'enable');
        if (empty($mall_enable)) {
            $this->Messager('没有开启积分商城模块', null);
        }
		
        $this->MasterObject($config);
    }

    
    public function index() {
        $this->Title = '积分商城';
		$type = jget('type');
		if($type == 'exp'){
			$css['mall'] = $css['exp'] = ' class="current"';
			$list  = jlogic('mall')->get_goods_list('exp');
			$noitem = '没有您可兑换的商品';
		}else{
			$css['mall'] = $css['all'] = ' class="current"';
			$list  = jlogic('mall')->get_goods_list('all');
			$noitem = '暂无商品或商品已过期下架！';
		}
        foreach ($list['list'] as $k => $one) {
            $list['list'][$k]['expire'] = my_date_format($one['expire']);
        }        
		$feeds = jlogic('feed')->get_feed(5,"`action`='兑换了'");
        $top_credit_members = jlogic('mall')->get_top_member_credits();
		$config  = jconf::get('mall');
        include template('mall_index');
    }

    function exchangegoods(){        
        $this->Title = '积分兑换记录';
		$pagenum = 10;
		$css['exchange'] = $css['exp'] = ' class="current"';
		$data = jtable('mall_order')->get(array('uid' => MEMBER_ID,'sql_order'=>'id desc','page_num'=>$pagenum));
        $page = $data['page']['html'];
        $data = $data['list'];
        foreach ($data as $key => $value) {
            $data[$key]['pay_time'] = $value['pay_time'] > 0 ? my_date_format($value['pay_time']) : '-';
            $data[$key]['xaddress'] = strlen($value['address']) > 26 ? cut_str($value['address'], 15) : $value['address'];
        }
		$feeds = jlogic('feed')->get_feed(5,"`action`='兑换了'");
        $top_credit_members = jlogic('mall')->get_top_member_credits();
		$config  = jconf::get('mall');
        include template('mall_exchangegoods');
    }

	function goodsinfo(){
		$id = jget('id');
		$info = jlogic('mall')->get_info($id);
		if($info){
			$config  = jconf::get('mall');
			$member  = jsg_member_info(MEMBER_ID);
			if($info['expire'] < TIMESTAMP){
				$info['exp'] = '商品已过期';
			}elseif($info['price']>$member[$config['credits']]){
				$info['exp'] = '您的'.$config['credits_name'].'不够';
			}elseif($info['credit']>$member['credits']){
				$info['exp'] = '您的总积分不够';
			}else{
				$info['exp'] = '';
			}
			$info['expire'] = my_date_format($info['expire']);
			$info['desc'] = nl2br($info['desc']);
			$this->Title = '商品详情 —— '.$info['name'];
			$this->MetaKeywords = '积分兑换,商品详情';
			$this->MetaDescription = $info['name'];
			$top_credit_members = jlogic('mall')->get_top_member_credits();
			$feeds = jlogic('feed')->get_feed(5,"`action`='兑换了'");
			$css['mall'] = ' class="current"';

			$this->item = 'mall';
			$this->item_id = $id;
			$h_key = 'mall';
						$gets = array(
			'mod' => 'mall',
			'code' => 'goodsinfo',
			'id' => $id,
			);
			$page_url = 'index.php?'.url_implode($gets);
			$tids = jlogic('mall')->get_topic_by_goodsid($id);
			$options = array(
			'tid' => $tids,
			'perpage' => 5,				'page_url' => $page_url,
			);
			$topic_info = jlogic('topic_list')->get_data($options);
			$topic_list = array();
			if (!empty($topic_info)) {
			$topic_list = $topic_info['list'];
			$page_arr['html'] = $topic_info['page']['html'];
			}
			$albums = jlogic('image')->getalbum();
			include template('mall_info');
		}else{
			header('Location: '.jurl('index.php?mod=mall'));
		}
	}
}