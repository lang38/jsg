/**
 * 智能发布框相关的函数库
 *
 * @author     jishigou
 * @version $Id$
 */

var ctrlEnter;
/**
 * 获取粉丝分组选择框
 */
function getFansGroupSelect(id)
{
	$.get(
		'ajax.php?mod=misc&code=fansgroup_select',
		{},
		function(r) {
			$("#"+id).html(r);
			if ($('#fansgroup_select').val() == 0) {
				if ($('#weibo_syn_wp')) {
					$('#weibo_syn_wp').show();
					__ALLOW_WEIBO_SYN__ = true;
				}
			}
			$("#fansgroup_select").change(function(){
				if ($('#fansgroup_select').val() == 0) {
					if ($('#weibo_syn_wp')) {
						$('#weibo_syn_wp').show();
						__ALLOW_WEIBO_SYN__ = true;
					}
				} else if ($('#fansgroup_select').val() == 'create') {
					$("#fansgroup_select").get(0).selectedIndex = 0; 
					showFansGroupAddDialog();
				} else {
					if ($('#weibo_syn_wp')) {
						$('#weibo_syn_wp').hide();
						__ALLOW_WEIBO_SYN__ = false;
					}
					$('#topic_type').val($('#fansgroup_select').val());
				}
			});
		}
	);	
}

/**
 * 获取我的投票列表
 */
function getMyVoteList(page, options)
{
	var cache = Cache.get('con_vote_content_2');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_vote_content_2').html(cache);
		return ;
	}	
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=vote&code=my_vote&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_vote_content_2').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_vote_content_2').html(r);
					Cache.save('con_vote_content_2', r);
				}
			}
		}
	);
}

/**
 * 获取我的参与的投票列表
 */
function getMyJoinList(page, options)
{
	var cache = Cache.get('con_vote_content_3');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_vote_content_3').html(cache);
		return ;
	}
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=vote&code=my_join&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_vote_content_3').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_vote_content_3').html(r);
					Cache.save('con_vote_content_3', r);
				}
			}
		}
	);
}

/**
 * 从首页获取投票应用
 */
function getVoteAvtivityFromIndex(appCode, appWpId,appType)
{
	var options = {
		arf:'index'
	};
    if('undefined' != appType){
        options.appType = appType;
    }
	getAppActivity('vote', appCode, appWpId, options);
}

//获取我的群
function getMyQun()
{	
	var html = $('#wcontent_wp').html();
	if (html == '') {
		$('#wcontent_wp').html(lang('loading'));	
	} else {
		return false;
	}
	$.get(
		'ajax.php?mod=qun&code=widgets&op=my_qun&random='+Math.random(),
		{},
		function (d) {
			d = evalscript(d);
			$("#wcontent_wp").html(d);
			var cb = ComboBoxManager.create('my_qun_select');
			cb.setComboBoxWidth(175);
			cb.change = function() {
				if (cb.val() == 0) {
					$("#mapp_item").val('');
					$("#mapp_item_id").val('');
					$("#toweibo_wp").hide();
					$("#topic_type").val('first');
				} else {
					$("#mapp_item").val('qun');
					$("#mapp_item_id").val(cb.val());
					$("#topic_type").val('first');
					$("#toweibo_wp").show();
					$("#goto_qun").attr('href', 'index.php?mod=qun&qid='+cb.val());
				}
			};			
			$("#checkbox_toweibo").click(function(){
				if ($("#checkbox_toweibo").attr("checked")) {
					$("#topic_type").val('first');
				} else {
					$("#topic_type").val('qun');
				}
			});	
		}
	);
}

//获取签到话题
function getSignTag(uid)
{	
    if('undefined' == typeof(uid) || uid < 1){
        $(".menu_hts").hide();
        return false;
    }    
	$.get(
		'ajax.php?mod=class&code=getsigntag',
		{},
		function (d) {
			if(is_json(d)){
				var json = eval('('+d.toString()+')');
				if(json.done == false){
					$('#sign_tag_'+uid).html(json.msg);
				}
			}else{
				$('#sign_tag_'+uid).html(d);
			}
		}
	);
}

//话题填充到发布框
function setSignTag(tag){
    if ('undefined' != typeof(tag) && tag) {
        tag = '#' + tag + '#';
        $("#"+__CONTENTID__).insertAtCaret(tag,0);
    }    
    $('.menu_hts').hide();
}

/**
 * 在首页发布活动
 */
function getEventPost()
{
	var cache = Cache.get('con_event_content_1');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_1').html(cache);
		return ;
	}
	$.ajaxSetup({cache : true});
	$.get(
		'ajax.php?mod=event&code=eventpost',
		{'item':__APPITEM__,
		 'item_id':__APPITEM_ID__},
		function(r){
			$('#con_event_content_1').html(r);
			Cache.save('con_event_content_1', r);
		}
	);	
}

/**
 * 获取我的活动列表
 */
function getMyEventList(page, options)
{
	var cache = Cache.get('con_event_content_2');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_2').html(cache);
		return ;
	}	
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=event&code=eventlist&type=event&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_event_content_2').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_event_content_2').html(r);
					Cache.save('con_event_content_2', r);
				}
			}
		}
	);
}

