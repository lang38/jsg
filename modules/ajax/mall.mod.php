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
 * @Date 2014 1896981208 6283 $
 */





if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}


class ModuleObject extends MasterObject {

    var $auto_run = true;

    public function ModuleObject($config) {
        $this->MasterObject($config);
    }

    
    public function convertible() {        
        MEMBER_ID == 0 &&  json_error('请先进行登录');
        $config = jconf::get('mall');
        (int)$config['enable']  === 0 && json_error('没有开启积分商城模块');
        $gid     = jget("gid",'int');
        $address = trim(jget('address','txt'));
        $mobile  = jget('mobile','mobile');
        $qq      = trim(jget('qq'));
        $cid     = jget("cid",'int');
        $num     = 1;
		$company_enable = $GLOBALS['_J']['config']['company_enable'];
		if($company_enable){
			$cid === 0 && json_error('请选择所属单位');
			$company = jtable('company')->info($cid);
			$address = $company['name'];
			if($company['upid']){
				$upaddress = '';
				$upcids = explode(',',$company['upid']);
				foreach($upcids as $val){
					$upinfo = jtable('company')->info($val);
					$upaddress .= $upinfo['name'];
				}
				$address = $upaddress . $address;
			}
		}elseif(strlen($address)<16){
			return json_error('请填写有效的送货地址');
		}
		$info    = jlogic('mall')->get_info($gid);
		$member  = jsg_member_info(MEMBER_ID);
		$config  = jconf::get('mall');
        if ( empty($mobile) ) {
            return json_error('请填写正确的手机号码');
        }
        if(TIMESTAMP > ($info['expire'])){
            return json_error('商品已过期！');
        }
        if ($info['credit'] > $member['credits']) {
            return json_error('您的总积分没有达到兑换该商品的总积分要求！');
        }
        if ($info['price'] > $member[$config['credits']]) {
            return json_error('您的'.$config['credits_name'].'不足');
        }
        if($num > $info['total']){
            return json_error('库存商品不足！');
        }
        $data = array(
            'goods_id'      => $info['id'],
            'goods_name'    => $info['name'],
            'goods_num'     => $num,
            'goods_price'   => $info['price'],
            'goods_credit'  => $info['credit'],
            'pay_credit'    => $info['price'],
            'address'       => $address,
            'tel'           => $tel,
            'qq'            => $qq,
            'mobile'        => $mobile,
            'add_time'      => TIMESTAMP,
            'pay_time'      => TIMESTAMP,
            'status'        => 0,
        );
        $r = jlogic('mall_order')->add_order($data);
        return $r ? json_result('兑换商品成功') : json_error('兑换商品失败') ;
    }

    
    function ordership(){
        if('admin' === MEMBER_ROLE_TYPE){
			$config = jconf::get('mall');
			$mall_enable = (int)$config['enable'];
			if ($mall_enable === 0 ) {
				return json_error('没有开启积分商城模块');
			}
			$order_id = jget('oid','int');
			if ($order_id === 0) {
				return json_error('没有找到订单对象');
			}
			$order = jtable("mall_order")->info($order_id);
			if (empty($order)) {
				return json_error('没有找到订单对象');
			}
			jtable("mall_order")->update(array('status' => 1), array('id'=>$order_id));
			jtable('mall_order_action')->insert(array('uid'=>$order['uid'],'order_id'=>$order['id'],'status'=>1,'msg'=>'','dateline'=>TIMESTAMP));
			return json_result('订单发货成功') ;
		}
    }

    
    function ordercancle(){
		if('admin' === MEMBER_ROLE_TYPE){
			$config = jconf::get('mall');
			$mall_enable = (int)$config['enable'];
			if ($mall_enable === 0 ) {
				return json_error('没有开启积分商城模块');
			}
			$order_id = jget('oid','int');
			if ($order_id === 0) {
				return json_error('没有找到订单对象');
			}
			$order = jtable("mall_order")->info($order_id);
			if (empty($order)) {
				return json_error('没有找到订单对象');
			}
						jtable('mall_goods')->update_count(array('id' => $order['goods_id']), 'seal_count', '-'.$order['goods_num']);
			jtable('mall_goods')->update_count(array('id' => $order['goods_id']), 'total', '+'.$order['goods_num']);		
			jtable("mall_order")->update(array('status' => 2), array('id'=>$order_id));
						update_credits_by_action('unconvert',$order['uid'],1,$order['pay_credit']);
			return json_result('订单取消成功') ;
		}
    }

		function listcompany(){
		$gid = jget('gid');
		$info = jlogic('mall')->get_info($gid);
		$member  = jsg_member_info(MEMBER_ID);
		$config  = jconf::get('mall');
		if(MEMBER_ID == 0){echo '<center><br><br><font color=red onclick="ShowLoginDialog(); return false;" style="font-weight:600;cursor: pointer;font-size:14px;">请先登录！</font><br><br><br><br></center>';}
		elseif (TIMESTAMP > ($info['expire'])){
            echo '<center><br><br><font color=red>您要兑换的商品已过期下架，无法兑换！</font><br><br><br><br></center>';
        }elseif ($info['credit'] > $member['credits']) {
            echo '<center><br><br><font color=red>您的总积分没有达到兑换该商品的总积分要求！</font><br><br><br><br></center>';
        }elseif ($info['price'] > $member[$config['credits']]) {
            echo '<center><br><br><font color=red>您的'.$config['credits_name'].'不足，没有达到兑换该商品所需要的'.$config['credits_name'].'！</font><br><br><br><br></center>';
        }elseif($num > $info['total']){
            echo '<center><br><br><font color=red>您要兑换的商品库存不足！</font><br><br><br><br></center>';
        }else{
			$company_enable = $GLOBALS['_J']['config']['company_enable'];
			if($company_enable && @is_file(ROOT_PATH . 'include/logic/cp.logic.php')){
				$companyid = $GLOBALS['_J']['member']['companyid'];
				$companyselect = jlogic('cp')->get_cp_html($companyid);
			}
			include template('mall_ajax_bak');
		}
	}

}