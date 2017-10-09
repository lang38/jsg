<?php
/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename xwb_plugins_publish.class.php $
 *
 * @Author 狐狸<foxis@qq.com> $
 *
 * @version $Id: xwb_plugins_publish.class.php 3699 2013-05-27 07:26:39Z wuliyong $
 */

class xwb_plugins_publish{
	function xwb_plugins_publish(){}

	/**
	 * 同步主题 For JishiGou
	 * @param $tid int 记事狗微博 topic id
	 * @param $totid int 记事狗微博 topic id
	 * @param $message string 微博内容
	 * @param $imageid mixed 微博关联的图片ID 或者 完整的图片地址
	 * @return array
	 */
	function topic( $tid, $totid = 0, $message = '', $imageid = '')
	{
        $tid = max(0,(int) $tid);
        $totid = max(0, (int) $totid);
		if($tid < 1)
		{
			return false;
		}

		if ($this->_isSyn($tid))
		{
			return false;
		}

		$db = XWB_plugin::getDB();

		$baseurl = XWB_plugin::siteUrl();

		if( empty($message) )
		{
			//可以通过$tid, $pid进行查询
			//但由于此方法主要用于hook，而hook已经实施了拦截，因此可以无需通过数据查询即可进行获取
			return false;
		}
		else
		{
			// 转码前的内容保存
			$postinfo = array();
			$postinfo['message'] = $message;
		}

		// 转码
		$message = $this->_convert($postinfo['message']);

		// 过滤UBB与表情
		$message = $this->_filter($message);

		// 去除HTML标记
		$message = strip_tags($message);


		$link = ' ' .$baseurl . 'index.php?mod=topic&code=' . $tid;

        if(function_exists('get_full_url'))
        {
            $link = ' ' . get_full_url($baseurl,'index.php?mod=topic&code=' . $tid);
        }

		$length = 140 - ceil(strlen( urlencode($link) ) * 0.5) ;   //2个字母为1个字
		$message = $this->_substr($message, $length);

		//将最后附带的url给删除。
		$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);

		$message .= $link;


		$wb = XWB_plugin::getWB();

