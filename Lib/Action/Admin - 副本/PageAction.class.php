<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-27
 * Time: 上午9:52
 *
 */

class PageAction extends AdminBaseAction
{

    private $pageIds;
    private $id;

    public function __construct()
    {
        parent::__construct();
        $this->pageIds = $this->_get('pageIds');
        $this->id = $this->_get('id') | $this->_post('id');

        //获得模板设置
        $DB_page_setting = M('page_setting');
        $pageSwitch = $DB_page_setting -> find();
        $this->assign('pageSwitch',$pageSwitch);

    }


    public function index()
    {
        //ICE 当前URL参数中获得categoryId 则显示当前ID下内容 如未获得 则显示顶级ID下内容 

        $catId = $this->_get('categoryId');

        /*
        $PageTable = D('Page');
        $pre_page = 10; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $PageTable->count(); // 查询满足要求的总记录数
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageList =  null;

        //fix bug 146
        $orderStr = "create_date desc , id desc";

        if ( empty( $catId ) ){
            $count = $PageTable -> relation(true) ->count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
            $pageList = $PageTable -> relation(true) -> order($orderStr) -> limit($Page->firstRow . ',' . $Page->listRows)->select();
        }else{
            $count = $PageTable -> relation(true) -> where(array('cat_id' => $catId)) -> count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
            $pageList = $PageTable->relation(true)->where(array('cat_id' => $catId))->order($orderStr)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        }

//var_dump($PageTable->getLastSql());

        $show = $Page->show(); // 分页显示输出
        */

        $pageResult = $this->queryPage($catId);

        $this->assign('pages', $pageResult['pageList']);
        $this->assign('page', $pageResult['pageShow']); // 赋值分页输出
        $this->display();
    }

    private function queryPage($catId="" ,$pre_page = "10"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['title'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['cat_id'] = $catId;
        }

        $PageTable = D('Page');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $PageTable->count(); // 查询满足要求的总记录数
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageList =  null;

        //fix bug 146
        $orderStr = "create_date desc , id desc";

        $count = $PageTable -> relation(true)->where($where) ->count(); // 查询满足要求的总记录数
//var_dump($PageTable->getLastSql());
//var_dump($count);
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageList = $PageTable -> relation(true)->where($where) -> order($orderStr) -> limit($Page->firstRow . ',' . $Page->listRows)->select();
//var_dump($PageTable->getLastSql());
        /*
        if ( empty( $catId ) ){
        }else{
            $where['']
            $count = $PageTable -> relation(true) -> where(array('cat_id' => $catId)) -> count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
            $pageList = $PageTable->relation(true)->where(array('cat_id' => $catId))->order($orderStr)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        }
        */

        $resultArr['pageList'] = $pageList;
        $resultArr['pageShow'] = $Page->show();

        return $resultArr;
    }

    public function ajaxSearchPage(){
        $keyword = "";


        $pageResult = $this->queryPage();

        $this->assign('pages', $pageResult['pageList']);

        $pageTabelHtml   = $this->fetch("Tpl/Admin/default/Page/page-table.inc.html");

        $ajaxResult = array("pageTabelHtml"=>$pageTabelHtml ,'pagination'=> $pageResult['pageShow']);

        $this->success($ajaxResult);
    }

    /**
     * 新建页面
     */
    public function create()
    {
        $this->display();

    }

    public function edit()
    {
    /*
     * 编辑内容页面
     */
        $category_model = M('Category');
        $page = D('Page');
        $ret = $page -> where( array( "id" => $this->id ) ) -> find();
        $category = $category_model->where( array( 'catid' => $ret['cat_id']) )->find();
        $this->assign('category',$category);
        $this->assign('page', $ret);
        $this->display();
    }
    public function copy()
    {
    /*
     * 编辑内容页面
     */
        $category_model = M('Category');
        $page = D('Page');
        $ret = $page -> where( array( "id" => $this->id ) ) -> find();
        $category = $category_model->where( array( 'catid' => $ret['cat_id']) )->find();
        $this->assign('category',$category);
        $this->assign('page', $ret);
        $this->display();
    }

    public function deleteIntroduction()
    {
        $news = D('Page');
        $ids = join($this->pageIds, ',');
        $news->delete($ids);
        if ($news->getError()) {
            $this->error($news->getError());
        }
        $this->success('');
    }

    /**
     *
     */
    public function add()
    {
        $page = D('Page');
        $title = $this->_post('title');
        $content = $this->_post('editor');

        $description = $this->_post('description');
        $keywords = $this->_post('keywords');

        $create_date = $this->_post('create_date');

        $create_date = empty($create_date)?date('Y-m-d h:m:s'):$create_date;

        $createDate = strtotime($create_date);
        $category_id = $this->_post('category_id');
        if (isset ($createDate)) {
            $date = date('Y-m-d', $createDate);
        }

        $data = array('title' => $title, 'content' => $content, 'create_date' => $date,
            'description' => $description, 'keywords' => $keywords, 'id' => $this->id , 'cat_id' => $category_id , 'pinyin'=>Pinyin($title)) ;


        if (empty ($this->id)) {
            $ret = $page->add($data);
        } else {
            $ret = $page->create($data);
        }

        $page->save();

        if ($ret) {
            $this->success($page->getDbError());
        } else {
            $this->error($page->getDbError());
        }

    }

    public function deletePage()
    {
        /*
         * ICE 2013-06-28
         * 通过获得的ID删除页面
         */
        $DB_page = M('page');
        $ids = join($this->_post('PageIds'), ',');
        $res = $DB_page-> where("`id` IN ($ids)") -> delete();
        if ($res) {
            $this->success('删除成功');
        }
        else {
            $this->error('删除失败');
        }
    }

    public function teamplate()
    {
        $ret = M('PageSetting')->find();
        $this->assign('template',$ret);
        $this->display();

    }

    public function saveTemplate(){
        $name = $this->_get('name');
        $checked = $this->_get('checked');

        $checkedBit = 0;
        if ($checked == "true"){
            $checkedBit = 1;
        }

        $ret = M('PageSetting')->find();
        $templateModel =  M('PageSetting');
        $ret[$name] = $checkedBit;
        $templateModel->save($ret);
        $this->success();
    }
}