function get_new_topic_nums(){
	var cookie_array = {};var get_cookie_nums = Cookies.getCookie('cookie_new_nums');
	if(get_cookie_nums){
		var s= get_cookie_nums.split(',');
		for(var i=0;i<s.length;i++){
			var ss = s[i].split(':');cookie_array[ss[0]] = isNaN(ss[1]) ? 0 : ss[1];
		}
	}
	var myAjax=$.post("ajax.php?mod=dig&code=newtopicnums",{},function(d){
		var json = eval('('+d.toString()+')');var set_cookie_nums = ''; 
		$.each(json, function(k, v){
			v = isNaN(v) ? 0 : v;
			set_cookie_nums = set_cookie_nums + (set_cookie_nums ? (',' + k + ':' + v) : (k + ':' + v));
			cookie_array[k] = cookie_array[k] ? parseInt(cookie_array[k]) : 0;
			if(cookie_array[k] != v){
				var num = parseInt(parseInt(v)-parseInt(cookie_array[k]));
				if(k.indexOf('channel_')!=-1 && parseInt(cookie_array[k])==0){num=0;modify_new_topic_nums(k,v);}
				var strid = 'nav_num_'+k;
				if($('#'+strid).length>0 && num>0){
					$('#'+strid).html('<em>'+num+'</em>');
				}
			}
		});
	});
	return false;
}

function modify_new_topic_nums(name,value){
	var set_cookie_nums = '';var new_name = 1;
	if($('#nav_num_'+name).length>0){
		var value = 'undefined' == typeof(value) ? ($('#nav_num_'+name).text() ? parseInt($('#nav_num_'+name).text()) : 0) : parseInt(value);
		value = isNaN(value) ? 0 : value;
		var get_cookie_nums = Cookies.getCookie('cookie_new_nums');
		if(get_cookie_nums){
			var s= get_cookie_nums.split(',');
			for(var i=0;i<s.length;i++){
				var ss = s[i].split(':');ss[1] = isNaN(ss[1]) ? 0 : ss[1];
				if(ss[0]==name){
					if(name.indexOf('channel_')!=-1){
						ss[1] = parseInt(ss[1]) + parseInt(value);
					}else{
						ss[1] = 0;
					}
					new_name = 0;
				}
				set_cookie_nums = set_cookie_nums+(set_cookie_nums ? (','+ss[0]+':'+ss[1]) : (ss[0]+':'+ss[1]));
			}
			if(new_name){
				set_cookie_nums = set_cookie_nums+','+name+':'+value;
			}
		}else{
			set_cookie_nums = name+':'+value;
		}
		$('#nav_num_'+name).html('');
		Cookies.setCookie('cookie_new_nums',evalscript(set_cookie_nums),8640000000,'/');
	}	
	return false;
}