		/**
		 * 全新发布微博
		 */
		if ($totid < 1 && XWB_plugin::pCfg('is_synctopic_toweibo'))
		{
			// 取出第一张图片
			$first_img_url = '';
			if( XWB_plugin::pCfg('is_upload_image') && $imageid) {
				if (is_string($imageid) && false!==strpos($imageid, ':/'.'/')) {
					$first_img_url = $imageid;
				} else {
					$imageid = (int) $imageid;
					if($imageid > 0) {
						if($GLOBALS['_J']['config']['ftp_on']) {
							$first_img_url = topic_image($imageid, 'original', 0);
						} else {
							$tpic = topic_image($imageid, 'original', 1);
							if(is_file(XWB_S_ROOT . $tpic)) {
								$first_img_url = topic_image($imageid, 'original', 0);
							}
						}
					}
				}
			}


			$ret = array();

			// 同步到微博
			if ($first_img_url)
			{
				$ret = $wb->upload($message, $first_img_url, null, null, false);

				if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] )
				{
					$ret = $wb->update($message, false);
				}
			}
			else
			{
				$ret = $wb->update($message, false);
			}

			//同步微博后的ID
			$mid = ($ret['idstr'] ? $ret['idstr'] : ($ret['mid'] ? $ret['mid'] : $ret['id']));
			if ($mid)
			{
				//json_decode可能存在解析超过int最大数的错误（#47644），需要注意
                $this->_setSynId($tid, $mid);

			}
		}

		/**
		 * 回复某个微博
		 */
		elseif ($totid>0 && XWB_plugin::pCfg('is_syncreply_toweibo'))
		{
			$mid = $this->_isSyn($totid);
			if (!$mid)
			{
				return false;
			}

			$rs = $wb->comment($mid, $message,null, false);
			$_mid = ($rs['idstr'] ? $rs['idstr'] : ($rs['mid'] ? $rs['mid'] : $rs['id']));
            if($_mid)
            {
                $this->_setSynId($tid, $_mid);
            }
		}

	}


    /**
	 * 获取转发主题信息 For DiscuzX1.5
	 * @param $tid int 论坛thread id
	 * @return array
	 */
    function forShare($tid)
    {
        /* 主题URL */
        $baseurl = XWB_plugin::siteUrl();
        $topic_url = $baseurl . 'index.php?mod=topic&code=' . $tid;
        if(function_exists('get_full_url'))
        {
            $topic_url = get_full_url($baseurl,'index.php?mod=topic&code=' . $tid);
        }
        $url = ' ' . $topic_url;

        /* 获取微博信息 */
        $db = XWB_plugin::getDB();
		$topic = $db->fetch_first("SELECT `tid`,`content`,`imageid` FROM " . XWB_S_TBPRE . "topic WHERE tid='{$tid}'");
        if (empty($topic)) return FALSE;

        /* 转码 */
		$message = $this->_convert(trim($topic['content']));

		/* 过滤UBB与表情 */
		$message = $this->_filter($message);

		$message = strip_tags($message);

        /* 将最后附带的url给删除 */
		$message = preg_replace("|\s*http:/"."/[a-z0-9-\.\?\=&_@/%#]*\$|sim", "", $message);

        /* 合并标题和链接 */
        $message = $message . $url;

        // 取出所有图片
		$img_urls = array();
		if($topic['imageid'] && XWB_plugin::pCfg('is_upload_image'))
        {
			$image_file = "/images/topic/" . jsg_face_path($topic['imageid']) . $topic['imageid'] . "_o.jpg";
			if(is_file(XWB_S_ROOT . $image_file))
			{
				$img_urls[] = $baseurl . $image_file;
			}
		}

        return array(
            'url' => $topic_url,
            'message' => $message,
            'pics' => array_map('trim', $img_urls)
        );
    }

    /**
	 * 转发主题 For DiscuzX1.5
	 * @param $message 发布内容
     * @param $pic 发布图片
	 * @return bool
	 */
    function sendShare($message, $pic = '')
    {
        if (empty($message)) return false;

        // 转码及过滤UBB与表情
		$message = $this->_filter($message);

        // 同步到微博
        $wb = XWB_plugin::getWB();
		$ret = array();

		if ( ! empty($pic))
        {
			$ret = $wb->upload($message, $pic, null, null, false);
            if ( isset($ret['error_code']) && 400 == (int)$ret['error_code'] )
            {
				$ret = $wb->update($message, false);
			}
		}
        else
        {
			$ret = $wb->update($message, false);
		}

        return $ret;
    }


	/**
	 * 转换为微博可以使用的编码
	 */
	function _convert($msg) {
		return XWB_plugin::convertEncoding($msg, XWB_S_CHARSET, 'UTF-8');
	}


	/**
	 * 过滤发布内容
	 */
	function _filter($content) {
		//将[attachimg]和[attach]的UBB标签连同内容给全部删除
		$content = preg_replace('!\[(attachimg|attach)\]([^\[]+)\[/(attachimg|attach)\]!', '', $content);

        /* 过滤[img]标签，在其后面添加空格，防止粘连 2010-10-12 */
        $content = preg_replace('|\[img(?:=[^\]]*)?](.*?)\[/img\]|', '\\1 ', $content);

		// 过滤UBB
		$re ="#\[([a-z]+)(?:=[^\]]*)?\](.*?)\[/\\1\]#sim";
		while(preg_match($re, $content)) {
			$content = preg_replace($re, '\2', $content);
		}

		//多个空格合为一个空格；前后空格去掉
		$content = preg_replace("#\s+#", ' ', $content);
		$content = trim($content);

		return $content;
	}



	/**
	 * 标题和内容去重，然后合并
	 *
	 */
	function _mergeMessage( $subject, $message ){
		$result = '';

		if( $subject != '' ){
			//当处理完成的帖子内容，全部去掉前后空格 包含于 帖子标题 ，则仅取帖子标题作为微博内容。并且返回。
			if( false !== strpos( $subject , $message ) ){
				$result = $subject;
				return $result;
			}

			//当处理完成的帖子内容，开头与帖子标题重复时，去掉帖子标题，仅取帖子内容作为微博内容。并且返回。
			if( 0 === strpos( $message, $subject ) ){
				$result = $message;
				return $result;
			}
		}

		//以上皆不符合，就直接进行整合。
		$result = $subject . ' | ' . $message;
		return $result;
	}


	/**
	 * 对utf-8编码截取
	 * @param $str string 要截取的源内容
	 * @param $length int 要截取的长度
	 */
	function _substr($str, $length) {
		//防止后面的操作导致内存溢出
		if( strlen($str) > $length + 600 ){
			$str = substr($str, 0, $length + 600);
		}

		$p = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
		preg_match_all($p,$str,$o);
		$size = sizeof($o[0]);
		$count = 0;
		for ($i=0; $i<$size; $i++) {
			if (strlen($o[0][$i]) > 1) {
				$count += 1;
			} else {
				$count += 0.5;
			}

			if ($count  > $length) {
				$i-=1;
				break;
			}

		}
		return implode('', array_slice($o[0],0, $i));
	}


	/**
	 * 主题是否已经同步
	 * @param $tid int thread id
	 * @return false|int
	 */
	function _isSyn($tid) {
		$db = XWB_plugin::getDB();
		$sql = 'SELECT * FROM ' . XWB_S_TBPRE . 'xwb_bind_topic WHERE `tid`=' . "'{$tid}'";
		$rs = $db->fetch_first($sql);
		if (!$rs)
		{
			return false;
		}
		return $rs['mid'];
	}

	/**
	 * 设置帖子同步标志
	 * @param $tid int thread id
	 * @param $mid int 微博id
	 */
	function _setSynId($tid, $mid)
    {
        $tid = (is_numeric($tid) ? $tid : 0);
        $mid = (is_numeric($mid) ? $mid : 0);

        if($tid > 0 && $mid > 0)
        {
            $db = XWB_plugin::getDB();
    		$sql = 'INSERT INTO ' . XWB_S_TBPRE . 'xwb_bind_topic(`tid`,`mid`) VALUES("' .$tid. '", "' . mysql_real_escape_string($mid) . '")';
    		$db->query($sql);
    		if ($db->affected_rows())
    		{
    			return true;
    		}
        }

		return false;
	}



}
