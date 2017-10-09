function c_show(){
	$('#p_channel').show();
	$('#t_pb').addClass('hover');
}
function c_cut(){
	$('#t_channel').html('');
	$('#mapp_item{$h_key}').val('{$this->item}');
	$('#mapp_item_id{$h_key}').val('{$this->item_id}');
}
function c_hide(){
	$('#p_channel').hide();
	$('#t_pb').removeClass('hover');
}
function c_int(n,s){
	$('#p_channel').hide();
	$('#t_pb').removeClass('hover');
	$('#t_channel').html(s+'<em onclick="c_cut();">×</em>');
	$('#mapp_item{$h_key}').val('channel');
	$('#mapp_item_id{$h_key}').val(n);
}