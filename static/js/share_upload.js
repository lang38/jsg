/**
* 上传图片
*/

function upload_image_qun(){
	var backdrop_html = '<div id="jsg_backdrop" class="jsg_backdrop" style="z-index: 10001;height: 100%;width: 100%;background: none;position: fixed;top: 0px;left: 0px;"></div>';
    $("body").append(backdrop_html);//添加遮蔽层
	if($("#ulinnhtm1:visible")){
		var qid= 55;
	}else{
		var qid= 4;
	}
	$("body").append('<div class="jsg_modal" style="z-index: 10010;position: fixed;left: 45%;top: 40%;width:auto;height:width;background: #fff; border:5px solid #ddd; *border:1px solid #ddd;;"><iframe frameborder="no" border="0" style="border:none;margin:0;overflow:hidden;" width="320px" height="200px"  src="http://127.0.0.1/jishigou/index.php?mod=share&code=upload&item=qun&item_id='+qid+'"></iframe></div>');
	$(".jsg_backdrop").click(function(){
		$(".jsg_backdrop,.jsg_modal").remove();
	});	
}

function upload_image_cp(id){
	var id = 'undefined' == typeof(id) ? 0 : id;
	var backdrop_html = '<div id="jsg_backdrop" class="jsg_backdrop" style="z-index: 10001;height: 100%;width: 100%;background: none;position: fixed;top: 0px;left: 0px;"></div>';
    $("body").append(backdrop_html);//添加遮蔽层	
	$("body").append('<div class="jsg_modal" style="z-index: 10010;position: fixed;left: 45%;top: 40%;width:auto;height:width;background: #fff; border:5px solid #ddd; *border:1px solid #ddd;;"><h3 style="width:100%;height:35px; line-height:35px; background:#f2f2f2;margin:0;font-size: 14px;text-indent: 15px;color: #666;position: relative;">图片上传<span class="jsg_modal_imgspan" style="width:50px;height: 15px;color: #666;line-height: 15px;position: absolute;right:0;top:10px;font-size:12px;cursor: pointer;font-weight: 500;" onclick="closewindow();">关闭</span></h3><iframe frameborder="no" border="0" style="border:none;margin:0;overflow:hidden;" width="320px" height="165px"  src="http://weibo2.hzswb.cn/index.php?mod=share&code=upload&item=company&item_id='+id+'"></iframe></div>');
	$(".jsg_backdrop").click(function(){
		$(".jsg_backdrop,.jsg_modal").remove();
	});	
}
function closewindow(){$(".jsg_backdrop,.jsg_modal").remove();}