/**
 * 获取我的参与的活动列表
 */
function getJoinEventList(page, options)
{
	var cache = Cache.get('con_event_content_3');
	if (!isUndefined(cache) && cache.length > 0) {
		$('#con_event_content_3').html(cache);
		return ;
	}
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=event&code=eventlist&type=join&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con_event_content_3').html(json.msg);
				} else {
					r = evalscript(r);
					$('#con_event_content_3').html(r);
					Cache.save('con_event_content_3', r);
				}
			}
		}
	);
}

/**
 * 获取我的分类列表
 */
function getMyFenleiList(page, options)
{
	if (isUndefined(options)) {
		options = {};
	}
	var dataUrl = 'ajax.php?mod=fenlei&code=fenleilist&page='+page;
	$.get(
		dataUrl,
		{},
		function(r) {
			if (options.response) {
				options.response.call();
			} else{
				if (is_json(r)) {
					var json = eval('('+r+')');
					$('#con2_vote2_content_2').html(json.msg);
				} else {
					$('#con2_vote2_content_2').html(r);
				}
			}
		}
	);
}

/**
 * 分类
 */
function getClassPost(){
	$.post(
	  "ajax.php?mod=fenlei&code=fenleipost",
	  function(d){
			$('#' + "con2_vote2_content_1").html(d);
		  },'json'
	)
}

//insert image or attach into content
function insertIntoContent(type,id,contentId){
    if('undefined' == typeof(type)){
        return false;
    }    
    var contentTextBox;
    if('undefined' == typeof(contentId)){
        contentTextBox = $('#'+__CONTENTID__);
    } else {
        contentTextBox = $('#'+contentId);
    }    
    var t = type;
    if('undefined'==typeof(id) || id < 1){
        return false;
    }
    var new_content = '';
    //当前发布框
    var content = contentTextBox.val();
    var r = "["+t+"]"+id+"[/"+t+"]";
    var n = content.split(r).length - 1;
    if (n>0) {
        for (var i = 0;i<n;i++) {
            content = content.replace(r,"");
        }
        new_content = content;
        contentTextBox.val(new_content);
    } else {
        contentTextBox.insertAtCaret(r,0);
    }
	if('image'==t){
		__IMAGE_IDS__[id] = id;
	}
    contentTextBox.focus();
}

function insertAT(){
    var contentTextBox = $('#'+__CONTENTID__);    
    contentTextBox.insertAtCaret("@",0);
    contentTextBox.keyup();
	$(".menu_m_c1").click();
}

function setFont(key,val){
    var k = key;
    if ('undefined' != typeof(val)) {
        key = key + '=' + val;
    }
	var post = $("#"+__CONTENTID__).getSelectContents();
    if(trim(post)){
        post = '['+ key +']'+post+'[/'+ k +']';
        $("#"+__CONTENTID__).insertAtCaret(post,0);
    }    
    $(".menu_a_c1").click();
}

function insertUrlIntoContent(str){
    $("#"+__CONTENTID__).insertAtCaret(str,0);
    $("#"+__CONTENTID__).focus();
    $(".menu_tqb").hide();
}

function getColorMenu(){
    var colorList = 
        ['Black','Sienna','DarkOliveGreen','DarkGreen',
        'DarkSlateBlue','Navy','Indigo','DarkSlateGray',
        
        'DarkRed','DarkOrange','Olive','Green',
        'Teal','Blue','SlateGray','DimGray',
        
        'Red','SandyBrown','YellowGreen','SeaGreen',
        'MediumTurquoise','RoyalBlue','Purple','Gray',
        
        'Magenta','Orange','Yellow','Lime',
        'Cyan','DeepSkyBlue','DarkOrchid','Silver',
        
        'Pink','Wheat','LemonChiffon','PaleGreen',
        'PaleTurquoise','LightBlue','Plum','White'
        ];
     var colorMenuList = '';
	 $(colorList).each(function(i){
		 colorMenuList += '<input type="button" onclick="setFont(\'color\',\''+colorList[i]+'\')" style="background-color: '+colorList[i]+';cursor: pointer;height: 12px;margin: 2px;float:left;">';
	 });
	 $("#color_menu").html(colorMenuList);
}

/**
 * 插入引用
 */
function insertQuote(quote,type){
    $(".menu_a_c1").click();
    quote = quote.value;
    if(quote.replace(/\ /g,"")) {
        quote = "["+type+"]"+quote+"[/"+type+"]";
        $("#"+__CONTENTID__).insertAtCaret(quote,0);
    }
    if(type=='quote'){
        $(".menu_q_b").hide();
    } else if (type=='code') {
        $(".menu_c_b").hide();
    } 
    return false;
}
/**
 * 插入私信引用
 */
function insertpmQuote(pmQuote,pmText){
    pmQuote = pmText;
    if(pmQuote.replace(/\ /g,"")) {
        pmQuote = "<em><i>引用：</i>"+pmQuote+"</em>";
        $("#"+__CONTENTID__).insertAtCaret(pmQuote,0);
    } 
 
    return false;
}

