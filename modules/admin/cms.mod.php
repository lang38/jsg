<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename cms.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 313986482 10335 $
 */




if (!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject {

    public function __construct($config) {
        $this->MasterObject($config, 1);
    }

    
    public function index() {
        $catid = jget('catid');
        $no_header = jget('is_ajax') ? TRUE : FALSE;
        $list = jlogic('cms')->get_category_sublist($catid);
        if ($list) {
            
        }
        if ($no_header && !$list) {
            exit();
        } else {
            include template('admin/cms/index');
        }
    }

    public function actionName() {
        $return = '<a href="admin.php?mod=cms">' . parent::actionName() . '</a>';
        if (jget('catid') > 0) {
            $return .= $this->breadcrumb(jget('catid'));
        } elseif (jget('article') > 0) {
            $artinfo = jlogic('cms')->getarticlebyid(jget('article'));
            $return .= $this->breadcrumb($artinfo['catid']);
        }
        return $return;
    }

    
    private function breadcrumb($catid) {
        $CmsLogic = jlogic('cms');
        $info = $CmsLogic->get_category_info($catid);
        $html = '';
        if ($info['upid']) {
            $upid = explode(',', $info['upid']);
            foreach ($upid as $one) {
                $one = $CmsLogic->get_category_info($one);
                $html .= '&raquo;<a href="admin.php?mod=cms&catid=' . $one['catid'] . '">' . $one['catname'] . '</a>';
            }
        }
        $html .= '&raquo;<a href="admin.php?mod=cms&catid=' . $info['catid'] . '">' . $info['catname'] . '</a>';
        return $html;
    }

    
    public function add() {
        $parent_id = jget('catid','int');
		if($parent_id){
			$category = jlogic('cms')->Getonecategory($parent_id);
		}
        $role = jtable('role')->get();
                $purview = array(2, 3, 7, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117);
        $filter = array(2);
        include template('admin/cms/add');
    }

    public function do_add() {
        $data = jget('data');
        if ($data['name']) {
                    } else {
            $this->Messager('名字必须填写');
            return;
        }
		if($data['template'] &&  !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $data['template'])){
			$this->Messager("模板文件名称不合法");
		}
		if($data['template'] && !jclass('jishigou/template')->exists('cms/'.$data['template'])){
			$this->Messager("模板文件 cms/".$data['template'].".html 不存在");
		}
        $data['managename'] = explode('|', $data['managename']);
        $name = explode("\n", $data['name']);
        unset($data['name']);
        $cmslogic = jlogic('cms');
        foreach ($name as $one) {
            $data['catname'] = $one;
            $r = $cmslogic->add_category($data);
        }

        if ($r)
            $this->Messager('创建成功！将返回添加页面。');
        else
            $this->Messager('创建失败！');
    }

    
    public function delete_category() {
        $catid = (int) jget('catid');
        if ($catid < 1) {
            $this->Messager('删除哪个？');
            return;
        }
        $r = jlogic('cms')->delete_category($catid);
        if ($r < 0) {
            $this->Messager('请先删除所有子分类！');
        } else {
            $this->Messager('已删除！');
        }
    }

    
    public function modify() {
        $catid = (int) jget('catid');
        $role = jtable('role')->get();
        $info = jlogic('cms')->get_category_info($catid);
        $info['purview'] = explode(',', $info['purview']);
        $info['filter'] = explode(',', $info['filter']);
        $info['managename'] = str_replace(',', '|', $info['managename']);
                $categoryselect = jlogic('cms')->get_category_html($info['parentid']);


        include template('admin/cms/modify');
    }

    public function do_modify() {
        $data = jget('data');
        $catid = (int) jget('catid');
        if ($data['name']) {
            $data['catname'] = $data['name'];
        } else {
            $this->Messager('名字必须填写');
            return;
        }
		if($data['template'] &&  !preg_match("/^[a-z]+[a-z0-9_]*[a-z0-9]+$/i", $data['template'])){
			$this->Messager("模板文件名称不合法");
		}
		if($data['template'] && !jclass('jishigou/template')->exists('cms/'.$data['template'])){
			$this->Messager("模板文件 cms/".$data['template'].".html 不存在");
		}
        $data['parentid'] = $data['parent_id'];
        unset($data['parent_id']);
        $data['managename'] = explode('|', $data['managename']);
        $r = jlogic('cms')->modify_category($data, $catid);
        if ($r)
            $this->Messager('成功！', 'admin.php?mod=cms');
        else
            $this->Messager('失败！');
    }

    
    public function article_list() {
        $catid = (int) jget('catid');
        $list = jlogic('cms')->get_cat_list($catid);
        $list = $list['list'];
        include template('admin/cms/article_list');
    }

    
    public function article_pass() {
        $article_id = (int) jget('article');
        if ($article_id < 1) {
            $this->Messager('哪一个？');
            return;
        }
        jlogic('cms')->check($article_id,1);
        if (jget('is_ajax')) {
            json_result('完成');
        } else {
            $this->Messager('完成');
        }
    }

    
    public function article_un_pass() {
        $article_id = (int) jget('article');
        if ($article_id < 1) {
            $this->Messager('哪一个？');
            return;
        }
        jlogic('cms')->uncheck($article_id);
        if (jget('is_ajax')) {
            json_result('完成');
        } else {
            $this->Messager('完成');
        }
    }

    
    public function article_delete() {

        $article_id = (int) jget('article');
        if ($article_id < 1) {
            $this->Messager('哪一个？');
            return;
        }
        jlogic('cms')->delete_article($article_id);
        if (jget('is_ajax')) {
            json_result('完成');
        } else {
            $this->Messager('完成');
        }
    }

    
    public function article_modify() {
        $article_id = (int) jget('article');
        if ($article_id < 1) {
            $this->Messager('哪一个？');
            return;
        }
        $article = jlogic('cms')->getarticlebyid($article_id);
        if ($article['imageid']) {
            $article['imageid'] = explode(',', $article['imageid']);
            foreach ($article['imageid'] as $key => $one) {
                $article['imageid'][$key] = jtable('topic_image')->info(array('id' => $one));
            }
        }
        if ($article['attachid']) {
            $article['attachid'] = explode(',', $article['attachid']);
            foreach ($article['attachid'] as $key => $one) {
                $article['attachid'][$key] = jtable('topic_attach')->info(array('id' => $one));
            }
        }
                $categoryselect = jlogic('cms')->get_category_html($article['catid']);
        include template('admin/cms/article_modify');
    }

    public function do_article_modify() {
        $article_id = (int) jget('article');
        if ($article_id < 1) {
            $this->Messager('哪一个？');
            return;
        }

        $data = jget('data');
        if (!$data['title'] || !$data['content']) {
            $this->Messager('标题或者内容不能为空');
        }
        if (!$data['catid']) {
            $this->Messager('请选择分类');
        }
        if ($data['attachid']) {
            $data['attachid'] = implode(',', $data['attachid']);
        }
        if ($data['imageid']) {
            $data['imageid'] = implode(',', $data['imageid']);
        }
        $r = jlogic('cms')->modify($article_id, $data);
        if ($r) {
            $this->Messager('成功', 'admin.php?mod=cms&code=article_list&catid=' . $data['catid']);
        } else {
            $this->Messager('失败');
        }
    }

    
    public function upload_image() {
        if ($_FILES['image']) {
            $r = jlogic('image')->upload(array('pic_field' => 'image'));
        }
        if ($r['error']) {
            echo '<script>parent.show_message("上传失败");</script>';
        } else {
            echo '<script>parent.add_new_up_load("image",{' . "name:'{$r['name']}',id:{$r['id']},src:'{$r['src']}'" . '});</script>';
        }
        exit;
    }

    
    public function upload_attach() {
        if ($_FILES['attach']) {
            $r = jlogic('attach')->upload(array('field' => 'attach'));
        }
        if ($r['error']) {
            echo '<script>parent.show_message("上传失败");</script>';
        } else {
			$fimg = 'images/filetype/'.$r['filetype'].'.gif';
            echo '<script>parent.add_new_up_load("attach",{' . "name:'{$r['name']}',img:'{$fimg}',id:{$r['id']}" . '});</script>';
        }
        exit;
    }

    
    public function search() {
        $key = jget('key');
        if (!trim($key)) {
            $this->Messager('请输入关键字');
            return;
        }
        $list = jlogic('cms')->search_category($key);
        include template('admin/cms/search');
    }

    
    public function search_article() {
        $key = jget('key');
        if (!trim($key)) {
            $this->Messager('请输入关键字');
            return;
        }
        $list = jlogic('cms')->getarticlebysearch($key);
        include template('admin/cms/search_article');
    }

}

?>
