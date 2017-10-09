function imageUploadifySelectOnce(divid){
	$('#uploading'+divid).html("<img src='images/loading.gif'/>&nbsp;上传中，请稍候……");
}

function imageUploadifyAllComplete(divid){
	$('#uploading'+divid).html('');
	$('#normalUploadFile'+divid).val('');
	if($.trim($('#i_already'+divid).val()).length < 1){$('#i_already'+divid).val('分享图片');}
	$('#i_already'+divid).focus();
}		

function imageUploadifyComplete(divid,idval, srcval, nameval){
	var imageIdsCount = 0;$.each( __IMAGE_IDS__, function( k, v ) { if( v > 0 ) { imageIdsCount += 1; } } );
	if( imageIdsCount >= 10 ){
		MessageBox('warning', '本次图片数量超过限制了');
	    return false;
	}
	var idval = ('undefined' == typeof(idval) ? 0 : idval);	
	var srcval = ('undefined' == typeof(srcval) ? 0 : srcval);
	$('#viewImgDiv'+divid).append("<tr id='upimg_"+idval+"'><td valign='top'><input type='checkbox' name='ids[]' value='" + idval + "' title='保存到相册' checked='true'></td><td valign='middle'><a href='javascript:void(0);' id='insert_image_"+idval+"' onclick=\"insertIntoContent('image','"+idval+"','i_already"+divid+"');\" title='点击插入' class='upimg_img'><img src='" + srcval + "'/></a></td><td valign='middle'><textarea name='title["+idval+"]' maxlength='140' onfocus=\"if(this.value == '图片简介') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '图片简介';}\">图片简介</textarea></td><td valign='middle'><a href='javascript:void(0);' onclick='imageUploadifyDelete(" + idval + "); return false;' title='删除图片' class='u-del'>X</a></td></tr>");
	$('#albumlist'+divid).show();
	__IMAGE_IDS__[idval] = idval;
}

function normalUploadFormSubmit(divid){
	var fileVal = $('#normalUploadFile'+divid).val();
	if(($.trim(fileVal)).length < 1){
		MessageBox('warning', '请选择一个正确的图片文件');
		return false;
		}
		else{
			if(!(/\.(jpg|png|gif|jpeg)$/i.test(fileVal))){
				MessageBox('warning', '请选择一个正确的图片文件');
				return false;
		}
    else{
		imageUploadifySelectOnce(divid);
		return true;
		}}
}
//删除一张图片
function imageUploadifyDelete(idval,tid){
	var idval = ('undefined'==typeof(idval) ? 0 : idval);
	var tid = ('undefined' == typeof(tid) ? 0 : tid);
	if(idval > 0){
		$.post('ajax.php?mod=uploadify&code=delete_image',{'id':idval,'tid':tid},function (r){if(r.done){$('#upimg_' + idval).remove();
		if( 'undefined' != typeof(__IMAGE_IDS__[idval]) ){
			__IMAGE_IDS__[idval] = 0;
		}}
		else{
			if(r.msg){
				MessageBox('warning', r.msg);
			}}},'json');
		}
}

//上传网络图片
function getUrlImage(divid,url){
	var url = $.trim(url.value);
	if(!url){
		show_message("请输入你要上传的网络图片地址");
	    return false;
	}
	if (!/.(gif|jpg|png|jpeg)$/i.test(url)) {
		show_message("请上传jpg、jpeg、gif、png格式的图片。");
		return false;
		}
	$("#uploading_img"+divid).show();
	$.post('ajax.php?mod=uploadify&code=urlimage',{url:url,type:'flash'},function (r) {
		$("#uploading_img"+divid).hide();
		if (r.done) {
			var rv = r.retval;
			if ( rv.id > 0 && rv.src.length > 0 ) {
				var imageIdsCount = 0;
				$.each( __IMAGE_IDS__, function( k, v ) { 
				if( v > 0 ) { 
				imageIdsCount += 1; 
				} } );
			if( imageIdsCount >= 10 ){
				MessageBox('warning', '本次图片数量超过限制了');
				return false;
			}
			var idval = rv.id;
			var srcval = rv.src;
			$('#viewUrlImgDiv'+divid).append("<tr id='upimg_"+idval+"'><td valign='top'><input type='checkbox' disabled='disabled'></td><td valign='middle'><a href='javascript:void(0);' id='insert_image_"+idval+"' onclick=\"insertIntoContent('image',"+idval+",'i_already"+divid+"');\" title='点击插入' class='upimg_img'><img src='" + srcval + "' /></a></td><td valign='middle'><textarea name='title["+idval+"]' maxlength='140' onfocus=\"if(this.value == '图片简介') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '图片简介';}\">图片简介</textarea></td><td valign='middle'><a href='javascript:void(0);' onclick='imageUploadifyDelete(" + idval + "); return false;' title='删除图片' class='u-del'>X</a></td></tr>");
			$('#img_url'+divid).val('');
			__IMAGE_IDS__[idval] = idval;
			$('#urlimgbutton'+divid).show();
		}} else {
			show_message(r.msg);
			}
		return false;},'json');
}

