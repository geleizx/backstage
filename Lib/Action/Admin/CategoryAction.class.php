<?php

/**
 * 类别设置
 * @author Gavin
 *
 */
class CategoryAction extends AdminBaseAction
{

    private $moduleId;
    private $tabIndex;
    private $parentId;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->moduleId = $this->_get('moduleId');
        //fix 没有顶级分类问题。
        $this->tabIndex = I("get.tabIndex",-1);
        $this->parentId = $this->_get('parentId');
        $this->db = D("Category");
    }




    public function ajaxSearchIsmenu()
    {
        $keyword = "";
        $moduleId = $this->_post("moduleId");
        $type = $this->_post("type");

        $where = array();
        $where['module'] = $moduleId;

        $relation = false;
            $where['status'] =array("eq",2);
            $categoryModel = D('Category');
            $category = $categoryModel->relation($relation)->where($where)->select();
        $ret = $this->getTree($category);

        $this->assign('ret', $ret);

        $categoryTabelHtml = $this->fetch("Tpl/Admin/default/Category/category-table.inc.html");

        $this->success($categoryTabelHtml);

    }
    public function ajaxSearchCategory()
    {
        $keyword = "";
        $keyword = $this->_post("keyword");
        $moduleId = $this->_post("moduleId");
        $type = $this->_post("type");

        $where = array();

        $where['module'] = $moduleId;

        $relation = false;

        if (!empty($keyword)) {
            $where['catname'] = array("LIKE", '%' . $keyword . '%');

            $categoryModel = D('Category');
            $category = $categoryModel->relation($relation)->where($where)->select();
        } else {
            $category = $this->queryCategory(0, $type, $moduleId);
        }

        $ret = $this->getTree($category);

        $this->assign('ret', $ret);

        $categoryTabelHtml = $this->fetch("Tpl/Admin/default/Category/category-table.inc.html");
        $this->success($categoryTabelHtml);

    }


    private function queryCategory($parentid = 0, $type = 0, $model = "")
    {
        import('ORG.Util.Page');// 导入分页类
        $category = array();

        if (!empty($model)) {
            $where['module'] = $model;
        }
        $where['parentid'] = $parentid;
//        $where['type'] = $type;
        $categoryModel = D('Category');
        $count = $categoryModel->where($where)->count();// 查询满足要求的总记录数

        $Page  = new Page($count,800);// 实例化分页类 传入总记录数和每页显示的记录数
//        $Page->setConfig('header','item');
//        $Page->setConfig('prev','prev');
//        $Page->setConfig('next','next');
//        $Page->setConfig('fitst','first');
//        $Page->setConfig('last','last');
        $Page->setConfig('theme',"  %upPage% %first% %prePage% %linkPage% %nextPage% %end% %downPage% ");
        $show = $Page->show();// 分页显示输出
        $category = $categoryModel->relation(true)->where($where)->order("listorder  desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($category as $key => $categoryObj) {
            if (!empty($categoryObj['childs'])) {
                foreach ($categoryObj['childs'] as $ckey => $childObj) {
                    $category[$key]['childs'][$ckey]['childs'] = $this->queryCategory($childObj['catid'], $type, $model);
                }
            }
            continue;
        }
        return $category;
    }

    //排序
    public function changeSort(){
        $categoryModel = M('category');
        $itemid = I('itemid');
        $newtxt = I('newtxt');
        $data['listorder'] =$newtxt;
        $res = $categoryModel->where('catid = '.$itemid)->save($data);
        $this->ajaxReturn($res);
    }

    private function getTree($category, $tab = 0)
    {
        $strHtml = "";
        $str = "";
        $LANG = l('status');
        while ($cat = array_pop($category)) {
            $status = $cat['status'];
            $moduleHtml = "";
            if ($cat['type'] > 0) {
                $moduleStr = $this->findModule($cat['module']);
                $moduleHtml .= '<td>' .
                    $moduleStr .
                    '</td>';
            }
            if($cat['parentid'] ==0){
                $listorder = $cat['listorder'];
                $pid = $cat['catid'];
            }else{
                $listorder = $cat['listorder'];
                $pid = $cat['catid'];
            }
            if($cat['image'] !=''){
                $imgDir = '<img src='.$cat['image'].' style="width:5%;height:20%">';
            }else{
                $imgDir='';
            }
            $str .= '<tr class="category_tab_id_' . $tab . '_' . $cat['parentid'] . '" id="' . $cat['catid'] . '">' .
                '<td>' .
                '<input type="checkbox" name="Page_checkbox" id="Page_' . $cat['catid'] . '">' .
                '</td>' .
                '<td> ' .
                str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $tab) . $cat['catname']. '&nbsp;' .$imgDir.'&nbsp;--'.$pid.
                '<a href="javascript:void(0)" alt="删除" id="page_delete_' . $cat['catid'] . '" class="del"> × </a>&nbsp;' .
                '</td>' .
                $moduleHtml .
                '<td id="'. $cat['catid'] .'"> ' .
                 $listorder
                 .
                '</td>' .
                '<td>' .
                $cat['hits'] .
                '</td>' .
                '<td>' .
                date("Y-m-d", strtotime($cat['create_time'])) .
                '</td>' .
                '<td>' .
                $LANG[$status] .
                '</td>' .
                '<td>' .
                '<a href="' . U('Category/modify?id=' . $cat['catid']) . '"> 编辑 </a>' .
                $addContentHtml .
                '</td>' .
                '</tr>';

            if ($cat['childs'] != null) {
                $str .= $this->getTree($cat['childs'], $tab + 1);
            }
        }
        if (!empty($str)) {
            $strHtml .= "<tbody class='datalistclass'>";
            $strHtml .= $str;
            $strHtml .= "</tbody>";
            $str = "";
        }
        return $strHtml;
    }

    private function findModule($moduleId = "")
    {
        $moduleObj = M('module');
        $module = $moduleObj->where(array('id' => $moduleId))->find();

        $moduleStr = $module['name'];
        return $moduleStr;
    }

    /**
     *
     */

    public function index()
    {
        $type = 0;
        $where = array();
        if (!empty($this->moduleId)) {
            $where['module'] = $this->moduleId;
            $this->assign('moduleId', $this->moduleId);
        } else {
            $this->assign('moduleId', 0);
        }

        $typeParam = $this->_get('type');
        if ($typeParam > 0) {
            $type = $typeParam;
        }
        $this->assign('type', $type);
        $this->assign('moduleList', $this->getModuleList());
        $where['parentid'] = 0;
        $category = $this->queryCategory(0, $type, $this->moduleId);
        $pindaoCount = M('category')->where("parentid != 0")->count();

        $this->assign('pindaoCount', $pindaoCount);
        $ret = $this->getTree($category);
        $this->assign('ret', $ret);
        $this->display();
    }
