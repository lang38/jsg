<?php

/**
 * 相册及图片模块（目前只含读操作，不含写操作，后续有需求再补充）
 * 文件名： album.mod.php
 * 作  者：　狐狸 <foxis@qq.com>
 * @version $Id: album.mod.php  2013-09-29 08:22:22Z chenxianfeng $
 * 功能描述： api for JishiGou
 * 版权所有： Powered by JishiGou API 1.0.0 (a) 2005 - 2099 Cenwor Inc.
 * 公司网站： http://cenwor.com
 * 产品网站： http://jishigou.net
 */


if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
    
	function ModuleObject($config)
	{
		$this->MasterObject($config);
        $this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
			case 'topic':
                $this->topic();
                break;
			case 'album':
                $this->album();
                break;
			case 'image':
                $this->image();
                break;
			default :
				$this->Main();
				break;
		}
	}

	function Main()
	{
		api_output('album api is ok');
	}

		function album(){
		$uid = jget('uid','int');
		$count = jlogic('image')->albumnums(1,$uid);
		$albums = jlogic('image')->getalbum('',1,$uid);
		api_output(array('total'=>$count,'albums'=>array_merge($albums)));
	}

		function image(){
		$aid = jget('aid','int');
		$count = jlogic('image')->albumimgnums($aid,1);
		$images = jlogic('image')->getallalbumimg($aid,'',1);
		api_output(array('total'=>$count,'images'=>array_merge($images)));
	}

		function topic()
	{
		$id = jget('id','int');		$infos = jlogic('image')->get_uploadimg_byid($id);
		$sql_wheres = array("item"=>"`item` = 'topic_image'","item_id"=>"`item_id` = '".$id."'");
		$this->_topic_list('new',$sql_wheres,$order,array(),array('imageinfo'=>$infos[$id]));
	}
}
?>