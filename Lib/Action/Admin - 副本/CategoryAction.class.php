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
//        $category = $categoryModel->relation(true)->where($where)->select();


//var_dump($categoryModel->getLastSql());
//print_r($category);

        $ret = $this->getTree($category);

        $this->assign('ret', $ret);

        $categoryTabelHtml = $this->fetch("Tpl/Admin/default/Category/category-table.inc.html");
//        Tpl/Admin/default/Category/category-table.icn.html

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
//        $category = $categoryModel->relation(true)->where($where)->select();


//var_dump($categoryModel->getLastSql());
//print_r($category);

        $ret = $this->getTree($category);

        $this->assign('ret', $ret);

        $categoryTabelHtml = $this->fetch("Tpl/Admin/default/Category/category-table.inc.html");
//        Tpl/Admin/default/Category/category-table.icn.html

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
            }else{
                $listorder = $cat['listorder'];
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
                str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $tab) . $cat['catname']. '&nbsp;' .$imgDir.
                '<a href="javascript:void(0)" alt="删除" id="page_delete_' . $cat['catid'] . '" class="del"> × </a>&nbsp;' .
                '</td>' .
                $moduleHtml .
                '<td id="'. $cat['catid'] .'"> ' .
                 $listorder
                 .
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
        $this->display("Tpl/Admin/default/category-new.html");
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
        $this->display("Tpl/Admin/default/category-news-new.html");
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
                $top = "新闻资讯";
                break;
            case 3 :
                $top = "页面";
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

        if ($this->_post("category_id") == 10000) {
            $data['parentid'] = 0;
        }

        $isNav = $this->_post("is_nav");

        $data['module'] = $this->_post("moduleid");
        $data['catname'] = $this->_post("catname");
        $data['description'] = $this->_post("description");
        $data['create_time'] = $this->_post("create_date");
        $data['catid'] = $this->_post('catid');
        $data['status'] = $this->_post('status');
        $data['listorder'] = $this->_post('listorder');
        $data['tv_id'] = $this->_post('catid');
        $data['tv_type'] =  $this->_post("category_id");
        if ($isNav == 2) {
            $data['status'] = 2;
        }


        //cut pic

        $x = $this->_post('x');
        $y = $this->_post('y');
        $x1 = $this->_post('x2');
        $y1 = $this->_post('y2');

        $w = $this->_post('w');
        $h = $this->_post('h');

        //pic name

        //ok
        if ($x1 && $y1 && $w && $h) {
            $ratio = $this->_post('ratio');
            $ratioArray = split('_', $ratio);
            $picture = $this->_post('picture');
            $count = count(explode("/", $picture));
            $picture = explode("/", $picture);
            $picture = $picture [$count - 1];
            $picture = 'uploads/' . date(Ymd) . '/' . $picture;

            $file = thumb(APP_PATH . $picture, $w, $h, 0, 0, $x, $y);

            import('ORG.Util.Image');
            $realW = $ratioArray[0];
            $realH = $ratioArray[1];

            Image::thumb($file, $file, '', intval($realW), intval($realH), false);

            $fileCount = count(explode("/", $file));
            $file = explode("/", $file);
            $file = $file [$fileCount - 1];

            $data['picture'] = 'uploads/' . date(Ymd) . '/' . $file;
            $data['image'] = $picture;
            $data['pic_ratio'] = $ratio;
        }
        $picture = $this->_post('picture');
        $data['image'] = $picture;
//        if ($this->_post('status'))
//            $data['status'] = 1;
//        else
//            $data['status'] = 0;

        if (empty($data['status'])) {
            $data['status'] = 0;
        }

