<?php
/**
 *
 * 微博图片的数据库逻辑操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: image.logic.php 5672 2014-05-06 06:51:50Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 *
 * 微博图片的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class ImageLogic
{
	
	var $table = 'topic_image';

	var $upload_count = 0;

	function ImageLogic() {

	}

	
	function get($p)
	{
		$wheres = array();

		if($p['where']){
			$wheres[] = $p['where'];
		}
		if(isset($p['id']))
		{
			$p['id'] = max(0, (int) $p['id']);
			if($p['id'] > 0) $wheres[] = " `id`='{$p['id']}' ";
		}
		if(isset($p['ids']))
		{
			$p['ids'] = $this->get_ids($p['ids'], 0);
			if($p['ids']) $wheres[] = " `id` in ({$p['ids']}) ";
		}
		if(isset($p['tid']))
		{
			$p['tid'] = max(0, (int) $p['tid']);
			$wheres[] = " `tid`='{$p['tid']}' ";
		}
		if(isset($p['tids']))
		{
			$p['tids'] = $this->get_ids($p['tids'], 0);
			if($p['tids']) $wheres[] = " `tid` in ({$p['tids']}) ";
		}
		if(isset($p['dateline_min']))
		{
			$p['dateline_min'] = max(0, (int) $p['dateline_min']);
			$wheres[] = " `dateline`>='{$p['dateline_min']}' ";
		}
		if(isset($p['dateline_max']))
		{
			$p['dateline_max'] = max(0, (int) $p['dateline_max']);
			$wheres[] = " `dateline`<='{$p['dateline_max']}' ";
		}
		if(isset($p['uid']))
		{
			$p['uid'] = max(0, (int) $p['uid']);
			$wheres[] = " `uid`='{$p['uid']}' ";
		}
		if(isset($p['uids']))
		{
			$p['uids'] = $this->get_ids($p['uids'], 0);
			if($p['uids']) $wheres[] = " `uid` in ({$p['uids']}) ";
		}

		$sql_where = ($wheres ? " where " . implode(" and ", $wheres) : "");

		$count = max(0, (int) $p['count']);
		if($count < 1)
		{
			$count = DB::result_first("select count(*) as `count` from ".DB::table($this->table)." $sql_where ");
		}
		$list = array();
		$page = array();
		if($count > 0)
		{
			$sql_limit = '';
			if($p['per_page_num'])
			{
				$page = page($count, $p['per_page_num'], $p['page_url'], array('return' => 'Array'));

				$sql_limit = " {$page['limit']} ";
			}
			elseif($p['limit'])
			{
				if(false !== strpos(strtolower($p['limit']), 'limit '))
				{
					$sql_limit = " {$p['limit']} ";
				}
				else
				{
					$sql_limit = " limit {$p['limit']} ";
				}
			} elseif ($p['count']) {
				$sql_limit = " LIMIT {$p['count']} ";
			}

			$sql_order = '';
			if($p['order'])
			{
				if(false !== strpos(strtolower($p['order']), 'order by '))
				{
					$sql_order = " {$p['order']} ";
				}
				else
				{
					$sql_order = " order by {$p['order']} ";
				}
			}

			$sql_fields = ($p['fields'] ? $p['fields'] : "*");

			$query = DB::query("select $sql_fields from ".DB::table($this->table)." $sql_where $sql_order $sql_limit ");
			while(false != ($r = DB::fetch($query)))
			{
				$r['image'] = $r['image_small'] = topic_image($r['id'], 'small', 0);
    			$r['image_original'] = $r['image_big'] = topic_image($r['id'], 'original', 0);

				$list[] = $r;
			}

			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}
		return array();
	}

	
	function get_my_image($uid=MEMBER_ID, $limit=6) {
		$uid = (is_numeric($uid) ? $uid : 0);
		if($uid < 1) {
			return false;
		}
		$limit = max(1, (int) $limit);

		$cache_id = "{$uid}-my_image-{$limit}";
		if(false !== ($ret = cache_db('get', $cache_id))) {
			return $ret;
		}

		$ret = array();

		$p = array(
			'where' => ' `tid` > 0 ',
			'count' => $limit,
			'uid' => $uid,
			'order' => ' `id` DESC ',
		);
		$rets = $this->get($p);
		if($rets) {
			$ret = $rets['list'];
		}

		cache_db('set', $cache_id, $ret, 36000);

		return $ret;
	}

	
	function get_info($id)
	{
		$id = max(0, (int) $id);
		if($id < 1) return array();

		$p = array(
			'id' => $id,
			'count' => 1,
		);
		$rets = $this->get($p);

		$ret = $rets['list'][0];

		return $ret;
	}

	
	function add($uid, $username = '', $item = '', $itemid = 0, $albumid = 0)
	{
		$uid = is_numeric($uid) ? $uid : 0;
		$itemid = is_numeric($itemid) ? $itemid : 0;
		$albumid = is_numeric($albumid) ? $albumid : 0;
		if($uid < 1) {
			$uid = MEMBER_ID;
		}
		if($uid < 1) return 0;

		if(!$username) {
			$username = DB::result_first("select `username` from ".DB::table('members')." where `uid`='$uid'");
		}
		if(!$username) return 0;

		$arr = array(
			'uid' => $uid,
			'username' => $username,
			'item' => $item,
			'itemid' => $itemid,
			'albumid' => $albumid,
			'dateline' => time(),
		);
		$ret = DB::insert($this->table, $arr, 1);


		return $ret;
	}

	
	function modify($p)
	{
		$id = (is_numeric($p['id']) ? $p['id'] : 0);
		if($id < 1) return 0;

		$info = $this->get_info($id);
		if(!$info) return 0;
		$vtid = $p['vtid'];		$sets = array();

		$_int_fields = array('tid', 'filesize', 'width', 'height', 'uid', 'dateline', 'views');
		foreach($_int_fields as $_field) {
			if(isset($p[$_field])) {
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('site_url', 'photo', 'name', 'description', 'username', 'image_url');
		foreach($_str_fields as $_field) {
			if(isset($p[$_field])) {
				$sets[$_field] = trim(strip_tags($p[$_field]));
			}
		}

		$ret = 0;
		if($sets) {
			$ret = DB::update($this->table, $sets, array('id' => $id));

						if(isset($sets['tid']) && $sets['tid'] > 0) {
				$tid = $sets['tid'] ? $sets['tid'] : $info['tid'];

				$this->set_topic_imageid($tid);
			} elseif ($vtid > 0) {
				$this->set_topic_verify_imageid($vtid,$id);
			}
		}

		if($ret && $info['uid']) {
			cache_db('rm', "{$info['uid']}-my_image-%", 1);
			cache_db('rm', "{$info['uid']}-get_photo_list-%", 1);
		}

		return $ret;
	}

	
	function delete($ids) {
		$p = array('ids' => $ids);
		$rets = $this->get($p);
		if(!$rets) return 0;

		$ret = 1;
		$tids = array();
		$uids = array();
		foreach($rets['list'] as $r) {
			$id = $r['id'];

			jio()->DeleteFile(topic_image($id, 'small'));
			jio()->DeleteFile(topic_image($id, 'photo'));
			jio()->DeleteFile(topic_image($id, 'original'));

			DB::query("delete from ".DB::table($this->table)." where `id`='$id'");

			if($r['tid'] > 0) {
				$tids[$r['tid']] = $r['tid'];
			}
			if($r['uid'] > 0) {
				$uids[$r['uid']] = $r['uid'];
			}
		}

		if($tids) {
			foreach($tids as $tid) {
				$this->set_topic_imageid($tid);
			}
		}
		if($uids) {
			foreach($uids as $uid) {
				cache_db('rm', "{$uid}-my_image-%", 1);
				cache_db('rm', "{$uid}-get_photo_list-%", 1);
			}
		}

		return $ret;
	}

	
	function set_tid($ids, $tid, $set_topic_imageid = 0)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$tid = (int) $tid;

		$ret = DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id` in ($ids) and `tid`='0'");

		if($tid > 0 && $set_topic_imageid) {
			$this->set_topic_imageid($tid);
		}

		return $ret;
	}

	
	function get_ids($ids, $checks = array('uid' => -1, 'tid' => null), $ret_arr = 0)
    {
    	$_ids = array();
    	if(is_numeric($ids))
    	{
    		$_ids[$ids] = $ids;
    	}
    	elseif(is_string($ids))
    	{
    		$_rs = explode(',', $ids);
            foreach($_rs as $_r)
            {
                $_ids[$_r] = $_r;
            }
    	}
        else
        {
            if($ids)
            {
                $_ids = (array) $ids;
            }
        }

        $ids = array();
        if($_ids)
        {
            foreach($_ids as $_r)
            {
            	$_r = trim($_r , ' ,"\'');
                $_r = is_numeric($_r) ? $_r : 0;
                if($_r > 0)
                {
                    $ids[$_r] = $_r;
                }
            }
        }

        if($ids && $checks)
        {
        	        	$_checks = array('uid' => 1, 'tid' => 0);

        	if(is_numeric($checks))
        	{
        		$checks = array('uid' => $checks);
        		if($checks['uid'] >= $_checks['uid'])
        		{
        			        		}
        	}

        	$check_sql = '';
        	foreach($_checks as $k => $_v)
        	{
        		if(isset($checks[$k]))
        		{
        			$v = $checks[$k];

        			if(is_numeric($v) && $v >= $_v)
        			{
        				$check_sql .= " and `$k`='$v' ";
        			}
        			elseif(is_string($v) && false !== strpos(" and ", strtolower($v)))
        			{
        				$check_sql .= " $v ";
        			}
        		}
        	}

            $query = DB::query("select `id` from ".DB::table($this->table)." where `id` in ('".implode("','", $ids)."') $check_sql ");
            $rets = array();
            while(false != ($rs = DB::fetch($query)))
            {
                $rets[$rs['id']] = $rs['id'];
            }

            $ids = $rets;
        }

    	if($ret_arr)
        {
        	return $ids;
        }
        else
        {
            return implode(",", $ids);
        }
    }

    
    function clear_invalid($time = 300)
    {
    	$p = array(
    		'tid' => 0,
    	);
    	if($time) {
    		$p['dateline_max'] = time() - $time;
    	}

    	$rets = $this->get($p);
    	if(!$rets) return 0;

    	$ids = array();
    	foreach($rets['list'] as $r) {
    		$ids[] = $r['id'];
    	}

    	return $this->delete($ids);
    }

    
    function clear_vote_invalid($time = 300){
    	$time = time() - $time;
    	$ids = array();
    	$query = DB::query("select * from `".TABLE_PREFIX."vote_image` where `vid` = 0 && `dateline` < '$time' ");
    	while ($rs = DB::fetch($query)) {
    		if($rs['picurl']){
    			@unlink($rs['picurl']);
    		}
    		$ids[] = $rs['id'];
    	}
    	if($ids){
    		DB::query("delete from `".TABLE_PREFIX."vote_image` where `id` in (".jimplode($ids).")");
    	}
    }

    
    function set_topic_imageid($tid, $imageid = null)
    {
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	if(!isset($imageid))
    	{
    		$imageids = array();
    		$p = array(
    			'tid' => $tid,
    		);
    		$rets = $this->get($p);
    		if($rets)
    		{
    			foreach($rets['list'] as $r)
    			{
    				$imageids[$r['id']] = $r['id'];
    			}
    		}

    		$imageid = implode(",", $imageids);
    	}
    	else
    	{
    		$imageid = $this->get_ids($imageid);
    	}

    	return DB::query("update ".DB::table('topic')." set `imageid`='$imageid' where `tid`='$tid'");
    }

    
    function set_topic_verify_imageid($tid, $imageid = null){
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	return DB::query("update ".DB::table('topic_verify')." set `imageid`='$imageid' where `id`='$tid'");
    }

    
    function image_list($ids) {
		$ids = $this->get_ids($ids, 0, 1);
    	$list = array();
    	if($ids) {
			$rets = $this->get(array('ids'=>$ids, 'count'=>count($ids), 'result_list_order_by_self' => 1, ));
    		if($rets) {
    			foreach($rets['list'] as $row) {
					$id = $row['id'];
					$iid = face_path($id).$id;
					$httpurl = $row['site_url'] ? $row['site_url'].'/' : $GLOBALS['_J']['config']['site_url'].'/';
					$list[$id] = array(
						'id' => $row['id'],
						'image' => $httpurl.'images/topic/'.$iid."_s.jpg",
						'image_small' => $httpurl.'images/topic/'.$iid."_s.jpg",
						'image_middle' => $httpurl.'images/topic/'.$iid."_p.jpg",
						'image_big' => $httpurl.'images/topic/'.$iid."_o.jpg",
						'image_original' => $httpurl.'images/topic/'.$iid."_o.jpg",
						'image_width' => $row['width'],
						'image_height' => $row['height'],
					);
    			}
    		}
    	}
    	return $list;
    }

    
    function loadImage($name,$pname){
		
		$image_name = $pname.".jpg";
				$image_path = RELATIVE_ROOT_PATH . 'images/index/';
		$image_file = $image_path . $image_name;

		if (!is_dir($image_path))
		{
			jio()->MakeDir($image_path);
		}

		jupload()->init($image_path,$name,true);

		jupload()->setNewName($image_name);
		$result=jupload()->doUpload();

		if($result)
        {
			$result = is_image($image_file);
		}
		if(!$result)
        {
        	unlink($image_file);
			return false;
		}
		$return_images = 'images/index/'.$image_name;
		if($GLOBALS['_J']['config']['ftp_on']) {
	        $ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];
			$ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
			if($ftp_result > 0) {
				jio()->DeleteFile($image_file);
				$return_images = $site_url .'/'. $return_images;
			}
	     }
		return $return_images;
    }

    function upload($p = array()) {
    	$sys_config = jconf::get();

    	if($sys_config['image_uploadify_queue_size_limit'] > 0 && $this->upload_count >= $sys_config['image_uploadify_queue_size_limit']) {
    		return array('error' => 'image upload limit', 'code' => 0);
    	}

    	$pic_url = (($p['pic_url'] && false!==strpos($p['pic_url'], ':/'.'/')) ? $p['pic_url'] : '');
    	$p['pic_field'] = ($p['pic_field'] ? $p['pic_field'] : 'topic');
    	$pic_field = (($p['pic_field'] && $_FILES[$p['pic_field']]) ? $p['pic_field'] : '');
    	if(!$pic_url && !$pic_field) {
    		return array('error'=>'pic is empty', 'code'=>-1);
    	}

    	$itemid = (is_numeric($p['itemid']) ? $p['itemid'] : 0);
		$albumid = (is_numeric($p['albumid']) ? $p['albumid'] : 0);
    	$item = '';
    	if($itemid > 0) {
    		$item = $p['item'];
    	}
    	$uid = (int) ($p['uid'] ? $p['uid'] : MEMBER_ID);
    	if($uid < 1 || false == ($member_info = jsg_member_info($uid))) {
    		return array('error'=>'uid is invalid', 'code'=>-2);
    	}

    			$image_id = $this->add($uid, $member_info['nickname'], $item, $itemid, $albumid);
		if($image_id < 1) {
			return array('error'=>'write database is invalid', 'code'=>-3);
		}

    			$image_path = RELATIVE_ROOT_PATH . 'images/topic/' . face_path($image_id);
		$image_name = $image_id . "_o.jpg";
		$image_file = $image_path . $image_name;
		$image_file_small = $image_path.$image_id . "_s.jpg";
		$image_file_middle = $image_path.$image_id . "_m.jpg";
		$image_file_photo = $image_path.$image_id . "_p.jpg";
		$image_file_temp = $image_path.$image_id . "_t.jpg"; 		if (!is_dir($image_path)) {
			jio()->MakeDir($image_path);
					}

    	if($pic_field) {
    		if(empty($_FILES) || !$_FILES[$pic_field]['name']) {
    			return array('error'=>'FILES is empty', 'code'=>-4);
    		}
    		$_FILES[$pic_field]['name'] = get_safe_code($_FILES[$pic_field]['name']); 
			
			jupload()->init($image_path,$pic_field,true,false);
			jupload()->setMaxSize($sys_config['image_size']);
			jupload()->setNewName($image_name);
			$ret = jupload()->doUpload();

						if(!$ret) {
				$this->delete($image_id);

				$rets = jupload()->getError();
				$ret = ($rets ? implode(" ", (array) $rets) : 'image upload is invalid');

				return array('error'=>$ret, 'code'=>-5);
			}
    	} elseif($pic_url) {
    		$temp_image = dfopen($pic_url);
    		if($temp_image) {
    			jio()->WriteFile($image_file,$temp_image);
    		} else {
    			return array('error'=>'image download is invalid','code'=>-6);
    		}
    	}

    	    	if(!is_image($image_file)) {
    		jio()->DeleteFile($image_file);
    		return array('error'=>'image file is invalid', 'code'=>-7);
    	}

    	@copy($image_file, $image_file_temp);

    			list($image_width,$image_height,$image_type,$image_attr) = getimagesize($image_file);
		$thumbwidth = min($sys_config['thumbwidth'],$image_width);
		$thumbheight = min($sys_config['thumbheight'],$image_width);

				$maxw = $sys_config['maxthumbwidth'];
		$maxh = $sys_config['maxthumbheight'];
		$result = makethumb($image_file, $image_file_small, $thumbwidth, $thumbheight, $maxw, $maxh, 0, 0, 0, 0, $sys_config['thumb_cut_type'], $sys_config['image_thumb_quality']);
		clearstatcache();
		if(!is_file($image_file)) {
			@copy($image_file_temp, $image_file);
		}

		$iw = $image_width;
		$ih = $image_height;
		if(!$sys_config['thumb_cut_type']) {
	    				if($image_width != $image_height) {
				if($maxw > 300 && $maxh > 300 && ($iw > $maxw || $ih > $maxh)) {
										list($iw, $ih) = getimagesize($image_file);
				}

				$src_x = $src_y = 0;
				$src_w = $src_h = min($iw, $ih);
				if($iw > $ih) {
					$src_x = round(($iw - $ih) / 2);
				} else {
					$src_y = round(($ih - $iw) / 2);
				}
				$result = makethumb($image_file, $image_file_small, $thumbwidth, $thumbheight, 0, 0, $src_x, $src_y, $src_w, $src_h, 0, $sys_config['image_thumb_quality']);
			}
			clearstatcache();
			if (!$result && !is_file($image_file_small)) {
				@copy($image_file_temp, $image_file_small);
			}
		}

    	    	$image_width_p = (int) $sys_config['image_width_p'];
    	if($image_width_p < 1) {
    		$image_width_p = 280;
    	}
		if($iw > $image_width_p) {
			$p_width = $image_width_p;
			$p_height = round(($ih*$image_width_p)/$iw);
			$result = makethumb($image_file, $image_file_photo, $p_width, $p_height, 0, 0, 0, 0, 0, 0, 0, $sys_config['image_thumb_quality']);
		}
		clearstatcache();
		if($iw <= $image_width_p || (!$result && !is_file($image_file_photo))) {
			@copy($image_file_temp, $image_file_photo);
		}
				if($sys_config['watermark_enable']) {
			$this->watermark($image_file, array('member_info' => $member_info, 'image_thumb_quality'=>$sys_config['image_thumb_quality']));
			clearstatcache();
			if(!is_file($image_file)) {
				@copy($image_file_temp, $image_file);
			}
		}

				$site_url = '';
		if($sys_config['ftp_on']) {
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];

			$ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
						if($ftp_result > 0) {
				ftpcmd('upload', $image_file_small,'',$ftp_key);
				ftpcmd('upload', $image_file_photo,'',$ftp_key);

				jio()->DeleteFile($image_file);
				jio()->DeleteFile($image_file_small);
				jio()->DeleteFile($image_file_photo);

				$image_file_small = $site_url . '/' . str_replace('./','',$image_file_small);
			}
		}

				$image_size = filesize($image_file);
		$name = addslashes(basename($_FILES[$pic_field]['name']));
		$p = array(
			'id' => $image_id,
			'site_url' => $site_url,
			'photo' => $image_file,
			'name' => $name,
			'filesize' => $image_size,
			'width' => $image_width,
			'height' => $image_height,
        	'tid' => max(0, (int) $p['tid']),
        	'image_url' => $pic_url,
		);
		$this->modify($p);

				jio()->DeleteFile($image_file_temp);

		$p['src'] = $image_file_small;

				$this->upload_count += 1;

		return $p;
    }

	
	function watermark($pic_path, $options = array()) {
		$sys_config = jconf::get();
		if (!$sys_config['watermark_enable']) {
			return false;
		}
		if(!is_image($pic_path)) {
			return false;
		}
		$ims = @getimagesize($pic_path);
		if(in_array($ims['mime'], array('image/gif'))) {
			return false;
		}

		$new_pic_path = $options['new_pic_path'];
		if('' == $new_pic_path) {
			$new_pic_path = $pic_path;
		}

		$image_quality = (int) $options['image_thumb_quality'];
		if($image_quality < 1 || $image_quality > 100) {
			$image_quality = 100;
		}

		require_once(ROOT_PATH . 'include/ext/thumb.class.php');
		$_thumb = new ThumbHandler();
		$_thumb->setSrcImg($pic_path);
		$_thumb->setDstImg($new_pic_path);
		$_thumb->setImgCreateQuality($image_quality);

				$_thumb->setMaskPosition($sys_config['watermark_position']);
				$_thumb->setMaskFontColor($sys_config['watermark_contents_color']);
				$_thumb->setMaskFontSize(max((int) $sys_config['watermark_contents_size'],12));

		$watermark = $options['watermark'];
		if('' == trim($watermark)) {
			$member_info = $options['member_info'];
			if(!$member_info) {
				$uid = (int) $options['uid'];
				$member_info = ($uid > 0 ? jsg_member_info($uid) : array());
			}
			$username = ($member_info['username'] ? $member_info['username'] : MEMBER_NAME);
			$nickname = ($member_info['nickname'] ? $member_info['nickname'] : MEMBER_NICKNAME);
			if($sys_config['watermark_contents'] && is_array($sys_config['watermark_contents'])){
				if(in_array('nickname',$sys_config['watermark_contents']) && in_array('url',$sys_config['watermark_contents'])){
					$_thumb->setMaskOffsetY(40);
					$_thumb->setMaskWord($sys_config['site_url'] . "/" . $username);

					$_thumb->createImg(100);
					$_thumb->setMaskOffsetY(10);
					$options['watermark'] = '@'. $nickname;
					return $this->watermark($pic_path, $options);
				} else if (in_array('nickname',$sys_config['watermark_contents'])){
					$watermark = '@'.$nickname;
				} else {
					$watermark = $sys_config['site_url'] . "/" . $username;
				}
			} else {
				$watermark = $sys_config['site_url'] . "/" . $username;
			}
		}
		if(is_file($watermark)) {
			$_thumb->setMaskImgPct(100);
			$_thumb->setMaskImg($watermark);
		} else {
						$mask_word = (string) $watermark;
			if($sys_config['watermark_contents'] && in_array('nickname',$sys_config['watermark_contents']) && is_file(RELATIVE_ROOT_PATH . 'images/jsg.ttf')) {
				$_thumb->setMaskFont(RELATIVE_ROOT_PATH . 'images/jsg.ttf');
				$mask_word = array_iconv($sys_config['charset'], 'utf-8', $mask_word);
			} elseif(preg_match('~[\x7f-\xff][\x7f-\xff]~', $mask_word)) {
				$mask_word = $sys_config['site_url'];
			}
			$_thumb->setMaskWord($mask_word);
		}

		return $_thumb->createImg(100);
	}

    
    public function update_ids($param){
        $where = 'id in ('.$param['ids'].')';
        unset($param['ids']);
        $set = '';
        $set .= "tid='".$param['tid']."'";
        DB::query("update `".TABLE_PREFIX."topic_image` set $set where $where");
    }

    
    public function remove_topic_image($id=0){
        $sql = "select * from ".TABLE_PREFIX."topic_image where `tid`<1" . ($id>0?" and `id`<'".($id - 10)."'":"");
		$query = DB::query($sql);
		while ($row = DB::fetch($query))
		{
			jio()->DeleteFile(topic_image($row['id'],'small'));
			jio()->DeleteFile(topic_image($row['id'],'original'));
		}
    }
		function get_site_url($id){
		return DB::result_first("select `site_url` from " . TABLE_PREFIX . "topic_image where `id`='$id'");
	}

		function get_uploadimg_byid($ids,$uid=0){
				$ids_arr = is_array($ids) ? $ids : (array) explode(',', $ids);		$images = array();
		$where = $uid ? " AND uid = '$uid'" : "";
		$query = DB::query("SELECT * FROM ".DB::table('topic_image')." WHERE id IN(".jimplode($ids_arr).")".$where);
		while ($value = DB::fetch($query)){
			$image = str_replace('./','',str_replace('_o.jpg','_s.jpg',$value['photo']));
			$value['img'] = $value['site_url'] ? $value['site_url'].'/'.$image : './'.$image;
			$value['description'] = $value['description'] ? $value['description'] : '图片简介';
			$images[$value['id']] = $value;
		}
		return $images;
	}

	function addalbum($name='') {
		$uid = MEMBER_ID;
		$name = jfilter($name, 'txt');
		$f_rets = filter($name);
		if(is_array($f_rets) && $f_rets['error']) {
			return 0;
		}
		if($uid > 0 && $name && $name != '请输入相册名称' && $name != '默认相册') {
			$albumid = DB::result_first("select count(*) from ".DB::table('album')." where `albumname`='$name' and uid = '$uid'");
			if($albumid == 0){
				$set_ary = array(
					'albumname' => $name,
					'uid' => MEMBER_ID,
					'username' => MEMBER_NICKNAME,
					'dateline' => time()
				);
				$albumid = DB::insert('album', $set_ary, true);
				return $albumid;
			}
		}
		return 0;
	}

	function getalbum($limit='',$listall=0,$uid=0){
		$albums = array();
		if($listall){
			if($uid > 0){
				$query = DB::query("select a.*,m.nickname from ".DB::table('album')." a left join ".DB::table('members')." m on a.uid=m.uid where a.uid = '{$uid}' order by a.picnum desc {$limit}");
			}else{
				$query = DB::query("select a.*,m.nickname from ".DB::table('album')." a left join ".DB::table('members')." m on a.uid=m.uid order by a.picnum desc {$limit}");
			}
		}else{
			$uid = MEMBER_ID;
			if($uid>0){
				$query = DB::query("select * from ".DB::table('album')." where uid = '{$uid}' order by picnum desc {$limit}");
			}
		}
		while(false != ($rs = DB::fetch($query))){
			$albums[$rs['albumid']] = $rs;
		}
		return $albums;
	}

	function checkalbumbyid($id=0){
		$return = false;
		$id = (int) $id;
		$albuminfo = DB::fetch_first("SELECT uid,purview FROM ".DB::table('album')." WHERE albumid = '{$id}'");
		if($albuminfo){
			if($albuminfo['purview'] == 0 || $albuminfo['uid'] == MEMBER_ID){
				$return = true;
			}elseif($albuminfo['purview'] == 2){
				$return = jlogic('buddy')->is_follow($albuminfo['uid']);
			}elseif($albuminfo['purview'] == 1){
				$return = jlogic('buddy')->is_fans($albuminfo['uid']);
			}
		}
		return $return;
	}

	function getalbumbyid($type='album',$id=0,$ismy=0){
		$table = 'album'==$type ? 'album' : 'topic_image';
		$sid = 'album'==$type ? 'albumid' : 'id';
		$where = $ismy ? " AND uid='".MEMBER_ID."'" : "";
		return DB::fetch_first("SELECT * FROM ".DB::table($table)." WHERE $sid = '{$id}'".$where);
	}

	function update_data(){
		$query = DB::query("SELECT albumid FROM ".DB::table('album')." ORDER BY albumid ASC");
		while ($value = DB::fetch($query)) {
			$id = $value['albumid'];
			$count = $this->albumimgnums($id,1);
			$this->update_album_picnum($id,$count);
		}
	}

	function update_album_picnum($id,$num){
		$id = (int) $id;
		$num = (int) $num;
		DB::query("UPDATE ".DB::table('album')." SET picnum='{$num}' WHERE albumid='{$id}'");
	}

	function get_albumname_byid($aid=0){
		$aid = (int) $aid;
		return DB::result_first("SELECT albumname FROM ".DB::table('album')." WHERE albumid = '$aid'");
	}

	function getalbumname($aid=0,$listall=0,$url=1,$uid=0){
		$aid = (int) $aid;
		$uid = (int) $uid;
		if($listall){
			$val = DB::fetch_first("SELECT a.albumname,a.uid,m.nickname FROM ".DB::table('album')." a LEFT JOIN ".DB::table('members')." m on a.uid=m.uid WHERE a.albumid = '$aid'");
			if($url){
				return '<a href="'.jurl('index.php?mod=album&code=list&uid='.$val['uid']).'">'.$val['nickname'].'的相册</a> >> '.$val['albumname'];
			}else{
				return $val['albumname'];
			}
		}else{
			$uid = $uid ? $uid : MEMBER_ID;
			return DB::result_first("SELECT albumname FROM ".DB::table('album')." WHERE uid = '{$uid}' and albumid = '$aid'");
		}
	}

	function getallalbumimg($aid=0,$limit='',$listall=0,$uid=0){
		$aid = (int) $aid;
		$uid = (int) $uid;
		$albumimgs = array();
		if($listall && $aid > 0){
			$query = DB::query("select * from ".DB::table('topic_image')." where albumid = '$aid' order by id desc {$limit}");
		}else{
			$uid = $uid ? $uid : MEMBER_ID;
			$query = DB::query("select * from ".DB::table('topic_image')." where uid = '{$uid}' and albumid = '$aid' order by id desc {$limit}");
		}
		while(false != ($rs = DB::fetch($query))){
			$albumimgs[] = $rs;
		}
		return $albumimgs;
	}

	function albumnums($listall=0,$uid=0){
		$uid = (int) $uid;
		if($listall){
			if($uid > 0){
				return DB::result_first("SELECT COUNT(*) FROM ".DB::table('album')." WHERE uid = '{$uid}'");
			}else{
				return DB::result_first("SELECT COUNT(*) FROM ".DB::table('album'));
			}
		}else{
			$uid = MEMBER_ID;
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table('album')." WHERE uid = '{$uid}'");
		}
	}
	function albumimgnums($aid=0,$listall=0,$uid=0){
		$aid = (int) $aid;
		$uid = (int) $uid;		
		if($listall && $aid > 0){
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_image')." WHERE albumid = '{$aid}'");
		}else{
			$uid = $uid ? $uid : MEMBER_ID;
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_image')." WHERE uid = '{$uid}' and albumid = '{$aid}'");
		}
	}

	function getalbumimg($aid=0,$page=1,$per=5){
		$uid = MEMBER_ID;
		$aid = (int) $aid;
		$page = max(1, (int) $page);
		$per = (int) $per;
		$albumimgs = array();
		if($per > 0) {
			$query = DB::query("select * from ".DB::table('topic_image')." where uid = '{$uid}' and albumid = '$aid' order by id desc limit ".($page-1)*$per.",".$per);
			while(false != ($rs = DB::fetch($query))){
				$albumimgs[] = $rs;
			}
		}
		return $albumimgs;
	}

	function delalbumimg($type='album',$id=0){
		$uid = MEMBER_ID;		$count = 0;
		$id = (int) $id;
		if($uid > 0 and $id > 0){
			if('album' == $type){
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('album')." WHERE `albumid`='$id' and `picnum` = 0 and `uid` = '$uid'");
				if($count > 0){
					DB::query("delete from ".DB::table('album')." where `albumid`='$id'");
				}
			}else{
				$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('topic_image')." where `id`='$id' and `tid` = 0 and `uid` = '$uid'");
				if($count > 0){
					jio()->DeleteFile(topic_image($id, 'small'));
					jio()->DeleteFile(topic_image($id, 'photo'));
					jio()->DeleteFile(topic_image($id, 'original'));
					$this->_update_album_pic_by_imgid($id);
				}
			}
		}
		return $count;
	}
	function _update_album_pic_by_imgid($img_id){		$pic = '';
		$img_id = (int) $img_id;
		$albumid = DB::result_first("SELECT albumid FROM ".DB::table('topic_image')." WHERE id = '{$img_id}'");
		DB::query("delete from ".DB::table('topic_image')." where `id`='$img_id'");
		if($albumid > 0){
			$image = DB::fetch_first("SELECT site_url,photo FROM ".DB::table('topic_image')." WHERE albumid='{$albumid}' ORDER BY id DESC LIMIT 1");
			if($image){
				$pic = '/'.str_replace('./','',str_replace('_o.jpg','_s.jpg',$image['photo']));
				$pic = $image['site_url'] ? $image['site_url'].$pic : '.'.$pic;
			}
			DB::query("update ".DB::table('album')." set pic = '{$pic}' where albumid = '{$albumid}'");
		}
	}

	function updateimg($id,$value,$aid=0){
		$uid = MEMBER_ID;		$aid = (int) $aid;
		$value = jfilter($value, 'txt');
		if($aid>0){
			DB::query("update ".DB::table('topic_image')." set albumid='{$aid}',description='{$value}' where id = '{$id}' and uid = '{$uid}'");
			$image = DB::fetch_first("SELECT site_url,photo FROM ".DB::table('topic_image')." WHERE id='{$id}'");
			$pic = '/'.str_replace('./','',str_replace('_o.jpg','_s.jpg',$image['photo']));
			$pic = $image['site_url'] ? $image['site_url'].$pic : '.'.$pic;
			DB::query("update ".DB::table('album')." set picnum = picnum + 1,pic ='{$pic}' where albumid = '{$aid}' and uid = '{$uid}'");
		}else{
			DB::query("update ".DB::table('topic_image')." set description='{$value}' where id = '{$id}' and uid = '{$uid}'");
		}
	}

	function updatealbum($id=0,$type='album',$name='0',$dep='',$oldname='0',$purview='0'){
		$uid = MEMBER_ID;		$id = (int) $id;
		$name = jfilter($name, 'txt');
		$dep = jfilter($dep, 'txt');
		if('image' == $type){
			DB::query("update ".DB::table('topic_image')." set albumid = '{$name}',description ='{$dep}' where id = '{$id}' and uid = '{$uid}'");
			if($name != $oldname){
				if($name > 0){
					DB::query("update ".DB::table('album')." set picnum = picnum + 1 where albumid = '{$name}' and uid = '{$uid}'");
				}
				DB::query("update ".DB::table('album')." set picnum = if(picnum>0,picnum-1,0) where albumid = '{$oldname}' and uid = '{$uid}'");
			}
		}else{
			DB::query("update ".DB::table('album')." set albumname = '{$name}',depict ='{$dep}',purview ='{$purview}' where albumid = '{$id}' and uid = '{$uid}'");
		}
	}

    
    function updateimg_contest($id,$value){
		$uid = MEMBER_ID;		$id = (int) $id;
		$r = jtable('topic_image')->update($value,array('uid'=>$uid,'id'=>$id));
        return $r;

	}

		function get_topic_by_imageid($imgid){
		$tids = array();
		$imgid = (int) $imgid;
		$query = DB::query("SELECT tid FROM ".DB::table('topic_topic_image')." where item_id ='{$imgid}' ORDER BY tid DESC");
		while ($value = DB::fetch($query)){
			$tids[] = $value['tid'];
		}
		return $tids;
	}
}
?>