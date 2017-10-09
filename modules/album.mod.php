<?php
/**
 * 相册模块
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: album.mod.php $
 */

if(!defined('IN_JISHIGOU')) {
	exit('invalid request');
}

class ModuleObject extends MasterObject {

	function ModuleObject($config) {
		$this->MasterObject($config);
		
		$this->Execute();
	}
	function Execute() {
		ob_start();
		switch ($this->Code) {
			case 'updateimg':
				$this->Updateimg();
				break;
			case 'updatealbum':
				$this->Updatealbum();
				break;
			case 'list':
				$this->Listalbum();
				break;
			case 'viewimg':
				$this->Viewimg();
				break;
			default:
				$this->Code = '';
				$this->Main();
		}
		$body=ob_get_clean();
		$this->ShowBody($body);
	}

	function Viewimg(){
		$imgid = jget('pid', 'int');
		$infos = jlogic('image')->get_uploadimg_byid($imgid);
		$imginfo = $infos[$imgid];
		if(!$imginfo){
			$this->Messager("不存在该图片",null);
		}
		if($imginfo['albumid'] > 0 && !jlogic('image')->checkalbumbyid($imginfo['albumid'])){
			$this->Messager("您没有权限浏览该图片",null);
		}
		$imginfo['photo'] = $imginfo['site_url'].'/'.str_replace('./','',$imginfo['photo']);
		$albumname = $imginfo['albumid']>0 ? jlogic('image')->get_albumname_byid($imginfo['albumid']) : '默认相册';
		$imgname = $imginfo['description'] ? cut_str($imginfo['description'],18) : $imginfo['name'];
		$imgwidth = $imginfo['width'] > 800 ? 800 : $imginfo['width'];
		$imgheight = $imginfo['width'] > 800 ? ceil(($imginfo['height']/$imginfo['width'])*800) : $imginfo['height'];
		$imgsize = $imginfo['filesize'] > 0 ? ($imginfo['filesize'] < 1024*100 ? round(($imginfo['filesize']/1024),1).'K' : round(($imginfo['filesize']/(1024*1024)),1).'M') : '未知';
		$imgtime = my_date_format($imginfo['dateline']);
		if($imginfo['uid']==MEMBER_ID){
			$myclass='curr';
			$allclass='';
			$navtitle = '<a href="'.jurl('index.php?mod=album').'">我的相册</a> >> '.'<a href="'.jurl('index.php?mod=album&aid='.$imginfo['albumid']).'">'.$albumname.'</a> >> '.$imgname;
		}else{
			$myclass='';
			$allclass='curr';
			$navtitle = '<a href="'.jurl('index.php?mod=album&code=list').'">全部相册</a> >> '.'<a href="'.jurl('index.php?mod=album&code=list&uid='.$imginfo['uid']).'">'.$imginfo['username'].'的相册</a> >> '.'<a href="'.jurl('index.php?mod=album&code=list&aid='.$imginfo['albumid']).'">'.$albumname.'</a> >> '.$imgname;
		}
		$imgfrom = $imginfo['tid']>0 ? '<a href="'.jurl('index.php?mod=topic&code='.$imginfo['tid']).'">微博</a>' : ($imginfo['tid']<0 ? '私信' : '相册');
		$this->item = 'topic_image';
		$this->item_id = $imgid;
		$albumid = $imginfo['albumid'];
		$h_key = 'album';
				$gets = array(
			'mod' => 'album',
			'code' => 'viewimg',
			'pid' => $imgid,
		);
		$page_url = 'index.php?'.url_implode($gets);
		$tids = jlogic('image')->get_topic_by_imageid($imgid);
		$options = array(
			'tid' => $tids,
			'perpage' => 5,				'page_url' => $page_url,
		);
		$topic_info = jlogic('topic_list')->get_data($options);
		$topic_list = array();
		if (!empty($topic_info)) {
			$topic_list = $topic_info['list'];
			$page_arr['html'] = $topic_info['page']['html'];
		}
		$this->Title = '查看相册图片 - '.$imginfo['name'];
		$albums = jlogic('image')->getalbum();
		include(template("album_img"));
	}