// die("313");
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

    public function deleteRss()
    {
        $id = $this->_get('id');
        M('AdminRss')->delete($id);
        $this->success('');
    }

    public function ajaxGetRss()
    {
        $id = $this->_get('id');
        $data = M('AdminRss')->find($id);
        $this->success($data);
    }


    public function ajaxAddRss()
    {
        M('AdminRss')->save($_POST);
        $this->success($data);
    }

    public function updateAllRss()
    {
        $id = $this->_get('id');
        $catgoryModel = D('category');
        $category = $catgoryModel->relation(true)->where(array('catid' => $id))->find();

        for ($i = 0; $i < count($category['rssdata']); $i++) {
            $this->pushRssToDb($category['rssdata'][$i]);
        }
    }

    private function pushRssToDb($data)
    {
        import('@.Tool.Rss');
        $rss = new Rss();
        $rss->cp = 'utf-8';
        $rssData = $rss->Parse($data['url']);
        $keywords = $data['keywords'];
        $cat_id = $data['cat_id'];
        $newsData = array();

        foreach ($rssData['items'] as $key => $value) {
            //echo $value['title'];
            if (mb_strpos($value['title'], $keywords, 0, 'utf-8')) {


                $news_find = D('News');
                $finded = $news_find->where(array('title' => $value['title'], 'catid' => $cat_id))->find();

                if ($finded) {

                    continue;
                }

                //处理入库
                $news = array();
                $news['title'] = $value['title'];
                //   $news['description'] =
                $news['catid'] = $cat_id;
                $news['inputtime'] = time();
                $news['newsdata'] = array('content' => $value['description']);
                $newsModel = D('News');
                $newsModel->relation('newsdata')->add($news);
                //echo $newsModel->getLastSql();
                //echo $newsModel->getDbError();

            }
        }
    }

    /**
     *
     * 导出Excel
     */
//    function expFile(){//导出Excel
//        $xlsName  = "Category";
//        $xlsCell  = array(
//                array('catid','id'),
//            array('parentid','vst_id'),
//            array('catname','频道名称'),
//
//        );
//        $xlsModel = M('category');
//        $store_name = $_SESSION['user']['nickname'];
//        if($store_name='管理员'){
////            $xlsData  = $xlsModel->field('catname')->select();
//            $xlsData = M()->table('tp_product a,tp_category b')->where(" ")->field('product_name,category_id')->select();
//
//            foreach($xlsData as $key=>$value){
//                $subList[] = M()->table('tp_product')->where("category_id = ".$xlsData[$key]['catid'])->field('product_name,category_id')->select();
//            }
//            $string = implode(",",$subList);
//        }else{
//            $xlsData  = $xlsModel->where("store_name = '$store_name'")->field('catname')->select();
//        }
//        foreach ($xlsData as $k => $v)
//        {
//            $xlsData[$k]['catid'];
//            $xlsData[$k]['parentid'];
//            $string = implode(",",$xlsData[$k]['sub']);
//        }
//        exit;
//        $this->exportExcel($xlsName,$xlsCell,$xlsData);
//
//    }

    function impFile(){
        if (!empty($_FILES)) {
            import("ORG.Net.UploadFile");
            $config=array(
                'allowExts'=>array('xlsx','xls'),
                'savePath'=>'./Public/upload/',
                'saveRule'=>'time',
            );
            $upload = new UploadFile($config);
            if (!$upload->upload()) {
                $this->error($upload->getErrorMsg());
            } else {
                $info = $upload->getUploadFileInfo();
            }

//            vendor("PHPExcel.PHPExcel");
            import('ORG.PHPExcel.PHPExcel');//
            $file_name=$info[0]['savepath'].$info[0]['savename'];
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            for($i=2;$i<=$highestRow;$i++)
            {
                $data['parentid'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                $data['catname'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                $data['module'] =2;
                $data['ismenu'] =2;
                $data['status'] =1;
                $data['create_time'] =date("Y-m-d H:i:s",time());
                M('Category')->add($data);
            }
            $this->success('导入成功！');
        }else
        {
            $this->error("请选择上传的文件");
        }
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        ob_end_clean();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['user']['nickname'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
//        vendor("PHPExcel.PHPExcel");
        import('ORG.PHPExcel.PHPExcel');//
        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
