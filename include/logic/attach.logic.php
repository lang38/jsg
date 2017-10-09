<?php
/**
 *文件名： attach.logic.php
 *作  者： 狐狸<foxis@qq.com>
 * @version $Id: attach.logic.php 5342 2014-01-02 08:25:15Z chenxianfeng $
 *功能描述：微博上传附件逻辑操作

 */
if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

/**
 *
 * 微博上传附件的数据库逻辑操作类
 *
 * @author 狐狸<foxis@qq.com>
 *
 */
class AttachLogic
{
	
	var $table;

	function AttachLogic()
	{
		$this->table = 'topic_attach';
	}

	
	function get($p)
	{
		$wheres = array();

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
		if(isset($p['item']))
		{
			$wheres[] = " `item`='{$p['item']}' ";
		}
		if(isset($p['itemid']))
		{
			$p['itemid'] = max(0, (int) $p['itemid']);
			if($p['itemid'] > 0) $wheres[] = " `itemid`='{$p['itemid']}' ";
		}
		if(isset($p['itemids']))
		{
			$p['itemids'] = $this->get_ids($p['itemids'], 0);
			if($p['itemids']) $wheres[] = " `itemid` in ({$p['itemids']}) ";
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
			$candown = jclass('member')->HasPermission('uploadattach','down');
			$canviewtype = array('doc','ppt','pdf','xls','txt','docx','xlsx','pptx');
			while(false != ($r = DB::fetch($query)))
			{
				$r['type'] = 'images/filetype/'.$r['filetype'].'.gif';
				$r['time'] = my_date_format($r['dateline']);
				$r['size'] = ($r['filesize'] > 1024*1024) ? round($r['filesize']/(1024*1024),2).'MB' : round($r['filesize']/1024,1).'KB';
				$r['url'] = ($r['site_url'] ? $r['site_url'] : $GLOBALS['_J']['site_url']).'/'.str_replace('./','',$r['file']);
				$r['onlineview'] = ($candown && in_array($r['filetype'],$canviewtype) && $r['score']==0) ? $r['url'] : '';
				$list[] = $r;
			}

			if($list)
			{
				return array('count'=>$count, 'list'=>$list, 'page'=>$page);
			}
		}

		return array();
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
	function get_down_info($id)
	{
		return DB::fetch_first("SELECT * FROM ".DB::table('topic_attach')." WHERE id = '{$id}' AND tid != 0");
	}
	function mod_download_num($id)
	{
		DB::query("update `".DB::table('topic_attach')."` set `download` = download + 1  where `id`='{$id}'");
	}

	
	function add($uid, $username = '', $item = '', $itemid = 0,$category='')
	{
		$uid = is_numeric($uid) ? $uid : 0;
		$itemid = is_numeric($itemid) ? $itemid : 0;
		if($uid < 1)
		{
			$uid = MEMBER_ID;
		}
		if($uid < 1) return 0;

		if(!$username)
		{
			$username = DB::result_first("select `nickname` from ".DB::table('members')." where `uid`='$uid'");
		}
		if(!$username) return 0;

		$arr = array(
			'uid' => $uid,
			'username' => $username,
			'item' => $item,
			'itemid' => $itemid,
            'category'=>$category,
			'dateline' => time(),
		);
		$ret = DB::insert($this->table, $arr, 1);
        if($category){            jlogic('attach_category')->update_category_count_by_attach($category);
        }
		return $ret;
	}

	
	function modify($p)
	{
		$id = (is_numeric($p['id']) ? $p['id'] : 0);
		if($id < 1) return 0;

		$info = $this->get_info($id);
		if(!$info) return 0;

		$sets = array();

		$_int_fields = array('tid', 'filesize', 'itemid', 'uid', 'dateline', 'download', 'score');
		foreach($_int_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = (int) $p[$_field];
			}
		}

		$_str_fields = array('filetype', 'description', 'name', 'file', 'site_url', 'username', 'item');
		foreach($_str_fields as $_field)
		{
			if(isset($p[$_field]))
			{
				$sets[$_field] = trim(strip_tags($p[$_field]));
			}
		}

		$ret = 0;

		if($sets)
		{
			$ret = DB::update($this->table, $sets, array('id' => $id));

						if(isset($sets['tid']))
			{
				$tid = $sets['tid'] ? $sets['tid'] : $info['tid'];

				$this->set_topic_attachid($tid);
			}
		}

		return $ret;
	}

	
	function delete($ids)
	{
		$p = array('ids' => $ids);
		$rets = $this->get($p);
		if(!$rets) return 0;
		$ret = 1;
		foreach($rets['list'] as $r)
		{
			$id = $r['id'];
			if($r['site_url']){
				$ftpkey = getftpkey($r['site_url']);
				ftpcmd('delete',$r['url'],'',$ftp_key);
			}else{
				jio()->DeleteFile(topic_attach($id));
			}
			$ret = $ret && DB::query("delete from ".DB::table($this->table)." where `id`='$id'");
			if($r['tid'] > 0)
			{
				$this->set_topic_attachid($r['tid']);
			}
		}
		return $ret;
	}

	
	function set_tid($ids, $tid, $set_topic_attachid = 0)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$tid = max(0, (int) $tid);

