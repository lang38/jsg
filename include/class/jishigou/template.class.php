<?php
/**
 *
 * 模板编译核心类
 *
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @copyright Copyright (C) 2005 - 2099 Cenwor Inc.
 * @license http://www.cenwor.com
 * @link http://www.jishigou.net
 * @author 狐狸<foxis@qq.com>
 * @version $Id: template.class.php 5462 2014-01-18 01:12:59Z wuliyong $
 */

if(!defined('IN_JISHIGOU'))
{
	exit('invalid request');
}

function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function stripvtags($expr, $statement = '') {
		$expr = str_replace("\\\"", "\"", preg_replace("/\<\?php echo (\\\$.+?); \?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

class jishigou_template
{
	var $root_path = './';
	var $TemplateRootPath="./templates/";	var $TemplatePath="";					var $TemplateDir="default";				var $CompiledFolder="./data/cache/templates/";	var $CompiledPath="";					var $TemplateFile="";					var $CompiledFile="";					var $TemplateString="";				var $TemplateExtension='.html'; 	var $CompiledExtension='.php'; 	var $LinkFileType='css|js|jpeg|jpg|png|bmp|gif|swf'; 	var $TemplateHeadAdd = '';
	var $TemplateDeveloper = 0;
	var $_init_set = 0;
	var $io = null;
	var $replacecode = array('search' => array(), 'replace' => array());

	
	function jishigou_template()
	{
		global $_J;

		$this->root_path = (defined('TEMPLATE_ROOT_PATH') ? TEMPLATE_ROOT_PATH : ROOT_PATH);
		#if NEDU
		if (isset($_J['config']['__root_path__']))
		{
			$this->root_path = $_J['config']['__root_path__'];
		}
		#endif

		$this->TemplateRootPath = $_J['config']['template_root_path'] ? $_J['config']['template_root_path'] : "./templates/";
		$this->TemplateDir=$_J['config']['template_path'];
		$this->TemplatePath=$this->root_path . $this->TemplateRootPath . $this->TemplateDir . '/';
		$this->CompiledPath=$this->root_path . $this->CompiledFolder . '/' . $this->TemplateDir . '/';
		#if NEDU
		if (isset($_J['config']['__compiled_abs_path__']))
		{
			$this->CompiledPath = $_J['config']['__compiled_abs_path__'].'/'.$this->TemplateDir.'/';
		}
		#endif
		$this->TemplateHeadAdd = '<?php /'.'* '.date('Y-m-d').' in jishigou invalid request template *'.'/ if(!defined("IN_JISHIGOU")) exit("invalid request"); hookscriptoutput(); ?>';
		$this->TemplateDeveloper = ($_J['config']['templatedeveloper'] ? 1 : 0);
	}
	
	
	public function exists($template_filename) {
		return ($template_filename && false != $this->Template($template_filename, true));
	}

	
	function Template($filename, $only_check_exists = false) {
		if(false !== strpos($filename, ':')) { 			$is_plugin_tpl = true;
			list($plugin_id, $tpl_filename) = explode(':', $filename);
			$this->TemplateFile = $this->root_path . "plugin/{$plugin_id}/template/{$tpl_filename}" . $this->TemplateExtension;
			$this->CompiledFile = $this->CompiledPath . "plugin/{$plugin_id}" . '$' . "{$tpl_filename}.plugin" . $this->CompiledExtension;
		} else { 			$this->TemplateFile = $this->TemplatePath.$filename.$this->TemplateExtension;
			if(defined('APP_ROOT')) {
				$app_tpl = APP_ROOT . 'view/default/' . $filename . $this->TemplateExtension;
				if(is_file($app_tpl)) {
					$is_app_tpl = true;
					$this->TemplateFile = $app_tpl;
				}
				$this->CompiledFile = $this->CompiledPath . APP_PATH . '$' . $filename . '.app.view' . $this->CompiledExtension;
			} else {				
				$this->CompiledFile=$this->CompiledPath.$filename.$this->CompiledExtension;
			}
		}
		$to = $this->CompiledFile;
		if($this->check_compiled()) {
			if(!is_file($this->TemplateFile)) {
				$fe = false;
				if(!$is_plugin_tpl && !$is_app_tpl) {
					$tpl_path = (strpos($this->TemplateDir, '/') ? dirname($this->TemplatePath) . '/' : dirname($this->TemplatePath).'/default/');
					$this->TemplateFile=$tpl_path.$filename.$this->TemplateExtension;
					if(is_file($this->TemplateFile)) {
						$fe = true;
					}
				}
				if($only_check_exists) {
					return $fe;
				}
				if(!$fe) {
					$msg = "模板 ".$this->TemplateFile." 读取失败，请检查文件是否存在，如果该文件存在请给予读取权限。";
					
					jlog("template_load_error", $msg);
				}
			}
			if($this->Load()) {
				$this->Compile($is_plugin_tpl);
				$this->Write($to);
			} else {
				return false;
			}
		}
		return $to;
	}

		function check_compiled() {
		$ret = false;

		clearstatcache();
		if(!is_file($this->CompiledFile)) {
			$ret = true;
		} else {
						if(($this->TemplateDeveloper)) {
				if(@filemtime($this->TemplateFile) > @filemtime($this->CompiledFile)) {
					$ret = true;
				} else {
					$cf_mtime = 0;
					$cfs = $this->io()->ReadDir(dirname($this->CompiledFile));
					if(is_array($cfs) and count($cfs)) {
						foreach($cfs as $cf) {
							if($this->CompiledExtension == $this->io()->FileExt($cf, 0)) {
								$mt = @filemtime($cf);
								if($mt > $cf_mtime) {
									$cf_mtime = $mt;
								}
							}
						}
					}
					if($cf_mtime > 0) {
						$tf_mtime = 0;
						$tfs = $this->io()->ReadDir(dirname($this->TemplateFile));
						if(is_array($tfs) and count($tfs)) {
							foreach($tfs as $tf) {
								if($this->TemplateExtension == $this->io()->FileExt($tf, 0)) {
									$mt = @filemtime($tf);
									if($mt > $tf_mtime) {
										$tf_mtime = $mt;

										if($tf_mtime > $cf_mtime) {
											$this->io()->ClearDir(dirname($this->CompiledFile));
											$ret = true;
											break;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $ret;
	}

	
	function Load()
	{
		$this->TemplateString = $this->io()->ReadFile($this->TemplateFile);

		Return true;
	}

	
	function Compile($is_plugin_tpl)
	{
		global $jishigou_rewrite, $plugin;

		$this->_init_set();

		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(-\>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)?(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

		$nest = 5;

		$template=$this->TemplateString;

				if(defined('FORMHASH') && FORMHASH && !$is_plugin_tpl)
		{
			$template = preg_replace("/(\<form.*? method=[\"\']?post[\"\']?)([^\>]*\>)/i","\\1 \\2\n<input type=\"hidden\" name=\"FORMHASH\" value='{FORMHASH}'/>",$template);
		}

				$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

		$template = str_replace("{LF}", "<?php echo \"\\n\"; ?>", $template);

		$template = preg_replace("/\{$var_regexp\}/s", "<?php echo \\1; ?>", $template);

		$template = preg_replace("/$var_regexp/es", "addquote('<?php echo \\1; ?>')", $template);
		$template = preg_replace("/\<\?php echo \<\?php echo $var_regexp; \?\>; \?\>/es", "addquote('<?php echo \\1; ?>')", $template);
		$template = preg_replace("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/ie", "\$this->datetags('\\1')", $template);
		$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<?php \\1 ?>\n','')", $template);

		$template = preg_replace("/[\n\r\t]*\{conf\s+(.+?)\}[\n\r\t]*/ies", "addquote('<?php echo \$GLOBALS[_J][config][\\1]; ?>')", $template);

		$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php echo \\1; ?>','')", $template);
		$template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<?php } elseif(\\1) { ?>','')", $template);
		$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "\n<?php } else { ?>", $template);
		$template = preg_replace("/\{hook\/(\w+?)(\s+(.+?))?\}/ie", "\$this->hooktags('\\1', '\\3')", $template);

		for($i = 0; $i < $nest; $i++) {
			$template = preg_replace("/[\n\r\t]*\{(?:sub)?templates?\s+[\"\']?([\w\d\-\_\.\:\/]+)[\"\']?\}/ies",'$this->loadsubtemplate("\\1")',$template);
			#if NEDU
			$template = preg_replace("/[\n\r\t]*\{(?:sub)?templatesNEDU?\s+[\"\']?([\w\d\-\_\.\:\/]+)[\"\']?\}/ies",'$this->loadsubtemplateNEDU("\\1")',$template);
			#endif
			$template = preg_replace("/[\n\r\t]*\{(?:sub)?j?widgets?\s+[\"\']?([\w\d\-\_\.\:\/]+)[\"\']?\}/ies", "stripvtags('<?php jwidget(\'\\1\'); ?>')", $template);
			$template = preg_replace("/[\n\r\t]*\{(?:sub)?j?widgets?\s+[\"\']?([\w\d\-\_\.\:\/]+)[\"\']?\s+[\"\']?([\w\d\-\_\.\:\/]+)[\"\']?\}/ies", "stripvtags('<?php jwidget(\'\\1\', \'\\2\'); ?>')", $template);
			$template = preg_replace("/[\n\r\t]*\{loop\s+(\<\?[^\?]+?\?\>)\s+(\<\?[^\?]+?\?\>)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\n\\3\n<?php } } ?>\n')", $template);
			$template = preg_replace("/[\n\r\t]*\{loop\s+(\<\?[^\?]+?\?\>)\s+(\<\?[^\?]+?\?\>)\s+(\<\?[^\?]+?\?\>)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\n\\4\n<?php } } ?>\n')", $template);
			$template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<?php } ?>\n')", $template);
			$template = preg_replace("/[\n\r\t]*\{while\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/while\}[\n\r\t]*/ies", "stripvtags('\n<?php while(\\1) { ?>','\n\\2\n<?php } ?>\n')", $template);
		}
		$template = preg_replace("/\{$const_regexp\}/s", "<?php echo \\1; ?>", $template);

		#if NEDU
		$template = preg_replace("/\{\~(.+?)\}/s", "<?=\\1?>", $template);
		#endif

		if(!empty($this->replacecode)){
			$template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
		}

		$template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);
		$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
		$template = preg_replace("/<\!\-\-([^\{\}\?]*?)\-\->/s"," ",$template);
		$template = preg_replace("/\>[\r\n\t\s]+?\</s","> <",$template);
		$template = preg_replace("/[\r\n][\r\n\t\s]+/s","\n",$template);

		if(!$is_plugin_tpl){$template = $this->TemplateHeadAdd . $template;}
		$template = trim($template);
		$this->TemplateString=$template;

		if(!empty($this->LinkFileType))
		{
			$this->ModifyLinks();
		}
		if($jishigou_rewrite && (true===IN_JISHIGOU_INDEX || true===IN_JISHIGOU_AJAX))
		{
			$this->TemplateString=$jishigou_rewrite->output($this->TemplateString,true);
		}
	}

		function hooktags($hookid, $key = '') {
		global $_J;
		$i = count($this->replacecode['search']);
		$this->replacecode['search'][$i] = $search = "<!--HOOK_TAG_$i-->";
		$key = $key !== '' ? '['.stripvtags($key,'').']' : '';
		$dev = '';
		if(PLUGINDEVELOPER == 2) {
			$dev = "echo '<hook>[".($key ? 'array' : 'string')." ".$hookid."]</hook>';";
		}
		$this->replacecode['replace'][$i] = "<?php ".$dev."if(!empty(\$GLOBALS['_J']['pluginhooks']['".$hookid."']".$key.")) echo \$GLOBALS['_J']['pluginhooks']['".$hookid."']".$key.";?>";
		return $search;
	}

		function stripblock($var, $s) {
		$addstr = '<?php if(!defined("IN_JISHIGOU")) exit("invalid request"); ?>';
		$s = str_replace('\\"', '"', $s);
		$s = preg_replace("/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s);
		preg_match_all("/<\?=(.+?)\?>/e", $s, $constary);
		$constadd = '';
		$constary[1] = array_unique($constary[1]);
		foreach($constary[1] as $const) {
			$constadd .= '$__'.$const.' = '.$const.';';
		}
		$s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
		$s = str_replace("\\\"", "\"", preg_replace("/\<\?php echo (\\\$.+?); \?\>/s", "{\\1}", $s));
		$s = preg_replace("/(\<form.*? method=[\"\']?post[\"\']?)([^\>]*\>)/i","\\1 \\2\n<input type=\"hidden\" name=\"FORMHASH\" value='".FORMHASH."'/>",$s);
		$s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
		$s = str_replace('<?php', "\nEOF;\n", $s);
		return $addstr."<?php\n$constadd\$$var = <<<EOF\n".$s."\nEOF;\n?>";
	}

	
	function write($to='', $try_times = 0)
	{
		$save_dir=dirname($to);

		clearstatcache();
		if(!is_dir($save_dir))
		{
			$this->MakeDir($save_dir);
		}

		$length = $this->io()->WriteFile($to, $this->TemplateString);
		if(false === $length && !is_file($to))
		{
			if($try_times < 2)
			{
				$try_times += 1;

				$length = $this->write($to, $try_times);
			}

			die('模板无法写入,请检查 '.$save_dir.' 目录是否有可写权限');
		}

		Return $length;
	}

	
	function MakeDir($dir_name, $mode = 0777) {
		return $this->io()->MakeDir($dir_name, $mode);
	}
	
	function ModifyLinksbak()
	{
		preg_match_all("/src=[\"\'\s]?(.*?)[\"\'\s]|url[\(\"\']{1,3}(.*?)[\s\"\'\)]|background=[\"\']?(.*?)[\"\'\s]|href=[\"\'\s]?(.*?)[\"\'](.*?)\>/si", $this->TemplateString, $match);

		$old = @array_values(array_merge(@array_unique($match[1]), $match[2], @array_unique($match[3]), $match[4]));
		$old = array_unique($old);
		$old=preg_grep("~.*?\.(".$this->LinkFileType.")$~i",$old);
		foreach($old as $link)
		{
			if(trim($link) != "" and !strpos($link, ':/'.'/'))
			{
				if(strpos($link,'../')===0)
				{
					$this->TemplateString=str_replace($link, dirname($this->TemplatePath) . '/' . ltrim($link, './'), $this->TemplateString);
				}
				else
				{
					$this->TemplateString = str_replace($link, rtrim($this->TemplatePath,'\/') . '/' . ltrim($link, './'), $this->TemplateString);
				}
			}
		}
		return $this->TemplateString;
	}
		function ModifyLinks()
	{
		preg_match_all("/src=[\"\'\s]?(.*?)[\"\'\s]|url[\(\"\']{1,3}(.*?)[\s\"\'\)]|background=[\"\']?(.*?)[\"\'\s]|href=[\"\'\s]?(.*?)[\"\'](.*?)\>/si", $this->TemplateString, $match);

		$old = @array_values(array_merge(@array_unique($match[1]), $match[2], @array_unique($match[3]), $match[4]));
		$old = array_unique($old);
		$old = preg_grep('~.*?\.(' . $this->LinkFileType . ')$~i', $old);
		$to_dir_default = 'templates/default/';
		$to_dir = 'templates/' . $this->TemplateDir . '/';
		foreach($old as $link)
		{
			if(trim($link) != "" and false===strpos($link, ':/'.'/'))
			{
				$private_file = str_replace($to_dir_default, $to_dir, $link);
				clearstatcache();
				if (!@is_file($this->root_path . $private_file) && false===strpos($private_file, $to_dir))
				{
					$private_file = $to_dir . $private_file;
				}
				clearstatcache();
				if('default'!=$this->TemplateDir && !@is_file($this->root_path . $private_file))
				{
					$private_file = str_replace($to_dir, $to_dir_default, $private_file);
				}
				clearstatcache();
				if(!@is_file($this->root_path . $private_file))
				{
					continue ;
				}

				if(in_array($this->io()->FileExt($link), array('css', 'js'))) {
					$private_file .= "?" . urlencode(SYS_BUILD);
				}
				$this->TemplateString = str_replace($link, $private_file, $this->TemplateString);

								$this->TemplateString = str_replace(array($to_dir_default . $to_dir_default, $to_dir . $to_dir), array($to_dir_default, $to_dir), $this->TemplateString);
			}
		}
		return $this->TemplateString;
	}

	
	function RepairBracket($var)
	{
		Return preg_replace("~\[([a-z0-9_\x7f-\xff]*?[a-z_\x7f-\xff]+[a-z0-9_\x7f-\xff]*?)\]~i","[\"\\1\"]",$var);
	}

	function loadsubtemplate($file)
	{
		$tpl_file = $this->Template($file);

		if(($content = @implode('',file($tpl_file))))
		{
			$content = str_replace($this->TemplateHeadAdd,'',$content);

			return $content;
					}
		else
		{
			return '<!-- '.$file.' -->';

		}
	}

	#if NEDU
	function loadsubtemplateNEDU($file)
	{
		$tpl_file = ndriver('template')->file_src($file);

		if(($content = @implode('',file($tpl_file))))
		{
			$content = str_replace($this->TemplateHeadAdd,'',$content);

			return $content;
					}
		else
		{
			return '<!-- '.$file.' -->';

		}
	}
	#endif

	function datetags($parameter) {
		return "<?php echo my_date_format($parameter); ?>";
	}

	function io() {
		if(!$this->io) {
			$this->io = jio();
		}
		return $this->io;
	}

	function _init_set() {
		if(!$this->_init_set) {
			@ini_set('pcre.backtrack_limit', 1000000);

			$this->_init_set = 1;
		}
	}
}

?>