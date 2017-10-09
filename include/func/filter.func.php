<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename filter.func.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014-09-17 16:01:50 903291679 1970545639 4107 $
 */





if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

/**
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：过滤
 * @version $Id: filter.func.php 5701 2014-05-28 07:17:44Z wuliyong $
 */

function remove_xss($val) {
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); 		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); 	}
	$ra1 = jconf::get("xss",'tag');
	$ra2 = jconf::get("xss",'attribute');
	$ra = array_merge($ra1, $ra2);

	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
			$val = preg_replace($pattern, $replacement, $val);
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}


function __filter(&$string, $verify=1, $replace=1,$shield=0)
{
	static $filter = null;

	$rets = array();

		$string=trim($string);
	if($string) {
		if(false!==strpos($string,'<')) {
			$string=strip_selected_tags($string,"<script><iframe><style><link><meta><embed>");
			if($string) {
				$string=remove_xss($string);
			}
		}
		if(empty($string)) {
			$rets['error'] = 1;
			$rets['type'] = 'xss';
						$rets['msg'] = "含有禁止提交的代码，请修改后重新提交！";

			return $rets;
		}

		if($filter===null) {
			$filter = (array) jconf::get('filter');
		}

		if(!$filter['enable']) {
			return false;
		}

				if($replace && $filter['replace_list'])
		{
			foreach($filter['replace_list'] as $search=>$replace)
			{
				$strpos = jstrpos($string, $search);

				if($strpos!==false)
				{
					$string = str_replace($search, $replace, $string);
				}
			}
		}

				if(!empty($filter['keywords']))
		{
			if($filter['keyword_list']===null)
			{
				$filter['keyword_list'] =  explode("|",str_replace(array("\r\n","\r","\n","\t","\\|"),"|",trim($filter['keywords'])));
			}

			foreach ($filter['keyword_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['error'] = 1;
					$rets['type'] = 'filter';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含有禁止的内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ")."，请修改后重新提交！";

					return $rets;
				}
			}
		}

				if($verify && $filter['verify_list'])
		{
			foreach($filter['verify_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['verify'] = 1;
					$rets['type'] = 'verify';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含审核内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ")."需管理员审核后才会对外显示，<a href='index.php?mod=".MEMBER_NAME."&type=my_verify'>点此查看</a>";

					return $rets;
				}
			}
		}

		if($shield && $shield!=0 && $filter['shield_list']){
			foreach($filter['shield_list'] as $keyword)
			{
				$strpos = jstrpos($string, $keyword);

				if($strpos!==false)
				{
					$rets['shield'] = 1;
					$rets['type'] = 'shield';
					$rets['keyword'] = $keyword;
					$rets['msg'] = "含有屏蔽的内容 ".($filter['keyword_disable'] ? "" : " {$keyword} ");

					return $rets;
				}
			}
		}
	}

	return false;
}


?>