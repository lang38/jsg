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
 * @Date 2014-04-25 14:36:59 272463697 324988518 4750 $
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
			case 'listcategory':
				$this->Listcategory();
				break;
			case 'publish':
				$this->publish();
				break;
			case 'addcms':
				$this->addcms();
				break;
			case 'addreply':
				$this->addreply();
				break;
			case 'catlistajax':
				$this->catlistajax();
				break;
			case 'checkcms':
				$this->checkcms();
				break;
			default:
				$this->main();
				break;
		}
		response_text(ob_get_clean());
	}

	function main(){
		response_text("正在建设中……");
	}

		function Listcategory()
	{
		$catid = jget('catid');
		$j = jget('j');
		$k = (int)$j+1;
		if($catid){
			$category = jlogic('cms')->get_down_category($catid);
		}
		$html = '';
		if($category){
			$html .= '<select name="categoryids[]" id="categoryids_'.$j.'" onchange="listnextcategory(this,\''.$k.'\');"><option value="">请选择...</option>';
			foreach($category as $val){
				if($val['purview']){
					$html .= '<option value="'.$val['catid'].'">'.$val['catname'].'</option>';
				}else{
					$html .= '<option value="'.$val['catid'].'" disabled = "true">'.$val['catname'].'</option>';
				}
			}
			$html .= '</select>';
		}
		echo $html;
	}

		function publish(){
		if(MEMBER_ID < 1){
			response_text("<p style='margin:50px 20px;padding:30px;color:#666;border:1px dashed #999;text-align:center;font-weight:bold;font-size:14px;'>错误：请您先登录后再进行该操作！</p>");
			exit;
		}
		$aid = jget('aid');$fromcatid = $catid = jget('catid');
		if($aid){
			$cmsinfo = jlogic('cms')->getarticlebyid($aid);
			$uploadimages = $cmsinfo['images'];
			if(!$cmsinfo['edit']){
				response_text("<p style='margin:50px 20px;padding:30px;color:#666;border:1px dashed #999;text-align:center;font-weight:bold;font-size:14px;'>错误：您没有相关操作权限！</p>");
				exit;
			}
			$catid = $cmsinfo['catid'];
		}
		$categoryselect = jlogic('cms')->get_category_html($catid);
		$h_key = '';
		$albums = jlogic('image')->getalbum();
		include template('cms/publish');
	}

		function addcms(){
		if (MEMBER_ID < 1) {
			response_text("请先登录或者注册一个帐号");
		}
		$aid = trim($this->Post['aid']);
		$title = trim($this->Post['title']);
		$catid = trim($this->Post['catid']);
		$content = trim($this->Post['content']);
        if(!$content){					}
		if (!$title){response_text("请输入标题");}
		if (!$catid){response_text("请选择分类");}
		if (!$content){response_text("请输入内容");}
		$imageid = trim($this->Post['imageid']);
		$attachid = trim($this->Post['attachid']);
		$data = array(
			'title' => strip_tags($title),
			'catid' => $catid,
			'content' => $content,
			'imageid' => $imageid,
			'attachid' => $attachid,
		);
		if($aid > 0){
			$return = jlogic('cms')->modify($aid,$data);
		}else{
			$return = jlogic('cms')->create($data);
		}
		if($return >= 0){
			if($aid > 0){
				response_text("修改成功");
			}else{
				$str = $return > 0 ? '发布成功' : '发布成功，请等待管理员审核';
				response_text($return."|||".$str."|||".date('Y-m-d H:i:s',time()));
			}
		}else{
			response_text("操作失败，您没有相关操作权限");
		}
	}

		function catlistajax(){
		$catid = jget('id');
		$urlid = jget('urlid');
		$html = jlogic('cms')->Getulli($catid,$urlid);
		echo $html;
	}

		function checkcms(){
		$aid = jget('aid');
		$article = jlogic('cms')->getarticlebyid($aid);
		if(MEMBER_ID > 0 && $article && in_array(MEMBER_ID,explode(',',trim($article['likemanageid'],',')))  && !$article['check']){
			$return = jlogic('cms')->check($aid);
		}else{
			$return = '操作错误或没有权限';
		}
		echo $return;
	}

		function addreply(){
		if (MEMBER_ID < 1) {
			response_text("请先登录或者注册一个帐号");
		}
		$aid = trim($this->Post['aid']);
		$content = trim($this->Post['content']);
		$totopic = trim($this->Post['totopic']);
		if ($aid<1){response_text("您的操作错误");}
		if (!$content){response_text("请输入内容");}
		$data = array(
			'aid' => $aid,
			'content' => $content,
			'totopic' => $totopic,
		);
		$return = jlogic('cms')->addreply($data);
		if($return > 0){
			response_text("评论成功|||".MEMBER_NICKNAME.'|||'.date('Y-m-d H:i:s',time()));
		}else{
			response_text("操作失败，您没有相关操作权限");
		}
	}
}