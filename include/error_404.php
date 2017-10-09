<?php
	@header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html PUBliC "-//W3C//DTD XHTML 1.0 Transitional//EN"
         "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>没有找到您要访问的页面</title>
 <style type="text/html">BODY {
	FONT-SIZE: 14px; FONT-FAMILY: arial,sans-serif
}

H1 {
	FONT-SIZE: 22px
}
UL {
	MARGIN: 1em
}
li {
	liNE-HEIGHT: 2em; FONT-FAMILY: 宋体
}
A {
	COLOR: #00f
}
</style>
</head>
 <body>
<blockquote>
  <h1>没有找到您要访问的页面</h1>
  The requested URL was not found on this server. 
  <ol>
    <li>出现这个页面，可能是你输入的网址不正确；</li>
	<li>也可能是站长设置了服务器不支持的URL静态化模式；</li>
	<li><?php echo $msg;?></li>
	</ol>
  </blockquote> <p></p></body>
</html>
<?php exit;?>