<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename cms.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1972366880 29654 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class CmsLogic
{
	function CmsLogic() {
	}

	
	function Getonecategory($catid=0) {
		$row = DB::fetch_first("SELECT * FROM ".DB::table('cms_category')." WHERE catid = '$catid'");
		return $row;
	}

		function get_down_category($catid=0) {
		$category = array();
		global $_J;
		$query = DB::query("SELECT * FROM ".DB::table('cms_category')." WHERE parentid = '$catid' ORDER BY displayorder ASC");
		while($val=DB::fetch($query)){
		   $val['purview'] = (empty($val['purview']) || in_array($_J['member']['role_id'],explode(',',$val['purview'])) || in_array(MEMBER_ID,explode(',',$val['manageid']))) ? 1 : 0;		   $category[] = $val;
		}
		return $category;
	}

		function Getulli($catid=0,$urlid=0,$tupid=0) {
		$ulli = '';
		if($catid>0){$style = 'iin';}else{$style = '';}
		if($tupid>0){$where = " AND catid = '{$tupid}' ";}else{$where = '';}
		$total = DB::result_first("SELECT count(*) FROM ".DB::table('cms_category')." WHERE parentid = '$catid'" .$where);
		if($total){
			$query = DB::query("SELECT catid, catname, articles FROM ".DB::table('cms_category')." WHERE parentid = '$catid' ".$where." ORDER BY displayorder ASC");
			$ulli .= '<ul id="cms_'.$catid.'" class="'.$style.'">';
			while($val=DB::fetch($query)){
				$anums = $val['articles']>0 ? '('.$val['articles'].')' : '';
				if($urlid == $val['catid']){$val['catname'] = '<font color="red">'.$val['catname'].'</font>';}
				$totals = DB::result_first("SELECT count(*) FROM ".DB::table('cms_category')." WHERE parentid = '".$val['catid']."'");
				if($totals){
					$ulli .= '<li><img id="cms_img_'.$val['catid'].'" src="images/cp/open.gif" onclick="cat_list(this,\''.$val['catid'].'\',\''.$urlid.'\');"><a href="index.php?mod=cms&code=channel&id='.$val['catid'].'">'.$val['catname'].'</a>'.$anums.'</li>';
				}else{
					$ulli .= '<li class="in"><a href="index.php?mod=cms&code=channel&id='.$val['catid'].'">'.$val['catname'].'</a>'.$anums.'</li>';
				}
			}
			$ulli .= '</ul>';
		}
		return $ulli;
	}

		function getnavbycatid($catid=0,$url='index.php?mod=cms'){
        $navhtml = '<a href="'.$url.'">CMS文章</a>';
		if($catid){
			$category = $this->Getonecategory($catid);
		}else{
			$navhtml .= '>>首页';
		}
		if($category){
			$query = DB::query("SELECT catid,catname FROM ".DB::table('cms_category')." WHERE catid IN(".trim($category['likecatid'],',').")");
			while($val=DB::fetch($query)){
			$navhtml .= '>><a href="'.$url.'&code=channel&id='.$val['catid'].'">'.$val['catname'].'</a>';
			}
		}
		return $navhtml;
	}

	
	

		function get_category_html($catid=0) {
		$html = '';
		$category = $this->Getonecategory($catid);
		if($category){
			$upids = array_filter(explode(',',trim($category['upid'],',')));
		}
		if($upids[0]>0){
			$upids[] = $catid;
		}else{
			$upids[0] = $catid;
		}
		if($catid>0){			$upids[] = $catid;
		}
		$i = 0;
		foreach($upids as $k => $v){
			$j = $k+1;
			$datas = $this->get_down_category($i);
			if($datas){
			$html .= '<span id="nextcategory_'.$k.'"><select name="categoryids[]" id="categoryids_'.$k.'" onchange="listnextcategory(this,\''.$j.'\');"><option value="">请选择...</option>';
			foreach($datas as $val){
				if($val['purview']){
					if($val['catid'] == $v){
						$html .= '<option value="'.$val['catid'].'" selected>'.$val['catname'].'</option>';
					}else{
						$html .= '<option value="'.$val['catid'].'">'.$val['catname'].'</option>';
					}
				}else{
					$html .= '<option value="'.$val['catid'].'" disabled = "true">'.$val['catname'].'</option>';
				}
			}
			$html .= '</select></span>';
			}
			$i = $v;
		}
		return $html;
	}

		function create($data) {
		global $_J;
		
		$data['imageid'] = DB::filter_in_num($data['imageid']);
		$data['attachid'] = DB::filter_in_num($data['attachid']);
		
		$category = $this->Getonecategory($data['catid']);
		if(MEMBER_ID > 0 && $category && (empty($category['purview']) || in_array($_J['member']['role_id'],explode(',',$category['purview'])) || in_array(MEMBER_ID,explode(',',$category['manageid'])))){
			$check = $category['verify'] && !in_array(MEMBER_ID,explode(',',$category['manageid'])) && !in_array($_J['member']['role_id'],explode(',',$category['filter'])) ? 0 : 1;
			$cmsdata = array(
				'title' => jhtmlspecialchars($data['title']),
				'content' => jhtmlspecialchars($data['content']),
				'catid' => $data['catid'],
				'imageid' => $data['imageid'],
				'attachid' => $data['attachid'],
				'likecatid' => $category['likecatid'],
				'likemanageid' => $category['manageid'],
				'dateline' => time(),
				'uid' => MEMBER_ID,
				'username' => MEMBER_NICKNAME,
				'check' => $check,
			);
			$aid = DB::insert('cms_article', $cmsdata, true);
			if($data['imageid']){
				DB::query("UPDATE ".DB::table('topic_image')." SET item='cms',itemid='{$aid}' WHERE id IN(".$data['imageid'].")");
			}
			if($data['attachid']){
				DB::query("UPDATE ".DB::table('topic_attach')." SET item='cms',itemid='{$aid}' WHERE id IN(".$data['attachid'].")");
			}
            if($check>0){
                $this->update_cat_count($data['catid'],1,true);
            }
			$topicdata = array(
				'content' => cut_str($data['content'], 140, ''),
				'imageid' => $data['imageid'],
				'attachid' => $data['attachid'],
				'item' => 'cms',
				'item_id' => $aid,
			);
			jlogic('topic')->Add($topicdata);			return $check ? $aid : 0;
		}else{
			return -1;		}
	}

		function modify($aid,$data) {
		$category = $this->Getonecategory($data['catid']);
		$article = $this->getarticlebyid($aid);
		$oldcategory = $this->Getonecategory($article['catid']);
		if(MEMBER_ROLE_TYPE == 'admin' || (MEMBER_ID > 0 && $category && $article && $oldcategory && (in_array(MEMBER_ID,explode(',',$oldcategory['manageid'])) || ($article['uid']==MEMBER_ID && !$article['check'])))){
			$cmsdata = array(
				'title' => jhtmlspecialchars($data['title']),
				'content' => jhtmlspecialchars($data['content']),
				'catid' => $data['catid'],
				'imageid' => $data['imageid'],
				'attachid' => $data['attachid'],
				'likecatid' => $category['likecatid'],
				'lastupdate' => time(),
				'likemanageid' => $category['manageid'],
			);
			$role_id = DB::result_first("SELECT role_id FROM ".DB::table('members')." WHERE uid='".$article['uid']."'");
			if($data['catid'] != $article['catid'] && !(empty($category['purview']) || in_array($role_id,explode(',',$category['purview'])) || in_array(MEMBER_ID,explode(',',$category['manageid'])))){
				$return = -2;			}else{
                $old_catid = jtable('cms_article')->val(array('aid' => $aid),'catid');
				DB::update('cms_article', $cmsdata, array('aid' => $aid));
				$return = $aid;
				                if($old_catid != $data['catid']){
                    $this->update_cat_count($old_catid, 1, FALSE);                    $this->update_cat_count($data['catid'], 1, TRUE);                }
			}
			return $return;
		}else{
			return -1;		}
	}

		function addreply($data) {
		$article = DB::result_first("SELECT title FROM ".DB::table('cms_article')." WHERE aid='".$data['aid']."'");
		if(MEMBER_ID > 0 && $article){
			$cmsreplydata = array(
				'content' => jhtmlspecialchars($data['content']),
				'aid' => $data['aid'],
				'dateline' => time(),
				'uid' => MEMBER_ID,
				'username' => MEMBER_NICKNAME,
			);
			$rid = DB::insert('cms_reply', $cmsreplydata, true);
			if($data['totopic'] > 0){
				$topicdata = array(
					'content' => '评论CMS文章《'.$article.'》：'.cut_str($data['content'], 140, ''),
					'item' => 'cms',
					'item_id' => $data['aid'],
				);
				jlogic('topic')->Add($topicdata);			}
			DB::query("UPDATE ".DB::table('cms_article')." SET replys=replys+1 WHERE aid ='".$data['aid']."'");			return $rid;
		}else{
			return -1;		}
	}

		function ismanageman($catid=0){
		$return = 0;
		$where = '';
		if($catid > 0){$where = " AND catid = '{$catid}' ";}
		if(MEMBER_ID > 0){
			$uid = ','.MEMBER_ID.',';
			$return = DB::result_first("SELECT COUNT(*) FROM ".DB::table('cms_category')." WHERE manageid LIKE '%{$uid}%' {$where}");
		}
		return $return;
	}

		function check($aids,$isadmin=0){
		$aids = is_array($aids) ? $aids : array($aids);
		$aids = implode(',',$aids);
		if(!$isadmin){			$checkids = array();
			$query = DB::query("SELECT aid FROM ".DB::table('cms_article')." WHERE aid IN({$aids}) AND likemanageid LIKE '%,".MEMBER_ID.",%'");
			while ($value = DB::fetch($query)){
				$checkids[] = $value['aid'];
			}
			$aids = implode(',',$checkids);
		}
		if($aids){
			DB::query("update `".DB::table('cms_article')."` set `check` = 1,`checktime` = '".time()."',`checkname` = '".MEMBER_NICKNAME."' where `aid` IN({$aids})");
			            foreach (explode(',',$aids) as $one){
                $catid = jtable('cms_article')->val(array('aid' => $one),'catid');
                $this->update_cat_count($catid, 1, TRUE);            }
            return '审核成功';
		}
		return '操作错误或没有权限';

	}

		function getarticlebyid($aid){
		$row = DB::fetch_first("SELECT * FROM ".DB::table('cms_article')." WHERE aid = '$aid'");
		if($row){
			$row['edit'] = (MEMBER_ID > 0 && (MEMBER_ROLE_TYPE == 'admin' || in_array(MEMBER_ID,explode(',',$row['likemanageid'])) || ($row['uid']==MEMBER_ID && !$row['check']))) ? 1 : 0;
			if($row['imageid']){
				$query = DB::query("SELECT * FROM ".DB::table('topic_image')." WHERE id IN(".$row['imageid'].")");
				while ($value = DB::fetch($query)){
					$image = str_replace('./','',str_replace('_o.jpg','_s.jpg',$value['photo']));
					$row['images'][$value['id']]['img'] = $value['site_url'] ? $value['site_url'].'/'.$image : $image;
				}
			}
			if($row['attachid']){
				$query = DB::query("SELECT * FROM ".DB::table('topic_attach')." WHERE id IN(".$row['attachid'].")");
				$candown = jclass('member')->HasPermission('uploadattach','down');
				$canviewtype = array('doc','ppt','pdf','xls','txt','docx','xlsx','pptx');
				while ($value = DB::fetch($query)){
					$attach_url = ($value['site_url'] ? $value['site_url'] : $GLOBALS['_J']['site_url']).'/'.str_replace('./','',$value['file']);
					$row['attachs'][$value['id']]['img'] = 'images/filetype/'.$value['filetype'].'.gif';
					$row['attachs'][$value['id']]['name'] = $value['name'];
					$row['attachs'][$value['id']]['score'] = $value['score'];
					$row['attachs'][$value['id']]['onlineview'] = ($candown && in_array($value['filetype'],$canviewtype) && $value['score']==0) ? $attach_url : '';
				}
			}
		}
		return $row;
	}
		function getarticleviewbyid($aid){
		$row = $this->getarticlebyid($aid);
		if($row && $row['check']){
		if(preg_match_all('~\[image\](.+?)\[\/image\]~', $row['content'], $match)) {
			foreach ($match[0] as $k => $v) {
				if($image_url = str_replace('_s.jpg','_o.jpg',$row['images'][$match[1][$k]]['img'])){
					$imageHTML = '<br><img src="'.$image_url.'">';
					$row['content'] = str_replace($v, $imageHTML, $row['content']);
					unset($row['images'][$match[1][$k]]);
				}else{
					$row['content'] = str_replace($v, '', $row['content']);
				}
			}
		}
		if($row['images']){
			foreach($row['images'] as $key => $val){
				$row['images'][$key]['img'] = str_replace('_s.jpg','_o.jpg',$val['img']);
			}
		}
		$row['time'] = date('Y-m-d H:i:s',$row['dateline']);
		$row['content'] = nl2br($row['content']);
		$row['replycontent'] = array();
		if($row['replys'] > 0){
			$query = DB::query("SELECT * FROM ".DB::table('cms_reply')." WHERE aid = '".$row['aid']."' ORDER BY rid DESC LIMIT 20");
			while ($value = DB::fetch($query)){
				$value['time'] = date('Y-m-d H:i:s',$value['dateline']);
				$row['replycontent'][$value['rid']] = $value;
			}
		}
				$row['navhtml'] = $this->getnavbycatid($row['catid']);
		$row['navhtml'] .= '>>浏览文章';
		return $row;
		}
	}

		function searcharticlenums($seachchar){
		$seachchar = jhtmlspecialchars($seachchar);
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table('cms_article')." WHERE title LIKE '%{$seachchar}%' or content LIKE '%{$seachchar}%'");
	}
	function getarticlebysearch($seachchar,$limit=''){
		$articles = array();
		$seachchar = jhtmlspecialchars($seachchar);
		$query = DB::query("SELECT `aid`, `title`, `check`, `uid`, `dateline`, `likemanageid` FROM ".DB::table('cms_article')." WHERE title LIKE '%{$seachchar}%' or content LIKE '%{$seachchar}%' ORDER BY aid DESC {$limit}");
		while ($value = DB::fetch($query)){
			$value['edit'] = (MEMBER_ID > 0 && (MEMBER_ROLE_TYPE == 'admin' || in_array(MEMBER_ID,explode(',',trim($value['likemanageid'],','))) || ($value['uid']==MEMBER_ID && !$value['check']))) ? 1 : 0;
			$value['cancheck'] = (MEMBER_ID > 0 && in_array(MEMBER_ID,explode(',',trim($value['likemanageid'],',')))  && !$value['check']) ? 1 : 0;
			$value['time'] = date('Y-m-d H:i:s',$value['dateline']);
			$articles[$value['aid']] = $value;
		}
		return $articles;
	}

		function catidarticlenums($catid=0,$ischeck=0,$ismy=0){
		if($ischeck && MEMBER_ID > 0){
			$uids = ','.MEMBER_ID.',';
			$where = " AND `check` = 0 AND likemanageid LIKE '%{$uids}%'";
		}else{
			$where = " AND `check` = 1 ";
		}
		if($ismy && MEMBER_ID > 0){
			$where = " AND `uid` = '".MEMBER_ID."' ";
		}
		if($catid){
			$catgory = $this->Getonecategory($catid);
			$fids = $catgory['likecatid'];
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table('cms_article')." WHERE likecatid LIKE '%{$fids}%' {$where}");
		}else{
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table('cms_article')." WHERE 1=1 {$where}");
		}
	}
	function getarticlebycatid($catid=0,$limit='',$ischeck=0,$ismy=0){
		$articles = array();
		if($ischeck && MEMBER_ID > 0){
			$uids = ','.MEMBER_ID.',';
			$where = " AND `check` = 0 AND likemanageid LIKE '%{$uids}%'";
		}else{
			$where = " AND `check` = 1 ";
		}
		if($ismy && MEMBER_ID > 0){
			$where = " AND `uid` = '".MEMBER_ID."' ";
		}
		if($catid){
			$catgory = $this->Getonecategory($catid);
			$fids = $catgory['likecatid'];
			$query = DB::query("SELECT `aid`, `title`, `check`, `uid`, `dateline`, `likemanageid` FROM ".DB::table('cms_article')." WHERE likecatid LIKE '%{$fids}%' {$where} ORDER BY aid DESC {$limit}");
		}else{
			$query = DB::query("SELECT `aid`, `title`, `check`, `uid`, `dateline`, `likemanageid` FROM ".DB::table('cms_article')." WHERE 1=1 {$where} ORDER BY aid DESC {$limit}");
		}
		while ($value = DB::fetch($query)){
			$value['edit'] = (MEMBER_ID > 0 && (MEMBER_ROLE_TYPE == 'admin' || in_array(MEMBER_ID,explode(',',trim($value['likemanageid'],','))) || ($value['uid']==MEMBER_ID && !$value['check']))) ? 1 : 0;
			$value['cancheck'] = (MEMBER_ID > 0 && in_array(MEMBER_ID,explode(',',trim($value['likemanageid'],',')))  && !$value['check']) ? 1 : 0;
			$value['time'] = date('Y-m-d H:i:s',$value['dateline']);
			$articles[$value['aid']] = $value;
		}
		return $articles;
	}

    
    public function get_category_sublist($id = 0) {

        $r = jtable('cms_category')->get(array('parentid' => (int) $id,'sql_order'=> 'displayorder'));
        if ($r['count']){
            foreach ($r['list'] as &$one){
                $count = jtable('cms_category')->count(array('parentid' => (int) $one['catid']));
                $one['count_sub'] = (int)$count;
            }
            return $r['list'];
        }
        else
            return false;
    }

    
    public function add_category($data) {
        $data['parentid'] = (int) $data['parentid'];

        $data = $this->data_process_category($data);
        unset($data['likecatid']);
        $r = jtable('cms_category')->insert($data, 1);
        if($r){
            $likecatid = $r;
            if($data['parentid']){
                $likecatid = rtrim($data['upid'],',').','.$r;
            }
            $likecatid = ','.$likecatid.',';
            $r = jtable('cms_category')->update(array('likecatid'=>$likecatid), array('catid'=>$r));
        }
        return $r;
    }


    public function delete_category($catid){
        $info = $this->get_category_info($catid);
        if(!$info){
            return TRUE;
        }
        $r = jtable('cms_category')->count(array('parentid' => $catid));
        if($r>0){
            return -1;
        }
        $r = jtable('cms_category')->delete(array('catid' => $catid));
        if ($r) {
                        $r = jtable('cms_article')->delete(array('like@likecatid' => $info['likecatid'].'%'));
                        $this->update_cat_count($catid, $info['articles'], FALSE);
        }
        return $r;
    }
    
    public function delete_all_category($catid) {
        $info = $this->get_category_info($catid);
        if(!$info){
            return TRUE;
        }
        $r = jtable('cms_category')->delete(array('like@likecatid' => $info['likecatid'].'%'));
        if ($r) {
                        $r = jtable('cms_article')->delete(array('like@likecatid' => $info['likecatid'].'%'));
                        $this->update_cat_count($catid, $info['articles'], FALSE);
        }
        return $r;
    }

    
    public function get_category_info($catid) {
        return jtable('cms_category')->info(array('catid' => $catid));
    }

    
    public function modify_category($data,$catid){
        $par_likecatid = jtable('cms_category')->val(array('catid'=>$data['parentid']),'likecatid');
        $par_likecatid = array_filter(explode(',', $par_likecatid));
        if(in_array($catid, $par_likecatid)){
                    return FALSE;
        }
        $data = $this->data_process_category($data, $catid);
        $info = $this->get_category_info($catid);
        if($info['parentid'] != $data['parentid']){
                        $this->update_cat_count_for_MC($info,$data['upid']);
                        $this->update_articles_cat($info,$data['upid'],$data['likecatid']);
        }
        $r = jtable('cms_category')->update($data, array('catid'=>$catid));
                jtable('cms_article')->update(array('likemanageid'=>$data['manageid']),array('catid'=>$info['catid']));
        return $r;
    }

    
    private function data_process_category($data, $catid=0){
        $data['upid'] = $data['parentid'];
        $data['likecatid'] = $catid;
        if ($data['parentid']) {
            $parent = jtable('cms_category')->info(array('catid' => $data['parentid']));
            while ($parent['parentid']) {
                $data['upid'] = $parent['parentid'] . ',' . $data['upid'];
                $parent = jtable('cms_category')->info(array('catid' => $parent['parentid']));
            }
            $data['likecatid'] = trim($data['upid'],',').','.$data['likecatid'];
        }
        $data['likecatid'] = ','.trim($data['likecatid'],',').',';
        $data['purview'] = implode(',', $data['purview']);
        $data['filter'] = implode(',', $data['filter']);
        $data['manageid'] = array();
        foreach ($data['managename'] as $key=>$one) {
            $uid = jtable('members')->val(array('nickname' => trim($one)), 'uid');
            if($uid){
                $data['manageid'][] = $uid;
            }  else {
                unset($data['managename'][$key]);
            }
        }
        $data['manageid'] = $data['manageid'] ? ','.implode(',', $data['manageid']).',' : '';
        $data['managename'] = implode(',', $data['managename']);
		if($data['template'] &&  !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $data['template'])){
			$data['template'] = '';
		}
		if(!jclass('jishigou/template')->exists('cms/'.$data['template'])){
			$data['template'] = '';
		}
        return $data;
    }

    
    private function update_cat_count_for_MC($info,$new_upid,$num=0){
                if(!$num){
            $num = $info['articles'];
        }
        $parents = array_filter(explode(',', $info['upid']));
        $r = jtable('cms_category')->update(array('-@articles'=>$num),$parents);
                $parents = array_filter(explode(',', $new_upid));
        $r = jtable('cms_category')->update(array('+@articles'=>$num),$parents);

    }

    
    private function update_articles_cat($info,$newupid,$newlikecatid){
                DB::query("UPDATE ".DB::table('cms_category')." SET upid = REPLACE(upid,'{$info['upid']}','{$newupid}'),likecatid = REPLACE(likecatid,'{$info['likecatid']}','{$newlikecatid}') WHERE likecatid LIKE '%{$info['likecatid']}%'");
                DB::query("UPDATE ".DB::table('cms_article')." SET likecatid = REPLACE(likecatid,'{$info['likecatid']}','{$newlikecatid}') WHERE likecatid LIKE '%{$info['likecatid']}%'");

        return true;
    }

    
    public function uncheck($article_id){
        $article_id = is_array($article_id) ? $article_id : array($article_id);
        $r = jtable('cms_article')->update(array('check'=>0),$article_id);
                foreach ($article_id as $one){
            $catid = jtable('cms_article')->val(array('aid' => $one),'catid');
            $this->update_cat_count($catid, 1, FALSE);        }
        return $r;
    }
    
    public function delete_article($article_id){
        $article_id = is_array($article_id) ? $article_id : array($article_id);
                foreach ($article_id as $one){
            $catid = jtable('cms_article')->val(array('aid' => $one),'catid');
            $this->update_cat_count($catid, 1, FALSE);        }
		$r = jtable('cms_article')->delete($article_id);
        return $r;
    }
    
    public function search_category($key){
        if(!trim($key))
            return FALSE;
        $r = jtable('cms_category')->get(array('like@catname'=>'%'.trim($key).'%'));
        return $r['list'];
    }

    
    private function update_cat_count($catid,$value,$operator=true){
        $info = $this->get_category_info($catid);
        $parents = array_filter(explode(',', trim($info['likecatid'],',')));
        $operator = $operator ? '+' : '-';
        $r = jtable('cms_category')->update(array($operator . '@articles'=>$value),$parents);
        return $r;
    }
    
    public function get_related_art ($artid,$limit=8){
        $likecatid = jtable('cms_article')->val(array('aid'=>$artid),'likecatid');
        $list = jtable('cms_article')->get(array(
            'like@likecatid'=>'%'.$likecatid.'%',
            '<>@aid'=>$artid,
            'check'=>1,
            'sql_order'=>'`dateline` desc',
            'sql_limit'=>$limit,
            ));
        return $list['list'];
    }
    
    public function get_hot_art ($artid,$limit = 8){
        $likecatid = jtable('cms_article')->val(array('aid'=>$artid),'likecatid');
        $list = jtable('cms_article')->get(array(
            'like@likecatid'=>'%'.$likecatid.'%',
            '<>@aid'=>$artid,
            'check'=>1,
            'sql_order'=>'`replys` desc',
            'sql_limit'=>$limit,
            ));
        return $list['list'];
    }
    
    public function get_cat_list($catid,$ischeck=2,$order='dateline',$limit=0){
        if($catid<1){
            return FALSE;
        }
        $likecatid = jtable('cms_category')->val(array('catid'=>$catid),'likecatid');
        $where = array('like@likecatid'=>'%'.$likecatid.'%');
        if($ischeck == 0){
            $where['ischeck'] = 0;
        }elseif($ischeck == 1){
            $where['ischeck'] = 1;
        }
        $where['sql_order'] = $order;
        if($limit>0){
            $where['sql_limit'] = $limit;
        }

        $list = jtable('cms_article')->get($where);
        return $list;
    }


    
    public function modify_order($catid,$order){
        $r = jtable('cms_category')->update(array('displayorder'=>$order),array('catid'=>$catid));
        return (bool) $r;
    }

    
    public function get_artlist_only($catid,$ischeck=1,$order='dateline DESC',$limit=8){
        if($catid<1){
            return FALSE;
        }
        $where = array('catid'=>(int)$catid);
        if($ischeck == 0){
            $where['ischeck'] = 0;
        }elseif($ischeck == 1){
            $where['ischeck'] = 1;
        }
        $where['sql_order'] = $order;
        if($limit>0){
            $where['sql_limit'] = $limit;
        }

        $list = jtable('cms_article')->get($where);
        return $list;
    }

}
