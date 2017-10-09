<?php
/**
 * 后台管理页面子菜单
 *
 * 直接在此归类和添加就可以，不用再在程序中设置了 By foxis 2012.03.06
 *
 * @version $Id: admin_page_menu.php 5466 2014-01-18 02:44:57Z chenxianfeng $
 */

$menu_list = array(
	'qun' => array(
		array(
			'name' => '基本设置',
			'link' => 'admin.php?mod=qun&code=setting',
		),
		array(
			'name' => '微群分类',
			'link' => 'admin.php?mod=qun&code=category',
		),
		/*
		array(
			'name' => '微群等级',
			'link' => 'admin.php?mod=qun&code=level',
		),*/
		array(
			'name' => '微群策略',
			'link' => 'admin.php?mod=qun&code=ploy',
		),
		array(
			'name' => '微群管理',
			'link' => 'admin.php?mod=qun&code=manage',
		),
		array(
			'name' => '创建微群',
			'link' => 'admin.php?mod=qun&code=add',
		),
		array(
			'name' => '添加群模板',
			'link' => 'admin.php?mod=qun&code=module',
		),
	),

	'sms' => array(
		array(
    			'name' => '手机应用设置',
	    		'link' => 'admin.php?mod=setting&code=modify_mobile',
    		),
    	/*
		array(
			'name' => '手机短信设置',
			'link' => 'admin.php?mod=sms&code=setting',
		),
		array(
			'name' => '短信群发',
			'link' => 'admin.php?mod=sms&code=list',
		),
		array(
			'name' => '发送记录列表',
			'link' => 'admin.php?mod=sms&code=send_log',
		),
		array(
			'name' => '接收记录列表',
			'link' => 'admin.php?mod=sms&code=receive_log',
		),
		*/
	),

	'tag' => array(
		array(
			'name' => '话题管理',
			'link' => 'admin.php?mod=tag&code=list',
		),
		array(
			'name' => '话题推荐',
			'link' => 'admin.php?mod=tag&code=recommend',
		),
		array(
			'name' => '话题专题',
			'link' => 'admin.php?mod=tag&code=extra',
		),
	),

	'plugin' => array(
		array(
			'name' => '已安装插件',
			'link' => 'admin.php?mod=plugin',
		),
		array(
			'name' => '安装新插件',
			'link' => 'admin.php?mod=plugin&code=add',
		),
		array(
			'name' => '插件设计',
			'link' => 'admin.php?mod=plugin&code=design',
			'type' => '1',
		),
		array(
			'name' => '设计中插件',
			'link' => 'admin.php?mod=plugin&code=designing',
			'type' => '1',
		),
	),

	'plugindesign' => array(
		array(
			'name' => '插件列表',
			'link' => 'admin.php?mod=plugin',
		),
		array(
			'name' => '设置',
			'link' => 'admin.php?mod=plugindesign&code=design',
		),
		array(
			'name' => '模块',
			'link' => 'admin.php?mod=plugindesign&code=modules',
		),
		array(
			'name' => '变量',
			'link' => 'admin.php?mod=plugindesign&code=vars',
		),
		array(
			'name' => '导出',
			'link' => 'admin.php?mod=plugindesign&code=export',
		),
	),

	'vote' => array(
		array(
			'name' => '投票设置',
			'link' => 'admin.php?mod=vote&code=setting',
		),
		array(
			'name' => '投票管理',
			'link' => 'admin.php?mod=vote&code=index',
		),
		array(
			'name' => '投票审核',
			'link' => 'admin.php?mod=vote&code=verify',
		),
	),
	'event' => array(
		array(
			'name' => '活动设置',
			'link' => 'admin.php?mod=event&code=setting',
		),
		array(
			'name' => '活动主题',
			'link' => 'admin.php?mod=event&code=index',
		),
		array(
			'name' => '可选必填项',
			'link' => 'admin.php?mod=event&code=profile',
		),
		array(
			'name' => '活动管理',
			'link' => 'admin.php?mod=event&code=manage',
		),
		array(
			'name' => '活动审核',
			'link' => 'admin.php?mod=event&code=verify',
		),
	),

	'live' => array(
		array(
			'name' => '直播配置',
			'link' => 'admin.php?mod=live&code=config',
		),
		array(
			'name' => '添加直播',
			'link' => 'admin.php?mod=live&code=add',
		),
		array(
			'name' => '直播管理',
			'link' => 'admin.php?mod=live&code=index',
		),
	),

	'talk' => array(
		array(
			'name' => '访谈配置',
			'link' => 'admin.php?mod=talk&code=config',
		),
		array(
			'name' => '访谈分类',
			'link' => 'admin.php?mod=talk&code=category',
		),
		array(
			'name' => '添加访谈',
			'link' => 'admin.php?mod=talk&code=add',
		),
		array(
			'name' => '访谈管理',
			'link' => 'admin.php?mod=talk&code=index',
		),
	),

	//* //已暂时关闭
	'account' => array(
		array(
			'name' => '用户绑定情况',
			'link' => 'admin.php?mod=account&code=index',
		),
		array(
			'name' => 'YY帐户设置',
			'link' => 'admin.php?mod=account&code=yy',
		),
		array(
			'name' => '人人帐户设置',
			'link' => 'admin.php?mod=account&code=renren',
		),
		array(
			'name' => '开心帐户设置',
			'link' => 'admin.php?mod=account&code=kaixin',
		),
		/*
		array(
			'name' => 'FJAU设置',
			'link' => 'admin.php?mod=account&code=fjau',
		),
		*/
		array(
			'name' => '新浪微博绑定',
			'link' => 'admin.php?mod=setting&code=modify_sina',
		),
		array(
			'name' => '腾讯微博绑定',
			'link' => 'admin.php?mod=setting&code=modify_qqwb',
		),
		/*
		array(
			'name' => 'QQ机器人绑定',
			'link' => 'admin.php?mod=imjiqiren&code=imjiqiren_setting',
		),
		*/
	),


	//子菜单 注册和访问
	'_setting_access' => array(
    		array(
    			'name' => '用户注册控制',
	    		'link' => 'admin.php?mod=setting&code=modify_register',
    		),
			array(
    			'name' => '注册引导步骤',
	    		'link' => '	admin.php?mod=setting&code=modify_register_guide',
    		),
    		array(
    			'name' => '全局IP访问控制',
	    		'link' => 'admin.php?mod=setting&code=modify_access',
    		),
    		array(
    			'name' => '用户IP登录限制',
	    		'link' => 'admin.php?mod=failedlogins&code=index',
    		),
   			array(
    			'name' => '自动关注推荐',
	    		'link' => 'admin.php?mod=setting&code=regfollow',
    		),
    		array(
    			'name' => '默认关注分组',
	    		'link' => 'admin.php?mod=setting&code=follow',
    		),
    		array(
    			'name' => '邀请注册相关',
	    		'link' => 'admin.php?mod=setting&code=invite',
    		),

    	),
	'sign' => array(
		array(
			'name' => '签到设置',
			'link' => 'admin.php?mod=sign&code=index',
		),
		array(
			'name' => '签到积分排行',
			'link' => 'admin.php?mod=sign&code=sign_list',
		),
	),

    //子菜单 积分相关
	'_setting_credit' => array(
    		array(
    			'name' => '积分项目设置',
	    		'link' => 'admin.php?mod=setting&code=modify_credits',
    		),
    		array(
    			'name' => '积分规则设置',
	    		'link' => 'admin.php?mod=setting&code=list_credits_rule',
    		),
			/*
			array(
    			'name' => '管理员等级设置',
	    		'link' => 'admin.php?mod=role&code=list&type=admin',
    		),
    		array(
    			'name' => '积分等级设置',
	    		'link' => 'admin.php?mod=role&code=list&type=normal',
    		),

			*/
    		array(
    			'name' => '查看积分排行',
	    		'link' => 'admin.php?mod=sign&code=credits_top',
    		),
    	),
    //子菜单 SEO相关
	'_setting_seo' => array(
    		array(
    			'name' => 'URL伪静态',
	    		'link' => 'admin.php?mod=setting&code=modify_rewrite',
    		),
    		array(
    			'name' => '关键词设置',
	    		'link' => 'admin.php?mod=setting&code=modify_meta',
    		),
    	),
    //子菜单 网站设置
	'_setting_normal' => array(
    		array(
    			'name' => '核心设置',
	    		'link' => 'admin.php?mod=setting&code=modify_normal',
    		),
			array(
    			'name' => '站点状态',
	    		'link' => 'admin.php?mod=setting&code=visit_state',
    		),
			array(
    			'name' => '审核开关',
	    		'link' => 'admin.php?mod=setting&code=check_switch',
    		),
			array (
        		'name' => '系统负载',
        		'link' => 'admin.php?mod=setting&code=modify_sysload',
             ),

    	),

		//子菜单 内容设置
   		 '_setting_content' => array(
			array(
    			'name' => '首页公告',
	    		'link' => 'admin.php?mod=notice',
    		),
    		array(
    			'name' => '关于我们',
	    		'link' => 'admin.php?mod=web_info',
    		),
    	),

		//子菜单 邮件发送设置
		'_setting_email' => array(
		array(
    			'name' => '邮件发送设置',
	    		'link' => 'admin.php?mod=setting&code=modify_smtp',
    		),
		array(
      		  'name' => '邮件队列',
      		  'link' => 'admin.php?mod=notice&code=mailq',
  		    ),
		),


		//子菜单 附件文档
	   '_setting_attachment' => array(
	       	array(
    			'name' => '图片上传',
	    		'link' => 'admin.php?mod=setting&code=modify_image',
    		),

    		array(
    			'name' => '图片签名档',
	    		'link' => 'admin.php?mod=setting&code=modify_qmd',
    		),

	    array (
      		  'name' => '附件文档',
        	  'link' => 'admin.php?mod=attach',
            ),
		array(
    			'name' => '远程附件',
	    		'link' => 'admin.php?mod=setting&code=modify_ftp',
    		),
		),
		/*
    //子菜单 站点功能
    '_setting_function' => array(
    		array(
    			'name' => 'UCenter整合',
	    		'link' => 'admin.php?mod=ucenter&code=ucenter',
    		),
			array(
				'name' => '调用Discuz!论坛',
				'link' => 'admin.php?mod=dzbbs&code=discuz_setting',
				),
			array(
    			'name' => '论坛帖子同步发微博',
	    		'link' => 'admin.php?mod=setting&code=bbs_plugin',
    		),
			array(
				'name' => '调用DedeCMS文章',
				'link' => 'admin.php?mod=dedecms&code=dedecms_setting',
				),
			array(
				'name' => '整合调用PhpWind',
				'link' => 'admin.php?mod=phpwind&code=phpwind_setting',
				),
    	),
		*/
	//子菜单 首页幻灯
	'_setting_slide' => array(
    		array(
    			'name' => '我的首页幻灯',
	    		'link' => 'admin.php?mod=setting&code=modify_slide',
    		),
    		array(
    			'name' => '网站首页幻灯',
	    		'link' => 'admin.php?mod=setting&code=modify_slide_index',
    		),
		),
	//子菜单 广告管理
	'_setting_ad' => array(
    		array(
    			'name' => '广告位',
	    		'link' => 'admin.php?mod=income',
    		),
    		array(
    			'name' => '所有广告',
	    		'link' => 'admin.php?mod=income&code=ad_list',
    		),
		),

    //子菜单 私信管理
    '_manage_pm' => array(
    		array(
    			'name' => '私信管理',
	    		'link' => 'admin.php?mod=pm&code=pm_manage',
    		),
    		array(
    			'name' => '私信群发',
	    		'link' => 'admin.php?mod=pm&code=pmsend',
    		),
    		array(
    			'name' => '给管理员的信',
	    		'link' => 'admin.php?mod=pm&code=to_admin',
    		),
    	),
		/*
    //子菜单 个人信息管理
    '_manage_userinfo' => array(
    		array(
    			'name' => '签名管理',
	    		'link' => 'admin.php?mod=topic&code=signature',
    		),
    		array(
    			'name' => '头像签名审核',
	    		'link' => 'admin.php?mod=verify&code=fs_verify',
    		),
			array(
    			'name' => '自我介绍管理',
	    		'link' => 'admin.php?mod=topic&code=aboutme',
    		),
    		array(
    			'name' => '个人标签管理',
	    		'link' => 'admin.php?mod=user_tag&code=user_tag_manage',
    		),
      	),
		*/
		/*
    //子菜单 内容管理
    '_manage_content' => array(
    		array(
    			'name' => '微博管理',
	    		'link' => 'admin.php?mod=topic&code=topic_manage',
    		),
    		array(
    			'name' => '待审核微博',
	    		'link' => 'admin.php?mod=topic&code=verify',
    		),
			array(
    			'name' => '官方推荐微博',
	    		'link' => 'admin.php?mod=recdtopic&code=recdtopic_manage',
    		),
			array(
    			'name' => '微博举报管理',
	    		'link' => 'admin.php?mod=report&code=report_manage',
    		),
    		array(
    			'name' => '内容过滤设置',
	    		'link' => 'admin.php?mod=setting&code=modify_filter',
    		),
    		array(
    			'name' => '微博回收站',
	    		'link' => 'admin.php?mod=topic&code=del&del=1',
    		),
      	),
		*/
		/*
    //子菜单 系统工具
	  '_tool_system' => array(
    		array(
    			'name' => '清空系统缓存',
	    		'link' => 'admin.php?mod=cache',
    		),
    		array(
    			'name' => '系统在线升级',
	    		'link' => 'admin.php?mod=upgrade',
    		),
    		 array (
        		'name' => '后台操作记录',
        		'link' => 'admin.php?mod=logs',
        	),
    	),
		*/
		/*
    '_tool_seotools' => array(
		array(
    			'name' => '蜘蛛爬行统计',
	    		'link' => 'admin.php?mod=robot',
    		),
		 array (
        		'name' => '友情链接检测',
        		'link' => 'http://checklink.biniu.com',
           	),
		 array (
        		'name' => '反向链接分析',
        		'link' => 'http://backlink.biniu.com',
        	),
		 array (
       			 'name' => '收录查询',
        		 'link' => 'http://shoulu.biniu.com',
            ),
		 array (
        		'name' => '关键词排名',
        		'link' => 'http://keyword.biniu.com',
              ),
		 array (
        		'name' => 'alxea排名',
        		'link' => 'http://keyword.biniu.com',
              ),
    	 array(
    			'name' => '同Ip网站监测',
	    		'link' => 'http://cnrdn.com/G8f4',
    		),

    	),
		*/
    //子菜单 用户管理
	/*
    '_manage_member' => array(
    		array(
    			'name' => '编辑用户',
	    		'link' => 'admin.php?mod=member&code=search',
    		),
			  array (
				'name' => '添加新用户',
				'link' => 'admin.php?mod=member&code=add',
			  ),
			  array (
				'name' => '修改我的资料',
				'link' => 'admin.php?mod=member&code=modify',
			  ),
    	),
		*/
    //子菜单 用户管理
    '_member_list' => array(
    		array(
    			'name' => '用户列表',
	    		'link' => 'admin.php?mod=member&code=newm',
    		),
			  array (
				'name' => '封杀用户列表',
				'link' => 'admin.php?mod=member&code=force_out',
			  ),
			  array (
				'name' => '上报领导列表',
				'link' => 'admin.php?mod=member&code=leaderlist',
			  ),
    	),
	  //子菜单 用户访问记录
    '_member_loginsessions' => array(
			  array(
				'name' => '用户访问记录',
				'link' => 'admin.php?mod=member&code=login',
			  ),
			    array (
				'name' => '当前在线用户',
				'link' => 'admin.php?mod=sessions',
			  ),
			    array (
				'name' => '用户登录日志',
				'link' => 'admin.php?mod=member&code=user_login_log',
			  ),
    	),
    //子菜单 V认证
    '_validate' => array(
    		array(
    			'name' => '用户V认证',
	    		'link' => 'admin.php?mod=vipintro',
    		),
    		array(
    			'name' => 'V认证条件',
	    		'link' => 'admin.php?mod=vipintro&code=setting',
    		),
    		array(
    			'name' => 'V认证设置',
	    		'link' => 'admin.php?mod=vipintro&code=validate_setting',
    		),
    		array(
    			'name' => 'V认证类别',
	    		'link' => 'admin.php?mod=vipintro&code=categorylist',
    		),
    		array(
    			'name' => '手动添加认证',
	    		'link' => 'admin.php?mod=vipintro&code=addvip',
    		),
			/*
			array(
    			'name' => '名人堂',
	    		'link' => 'admin.php?mod=vipintro&code=people_setting',
    		),
			 array (
        		'name' => '推荐用户',
        		'link' => 'admin.php?mod=media',
       		),
			*/
    	),
		//子菜单 导航设置
		 '_setting_navigation' => array(
    		array(
    			'name' => '顶部和左侧导航菜单',
	    		'link' => 'admin.php?mod=navigation',
    		),
			/*
			array(
    			'name' => '左侧导航菜单',
	    		'link' => 'admin.php?mod=navigation&code=left_navigation',
    		),
			*/
			array(
    			'name' => '底部导航菜单',
	    		'link' => 'admin.php?mod=navigation&code=footer_navigation',
    		),
		),

    //子菜单 显示管理
    '_setting_show' => array(
    		array(
    			'name' => '页面显示设置',
	    		'link' => 'admin.php?mod=show&code=modify',
    		),
			/*
			 array(
                'name'=>'发布框设置',
                'link'=>'admin.php?mod=setting&code=topic_publish',
            ),
			array(
    			'name' => '发布来源设置',
	    		'link' => 'admin.php?mod=setting&code=modify_topic_from',
    		),
			 array(
                'name' => '前台文字替换',
                'link'=>'admin.php?mod=setting&code=changeword',
            ),
    	*/
    	),
		/*
		 //子菜单 网站logo，友情链接
    '_setting_logolink' => array(
       		array(
    			'name' => '网站logo',
	    		'link' => 'admin.php?mod=show&code=editlogo',
    		),
			array(
    			'name' => '友情链接',
	    		'link' => 'admin.php?mod=link',
    		),
    	),
		*/
		//子菜单 皮肤风格、模板风格设置
	  '_setting_theme' => array(
    		array(
    			'name' => '皮肤风格设置',
	    		'link' => 'admin.php?mod=show&code=modify_theme',
    		),
    		array(
    			'name' => '模板风格设置',
	    		'link' => 'admin.php?mod=show&code=modify_template',
    		),
    	),
    //子菜单 站外调用
    '_setting_share' => array(
    		array(
    			'name' => '微博站外展示调用',
	    		'link' => 'admin.php?mod=share&code=share_setting',
    		),
    		array(
    			'name' => '微博评论模块',
	    		'link' => 'admin.php?mod=output&code=output_setting',
    		),
    ),
	//子菜单 单位部门
    '_company' => array(
    		array(
    			'name' => '功能说明',
	    		'link' => 'admin.php?mod=setting&code=cp_ad',
    		),
			array(
    			'name' => '单位管理',
	    		'link' => 'admin.php?mod=company',
    		),
    		array(
    			'name' => '部门管理',
	    		'link' => 'admin.php?mod=department',
    		),
			array(
    			'name' => '岗位管理',
	    		'link' => 'admin.php?mod=job',
    		),
    ),
	//马甲
    'vest' => array(
    	array(
    		'name' => '马甲设置',
	    	'link' => 'admin.php?mod=member&code=config',
    	),
		array(
    		'name' => '马甲管理',
	    	'link' => 'admin.php?mod=member&code=vest',
    	),
    ),
	//频道
    'channel' => array(
    	array(
    		'name' => '频道设置',
	    	'link' => 'admin.php?mod=channel&code=config',
    	),
		array(
    		'name' => '频道类型',
	    	'link' => 'admin.php?mod=channel&code=channeltype',
    	),
		array(
    		'name' => '频道管理',
	    	'link' => 'admin.php?mod=channel&code=index',
    	),
    ),
    //url
    'url' => array(
    	array(
    		'name' => '说明',
    		'link' => 'admin.php?mod=url&code=index',
    	),
    	array(
    		'name' => '相关设置',
    		'link' => 'admin.php?mod=url&code=setting',
    	),
    	array(
    		'name' => 'URL链接地址管理',
    		'link' => 'admin.php?mod=url&code=manage',
    	),
    ),
    //tools
    'tools' => array(
    	array(
    		'name' => '说明',
    		'link' => 'admin.php?mod=tools&code=index',
    	),
    	array(
    		'name' => '分享到微博设置',
    		'link' => 'admin.php?mod=tools&code=share_to_weibo',
    	),
    	array(
    		'name' => '微博秀设置',
    		'link' => 'admin.php?mod=tools&code=weibo_show',
    	),
    ),
    //mall
    'mall' => array(
    	array(
    		'name' => '说明',
    		'link' => 'admin.php?mod=mall&code=index',
    	),
    	array(
    		'name' => '设置',
    		'link' => 'admin.php?mod=mall&code=setting',
    	),
		array(
    		'name' => '添加商品',
    		'link' => 'admin.php?mod=mall&code=add_goods',
    	),
    	array(
    		'name' => '商品管理',
    		'link' => 'admin.php?mod=mall&code=goods_list',
    	),
    	array(
    		'name' => '订单管理',
    		'link' => 'admin.php?mod=mall&code=order_list',
    	),
    ),
    //wechat
    'wechat' => array(
    	array(
    		'name' => '设置',
    		'link' => 'admin.php?mod=wechat',
    	),
    	array(
    		'name' => '绑定情况',
    		'link' => 'admin.php?mod=wechat&code=do_list',
    	),
    ),

	'feed' => array(
		array(
    		'name' => '动态记录',
    		'link' => 'admin.php?mod=feed',
    	),
    	array(
    		'name' => '系统设置',
    		'link' => 'admin.php?mod=feed&code=setting',
    	),
		array(
    		'name' => '重要人物',
    		'link' => 'admin.php?mod=feed&code=leader',
    	),
    ),

);
?>