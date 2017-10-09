<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename install.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 1067188441 30626 $
 */




define('IN_JISHIGOU', TRUE);
define('IN_JISHIGOU_INSTALL', true);

error_reporting(E_ERROR);
@set_time_limit(900);
ini_set('max_execution_time', 900);
ini_set("memory_limit","512M");
ini_set("magic_quotes_runtime", 0);

$config_default_file = './setting/settings.default.php';
include $config_default_file;
$charset = $config['charset'];

@include './setting/settings.php';
$config_install_lock_time = $config['install_lock_time'];
$charset = $config['charset'] ? $config['charset'] : $charset;

@header('Content-Type: text/html; charset=' . $charset);

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
}

$installfile = basename(__FILE__);
$sqlfile = './install/jishigou.sql';
$sqldatafile = './install/jishigou_data.sql';
$lockfile = './data/install.lock';
$attachdir = './attachments';
$attachurl = 'attachments';
$quit = FALSE;
@unlink('./install/.htaccess');


include './install/install.lang.php';
include './install/global.func.php';
include './install/db_mysql.class.php';
include './setting/constants.php';


$inslang = defined('INSTALL_LANG') ? INSTALL_LANG : '';
$version = SYS_VERSION . ' ' . SYS_PUBLISHED . ' ' . SYS_BUILD . ' ' . $lang[$inslang];



if(!defined('INSTALL_LANG') || !function_exists('instmsg') || !is_readable($sqlfile)) {
	exit("Please upload all files to install JishiGou system<br />&#x5b89;&#x88c5; 【记事狗】 &#x60a8;&#x5fc5;&#x987b;&#x4e0a;&#x4f20;&#x6240;&#x6709;&#x6587;&#x4ef6;&#xff0c;&#x5426;&#x5219;&#x65e0;&#x6cd5;&#x7ee7;&#x7eed;");
} elseif(!isset($config['db_host']) || !isset($config['auth_key'])) {
	instmsg('config_nonexistence');
} elseif(is_file($lockfile) || file_exists('./install/install.lock') || ($config_install_lock_time > 0 && ($config_install_lock_time + 1800) < time())) {
	instmsg('lock_exists');
} elseif(!class_exists('dbstuff')) {
	instmsg('database_nonexistence');
}

if(function_exists('instheader')) {
	instheader();
}

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];
if(in_array($action, array('check', 'config'))) {
	if(is_writeable('./setting')) {
		$writeable['config'] = result(1, 0);
		$write_error = 0;
	} else {
		$writeable['config'] = result(0, 0);
		$write_error = 1;
	}
}