/////////////////////////////////////////////////////////////////////////////


    private function getModuleList()
    {
        $moduleObj = M('module');
        $moduleList = $moduleObj->select();

        $moduleArr = array();

        foreach ($moduleList as $key => $moduleList) {
            $moduleArr[$moduleList['id']] = $moduleList['name'];
        }
        return $moduleArr;
    }

    /**
     *
     */
    public function create()
    {

        if ($this->moduleId)
            $where['module'] = $this->moduleId;

        $this->assign("categories", $this->db->where($where)->select());
        $this->assign("moduleid", $this->moduleId);
        $this->display("Tpl/Admin/default/category-sou.html");
    }


    public function create_new()
    {
        if ($this->moduleId)
        $where['module'] = $this->moduleId;
        $this->assign("categories", $this->db->where($where)->select());
        $this->assign("moduleid", 19);
        $this->display("Tpl/Admin/default/category-new.html");
    }


    public function modify()
    {

        $id = $this->_get('id');
        $category = D('Category')->relation(true)->find($id);
        $modelid = $category['module'];
        $where['module'] = $modelid;
        $this->assign("categories", $this->db->where($where)->select());
        $this->assign("moduleid", $modelid);

        $parent = array('catid' => 0, 'catname' => '顶级分类');

        if ($category['parentid'] != 0) {
            $parent = D('Category')->relation(true)->find($category['parentid']);
        }

        $this->assign("category", $category);
        $this->assign('parent', $parent);
        if($modelid==3){
            $this->display("Tpl/Admin/default/category-sou-sou.html");
        }else{
            $this->display("Tpl/Admin/default/category-news-new.html");
        }
    }


    /**
     * Ajax 拖动排序
     *
     * @Author
     * @date
     */
    public function sortCategory()
    {

        $catIds = $this->_post('after');

        foreach ($catIds as $sortKey => $catId) {

            //  $QuickMenuModelT = M('menu');
            $catelogyM = D("Category");

            $catObj['listorder'] = $sortKey;
            //  $catObj['catid'] = $catId;

            //$catelogyM->save($catObj);
            $catelogyM->relation(true)->where(array('catid' => $catId))->save($catObj);

//var_dump($catelogyM->getLastSql());
        }
        $this->success(1);
    }


    /**
     * Ajax 获得Tree
     */
    public function ajaxListCategory()
    {
        if ($this->moduleId)
            $where['module'] = $this->moduleId;
        $categories = $this->db->where($where)->select();

        $data = array();
        foreach ($categories as $n => $catelogy) {
            $node = array();
            if ($catelogy["parentid"] == 0) {
                $node = array(id => $catelogy["catid"], pId => $catelogy["parentid"], name => $catelogy["catname"],
                    //t =>$catelogy["description"], open=>true,click=>false);
                );

            } else {
                $node = array(id => $catelogy["catid"], pId => $catelogy["parentid"], name => $catelogy["catname"],
                    //t =>$catelogy["description"], open=>false,click=> true);
                );
            }
            array_push($data, $node);
        }
        $this->success($data);
    }


    /**
     * Ajax 获得类型数据
     */
    public function ajaxGetCategory()
    {
        $where = array();

        if (isset($this->moduleId) && !empty($this->moduleId))
            $where['module'] = $this->moduleId;

//        if (isset($this->parentId) && !empty($this->parentId))
            $where['parentid'] = $this->parentId;

        //顶级分类类型
        $top = "";

        switch ($this->moduleId){
            case 19:
                $top = "顶级分类";
                break;
            case 3 :
                $top = "顶级分类";
                break;
            case 2 :
                $top = "顶级分类";
                break;
        }


        if ( $this->tabIndex == 0 &&  $this->parentId == 0) {
            return ;
        }


        //$where['parentid'] = $this->parentId;

//        if ( $this->parentId == '0'){
//            return ;
//        }

        $categories = $this->db->where($where)->select();
        $index = 0;
        if (is_numeric($this->tabIndex)) {
            $index = intval($this->tabIndex) + 1;
        }

        $ret = '';

//		if ( ! $categories)
//			$this->success($ret);

        $ret = "<li class=\"cc-list-item\" tabindex=\"" . $index . "\" style=\"\" > <div role=\"tree\" class=\"cc-tree\" >" .
            "<ul role=\"listbox\" tabindex=\"0\" hidefocus=\"-1\" unselectable=\"on\" class=\"cc-tree-cont\">" .
            "<li id=\"cc-tree-group-id\" class=\"cc-tree-group\" aria-expanded=\"false\" role=\"treeitem\"> " .
            "<ul class=\"cc-tree-gcont\" role=\"group\"> ";

//        $ret .= '<li id="create_0" ' . '" style="padding: 0 16px 0 14px;"  parentid="' . ($this->parentId ? $this->parentId : 0) . '" class="create_btn"  >' .
//            '<i class="icon-plus-2" ></i>新建  ' . '</li>';
//fix bug ITHINK-177

        //如果不是编辑结构的时候，这里需要显示顶级分类  1 2 3 4 5 6 7
        if ( $this->tabIndex == -1 ) {
            $ret .= "<li id=\"item-" . 0 . "\" class=\"cc-tree-item cc-hasChild-item\" role=\"treeitem\" style=\"\">" . $top . "</li>";
        }

        foreach ($categories as $n => $category) {
            $ret .= "<li id=\"item-" . $category['catid'] . "\" class=\"cc-tree-item cc-hasChild-item\" role=\"treeitem\" style=\"\">" . $category['catname'] . "</li>";
        }

        $ret .= "</ul></li></ul> </div></li>";
        $this->success($ret);

    }

    public function dialogDiv()
    {
        $this->display("Tpl/Admin/default/category-dialog.html");
    }


    private function image()
    {
        thumb($src_file, $new_width, $new_height);
    }


    public function managment()
    {
        $this->display("Tpl/Admin/default/category-managment.html");
    }


    public function ajaxGetManagementCategory()
    {
        $categoryModel = M('Category');

        $catelogy = M("Category");
        if ($this->moduleId)
            $where['module'] = $this->moduleId;
        $categories = $catelogy->where($where)->select();

        $data = array();

        foreach ($categories as $n => $catelogy) {
            $node = array();
            if ($catelogy["parentid"] == 0) {
                $node = array(id => $catelogy["catid"], pId => $catelogy["parentid"], name => $catelogy["catname"],
                    t => $catelogy["description"], open => true, click => false);
            } else {
                $node = array(id => $catelogy["catid"], pId => $catelogy["parentid"], name => $catelogy["catname"],
                    t => $catelogy["description"], open => false, click => true);
            }
            array_push($data, $node);
        }
        $this->success($data);
    }

    public function  add()
    {
        if (!$this->_post('catname'))
            $this->error("请正确输入类别名称！");

        $catelogy = D("Category");
        $data['parentid'] = $this->_post("category_id");

        $albumid = $this->_post("albumid");
        $id = $this->_post("catid");
        if ($this->_post("category_id") == 10000) {
            $data['parentid'] = 0;
        }
        if( $this->_post("title")!=''){

            $catname = $this->_post("title");
            $catname = explode(",",$this->_post("title"));
            $result_name=array_pop($catname);
            $count =  count($catname);
            for($i=0;$i<$count;$i++){
                $catname[$i]   = explode("-",$catname[$i]);
                $data['albumid'] = $catname[$i][0];
                $data['catname'] = $catname[$i][1];
                $isNav = $this->_post("is_nav");
                $isLogo = $this->_post("islogo");
                $data['module'] = $this->_post("moduleid");
                $data['create_time'] = $this->_post("create_date");
                $data['catid'] = $this->_post('catid');
                $data['status'] = $this->_post('status');
                $data['listorder'] = $this->_post('listorder');
                if ($isNav == 2) {
                    $data['status'] = 2;
                }
                if ($isLogo == 1) {
                    $data['islogo'] = 1;
                }else{
                    $data['islogo'] = 0;
                }
                $picture = $this->_post('picture');
                $data['image'] = $picture;

                if (empty($data['status'])) {
                    $data['status'] = 0;
                }
                $id = $this->_post("catid");
                if (empty($id)) {
                    $catelogy ->add($data);
                } else {
                    $catelogy ->where(array('catid' => $id))->save($data);
                }

            }
        }else{
            if($this->_post('category_id')==0&&$this->_post("moduleid")==19&&$id==''){   //创建影视专辑 同时创建影视直播分类
                $catname = I("catname");
                 $where['catname'] =$catname;
                 $where['module'] =2;
                 $where['parentid'] = 0;
                 $where['status'] = 1;
                $where['listorder'] = 6666;
                $isOnlyCatname =  M('category')->where("catname = '$catname' and parentid = 0 and module=2")->find();
                if($isOnlyCatname){
                    $this->error('直播频道存在同名专辑');
                    exit;
                }else{
                    $addLiveCategory = M('category')->add($where);
                }
            }else{


            }

            $data['catname'] = I("catname");
            $isNav = $this->_post("is_nav");
            $isLogo = $this->_post("islogo");
            $data['module'] = $this->_post("moduleid");
            $data['create_time'] = $this->_post("create_date");
            $data['catid'] = $this->_post('catid');
            $data['status'] = $this->_post('status');
            $data['listorder'] = $this->_post('listorder');
            if ($isNav == 2) {
                $data['status'] = 2;
            }
            if ($isLogo == 1) {
                $data['islogo'] = 1;
            }else{
                $data['islogo'] = 0;
            }
            $picture = $this->_post('picture');
            $data['image'] = $picture;

            if (empty($data['status'])) {
                $data['status'] = 0;
            }

            if (empty($id)) {
                $catelogy ->add($data);
            } else {
                $catelogy ->where(array('catid' => $id))->save($data);
                if($catelogy&&$albumid){
                    $info['catname'] = I("catname");
                    $updateCatname = M('category')->where("catid = $albumid")->save($info);
                }else{
                    $info['catname'] = I("catname");
                    $updateCatname = M('category')->where("albumid =$id ")->save($info);
                }

            }
        }

        $info = array('');
        $this->success('');
    }


    public function  categoryTree()
    {

        $module = $this->_get('moduleId');
        $type = $this->_get('type');
        if (!empty($module)) {
            $where['module'] = $module;
        }
        $where['type'] = $type;
        $where['parentid'] = 0;

        $ret = $this->db->relation(true)->where($where)->select();
        $this->success($ret);
    }

    public function delCategoryAct()
    {
        /*
         * ICE 2013-07-05 删除指定ID的分类
         * @param AJAX POST id
         */
        $id = $this->_post('id');
        $DB_category = M('category');
        $res = $DB_category->where("`catid` = $id")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    ////////////////////////////////////////////////////////
    public function deleteCategory()
    {
        /*
         * ICE 2013-06-28
         * 通过获得的ID删除页面
         */
        $DB_page = M('category');
        $ids = join($this->_post('PageIds'), ',');
        $res = $DB_page->where("`catid` IN ($ids)")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    ////////////////////////////////////////////////////////
    public function deleteCategory1()
    {
        /*
         * ICE 2013-06-28
         * 通过获得的ID删除页面
         */
        $DB_page = M('category1');
        $ids = join($this->_post('PageIds'), ',');
        $res = $DB_page->where("`catid` IN ($ids)")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //////////////////////////////////////////////////////////

    public function CategoryAct()
    {
        /*
         * 	ICE 2013-07-05 快速添加分类
         *  @param AJAX POST pId , name
         */
        $data['parentid'] = $this->_post('pId');
        $data['module'] = $this->_post('module');
        $data['type'] = $this->_post('type');
        $data['listorder'] = $this->_post('listorder');
        $data['catname'] = $this->_post('name');
        $data['status'] = $this->_post('status');
        //dump ($data);
        $DB_category = M('category');
        $res = $DB_category->add($data);

        if ($res) {
            $this->success(array(data => '添加成功', "id" => $DB_category->getLastInsID()));
        } else {
            $this->error('添加失败');
        }
    }
    ///////////////////////////////////////////////////////////////////
    public function editCategoryAct()
    {
        /*
         * 	ICE 2013-07-05 修改分类名
         *  @param AJAX POST pId , name
         */
        $id = $this->_post('id');
        $data['catname'] = $this->_post('name');
        //dump ($data);
        $DB_category = M('category');
        $res = $DB_category->where("`catid` = $id")->save($data);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }
    ////////////////////////////////////////////////////////////////////////
    public function ajaxCategory(){
        $keyword = I('keyword');
        $categroy = M('category');

        $catlist = $categroy->where('parentid = 0')->select();
        foreach($catlist as $value){
            $catIds[] = $value[catid];
        }
        $caids = implode(",",$catIds);
        $where['parentid'] = array("in",$caids);
        $where['status'] = array("gt",0);
        $where['catname'] = array("like","%".$keyword."%");
        $where['module'] = array("eq",2);
        $result =$categroy->where($where)->field("catname,catid")->select();
        foreach($result as $v){
            $suggestions[]= array('title' => $v['catname'],'id'=>$v['catid']);
        }
        echo json_encode(array('data' => $suggestions));
    }


    public function  addCategory()
    {
        if (!$this->_post('catname'))
            $this->error("请正确输入类别名称！");

        $catelogy = D("Category");
        $data['parentid'] = $this->_post("category_id");

        if ($this->_post("category_id") == 10000) {
            $data['parentid'] = 0;
        }

        $isNav = $this->_post("is_nav");

        $data['catname'] =$this->_post("catname");
        $isNav = $this->_post("is_nav");
        $isLogo = $this->_post("islogo");
        $data['module'] = $this->_post("moduleid");
        $data['create_time'] = $this->_post("create_date");
        $data['catid'] = $this->_post('catid');
        $data['status'] = $this->_post('status');
        $data['listorder'] = $this->_post('listorder');
        if ($isNav == 2) {
            $data['status'] = 2;
        }
        if ($isLogo == 1) {
            $data['islogo'] = 1;
        }else{
            $data['islogo'] = 0;
        }
        $picture = $this->_post('picture');
        $data['image'] = $picture;


        if (empty($data['status'])) {
            $data['status'] = 0;
        }

        $id = $this->_post("catid");

        if (empty($id)) {
//             $catelogy->add($data);
            $catelogy ->add($data);
        } else {
            $catelogy ->where(array('catid' => $id))->save($data);
        }

        $info = array('');

        $this->success();
    }

}
