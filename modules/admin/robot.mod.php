<?php

/**
 *[JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 搜索引擎蜘蛛
 *
 * @author 狐狸<foxis@qq.com>
 * @package www.jishigou.net
 */

if(!defined('IN_JISHIGOU'))
{
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	
	var $ad=false;
	
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->ID = $this->Get['id']?(int)$this->Get['id']:(int)$this->Post['id'];
		$this->configPath=ROOT_PATH . 'setting';
		$this->Execute();
	}

	
	function Execute()
	{
		ob_start();
		switch($this->Code)
		{
			case 'domodify':
				$this->DoModify();
				break;
			case 'view':
				$this->view();
				break;
			case 'viewip':
				$this->viewIP();
				break;
			case 'deleteip':
				$this->deleteIP();
				break;
			case 'disallow0':
			case 'disallow1':
				$this->Disallow();
				break;
			default:
				$this->Code = 'robot_tool';
				$this->Main();
				break;
		}
		$body = ob_get_clean();

		$this->ShowBody($body);

	}

	
	function Main()
	{
		$config=jconf::get('robot');



				$order_by=$this->Get['order_by']?$this->Get['order_by']:"today_times";
		$order_type=$this->Get['order_type']?$this->Get['order_type']:"desc";
		$toggle_order_type=$order_type=="desc"?"asc":"desc";
		$$order_by="order_".$order_type;

		include_once(ROOT_PATH . 'include/logic/robot.logic.php');
		$RobotRogic=new RobotLogic();
		$turnon_radio=$this->jishigou_form->YesNoRadio('config[turnon]',(int)$config['turnon'],'','class="radio"');

		if ($config['turnon'])
		{
			if(false === ($robot_list = cache_file('get', ($cache_id = 'robot/list')))) {
				$sql="SELECT * FROM ".$RobotRogic->tableName;
				$query=$this->DatabaseHandler->Query($sql);
				$robot_list=array();
				while(false != ($row = $query->GetRow())) {
					if($row['times'] < 10) {
						continue ;
					}
					$row['link']=preg_replace("/.*?(((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k):\/\/)|(www\.))([^\[\"'\s\)\(\;]+))|([a-z0-9\_\-.]+@[a-z0-9]+\.[a-z0-9\.]{2,}))*/i","\\1",$row['agent']);
					if(strpos($row['link'],'@')!==false) {
						$row['link']="mailto:".$row['link'];
					}
					if($row['link'] && strpos($row['link'],":")===false) {
						$row['link']="http:/"."/".$row['link'];
					}
					$row['first_visit_timestamp']=$row['first_visit'];
					$row['first_visit']=my_date_format($row['first_visit']);
					$row['last_visit_timestamp']=$row['last_visit'];
					$row['last_visit']=my_date_format($row['last_visit']);
					if($this->ad) {
						$show_ad=isset($config['list'][$row['name']]['show_ad'])
								?(int)$config['list'][$row['name']]['show_ad']:
								1;
						$row['show_ad_radio']=$this->jishigou_form->YesNoRadio("config[list][{$row['name']}][show_ad]",$show_ad,'',"class='radio'");
					}
					$row['today_times']=0;
					$row['name']=trim($row['name']);

					$robot_list[$row['name']] = $row;
				}
				cache_file('set', $cache_id, $robot_list, 3600);
			}

			$name_list=array();
			foreach($robot_list as $row) {
				if($row['last_visit_timestamp'] + 86400 > TIMESTAMP) {
					$name_list[]=$row['name'];
				}
			}

						if(!empty($name_list) && count($name_list)>=0)
			{
				$names = jimplode($name_list);
				include_once ROOT_PATH . 'include/logic/robot_log.logic.php';
				$RobotLogLogic=new RobotLogLogic("");
				$sql="SELECT * FROM {$RobotLogLogic->tableName}
				where
					`name` in($names)
					and `date`='{$RobotLogLogic->date}'";
				$query=$this->DatabaseHandler->Query($sql);
				while ($row=$query->GetRow())
				{
					if(isset($robot_list[$row['name']])) {
						$robot_list[$row['name']]['today_times']=$row['times'];
					}
				}
			}

						if(is_array($robot_list) && sizeof($robot_list)>0)
			{
				foreach ($robot_list as $key=>$value)
				{
					$order_by_list[$key]=$value[$order_by];
				}
				array_multisort($order_by_list,constant(strtoupper("sort_".$order_type)),$robot_list);
			}
						if(sizeof($robot_list)>0 && false === ($robot_ip_list = cache_file('get', ($cache_id = 'robot/ip_list'))))
			{
				$robot_ip_list=array();
								$sql="SELECT ip,name from {$RobotRogic->tableName}_ip GROUP BY `name` order by `last_visit` DESC";
				$query=$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
				if($query!=false)
				{
					while ($row=$query->GetRow())
					{
						$robot_ip_list[$row['name']][]=$row['ip'];
					}
					if(!empty($robot_ip_list))
					{
						foreach ($robot_ip_list as $_robot=>$_ip_list)
						{
							if(sizeof($_ip_list)>5)
							{
								$ip_list=array();
								$ip_list_count=0;
								foreach ($_ip_list as $_ip)
								{
									$ip=substr($_ip,0,strrpos($_ip,".")).".*";
									$ip_list[$ip]=$ip;
									$ip_list_count++;
									if($ip_list_count>10)break;
								}
								$robot_ip_list[$_robot]=$ip_list;
							}
						}
					}
				}
				cache_file('set', $cache_id, $robot_ip_list, 3600);
			}
		}


		include template("admin/robot");
	}
	function doModify()
	{
		$delete_list=(array)$this->Post['delete'];

		@$robot_config=jconf::get('robot');
		$robot_config['turnon'] = (bool) $this->Post['config']['turnon'];

		if(sizeof($delete_list))
		{
			include_once(ROOT_PATH . 'include/logic/robot.logic.php');
			$RobotRogic=new RobotLogic();

			include_once ROOT_PATH . 'include/logic/robot_log.logic.php';
			$RobotLogLogic=new RobotLogLogic("");

			foreach ($delete_list as $name)
			{
				unset($robot_config['list'][$name]);
				$sql="DELETE from ".$RobotRogic->tableName." where name='".$name."'";
				$query = $this->DatabaseHandler->Query($sql);
				$sql="DELETE from ".$RobotLogLogic->tableName." where name='".$name."'";
				$query=$this->DatabaseHandler->Query($sql,"SKIP_ERROR");
			}
		}

		jconf::set('robot',$robot_config);

		jconf::update('robot_enable', ($robot_config['turnon'] ? 1 : 0));


		$this->Messager("修改成功");
	}
	function view()
	{
		$name=trim($this->Get['name']);
		$day=(int)($this->Get['day']);
		if($name=="")$this->Messager("名称不能为空",null);
		if($day<1)$this->Messager("时间周期不能小于1",null);
		$date_from=date("Ymd",time()-($day*86400));
		$date_to=date("Ymd");

		$perpage = jget('pn', 'int');
		if($perpage < 1 || $perpage > 200) {
			$perpage = 50;
		}
		$rets = jtable('robot_log')->get(array(
			'perpage' => $perpage,
			'page_options' => array(
				'per_page_nums' => '20 50 100 200',
			),
			'name' => $name,
			'>@date' => $date_from,
			'<=@date' => $date_to,
		));
		$count = $rets['count'];
		$page = $rets['page'];
		$times = 0;
		$log_list = array();
		if($count > 0) {
			foreach($rets['list'] as $k=>$row) {
				$row['first_visit']=my_date_format($row['first_visit'],"H:i:s");
				$row['last_visit']=my_date_format($row['last_visit'],"H:i:s");
				$times+=$row['times'];
				$log_list[$k]=$row;
			}
		}

		include template('admin/robot_view');
	}

	function viewIP()
	{
		$robot=trim($this->Get['robot']);
		if(empty($robot)) {
			$this->Messager('请先指定一个要查询的名称');
		}
		$page_url = 'admin.php?mod=robot&code=viewip&robot=' . urlencode($robot);
		$per_page_num = jget('pn', 'int');
		if($per_page_num < 1) {
			$per_page_num = jget('per_page_num', 'int');
		}
		if($per_page_num < 1 || $per_page_num > 200) {
			$per_page_num = 50;
		}
		$page_url .= '&pn=' . $per_page_num;
		$npage = $page_url;
		$order = jget('order', 'txt');
		if(!in_array($order, array('times', 'ip'))) {
			$order = 'last_visit';
		}
		$page_url .= '&order=' . $order;
		$rets = jtable('robot_ip')->get(array(
			'perpage' => $per_page_num,
			'page_url' => $page_url,
			'page_options' => array(
				'per_page_nums' => '20 50 100 200',
			),
			'name' => $robot,
			'sql_order' => ' `'.$order.'` DESC ',
		));
		$count = $rets['count'];
		$page = $rets['page'];
		if($count > 0) {
			$ip_list = array();
			foreach($rets['list'] as $k => $row) {
				$row['first_visit']=my_date_format($row['first_visit']);
				$row['last_visit']=my_date_format($row['last_visit']);
				$times+=$row['times'];
				$ip_list[$k]=$row;
			}
		} else {
			$this->Messager("无IP记录");
		}
		include template('admin/robot_view_ip');
	}
	function deleteIP()
	{
		$ip=trim($this->Get['ip']);
		if(empty($ip))
		{
			$this->Messager("请指定IP");
		}
		$sql="delete from ".TABLE_PREFIX."robot_ip where ip='$ip'";
		$this->DatabaseHandler->Query($sql);
		$this->Messager("删除成功");
	}

	function Disallow()
	{
		$name = trim($this->Get['name']);
		$disallow = 'disallow1' == $this->Code ? 1 : 0;

		$sql = "update `".TABLE_PREFIX."robot` set `disallow`='{$disallow}' where `name`='{$name}'";
		$this->DatabaseHandler->Query($sql);

		$sql = "select `name`,`disallow` from `".TABLE_PREFIX."robot` where `disallow`=1";
		$query = $this->DatabaseHandler->Query($sql);
		$robot_config = jconf::get('robot');
		$robot_config['list'] = array();
		while (false != ($row = $query->GetRow()))
		{
			$robot_config['list'][$row['name']]['disallow'] = $row['disallow'];
		}
		jconf::set('robot',$robot_config);


		$disallow_string = "User-agent: {$name}
Disallow: /

";



		$robots_path = ROOT_PATH . 'robots.txt';

		$robots_string_new = $robots_string = jio()->ReadFile($robots_path);
		$disallow_string_strpos = strpos($robots_string,$disallow_string);
		if ($disallow && false===$disallow_string_strpos) {
			$robots_string_new = $disallow_string . $robots_string_new;
		} elseif (!$disallow && false!==$disallow_string_strpos) {
			$robots_string_new = str_replace($disallow_string,"",$robots_string_new);
		}

		if ($robots_string_new!=$robots_string) {
			$return = jio()->WriteFile($robots_path,$robots_string_new);

			if (!$return) {
				$this->Messager("写入 <b>{$robots_path}</b> 文件失败，请检查是否有可读写的权限",null);
			}
		}

		$this->Messager("修改成功");
	}

}
?>
