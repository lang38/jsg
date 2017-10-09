/**
 * 验证码函数
 * @version $Id$
 */
function YXM_float(tid,type){
	var tid='undefined'==typeof(tid)?'':'_'+tid;
	var type='undefined'==typeof(type)?'':'_'+type;
	$('#yxm_pub_button'+type+tid).click();
}
function YXM_pubtopic(ci,cp,type,bn,itemid,channelmust){
	var content=$('#'+ci).val();
	var type = $('#'+type).val();
	var type = ('undefined' == typeof(type) ? '' : type);
	var content='undefined'==typeof(content)?'':content;
	var itemid='undefined'==typeof(itemid)?0:itemid;
	var channelmust='undefined'==typeof(channelmust)?0:channelmust;
	var cp='undefined'==typeof(cp)?'':cp;
	var bn='undefined'==typeof(bn)?'':bn;
	if(''==content || '#插入自定义话题#'==content){
		show_message('请输入内容',1,'提示','msgBox','msg_alert');
		return false;
	}
	if(content.length<2){
		show_message('内容至少2个字',1,'提示','msgBox','msg_alert');
		return false;
	}
	if(channelmust && itemid<1){
		show_message('您必须要选择一个频道才能发布',2,'提示','msgBox','msg_alert');
		return false;
	}
	if(((type=='reply' && YXM_CODE_COM!='1') || (type=='forward' && YXM_CODE_FOR!='1') || (type=='both' && (YXM_CODE_COM!='1' && YXM_CODE_FOR!='1'))) && bn)
	{
		$('#'+bn).click();
	}else{
		$('#yxm_pub_button'+cp).click();
	}
}
function YXM_submit(obj,form,type,title){
	var bstr = document.getElementById(form).onsubmit();
	if(bstr===true){
		YXM_popBox(obj,type+','+form,title);
	}
}
function YXM_valided_true(){
	var str = YXM_param.split(',');
	if(str[0]=='login'){
		guestLoginSubmit();
	}else if(str[0]=='reg' || str[0]=='getpsd'){
		add_input();
		$('#'+str[1]).submit();
	}else if(str[0]=='sms'){
		sendMsg();
	}else if(str[0]=='sms1'){
		sendMsg(1);
		return false;
	}else if(str[0]=='smsajax'){
		$('#yxm_ajaxsms').click();
	}else if(str[0]=='atajax'){
		$('#yxm_ajaxat').click();
	}else if(str[0]=='topic'){
		$('#yxm_topicpub'+str[1]).click();
	}else if(str[0]=='forward'){
		$('#YXM_F_'+str[1]).click();
	}else if(str[0]=='reply'){
		$('#YXM_R_'+str[1]).click();
	}else if(str[0]=='rreply'){
		$('#yxm_rtopicpub').click();
	}
}
function YXM_valided_false(){
	show_message('验证码输入错误',1,'提示','msgBox','msg_alert');
	return false;	
}
function add_input(){
	$('#add_YXM_input_result').val($('#YXM_input_result').val());
	$('#add_YXM_level').val($('#YXM_level').val());
	$('#add_YinXiangMa_challenge').val($('#YinXiangMa_challenge').val());
}