<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename validate_category.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 276837374 6929 $
 */



if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ValidateLogic
{


	var $DatabaseHandler;

		
	function ValidateLogic()
	{
		$this->DatabaseHandler = &Obj::registry("DatabaseHandler");

	}



	

	function CategoryList($cid='')
	{
				$cid = max(0, (int) $cid);
		$p = array(
			'cache_time' => 300,
			'cache_file' => 1,
			'category_id' => $cid,
			'sql_order' => ' `id` ASC ',
			'return_list' => 1,
		);
		return jtable('validate_category')->get($p);
	}




		function CategoryView($ids=0)
	{
		$ids = jfilter($ids, 'int');
		$error_info = array(
			0 => '未找到指定分类信息',
		);
		if($ids < 1) {
			return $error_info[0];
		}

				return jtable('validate_category')->info($ids);
	}

	
	function CategoryUserList($where='',$limit='',$query_link='',$orderby='uid')
	{
		$per_page_num = $limit ? $limit : 20;

				$p = array(
			'cache_time' => 300,
			'cache_file' => 1,
			'sql_where' => $where,
			'sql_order' => ' `id` DESC ',
		);
		$uds = jtable('validate_category_fields')->get_ids($p, 'uid');
		if($uds) {
						$total_record = count($uds);

						$page_arr = page ($total_record,$per_page_num,$query_link,array('return'=>'array',));

			$wherelist = "where `uid` in (".jimplode($uds).") and `city` !='' order by `{$orderby}` desc  {$page_arr['limit']} ";


			$TopicLogic = jlogic('topic');

			$members = $TopicLogic->GetMember($wherelist,"`uid`,`ucuid`,`media_id`,`aboutme`,`username`,`nickname`,`province`,`city`,`face_url`,`face`,`validate`");
			$members = buddy_follow_html($members, 'uid', 'follow_html2');
		}

		$user_ary = array('member'=>$members,'uids' =>$uds,'pagearr'=>$page_arr);

		return $user_ary;
	}

	
	function getValidatedUid(){
				$p = array(
			'cache_time' => 300,
			'cache_file' => 1,
			'is_audit' => 1,
			'sql_order' => ' `id` DESC ',
		);
		return jtable('validate_category_fields')->get_ids($p, 'uid');
	}


	
	function CategoryProvinceList($pid=0,$cid=0)
	{
		if($pid)
		{
			$province_where = "where `province` = '{$pid}'  ";
		}

		if($cid)
		{
			$city_where = " and `city` = '{$cid}'  ";
		}

		$where_list = $province_where . $city_where;

		if(empty($where_list))
		{
			return false;
		}

		$query = DB::query("SELECT *
							FROM ".DB::table('validate_category_fields')."
							{$where_list}  order by `dateline` desc limit 0,20");
		$cat_Province_ary = array();
		while ($value = DB::fetch($query))
		{
			$cat_Province_ary[$value['uid']] = $value['uid'];
		}

		return $cat_Province_ary;

	}


		
	function CategoryCityList($where='',$is_check_user=0)
	{

		$query = DB::query("SELECT *
							FROM ".DB::table('common_district')."
							{$where}  order by list ");
		$ary_list = array();
		while ($value = DB::fetch($query))
		{
			if($is_check_user)
			{
			 	$where = "where `city` = '".$value['name']."' limit 0,1";
			 	$count = DB::result_first("SELECT count(*) FROM ".DB::table('members')." {$where} ");
			 	$value['user_count'] =  $count;
			}

			$ary_list[$value['id']] = $value ;

		}

		return $ary_list;
	}



	

	function Member_Validate_Add($data='')
	{

		$error_info = array(

							0=>'申请失败，未知错误',
							1=>'申请成功，等待审核',
							2=>'已经提交过认证，等待审核',
							3=>'请填写完整的认证信息'
		);

		$msg = $error_info[1];

		if(empty($data)){
			$msg = $error_info[3];
			$ary_info = array('msg_info'=>$msg);

			return $ary_info;

		}

			    $validate_info = DB::fetch_first("select * from ".DB::table('validate_category_fields')." where `uid`='".MEMBER_ID."' ");
	    if($validate_info && $validate_info['is_audit'] != -1)
	    {
			$msg = $error_info[2];
			$ary_info = array('msg_info'=>$msg);

			return $ary_info;

	    }

	    $table_name = 'validate_category_fields';
	    if($validate_info['is_audit']== -1){
			$ids = $validate_info['id'];
			DB::update($table_name, $data," id = '$ids' ");
	    }else{
	    	DB::insert($table_name, $data);
			$ids = $this->DatabaseHandler->Insert_ID();
	    }
        if ($ids < 1)
        {
            $msg = $error_info[0];
            $ary_info = array('msg_info'=>$msg);

			return $ary_info;

        }

                $province_info = DB::fetch_first("select * from ".DB::table('common_district')." where `id`='". (int) $data['province']."' ");
	    $city_info = DB::fetch_first("select * from ".DB::table('common_district')." where `id`='". (int) $data['city']."' ");

        $sql = "update `".TABLE_PREFIX."members` set `province`='{$province_info['name']}',`city`='{$city_info['name']}' where `uid`='".MEMBER_ID."'";
        $this->DatabaseHandler->Query($sql);

  		$ary_info = array('msg_info'=>$msg,'ids'=>$ids);

        return $ary_info;

	}


		function Small_CategoryList($category_fid=0)
	{
				$category_fid = (int) $category_fid;
				if($category_fid > 0)
		{
			$subclass_list = $this->CategoryList($category_fid);

		   		   if($subclass_list)
		   {
			   for ($i = 0; $i < count($subclass_list); $i++)
			   {
			 	 echo '<option value="'.$subclass_list[$i]['id'].'">'.$subclass_list[$i]['category_name'].'</option>';
			   }
		   }
		   else
		   {
		   		echo '<option value="0" selected="selected">没有分类</option>';
		   }
		   		   exit;
		}
		else
		{
					   		echo '<option value="none" selected="selected">没有分类</option>';
		   			   	exit;
		}



	}


}
?>