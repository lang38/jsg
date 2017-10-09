<?php 

/**
 * @version $Id: plugin.php 3661 2013-05-21 07:03:39Z wuliyong $
 */
$config['plugin']=array (
	'modtype' =>array(
		'扩展项目' => array(
			array (
				'name' => '个人设置',
				'val'  => '3',
			),
			array (
				'name' => '个人设置 - 资料设置',
				'val'  => '2',
			),
			array (
				'name' => '管理中心',
				'val'  => '4',
			)
		),
		'程序脚本' => array(
			array (
				'name' => '页面镶入',
				'val'  => '5',
			),
			array (
				'name' => '独立模块',
				'val'  => '1',
			)
		)
	),
	'vartype' => array(
		array(
		   	'name' => '单行文本框',
			'val'  => 'text',
		),
		array(
		   	'name' => '多行文本框',
			'val'  => 'textarea',
		),
		array(
		   	'name' => '下拉菜单单选',
			'val'  => 'select',
		),
		array(
		   	'name' => '下拉菜单多选',
			'val'  => 'selects',
		),
		array(
		   	'name' => '单选框',
			'val'  => 'radio',
		),
		array(
		   	'name' => '复选框',
			'val'  => 'checkbox',
		),
		array(
		   	'name' => '用户组单选',
			'val'  => 'usergroup',
		),
		array(
		   	'name' => '用户组多选',
			'val'  => 'usergroups',
		)
	),
);
?>