<?php
/**
 *
 * 微博图片操作类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: topic_image.logic.php 5539 2014-02-11 09:22:05Z chenxianfeng $
 */
if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class TopicImageLogic {

    public function __construct() {
        ;
    }
    
    
    public function del($tid, $id) {
    	$id = (int) $id;
    	if($id < 1) {
    		return jerror('要删除的图片ID不能为空', -1);
    	}
    	$info = jlogic('image')->get_info($id);
    	if(!$info) {
    		return jerror('请指定一个正确的图片ID，图片不存在或已经被删除了。', -2);
    	}
    	if(jdisallow($info['uid'])) {
    		return jerror('您无权对该图片进行操作', -3);
    	}
    	$tid = (int) $tid;
    	if($tid > 0) {
	    	$tinfo = jlogic('topic')->Get($tid);
	    	if(!$tinfo) {
	    		return jerror('请指定一个正确的微博ID，微博不存在或已经被删除了。', -5);
	    	}
	    	if(jdisallow($tinfo['uid'])) {
	    		return jerror('您无权对该微博进行操作', -6);
	    	}
			$_iids = explode(',', $tinfo['imageid']);
			foreach($_iids as $iid) {
				$iids[$iid] = $iid;
			}
			unset($iids[$id]);
			jlogic('image')->set_topic_imageid($tid, $iids);
    	} else {
    		if(!$info['tid']) {
    			jlogic('image')->delete($id);
    		}elseif($tid){
				return jerror('删除失败，该图不允许删除', -10);
			}
    	}
    } 

}