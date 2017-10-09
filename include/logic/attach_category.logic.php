<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename attach_category.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 563842429 10870 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class AttachCategoryLogic {

    
    public function get_attr($catid, $attr) {
        $attr = jtable('attach_category')->val(array('id' => $catid), $attr);
        return $attr;
    }

    
    public function get_info($catid) {
        $info = jtable('attach_category')->info(array('id' => (int) $catid));

        return $info;
    }

    
    public function get_attacht_cat($catid) {
        $upid = trim($this->get_attr($catid, 'upid'), ',');
        if ($upid) {
            $upid = ',' . $upid . ',' . $catid . ',';
        } else {
            $upid = ',' . $catid . ',';
        }
        return $upid;
    }

    
    public function create_cat($name, $parent_id = 0, $order = 0) {
        if (trim($name)) {
            $id = jtable('attach_category')->insert(array('name' => trim($name), 'parent_id' => $parent_id, 'order' => (int) $order), 1);
            if ($id) {
                if ($parent_id > 0) {
                    $upid = trim($this->get_attr($parent_id, 'upid'), ',');
                    $upid = $upid ? $upid . ',' . $parent_id : $parent_id;
                    $upid = ',' . $upid . ',';
                } else {
                    $upid = '';
                }
                jtable('attach_category')->update(array('upid' => $upid), array('id' => $id));
            }
            return (int) $id;
        } else {
            return FALSE;
        }
    }

    
    public function modify_cat($catid, $name, $parent_id = 0, $order = 0) {
        $info = $this->get_info($catid);
        
        if($parent_id){
            $parent_upid = $this->get_attr($parent_id, 'upid');
            if(FALSE !== strpos($parent_upid,','.$parent_id.',') || $parent_id == $catid){
                return FALSE;
            }
        }
        if ($catid && $info) {
            if ($parent_id > 0) {
                $upid = trim($this->get_attr($parent_id, 'upid'), ',');
                $upid = $upid ? $upid . ',' . $parent_id : $parent_id;
                $upid = ',' . $upid . ',';
            } else {
                $upid = '';
            }
            $update = array('order' => (int) $order, 'name' => $name);
            if ($info['parent_id'] != $parent_id) {
                $update['upid'] = $upid;
                $update['parent_id'] = $parent_id;
                $query_upid = $info['upid'] ? $info['upid'] . $catid . ',' : ',' . $catid . ',';
                $new_upid = $upid ? $upid . $catid . ',' : ',' . $catid . ',';
                DB::query("UPDATE " . DB::table('attach_category') . " SET upid = REPLACE(upid,'{$query_upid}','{$new_upid}') WHERE upid LIKE '%{$query_upid}%'");
                DB::query("UPDATE " . DB::table('topic_attach') . " SET category = REPLACE(category,'{$query_upid}','{$new_upid}') WHERE category LIKE '%{$query_upid}%'");
                                $this->update_count($info['parent_id']);                $this->update_count($parent_id);            }
            $r = jtable('attach_category')->update($update, array('id' => $catid));
            return $r;
        } else {
            return FALSE;
        }
    }

    
    public function get_cat_list($catid = 0) {
        $attach_category_table = jtable('attach_category');

        $list = $attach_category_table->get(array('parent_id' => $catid, 'sql_order' => '`order`'));
        if(is_array($list['list']) && count($list['list'])) {
	        foreach ($list['list'] as &$one) {	            $one['count_sub'] = (int) $attach_category_table->count(array('parent_id' => $one['id']));	
	        }
        }
        return $list['list'];
    }

    
    public function delete_cat($catid) {
        $count = $this->get_count_sub($catid);
        if ($count < 1) {
            $parent_id = $this->get_attr($catid, 'parent_id');
            $r = jtable('attach_category')->delete(array('id' => $catid), 1);
                        if($r && $parent_id>0){
                
                $this->update_count($parent_id);
            }
            return $r;
        } else {
            return FALSE;
        }
    }

    
    public function get_count_sub($catid) {
        $count = jtable('attach_category')->count(array('parent_id' => $catid));
        return (int) $count;
    }

    
    public function get_all_attach($catid, $limit = 10) {
        if ($catid) {
            $likeid = $this->get_attacht_cat($catid);
        } else {
            $likeid = '';
        }
                $list = jtable('topic_attach')->get(array('category' => $likeid, 'page_num' => $limit, 'sql_order' => 'dateline DESC'));
        return $list;
    }

    
    public function get_all_cat_att() {
        $cats = $this->get_cat_list();
        if($cats) {
	        foreach ($cats as &$one) {
	            $list = $this->get_all_attach($one['id']);
	            $one['attach_list'] = $list['list'];
	            if ($this->get_count_sub($one['id'])) {	                $sub_list = $this->get_cat_list($one['id']);
	                if($sub_list) {
		                foreach ($sub_list as &$sub_one) {
		                    $sub_att_list = $this->get_all_attach($sub_one['id']);
		                    $sub_one['attach_list'] = $sub_att_list['list'];
		                    if ($this->get_count_sub($sub_one['id'])) {		                        $sub_sub_list = $this->get_cat_list($sub_one['id']);
		                        foreach ($sub_sub_list as &$sub_one_one) {
		                            $sub_sub_att_list = $this->get_all_attach($sub_one_one['id']);
		                            $sub_one_one['attach_list'] = $sub_sub_att_list['list'];
		                        }
		                        $sub_one['sub_list'] = $sub_sub_list;
		                    }	                    
		                }
	                }
	                $one['sub_list'] = $sub_list;
	            }
	        }
        }
        return $cats;
    }

    
    public function delete_attach($aid) {
        $info = jtable('topic_attach')->info(array('id' => (int) $aid));
        $r = jtable('topic_attach')->delete(array('id' => (int) $aid));
        if ($r && $info['tid']>0) {
            $r = jtable('topic')->Delete($info['tid']);
                        $this->update_category_count_by_attach($info['category']);
        }
        return (bool) $r;
    }
    
    public function update_category_count_by_attach($category){
        
        $category = explode(',',trim($category,','));
        $r = $this->update_count(end($category));
        return $r;
    }

    
    public function get_select_html($parentid = 0, $selfid = 0) {

        if ($selfid > 0) {            $upid = trim($this->get_attr($selfid, 'upid'), ',');
            if ($upid) {
                $upid_arr = array_filter(explode(',', $upid));
                                $html = $this->make_select_html(0, reset($upid_arr));
                foreach ($upid_arr as $key => $one) {
                    $html .= $this->make_select_html($one, $upid_arr[$key + 1] ? $upid_arr[$key + 1] : $selfid);
                }
            } else {

                $html = $this->make_select_html(0, $selfid);
            }
        } else {
            $html = $this->make_select_html($parentid, $selfid);
        }
        return $html;
    }

    
    private function make_select_html($parentid = 0, $selfid = 0) {
        $where = '';
        if ($selfid) {
            $where .= ' AND NOT upid LIKE "%,' . (int) $selfid . ',%"';
        }
        $list = DB::fetch_all('SELECT * FROM ' . DB::table('attach_category') .
                        ' WHERE parent_id = ' . (int) $parentid . $where . ' ORDER BY `order`');
        if (!$list) {
            return FALSE;
        }
        $html .= "<select data-parent-id='{$parentid}' onchange='get_sub_cat_val(this);' >";
        $html.= "<option value='0' >请选择</option>";
        foreach ($list as $one) {
            if ($one['id'] == $selfid) {
                $html.= "<option selected='selected' value='$one[id]'>$one[name]</option>";
            } else {
                $html.= "<option value='$one[id]'>$one[name]</option>";
            }
        }
        $html .= '</select>';
        return $html;
    }

    
    private function update_count($catid, $num = 0, $operator = TRUE) {
        if ($num) {
            
        } else {
            $likeid = $this->get_attacht_cat($catid);
            $count = jtable('topic_attach')->count(array('category' => $likeid));
            $total_count_num = jtable('topic_attach')->count(array('like@category' => '%'.$likeid.'%'));
                        
            jtable('attach_category')->update(array('count_num' => $count,'total_count_num'=>$total_count_num),array('id'=>$catid));
            
            $parent_id = $this->get_attr($catid, 'parent_id');
            if($parent_id>0){                $this->update_count($parent_id);
            }
        }
    }

}

?>
