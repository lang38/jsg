function get_album(aid,page){
	$.post('ajax.php?mod=uploadify&code=album',{aid:aid,page:page},function(d){$('#album_list').html(d);});
}
function selectCreateTab(flag){
	if(flag==0){
		var html = "<input type='text' maxlength='15' name='newalbum' id='newalbum' size='15' value='请输入相册名称' onfocus=\"if(this.value == '请输入相册名称') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '请输入相册名称';}\">&nbsp;<button type='button' class='u-btn' onclick='createNewAlbum(0);'><span>创建新相册</span></button>&nbsp;<button type='button' class='u-btn u-btn-cancel' onclick='selectCreateTab(1);'><span>取消</span></button>";
		$('#albumlist').hide();$('#creatalbum').html(html);$('#creatalbum').show();
	}else{
		$('#uploadalbum').val(0);$('#albumlist').show();$('#creatalbum').html('');$('#creatalbum').hide();
	}
}
function createNewAlbum(type){
	var name = $('#newalbum').val();
	if(name && name != '请输入相册名称'){
		$.post('ajax.php?mod=uploadify&code=addalbum',{name:name},function(d){if(d>0){if(type){$('#albumlists').prepend('<li class="new" id="album_'+d+'"><img src="images/noavatar.gif" width="130" height="130"><br><a href="index.php?mod=album&aid='+d+'">'+name+'</a><br>图片：0张<br><a class="usermod" href="javascript:;" onclick=\'albumimagedone("album","mod",'+d+');\'>编辑</a><a class="usermod" href="javascript:;" onclick=\'albumimagedone("album","del",'+d+');\'>删除</a></li>');$('#addalbumhtml').html('<input type="button" value="添加相册" class="u-btn" onclick="Createalbum(0);">');show_message('相册添加成功!');}else{$('#uploadalbum').append('<option value="'+d+'" selected>'+name+'</option>');$('#albumlist').show();$('#creatalbum').html('');$('#creatalbum').hide();}}else{show_message('该相册已经存在，添加失败！',2,'提示','msgBox','msg_alert');}});
	}
}
function selectAllUpImg(state){
	if(state){$("input[name='ids[]']").attr("checked",true);}else{$("input[name='ids[]']").attr("checked",false);}
}
function upimgback(app,handle_key){
	$("input[name='idas[]']:checked").each(function(){__IMAGE_IDS__[$(this).val()] = $(this).val();});
	$('#formalbum').attr({action:"index.php?mod=album&code=updateimg&reload=1&app="+app+"&hkey="+handle_key,target:"frameupimg",method:"post"});
	$('#formalbum').submit();
}
function albumimagedone(type,done,id){
	if('mod'==done){
		var ajax_url = "ajax.php?mod=uploadify&code=albumedit&type="+type+"&id="+id;
		var handle_key = type+'_win_'+id+'_'+Date.parse(new Date());
		showDialog(handle_key, 'ajax', "编辑", {"url":ajax_url});
	}else if('del'==done){
		var mstr = ('album'==type) ? '相册' : '图片';
		options = {'onClickYes':function(){
			$.post('ajax.php?mod=uploadify&code=delalbumimage',{type:type,id:id},function(d){if(d>0){show_message('删除成功');$('#'+type+'_'+id).remove();}else{show_message('该'+mstr+'不可以删除，删除失败！',2,'提示','msgBox','msg_alert');}});
		}}
		MessageBox('confirm','您确定要删除该'+mstr+'吗？','提示', options);
	}
}
function Createalbum(flag){
	if(flag==0){
		var html = "<input type='text' maxlength='15' name='newalbum' id='newalbum' size='30' value='请输入相册名称' onfocus=\"if(this.value == '请输入相册名称') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '请输入相册名称';}\">&nbsp;<button type='button' class='u-btn' onclick='createNewAlbum(1);'><span>创建新相册</span></button>&nbsp;<button type='button' class='u-btn u-btn-cancel' onclick='Createalbum(1);'><span>取消</span></button>";
	}else{
		var html = '<input type="button" value="添加相册" class="u-btn" onclick="Createalbum(0);">';
	}
	$('#addalbumhtml').html(html);
}
function editalbum(handle_key,type,id){
	if('image' == type){if($('#namea').val() != $('#oldnamea').val()){$('#'+type+'_'+id).remove();}else if($('#description').val() != $('#olddescription').val()){$('#'+type+'_'+id+'_name').html($('#description').val());}}else if($('#namea').val() != $('#oldnamea').val()){$('#'+type+'_'+id+'_name').html($('#namea').val());}
	$('#editalbumform').attr({action:"index.php?mod=album&code=updatealbum&type="+type+"&id="+id+"&hkey="+handle_key,target:"frameeditalbum",method:"post"});
	$('#editalbumform').submit();
}