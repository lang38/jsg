<?php

/**
 *
 * mall的相关操作
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mall.logic.php 3740 2013-05-28 09:38:05Z wuliyong $
 */
if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class MallLogic {

    function __construct() {
        ;
    }

    
    public function get_attr() {
        
    }

	
	function is_exists($id)
	{
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('mall_goods')." WHERE id='{$id}'");
		return $count > 0 ? true : false;
	}

    
    public function get_goods_list( $exp = '', $order = "`order` desc,id desc", $limit = 12) {
        if('all'==$exp){
			return jtable("mall_goods")->get(array("sql_where"=>'expire > '.TIMESTAMP,"sql_order" => $order, "page_num" => $limit));
		}elseif('exp'==$exp){
			$member  = jsg_member_info(MEMBER_ID);
			$config  = jconf::get('mall');
			$sql_where = "expire > ".TIMESTAMP." AND credit <= ".$member['credits']." AND price <= ".$member[$config['credits']];
			return jtable("mall_goods")->get(array("sql_where" => $sql_where,"sql_order" => $order, "page_num" => $limit));
		}else{
			return jtable("mall_goods")->get(array("sql_order" => "id desc", "page_num" => $limit));
		}
    }

    
    public function add_goods($data) {

        $data['uid'] = MEMBER_ID;
        if (!trim($data['name']) || $data['price'] < 1 || $data['total'] < 1 || $data['uid'] < 1) {
            return FALSE;
        }
        if ($data['expire'] < (int) time()) {            return FALSE;
        }
        $data['dateline'] = (int) time();
        $data['last_uid'] = $data['uid'];
        $data['last_update'] = (int) time();

        $id = jtable("mall_goods")->insert($data, 1);
				if ($id) {
			$feed_msg = cut_str(trim($data['name']),30,'');
			feed_msg('mall','post',$id,$feed_msg,0);
        }
        return (int) $id;
    }

    
    public function modify_goods($gid, $data) {
        
        $data['last_uid'] = MEMBER_ID;
         $data['last_update'] = TIMESTAMP;

        if ( empty($data['name']) || $data['price'] < 1 || $data['total'] < 1) {
            return false;
        }

                if ($data['expire'] < TIMESTAMP) {
            return false;
        }
       
        return jtable("mall_goods")->update($data, $gid);

    }

    
    public function get_info($gid) {
        return jtable("mall_goods")->info(array("id" => $gid));
    }

	public function get_top_member_credits($limit=5){
		$config  = jconf::get('mall');
		$info = jtable("members")->get(array("sql_where"=>'`'.$config['credits'].'`>0', "sql_field"=> "`uid`,`username`,`nickname`,`face`,`face_url`,`{$config['credits']}` as `credits` ", "sql_order" => '`'.$config['credits'].'` DESC', "sql_limit" => $limit, 'result_list_row_make_func'=>'jsg_member_make'));
		return $info['list'];
	}

		function get_topic_by_goodsid($id){
		$tids = array();
		$query = DB::query("SELECT tid FROM ".DB::table('topic_mall')." where item_id ='{$id}' ORDER BY tid DESC");
		while ($value = DB::fetch($query)){
			$tids[] = $value['tid'];
		}
		return $tids;
	}
}