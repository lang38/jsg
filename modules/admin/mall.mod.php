<?php

/**
 *
 * 商城管理模块
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: mall.mod.php 3740 2013-05-28 09:38:05Z wuliyong $
 */

if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}


class ModuleObject extends MasterObject {

    
    private $order_status = array(
        0 => "已下单",
        1 => "已发货",
        2 => "已退还",
    );

    
    var $auto_run = true;

    function ModuleObject($config) {
        $this->MasterObject($config);
    }

    
    function index() {

        
        include template();

    }

    
    function setting() {
                $mall = jconf::get('mall');
		$oldmallcredits = $mall['credits'];
        
        $credits_options = array();
        $credits = jconf::get('credits');
        if ($credits && $credits['ext']) {
            foreach ($credits['ext'] as $_key => $_row) {
                if ($_row['enable'] && $_row['name'] && $_key) {
                    $credits_options[$_key] = array('name' => $_row['name'], 'value' => $_key);
                }
            }
        }
        if (empty($credits_options)) {
            $this->Messager('请先设置积分项', 'admin.php?mod=setting&code=modify_credits');
        }

        
        if (jget('settingsubmit')) {
            $_mall = jget('mall');
            $mall['enable'] = (int) $_mall['enable'];
			$mall['exchange'] = (int) $_mall['exchange'];
			$mall['post'] = (int) $_mall['post'];

            $mc = $_mall['credits'];
            if ($mc && $credits['ext'][$mc]['enable']) {
                $mall['credits'] = $mc;
                
                $mall['credits_name'] = $credits['ext'][$mc]['name'];
            }
            
            $mall['rule'] = jget('rule');
            
            jconf::set('mall', $mall);

						if($mall['credits'] != $oldmallcredits){
				$sets = array();$rule = array('extcredits1','extcredits2','extcredits3','extcredits4','extcredits5','extcredits6','extcredits7','extcredits8');
				foreach ($rule as $v){
					if($mall['credits'] == $v){
						$sets[] = "`$v`='1'";
					}else{
						$sets[] = "`$v`='0'";
					}
				}
				DB::Query("update ".TABLE_PREFIX."credits_rule set ".implode(" , ",$sets)." where `action`='convert' or `action`='unconvert'");
				$credits_rule = jconf::get('credits_rule');
				foreach($credits_rule as $ck => $cv){
					if(in_array($ck,array('convert','unconvert'))){
						foreach($cv as $_k => $_v){
							if(!in_array($_k,array('rid','rulename','action','cycletype'))){
								unset($credits_rule[$ck][$_k]);
							}
						}
						$credits_rule[$ck][$mall['credits']] = '1';
					}
				}
				jconf::set('credits_rule', $credits_rule);
			}

            $this->Messager('设置成功');
        }


        
        $mall_enable_radio = $this->jishigou_form->YesNoRadio('mall[enable]', (int) $mall['enable']);
		$feed_exchange_radio = $this->jishigou_form->YesNoRadio('mall[exchange]', (int) $mall['exchange']);
		$feed_post_radio = $this->jishigou_form->YesNoRadio('mall[post]', (int) $mall['post']);
        $mall_credits_select = $this->jishigou_form->Select('mall[credits]', $credits_options, $mall['credits']);
        $mall_rule = $this->jishigou_form->Textarea('rule', $mall['rule']);
        
        include template();
    }

    
    function manage() {
        $p = array(
            'perpage' => 100,
            'page_mall' => 'admin.php?mod=mall&code=manage',
            'sql_order' => ' `id` DESC ',
        );
        $id = jget('id', 'int');
        if ($id > 0) {
            $p['id'] = $id;
            $p['page_mall'] .= "&id=$id";
        }
        $key = jget('key');
        if ($key) {
            $p['key'] = $key;
            $p['page_mall'] .= "&key=$key";
        }
        $mall = jget('mall');
        if ($mall) {
            $p['sql_where'] = " MATCH (`mall`) AGAINST ('{$mall}') ";
            $p['page_mall'] .= "&mall=$mall";
        }
        $site_id = jget('site_id', 'int');
        if ($site_id > 0) {
            $p['site_id'] = $site_id;
            $p['page_mall'] .= "&site_id=$site_id";
        }
        $order = jget('order');
        if ($order && in_array($order, array('dateline', 'open_times'))) {
            $p['sql_order'] = " `{$order}` DESC ";
            $p['page_mall'] .= "&order=$order";
        }
        $rets = jlogic('mall')->get($p);

        include template();
    }

    
    function do_manage() {
        $id = jget('id', 'int');
        $ids = jget('ids');
        if (!$ids && $id < 1) {
            $this->Messager('请先指定要操作的对象');
        }
        $ids = (array) ($id > 0 ? $id : $ids);

        $info = array();
        if ($id > 0) {
            $info = jlogic('mall')->get_info_by_id($id);
        }

        $action = jget('action');
        if ('delete' == $action) {
            jlogic('mall')->delete(array('id' => $ids));
        } elseif ('status') {
            $status = jget('status', 'int');
            jlogic('mall')->set_status($ids, $status);
            if ($info && ($site = jlogic('site')->get_info_by_id($info['site_id']))) {
                if (jget('confirm')) {
                    jlogic('mall')->set_status(array('site_id' => $site['id']), $status);
                    jlogic('site')->set_status(array('id' => $site['id']), $status);
                } else {
                    $mall = "admin.php?mod=mall&code=do_manage&action=status&status=$status&id=$id&confirm=1";
                    $this->Messager("已经设置成功，<a href='{$mall}'>点此可以将此站点 {$site['host']} 下的所有URL链接地址都设置为相同的状态</a><br />（默认不点击时将为您跳转回列表页面）。", '', 5);
                }
            }
        }

        $this->Messager('操作成功', 'admin.php?mod=mall&code=manage');
    }

    
    public function goods_list() {
		$config  = jconf::get('mall');
        $list = jlogic("mall")->get_goods_list();
        include template();
    }

    
    public function add_goods() {
		$config  = jconf::get('mall');
        include template();
    }

    
    public function do_add_goods() {
        $data = jget("data");

        $data = $this->check_data($data);
        $image_src = '';
        if ($_FILES['image']) {
            $image = jlogic('image')->upload(array('pic_field' => 'image'));
			$image['photo'] = $image['site_url'] ? $image['site_url'].'/'.str_replace('./','',$image['photo']) : $image['photo'];
            $image_src = (string) $image['photo'];
        }
        $data['image'] = $image_src;
        $r = jlogic("mall")->add_goods($data);
        if ($r) {
            $this->Messager("添加商品成功");
        } else {
            $this->Messager("添加商品失败");
        }
    }

    
    public function modify_goods() {
		$config  = jconf::get('mall');
        $id = jget("id",'int');
        if ($id < 1) {
            $this->Messager("请选择你要修改的商品");
        }
        $info = jlogic("mall")->get_info($id);
        include template();
    }

