<?php
/**
 * 文件名：income.mod.php
 * @version $Id: income.mod.php 5462 2014-01-18 01:12:59Z wuliyong $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述：广告模块管理
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $ID = 0;
	var $_config=array();
	var $configPath="";

	
	function ModuleObject($config)
	{
		$this->MasterObject($config);

						$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'ad':
				$this->addAD();
				break;
			case 'ad_list':
				$this->ADList();
				break;
			case 'doAdd':
				$this->DoAddADV();
				break;
			case 'dodeladv':
				$this->DoDelADV();
				break;

			case 'domodify':
				$this->DoModify();
				break;
			case 'google':
				$this->Google();
				break;
			case 'baidu':
				$this->Baidu();
				break;
			case 'aijuhe':
				$this->Aijuhe();
				break;
			case 'alimama':
				$this->Alimama();
				break;
			case 'vodone':
				$this->Vodone();
				break;
			case 'other':
				$this->Other();
				break;
			default:
				$this->Code = 'income_setting';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function AdLocationList(){
		$ad_location = array(
			'header' => array(
							'name'=>'顶部广告',
							'page'=>array(
                                'tag_view'=>'话题页',
								'group_new' => '微博广场',
								'channel' => '频道',
								'channel_home' => '频道首页',
                                'group_myhome' => '我的首页',
								'qun'=>'微群',
								'event'=>'活动',
								'vote'=>'投票'),
                                'width' => '980px'),


			'middle_center1' => array(
							'name' => '中间中部广告',
							'page'=>array(
                                'tag_view'=>'话题页',
								'group_new' => '微博广场',
                                'channel' => '频道',
                                'channel_home' => '频道首页',
								'group_myhome' => '我的首页'),
                                'width'=>'580px'),

			'middle_right_top' => array(
							'name' => '右侧上部广告',
							'page'=>array(
                                'tag_view'=>'话题页',
								'group_new' => '微博广场',
                                'channel' => '频道',
                                'channel_home' => '频道首页',
								'group_myhome' => '我的首页',
								'event'=>'活动'),
                                'width'=>'200px'),

			'middle_right' => array(
							'name' => '右侧下部广告',
							'page'=>array(
                                'topic_'=>'网站首页',
								'tag_view'=>'话题页',
								'group_new' => '微博广场',
                                'channel' => '频道',
                                'channel_home' => '频道首页',
								'group_myhome' => '我的首页',
								'qun'=>'微群',
								'event'=>'活动',
								'vote'=>'投票'),
                                'width'=>'200px'),

			'middle' => array(
							'name' => '消息页广告',
							'page'=>array('messager' => '消息提示页面的广告'),
							'width'=>'960px'),

			'footer' => array(
							'name' => '底部广告',
							'page'=>array(
                                'topic_'=>'网站首页',
								'tag_view'=>'话题页',
								'group_new' => '微博广场',
                                'channel' => '频道',
                                'channel_home' => '频道首页',
								'group_myhome' => '我的首页',
								'qun'=>'微群',
								'vote'=>'投票',
								'event'=>'活动'),
							'width'=>'980px')
		);
		return $ad_location;
	}

	
	function ADList(){
		$sql_where = '';
		$location = jget('op');
		$ad_location = $this->AdLocationList();
		if($location){
			$ad_location_name = $ad_location[$location]['name'];
			$sql_where = " where `location` = '$location'  ";
		}

		$count = DB::result_first("select count(*) from `".TABLE_PREFIX."ad` $sql_where ");

		$per_page_num = 20;
		$url = 'admin.php?mod=income&code=ad_list&op='.$location;

		$page_arr = page($count,$per_page_num,$url,array('return'=>'array',),'20 50 100 200 500');
		$sql = "select * from `".TABLE_PREFIX."ad` $sql_where order by adid desc $page_arr[limit] ";

		$ad_list = array();
		$query =  DB::query($sql);

		while ($rs = DB::fetch($query)) {
			$rs['location_name'] = $ad_location[$rs['location']]['name'];

			$rs['ftime'] = $rs['ftime'] ? date('Y-m-d',$rs['ftime']) : '不限制';
			$rs['ttime'] = $rs['ttime'] ? date('Y-m-d',$rs['ttime']) : '不限制';
			switch ($rs['type']) {
				case 3:
					$rs['type_name'] = '图片';
					break;
				case 2:
					$rs['type_name'] = '文字';
					break;
				case 1:
				default:
					$rs['type_name'] = '代码';
					break;
			}

			$hcode = unserialize(base64_decode($rs['hcode']));

			if ($hcode['page']) {
				$rs['page'] = implode(',',$hcode['page']);
				$rs['page'] = str_replace(array('topic_','tag_view','group_new','group_myhome','qun','event','vote','channel_home','channel'),array('网站首页','话题页','微博广场','我的首页','微群','活动','投票','频道首页','频道'),$rs['page']);
			}

			$ad_list[$rs['adid']] = $rs;
		}

		include template('admin/advertisement_list');
	}

	
	function addAD(){
		$location = jget('location');

		$AdLocationList = $this->AdLocationList();

		$adid = jget('adid','int');
		if($adid){
			$sql = "select * from `".TABLE_PREFIX."ad` where `adid` = '$adid' ";
			$ad_info = DB::fetch_first($sql);
			$location = $ad_info['location'];
			$ftime = date('Y-m-d H:i:s',$ad_info['ftime']);
			$ttime = date('Y-m-d H:i:s',$ad_info['ttime']);

			$hcode = unserialize(base64_decode($ad_info['hcode']));

			$hcode['html'] = $hcode['html'] ? stripslashes($hcode['html']) : '';

			if($hcode['page']){
				foreach ($hcode['page'] as $v) {
					$sel_checked[$v] = " checked ";
				}
			}
		}
		$ADLocationName = $AdLocationList[$location]['name'];
		$page_list = $AdLocationList[$location]['page'];

		include template('admin/advertisement_create');
	}

	function DoAddADV(){
		$html = '';
		$location = jget('location','trim');

		$adid = jget('adid','int');
		if($adid){
			$sql = "select * from `".TABLE_PREFIX."ad` where `adid` = '$adid' ";
			$ad_info = DB::fetch_first($sql);
			$ad_info || $this->Messager("您要修改的广告不存在或已删除。",-1);
		}

		($location || $ad_info['location']) || $this->Messager("这个是哪里的广告位？",'admin.php?mod=income');

		$title = jget('title','trim');
		if(!$title){
			$this->Messager("请输入广告标题",-1);
		}

		$hcode = jget('hcode');
		if(count($hcode['page']) < 1){
			$this->Messager("广告投放范围必须要有哦",-1);
		}

		$ftime = jget('ftime','trim');
		if($ftime){
			$ftime = strtotime($ftime);
		}

		$ttime = jget('ttime','trim');
		if($ttime){
			$ttime = strtotime($ttime);
		}

		$type = jget('type','int');
		switch ($type) {
			case 1:#代码
				if(!$hcode['html']){
					$this->Messager("广告HTML代码必须要有哦",-1);
				}
				$html = $hcode['html'];
				break;
			case 2:#文字
				if(!$hcode['word']){
					$this->Messager("文字内容必须要有哦",-1);
				}
				if(!$hcode['word_url']){
					$this->Messager("文字链接必须要有哦",-1);
				}
				if($hcode['word_size']) $word_size = 'style="font-size:'.$hcode['word_size'].'px"';
				$html = '<a href="'.$hcode['word_url'].'" target="_blank"><span '.$word_size.'>'.$hcode['word'].'</span></a>';
				break;
			case 3:#图片

		        


		        if($_FILES['image']['name']){
					$name = time().MEMBER_ID;
					$image_name = $name.".jpg";
					$image_path = RELATIVE_ROOT_PATH . 'images/ad/';
					$image_file = $image_path . $image_name;

					if (!is_dir($image_path))
					{
						jio()->MakeDir($image_path);
					}

					jupload()->init($image_path,'image',true);

					jupload()->setNewName($image_name);
					$result=jupload()->doUpload();

					if($result)
			        {
						$result = is_image($image_file);
					}
					if(!$result)
			        {
						unlink($image_file);
						$this->Messager("图片上传失败。",-1);
					}
					if($this->Config['ftp_on']) {
						$ftp_key = randgetftp();
						$get_ftps = jconf::get('ftp');
						$site_url = $get_ftps[$ftp_key]['attachurl'];
						$ftp_result = ftpcmd('upload',$image_file,'',$ftp_key);
						if($ftp_result > 0) {
							jio()->DeleteFile($image_file);
							$image_file = $site_url .'/'. str_replace('./','',$image_file);
						}
					}
					$hcode['image'] = $image_file;
		        } else {
		        	if(!$adid){
		        		$this->Messager("图片必须要有哦",-1);
		        	}else {
		        		$un_hcode = unserialize(base64_decode($ad_info['hcode']));
		        		$hcode['image'] = $un_hcode['image'];
		        	}
		        }

		        $hcode['pic_url'] || $this->Messager("图片链接必须要有哦",-1);
		        $image_width_html = $hcode['pic_width'] ? ' width='.$hcode['pic_width'] : '';
		        $image_height_html = $hcode['pic_height'] ? ' height= '.$hcode['pic_height'] : '';
		        $html = '<a href="'.$hcode[pic_url].'" target="_blank" title="'.$hcode['pic_title'].'"><img src="'.$hcode['image'].'" '.$image_width_html.$image_height_html. '></a>';
				break;
			default:
				$this->Messager("展现方式必须要有哦",-1);
				break;
		}

		$ser_hcode = base64_encode(serialize($hcode));

		#保存到数据库
		$data = array(
			'location' => $location,
			'title' => $title,
			'type' => $type,
			'ftime' => $ftime,
			'ttime' => $ttime,
			'hcode' => $ser_hcode,
		);
		if($adid){
			DB::update('ad',$data," `adid` = '$adid' ");
		}else {
			$adid = DB::insert('ad',$data,true);
		}
		#保存到本地
		$ad = jconf::get('ad');
		if($un_hcode['page']){
			foreach ($un_hcode['page'] as $k=>$v) {
				if(isset($ad['ad_list'][$v][$location][$adid]) && is_array($ad['ad_list'][$v][$location][$adid])) {
					unset($ad['ad_list'][$v][$location][$adid]);
				}
			}
		}

                        $AdLocationList = $this->AdLocationList();
        $ad_localtions = $AdLocationList[$location]['page'];
        if(isset($ad_localtions['width'])) {
        	unset($ad_localtions['width']);
        }
        $unset_ad_pages = array_diff(array_keys($ad_localtions),$hcode['page']);
        if(is_array($unset_ad_pages) && count($unset_ad_pages)>0) {
            foreach($unset_ad_pages as $page) {
                if(isset($ad['ad_list'][$page]) && isset($ad['ad_list'][$page][$location]) &&
                	isset($ad['ad_list'][$page][$location][$adid]) && is_array($ad['ad_list'][$page][$location][$adid]) &&
                	$ad['ad_list'][$page][$location][$adid]) {
                    unset($ad['ad_list'][$page][$location][$adid]);
                }
                if($ad['ad_list'][$page]) {
                	$ad['ad_list'][$page]=array_filter($ad['ad_list'][$page]);
                }
            }
                        $ad['ad_list']=array_filter($ad['ad_list']);
        }
        
		$ad['enable'] = 1;
		foreach ($hcode['page'] as $k => $v) {
            if(!is_array($ad['ad_list'][$v][$location])){
                $ad['ad_list'][$v][$location]=array();

            }
            if(!is_array($ad['ad_list'][$v][$location][$adid])){
                $ad['ad_list'][$v][$location][$adid]=array();
            }
			$ad['ad_list'][$v][$location][$adid]['html'] = $html;
			$ad['ad_list'][$v][$location][$adid]['ftime'] = $ftime;
			$ad['ad_list'][$v][$location][$adid]['ttime'] = $ttime;
		}

		jconf::set('ad',$ad);
		$this->Messager('设置成功','admin.php?mod=income&code=ad_list&op='.$location);
	}

	
	function DoDelADV(){
		$ids = jget('ids');

		if($ids && is_array($ids)){
			$ad = jconf::get('ad');

                        $ids_where = implode(',', $ids);
            DB::query("delete from `".TABLE_PREFIX."ad` where `adid` in ($ids_where) ");

            
            foreach($ad['ad_list'] as $k=>$page){
                foreach($page as $k_local=>$local){

                    foreach($local as $ad_id=>$ad_one){
                                                $ad_info = DB::fetch_first("select * from `".TABLE_PREFIX."ad` where `adid` = '$ad_id' ");
                        if(!$ad_info){
                            if(isset($ad['ad_list'][$k][$k_local][$ad_id]) && is_array($ad['ad_list'][$k][$k_local][$ad_id])) {
                            	unset($ad['ad_list'][$k][$k_local][$ad_id]);
                            }
                        }
                                                if(in_array($ad_id,$ids)){
                            if(isset($ad['ad_list'][$k][$k_local][$ad_id]) && is_array($ad['ad_list'][$k][$k_local][$ad_id])) {
                            	unset($ad['ad_list'][$k][$k_local][$ad_id]);
                            }
                        }

                    }
                    $ad['ad_list'][$k][$k_local]=array_filter($ad['ad_list'][$k][$k_local]);
                }
                $ad['ad_list'][$k]=array_filter($ad['ad_list'][$k]);
            }
            $ad['ad_list']=array_filter($ad['ad_list']);

			jconf::set('ad',$ad);
		}

		$this->Messager('删除成功','admin.php?mod=income&code=ad_list');
	}

	
	function Main()
	{
		
		$config = jconf::get();
		$enable_radio=$this->jishigou_form->YesNoRadio("enable",(int) $config['ad_enable']);

		$ad_location = $this->AdLocationList();

		include(template('admin/advertisement'));
	}

	function DoModify()
	{
		$enable = ($this->Post['enable'] ? 1 : 0);
        
        if($enable != $this->Config['ad_enable'])
        {
            jconf::update('ad_enable', $enable);
        }

		$this->Messager("修改成功");
	}

	function Google()
	{
		include(template('admin/income_google'));
		exit;
	}
	function Baidu()
	{
		include(template('admin/income_baidu'));
		exit;
	}
	function Other()
	{
		include(template('admin/income_other'));
		exit;
	}
	function Alimama()
	{
		include(template('admin/income_alimama'));
		exit;
	}
	function Vodone()
	{
		include(template('admin/income_vodone'));
		exit;
	}
	function Aijuhe()
	{
		include(template('admin/income_aijuhe'));
		exit;
	}

}
?>
