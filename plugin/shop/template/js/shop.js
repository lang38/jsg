$(function(){
	$("#shop_add").click(function(){check_shop();});
});
function check_shop(){
	var inputurl = $("#shop_url").val();
	if(inputurl.length==0){
		$("#upload_shop_list").html('<font color="red">商品地址不能为空，请输入商品链接地址！</font>');$("#shop_url").val('http://');return false;
	}
	$("#upload_shop_list").html("<div><center><span class='loading'>数据处理中，请稍候……</span></center></div>");
	var myAjax = $.post("ajax.php?mod=plugin&id=shop:shop",{code:'getshop',url:inputurl},function(d){
		if(''!=d){
			var s= d.split('|||');
			if(s[0] > 0){
				plugindata.shopid=s[0];//此处为传入商品ID值给POST变量plugindata
				$("#upload_shop_list").html(s[1]);$("#upload_shop_input").hide();
			}else{
				$("#upload_shop_list").html(s[1]);$("#shop_url").val('http://');
			}
		}
	});
}
function del_shop(shopid){
	$("#upload_shop_list").html("");$("#upload_shop_input").show();$("#shop_url").val('http://');
	$.post("ajax.php?mod=plugin&id=shop:shop",{code:'delshop',shopid:shopid},function(){});
}