	function Listalbum(){
		$albumid = jget('aid', 'int');
		if($albumid > 0){
			if(!jlogic('image')->checkalbumbyid($albumid)){
				$this->Messager("您没有权限浏览该相册",null);
			}
			$albumname = jlogic('image')->getalbumname($albumid,1,1);
			$talbumname = jlogic('image')->getalbumname($albumid,1,0);
			$count = jlogic('image')->albumimgnums($albumid,1);
			$albuminfo = jlogic('image')->getalbumbyid('album',$albumid);
			if($count != $albuminfo['picnum']){
				jlogic('image')->update_album_picnum($albumid,$count);
			}
			$pernum = 20;
			$pagehtml = page($count,$pernum,'index.php?mod=album&code=list&aid='.$albumid,array('return'=>'array'));
			$limit_sql = $pagehtml['limit'];
			$albums = jlogic('image')->getallalbumimg($albumid,$limit_sql,1);
			foreach($albums as $key => $val){
				$albums[$key]['pic'] = $val['site_url'].str_replace('./','/',str_replace('_o.jpg','_s.jpg',$val['photo']));
				$albums[$key]['albumname'] = $val['description'] ? cut_str($val['description'],18) : '';
				$albums[$key]['title'] = $val['description'];
				$albums[$key]['url'] = jurl('index.php?mod=album&code=viewimg&pid='.$val['id']);
				$albums[$key]['rel'] = $val['photo'] ? $val['site_url'].'/'.str_replace('./','',$val['photo']) : '';
			}
			$navtitle = '<a href="'.jurl('index.php?mod=album&code=list').'">全部相册</a> >> '.$albumname;
			$this->Title = '微博相册 - '.$talbumname;
		}else{
			$uid = jget('uid', 'int');
			$pernum = 10;
			if($uid > 0){
				$count = jlogic('image')->albumnums(1,$uid);
				$pagehtml = page($count,$pernum,'index.php?mod=album&code=list&uid='.$uid,array('return'=>'array'));
			}else{
				$count = jlogic('image')->albumnums(1);
				$pagehtml = page($count,$pernum,'index.php?mod=album&code=list',array('return'=>'array'));
			}
			$limit_sql = $pagehtml['limit'];
			$albums = jlogic('image')->getalbum($limit_sql,1,$uid);
			$albumuser = '';
			$purviewtext = array(0=>'所有人可见',1=>'仅作者关注的人可见',2=>'仅作者的粉丝可见',3=>'仅作者自己可见');
			foreach($albums as $key => $val){
				$albums[$key]['pic'] = $val['pic'] ? str_replace('_o.jpg','_s.jpg',$val['pic']) :'images/noavatar.gif';
				$albums[$key]['albumname'] = cut_str($val['albumname'],18);
				$albums[$key]['title'] = $val['depict'] ? $val['depict'] : $val['albumname'];
				$albums[$key]['url'] = jurl('index.php?mod=album&code=list&aid='.$val['albumid']);
				$albums[$key]['id'] = $val['albumid'];
				$albums[$key]['purview'] = $purviewtext[$val['purview']];
				$albumuser = $val['nickname'];
			}
			if($uid > 0){
				$navtitle = '<a href="'.jurl('index.php?mod=album&code=list').'">全部相册</a> >> '.$albumuser.'的相册';
			}else{
				$navtitle = '全部相册 >> 所有';
			}
			$this->Title = '微博相册 - '.$albumuser.'的相册';
		}
		include(template("album_list"));
	}

