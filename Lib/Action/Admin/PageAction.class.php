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
        for($i=0;$i<6;$i++){
            $cid  = $i+1969;
            $tv[$i] = M('page')->where("cid = $cid")->count();
        }
        $this->assign('tv',$tv);
        $pageResult = $this->queryPage($catId);
        $this->assign('type', $pageResult['catArray']);
        $this->assign('pages', $pageResult['pageList']);
        $this->assign('page', $pageResult['pageShow']); // 赋值分页输出
        $this->display();
    }

    private function queryPage($catId="" ,$pre_page = "15"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['title'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['cid'] = $catId;
        }

        $PageTable = D('Page');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $PageTable->count(); // 查询满足要求的总记录数
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageList =  null;

        //fix bug 146
        $orderStr = "  id asc";

        $count = $PageTable -> relation(true)->where($where) ->count(); // 查询满足要求的总记录数

        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pageList = $PageTable -> relation(true)->where($where) -> order($orderStr) -> limit($Page->firstRow . ',' . $Page->listRows)->select();
       foreach($pageList as $k=>$val){
           $filmType =  $this->pageType();
           $catList = explode(",",$val['cat']);
           $pageList[$k]['catList'] = $catList;
          if($val['cid']==1969){
              $resultArr['catArray'] = $filmType[0];
          }elseif($val['cid']==1970){
              $resultArr['catArray'] = $filmType[1];
          }
       }

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

    public function pageType(){
        $Film = array(
              '喜剧',  '爱情',  '动作',   '恐怖', '科幻',  '剧情',  '冒险',   '犯罪',   '奇幻',  '战争',
              '悬疑', '动画',   '文艺',   '伦理',   '纪录', '传记',   '歌舞',   '古装', '武侠',
               '历史',   '惊悚', '其它'
        );
        $Tv = array(
             '言情',  '剧情', '伦理',  '喜剧',   '悬疑',   '都市',  '乡村',  '偶像',   '古装',  '军事',   '警匪',
             '历史',   '武侠',  '科幻', '奇幻',   '情景',  '动作',  '神话',  '谍战',   '其它'
        );
        return array("0"=>$Film,"1"=>$Tv);
    }


    /**
     * 新建页面
     */
    public function create()
    {
        $data = $this->pageType();
        $this->assign("Film",$data[0]);
        $this->assign("Tv",$data[1]);

        $this->display();
    }

    public function edit()
    {
        $data = $this->pageType();
    /*
     * 编辑内容页面
     */
        $category_model = M('Category');
        $page = D('Page');
        $ret = $page -> where( array( "id" => $this->id ) ) -> find();

        $retuuid = M('pagetype')->where("aid =  $this->id")->field('mkey,type,aid')->select();
        foreach($retuuid as $k=>$v){
            if($v['type']==1){
                $retuuid[$k]['uname'] = "VST视频";
                $retuuid[$k]['type'] = "1";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==2){
                $retuuid[$k]['uname'] = "百事通视频";
                $retuuid[$k]['type'] = "2";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==3){
                $retuuid[$k]['uname'] = "兔子视频";
                $retuuid[$k]['type'] = "3";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==4){
                $retuuid[$k]['uname'] = "电视猫视频";
                $retuuid[$k]['type'] = "4";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==5){
                $retuuid[$k]['uname'] = "华数视频";
                $retuuid[$k]['type'] = "5";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==6){
                $retuuid[$k]['uname'] = "优酷视频";
                $retuuid[$k]['type'] = "6";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==7){
                $retuuid[$k]['uname'] = "腾讯视频";
                $retuuid[$k]['type'] = "7";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==8){
                $retuuid[$k]['uname'] = "蜜蜂视频";
                $retuuid[$k]['type'] = "8";
                $retuuid[$k]['aid'] = $v['aid'];
            }elseif($v['type']==9){
                $retuuid[$k]['uname'] = "中华云";
                $retuuid[$k]['type'] = "9";
                $retuuid[$k]['aid'] = $v['aid'];
            }
        }

       $checkArray  =  explode(",",$ret['cat']);

        if($ret['cid']==1969){
            $this->assign("type",$data[0]);
        }else{
            $this->assign("type",$data[1]);
        }

        $category = $category_model->where( array( 'catid' => $ret['cid']) )->find();
        $this->assign('category',$category);
        $this->assign('page', $ret);
        $this->assign('checkArray', $checkArray);
        $this->assign('retuuid', $retuuid);
        $this->display();
    }
    //删除播放器
    public function delete_player(){
        $id = I('id');
        $type = I('type');

        $del = M('pagetype')->where("aid = $id and type = $type")->delete();
        return $del;
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
        import("ORG.Pinyin");
        $py = new  PinYin();
        $page = D('Page');
        $title = $this->_post('title');
        $content = $this->_post('editor');
        $area = $this->_post('area');

        $act = $this->_post('act');
        $director = $this->_post('director');
        $year = $this->_post('year');
        $cat =  $this->_post('cat');
        $alias =  $this->_post('alias');
        $cat = rtrim($cat, ",");
        $create_date = $this->_post('create_date');
        $create_date = empty($create_date)?date('Y-m-d h:m:s'):$create_date;

        $createDate = strtotime($create_date);
        $category_id = $this->_post('category_id');
        if (isset ($createDate)) {
            $date = date('Y-m-d', $createDate);
        }

        $data = array('title' => $title, 'desc' => $content, 'create_date' => $date,'area'=>$area,'cat'=>$cat,'alias'=>$alias,'director'=>$director,
            'act' => $act, 'year' => $year, 'id' => $this->id , 'cid' => $category_id , 'pingy'=>$py->getFirstPY($title)) ;

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


    public function verifyPageAct () {
        //审核消息
        $id = $this->_post('id');

        //获得传入ID

        $pageModel = M('page');
        $verify = $pageModel -> where("`id` = $id") -> field('verify') -> find();


        if ($verify['verify'] == 1) {
            $data['verify'] = 0 ;
        }
        else {
            $data['verify'] = 1 ;
        }
        $res = $pageModel -> where("`id` = $id") -> save($data);

        $status = $data['verify'];

        if ($res) {
            $this->success($status);
        }
        else {
            $this->error('操作失败');
        }

    }
}