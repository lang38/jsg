<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename admin_left_menu.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-19 37961372 18772 $
 */

 $menu_list = array (
  1 => 
  array (
    'title' => '常用操作',
    'link' => 'admin.php?mod=index&code=home',
  ),
  2 => 
  array (
    'title' => '全局',
    'link' => 'admin.php?mod=index&code=home',
    'sub_menu_list' => 
    array (
      0 => 
      array (
        'title' => '<font color="#266AAE">核心设置</font>',
        'link' => 'admin.php?mod=setting&code=modify_normal',
        'shortcut' => true,
      ),
      1 => 
      array (
        'title' => '注册登陆',
        'link' => 'admin.php?mod=setting&code=modify_register',
        'shortcut' => true,
      ),
	  3 => 
      array (
        'title' => '积分规则',
        'link' => 'admin.php?mod=setting&code=modify_credits',
        'shortcut' => false,
      ),    
	  4 => 
      array (
        'title' => '积分等级',
        'link' => 'admin.php?mod=role&code=list&type=normal',
        'shortcut' => false,
      ),          
      5=> 
      array (
        'title' => '手机应用',
        'link' => 'admin.php?mod=setting&code=modify_mobile',
        'shortcut' => false,
      ),
	  6 => 
      array(
        'title' => '邮件发送',
        'link' => 'admin.php?mod=setting&code=modify_smtp',
        'shortcut' => false,
      ),
	  7 => 
      array (
        'title' => '图片附件',
        'link' => 'admin.php?mod=setting&code=modify_image',
        'shortcut' => false,
      ),
	
      9=> 
      array (
        'title' => '帐户绑定',
        'link' => 'admin.php?mod=setting&code=modify_sina',
        'shortcut' => false,
      ),   
	  10 => 
      array (
        'title' => '省市区域',
        'link' => 'admin.php?mod=city',
        'shortcut' => false,
      ), 	
	  11=> 
      array (
        'title' => '伪静态设置',
        'link' => 'admin.php?mod=setting&code=modify_rewrite',
        'shortcut' => false,
      ),
	  12=> 
	  array(
    		'title' => '防灌水验证码',
	    	'link' => 'admin.php?mod=setting&code=modify_seccode',
    		'shortcut' =>false,
		),
		15 => 
      array (
        'title' => '系统整合',
        'link' => 'hr',
        'shortcut' => false,
      ),  
	    13 => 
      array (
        'title' => '微博评论模块',
        'link' => 'admin.php?mod=output&code=output_setting',
        'shortcut' => true,
      ),  
	  14=>  
	  array(
    			'title' => '微博站外调用',
	    		'link' => 'admin.php?mod=share&code=share_setting',
				'shortcut' => false,
    	),	 
	   16 => 
      array (
        'title' => 'UCenter整合',
        'link' => 'admin.php?mod=ucenter&code=ucenter',
        'shortcut' => false,
      ),
	   array (
        'title' => '帖子同步发微博',
        'link' => 'admin.php?mod=setting&code=bbs_plugin',
        'shortcut' => false,
      ),
	   array (
        'title' => '调用Discuz',
        'link' => 'admin.php?mod=dzbbs&code=discuz_setting',
        'shortcut' => false,
      ),
	   array (
        'title' => '调用PhpWind',
        'link' => 'admin.php?mod=phpwind&code=phpwind_setting',
        'shortcut' => false,
      ),
	   array (
        'title' => '调用DedeCMS',
        'link' => 'admin.php?mod=dedecms&code=dedecms_setting',
        'shortcut' => false,
      ),
	  array (
        'title' => 'Windowns AD域',
        'link' => 'admin.php?mod=ldap&code=ldap_setting',
        'shortcut' => false,
      ),	  
    ),
  ),
  
  3 => 
  array (
    'title' => '界面',
    'link' => 'admin.php?mod=index&code=home',
    'sub_menu_list' => 
    array ( 
	
	  0 => 
      array (
        'title' => '页面显示',
        'link' => 'admin.php?mod=show&code=modify',
        'shortcut' => true,
      ),
1 => 
      array (
        'title' => '导航设置',
        'link' => 'admin.php?mod=navigation',
        'shortcut' => false,
      ),	  
	
	   2 => 
	   array(
                'title'=>'发布框设置',
                'link'=>'admin.php?mod=setting&code=topic_publish',
				'shortcut' => false,
		),
       3 => 
      array (
        'title' => '发布来源',
        'link' => 'admin.php?mod=setting&code=modify_topic_from',
        'shortcut' => false,
      ),
	   4=> 
      array (
        'title' => '文字替换',
        'link' => 'admin.php?mod=setting&code=changeword',
        'shortcut' => false,
      ),
	  5=> 
      array (
        'title' => '皮肤风格',
        'link' => 'admin.php?mod=show&code=modify_theme',
        'shortcut' => true,
      ),
      6=> 
      array (
        'title' => '模板风格',
        'link' => 'admin.php?mod=show&code=modify_template',
        'shortcut' => false,
      ),
	 7 => 
      array (
        'title' => '网站logo',
        'link' => 'admin.php?mod=show&code=editlogo',
        'shortcut' => false,
      ),
    ),
  ),
  4 => 
  array (
    'title' => '内容',
    'link' => '',
    'sub_menu_list' => 
    array (
      0 => 
      array (
        'title' => '微博管理',
        'link' => 'admin.php?mod=topic&code=topic_manage',
        'shortcut' => false,
      ),
	 
      1 => 
      array (
        'title' => '<font color="#266AAE">待审核微博</font>',
        'link' => 'admin.php?mod=topic&code=verify',
        'shortcut' => true,
      ),
	   5 => 
      array (
        'title' => '推荐微博',
        'link' => 'admin.php?mod=recdtopic',
        'shortcut' => false,
      ),
	     4 => 
      array (
        'title' => '微博举报',
        'link' => 'admin.php?mod=report',
        'shortcut' => true,
      ),
	  3 => 
      array (
        'title' => '内容过滤',
        'link' => 'admin.php?mod=setting&code=modify_filter',
        'shortcut' => true,
      ),
      2 => 
      array (
        'title' => '微博回收站',
        'link' => 'admin.php?mod=topic&code=del&del=1',
        'shortcut' => false,
      ),   
    
      6 => 
      array (
        'title' => '话题和专题',
        'link' => 'admin.php?mod=tag',
        'shortcut' => false,
      ),
	  28 => 
      array (
        'title' => '微博属性管理',
        'link' => 'admin.php?mod=feature',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '微博管理记录',
        'link' => 'admin.php?mod=topic&code=manage',
        'shortcut' => false,
      ), 
      15 =>
      array (
        'title' => 'URL链接管理',
        'link' => 'admin.php?mod=url&code=manage',
        'shortcut' => false,
      ), 
      8 => 
      array (
        'title' => '个人信息管理',
        'link' => 'hr',
        'shortcut' => false,
      ),
	  13 => 
      array (
        'title' => '私信管理',
        'link' => 'admin.php?mod=pm&code=pm_manage',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '签名管理',
        'link' => 'admin.php?mod=topic&code=signature',
        'shortcut' => false,
      ),    
      11 => 
      array (
        'title' => '自我介绍',
        'link' => 'admin.php?mod=topic&code=aboutme',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => '个人标签',
        'link' => 'admin.php?mod=user_tag',
        'shortcut' => false,
      ),
	    10 => 
      array (
        'title' => '头像签名审核',
        'link' => 'admin.php?mod=verify',
        'shortcut' => false,
      ),
    ),
  ),
  5 => 
  array (
    'title' => '用户',
    'link' => '',
    'sub_menu_list' => 
    array (
     0 => 
      array (
        'title' => '用户列表',
        'link' => 'admin.php?mod=member&code=newm',
        'shortcut' => true,
      ),     
	   1 => 
	    array (
			'title' => '用户绑定情况',
			'link' => 'admin.php?mod=account&code=index',
			 'shortcut' =>false,	
	   ),      
	  2 => 
	    array (
			'title' => '待验证用户',
			'link' => 'admin.php?mod=member&code=waitvalidate',
	  		'shortcut'=>false,	
	  ),
      3 => 
      array (
        'title' => '编辑用户',
        'link' => 'admin.php?mod=member&code=search',
        'shortcut' => true,
      ),
	  4 => 
      array (
        'title' => '添加新用户',
        'link' => 'admin.php?mod=member&code=add',
        'shortcut' => false,
      ),
	  5 => 
      array (
        'title' => '修改我的资料',
        'link' => 'admin.php?mod=member&code=modify',
        'shortcut' => false,
      ),
      6 => 
      array(
        'title' => '资料自定义',
        'link' => 'admin.php?mod=member&code=profile',
        'shortcut' => false,
      	),
	
      7 => 
      array (
        'title' => '<font color="#266AAE">用户V认证</font>',
        'link' => 'admin.php?mod=vipintro',
        'shortcut' => true,
      ),
    
	  8 => 
      array (
        'title' => '名人堂',
        'link' => 'admin.php?mod=vipintro&code=people_setting',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '推荐用户',
        'link' => 'admin.php?mod=media',
        'shortcut' => false,
      ),
	  10 => 
      array(
        'title' => '用户访问记录',
        'link' => 'admin.php?mod=member&code=login',
        'shortcut' => false,
      ),
      11 => 
      array (
        'title' => '导出用户到Excel',
        'link' => 'admin.php?mod=member&code=export_all_user',
        'shortcut' => false,
      ),
     
      12 => 
      array (
        'title' => '角色权限设置',
        'link' => 'hr',
        'shortcut' => false,
      ),
      13 => 
      array (
        'title' => '管理员角色',
        'link' => 'admin.php?mod=role&code=list&type=admin',
        'shortcut' => false,
      ),
      14 => 
      array (
        'title' => '普通用户角色',
        'link' => 'admin.php?mod=role&code=list&type=normal',
        'shortcut' => false,
      ),
      15=> 
      array (
        'title' => '添加用户角色',
        'link' => 'admin.php?mod=role&code=add',
        'shortcut' => false,
      ),
    ),
  ),
    6 => 
  array (
    'title' => '运营',
    'link' => '',
    'sub_menu_list' => 
    array (
  	0 => 
      array (
        'title' => '公告信息',
        'link' => 'admin.php?mod=notice',
        'shortcut' => false,
      ),
	 1=> 
      array (
        'title' => '广告管理',
        'link' => 'admin.php?mod=income',
        'shortcut' => true,
      ),
	
	   2=> 
      array (
        'title' => '首页幻灯',
        'link' => 'admin.php?mod=setting&code=modify_slide',
        'shortcut' => false,
      ),
	    3 => 
      array (
        'title' => '勋章管理',
        'link' => 'admin.php?mod=medal',
        'shortcut' => false,
      ),
	   4 => 
	    	array(
			'title' => '短信群发',
			'link' => 'admin.php?mod=sms&code=list',
			 'shortcut' => false,
		),
	    6=> 
      array (
        'title' => '关键词设置',
        'link' => 'admin.php?mod=setting&code=modify_meta',
        'shortcut' => false,
      ),
	   7 => 
      array (
        'title' => '友情链接',
        'link' => 'admin.php?mod=link',
        'shortcut' => false,
       ),
	    8=> 
      array (
        'title' => '运营工具',
        'link' => 'hr',
        'shortcut' => false,
      ),
	   9 => 
      array (
        'title' => '蜘蛛爬行统计',
        'link' => 'admin.php?mod=robot',
        'shortcut' => false,
      ),
	   10 => 
      array (
        'title' => '友情链接检测',
        'link' => 'http://checklink.biniu.com',
        'shortcut' => false,
      ),
	   11 => 
      array (
        'title' => '反向链接分析',
        'link' => 'http://backlink.biniu.com',
        'shortcut' => false,
      ),
	  12 => 
      array (
        'title' => '收录查询',
        'link' => 'http://shoulu.biniu.com',
        'shortcut' => false,
      ),
	   13 => 
      array (
        'title' => '关键词排名',
        'link' => 'http://keyword.biniu.com',
        'shortcut' => false,
      ),
      14 => 
      array (
        'title' => 'alexa排名',
        'link' => 'http://alexa.biniu.com',
        'shortcut' => false,
      ),   
      15 => 
      array (
        'title' => '同IP网站监测',
        'link' => 'http://cnrdn.com/G8f4',
        'shortcut' => false,
      ),  
    ),
  ),
  7 => 
  array (
    'title' => '应用',
    'link' => '',
    'sub_menu_list' => 
    array (
	  1=> 
      array (
        'title' => '<font color="#266AAE">频道</font>',
        'link' => 'admin.php?mod=channel&code=index',
        'shortcut' => true,
      ),
      2 => 
      array (
        'title' => '微群',
        'link' => 'admin.php?mod=qun',
        'shortcut' => false,
      ),
      3 => 
      array (
        'title' => '投票',
        'link' => 'admin.php?mod=vote&code=index',
        'shortcut' => false,
      ),     
      4 => 
      array (
        'title' => '活动',
        'link' => 'admin.php?mod=event&code=manage',
        'shortcut' => false,
      ),
	  5 => 
      array (
        'title' => '签到',
        'link' => 'admin.php?mod=sign',
        'shortcut' => false,
      ),
      /*
      9 => array(
        'title' => '版块',
        'link' => 'admin.php?mod=block',
        'shortcut' => false,
      ),
      10 => array(
      	'title' => '分类',
      	'link' => 'admin.php?mod=fenlei',
      	'shortcut' => false,
      ),*/
     
     
      6 => 
      array (
        'title' => '微直播',
        'link' => 'admin.php?mod=live&code=index',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '微访谈',
        'link' => 'admin.php?mod=talk&code=index',
        'shortcut' => false,
      ),
	  8 => array(
      	'title' => '有奖转发',
      	'link' => 'admin.php?mod=reward',
      	'shortcut' => false,
      ),
	  25 => 
      array (
        'title' => '微信公共平台',
        'link' => 'admin.php?mod=wechat',
        'shortcut' => false,
        'type' => '1',
      ),
	  27 => 
      array (
        'title' => '积分商城',
        'link' => 'admin.php?mod=mall&code=goods_list',
        'shortcut' => false,
        'type' => '1',
      ),
	  26 => 
      array (
        'title' => '资讯管理',
        'link' => 'admin.php?mod=cms',
        'shortcut' => false,
        'type' => '1',
      ),
	   9 => 
      array(
        'title' => '马甲管理',
        'link' => 'admin.php?mod=member&code=vest',
        'shortcut' => true,
      ),
   
	 11 => 
      array (
        'title' => '<font color="#266AAE">单位和部门</font>',
        'link' => 'admin.php?mod=company',
        'shortcut' => false,
      ),
      12 => 
      array (
        'title' => 'API应用授权',
        'link' => 'admin.php?mod=api',
        'shortcut' => false,
      ),      
      13 => 
      array (
        'title' => '微博秀',
        'link' => 'admin.php?mod=tools&code=weibo_show',
        'shortcut' => false,
      ),
	  19 => 
      array (
        'title' => '动态提醒',
        'link' => 'admin.php?mod=feed',
        'shortcut' => false,
      ),   
      20 => 
      array (
        'title' => '插件',
        'link' => 'hr',
        'shortcut' => false,
      ),
      21 => 
      array (
        'title' => '已安装插件',
        'link' => 'admin.php?mod=plugin',
        'shortcut' => false,
      ),
      22 => 
      array (
        'title' => '安装新插件',
        'link' => 'admin.php?mod=plugin&code=add',
        'shortcut' => false,
      ),
      23 => 
      array (
        'title' => '插件设计',
        'link' => 'admin.php?mod=plugin&code=design',
        'shortcut' => false,
        'type' => '1',
      ),
	  24 => 
      array (
        'title' => '设计中插件',
        'link' => 'admin.php?mod=plugin&code=designing',
        'shortcut' => false,
        'type' => '1',
      ),
    ),
  ),
 8 => 
  array (
    'title' => '工具',
    'link' => '',
    'sub_menu_list' => 
    array (
	   1 => 
      array (
        'title' => '清空缓存',
        'link' => 'admin.php?mod=cache',
        'shortcut' => false,
      ),
       2 => 
      array (
        'title' => '在线升级',
        'link' => 'admin.php?mod=upgrade',
        'shortcut' => false,
      ),	
      3 => 
      array (
        'title' => '<font color="#D94446">后台操作记录</font>',
        'link' => 'admin.php?mod=logs',
        'shortcut' => true,
      ),
	   4=> 
      array (
        'title' => '数据库管理',
        'link' => 'hr',
        'shortcut' => false,
      ),
      5 => 
      array (
        'title' => '数据备份',
        'link' => 'admin.php?mod=db&code=export',
        'shortcut' => false,
      ),
      6 => 
      array (
        'title' => '数据恢复',
        'link' => 'admin.php?mod=db&code=import',
        'shortcut' => false,
      ),
      7 => 
      array (
        'title' => '数据表优化',
        'link' => 'admin.php?mod=db&code=optimize',
        'shortcut' => false,
      ),
    ),
  ),
  9 => 
  array (
    'title' => '帮助',
    'link' => '',
    'sub_menu_list' => 
    array (
	   1 => 
      array (
        'title' => '安装使用',
        'link' => 'http://t.jishigou.net/channel/id-5',
        'shortcut' => false,
      ),
	    2 => 
      array (
        'title' => 'bug反馈',
        'link' => 'http://t.jishigou.net/channel/id-6',
        'shortcut' => false,
      ),
	    3 => 
      array (
        'title' => '风格模板',
        'link' => 'http://t.jishigou.net/channel/id-7',
        'shortcut' => false,
      ),
	    4 => 
      array (
        'title' => '发展建议',
        'link' => 'http://t.jishigou.net/channel/id-8',
        'shortcut' => false,
      ),
	    5 => 
      array (
        'title' => '二次开发',
        'link' => 'http://t.jishigou.net/channel/id-9',
        'shortcut' => false,
      ),
	    6 => 
      array (
        'title' => '经验分享',
        'link' => 'http://t.jishigou.net/channel/id-18',
        'shortcut' => false,
      ),
	    7 => 
      array (
        'title' => 'android客户端',
        'link' => 'http://t.jishigou.net/channel/id-22',
        'shortcut' => false,
      ),
	   8 => 
      array (
        'title' => 'iphone客户端',
        'link' => 'http://t.jishigou.net/channel/id-23',
        'shortcut' => false,
      ),
      9 => 
      array (
        'title' => '商业授权',
        'link' => 'http://cnrdn.com/15f4',
        'shortcut' => false,
      ),
       10 => 
      array (
        'title' => '最新版微博体验',
        'link' => 'http://cnrdn.com/35f4',
        'shortcut' => false,
      ),
    ),
  ),
); ?>