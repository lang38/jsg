<?php
/**
 * 文件名：qqwb_env.func.php
 * @version $Id: qqwb_env.func.php 3812 2013-06-05 02:24:18Z yupengfei $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 腾讯微博接口函数
 */
if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}



function qqwb_env()
{
	$msgs = array();

	
	$files = array(ROOT_PATH . 'include/ext/qqwb/qqoauth.php',ROOT_PATH . 'include/ext/qqwb/oauth.php',ROOT_PATH . 'modules/qqwb.mod.php',);
	foreach ($files as $f)
	{
		if (!is_file($f))
		{
			$msgs[] = "文件<b>{$f}</b>不存在";
		}
	}

	
	$funcs = array('version_compare',array('fsockopen', 'pfsockopen'),'preg_replace',array('iconv','mb_convert_encoding'),array("hash_hmac","mhash"));
	foreach ($funcs as $func)
	{
		if (!is_array($func))
		{
			if (!function_exists($func))
			{
				$msgs[] = "函数<b>{$func}</b>不可用";
			}
		}
		else
		{
			$t = false;
			foreach ($func as $f)
			{
				if(function_exists($f))
				{
					$t = true;
					break;
				}
			}

			if (!$t)
			{
				$msgs[] = "函数<b>".implode(" , ",$func)."</b>都不可用";
			}
		}
	}

	return $msgs;
}

?>