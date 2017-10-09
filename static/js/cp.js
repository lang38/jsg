function cp_list(obj,h,t,id,du){
	var img;var title;var o = obj.parentNode;
	if($('#'+t+'_img_'+id).length>0){
		if('images/cp/close.gif'==$('#'+t+'_img_'+id).attr("src")){img='images/cp/open.gif';}else{img='images/cp/close.gif';}
		$('#'+t+'_img_'+id).attr("src",img);title=o.childNodes[1].innerHTML;
	}else{
		title=o.childNodes[0].innerHTML;
	}
	
	if($('#'+t+'_'+id).length>0){$('#'+t+'_'+id).toggle();}else{cp_list_cp(obj,h,t,id);}
	if(!h){
	if(du && 'company' == t){$('#cp_title').html(title);if($('#cp_department').length>0){cp_list_department(id);}}
	if('company' == t){$('#cp_u_title').html(title);$('#dp_u_title').html('');}else{$('#dp_u_title').html(title);}
	cp_list_user(t,id);
	}
}

function cp_list_cp(obj,h,t,id)
{	
	var o = obj.parentNode;
	var myAjax=$.post("ajax.php?mod=misc",{id:id,t:t,h:h,code:'cpcp'},function(d){if(d){$(o).append(evalscript(d));}});
	return false;
}

function cp_list_department(id)
{	
	var myAjax=$.post("ajax.php?mod=misc",{id:id,code:'department'},function(d){if(d){$('#cp_department').html(evalscript(d));}else{$('#cp_department').html('');}});
	return false;
}

function cp_list_user(t,id)
{	
	var myAjax=$.post("ajax.php?mod=misc",{id:id,t:t,code:'cpuser'},function(d){if(d){$('#cp_user').html(evalscript(d));}else{$('#cp_user').html('');}});
	return false;
}

function listcphtml(obj,t,j)
{
	if(t=='company' && $('#departmentid').length>0){$('#departmentid').val('');}
	if($('#'+t+'id').length>0){$('#'+t+'id').val(obj.value);}
	if($('#next'+t+'_'+j).length>0){for(i=j;i<=10;i++){if($('#next'+t+'_'+i).length>0){$('#next'+t+'_'+i).html('');}}}else{$(obj).after('<span id="next'+t+'_'+j+'"></span>');}
	var myAjax=$.post("ajax.php?mod=misc",{id:obj.value,j:j,t:t,code:'listcp'},function(d){if(d){$('#next'+t+'_'+j).html(evalscript(d));}});
	if('company'==t && $('#departmentselect').length>0){$('#departmentselect').html('');
	var myAjax2=$.post("ajax.php?mod=misc",{cid:obj.value,id:0,j:0,t:'department',code:'listcp'},function(d){if(d){$('#departmentselect').append('<span id="nextdepartment_0"></span>');$('#nextdepartment_0').html(evalscript(d));}});
	}
	return false;
}

//前后台分级select列单位目录
function listnextc(obj,j)
{
	if($('#nextcompany_'+j).length>0){for(i=j;i<=10;i++){if($('#nextcompany_'+i).length>0){$('#nextcompany_'+i).html('');}}}else{$(obj).after('<span id="nextcompany_'+j+'"></span>');}
	var myAjax=$.post("ajax.php?mod=misc",{id:obj.value,j:j,code:'listc'},function(d){if(d){$('#nextcompany_'+j).html(evalscript(d));}});
	return false;
}

//后台用
function acp_list(obj,t,id){
	var img;
	if($('#img_'+id).length>0){
		if('images/cp/close.gif'==$('#img_'+id).attr("src")){img='images/cp/open.gif';}else{img='images/cp/close.gif';}
		$('#img_'+id).attr("src",img);
	}
	if($('#cpdown_'+id).length>0){$('#cpdown_'+id).toggle();}else{acp_list_cp(obj,t,id);}
}
//后台用
function acp_list_cp(obj,t,id)
{	
	if(t=='company'){var tn = 7;}else{var tn = 8;}
	var o = obj.parentNode.parentNode;
	var myAjax=$.post("ajax.php?mod=misc",{id:id,t:t,code:'acpcp'},function(d){if(d){$(o).after('<tr><td colspan="'+tn+'" id="cpdown_'+id+'"></td></tr>');$('#cpdown_'+id).html(evalscript(d));}});
	return false;
}