/**
 * 虾米音乐搜索
 */
function music_serach(){
   var music_name=$('#music_name'); 
   var pages=$('#page').val(); 
   if(pages == null || pages == ''){
	   pages = 1;
   }
		if(music_name.val()==''){
		  show_message('请输入需要搜索的歌名',2);
		  music_name.focus();
		  return false;
		}
	   //$('#music_search_tip').html('<span style="color:red"><img src="./images/loading.gif">正在检索，请稍等...</span>');
		$.post('ajax.php?mod=class&code=xiami',
			{name:music_name.val(),
			page:pages},
			function(d){
			if(''!=d){
				d= eval('('+d+')');
				var json=eval(d.results);
				if(json.length>0){
					var totle = d.total;
					var num = Math.ceil(totle/8);

					var phtml = page_html(pages,num,'music_serach');
					
					$('#music_list').html('<ul class="tagB" style="display:block;*width:300px;"><div id="add_ajax_favorite_tags" class="music_end"></div><div class="music_page">'+ phtml +'</div><span id="page"></span></ul>');
					
					for(var i=0; i<json.length; i++)  {
						json[i].song_name = decodeURIComponent(json[i].song_name).replace(/\+|'|"/g," ");
						json[i].artist_name = decodeURIComponent(json[i].artist_name).replace(/\+|'|"/g," ");
						var html='<span onclick="check_music('+json[i].song_id+',\''+decodeURI(json[i].album_logo)+'\',\''+decodeURI(json[i].artist_name)+'\',\''+decodeURI(json[i].song_name)+'\')" style="width:100%;cursor:pointer;" onmouseover="$(this).css(\'color\',\'#f60\');" onmouseout="$(this).css(\'color\',\'\');">'+decodeURI(json[i].song_name)+' ---  '+decodeURI(json[i].artist_name)+'</span>';
						$('.music_end').append(html);
					}
				}else{
					$('#music_list').html('<span style="padding:0 10px;">未搜到符合条件的歌曲</span>');
				}
			}else{
				$('#music_list').html('<span style="padding:0 10px;color:#e00">★关联网站暂不支持该操作！</span>');
			}
		 });
}

/**
 * 制作虾米音乐的简单HTML分页代码 page：当前页 num：总页数
 */
function page_html(page,num,func){
	page = parseInt(page);
	num = parseInt(num);	
	var html = '';
	if(num < 2){
		return '';
	}
	if(page > 1){
		var fpage = page-1;
		html += '<span onclick="changepage('+ fpage +',\''+func+'\');" style="cursor:pointer;">上一页</span>';
	}
	if(page < num){
		var npage = page+1;
		html += '<span onclick="changepage('+ npage +',\''+func+'\');" style="cursor:pointer;">下一页</span>';
	}
	return html;
}

function changepage(page,func){
	$('#page').val(page);
    if(func == 'music_serach'){
        music_serach();
    } else {
		$('#b_i_page').val(page);
        searchImgByBaiDu();
    }
}

/**
 * 选择音乐
 */
function check_music(music_id,p_url,name,music_name){
	$('#xiami_id').val(music_id);
	var content = music_name + '--' + name;
	var i_alerady_value =$('#i_already').val(); 
	$('#i_already').val(i_alerady_value+content);
	$(".menu_music").hide();
	$(".menu_xb").hide();
}

/**
 * 用百度搜索图片
 */
function searchImgByBaiDu(){
    var word = $("#word").val();
    if('undefined' == word || '' == word || 'undefined' == typeof(word)) {
        show_message("请输入要搜索的关键字。");
        return false;
    }
    var page = $("#b_i_page").val();
    if('undefined' == page || '' == page || 'undefined' == typeof(page)) {
        page = 0;
    }
    var per = 6;    
    $.post(
        'ajax.php?mod=uploadify&code=searchimage',
        {word:word,
         page:page,
         per:per},
         function (d) {
            d= eval('('+d+')');
            if(d.listNum>0){
                var totle = (d.listNum > 60 ? 60 : d.listNum);
                var num = Math.ceil(totle/per);
                var phtml = page_html(page,num);                
                var data = d.data;                
                $('#baidusearch_image_list').html('<ul class="tagB" style="display:block"><div id="add_ajax_favorite_tags" class="baidu_image_div"><ul class="baidu_image_ul"></ul></div><div class="baidu_img_page">'+ phtml +'</div><span id="b_i_page"></span></ul>');
                var html = '';
                for(var i=0; i<data.length; i++)  {
                    if('undefined' !== typeof(data[i].objURL)){
                    html = html + '<li class="menu_cs"><a href="javascript:;" onclick="insertUrlIntoContent(\''+data[i].objURL+'\');" title="点击插入"><img sr' + 'c' + '=' + '"' + data[i].objURL + '"/></a> <p class="vv_3"><i> &nbsp; </i></p></li>';
                    }
                }
                $('.baidu_image_ul').append(html);
            }else{
                $('#baidusearch_image_list').html('<li class="menu_cs"><span style="padding:0 10px;">未搜到符合条件的图片</span></li>');
            }
        }
    );
}