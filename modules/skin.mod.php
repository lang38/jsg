<?php
/**
 * 文件名：theme.mod.php
 * @version $Id: skin.mod.php 5422 2014-01-15 09:19:15Z chenxianfeng $
 * 作者：狐狸<foxis@qq.com>
 * 功能描述: 个人模板设置模块
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	var $Member;

	function ModuleObject($config)
	{
		$this->MasterObject($config);



		$this->TopicLogic = jlogic('topic');

		if (MEMBER_ID < 1)
		{
			$this->Messager("请先<a onclick='ShowLoginDialog(); return false;'>点此登录</a>或者<a onclick='ShowLoginDialog(1); return false;'>点此注册</a>一个帐号",null);
		}

		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`='".MEMBER_ID."'");
		$this->Member = $query->GetRow();
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch ($this->Code) {
			case 'modify':
				$this->Modify();
				break;
			case 'do_modify':
				$this->DoModify();
				break;

			default:
				$this->Code = 'theme';
				$this->Main();
		}
		$body=ob_get_clean();

		$this->ShowBody($body);

	}


	function Main()
	{
		$this->Modify();
	}

	function Modify()
	{
		$member = jsg_member_info(MEMBER_ID);
		$theme_id = $this->Member['theme_id'];
		$theme_bg_image = $this->Member['theme_bg_image'];
		$theme_bg_color = $this->Member['theme_bg_color'];
		$theme_text_color = $this->Member['theme_text_color'];
		$theme_link_color = $this->Member['theme_link_color'];
		$theme_bg_image_type = $this->Member['theme_bg_image_type'];
        $theme_bg_repeat = $this->Member['theme_bg_repeat'];
        $theme_bg_fixed = $this->Member['theme_bg_fixed'];

        $open_theme_list = jconf::get('theme');
        $themelist = $open_theme_list['theme_list'];

		$count = 0;
		foreach($themelist as $k=>$v)
		{
			$v['element'] = "{$v[theme_bg_color]},{$v[theme_text_color]},{$v[theme_link_color]},{$v[theme_id]},{$v[theme_bg_image_type]}";

			$themelist[$k] = $v;

			$count = $count + 1;
		}
						$prepage = 8;
				$page_count = ceil($count / $prepage);
				$page = 1;
		if ($count > $perpage) {
			$multi .= '<a href=\'javascript:;\' onclick=\'pre("pre");\'>上一页</a>';
			$multi .= '&nbsp;&nbsp;<a href=\'javascript:;\' onclick=\'pre("next");\'>下一页</a>';
		}

		$my_bg_image = RELATIVE_ROOT_PATH . 'images/theme/' . face_path(MEMBER_ID) . MEMBER_ID . '_o.jpg';
		if (is_file($my_bg_image))
		{
			$my_bg_image = $this->Config['site_url'] . "/" . $my_bg_image;
		}
		else
		{
			$my_bg_image = '';
		}

		$this->Title = "个人模板设置";
		include(template('topic_theme'));
	}

	function DoModify()
	{
		$field = 'theme';
		$image_id = MEMBER_ID;
		$theme_bg_image = str_replace($this->Config['site_url'].'/','',$this->Post['theme_bg_image']);

		$image_path = RELATIVE_ROOT_PATH . 'images/' . $field . '/' . face_path($image_id);
		$image_name = $image_id . "_o.jpg";
		$image_file = $image_path . $image_name;
		
		if ($_FILES && $_FILES[$field]['name'])
		{
			if (!is_dir($image_path))
			{
				jio()->MakeDir($image_path);
			}


			jupload()->init($image_path,$field,true);

			jupload()->setNewName($image_name);
			$result=jupload()->doUpload();

			if($result)
			{
				$result = is_image($image_file);
			}

			if (!$result)
			{
				jio()->DeleteFile($image_file);

				$this->Messager("[图片上载失败]".implode(" ",(array) jupload()->getError()),null);
			}
			else
			{
				$theme_bg_image = $image_file;
			}
		}
		else
		{
			if ($theme_bg_image!=$image_file)
			{
			}
		}

		$theme_id = $this->Post['theme_id'];
		$theme_bg_color = $this->Post['theme_bg_color'];
		$theme_text_color = $this->Post['theme_text_color'];
		$theme_link_color = $this->Post['theme_link_color'];
		$theme_bg_image_type = $this->Post['theme_bg_image_type'];
        $theme_bg_repeat = $this->Post['theme_bg_repeat'] ? 1 : 0;
        $theme_bg_fixed = $this->Post['theme_bg_fixed'] ? 1 : 0;

		$sql = "update ".TABLE_PREFIX."members set
			`theme_bg_image`='$theme_bg_image', `theme_bg_color`='$theme_bg_color', `theme_text_color`='$theme_text_color',
			`theme_link_color`='$theme_link_color' , theme_id='$theme_id' , theme_bg_image_type='$theme_bg_image_type' ,
			`theme_bg_repeat`='$theme_bg_repeat' , `theme_bg_fixed`='$theme_bg_fixed'
			where `uid`='".MEMBER_ID."'";
		$this->DatabaseHandler->Query($sql);


		
		if ('admin'==MEMBER_ROLE_TYPE && $this->Post['set_default'])
		{
			$config = array();
			$config['theme_id'] = $theme_id;
			$config['theme_bg_image'] = $theme_bg_image;
			$config['theme_bg_color'] = $theme_bg_color;
			$config['theme_text_color'] = $theme_text_color;
			$config['theme_link_color'] = $theme_link_color;
		    $config['theme_bg_image_type'] = $theme_bg_image_type;
		    $config['theme_bg_repeat'] = $theme_bg_repeat;
		    $config['theme_bg_fixed'] = $theme_bg_fixed;

			jconf::update($config);
		}


		
		$query = $this->DatabaseHandler->Query("select * from ".TABLE_PREFIX."members where `uid`='".MEMBER_ID."'");
		$this->_initTheme($query->GetRow());


		$this->Messager("设置成功",'index.php?mod=topic&code=myhome');
	}

}


?>
