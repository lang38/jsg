<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename navigation.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 194492548 23854 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    public function ModuleObject($config) {
    	
        $this->MasterObject($config, 1);
    }

    public function index() {
    	$nav_conf = jconf::get('navigation');

    	include template('admin/navigation_index');
    }
    
    public function restore() {
    	$nav_def = jconf::get('navigation_default');
    	if($nav_def) {
    		jconf::set('navigation', $nav_def);
    	}
    	$this->Messager('成功恢复', 'admin.php?mod=navigation');
    }
    public function new_save() {
    	    	$nav_conf = jconf::get('navigation');

    	$count = 0;
    	$nav = jget('nav');
    	$new_top_nav = jget('new_top_nav');
    	$new_side_nav = jget('new_side_nav');
    	$new_nav = jget('new_nav');
    	$del_top_nav = jget('del_top_nav');
    	$del_side_nav = jget('del_side_nav');
    	$del_nav = jget('del_nav');

    	
    	foreach($nav as $ntk=>$nt) {
    		$nav_conf[$ntk] = $this->_merge_nav($nt, $nav_conf[$ntk]);
    		if(is_array($nt['list'])) {
    			foreach($nt['list'] as $nsk=>$ns) {
    				$nav_conf[$ntk]['list'][$nsk] = $this->_merge_nav($ns, $nav_conf[$ntk]['list'][$nsk]);
    				if(is_array($ns['list'])) {
    					foreach($ns['list'] as $nk=>$n) {
    						$nav_conf[$ntk]['list'][$nsk]['list'][$nk] = $this->_merge_nav($n, $nav_conf[$ntk]['list'][$nsk]['list'][$nk]);
    					}
    				}
    			}
    		}
    	}

    	
    	if($new_top_nav) {
    		foreach($new_top_nav['name'] as $k=>$n) {
    			$n = jfilter($n, 'txt');
    			if(empty($n)) {
    				continue ;
    			}
    			$val = $this->_nav_key($new_top_nav['value'][$k], $nav_conf);
    			if(empty($val) || is_array($nav_conf[$val])) {
    				$val = 'top_nav-'.TIMESTAMP.++$count;
    			}
    			if(!isset($nav_conf[$val])) {
	    			$nav_conf[$val] = array(
	    				'name' => $n,
	    				'value' => $val,
	    				'enable' => 1,
	    				'display_in_top' => 1,
	    				'display_in_side' => 1,
	    				'order' => '10',
	    				'url' => '',
	    				'target' => '_parent',
	    			);
    			}
    		}
    	}
    	if($new_side_nav) {
    		foreach($new_side_nav as $top_nav_key=>$top_nav) {
    			if(isset($nav_conf[$top_nav_key])) {
    				foreach($top_nav['name'] as $k=>$n) {
		    			$n = jfilter($n, 'txt');
		    			if(empty($n)) {
		    				continue ;
		    			}
    					$val = $this->_nav_key($top_nav['value'][$k], $nav_conf);
    					if(empty($val) || is_array($nav_conf[$top_nav_key]['list'][$val])) {
    						$val = "{$top_nav_key}-side_nav-".TIMESTAMP.++$count;
    					}
    					if(!isset($nav_conf[$top_nav_key]['list'][$val])) {
	    					$nav_conf[$top_nav_key]['list'][$val] = array(
	    						'name' => $n,
	    						'value' => $val,
	    						'enable' => $top_nav['enable'][$k],
	    						'display_in_top' => $top_nav['display_in_top'][$k],
	    						'display_in_side' => $top_nav['display_in_side'][$k],
	    						'order' => $top_nav['order'][$k],
	    						'url' => $top_nav['url'][$k],
	    						'target' => $top_nav['target'][$k],
	    					);
    					}
    				}
    			}
    		}
    	}
    	if($new_nav) {
    		foreach($new_nav as $top_nav_key=>$top_nav) {
    			if (isset($nav_conf[$top_nav_key])) {
    				foreach($top_nav as $side_nav_key=>$side_nav) {
    					if(isset($nav_conf[$top_nav_key]['list'][$side_nav_key])) {
    						foreach($side_nav['name'] as $k=>$n) {
    							$n = jfilter($n, 'txt');
				    			if(empty($n)) {
				    				continue ;
				    			}
    							$val = $this->_nav_key($side_nav['value'][$k], $nav_conf);
    							if(empty($val) || is_array($nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$val])) {
    								$val = "{$side_nav_key}-nav-".TIMESTAMP.++$count;
    							}
    							if(!isset($nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$val])) {
	    							$nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$val] = array(
	    								'name' => $n,
			    						'value' => $val,
			    						'enable' => $side_nav['enable'][$k],
			    						'display_in_top' => $side_nav['display_in_top'][$k],
			    						'display_in_side' => $side_nav['display_in_side'][$k],
			    						'order' => $side_nav['order'][$k],
			    						'url' => $side_nav['url'][$k],
			    						'target' => $side_nav['target'][$k],
	    							);
    							}
    						}
    					}
    				}
    			}
    		}
    	}

    	
    	if($del_top_nav) {
    		foreach($del_top_nav as $top_nav_key) {
    			if(isset($nav_conf[$top_nav_key]) && is_array($nav_conf[$top_nav_key]) && 
    				!$nav_conf[$top_nav_key]['system']) {
    				unset($nav_conf[$top_nav_key]);
    			}
    		}
    	}
    	if($del_side_nav) {
    		foreach($del_side_nav as $top_nav_key=>$side_nav_keys) {
    			if(isset($nav_conf[$top_nav_key]) && is_array($nav_conf[$top_nav_key])) {
	    			foreach($side_nav_keys as $side_nav_key) {
	    				if(isset($nav_conf[$top_nav_key]['list'][$side_nav_key]) && 
	    					is_array($nav_conf[$top_nav_key]['list'][$side_nav_key]) && 
	    					!$nav_conf[$top_nav_key]['list'][$side_nav_key]['system']) {
	    					unset($nav_conf[$top_nav_key]['list'][$side_nav_key]);
	    				}
	    			}
    			}
    		}
    	}
    	if($del_nav) {
    		foreach($del_nav as $top_nav_key=>$side_nav_keys) {
    			if(isset($nav_conf[$top_nav_key]) && is_array($nav_conf[$top_nav_key])) {
    				foreach($side_nav_keys as $side_nav_key=>$nav_keys) {
    					if(isset($nav_conf[$top_nav_key]['list'][$side_nav_key]) && 
    						is_array($nav_conf[$top_nav_key]['list'][$side_nav_key])) {
    						foreach($nav_keys as $nav_key) {
    							if (isset($nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$nav_key]) && 
    								is_array($nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$nav_key]) && 
    								!$nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$nav_key]['system']) {
    								unset($nav_conf[$top_nav_key]['list'][$side_nav_key]['list'][$nav_key]);
    							}
    						}
    					}
    				}
    			}
    		}
    	}
    	
    	
    	if($nav_conf) {
	    	$tmp_order_func = create_function('$a,$b', 'return ($a[order]==$b[order]?0:($a[order]<$b[order]?1:-1));');
	    	foreach($nav_conf as $tnk=>$tn) {
	    		if($tn['list']) {
	    			foreach($tn['list'] as $snk=>$sn) {
	    				if($sn['list']) {
	    					uasort($nav_conf[$tnk]['list'][$snk]['list'], $tmp_order_func);
	    				}
	    			}
	    			uasort($nav_conf[$tnk]['list'], $tmp_order_func);
	    		}
	    	}
	    	uasort($nav_conf, $tmp_order_func);
	
	    	
	    	jconf::set('navigation', $nav_conf);
    	}

    	$this->Messager('更新成功');
    }
    
    public function icon() {
    	$file = $key = jget('key', 'txt');
    	$nav_top = jget('nav_top', 'txt');
    	$nav_side = jget('nav_side', 'txt');
    	$nav_nav = jget('nav_nav', 'txt');
    	if($_FILES[$file]['name']) {
    		$image_name = dir_safe($key) . ".jpg";
            $image_path = RELATIVE_ROOT_PATH . 'images/icon/';
            $image_file = $image_path . $image_name;

            if (!is_dir($image_path)) {
                jio()->MakeDir($image_path);
            }

            jupload()->init($image_path, $file, true);
            jupload()->setMaxSize(1024);
            jupload()->setNewName($image_name);
            $result = jupload()->doUpload();

            if ($result) {
                $result = is_image($image_file);
            }
            if (!$result) {
                                $this->_js_output('alert("图片上传失败");');
            }
            image_thumb($image_file, $image_file, 100, 100);
            if ($this->Config['ftp_on']) {
                $ftp_key = randgetftp();
                $get_ftps = jconf::get('ftp');
                $site_url = $get_ftps[$ftp_key]['attachurl'];
                $ftp_result = ftpcmd('upload', $image_file, '', $ftp_key);
                if ($ftp_result > 0) {
                    jio()->DeleteFile($image_file);
                    $image_file = $site_url . '/' . str_replace('./', '', $image_file);
                }
            }

            
            $nav_conf = jconf::get('navigation');
            if($nav_top && isset($nav_conf[$nav_top])) {
            	if($nav_side) {
            		if($nav_nav) {
            			if(isset($nav_conf[$nav_top]['list'][$nav_side]['list'][$nav_nav])) {
            				$nav_conf[$nav_top]['list'][$nav_side]['list'][$nav_nav]['icon'] = $image_file;
            			}
            		} else {
            			if(isset($nav_conf[$nav_top]['list'][$nav_side])) {
            				$nav_conf[$nav_top]['list'][$nav_side]['icon'] = $image_file;
            			}
            		}
            	} else {
            		$nav_conf[$nav_top]['icon'] = $image_file;
            	}
            	jconf::set('navigation', $nav_conf);
            }

            $this->_js_output("parent.document.getElementById('nav_{$key}_icon').src='{$image_file}?".TIMESTAMP."';
	            parent.document.getElementById('nav_{$key}_icon').style.display='block';
	            parent.document.getElementById('nav_{$key}_icon_value').value='$image_file';
	            ");
    	} else {
    		$this->_js_output('alert("请选择要上传的图片");');
    	}
    }
    
    private function _merge_nav($nav_new, $nav_def) {
    	$nav = array();
    	foreach($nav_def as $k=>$v) {
    		if(!is_array($v) && $v != $nav_new[$k] && !in_array($k, array('system', 'value'))) {
    			    			if(!$nav_def['system'] ||
    				($nav_def['system'] && in_array($k, array('name', 'order', 'enable', 'display_in_top', 'display_in_side', 'target', 'icon')))) {
    				$v = $nav_new[$k];
    			}
    		}
    		$nav[$k] = $v;
    	}
    	return $nav;
    }
    
    private function _nav_key($key, $nav_conf = array()) {
    	    	$key = jfilter($key, 'txt');
    	if(empty($key)) {
    		return false;
    	}
    	$nav_conf = ($nav_conf ? $nav_conf : jconf::get('navigation'));
    	    	if(isset($nav_conf[$key]) && is_array($nav_conf[$key])) {
    		return false;
    	}
    	foreach($nav_conf as $tnk=>$tn) {
    		if($tn['list']) {
    			    			if(isset($tn['list'][$key]) && is_array($tn['list'][$key])) {
    				return false;
    			}
    			foreach($tn['list'] as $snk=>$sn) {
    				if($sn['list']) {
    					    					if(isset($sn['list'][$key]) && is_array($sn['list'][$key])) {
    						return false;
    					}
    				}
    			}
    		}
    	}
    	return $key;
    }
    
    private function _js_output($str, $halt = 1) {
    	echo "<script type='text/javascript'>{$str}</script>";
    	$halt && exit;
    }

    
    public function navigation($local = 'top') {
        $conf_file = 'navigation';
        if ($local == "footer") {
            $conf_file = "footer_navigation";
        }
        $slide_config = jconf::get($conf_file);
        $slide_list = $slide_config['list'];
                foreach ($slide_list as $group_key => $group_one) {
            if ($group_one['code'] == 'beian') {
                $slide_list[$group_key]['name'] = $this->Config['icp'];
            }
            foreach ($group_one['type_list'] as $item_key => $item_one) {
                if ($item_one['code'] == 'beian') {
                    $slide_list[$group_key]['type_list'][$item_key]['name'] = $this->Config['icp'];
                }
            }
        }
        
        include(template('admin/navigation_navigation'));
    }

    
    public function footer_navigation() {
        $this->navigation('footer');
    }

    
    public function save() {
        if ($this->Post['local'] == 'footer') {
            $conf_file = 'footer_navigation';
        } elseif ($this->Post['local'] == 'top') {
            $conf_file = 'navigation';
        }
                $conf_file || $this->Messager('请重试！');

        $nav_old_config = jconf::get($conf_file);

        $nav_conf = jget('slide');
        $del = jget('del');
        $del_one = jget('del_one');
        $slide_new_order = jget('slide_new_order');
        $slide_new_name = jget('slide_new_name');
        $slide_new_code = jget('slide_new_code');
        $slide_new_url = jget('slide_new_url');
        $slide_new_target = jget('slide_new_target');
        $slide_new_avaliable = jget('slide_new_avaliable');


        $type_new_order = jget('type_new_order');
        $type_new_name = jget('type_new_name');
        $type_new_code = jget('type_new_code');
        $type_new_url = jget('type_new_url');
        $type_new_target = jget('type_new_target');
        $type_new_avaliable = jget('type_new_avaliable');
                if (is_array($slide_new_name)) {
            foreach ($slide_new_name as $new_group_key => $new_group) {
                $nav_conf[] = array(
                    'order' => (int) $slide_new_order[$new_group_key],
                    'name' => $slide_new_name[$new_group_key],
                    'code' => $slide_new_code[$new_group_key],
                    'url' => $slide_new_url[$new_group_key],
                    'target' => $slide_new_target[$new_group_key],
                    'avaliable' => (int) $slide_new_avaliable[$new_group_key],
                );
            }
        }
                if (is_array($type_new_name)) {
            foreach ($type_new_name as $new_key => $item) {
                if ($nav_conf[$new_key]) {
                    foreach ($item as $new_one_key => $one) {
                        if ($one) {
                            $nav_conf[$new_key]['type_list'][] = array(
                                'order' => (int) $type_new_order[$new_key][$new_one_key],
                                'name' => $type_new_name[$new_key][$new_one_key],
                                'code' => $type_new_code[$new_key][$new_one_key],
                                'url' => $type_new_url[$new_key][$new_one_key],
                                'target' => $type_new_target[$new_key][$new_one_key],
                                'avaliable' => (int) $type_new_avaliable[$new_key][$new_one_key],
                            );
                        }
                    }
                }
            }
        }
                if (is_array($del)) {
            foreach ($del as $one) {
                if ($nav_conf[$one]) {
                    unset($nav_conf[$one]);
                }
            }
        }
                if (is_array($del_one)) {
            foreach ($del_one as $one_key => $group) {
                if ($nav_conf[$one_key]['type_list']) {
                    foreach ($group as $one) {
                        if ($nav_conf[$one_key]['type_list'][$one]) {
                            unset($nav_conf[$one_key]['type_list'][$one]);
                        }
                    }
                }
            }
        }

                foreach ($nav_conf as $key_for_dort_g => $group_temp) {
                        foreach ($group_temp['type_list'] as $item_key => $item_one) {
                if ($item_one['code'] == 'beian') {
                    $group_temp['type_list'][$item_key]['name'] = $this->Config['icp'];
                }
            }
                        if ($group_temp['type_list']) {
                usort($group_temp['type_list'], create_function('$a,$b', 'if($a[order]==$b[order])return 0;return $a[order]<$b[order]?1:-1;'));
            }
            $nav_conf[$key_for_dort_g]['type_list'] = $group_temp['type_list'];
        }

        if ($nav_conf) {
            usort($nav_conf, create_function('$a,$b', 'if($a[order]==$b[order])return 0;return $a[order]<$b[order]?1:-1;'));
        }
                if (!$nav_conf) {
            $nav_conf = $nav_old_config;
        }
        $slide['list'] = $nav_conf;
                $slide['pluginmenu'] = $nav_old_config['pluginmenu'] ? $nav_old_config['pluginmenu'] : array();
        $r = jconf::set($conf_file, $slide);
        $this->Messager('设置成功');
    }

    
    public function left_navigation() {

        $slide_list = jconf::get('left_navigation');

        include(template('admin/navigation_left_navigation'));
    }

    
    public function modify_left_icon() {

        $key = $this->Get['key'];
        $group = $this->Get['group'];
        $name = $this->Get['name'];


        $file = $key;

        $slide = $this->Post['slide'];

        if ($_FILES[$file]['name']) {

            
            $image_name = dir_safe($key) . ".jpg";
            $image_path = RELATIVE_ROOT_PATH . 'images/lefticon/';
            $image_file = $image_path . $image_name;

            if (!is_dir($image_path)) {
                jio()->MakeDir($image_path);
            }

            jupload()->init($image_path, $file, true);
            jupload()->setMaxSize(512);
            jupload()->setNewName($image_name);
            $result = jupload()->doUpload();

            if ($result) {
                $result = is_image($image_file);
            }
            if (!$result) {
                                $this->_js_output('alert("图片上传失败");');
            }
            image_thumb($image_file, $image_file, 100, 100);
            if ($this->Config['ftp_on']) {
                $ftp_key = randgetftp();
                $get_ftps = jconf::get('ftp');
                $site_url = $get_ftps[$ftp_key]['attachurl'];
                $ftp_result = ftpcmd('upload', $image_file, '', $ftp_key);
                if ($ftp_result > 0) {
                    jio()->DeleteFile($image_file);
                    $image_file = $site_url . '/' . str_replace('./', '', $image_file);
                }
            }
        } else {
            echo "<script type='text/javascript'>";
            echo "alert('没有图片');";
            echo "</script>";
            exit();
        }
                $slide_config = jconf::get('left_navigation');
        foreach ($slide_config[$group] as &$v) {
            if ($v['code'] == $name) {
                $v['icon'] = $image_file;
            }
        }
        jconf::set('left_navigation', $slide_config);

        echo "<script type='text/javascript'>";

                echo "parent.document.getElementById('show_image_$key').src='{$image_file}';";
        echo "parent.document.getElementById('show_image_$key').style.display='block';";
        echo "parent.document.getElementById('show_image_{$key}_value').value='{$image_file}';";
                echo "parent.document.location.reload();";
        echo "</script>";
        exit;
    }

    
    public function do_left_navigation() {
        $slide_config = jconf::get('left_navigation');
        $slide = $this->Post['slide'];
        $chk = $this->Post['chk'];
        $temp['order'] = $this->Post['slide_new_order'];
        $temp['name'] = $this->Post['slide_new_name'];
        $temp['code'] = $this->Post['slide_new_code'];
        $temp['url'] = $this->Post['slide_new_url'];
        $temp['avaliable'] = $this->Post['slide_new_avaliable'];
        $temp_for_slide = array("mine" => array(), "myapp" => array(), "app" => array());
        if ($slide) {

            foreach ($temp_for_slide as $k_type => $value) {

                                if (!empty($temp['code'][$k_type])) {
                    foreach ($temp['code'][$k_type] as $k_new_one => $code_one) {
                        if (!$code_one || !$temp['name'][$k_type][$k_new_one]) {
                                                        continue;
                        }
                                                $slide[$k_type][$code_one]['code'] = $code_one;
                        $slide[$k_type][$code_one]['name'] = $temp['name'][$k_type][$k_new_one];
                        $slide[$k_type][$code_one]['order'] = $temp['order'][$k_type][$k_new_one];
                        $slide[$k_type][$code_one]['url'] = $temp['url'][$k_type][$k_new_one];
                        $slide[$k_type][$code_one]['avaliable'] = $temp['avaliable'][$k_type][$k_new_one];
                    }
                }

                foreach ($slide[$k_type] as $k => $v) {
                    if ($chk && in_array($k, $chk)) {
                                                unset($slide[$k_type][$k]);
                        continue;
                    }
                                        $slide[$k_type][$k]['avaliable'] = $v['avaliable'] ? 1 : 0;
                    if (!$v['name'] || !$v['code']) {
                        unset($slide[$k_type][$k]);
                        continue;
                    }
                                        $changeWords = array('mygroup');

                    if (in_array($v['code'], $changeWords)) {
                        $slide[$k_type][$k]['name'] = str_replace('微群', $this->Config['changeword']['weiqun'], $v['name']);
                    }
                                        $changeWords = array('newdigout', 'mydigout');
                    if (in_array($v['code'], $changeWords)) {
                        $slide[$k_type][$k]['name'] = str_replace('赞', $this->Config['changeword']['dig'], $v['name']);
                    }
                }
            }
        }

                if ($slide['mine']) {
            usort($slide['mine'], create_function('$a,$b', 'if($a[order]==$b[order])return 0;return $a[order]<$b[order]?1:-1;'));
        } else {
            $slide['mine'] = array();
        }
        if ($slide['myapp']) {
            usort($slide['myapp'], create_function('$a,$b', 'if($a[order]==$b[order])return 0;return $a[order]<$b[order]?1:-1;'));
        } else {
            $slide['myapp'] = array();
        }
        if ($slide['app']) {
            usort($slide['app'], create_function('$a,$b', 'if($a[order]==$b[order])return 0;return $a[order]<$b[order]?1:-1;'));
        } else {
            $slide['app'] = array();
        }


        $slide = $slide ? $slide : $slide_config;
        jconf::set('left_navigation', $slide);
        $this->Messager('设置成功');
    }

}

?>
