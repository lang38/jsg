<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename cms.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 917927231 6278 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config){
		$this->MasterObject($config);
		$this->Execute();
	}

	function Execute(){
		ob_start();
		switch ($this->Code){
			case 'channel':
				$this->Channel();
				break;
			case 'chk':
				$this->Chk();
				break;
			case 'article':
				$this->Article();
				break;
			default:
				$this->main();
				break;
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}

	function Chk(){
		$catid = jget('id');
		$ismanageman = jlogic('cms')->ismanageman($catid);
		if($ismanageman){
			$navtitle = $this->Title = $this->MetaKeywords = $this->MetaDescription = 'CMS文章审核';
			if($catid){
				$category = jlogic('cms')->Getonecategory($catid);
				if($category){
					$navtitle .= ' >> '.$category['catname'];
					$navhtml = jlogic('cms')->getnavbycatid($catid);
					if($category['upid']){
						$catids = $category['upid'];
					}
				}					
			}
			$pernum = 15;
			$count = jlogic('cms')->catidarticlenums($catid,1);
			$pagehtml = page($count,$pernum,'index.php?mod=cms&code=chk&id='.$catid,array('return'=>'array'));
			$limit_sql = $pagehtml['limit'];
			$articles = jlogic('cms')->getarticlebycatid($catid,$limit_sql,1);
			$cmscatlist = jlogic('cms')->Getulli(0,$catid);		
			$searchchar = '输入关键词';
			$navhtml .= ' >> 文章审核';
			include template('cms/channel');
		}else{
			$this->main();
		}
	}

	function Channel(){
		$search = jget('search');
		$pernum = 15;
		if('yes' == $search){
			$ismanageman = jlogic('cms')->ismanageman();
			$searchchar = jget('cmssearchchar');
			if(''==$searchchar || '输入关键词'==$searchchar){
				$this->Messager("请输入关键词搜索");
			}
			$catid = 0;
			$count = jlogic('cms')->searcharticlenums($searchchar);
			$pagehtml = page($count,$pernum,'index.php?mod=cms&code=channel&search=yes&cmssearchchar='.$searchchar,array('return'=>'array'));
			$limit_sql = $pagehtml['limit'];
			$articles = jlogic('cms')->getarticlebysearch($searchchar,$limit_sql);
			$cmscatlist = jlogic('cms')->Getulli(0);
			$navtitle = '文章搜索';
			$navhtml = '搜索['.$searchchar.']结果';
			$this->Title = '文章搜索 - CMS文章';
			$this->MetaKeywords = 'CMS,文章,分类,'.$searchchar;
			$this->MetaDescription = '微博文章,文章分类,'.$searchchar;
			include template('cms/channel');
		}else{
			$my = 'yes' == jget('my') ? 1 : 0;
			$myurl = $my ? '&my=yes' : '';
			$catid = jget('id');
			$templatefile = 'channel';
			if($catid){
				$category = jlogic('cms')->Getonecategory($catid);
				if($category){
					$catids = trim($category['upid'],',') ? trim($category['upid'],',').','.$catid : $catid;
				}
				if($category['template']){
					$templatefile = $category['template'];
				}
			}
			if($category){
				$catid = $category['catid'];
				$ismanageman = jlogic('cms')->ismanageman($catid);
				$navtitle = $category['catname'];
				if('channel' == $templatefile){
					$count = jlogic('cms')->catidarticlenums($catid,0,$my);
					$pagehtml = page($count,$pernum,'index.php?mod=cms&code=channel'.$myurl.'&id='.$catid,array('return'=>'array'));
					$limit_sql = $pagehtml['limit'];
					$articles = jlogic('cms')->getarticlebycatid($catid,$limit_sql,0,$my);
					$cmsupids = explode(',',trim($category['upid'],','));
					$tupid = $cmsupids[0] ? $cmsupids[0] : $catid;
					$cmscatlist = jlogic('cms')->Getulli(0,$catid,$tupid);
				}else{
					                    $this_cat_art_list = jlogic('cms')->get_artlist_only($catid);
                    if($this_cat_art_list['count']>0){
                        $info = jlogic('cms')->get_category_info($catid);
                        $articles[$catid]['catid'] = $catid;
                        $articles[$catid]['catname'] = $info['catname'];
                        $articles[$catid]['article'] = $this_cat_art_list['list'];
                    }
					if($articles){
						$articles = $articles + $this->_getchannellist($catid,$my);
					}else{
						$articles = $this->_getchannellist($catid,$my);
					}
					
				}
				$navhtml = jlogic('cms')->getnavbycatid($catid);
				$this->Title = $category['catname'].' - CMS文章';
				$this->MetaKeywords = $category['catname'].',CMS,文章,分类';
				$this->MetaDescription = $category['catname'].',微博文章,文章分类';
				$searchchar = '输入关键词';
				include template('cms/'.$templatefile);
			}else{
				$this->main();
			}
		}
	}

	function Article(){
		$aid = jget('id','int');
		$article = jlogic('cms')->getarticleviewbyid($aid);
		if($article){
			$hotarticles = jlogic('cms')->get_hot_art($aid);
			$byarticles = jlogic('cms')->get_related_art($aid);
			$this->Title = $article['title'].' - CMS文章浏览';
			$this->MetaKeywords = $article['title'].',CMS,文章';
			$this->MetaDescription = $article['title'].',CMS,微博文章,文章';
			include template('cms/article');
		}else{
			$this->Messager("文章不存在或正在审核中",jurl("index.php?mod=cms"));
		}
	}

	function main(){
		$my = 'yes' == jget('my') ? 1 : 0;
		$catid = 0;
		$ismanageman = jlogic('cms')->ismanageman();
		$articles = $this->_getchannellist(0,$my);
		$navtitle = 'CMS文章';
		$navhtml = jlogic('cms')->getnavbycatid(0);
		$this->Title = 'CMS文章首页';
		$this->MetaKeywords = 'CMS,文章首页';
		$this->MetaDescription = 'CMS,微博文章,文章首页';
		include template('cms/index');
	}

	function _getchannellist($catid=0,$my=0){
		$channels = jlogic('cms')->get_down_category($catid);
		$articles = array();
		if($channels){
			foreach($channels as $val){
				$articles[$val['catid']]['catid'] = $val['catid'];
				$articles[$val['catid']]['catname'] = $val['articles']>0 ? $val['catname'].'('.$val['articles'].')' : $val['catname'];
				$articles[$val['catid']]['article'] = jlogic('cms')->getarticlebycatid($val['catid'],' LIMIT 8 ',0,$my);
			}
		}
		return $articles;
	}
}