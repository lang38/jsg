<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename attachcat.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 818716863 3694 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    public function __construct($config) {
        $this->MasterObject($config, 1);
    }

    
    public function index() {
        $no_header = FALSE;
        $id = (int) jget('id');
        if ($id > 0) {
            $no_header = TRUE;
            $list = jlogic('attach_category')->get_cat_list($id);
        } else {
            $list = jlogic('attach_category')->get_cat_list();
        }
        include template('admin/attachcat/index');
    }

    public function add() {
        include template('admin/attachcat/add');
    }

    public function do_add() {
        $data = jget('data');

        if (trim($data['name'])) {
            $r = jlogic('attach_category')->create_cat($data['name'], (int) $data['parent_id'], (int) $data['order']);
            if ($r) {
                $this->Messager('成功');
            } else {
                $this->Messager('失败');
            }
        } else {
            $this->Messager('请填写名字！');
        }
    }

    public function delete() {

        $catid = (int) jget('catid');
        if ($catid < 1) {
            $this->Messager('删除哪个？');
            return;
        }
        if (jlogic('attach_category')->get_count_sub($catid) > 0) {
            $this->Messager('请先删除子分类');
        }
        $r = jlogic('attach_category')->delete_cat($catid);
        if ($r) {
            $this->Messager('成功');
        } else {
            $this->Messager('失败');
        }
    }

    
    public function modify() {
        $catid = (int) jget('catid');
        if ($catid < 1) {
            $this->Messager('编辑哪个？');
            return;
        }
        $info = jlogic('attach_category')->get_info($catid);

        include template('admin/attachcat/modify');
    }

    
    public function do_modify() {
        $catid = (int) jget('catid');
        if ($catid < 1) {
            $this->Messager('编辑哪个？');
            return;
        }
        $data = jget('data');
        if (trim($data['name'])) {
            $r = jlogic('attach_category')->modify_cat($catid, $data['name'], (int) $data['parent_id'], (int) $data['order']);
            if ($r) {
                $this->Messager('成功');
            } else {
                $this->Messager('失败');
            }
        } else {
            $this->Messager('请填写名字！');
        }
    }

    
    public function get_attach_list() {
        $catid = (int) jget('catid');
        $attachCatLogic = jlogic('attach_category');
        $attach_list = $attachCatLogic->get_all_attach($catid);

        include template('admin/attachcat/attach_list');
    }

    
    public function delete_attach() {
        $aid = jget('aid');
        if ($aid < 1) {
            $this->Messager('查看哪个？');
            return;
        }
        $r = jlogic('attach_category')->delete_attach($aid);
        if ($r) {
            $this->Messager('成功');
        } else {
            $this->Messager('失败');
        }
    }

    
    public function get_sub_cat() {
        $id = (int) jget('id');

        $html = jlogic('attach_category')->get_select_html($id);
        if ($html) {
            echo $html;
        }
        exit;
    }

}

?>
