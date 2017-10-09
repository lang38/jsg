<?php
/**
 * 
 * @author xionghui
 * @version $Id: test.mod.php 3699 2013-05-27 07:26:39Z wuliyong $
 *
 */
class test {
	
	function test(){
		/*
		$md5_right = 'd7573d';
		if( !isset($_GET['secret']) || $md5_right != substr ( md5( substr( md5($_GET['secret'].'pass12345'),0 ,10 ) ), 0, 6 ) ){
			exit('WRONG DEBUG SECRET!');
		}
		*/
	}
	
	function default_action(){
		//echo 'echo default_action';
	}
	
	function urlTest(){
		/*
		$url = XWB_plugin::URL('test.urlTest');
		$entryurl = XWB_plugin::URL('test.urlTest', false, 'ETCCC/FFFFF/');
		$entryurl2 = XWB_plugin::URL('test.urlTest', array('txt', 'ecx'=>2));
		$baseurl = XWB_plugin::baseUrl();
		$siteurl = XWB_plugin::siteUrl();
		
		echo <<<EOF
		
		 XWB_plugin::URL('test.urlTest') => {$url}<br />
		 XWB_plugin::URL('test.urlTest', false, 'ETCCC/FFFFF/') => {$entryurl}<br />
		 XWB_plugin::URL('test.urlTest', array('txt', 'ecx'=>2))  => {$entryurl2}<br />
		 XWB_plugin::baseUrl() => {$baseurl}<br />
		 XWB_plugin::siteUrl() => {$siteurl}<br />
EOF;
		*/
		
	}
	
	/*
	function o(){
		$rst = XWB_plugin::getBindInfo();
		var_dump($rst);
	}
	function t(){
		print_r(array(XWB_S_TITLE,XWB_S_CHARSET,XWB_S_TBPRE,XWB_S_VERSION,XWB_S_UID));exit;
	}
	

	function debug(){
		if ($_GET['info']){
			phpinfo();
		}else{
			echo '<pre>';
			echo XWB_plugin::URL("xwbSiteInterface.reg");
			echo "\n";
			print_r($_SERVER);
		}
		
	}
	*/
	
}

?>