if(!$action) {

	$JISHIGOU_license = str_replace('  ', '&nbsp; ', $lang['license']);

	?>
<tr>
	<td><b><?php echo $lang['current_process']; ?> </b><font
		color="#0000EE"><?php echo $lang['show_license']; ?> </font></td>
</tr>
<tr>
	<td><hr noshade align="center" width="100%" size="1"></td>
</tr>
<tr>
	<td><br />
		<table width="90%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr>
				<td class="altbg1">
					<table width="99%" cellspacing="1" border="0" align="center">
						<tr>
							<td><?php echo $JISHIGOU_license; ?></td>
						</tr>
					</table></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td align="center"><br />
		<form method="post" action="<?php echo $installfile; ?>">
			<input type="hidden" name="action" value="check"> <input
				type="submit" name="submit"
				value="<?php echo $lang['agreement_yes']; ?>" style="height: 25">&nbsp;
			<input type="button" name="exit"
				value="<?php echo $lang['agreement_no']; ?>" style="height: 25"
				onclick="javascript: window.close();">
		</form></td>
</tr>
	<?php

} elseif($action == 'check') {

	?>
<tr>
	<td><b><?php echo $lang['current_process']; ?> </b><font
		color="#0000EE"> <?php echo $lang['check_config']; ?> </font></td>
</tr>
<tr>
	<td><hr noshade align="center" width="100%" size="1"></td>
</tr>
<tr>
	<td><br /> <?php

	$msg = '';
	$curr_os = PHP_OS;

		$curr_path = __FILE__;
	if (preg_match('~(?:[\x7f-\xff][\x7f-\xff])+~',$curr_path)) {
		$msg .= "<li>{$lang['path_unsupport']}</li>";
		$quit = true;
	}

	if(!function_exists('mysql_connect')) {
		$curr_mysql = $lang['unsupport'];
		$msg .= "<li>$lang[mysql_unsupport]</li>";
		$quit = TRUE;
	} else {
				$curr_mysql = mysql_get_client_info();
		if(!$curr_mysql)
		{
			$curr_mysql = $lang['support'];
		}
		else
		{
			if($curr_mysql < '4.1.9')
			{
				$msg .= "<li>您的 MYSQL 版本小于 4.2, 无法使用 JishiGou微博系统。</li>";
				$quit = TRUE;
			}
		}
	}

	$curr_php_version = PHP_VERSION;
	if($curr_php_version < '4.0.6') {
		$msg .= "<li>$lang[php_version_406]</li>";
		$quit = TRUE;
	}

	if(ini_get(file_uploads)) {
		$max_size = ini_get(upload_max_filesize);
		$curr_upload_status = $lang['attach_enabled'].$max_size;
	} else {
		$curr_upload_status = $lang['attach_disabled'];
		$msg .= "<li>$lang[attach_disabled_info]</li>";
	}

	$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

	$checkdirarray = array(
				'cache' => './data/cache',
				'images' => './images',
				'install' => './install',
				'log' => './data/log',
				'setting' => './setting',
	);

	foreach($checkdirarray as $key => $dir) {
		if(dir_writeable($dir)) {
			$writeable[$key] = result(1, 0);
		} else {
			$writeable[$key] = result(0, 0);
			$langkey = $key.'_unwriteable';
			$msg .= "<li>$lang[$langkey]</li>";
			$quit = TRUE;
		}
	}
			$setting_dir='./setting';
	$fp=opendir($setting_dir);
	while ($filename=readdir($fp))
	{
		if(preg_match("/\.php$/i",$filename)>0)
		{
			$_file=$setting_dir.'/'.$filename;
			@chmod($_file,0777);
			if(touch($_file)==false)
			{
				$writeable['setting_config'].=$_file.result(0, 0);
				$quit=true;
			}
		}
	}
	if(empty($writeable['setting_config']))$writeable['setting_config']=result(1, 0);

	if($quit) {
		$submitbutton = '<input type="button" name="submit" value=" '.$lang['recheck_config'].' " style="height: 25" onclick="window.location=\'?action=check\'">';
	} else {
		$submitbutton = '<input type="submit" name="submit" value=" '.$lang['new_step'].' " style="height: 25">';
		$msg = $lang['preparation'];
	}

	?>
<tr>
	<td align="center">
		<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr bgcolor="#3A4273">
				<td style="color: #FFFFFF; padding-left: 10px" width="32%"><?php echo $lang['tips_message']; ?>
				</td>
			</tr>
			<tr>
				<td class="message"><?php echo $msg; ?></td>
			</tr>
		</table> <br />
		<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr class="header">
				<td></td>
				<td><?php echo $lang['env_required']; ?></td>
				<td><?php echo $lang['env_best']; ?></td>
				<td><?php echo $lang['env_current']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1"><?php echo $lang['env_os']; ?></td>
				<td class="altbg2"><?php echo $lang['unlimited']; ?></td>
				<td class="altbg1">UNIX/Linux/FreeBSD</td>
				<td class="altbg2"><?php echo $curr_os; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1"><?php echo $lang['env_php']; ?></td>
				<td class="altbg2">5.x</td>
				<td class="altbg1">5.3以上</td>
				<td class="altbg2"><?php echo $curr_php_version; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1"><?php echo $lang['env_attach']; ?></td>
				<td class="altbg2"3><?php echo $lang['unlimited']; ?></td>
				<td class="altbg1"><?php echo $lang['enabled']; ?></td>
				<td class="altbg2"><?php echo $curr_upload_status; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1"><?php echo $lang['env_mysql']; ?></td>
				<td class="altbg2">5.x</td>
				<td class="altbg1">5.2以上</td>
				<td class="altbg2"><?php echo $curr_mysql; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1"><?php echo $lang['env_diskspace']; ?></td>
				<td class="altbg2">100M+</td>
				<td class="altbg1">1000M+</td>
				<td class="altbg2"><?php echo $curr_disk_space; ?></td>
			</tr>
		</table> <br />
		<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr class="header">
				<td width="33%"><?php echo $lang['check_catalog_file_name']; ?></td>
				<td width="33%"><?php echo $lang['check_need_status']; ?></td>
				<td width="33%"><?php echo $lang['check_currently_status']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./setting</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['config']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./setting/目录下所有文件</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['setting_config']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./data/cache</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['cache']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./images</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['images']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./data/log</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['log']; ?></td>
			</tr>
			<tr class="option">
				<td class="altbg1">./install</td>
				<td class="altbg2"><?php echo $lang['writeable']; ?></td>
				<td class="altbg1"><?php echo $writeable['install']; ?></td>
			</tr>
		</table>

</tr>
<tr>
	<td align="center"><br />
		<form method="post" action="<?php echo $installfile; ?>">
			<input type="hidden" name="action" value="config"> <input
				type="button" name="submit"
				value=" <?php echo $lang['old_step']; ?> " style="height: 25"
				onclick="window.location='<?php echo $installfile; ?>'">&nbsp;
				<?php echo $submitbutton; ?>
		</form></td>
</tr>
				<?php

} elseif($action == 'config') {

	?>
<tr>
	<td><b><?php echo $lang['current_process']; ?> </b><font
		color="#0000EE"> <?php echo $lang['edit_config']; ?> </font></td>
</tr>
<tr>
	<td><hr noshade align="center" width="100%" size="1"></td>
</tr>
<tr>
	<td><br /> <?php

	$inputreadonly = $write_error ? 'readonly' : '';
	$msg = '<li>'.$lang['config_comment'].'</li>';

	if($_POST['saveconfig']) {
		$msg = '';
		if (!$_POST['db_host'] || !$_POST['db_user'] || !$_POST['db_name'] || !$_POST['site_admin_email']) {
			$msg .="<li>数据库配置和邮箱不能为空</li>";
			$quit = true;
		}

		if(!preg_match("~^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+([a-z]{2,4})|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$~i",$_POST['site_admin_email'])) {
			$msg .= "<li>请填写正确的邮箱地址</li>";
			$quit = true;
		}

		$config['template_path'] = setconfig(array_rand(array('default'=>1,)));
		$config['auth_key'] = setconfig(random(64)); 		$config['safe_key'] = setconfig(random(64));
		$config['cookie_prefix'] = setconfig('JishiGou_' . random(6) . '_'); 		$_host = getenv('HTTP_HOST') ? getenv('HTTP_HOST') : $_SERVER['HTTP_HOST'];
		$cookie_domain = '';
		if('www.'==substr($_host, 0, 4)) {
			$cookie_domain = substr($_host, 4);
		}
		if($cookie_domain && false !== ($csp = strpos($cookie_domain, ':'))) {
			$cookie_domain = substr($cookie_domain, 0, $csp);
		}
		$config['cookie_domain'] = setconfig($cookie_domain);
		$config['db_host'] = setconfig($_POST['db_host']);
		$config['db_user'] = setconfig($_POST['db_user']);
		$config['db_pass'] = setconfig($_POST['db_pass']);
		$config['db_name'] = setconfig($_POST['db_name']);
		$config['site_admin_email'] = setconfig($_POST['site_admin_email']);
		$config['db_table_prefix'] = setconfig($_POST['db_table_prefix'] ? $_POST['db_table_prefix'] : 'jishigou_' . random(6) . '_');
		$config['install_lock_time'] = time();
		if(empty($config['db_name'])) {
			$msg .= '<li>dbname_invalid '.$lang['dbname_invalid'].'</li>';
			$quit = TRUE;
		} else {
			if(!mysql_connect($config['db_host'], $config['db_user'], $config['db_pass'])) {
				$errormsg = 'database_errno_'.mysql_errno();
				$msg .= '<li>' . $errormsg . ': ' . mysql_error() . ' ' .$lang[$errormsg].'</li>';
				$quit = TRUE;
			} else {
				if(mysql_get_server_info() > '4.1') {
					mysql_query("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` DEFAULT CHARACTER SET $dbcharset");
				} else {
										$errormsg = '【'.mysql_get_server_info().'】服务器端的MYSQL数据库版本过低，请升级到4.2以上，推荐使用5.x版本。';
					$msg .= "<li>$errormsg </li>";
					$quit = TRUE;
				}
				if(mysql_errno()) {
					$errormsg = 'database_errno_'.mysql_errno();
					$msg .= "<li>{$errormsg}: ".mysql_error().' '.$lang[$errormsg].'</li>';
					$quit = TRUE;
				}

				mysql_close();
			}
		}

		if(strstr($config['db_table_prefix'], '.')) {
			$msg .= '<li>tablepre_invalid '.$lang['tablepre_invalid'].'</li>';
			$quit = TRUE;
		}

						@unlink(".htaccess");
		$_rewrite=array (
		  'mode' => '',
		  'abs_path' => '/',
		  'arg_separator' => '/',
		  'var_separator' => '-',
		  'prepend_var_list' =>
		array (
		0 => 'mod',
		1 => 'code',
		),
		  'var_replace_list' =>
		array (
			'mod' =>
		array (
		),
		),
		  'value_replace_list' =>
		array (
			'mod' =>
		array (
				'topic' => array_rand(array('miniblog'=>1,'myblog'=>1,'blog'=>1,'topics'=>1,'weibo'=>1,)),
				'tag' => array_rand(array('keywords'=>1,'channels'=>1,'class'=>1,'tags'=>1,)),
				'profile' => array_rand(array('profiles'=>1,'personals'=>1,)),
				'member' => array_rand(array('users'=>1,'members'=>1,)),
				'plugin' => array_rand(array('plugins'=>1, 'extends'=>1, 'expands'=>1, 'applications'=>1, 'packages'=>1, )),
		),
		),
		  'gateway' => '',
		);
		saveconfig($_rewrite,'./setting/rewrite.php','$config["rewrite"]');

		if(!$quit) {
			if(!$write_error) {
				saveconfig($config);
								@unlink($config_default_file);

				include('./setting/link.php');
				if(!isset($config['link']['biniu'])) {
					$biniu_link_list = array(
					array (
							'name' => _getaarrayrandval(array('比牛网','微站系统','微营销系统','微店系统','比牛网，让业务牛逼起来','微信建站系统','微信营销系统',)),
							'url' => 'http:/'.'/www.biniu.com',
					),
					array (
							'name' => _getaarrayrandval(array('团购源码下载','天天团购系统','团购系统','免费团购系统','团购程序','团购网站源码','开源团购系统',)),
							'url' => 'http:/'.'/www.tttuangou.net',
					),
					array (
							'name' => _getaarrayrandval(array('免费微博系统','开源微博系统','开源微博程序','微博程序','微博系统','微博网站源码','微博源码下载','微博源码',)),
							'url' => 'http:/'.'/www.jishigou.net',
					),
					array (
							'name' => _getaarrayrandval(array('旺企宝','企业2.0系统','企业微博系统','企业社交平台','企业协作平台','企业微博程序',)),
							'url' => 'http:/'.'/wangqibao.com',
					),
					array (
							'name' => _getaarrayrandval(array('开源微博系统','php微博系统','免费微博程序','开源微博程序','微博源码下载','轻博系统','轻博程序','免费开源微博系统','记事狗微博系统',)),
										'url' => 'http:/'.'/www.jishigou.net',
					),

					);
					$config['link']['biniu'] = _getaarrayrandval($biniu_link_list);
					$config['link']['biniu1'] = _getaarrayrandval($biniu_link_list);
					if($config['link']['biniu']['url'] == $config['link']['biniu1']['url']) {
						unset($config['link']['biniu1']);
					}
					$config['link']['biniu']['order'] = $config['link']['biniu1']['order'] = 100;


					saveconfig($config['link'],'./setting/link.php','$config["link"]');
				}
			}
			redirect("$installfile?action=admin");
		}
	}

		$config['db_host'] = 'localhost';
	$config['db_user'] = 'root';
	$config['db_pass'] = '';
	$config['db_name'] = 'jishigou';
	unset($config['site_admin_email'], $config['site_domain'], $config['site_url'], $config['wap_url'], $config['mobile_url']);
	$config['db_table_prefix'] = 'jishigou_';
	?>
<tr>
	<td align="center">
		<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr bgcolor="#3A4273">
				<td style="color: #FFFFFF; padding-left: 10px" width="32%"><?php echo $lang['tips_message']; ?>
				</td>
			</tr>
			<tr>
				<td class="message"><?php echo $msg; ?></td>
			</tr>
		</table> <br />
		<form method="post" action="<?php echo $installfile; ?>">
			<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
				align="center">
				<tr class="header">
					<td width="20%"><?php echo $lang['variable']; ?></td>
					<td width="30%"><?php echo $lang['value']; ?></td>
					<td width="50%"><?php echo $lang['comment']; ?></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['dbhost']; ?></td>
					<td class="altbg2"><input type="text" name="db_host"
						value="<?php echo $config['db_host']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<?php echo $lang['dbhost_comment']; ?></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['dbuser']; ?></td>
					<td class="altbg2"><input type="text" name="db_user"
						value="<?php echo $config['db_user']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<?php echo $lang['dbuser_comment']; ?></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['dbpw']; ?></td>
					<td class="altbg2"><input type="password" name="db_pass"
						value="<?php echo $config['db_pass']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<?php echo $lang['dbpw_comment']; ?></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['dbname']; ?></td>
					<td class="altbg2"><input type="text" name="db_name"
						value="<?php echo $config['db_name']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<?php echo $lang['dbname_comment']; ?></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<span class="redfont"><?php echo $lang['email']; ?>
					</span></td>
					<td class="altbg2"><input type="text" name="site_admin_email"
						value="<?php echo $config['site_admin_email']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<span class="redfont"><?php echo $lang['email_comment']; ?>
					</span></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['tablepre']; ?></td>
					<td class="altbg2"><input type="text" name="db_table_prefix"
						value="<?php echo $config['db_table_prefix']; ?>"
						<?php echo $inputreadonly; ?> size="30"></td>
					<td class="altbg1">&nbsp;<?php echo $lang['tablepre_comment']; ?></td>
				</tr>
			</table>
			<br /> <input type="hidden" name="action" value="config"> <input
				type="hidden" name="saveconfig" value="1"> <input type="button"
				name="submit" value=" <?php echo $lang['old_step']; ?> "
				style="height: 25" onclick="window.location='?action=check'">&nbsp;
			<input type="submit" name="submit"
				value=" <?php echo $lang['new_step']; ?> " style="height: 25">
		</form></td>
</tr>
						<?php

} elseif($action == 'admin') {

	?>
<tr>
	<td><b><?php echo $lang['current_process']; ?> </b><font
		color="#0000EE"> <?php echo $lang['check_env']; ?> </font></td>
</tr>
<tr>
	<td><hr noshade align="center" width="100%" size="1"></td>
</tr>
<tr>
	<td><br /> <?php

	$msg = '<li>'.$lang['add_admin'].'</li>';
	if(!mysql_connect($config['db_host'], $config['db_user'], $config['db_pass'])) {
		$errormsg = 'database_errno_'.mysql_errno();
		$msg .= "<li>{$errormsg}: ".mysql_error().' '.$lang[$errormsg].'</li>';
		$quit = TRUE;
	} else {
		$curr_mysql_version = mysql_get_server_info();
		if($curr_mysql_version < '3.23') {
			$msg .= '<li>mysql_version_323 '.$lang['mysql_version_323'].'</li>';
			$quit = TRUE;
		}

		$sqlarray = array(
				'createtable' => 'CREATE TABLE `jishigou_test` (`test` TINYINT (3) UNSIGNED)',
				'insert' => 'INSERT INTO `jishigou_test` (`test`) VALUES (1)',
				'select' => 'SELECT * FROM `jishigou_test`',
				'update' => 'UPDATE `jishigou_test` SET `test`=\'2\' WHERE `test`=\'1\'',
				'delete' => 'DELETE FROM `jishigou_test` WHERE `test`=\'2\'',
				'droptable' => 'DROP TABLE `jishigou_test`'
				);

				mysql_select_db($config['db_name']);
				foreach($sqlarray as $key => $sql) {
					mysql_query($sql);
					if(mysql_errno()) {
						$errnolang = 'dbpriv_'.$key;
						$msg .= '<li>dbpriv_'.$key.' '.mysql_errno().': '.mysql_error().' '.$lang[$errnolang].'</li>';
						$quit = TRUE;
					}
				}

				$result = (mysql_query("SELECT COUNT(*) FROM `{$config['db_table_prefix']}topic`") &&
				mysql_query("SELECT COUNT(*) FROM `{$config['db_table_prefix']}topic_reply`"));
				if($result) {
					$msg .= '<li><font color="#FF0000">'.$lang['db_not_null'].'</font></li>';
					$alert = " onSubmit=\"return confirm('$lang[db_drop_table_confirm]');\"";
				}
	}

	if($_POST['submit']) {

		$username = $_POST['username'];
		$email = $_POST['email'];
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];

				$config['site_domain']=$_SERVER['HTTP_HOST'];
		$config['site_name']=$_POST['site_name'];
		$config['site_notice']=$_POST['site_notice'];
		$config['site_url']=rtrim(htmlspecialchars('http'.(443==$_SERVER['SERVER_PORT'] ? 's' : '').':/'.'/'.$_SERVER['HTTP_HOST'].preg_replace("/\/+/",'/',str_replace("\\",'/',dirname($_SERVER['PHP_SELF']))."/")),'/');
		$config['wap_url'] = $config['site_url'] . '/wap';
		$config['mobile_url'] = $config['site_url'] . '/mobile';
		$config['jishigou_founder'] = '1';
		$config['seccode_enable'] = (@fsockopen('api.yinxiangma.com',80,$errno,$errstr,1) ? 1 : 0);
				$config['cache_db_to_memory'] = 1;
		$config['cache_file_to_memory'] = 1;
		$config['memory'] = array (
		  	'redis' => array(
		  		'enable' => ((extension_loaded('redis') && ($obj = new Redis()) && $obj->connect('127.0.0.1', '6379')) ? 1 : 0),
		  		'server' => '127.0.0.1',
		  		'port' => '6379',
		  		'pconnect' => 1,
		  		'serializer' => 1,
		  	),
		    'memcache' =>
		    array (
		      'enable' => ((extension_loaded('memcache') && ($obj = new Memcache()) && $obj->connect('127.0.0.1', '11211')) ? 1 : 0),
		      'connect' =>
		      array (
		        0 =>
		        array (
		          'server' => '127.0.0.1',
		          'port' => '11211',
		        ),
		      ),
		    ),
		   	'apc' => array('enable' => ((function_exists('apc_cache_info') && apc_cache_info()) ? 1 : 0)),
		    'xcache' => array('enable' => (function_exists('xcache_get') ? 1 : 0)),
		    'eaccelerator' => array('enable' => (function_exists('eaccelerator_get') ? 1 : 0)),
		    'wincache' => array('enable' => ((function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo()) ? 1 : 0)),
		);
		$config['memory_enable'] = (($config['memory']['redis']['enable'] ||
			$config['memory']['redis']['enable'] ||
			$config['memory']['memcache']['enable'] ||
			$config['memory']['apc']['enable'] ||
			$config['memory']['xcache']['enable'] ||
			$config['memory']['eaccelerator']['enable'] ||
			$config['memory']['wincache']['enable']) ? 1 : 0);
		$config['gzip'] = (function_exists('ob_gzhandler') ? 1 : 0);
		saveconfig($config);

		if($username && $email && $password1 && $password2) {
			if($password1 != $password2) {
				$msg .= '<li><font color="#FF0000">'.$lang['admin_password_invalid'].'</font></li>';
				$quit = TRUE;
			} elseif(strlen($username) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^游客|^Guest/is", $username)) {
				$msg = $lang['admin_username_invalid'];
				$quit = TRUE;
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != htmlspecialchars($email)) {
				$msg = $lang['admin_email_invalid'];
				$quit = TRUE;
			}
		} else {
			$msg .= '<li><font color="#FF0000">'.$lang['admin_invalid'].'</font></li>';
			$quit = TRUE;
		}

		if(!$quit){
			install_request(array(),$install_request_error);
			;
			redirect("$installfile?action=install&username=".rawurlencode($username)."&email=".rawurlencode($email)."&password=".md5($password1));
		}
	}

	?>
