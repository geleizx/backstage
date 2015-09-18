<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mackcyl
 * Date: 13-10-24
 * Time: 上午11:36
 * To change this template use File | Settings | File Templates.
 */

class RoleAction extends AdminBaseAction{

    private $modelCodeC = array(
        'Category' => '0',
        'Product' => '1',
        'Page'=>'2',
        'News'=>'3',
    );

    /**
     * Ajax 获得类型数据
     */
    public function ajaxGetCategoryRole()
    {

        $categoryModel = M('category');

        $moduleId = I('moduleId',0);
        $parentId = I('parentId',0);

        $where = array();
        if (isset($moduleId) && !empty($moduleId))
            $where['module'] = $moduleId;
        if (isset($parentId) && !empty($parentId))
            $where['parentid'] = $parentId;
        if ( $parentId == '100000'){
            return ;
        }
        $tabIndex = I('tabIndex',0);
        $index = 0;
        if (is_numeric($tabIndex)) {
            $index = intval($tabIndex) + 1;
        }

        $categories = $categoryModel->where($where)->select();
        $roleHtml = $this->createSelectHtml($categories,0,$index);

        $this->success($roleHtml);

    }
    /**
     * Ajax 获得类型数据
     */
    public function ajaxGetProductRole()
    {

        $productModel = M('product');

        $category_id = I('category_id',0);

        $where = array();
        if (isset($category_id) && !empty($category_id))
            $where['category_id'] = $category_id;

        $tabIndex = I('tabIndex',0);
        $index = 0;
        if (is_numeric($tabIndex)) {
            $index = intval($tabIndex) + 1;
        }

        $products = $productModel->where($where)->select();
        $roleHtml = $this->createSelectHtml($products,1,$index);

        $this->success($roleHtml);

    }

    public function ajaxGetPageRole()
    {

        $productModel = M('page');

        $category_id = I('category_id',0);

        $where = array();
        if (isset($category_id) && !empty($category_id))
            $where['cat_id'] = $category_id;

        $tabIndex = I('tabIndex',0);
        $index = 0;
        if (is_numeric($tabIndex)) {
            $index = intval($tabIndex) + 1;
        }

        $products = $productModel->where($where)->select();
        $roleHtml = $this->createSelectHtml($products,2,$index);

        $this->success($roleHtml);

    }

    public function ajaxGetNewsRole()
    {

        $productModel = M('news');

        $category_id = I('category_id',0);

        $where = array();
        if (isset($category_id) && !empty($category_id))
            $where['catid'] = $category_id;

        $tabIndex = I('tabIndex',0);
        $index = 0;
        if (is_numeric($tabIndex)) {
            $index = intval($tabIndex) + 1;
        }

        $products = $productModel->where($where)->select();
        $roleHtml = $this->createSelectHtml($products,3,$index);

        $this->success($roleHtml);

    }


    public function ajaxDelRole(){
        $cat_name = I('category_name','');
        $role_id = I('role_id','0');
        $group_id = I('group_id','0');
        $model_name = '';
        switch($cat_name){
            case 'cat': //分类
                $model_name = 'Category';
                break;
            case 'product': //产品
                $model_name = 'Product';
                break;
            case 'page': //页面
                $model_name = 'Page';
                break;
            case 'news': //文章
                $model_name = 'News';
                break;
        }

        if(empty($model_name) || empty($role_id) || empty($group_id)){
            $this->error('参数错误');
        }






        $where['model_name'] = $model_name;
        $where['group_id'] = $group_id;
        $where['role_id'] = $role_id;


        $model = M("group_role");

        $roleObj = $model->where($where)->find();
        if(!empty($roleObj)){
            $result = $model->where($where)->delete();
            if($result){
                $this->success('成功');
            }
        }
        $this->error('删除失败,对象未找到!有种你New一个');

    }


    private function createSelectHtml($list , $type=0,$index=0){

        $roleList = array();

        foreach($list as $key=>$value){
            switch($type){
                case 0: //分类
                    $roleList[$key]['id'] = $value['catid'];
                    $roleList[$key]['name'] = $value['catname'];
                break;
                case 1: //产品
                    $roleList[$key]['id'] = $value['id'];
                    $roleList[$key]['name'] = $value['product_name'];
                break;
                case 2: //页面
                    $roleList[$key]['id'] = $value['id'];
                    $roleList[$key]['name'] = $value['title'];
                break;
                case 3: //文章
                    $roleList[$key]['id'] = $value['id'];
                    $roleList[$key]['name'] = $value['title'];
                break;

            }

        }

        $this->assign('roleList',$roleList);
        $this->assign('type',$type);
        $this->assign('index',$index);

        $roleHtml = $this->fetch("Tpl/Admin/default/role-inc.html");

        return $roleHtml;
    }

}