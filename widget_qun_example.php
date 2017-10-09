<!-- 本文件为微群站外调用测试文件，可以据此进行相应的修改 -->
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html;">
	<script type="text/javascript" src="./static/js/min.js"></script>
		<style type="text/css">
			.jsg_mcenter{ width:600px; height:500px; background:#ffffff; border:1px solid #ebebeb; padding:1px; text-align:left}.iframe_share_title{ width:100%; height:30px; line-height:30px; overflow:hidden; background:#ffffff; color:#626262; font-size:14px; text-indent:7px;}.jsg_mcenter .comBox{ display:none;margin:10px 0; overflow:hidden; padding:10px; z-index:200}.jsg_mcenter .indexrow{ width:585px;}.jsg_mcenter .feedCell{ width:585px; overflow:hidden;}.jsg_mcenter .msg_left_img{ width:47px; height:47px; float:left;display:inline;margin-right:10px;}.jsg_mcenter .msg_left_con{ float:left; display:inline; white-space:normal; *height: *height:100%;width:520px; overflow:hidden;}.jsg_mcenter .feedCell {padding:6px; line-height:18px; *width:100%; *height:100%; overflow:hidden;}.jsg_mcenter .feedCell .avatar {display:inline;float:left;height:45px;width:45px; overflow:hidden; margin-bottom:-15px;margin-top:4px;text-align:center;border:1px solid #ccc; padding:1px;}.jsg_mcenter .feedCell .avatar img {height:45px;width:45px;}.jsg_mcenter .feedCell .feedUservideo {overflow:hidden;position:relative;}.jsg_mcenter .feedCell .feedUservideo img {border:1px solid #dcdcdc;padding:2px; cursor:pointer; width:120px; height:80px;}.jsg_mcenter .feedCell .feedUservideo .vP{position:absolute; margin:27px 46px;}.jsg_mcenter .feedCell .feedUservideo .vP img{border:none; filter:alpha(opacity=70);-moz-opacity:0.7; opacity:0.7; width:25px; height:25px;}.jsg_mcenter .bg_arrow{background:#e6e6e6 url(./images/ico_tblog_arrow.png);background-repeat:repeat-x;height:7px;text-align:center;margin:0}.jsg_mcenter .bg_arrow img{ margin:0 auto; width:8px; height:4px; background:url(./images/ico_tblog_arrow.png) no-repeat}.jsg_mcenter a.arrow_up img {background-position: 0 -20px;}.jsg_mcenter a.arrow_down {background-position: 0 -32px;}.jsg_mcenter .mBlog_linedot {background:url(./static/image/linedot.gif) repeat-x;clear:both;height:1px;overflow:hidden;margin-top:5px;}.jsg_mcenter .list_warp{overflow:scroll;overflow-x:hidden;width: 100%; position:relative; height:480px;overflow:hidden;}.jsg_mcenter .iframe_tt{ width:100%; height:7px; background:#e4e4e4; margin:0; overflow:hidden;}.jsg_mcenter .iframe_tt .bg_arrow{ background-position:0 2px; margin-left:47%;} .jsg_mcenter .iframe_ft{ width:100%; height:7px; background:#e4e4e4; margin:0; overflow:hidden; margin-top:7px;}.jsg_mcenter .iframe_ft .bg_arrow{ background-position:0 -12px; margin-left:47%;}
		</style>

	<script language="javascript">
		$(document).ready(function(){
			$.get(
				"widget.php?mod=qun&code=list&in_ajax=1",
				{
					qid:1, /* 群ID */
					page:1, /* 页码 */
					page_size:10 /* 每页显示数 */
				},
				function (d) {
					if (d.error == true) {
						alert("【no result】" + d.result);
					} else {
						var topicList = d.result.topic_list;
						var count = topicList.length;
						var html = '';
						/* 返回内容组合成HTML代码进行显示 */
						for (var i in topicList) {
							html = html + '<div id="ds1" class="indexrow"><div class="feedCell"><div style="color:#626262;"><div class="msg_left_img"><span class="avatar"><img border="0" src="'+topicList[i].face_small+'" onerror="javascript:faceError(this);"></span>&nbsp;</div><div style="margin-top:3px;" class="msg_left_con">'+ topicList[i].nickname +':<a style="color:#7d7d7d;" target="_blank" href="index.php?mod=topic&code='+topicList[i].tid+'">'+topicList[i].content+'</a></div></div></div><div style="width:585px; overflow:hidden" class="mBlog_linedot"></div></div>';

						} $("#Pcontent").html(html);
					}
				},
				"json"
			);
		});
	</script>
	</head>
	<body>
	<div class="jsg_mcenter">
		<div style="z-index:100;height:480px; overflow:hidden; position:absolute; margin-left:5px;" id="Pcontent"></div>
	</div>
	</body>
</html>