<tr>
	<td align="center">
		<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
			align="center">
			<tr bgcolor="#3A4273">
				<td style="color: #FFFFFF; padding-left: 10px" width="32%"><?php echo $lang['tips_message']; ?>
				</td>
			</tr>
			<tr>
				<td class="message"><?php echo $msg; ?></td>
			</tr>
		</table> <br />
	</td>
</tr>
<tr>
	<td align="center">
		<form method="post" action="<?php echo $installfile; ?>"
		<?php echo $alert; ?>>
			<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
				align="center">
				<tr bgcolor="#3A4273">
					<td style="color: #FFFFFF; padding-left: 10px" colspan="2"><?php echo $lang['add_admin']; ?>
					</td>
				</tr>
				<tr>
					<td class="altbg1" width="20%">&nbsp;<?php echo $lang['username']; ?>
					</td>
					<td class="altbg2" width="80%">&nbsp;<input type="text"
						name="username" value="admin" size="30">（注意：创始人有最大的管理权限）</td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['admin_email']; ?></td>
					<td class="altbg2">&nbsp;<input type="text" name="email"
						value="<?php echo $config['site_admin_email']; ?>" size="30"></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['password']; ?></td>
					<td class="altbg2">&nbsp;<input type="password" name="password1"
						size="30"></td>
				</tr>
				<tr>
					<td class="altbg1">&nbsp;<?php echo $lang['repeat_password']; ?></td>
					<td class="altbg2">&nbsp;<input type="password" name="password2"
						size="30"></td>
				</tr>
			</table>
			<br />

			<table width="80%" cellspacing="1" bgcolor="#000000" border="0"
				align="center">
				<tr bgcolor="#3A4273">
					<td style="color: #FFFFFF; padding-left: 10px" colspan="2"><?php echo $lang['setting']; ?>
					</td>
				</tr>
				<tr>
					<td class="altbg1" width="20%">&nbsp;<?php echo $lang['site_name']; ?>
					</td>
					<td class="altbg2" width="80%">&nbsp;<input type="text"
						name="site_name" value="<?php echo $config['site_name']; ?>"
						size="30"></td>
				</tr>
			</table>
			<br> <input type="hidden" name="action" value="admin"> <input
				type="button" name="submit"
				value=" <?php echo $lang['old_step']; ?> " style="height: 25"
				onclick="window.location='?action=config'">&nbsp; <input
				type="submit" name="submit"
				value=" <?php echo $lang['new_step']; ?> " style="height: 25">
		</form></td>