    public function deltes_goods() {
       
        $goods_id  = jget("id");

        $r = jtable('mall_goods')->delete($goods_id);
        return $r ? $this->Messager('删除商品成功', 'admin.php?mod=mall&code=goods_list') : $this->Messager("删除商品失败");
    }

    
    public function do_modify_goods() {
        $data = jget("data");
        $gid  = jget("id");
        $data = $this->check_data($data);
        
        if ($_FILES['image']['error'] === 0) {
            $image = jlogic('image')->upload(array('pic_field' => 'image'));
			$image['photo'] = $image['site_url'] ? $image['site_url'].'/'.str_replace('./','',$image['photo']) : $image['photo'];
            $image_src = (string) $image['photo'];
            $data['image'] = $image_src;
        }
       
        $r = jlogic("mall")->modify_goods($gid, $data);
        return $r ? $this->Messager("编辑商品成功") : $this->Messager("编辑商品失败");
    }

    
    protected function check_data($data) {
		$config  = jconf::get('mall');
        $data['name'] = trim($data['name']);
        if (!$data['name']) {
            $this -> Messager("请填写名字");
        }

        if ((int) $data['price'] < 1) {
            $this -> Messager("请填写所需".$config['credits_name']);
        } else {
            $data['price'] = (int) $data['price'];
        }

        if ((int) $data['total'] < 1) {
            $this -> Messager("请填写总商品数");
        } else {
            $data['total'] = (int) $data['total'];
        }

        $data['expire'] = strtotime($data['expire']);
        if ($data['expire'] < TIMESTAMP) {
            $this -> Messager("商品有效期不正确");
        }
        return $data;
    }

    
    public function order_list() {

        $gid = jget("gid");
        $list = jlogic('mall_order')->get_list($gid);

        $goods_info = jtable('mall_goods')->info(array('id'=>$gid));
        foreach ($list['list'] as $k=>$one) {
            $list['list'][$k]['add_time'] = date('Y-m-d H:i:m', $one['add_time']);
        }

        $order_status = -1;
        include template();
    }

    
    public function search_order() {
        $goods_name = jget("goods_name");
        $order_sn   = jget("order_sn");
        $user_nickname = jget("user_nickname");
        $order_status  = jget("order_state",'int');

        if ($goods_name) {
            $goods_info = jtable('mall_goods')->info(array('name'=>trim($goods_name)));
            $sql_str['goods_id'] =$goods_info['id'];
        }
        if ($order_sn) {
             $sql_str['sn'] =$order_sn;
        }
        if ($user_nickname) {
            $members = jsg_get_member(trim($user_nickname));
            $sql_str['uid'] =$members['uid'];
        }
        if ($order_status >= 0) {
            $sql_str['status'] =$order_status;
        }

        $sql_str['sql_order'] = 'id desc';
        $sql_str['page_num'] = 20;
        $list = jtable('mall_order')->get($sql_str);
        foreach ($list['list'] as $k => $one) {
            $list['list'][$k]['add_time'] = date('Y-m-d H:i:m', $one['add_time']);
        }

        include template('admin/mall_order_list');
    }

}

?>