	function Main() {
		$uid = MEMBER_ID;
		if($uid < 1) {
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}
		if(isset($_GET['aid'])){
			$type = 'image';
			$albumid = jget('aid', 'int');
			$albumname = jlogic('image')->getalbumname($albumid);
			$albumname = $albumname ? $albumname : '默认相册';
			$addtitle = ' >> '.$albumname;
			$count = jlogic('image')->albumimgnums($albumid);
			$albuminfo = jlogic('image')->getalbumbyid('album',$albumid);
			if($count != $albuminfo['picnum']){
				jlogic('image')->update_album_picnum($albumid,$count);
			}
			$pernum = 20;
			$pagehtml = page($count,$pernum,'index.php?mod=album&aid='.$albumid,array('return'=>'array'));
			$limit_sql = $pagehtml['limit'];
			$albums = jlogic('image')->getallalbumimg($albumid,$limit_sql);
			foreach($albums as $key => $val){
				$site_url = ($val['site_url'] ? $val['site_url'] . '/' : '');
				$albums[$key]['pic'] = $site_url.str_replace('./','',str_replace('_o.jpg','_s.jpg',$val['photo']));
				$albums[$key]['albumname'] = $val['description'] ? cut_str($val['description'],18) : '';
				$albums[$key]['title'] = $val['description'];
				$albums[$key]['url'] = jurl('index.php?mod=album&code=viewimg&pid='.$val['id']);
				$albums[$key]['rel'] = $val['photo'] ? $site_url.str_replace('./','',$val['photo']) : '';
			}
						$navtitle = '<a href="'.jurl('index.php?mod=album').'">我的相册</a>'.$addtitle;
		}else{
			$type = 'album';
			$count = jlogic('image')->albumnums();
			$pernum = 9;
			$pagehtml = page($count,$pernum,'index.php?mod=album',array('return'=>'array'));
			$limit_sql = $pagehtml['limit'];
			$albums = jlogic('image')->getalbum($limit_sql);
			$albums[0] = array('albumid'=>0,'albumname'=>'默认相册');
			$purviewtext = array(0=>'所有人可见',1=>'仅我关注的人可见',2=>'仅我的粉丝可见',3=>'仅我自己可见');
			foreach($albums as $key => $val){
				$albums[$key]['pic'] = $val['pic'] ? str_replace('_o.jpg','_s.jpg',$val['pic']) :'images/noavatar.gif';
				$albums[$key]['albumname'] = cut_str($val['albumname'],18);
				$albums[$key]['title'] = $val['depict'] ? $val['depict'] : $val['albumname'];
				$albums[$key]['url'] = jurl('index.php?mod=album&aid='.$val['albumid']);
				$albums[$key]['id'] = $val['albumid'];
				$albums[$key]['purview'] = $purviewtext[$val['purview']];
			}
			$navtitle = '我的相册 >> 所有';
		}
		$this->Title = '相册列表'.$addtitle;
		include(template("album"));
	}

	function Updateimg(){
		$data = $_POST['title'];
		$albumid = $_POST['uploadalbum'];
		$urlid = $_POST['urlalbum'];
		$ids = $_POST['ids'] ? $_POST['ids'] : array();
		$handlekey = jget('hkey');
		$reload = jget('reload','int');
		$return = false;
		if($data){
			foreach($data as $key => $val){
				$val = $val == '图片简介' ? '' : $val;
				if($ids){
					$aid = in_array($key,$ids) ? $albumid : 0;
				}else{
					$aid = $urlid ? $urlid : 0;
				}
				if($val || $aid){
					jlogic('image')->updateimg($key,$val,$aid);
					$return = true;
				}
			}
		}
		if($return){
			echo "<script type='text/javascript'>window.parent.show_message('图片成功更新');</script>";
			if($reload){
				echo "<script type='text/javascript'>window.parent.location.reload();</script>";			}
		}else{
			echo "<script type='text/javascript'>window.parent.show_message('没有做任何改动！',1,'提示','msgBox','msg_alert');</script>";
		}
		if($handlekey){
			echo "<script type='text/javascript'>window.parent.closeDialog('".$handlekey."');</script>";
		}
	}

	function Updatealbum(){
		$type = jget('type');
		$hkey = jget('hkey');
		$id = jget('id', 'int');
		$name = trim(jget('namea', 'txt'));
		$oldname = jget('oldnamea');
		$description = trim(jget('description', 'txt'));
		$olddescription = jget('olddescription');
		$purview = trim(jget('purview'));
		$oldpurview = jget('oldpurview');
		if($name == $oldname && $description == $olddescription && $purview == $oldpurview){
			echo "<script type='text/javascript'>window.parent.show_message('没有做任何改动！',1,'提示','msgBox','msg_alert');</script>";
		}else{
			jlogic('image')->updatealbum($id,$type,$name,$description,$oldname,$purview);
			echo "<script type='text/javascript'>window.parent.show_message('修改成功！');</script>";
		}
		echo "<script type='text/javascript'>window.parent.closeDialog('".$hkey."');</script>";
	}
}
?>