</tr>
		<?php

} elseif($action == 'install') {
	if(($config_install_lock_time + 1800) < time()) {
		instmsg('请重新发起安装请求……');
	}

	$salt = random(6);
	$username = htmlspecialchars($_GET['username']);
	$email = htmlspecialchars($_GET['email']);
	$password = htmlspecialchars($_GET['password']);
	if(!$username || !$email || !$password) {
		instmsg('用户昵称、密码、邮箱地址不能为空……');
	}
	$password = md5($password . $salt);

	$db = new dbstuff;
	$db->connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $pconnect);
	if(!$db) {
		instmsg('数据库连接失败，请检查……');
	}
	$db->select_db($config['db_name']);

	$timestamp=time();

	$min = rand(1,29);
	$max = $min + rand(1,29);
	$timestamp_next = $timestamp + $min + $max + 3600 * rand(1,24);
	$invitecode = substr(md5(random(16)),0,16);

	$extrasql = <<<EOT
replace into `jishigou_members` (`uid`,`username`,`nickname`,`password`,`email`,`role_id`,`role_type`,`invitecode`, `regdate`, `lastactivity`, `regip`, `lastip`, `salt`) values (1,'1','$username','$password','$email',2,'admin','{$invitecode}', '{$timestamp}', '{$timestamp}', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['REMOTE_ADDR']}', '{$salt}');
replace into `jishigou_memberfields` (`uid`) values('1');
EOT;

	?>
