<?php
/**
 * 文件名：uploadify.mod.php
 * @version $Id: uploadify.mod.php 5556 2014-02-19 09:05:14Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 测试模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ImageLogic;

	var $Type;


	function ModuleObject($config)
	{
		$this->MasterObject($config);

		$this->ImageLogic = jlogic('image');

		$this->Execute();
	}

	function Execute()
	{
		switch($this->Code)
		{
            case 'searchimage':
                $this->SearchImageByBaiDu();
                break;
            case 'urlimage':
                $this->UrlImage();
                break;
			case 'image':
				$this->Image();
				break;
			case 'delete_image':
				$this->DeleteImage();
				break;

			case 'html':
				$this->Html();
				break;
			case 'album':
				$this->Album();
				break;
			case 'upimg':
				$this->Upimg();
				break;
			case 'addalbum':
				$this->Addalbum();
				break;
			case 'albumedit':
				$this->Albumedit();
				break;
			case 'delalbumimage':
				$this->Delalbumimage();
				break;

			default:
				$this->Main();
				break;
		}
	}

	function Main()
	{
		response_text('正在建设中……');
	}

	function Albumedit(){
		$type = jget('type');
		$id = jget('id');
		$album = jlogic('image')->getalbumbyid($type,$id,1);
		if($album){
			if('image' == $type){
				$albums = jlogic('image')->getalbum();
			}elseif('album' == $type){
				$html = '';
				$purview_values = array(0=>'所有用户',1=>'仅我关注的人',2=>'仅我的粉丝',3=>'仅我自己');
				foreach($purview_values as $k => $v){
					$selected = $album['purview'] == $k ? ' selected="selected"' : '';
					$html .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
				}
				$purview = '<select name="purview">'.$html.'</select>';
				$count = jlogic('image')->albumimgnums($id);
				if($count != $album['picnum']){
					jlogic('image')->update_album_picnum($id,$count);
				}
			}
			include template('modalbum');
		}else{
			response_text('<br><br><center><font color=red>对象不存在或您没有权限进行该操作！</font></center><br>');
		}
	}

	function Addalbum(){
		$name = jget('name', 'txt');
		$return = jlogic('image')->addalbum($name);
		response_text($return);
	}

	function Upimg(){
		$app = jget('app');
		$aid = jget('aid','int');
		$albums = jlogic('image')->getalbum();
		include template('upimg');
	}

	function Delalbumimage(){
		$type = jget('type');
		$id = jget('id');
		$return = jlogic('image')->delalbumimg($type,$id);
		response_text($return);
	}

	function Album(){
		$divid = jget('divid');
		$aid = jget('aid');
		$page = jget('page', 'int')>1 ? jget('page', 'int') : 1;
		$per = 3;
		$count = jlogic('image')->albumimgnums($aid);
		$num = ceil($count/$per);
		$albumimgs = jlogic('image')->getalbumimg($aid,$page,$per);
		if($albumimgs){
        $imgs = '';
		foreach($albumimgs as $rs){
			$rs['description'] = $rs['description'] ? $rs['description'] : '图片简介';
			$imgs .= "<tr><td valign='top'><input type='checkbox' name='idas[]' value='".$rs['id']."' title='选择该图片'></td><td valign='middle'><a href='javascript:void(0);' id='insert_image_".$rs['id']."' onclick=\"insertIntoContent('image',".$rs['id'].",'i_already".$divid."');\" title='点击插入'><img src='".($rs['site_url'] ? $rs['site_url'] : $this->Config['site_url']).'/'.str_replace('./','',str_replace('_o.jpg','_s.jpg',$rs['photo']))."' width='50' height='50' onerror='javascript:faceError(this);'></a></td><td valign='middle'><textarea name='title[".$rs['id']."]' maxlength='140' onfocus=\"if(this.value == '图片简介') {this.value = '';}\" onblur=\"if(this.value == '') {this.value = '图片简介';}\">".$rs['description']."</textarea></td></tr>";
		}
		if($count > $per){
			$imgs .= '<tr><td align="center" colspan="3">';
			if($page>1){
				$imgs .= '<a href="javascript:void(0);" onclick="get_album(\''.$divid.'\','.$aid.','.($page-1).');">上一页</a>';
			}
			if($page>1 && $num > $page){
				$imgs .= ' | ';
			}
			if($num > $page){
				$imgs .= '<a href="javascript:void(0);" onclick="get_album(\''.$divid.'\','.$aid.','.($page+1).');">下一页</a>';
			}
			$imgs .= '<i style="margin-left:20px;">'.$page.'/'.$num.'</i></td></tr>';
		}
		}else{
			$imgs = '<font color="red">此相册下暂无任何图片</font>';
		}
		response_text($imgs);
	}

	function Html()
	{
		$tid = max(0, (int) ($this->Post['tid'] ? $this->Post['tid'] : $this->Get['tid']));
		$image_uploadify_topic = array();
		if($tid > 0)
		{

			$TopicLogic = jlogic('topic');

			$image_uploadify_topic = $TopicLogic->Get($tid);
		}


		$from = (get_param('image_uploadify_from') ? get_param('image_uploadify_from') : get_param('from'));
		$image_uploadify_from = '';
		if('topic_publish' == $from)
		{
			$image_uploadify_from = $from;
		}


		$only_js = (get_param('image_uploadify_only_js') ? get_param('image_uploadify_only_js') : get_param('only_js'));
		$image_uploadify_only_js = 0;
		if($only_js)
		{
			$image_uploadify_only_js = 1;
		}


		$topic_uid = max(0, (int) (get_param('image_uploadify_topic_uid') ? get_param('image_uploadify_topic_uid') : get_param('topic_uid')));
		$image_uploadify_topic_uid = 0;
		if($topic_uid)
		{
			$image_uploadify_topic_uid = $image_uploadify_topic['uid'];
		}


		$image_small_size = max(0, (int) (get_param('image_uploadify_image_small_size') ? get_param('image_uploadify_image_small_size') : get_param('image_small_size')));
		$image_uploadify_image_small_size = 45;
		if($image_small_size)
		{
			$image_uploadify_image_small_size = $image_small_size;
		}


		$image_uploadify_new = (get_param('image_uploadify_new') ? get_param('image_uploadify_new') : get_param('new'));

		$image_uploadify_modify = (get_param('image_uploadify_modify') ? get_param('image_uploadify_modify') : get_param('modify'));

		$image_uploadify_type = (get_param('image_uploadify_type') ? get_param('image_uploadify_type') : get_param('type'));

		$content_textarea_id = (get_param('content_textarea_id') ? get_param('content_textarea_id') : get_param('content_id'));

		if(!is_null(get_param('content_textarea_empty_val')))
		{
			$content_textarea_empty_val = get_param('content_textarea_empty_val');
		}



		include(template('image_uploadify.inc'));
	}

	function Image() {
				$this->_init_auth();

		$item = $this->Get['iitem'];
		$itemid = max(0, (int)($this->Get['iitemid']));
		$p = array(
			'item' => $item,
			'itemid' => $itemid,
		);
		$rets = $this->ImageLogic->upload($p);
		if($rets['code']<0 && $rets['error']) {
			$this->_image_error($rets['error']);
		}
        if(jget('auto_topic')){
            			$this->auto_add_weibo($rets['id'],$item,$itemid);
		}

				$retval = array(
			'id' => $rets['id'],
			'src' => $rets['src'],
			'name' => $rets['name'],
		);
		$this->_image_result('ok',$retval);
	}

    
    function UrlImage(){
        if(MEMBER_ID < 1){
            $this->_image_error('你无权上传图片。');
        }
        $url = jget('url','url');
		$albumid = jget('aid','int');
        if(!$url){
            $this->_image_error('请上传正确的图片格式。');
        }

		$p = array(
			'pic_url' => $url,
			'albumid' => $albumid
		);
		$rets = jlogic('image')->upload($p);
		if($rets['code']<0 && $rets['error']) {
			$this->_image_error($rets['error']);
		}

				$retval = array(
			'id' => $rets['id'],
			'src' => $rets['src'],
			'name' => $rets['name'],
		);
		$this->_image_result('ok',$retval);
    }

	
    function SearchImageByBaiDu() {
    	$word = jget('word');
    	$word = get_safe_code($word);
    	if(!$word) {
    		exit('请输入搜索关键词');
    	}
    	$page = jget('page', 'int');
		$page = (($page > 0 && $page < 30) ? $page : 0);
		$per = jget('per','int');
        $per = (($per > 0 && $per < 30) ? $per : 6);
        if(false === ($data = cache_file('get', ($cache_id = "misc/image/search_by_baidu-".substr(md5($word), -16)."-{$per}-{$page}")))) {
        		        			$word = str_replace(' ', '+', $word);
			$url = "http:/"."/image.baidu.com/i?tn=baiduimagejson&ct=201326592&cl=2&lm=-1&st=-1&fm=result&fr=&sf=1&fmq=1349413075627_R&pv=&ic=0&nc=1&z=&se=1&showtab=0&fb=0&width=&height=&face=0&istype=2&word=$word&rn=$per&pn=" . $page * $per;
			$data = file_get_contents($url);

        	cache_file('set', $cache_id, $data, 86400);
        }
        		response_text($data);
    }

		function DeleteImage() {
		$id = jget('id', 'int');
		$tid = jpost('tid', 'int');

		$rets = jlogic('topic_image')->del($tid, $id);
		if(is_array($rets) && $rets['error']) {
			json_error($rets['result']);
		}

		json_result('删除成功');
	}

	function _init_auth()
	{
		$type = ($this->Post['type'] ? $this->Post['type'] : $this->Get['type']);
		$this->Type = $type;

		if('normal' == $type || 'normalnew' == $type || 'share_upload' == $type)
		{

		}
		else
		{
			$uid = 0;
			$password = '';
			$members = array();

			$cookie_auth = ($this->Post['cookie_auth'] ? $this->Post['cookie_auth'] : $this->Get['cookie_auth']);

			list($password,$uid) = ($cookie_auth ? explode("\t", authcode(str_replace(' ', '+', $cookie_auth), 'DECODE')) : array('', 0));

			if($uid > 0)
			{
				$members = DB::fetch_first("select `uid`, `username`, `nickname`, `role_type` from ".TABLE_PREFIX."members where `uid`='$uid'");
			}

			if(!$members)
			{
				json_error('auth is invalid');
			}
			else
			{
				$topic_uid = max(0, (int) ($this->Post['topic_uid'] ? $this->Post['topic_uid'] : $this->Get['topic_uid']));
				if($topic_uid > 0 && $topic_uid != $uid && 'admin'==$members['role_type'])
				{
					$members = DB::fetch_first("select `uid`, `username`, `nickname`, `role_type` from ".TABLE_PREFIX."members where `uid`='$topic_uid'");
				}

				define('MEMBER_ID', $members['uid']);
				define('MEMBER_NAME', $members['username']);
				define('MEMBER_NICKNAME', $members['nickname']);
				define('MEMBER_ROLE_TYPE', $members['role_type']);
			}
		}
	}

	function _image_error($msg)
	{
		if('normal' == $this->Type){
			echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');</script>";
			exit ;
		}elseif('normalnew' == $this->Type){
			$divid = jget('divid');
			echo "<script type='text/javascript'>window.parent.MessageBox('warning', '{$msg}');window.parent.imageUploadifyAllComplete('{$divid}');</script>";
			exit ;
		}elseif('share_upload' == $this->Type){
            echo "<script type='text/javascript'>window.parent.onComplete({done:0,msg:'{$msg}'});</script>";
            exit;
        }else{
			json_error($msg);
		}
	}
	function _image_result($msg, $retval=null)
	{
		if('normal' == $this->Type){
			$image_uploadify_id = ($this->Post['image_uploadify_id'] ? $this->Post['image_uploadify_id'] : $this->Get['image_uploadify_id']);
			echo "<script type='text/javascript'>window.parent.imageUploadifyComplete{$image_uploadify_id}('{$retval['id']}', '{$retval['src']}', '{$retval['name']}');window.parent.imageUploadifyAllComplete{$image_uploadify_id}();</script>";
			exit ;
		}elseif('normalnew' == $this->Type){
			$divid = jget('divid');
			echo "<script type='text/javascript'>window.parent.imageUploadifyComplete('{$divid}','{$retval['id']}', '{$retval['src']}', '{$retval['name']}');window.parent.imageUploadifyAllComplete('{$divid}');</script>";
			exit ;
        }elseif('share_upload' == $this->Type){
            echo "<script type='text/javascript'>window.parent.onComplete({done:1});</script>";
            exit;
        }else{
			json_result($msg, $retval);
		}
	}
     
     private function auto_add_weibo($id,$item='',$item_id=0){
        $weibo = "分享图片";
        if($item && $item_id){
            $topic = jlogic('topic')->Add($weibo,0,$id,0,'web','first',0,$item,$item_id);
        } else {
            $topic = jlogic('topic')->Add($weibo,0,$id);
        }
        $r = jlogic('attach')->modify(array('id' => $id,'tid' => $topic['tid'],));
        return $r;
    }

}

?>