		$ret = DB::query("update ".DB::table($this->table)." set `tid`='$tid' where `id` in ($ids)");

		if($tid > 0 && $set_topic_attachid)
		{
			$this->set_topic_attachid($tid);
		}

		return $ret;
	}

	
	function set_downloads($ids, $downloads)
	{
		$ids = $this->get_ids($ids);
		if(!$ids) return 0;

		$downloads = is_numeric($downloads) ? $downloads : 0;
		$sign = substr((string) $downloads, 0, 1);
		$downloads_set = " `download`=" . (('-' == $sign || '+' == $sign) ? "`download`+{$downloads}" : "$downloads") . " ";

		$ret = DB::query("update ".DB::table($this->table)." set $downloads_set where `id` in ($ids)");

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
        			$checks['tid'] = $_checks['tid'];
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
        return TRUE;    	$p = array(
    		'tid' => 0,
    	);
    	if($time)
    	{
    		$p['dateline_max'] = time() - $time;
    	}

    	$rets = $this->get($p);
    	if(!$rets) return 0;

    	$ids = array();
    	foreach($rets['list'] as $r)
    	{
    		$ids[] = $r['id'];
    	}

    	return $this->delete($ids);
    }

    
    function set_topic_attachid($tid, $attachid = null)
    {
    	$tid = is_numeric($tid) ? $tid : 0;
    	if($tid < 1) return 0;

    	if(!isset($attachid))
    	{
    		$attachids = array();
    		$p = array(
    			'tid' => $tid,
    		);
    		$rets = $this->get($p);
    		if($rets)
    		{
    			foreach($rets['list'] as $r)
    			{
    				$attachids[$r['id']] = $r['id'];
    			}
    		}

    		$attachid = implode(",", $attachids);
    	}
    	else
    	{
    		$attachid = $this->get_ids($attachid);
    	}

    	return DB::query("update ".DB::table('topic')." set `attachid`='$attachid' where `tid`='$tid'");
    }

    
    function attach_list($ids)
    {
    	$ids = $this->get_ids($ids, 0, 1);

    	$list = array();
    	if($ids)
    	{
    		$query = DB::query("SELECT * FROM ".DB::table('topic_attach')." WHERE id IN(".jimplode($ids).")");
						$candown = jclass('member')->HasPermission('uploadattach','down');
			$canviewtype = array('doc','ppt','pdf','xls','txt','docx','xlsx','pptx');
			while($attach = DB::fetch($query))
    		{
				$attach_img = $attach['filetype'];
				$attach_name = $attach['name'];
				$attach_size = $attach['filesize'];
				$attach_down = $attach['download'];
				$attach_size = ($attach_size > 1024*1024) ? round($attach_size/(1024*1024),2).'MB' : ($attach_size == 0 ? '未知' : round($attach_size/1024,1).'KB');
				$attach_score = $attach['score'];
				$attach_file = RELATIVE_ROOT_PATH . $attach['file'];
				$attach_url = ($attach['site_url'] ? $attach['site_url'] : $GLOBALS['_J']['site_url']).'/'.str_replace('./','',$attach['file']);
    			$list[$attach['id']] = array(
    				'id' => $attach['id'],
    				'attach_img' => 'images/filetype/'.$attach_img.'.gif',
    				'attach_file' => $attach_file,
					'attach_name' => $attach_name,
					'attach_score' => $attach_score,
					'attach_down' => $attach_down,
					'attach_size' => '大小:'.$attach_size,
					'url' => $attach_url,
					'onlineview' => ($candown && in_array($attach_img,$canviewtype) && $attach_score==0) ? $attach_url : '',
    			);
    		}
    	}

    	return $list;
    }

	
	function down_hot_attach()
	{
		$hotattachs = array();
		$i = 1;
		$query = DB::query("SELECT tid,name,download FROM ".DB::table('topic_attach')." WHERE tid>0 ORDER BY download DESC LIMIT 10");
		while ($value = DB::fetch($query))
		{
			$value['id'] = $i;
			$hotattachs[$value['id']] = $value;
			$i++;
		}
		return $hotattachs;
	}

	
	function attachs_list($num = 10, $where = '')
	{
		$total_attach = DB::result_first("SELECT count(*) FROM ".DB::table('topic_attach')." WHERE tid>0 {$where}");
		$page_arr = page($total_attach,$num,'index.php?mod=attach',array('return'=>'array'));
		$limit_sql = $page_arr['limit'];
		$attachs = array();
		$query = DB::query("SELECT * FROM ".DB::table('topic_attach')." WHERE tid>0 {$where} ORDER BY id DESC {$limit_sql}");
		$candown = jclass('member')->HasPermission('uploadattach','down');
		$canviewtype = array('doc','ppt','pdf','xls','txt','docx','xlsx','pptx');
		while ($value = DB::fetch($query))
		{
			$value['manage'] = (jallow($value[uid])) ? true : false;
			$value['filesize'] = ($value['filesize'] > 1024*1024) ? round($value['filesize']/(1024*1024),2).'MB' : ($value['filesize'] == 0 ? '未知' : round($value['filesize']/1024,1).'KB');
			$value['dateline'] = my_date_format2($value['dateline']);
			$value['img'] = 'images/filetype/'.$value['filetype'].'.gif';
			$value['url'] = ($value['site_url'] ? $value['site_url'] : $GLOBALS['_J']['site_url']).'/'.str_replace('./','',$value['file']);
			$value['onlineview'] = ($candown && in_array($value['filetype'],$canviewtype) && $value['score']==0) ? $value['url'] : '';
			$attachs[$value['id']] = $value;
		}
		$return = array('list' => $attachs);
		$return['page'] = ($page_arr ? $page_arr : $pagenum);
		return $return;
	}

    
    function upload($p){
                $sys_config = jconf::get();
        if(!$_FILES[$p['field']] || !$_FILES[$p['field']]['name']){
            return array('error'=>'attach is empty', 'code'=>-1);
        }
        $itemid = (is_numeric($p['itemid']) ? $p['itemid'] : 0);
    	$item = '';
    	if($itemid > 0) {
    		$item = $p['item'];
    	}
    	$uid = (int) ($p['uid'] ? $p['uid'] : MEMBER_ID);
    	if($uid < 1 || false == ($member_info = jsg_member_info($uid))) {
    		return array('error'=>'uid is invalid', 'code'=>-2);
    	}
        $_FILES[$p['field']]['name'] = get_safe_code($_FILES[$p['field']]['name']);

        		$att_id = $this->add($uid, $member_info['nickname'], $item, $itemid);
		if($att_id < 1) {
			return array('error'=>'write database is invalid', 'code'=>-3);
		}

        $filetype = end(explode('.',$_FILES[$p['field']]['name']));

        $att_name = $att_id.'.'.$filetype;
        $att_path = RELATIVE_ROOT_PATH . 'data/attachs/topic/' . face_path($att_id);
        $att_file = $att_path.$att_name;
        if (!is_dir($att_path)) {
			jio()->MakeDir($att_path);
		}

        jupload()->init($att_path,$p['field'],false,true);
		jupload()->setMaxSize($sys_config['attach_size_limit']);
		jupload()->setNewName($att_name);
		$ret = jupload()->doUpload();
        		if(!$ret) {
			$this->delete($att_id);
			$rets = jupload()->getError();
			$ret = ($rets ? implode(" ", (array) $rets) : 'image upload is invalid');
			return array('error'=>$ret, 'code'=>-5);
		}

        $site_url = '';
		if($sys_config['ftp_on']) {
			$ftp_key = randgetftp();
			$get_ftps = jconf::get('ftp');
			$site_url = $get_ftps[$ftp_key]['attachurl'];

			$ftp_result = ftpcmd('upload',$att_file,'',$ftp_key);
						if($ftp_result > 0) {
				jio()->DeleteFile($att_file);
				$att_file = $site_url . '/' . str_replace('./','',$att_file);
			}
		}
        $att_size = filesize($att_file);
        $p = array(
			'id' => $att_id,
			'site_url' => $site_url,
			'photo' => $att_file,
			'name' => $_FILES[$p['field']]['name'],
			'filesize' => $att_size,
            'filetype'=>$filetype,
        	'tid' => max(0, (int) $p['tid']),
        	'uid'=>$uid,
            'username'=>$member_info['nickname'],
            'dateline'=>(int)time(),
		);
        $this->modify($p);
        return $p;
    }

	function type_filter($data){
		$datas = explode('|',$data);
		$errortypes = array('asp','php','jsp','aspx','exe','bat','dll','com','sys');
		foreach($datas as $k => $v){
			$v = trim($v);
			$datas[$k] = $v;
			if(in_array($v,$errortypes)){
				unset($datas[$k]);
			}
		}
		$data = implode('|',$datas);
		return $data;
	}

}

?>