<tr>
	<td><b><?php echo $lang['current_process']; ?> </b><font
		color="#0000EE"> <?php echo $lang['start_install']; ?> </font></td>
</tr>
<tr>
	<td><hr noshade align="center" width="100%" size="1"></td>
</tr>
<tr>
	<td align="center"><br /> <script type="text/javascript">
	function showmessage(message) {
		document.getElementById('notice').value += message + "\r\n";
		document.getElementById('notice').scrollTop = 100000000;
	}
</script> <textarea name="notice" style="width: 80%; height: 400px"
			readonly id="notice"></textarea> <br /> <br /> <input type="button"
		name="submit" value=" <?php echo $lang['install_in_processed']; ?> "
		disabled style="height: 25" onclick="window.location='index.php'"
		id="laststep"><br /> <br /> <br />
	</td>
</tr>
	<?php

	$sqls = file_get_contents($sqlfile);
	if(!$sqls) {
		instmsg($sqlfile . ' 读取失败，请检查……');
	}
		$nedu_SQL = './nedu/data/install/struct.sql';
	if (is_file($nedu_SQL))
	{
		$sqls .= "\n\n".file_get_contents($nedu_SQL);
	}
		runquery($sqls);

	runquery($extrasql);

	$sqls = file_get_contents($sqldatafile);
	if(!$sqls) {
		instmsg($sqldatafile . ' 读取失败，请检查……');
	}
	runquery($sqls);


	$timestamp = time();

	touch($lockfile);

	touch('./install/install.lock');

	dir_clear('./data/cache');

	
	@unlink('./upgrade.php');
	@unlink('./public.php');
	@unlink('./server.php');
	@unlink('./include/func/server.func.php');
	@unlink('./modules/ajax/test.mod.php');

	echo '<script type="text/javascript">document.getElementById("laststep").disabled = false; </script>'."\r\n";
	echo '<script type="text/javascript">document.getElementById("laststep").value = \''.$lang['install_succeed'].'\'; </script>'."\r\n";
	echo '<iframe width="0" height="0" src="index.php"></iframe>';
}

if(function_exists('instfooter')) {
	instfooter();
}

?>
