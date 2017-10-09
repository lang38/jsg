<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename event.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 2132486228 25400 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class EventLogic
{
	


	function EventLogic()
	{

	}

	
	function is_exists($id)
	{
		$id = (int) $id;
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('event')." WHERE id='{$id}'");
		return $count;
	}

	
	function get_event_info($id)
	{
		$id = (int) $id;
		$event_info = DB::fetch_first("SELECT * FROM ".DB::table('event')." WHERE id='{$id}'");
		return $event_info;
	}

	
	function getMemberInfo($id,$uid){
		$uid = $uid ? (int) $uid : MEMBER_ID;
		$id = (int) $id;
		$user_info = DB::fetch_first("select app,app_time,play,play_time from ".DB::table('event_member')." where id = '$id' and fid = '$uid'");
	    return $user_info;
	}

	
	function createEvent($post,$item='',$item_id=0,$verify=1){
		if($this->allowedCreate(MEMBER_ID)) {
			return 0;
		}
		$item_id = max(0, (int) $item_id);
			    $qua_arr = array();
	    if($post['qua'] == 'qua'){
	    		    	if($post['fans']){
	    		$qua_arr['fans_num'] = (int) $post['fans_num'];
	    	}
	    		    	if($post['same_city']){
	    		$qua_arr['same_city'] = 1;
	    	}
	    		    	if($post['inqun']){
	    		$qua_arr['inqun'] = $post['inqun'];
	    	}
	    }
	    $qualification = serialize($qua_arr);
	    
	    $need_app_info = $post['info'] ? serialize($post['info']) : serialize(array());

	    	    $postman = MEMBER_ID;
	    	    if($post['money_r'] == 'money'){
	    	$money = (is_numeric($post['money']) ? $post['money'] : 0);
	    }else{
	    	$money = 0;
	    }
	    $time = TIMESTAMP;
	    	    $fromt = strtotime($post['fromt']." ".$post['hour_select_from'].":".$post['min_select_from']);
	    $tot = strtotime($post['tot']." ".$post['hour_select_to'].":".$post['min_select_to']);
				$post['name'] = jfilter(strip_tags($post['name']),'txt');

		$post['hid_pic'] = jfilter($post['hid_pic'],'txt');

	    	    if($post['act']) {
	    	if(jdisallow()) {
	    		return array();
	    	}

	    	$post['id'] = (int) $post['id'];

	    	$sql = "update ".TABLE_PREFIX."event
	    			set
	    			    type_id = '$post[type]',
	    			    title = '$post[name]',
	    			    fromt = '$fromt',
	    			    tot = '$tot',
	    			    content = '$post[content1]',
	    			    image = '$post[hid_pic]',
	    			    province_id = '$post[province]',
	    			    area_id = '$post[area]',
	    			    city_id = '$post[city]',
	    			    address = '$post[address]',
	    			    money = '$money',
	    			    lasttime = '$time',
	    			    qualification = '$qualification',
	    			    postip = '".$GLOBALS['_J']['client_ip']."',
	    			    need_app_info = '$need_app_info'
	    			where id  = '{$post[id]}'";
	    	DB::query($sql);
	    	$values = array(
				'id' => $post[id],
			);
	    	return $values;
	    }

	    $sql = "insert into ".TABLE_PREFIX."event (
	    			type_id,title,fromt,tot,content,
	    			image,province_id,area_id,city_id,address,money,
	    			postman,posttime,lasttime,qualification,need_app_info,verify,postip,item,item_id
	    ) values (
	    			'$post[type]','$post[name]','$fromt','$tot','$post[content1]',
	    			'$post[hid_pic]','$post[province]','$post[area]','$post[city]','$post[address]',$money,
	    			'$postman',$time,$time,'$qualification','$need_app_info',$verify,'".$GLOBALS['_J']['client_ip']."','$item','$item_id'
	    )";
	    DB::query($sql);
	    $id = DB::insert_id();

	    	    if($item  == 'qun' && $item_id){
	    	DB::query("insert into `".TABLE_PREFIX."qun_event` (`qid`,`eid`) values ('$item_id','$id')");
	    }

	    				if($verify == 0){
			if($notice_to_admin = $GLOBALS['_J']['config']['notice_to_admin']){
				$pm_post = array(
					'message' => MEMBER_NICKNAME."发布了一个活动进入待审核状态，<a href='admin.php?mod=event&code=verify' target='_blank'>点击</a>进入审核。",
					'to_user' => str_replace('|',',',$notice_to_admin),
				);
								$admin_info = DB::fetch_first('select `uid`,`username`,`nickname` from `'.TABLE_PREFIX.'members` where `uid` = 1');
				load::logic('pm');
				$PmLogic = new PmLogic();
				$PmLogic->pmSend($pm_post,$admin_info['uid'],$admin_info['username'],$admin_info['nickname']);
			}
		}
		return $id;
	}

	
	function delEvent($id,$admin=0){
		$id = (int) $id;
		if(jdisallow()) {
			return 1;
		}

		if($admin == 0){
									$count = DB::result_first("select count(*) as count from ".TABLE_PREFIX."event_member where id='$id' and app = 1 ");
			if($count){
				return 1;
			}
		}

				$event = DB::fetch_first("select * from ".TABLE_PREFIX."event where id = '$id' ");
		if(!$event){
			return 2;
		}
		$image = $event['image'];
		if($image){
			$type = trim(strtolower(end(explode(".",$image))));
			$name = explode("_",$image);
			$image_s = $name[0]."_s.".$type;

		    unlink($image);
			unlink($image_s);
		}
				DB::query("delete from ".TABLE_PREFIX."qun_event where eid = '$id' ");

				DB::query("delete from ".TABLE_PREFIX."event where id = '$id' ");
		DB::query("delete from ".TABLE_PREFIX."event_member where id = '$id' ");

		
		return ;
	}

	
	function getEventInfo($param){
		if (!empty($param['where'])) {
			$where_sql .= " {$param['where']} ";
		}

		$order_sql = " ";
		if (!empty($param['order'])) {
			$order_sql = " {$param['order']} ";
		}

		$limit_sql = " ";
		if (!empty($param['limit'])) {
			$limit_sql = " {$param['limit']} ";
		}

		$event_list = array();
		$count_sql = "select count(*) from ".TABLE_PREFIX."event a where $where_sql ";
		$count = DB::result_first($count_sql);

		if ($count) {
			if ($param['page']) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $param['perpage'], $param['page_url'], $_config);
				$limit_sql = $page_arr['limit'];
			}

	    	$sql = "select a.id,a.title,a.fromt,a.tot,a.need_app_info,a.item,a.item_id,
	    			   a.content,a.image,fp.name as province,fa.name as area,fc.name as city,
	    			   a.address,a.money,a.app_num,a.play_num,a.verify,
	    			   a.postman,a.posttime,a.item,a.item_id,m.nickname,m.username,m.uid,am.store,am.app
		    		from ".TABLE_PREFIX."event a
		    		left join ".TABLE_PREFIX."event_member am on am.id = a.id and am.fid = ".MEMBER_ID."
		    		left join ".TABLE_PREFIX."common_district fp on fp.id = a.province_id
		    		left join ".TABLE_PREFIX."common_district fa on fa.id = a.area_id
					left join ".TABLE_PREFIX."common_district fc on fc.id = a.city_id
					left join ".TABLE_PREFIX."members m on m.uid = a.postman
					WHERE {$where_sql}
					{$order_sql}
					{$limit_sql} ";

			$query = DB::query($sql);
			while ($rsdb = DB::fetch($query)) {
								if($rsdb['app'] == 1){
					$rsdb['app'] = "已报名";
					$rsdb['app_time'] = date("Y年m月d日 H:i",$rsdb['app_time']);
				}else{
					$rsdb['app'] = "未报名";
				}

				if($rsdb['tot'] <= TIMESTAMP){
			    	$rsdb['event_type'] = "活动已截止";
			    }else{
			    	$rsdb['event_type'] = "活动进行中";
			    	$app = "style='display:none'";
			    }

			    			    $rsdb['fromt_day'] = $this->_getDay($rsdb['fromt']);
			    $rsdb['fromt'] = date("Y年m月d日 H:i",$rsdb['fromt']);

			    			    $rsdb['tot_day'] = $this->_getDay($rsdb['tot']);
			    $rsdb['tot'] = date("Y年m月d日 H:i",$rsdb['tot']);

			    			    $rsdb['address'] = $rsdb['province'].$rsdb['city'].$rsdb['area'].$rsdb['address'];
								if(!$rsdb[image]){
					$rsdb[image] = "images/kuang.png";
				}
			    			    if($rsdb['money'] == 0){
			    	$rsdb['money'] = "免费";
			    }else{
			    	$rsdb['money'] = "人均".$rsdb['money']."元";
			    }
				$event_list[$rsdb['id']] = $rsdb;
			}

			return array(
				'count' => $count,
				'event_list' => $event_list,
				'page' => $page_arr,
				'app'=>$app,
			);
		}
		return false;
	}

	
	function getEvents($param){
		$uid = $param['uid'];

		if (!empty($param['where'])) {
			$where_sql .= " {$param['where']} ";
		}

		$order_sql = " ";
		if (!empty($param['order'])) {
			$order_sql = " {$param['order']} ";
		}

		$limit_sql = " ";
		if (!empty($param['limit'])) {
			$limit_sql = " {$param['limit']} ";
		}

		$event_list = array();
		$count_sql = "select count(*) from ".TABLE_PREFIX."event_member m
					  left join ".TABLE_PREFIX."event a on a.id = m.id and a.verify = 1
					  where $where_sql ";
		$count = DB::result_first($count_sql);

		if ($count) {
			if ($param['page']) {
								$_config = array(
					'return' => 'array',
				);
				$page_arr = page($count, $param['perpage'], $param['page_url'], $_config);
				$limit_sql = $page_arr['limit'];
			}

			$sql = "select a.id,a.title,a.fromt,a.tot,a.image,fp.name as province,fa.name as area,fc.name as city,
					m.app,m.app_time,m.play,m.play_time,m.store,m.store_time,a.address,a.app_num,a.play_num,m1.username,m1.nickname
					from ".TABLE_PREFIX."event_member m
					left join ".TABLE_PREFIX."event a on a.id = m.id and a.verify = 1
					left join ".TABLE_PREFIX."members m1 on m1.uid = a.postman
					left join ".TABLE_PREFIX."common_district fp on fp.id = a.province_id
					left join ".TABLE_PREFIX."common_district fa on fa.id = a.area_id
					left join ".TABLE_PREFIX."common_district fc on fc.id = a.city_id
					WHERE {$where_sql}
					{$order_sql}
					{$limit_sql} ";

			$query = DB::query($sql);
			while ($rsdb = DB::fetch($query)) {
								$rsdb['fromt'] = date("Y年m月d日 H:i",$rsdb['fromt']);
				$rsdb['tot'] = date("Y年m月d日 H:i",$rsdb['tot']);
								if(!$rsdb[image]){
					$rsdb[image] = "images/kuang.png";
				}
								if($rsdb['app'] == 1){
					$rsdb['app'] = "已报名";
					$rsdb['app_time'] = date("Y年m月d日 H:i",$rsdb['app_time']);
				}else{
					$rsdb['app'] = "未报名";
				}
				if($rsdb['play'] == 1){
					$rsdb['play_time'] = date("Y年m月d日 H:i",$rsdb['play_time']);
				}
				if($rsdb['store'] == 1){
					$rsdb['store_time'] = date("Y年m月d日 H:i",$rsdb['store_time']);
				}
								$rsdb['address'] = $rsdb['province'].$rsdb['city'].$rsdb['area'].$rsdb['address'];

				$event_list[$rsdb['id']] = $rsdb;
			}

			return array(
				'count' => $count,
				'event_list' => $event_list,
				'page' => $page_arr,
			);
		}
		return false;
	}

	
	function getNewEvent($param){
		$uid = (int) $param['uid'];
		$page_url = $param['page_url'];
		$per_page_num = $param['perpage'];
		$_config = array(
			'return' => 'array',
		);
		$query = DB::query("select * from ".TABLE_PREFIX."event_member where fid = '$uid' ");
		while ($rsdb = DB::fetch($query)){
			$id_arr[$rsdb['id']] = $rsdb['id'];
			$count++;
		}
		if($id_arr){
			$id_list = jimplode($id_arr);
			$where = "and a.id not in ($id_list)";
		}

		$sql = "select a.id,a.title,a.fromt,a.tot,a.image,a.content,a.postman,fp.name as province,fa.name as area,fc.name as city,
				m.app,m.app_time,m.play,m.play_time,m.store,m.store_time,a.address,a.app_num,a.play_num
				from ".TABLE_PREFIX."event_member m
				right join ".TABLE_PREFIX."event a on a.id = m.id
				left join ".TABLE_PREFIX."common_district fp on fp.id = a.province_id
				left join ".TABLE_PREFIX."common_district fa on fa.id = a.area_id
				left join ".TABLE_PREFIX."common_district fc on fc.id = a.city_id
				where
				    m.fid = '$uid'
				  or
				    (a.postman = '$uid' and a.verify = 1
				    $where)
                    order by a.lasttime desc,a.posttime desc ";

		$postcount = DB::result_first("select count(*) from ".TABLE_PREFIX."event a where a.postman = '$uid' $where  and a.verify = 1 ");
		$count = $count + $postcount;
		$page_arr = page($count,$per_page_num,$page_url,$_config);
		$sql .= $page_arr['limit'];
		if($count){
			$query = DB::query($sql);
			while ($rsdb = DB::fetch($query)){
								if(!$rsdb[image]){
					$rsdb[image] = "images/kuang.png";
				}
								if($rsdb['app'] == 1){
					$rsdb['app'] = "已报名";
					$rsdb['app_time'] = date("Y年m月d日 H:i",$rsdb['app_time']);
				}else{
					$rsdb['app'] = "未报名";
				}
			    if($rsdb['tot'] <= TIMESTAMP){
			    	$rsdb['event_type'] = "活动已截止";
			    }else{
			    	$rsdb['event_type'] = "活动进行中";
			    	$app = "style='display:none'";
			    }
								$rsdb['fromt'] = date("Y年m月d日 H:i",$rsdb['fromt']);
				$rsdb['tot'] = date("Y年m月d日 H:i",$rsdb['tot']);

				if($rsdb['play'] == 1){
					$rsdb['play_time'] = date("Y年m月d日 H:i",$rsdb['play_time']);
				}
				if($rsdb['store'] == 1){
					$rsdb['store_time'] = date("Y年m月d日 H:i",$rsdb['store_time']);
				}
								$rsdb['address'] = $rsdb['province'].$rsdb['city'].$rsdb['area'].$rsdb['address'];
				$rs[$rsdb['id']] = $rsdb;
			}
		}
		return array(
			'count' => $count,
			'event_list' => $rs,
			'page' => $page_arr,
		);
	}
	
	function getAllUser($param,$type){
		$where = $param['where'];
		$order = $param['order'];
		$limit_sql = $param['limit'];

		$count = DB::result_first("select count(*) from ".TABLE_PREFIX."event_member a where $where ");

		if ($param['page']) {
						$_config = array(
				'return' => 'array',
			);
			$page_arr = page($count, $param['perpage'], $param['page_url'], $_config);
			$limit_sql = $page_arr['limit'];
		}

	    $sql = "select m.uid,m.nickname,m.username,m.face,m.province,m.city,a.app_time,a.app_info,a.play_time
	    		from ".TABLE_PREFIX."event_member a
	    		left join ".TABLE_PREFIX."members m on m.uid=a.fid
	    		where $where
	    		$order
	    		$limit_sql ";
	    $query = DB::query($sql);
	    while ($rsdb = DB::fetch($query)){
	    	$rsdb['face'] = face_get($rsdb['uid']);
	    	if($type == 'app'){
	    		$rsdb['time'] = date("Y-m-d H:i",$rsdb['app_time']);
	    	} else {
	    		$rsdb['time'] = date("Y-m-d H:i",$rsdb['play_time']);
	    	}
	    	$member[$rsdb['uid']] = $rsdb;
	    }
	    $return['member'] = $member;
	    $return['count'] = $count;
	    $return['page_arr'] = $page_arr;
	    return $return;
	}

	
	function getHotEvent(){
				$hot_event = array();
		$query = DB::query("select * from ".TABLE_PREFIX."event where recd = 1 order by lasttime desc , app_num desc ");
		while ($rsdb = DB::fetch($query)){
			if(!$rsdb['image']){
				$rsdb[image] = "images/kuang.png";
			}else{
				$type = trim(strtolower(end(explode(".",$rsdb['image']))));
				$name = explode("_",$rsdb['image']);
				$rsdb['image'] = $name[0]."_s.".$type;
			}
			if(time()>=$rsdb['fromt'] && time()<=$rsdb['tot'] ){
				$rsdb['show'] = "火热进行中";
			}else if(time() < $rsdb['fromt']){
				$rsdb['show'] = "即将开始";
			}else if(time() > $rsdb['tot']){
				$rsdb['show'] = "活动已结束";
			}
			$hot_event[$rsdb['id']] = $rsdb;
		}
		return $hot_event;
	}

	
	function getDaRen(){
		$uids = array();
		$sql = "SELECT postman
				FROM `".TABLE_PREFIX."event`
				GROUP BY postman
				ORDER BY count( postman ) DESC
				LIMIT 12 ";
		$query = DB::query($sql);
		while ($rsdb = DB::fetch($query)){
			$uids[] = $rsdb['postman'];
		}
		return $uids;
	}

	
	function _getDay($time){
		$day_num = date("w",$time);
		switch ($day_num) {
			case 1:
				$day = "星期一";
			    break;
			case 2:
				$day = "星期二";
			    break;
			case 3:
				$day = "星期三";
			    break;
			case 4:
				$day = "星期四";
			    break;
			case 5:
				$day = "星期五";
			    break;
			case 6:
				$day = "星期六";
			    break;
			case 0:
				$day = "星期天";
			    break;
			default:
				break;
		}
		return $day;
	}

	
	function doStroe($id,$type){
		$id = (int) $id;
		$time = TIMESTAMP;
	    if($type == 'cancle'){
	    	DB::query("update ".TABLE_PREFIX."event_member set store = 0,store_time = '$time' where id = '$id' and fid = ".MEMBER_ID);
	    }else{
		    $count = DB::result_first("select count(*) from ".TABLE_PREFIX."event_member where id = '$id' and fid = ".MEMBER_ID);
		    if($count){
		    	DB::query("update ".TABLE_PREFIX."event_member set store = 1,store_time = '$time' where id = '$id' and fid = ".MEMBER_ID);
		    }else{
		    	$title = DB::result_first("select title from ".TABLE_PREFIX."event where id = '$id'");
		    	DB::query("insert into ".TABLE_PREFIX."event_member (id,title,fid,store,store_time) values('$id','$title',".MEMBER_ID.",1,'$time')");
		    }
	    }
	}

	
	function doCancle($id){
		$id = (int) $id;
    	DB::query("update ".TABLE_PREFIX."event_member set app = 0,app_time = '$time' where id = '$id' and fid = ".MEMBER_ID);
    	DB::query("update ".TABLE_PREFIX."event set app_num = app_num - 1,lasttime = '$time' where id = '$id'");

    	    	update_credits_by_action('event_cancel', MEMBER_ID);
	}

	
	function doApp($id,$qua){
		$id = (int) $id;
		$time = time();
		$member_info = $this->getMemberInfo($id,MEMBER_ID);
		if($member_info){
			DB::query("update ".TABLE_PREFIX."event_member set app = 1 ,app_time = '$time' ,app_info = '$qua' where id = '$id' and fid = ".MEMBER_ID);
		}else{
			$title = DB::result_first("select title from ".TABLE_PREFIX."event where id = '$id'");
			DB::query("insert into ".TABLE_PREFIX."event_member (id,title,fid,app,app_time,app_info) values('$id','$title',".MEMBER_ID.",1,'$time','$qua')");
		}
		DB::query("update ".TABLE_PREFIX."event set app_num = app_num + 1,lasttime = '$time' where id = '$id'");

				$postman = DB::result_first("select postman from ".TABLE_PREFIX."event where id = '$id' ");
		$query = DB::query("select fid from ".TABLE_PREFIX."event_member where id = '$id' order by `id` limit 300 ");
		$id_arr = array();
		while ($rsdb = DB::fetch($query)){
			$id_arr[$rsdb['fid']] = $rsdb['fid'];
		}
	    if(!in_array($postman,$id_arr)){
			$id_arr[$postman] = $postman;
		}
		if(in_array(MEMBER_ID,$id_arr)){
			unset($id_arr[MEMBER_ID]);
		}

		if($id_arr){
			DB::query("update ".TABLE_PREFIX."members set event_new = event_new + 1 where uid in ('".implode("','",$id_arr)."')");
		}

				update_credits_by_action('event_app', MEMBER_ID);
	}

	
	function allowedCreate($uid = MEMBER_ID,$member){
		$uid = (int) $uid;
		if(!$member){
			$member = DB::fetch_first("SELECT validate FROM ".DB::table('members')." WHERE uid='{$uid}'");
		}
		if(!$member) {
			return '游客不能创建活动';
		}
		if('admin' !== MEMBER_ROLE_TYPE) {
			$config = jconf::get();
			if($config['event_vip']){
				if(!$member['validate']) {
					return "非V认证用户不允许创建活动,<a href='index.php?mod=other&code=vip_intro'>点此申请V认证</a>";
				}
			}
		}
	}

    
    public function get_event_type($only_name_value = FALSE){
        $event_type = array();
        $r = jtable('event_sort')->get(array('sql_order'=>'`id`'));		foreach ($r['list'] as $one_temp){
			$acount =jtable('event')->count(array('type_id'=>$one_temp['id'],'verify'=>1));
			if(!$only_name_value){
                $event_type[$one_temp['id']]['count'] = $acount;
                $event_type[$one_temp['id']]['type'] = $one_temp['type'];
            }
            $event_type[$one_temp['id']]['value'] = $one_temp['id'];
			$event_type[$one_temp['id']]['name'] = $one_temp['type'];
		}
        return $event_type;
    }

    
    public function get_province_id($province = FALSE){
        if($province){
            $r = jtable('common_district')->val(array('name' => $province,'upid' => 0),'id');
        }else{
            $r = jtable('common_district')->val(array('upid' => 0,'sql_order'=>'list'),'id');
        }
        return $r;

    }

    
    public function get_province(){
        $query = jtable('common_district')->get(array('upid'=>0,'sql_order'=>'list'));
        foreach ($query['list'] as $one_temp){
            $province[$one_temp['id']]['value']  = $one_temp['id'];
            $province[$one_temp['id']]['name']  = $one_temp['name'];
        }
        return $province;
    }

    
    public function get_city_id($param){

        return jtable('common_district')->val($param,'id');
    }
    
    public function get_city($province_id){
        $province_id = (int) $province_id;
        $query = jtable('common_district')->get(array('upid'=>$province_id,'sql_order'=>'list'));
        foreach ($query['list'] as $one_temp){
            $city[$one_temp['id']]['value']  = $one_temp['id'];
            $city[$one_temp['id']]['name']  = $one_temp['name'];
        }
        return $city;
    }

    
    public function get_city_name($city_id){
    	$city_id = (int) $city_id;
        return  jtable('common_district')->val(array('id' =>$city_id),'name');
    }
    
    public function get_area($city_id){

    }
    
    public function get_area_id($param){

        return jtable('common_district')->val($param,'id');
    }

    
    public function get_event_title($id){
        return $this->get_event_attr('title', $id);
    }

    
    public function get_postman($id){
        return $this->get_event_attr('postman', $id);
    }

    
    public function get_event_attr($attr,$id){
    	$id = (int) $id;
        $attr_value = jtable('event')->val(array('id' =>$id),$attr);
        return $attr_value;
    }

}