/*选择相册*/
function selectCreateTab(divid,flag){
	if(flag==0){
		var html = "<input type='text' maxlength='15' name='newalbum' id='newalbum"+divid+"' size='15' value='请输入相册名称' onfocus=\"if(this.value == '请输入相册名称') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '请输入相册名称';}\">&nbsp;<button type='button' class='u-btn' onclick=\"createNewAlbum('"+divid+"',0);\">创建新相册</button>&nbsp;<button type='button' class='u-btn u-btn-cancel' onclick=\"selectCreateTab('"+divid+"',1);\">取消</button>";
		$('#albumlist'+divid).hide();
		$('#creatalbum'+divid).html(html);
		$('#creatalbum'+divid).show();
		}
	else{
		$('#uploadalbum'+divid).val(0);
		$('#albumlist'+divid).show();
		$('#creatalbum'+divid).html('');
		$('#creatalbum'+divid).hide();
		}
}

/*创建相册*/
function createNewAlbum(divid,type){
	var name = $('#newalbum'+divid).val();
	if(name && name != '请输入相册名称'){
		$.post('ajax.php?mod=uploadify&code=addalbum',{name:name},function(d){
			if(d>0){
				if(type){
					$('#albumlists').prepend('<li class="new" id="album_'+d+'"><img src="images/noavatar.gif" width="130" height="130"><br><a href="index.php?mod=album&aid='+d+'">'+name+'</a><br><a class="usermod" href="javascript:;" onclick=\'albumimagedone("album","mod",'+d+');\'>编辑</a><a class="usermod" href="javascript:;" onclick=\'albumimagedone("album","del",'+d+');\'>删除</a></li>');
					$('#addalbumhtml').html('<input type="button" value="添加相册" class="u-btn" onclick="Createalbum(0);">');
					show_message('相册添加成功!')
					;}
				    else{
						$('#uploadalbum'+divid).append('<option value="'+d+'" selected>'+name+'</option>');
						$('#albumlist'+divid).show();
						$('#creatalbum'+divid).html('');
						$('#creatalbum'+divid).hide();
					}
				  }
			else{
				show_message('该相册已经存在，添加失败！',2,'提示','msgBox','msg_alert');
				}
			  });
			}
}

function get_album(divid,aid,page){
	$.post('ajax.php?mod=uploadify&code=album',{divid:divid,aid:aid,page:page},function(d){$('#album_list'+divid).html(d);});
}
/*选择相片*/
function selectAllUpImg(state){
	if(state){
		$("input[name='ids[]']").attr("checked",true);
	}
	else{
	    $("input[name='ids[]']").attr("checked",false);
	}
}

//百度搜图
function searchBaiDuImg(divid){
    var word = $("#word"+divid).val();
	if('undefined' == word || '' == word || 'undefined' == typeof(word)){
		show_message("请输入要搜索的关键字。");
		return false;
		}
	var page = $("#bpage"+divid).val();
	if('undefined' == page || '' == page || 'undefined' == typeof(page)){
		page = 0;
	}
	var per = 6;
	$.post('ajax.php?mod=uploadify&code=searchimage',{word:word,page:page,per:per},function (d){d= eval('('+d+')');if(d.listNum>0){var totle = (d.listNum > 60 ? 60 : d.listNum);
	var num = Math.ceil(totle/per);
	var phtml = page_htmls(page,num,divid);
	var data = d.data;
	var html = '<ul>';
	for(var i=0; i<data.length; i++){if('undefined' !== typeof(data[i].objURL)){
		html = html + '<li><a href="javascript:;" onclick="insertUrlIntodivContent(\''+data[i].objURL+'\',\'i_already'+divid+'\');" title="点击插入"><img src="' + data[i].objURL + '"/></a></li>';}}html += '</ul><input type="hidden" id="bpage'+divid+'"><p>'+ phtml +'</p>';
		$('#baidu_image'+divid).html(html);
		}
	else{
		$('#baidu_image'+divid).html('<p>未搜到符合条件的图片</p>');
	}});
}

function page_htmls(page,num,divid){
	page = parseInt(page);num = parseInt(num);
	var html = '';if(num < 2){return '';}
	if(page > 0){
		var fpage = page-1;
		html += '<span onclick="changebpage('+ fpage +',\''+divid+'\');" style="cursor:pointer;">上一页</span>';}if(page < num){var npage = page+1;html += '<span onclick="changebpage('+ npage +',\''+divid+'\');" style="cursor:pointer;">下一页</span>';}
		return html;
}

function changebpage(page,divid){
	$('#bpage'+divid).val(page);
	searchBaiDuImg(divid);
}
	
function insertUrlIntodivContent(str,divid){
	$("#"+divid).insertAtCaret(str,0);
	$("#"+divid).focus();
}

function saveimgcallback(divid,handle_key){
	$("input[name='idas[]']:checked").each(function(){
		__IMAGE_IDS__[$(this).val()] = $(this).val();
	});
	$('#formalbum'+divid).attr({action:"index.php?mod=album&code=updateimg&hkey="+handle_key,target:"frameupimg"+divid,method:"post"});
	$('#formalbum'+divid).submit();
}