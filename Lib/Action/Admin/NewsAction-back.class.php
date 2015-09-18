<?php

/**
 * 新闻咨询Action
 * @author Gavin
 *
 */
class NewsAction extends AdminBaseAction
{

    private $categoryId;
    private $newsId;
    private $newsIds;

    public function __construct()
    {
        parent::__construct();
        $this->categoryId = $this->_get('categoryId');
        $this->newsIds = $this->_post('newsIds');

        //获得模板设置
        $DB_page_setting = M('news_setting');
        $pageSwitch = $DB_page_setting->find();
        $this->assign('pageSwitch', $pageSwitch);
    }

    public function index()
    {
        $this->getCategories();
        $this->getNews();
        //获得参数 $this->_get['categoryId'];

        //ICE 当前URL参数中获得categoryId 则显示当前ID下内容 如未获得 则显示顶级ID下内容


        $catId = $this->_get('categoryId');
        $catId = $catId + 0;


        $NewsList = D('News');
        $NewsCount = M('News');
        $dbPre = C('DB_PREFIX');
        $pre_page = 10; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        if ($catId) {
            /*
             * ICE 如果获得分类ID 则显示该分类下的所有信息
             */

            $count = $NewsCount->where("`catid` = $catId")->count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数

//            $sql = "select * from {$dbPre}news LEFT JOIN {$dbPre}news_data ON {$dbPre}news.id = {$dbPre}news_data.id where {$dbPre}news.catid = $catId "
//                . " order by  {$dbPre}news.inputtime desc limit $Page->firstRow,$Page->listRows;";

            $newsList = $NewsList -> relation(true) -> where(array(  'catid'=> $catId )) -> order('inputtime desc')->limit(  $Page->firstRow,$Page->listRows )->select();
            $show = $Page->show(); // 分页显示输出
        } else {
            /*
             * ICE 如果未获得分类ID 则显示所有分类下的所有信息
             */

//            $Category = M('category');
//            $topCatagory = $Category->where("`module` = 19  AND `parentid` = 0 ")->field('catid')->select();
//            //ICE 拼装一个查询条件字符串 有时间的时候 应该整理到常用函数
//            foreach ($topCatagory as $value) {
//                $catString .= $value['catid'] . ',';
//            }
//            $catString = rtrim($catString, ',');

            $count = $NewsCount->count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
            //$Page -> setConfig('theme',"总数：%totalRow% 当前页面：%nowPage% 列表：%linkPage% 首页:%upPage% 尾页:%end%");
//            $sql = "select * from {$dbPre}news LEFT JOIN {$dbPre}news_data ON {$dbPre}news.id = {$dbPre}news_data.id where {$dbPre}news.catid IN ($catString)"
//                . " order by {$dbPre}news.inputtime desc limit $Page->firstRow,$Page->listRows;";
//            $newsList = $NewsList->query($sql);
            $newsList = $NewsList->relation(true)->order('inputtime desc')->limit(  $Page->firstRow,$Page->listRows )->select();
            $show = $Page->show(); // 分页显示输出

        }

        $this->assign('newsList', $newsList);
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    /**
     * 新建新闻页面
     */
    public function create()
    {
        $this->getCategories();
        $this->getNews();
        $this->display();
    }

    public function edit()
    {

        $this->getCategories();
        /*
         * function Create by ICE @ 2013-06-21
         * 新增资讯编辑页面显示
         *
         */
        $newsId = $this->_get('id');
        $News = D('News');
//        $NewsData = M('news_data');
        $News = $News -> relation(true) -> where("`id` = '$newsId'")->find();
//        $newsData = $NewsData->where("`id` = '$newsId'")->find();
        $catid = $News['catid'];
        $Category = M('category');
        $categoryInfo = $Category->where("`catid` = '$catid'")->find();

        $this->assign('categoryInfo', $categoryInfo);
        $this->assign('news', $News);
//        $this->assign('newsData', $newsData);
        $this->display();

    }

    public function editAct()
    {

        /*
         * function Create by ICE @ 2013-06-21
         * 新增资讯编辑操作
         *
         */
        $news_title = $this->_post('news_title');
        $news_catid = $this->_post('category_id');
        $news_description = $this->_post('description');
        $news_keywords = $this->_post('keywords');
        $news_picture = $this->_post('picture');
        $news_pic_ratio = $this->_post('ratio');
        $news_status = $this->_post('status');
        $news_ishot = $this->_post('ishot');


        if ($news_status) {
            $news_status = 1;
        } else {
            $news_status = 0;
        }

        if ($news_ishot) {
            $news_ishot = 1;
        } else {
            $news_ishot = 0;
        }
        //dump ($news_status);

        $news_inputtime = $this->_post('create_date');
        $news_inputtime = strtotime($news_inputtime);
        //dump ($news_inputtime);
        //数据表中是 int10 date组件重写后 查看是否需要重新转换时间格式
        $newsData_content = stripslashes($this->_post("content"));
        $updateId = $this->_post('id');

        $News = D('News');

        $newsId = $this->_post('id');

        $data = array();
        $data['id'] = $newsId + 0;
        $data['title'] = $news_title;
        $data['catid'] = $news_catid;
        $data['description'] = $news_description;
        $data['keywords'] = $news_keywords;
        $data['picture'] = $news_picture;
        $data['pic_ratio'] =$news_pic_ratio;
        $data['status'] = $news_status;
        $data['ishot'] = $news_ishot ;
        $data['inputtime'] =$news_inputtime ;
        $data['newsdata'] = array ( 'content' => $newsData_content,
                                            'id' => $newsId + 0 );

         $News -> relation('newsdata') -> save($data);


//        if ($updateRes) {
            $this->success();
//        } else {
//            $this->error('编辑失败');
//        }
    }


    /**
     * 删除新闻
     */
    public function deleteNews()
    {
        $news = D('News');
        $ids = join($this->newsIds, ',');
        $news->relation('newsdata')->delete($ids);
        if ($news->getError()) {
            $this->error($news->getError());
        }
        $this->success('');
    }

    /**
     * 新建新闻
     */
    public function add()
    {
        $News = D('News');

     //   $newsData = D('NewsData');

        if (!$this->_post('content'))
            $this->error("请输入内容!");

        if (!$this->_post('news_title'))
            $this->error("请输入标题！");

        if (!$this->_post('category_id'))
            $this->error("请正确输入类别名称！");

        $data['title'] = $this->_post("news_title");
        $data['description'] = $this->_post("description");

        $inputtime = time();

        $createDate = $this->_post("create_date");

        $data['inputtime'] = strtotime($createDate);

        //dump ($inputtime);
        //$data['inputtime'] = time();
        $data['catid'] = $this->_post("category_id");
        $data['keywords'] = $this->_post("keywords");
        //ICE 是否推荐

        $data['status'] = $this->_post("status");
        //cut pic

        $x = $this->_post('x');
        $y = $this->_post('y');
        $x1 = $this->_post('x2');
        $y1 = $this->_post('y2');

        $w = $this->_post('w');
        $h = $this->_post('h');

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

            $data['thumb'] = 'uploads/' . date(Ymd) . '/' . $file;
            $date['images'] = $picture;
            $data['pic_ratio'] = $ratio;
        }

        if ($this->_post('status'))
            $data['status'] = 1;
        else
            $data['status'] = 0;

        if ($this->_post("ishot"))
            $data['ishot'] = 1;
        else
            $data['ishot'] = 0;

        $data['typeid'] = 19;

        $data['newsdata'] = array ( 'content' => $this->_post('content') );
        $News -> relation('newsdata') -> add($data);
        $this->success('');
        //ICE考虑重新构建此部分代码
    }

    /**
     * 获得分类信息
     *
     */
    private function getCategories()
    {
        $category = M('Category');
        $where['module'] = array('eq', 1);
        $where['parentid'] = array('eq', 0);
        $categories = $category->where($where)->select();

        if (is_array($categories)) {
            foreach ($categories as $n => $val) {
                $child_where['parentid'] = $val['catid'];
                $categories[$n]['child'] = $category->where($child_where)->select();
            }
        }
        $this->assign("categories", $categories);
    }

    /**
     *
     * 读取所有新闻
     */
    private function getNews()
    {

        $where = array();
        if ($this->categoryId) {
            $where['catid'] = $this->categoryId;
        }
        $newsModel = D('News');
        $list = parent::getPage($newsModel, 10, $where, true);
        $this->assign('news', $list);
    }

    /**
     *
     */
    public function teamplate()
    {
        $this->display();
    }

    public function deleteRss(){
        $id = $this->_get('rss_id');
        D('Rss') -> find($id) ->delete();
        $this->success('');
    }

    public function saveRss(){
        D('Rss')->create();
        $this->success('');
    }


}
