//前后台分级select列单位目录
function listnextcategory(obj,j){
	if(obj.value){$('#catid').val(obj.value);}else{var k=j-2<0?0:j-2;$('#catid').val($('#categoryids_'+k).val());}
	if($('#nextcategory_'+j).length>0){for(i=j;i<=10;i++){if($('#nextcategory_'+i).length>0){$('#nextcategory_'+i).html('');}}}
	var myAjax=$.post("ajax.php?mod=cms",{catid:obj.value,j:j,code:'listcategory'},function(d){if(d){if($('#nextcategory_'+j).length==0){$(obj).after('<span id="nextcategory_'+j+'"></span>');}$('#nextcategory_'+j).html(d);}});return false;
}

function publishcms(aid,catid){
	var aid ='undefined'==typeof(aid) ? 0 : aid;
	var catid ='undefined'==typeof(catid) ? 0 : catid;
	var handle_key = 'publishcms';
	var ajax_url = "ajax.php?mod=cms&code=publish&aid="+aid+"&catid="+catid;
	showDialog(handle_key, 'ajax', "文章发布", {"url":ajax_url}, 650);
}

function cmssearchSubmit(charid){
	var schar = $('#'+charid).val();
	if('' == schar || '输入关键词' == schar){show_message('请输入关键词搜索',1,'提示','msgBox','msg_alert');return false;}
	$('#cmssearchform').attr({action:"index.php?mod=cms&code=channel&search=yes",method:"post"});
	$('#cmssearchform').submit();
}
function removeHTMLTag(str) {
    str = str.replace(/<\/?[^>]*>/g,''); //去除HTML tag
    str = str.replace(/[ | ]*\n/g,'\n'); //去除行尾空白
    //str = str.replace(/\n[\s| | ]*\r/g,'\n'); //去除多余空行
    str=str.replace(/&nbsp;/ig,'');//去掉&nbsp;
    return str;
}
function cmspublishSubmit(titleid,categoryid,contentid,fromid){
	var aid = $('#cmsaid').val();
	var cmstitle = $('#'+titleid).val();
	var cmscategory = $('#'+categoryid).val();
	var cmscontent = $('#'+contentid).val();
	var fromid = 'undefined'==typeof(fromid) ? 0 : fromid;
	if('' == cmstitle){show_message('请输入文章标题',1,'提示','msgBox','msg_alert');return false;}
	if('' == cmscontent){show_message('请输入文章内容',1,'提示','msgBox','msg_alert');return false;}
	if(1 > cmscategory){show_message('请选择文章分类',1,'提示','msgBox','msg_alert');return false;}
	var imageids = '';$.each(__IMAGE_IDS__, function(k, v){if(v > 0){imageids = imageids + (imageids ? ',' + v : v );}});
	var attachids = '';$.each(__ATTACH_IDS__, function(k, v){if(v > 0){attachids = attachids + ( attachids ? ',' + v : v );}});
	if(fromid < 1){fromid = $('#categoryids_0').val();}
	$.post("ajax.php?mod=cms&code=addcms",{aid:aid,title:cmstitle,catid:cmscategory,content:cmscontent,imageid:imageids,attachid:attachids},function(d){
		if(''!=d){
			if(d.indexOf("发布成功") != -1){
				var s= d.split('|||');
				if(''!= imageids){__IMAGE_IDS__ = {};}
				if(''!= attachids){__ATTACH_IDS__ = {};}
				closeDialog("publishcms");
				$('.cmslist_'+fromid).prepend('<li><a href="index.php?mod=cms&code=article&id='+s[0]+'">'+removeHTMLTag(cmstitle)+'</a><span class="f-right">'+s[2]+'</span>');
				$('.noarticle').empty();
				show_message(s[1]);
			}else if(d.indexOf("修改成功") != -1){
				if(''!= imageids){__IMAGE_IDS__ = {};}
				if(''!= attachids){__ATTACH_IDS__ = {};}
				closeDialog("publishcms");
				show_message(d);
			}else{
				show_message(d,1,'提示','msgBox','msg_error');
			}
		}
	});
}

function cmsreplypublishSubmit(aid,contentid){
	var cmsaid = $('#'+aid).val();
	var cmsreplycontent = $('#'+contentid).val();
	var totopic = $('#totopic').val();
	if(cmsaid<1){show_message('发布失败，您的操作错误',1,'提示','msgBox','msg_alert');return false;}
	if('' == cmsreplycontent){show_message('请输入评论内容',1,'提示','msgBox','msg_alert');return false;}
	if(cmsreplycontent.length < 2){show_message('评论内容至少2个字',1,'提示','msgBox','msg_alert');return false;}
	$.post("ajax.php?mod=cms&code=addreply",{aid:cmsaid,content:cmsreplycontent,totopic:totopic},function(d){
		if(''!=d){
			if(d.indexOf("评论成功") != -1){
				$('#'+contentid).val('');var s= d.split('|||');
				$('#cmsreplynew').prepend('<p>'+cmsreplycontent+'（by '+s[1]+'：'+s[2]+'）</p>');$('#cmsreplyno').empty();var num = parseInt($('#cmsreplynum').html())+1;$('#cmsreplynum').html(num);
				show_message(s[0]);
			}else{
				show_message(d,1,'提示','msgBox','msg_error');
			}
		}
	});
}

function cat_list(obj,id,urlid){
	var img;var title;var o = obj.parentNode;
	if($('#cms_img_'+id).length>0){
		if('images/cp/close.gif'==$('#cms_img_'+id).attr("src")){img='images/cp/open.gif';}else{img='images/cp/close.gif';}
		$('#cms_img_'+id).attr("src",img);
	}	
	if($('#cms_'+id).length>0){$('#cms_'+id).toggle();}else{cat_list_down(obj,id,urlid);}
}

function cat_list_down(obj,id,urlid){	
	var o = obj.parentNode;
	var myAjax=$.post("ajax.php?mod=cms",{id:id,urlid:urlid,code:'catlistajax'},function(d){if(d){$(o).append(evalscript(d));}});
	return false;
}

function checkcms(aid){
	var aid ='undefined'==typeof(aid) ? 0 : aid;
	if(aid>0){var myAjax=$.post("ajax.php?mod=cms",{aid:aid,code:'checkcms'},function(d){if(d){show_message(d);$('.check_'+aid).empty();}});}
}

function checkhtml(catid){
	var catid ='undefined'==typeof(catid) ? 0 : catid;
	window.location.href="index.php?mod=cms&code=chk&id="+catid;
}