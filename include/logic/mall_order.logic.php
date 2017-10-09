<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename mall_order.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-20 1179320128 1767 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class MallOrderLogic {

    
    public function get_list($gid=0, $limit = 20, $order = 'id desc') {
		$sql_array = $gid ? array('goods_id' => $gid, "sql_order" => $order, "page_num" => $limit) : array("sql_order" => $order, "page_num" => $limit);
        return jtable("mall_order")->get($sql_array);
    }

    
    public function add_order($data) {
        
        $data['uid'] = MEMBER_ID;
        $data['username'] = MEMBER_NICKNAME;
        $data['sn'] = TIMESTAMP.mt_rand(1, 9999);         $config = jconf::get('mall');
       
        $id = jtable('mall_order')->insert($data, 1);
        if ($id) {
                        jtable('mall_goods')->update_count(array('id' => $data['goods_id']), 'seal_count', '+' . $data['goods_num']);
            jtable('mall_goods')->update_count(array('id' => $data['goods_id']), 'order_count', '+'. $data['goods_num']);
			jtable('mall_goods')->update_count(array('id' => $data['goods_id']), 'total', '-'. $data['goods_num']);
            			update_credits_by_action('convert',$data['uid'],1,-$data['pay_credit']);
						$feed_msg = cut_str($data['goods_name'],30,'');
			feed_msg('mall','exchange',$data['goods_id'],$feed_msg,0);
        }
        return $id;
    }

}