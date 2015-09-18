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

/*
        $NewsList = D('News');
        $NewsCount = M('News');
        $dbPre = C('DB_PREFIX');
        $pre_page = 10; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        //fix bug 146
        $orderStr = "inputtime desc , id desc";

        if ($catId) {
//
//             ICE 如果获得分类ID 则显示该分类下的所有信息
//

            $count = $NewsCount->where("`catid` = $catId")->count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数

            $newsList = $NewsList -> relation(true) -> where(array(  'catid'=> $catId )) -> order($orderStr)->limit(  $Page->firstRow,$Page->listRows )->select();
            $show = $Page->show(); // 分页显示输出
        } else {


            $count = $NewsCount->count(); // 查询满足要求的总记录数
            $Page = new Page($count, $pre_page);
            $newsList = $NewsList->relation(true)->order($orderStr)->limit(  $Page->firstRow,$Page->listRows )->select();
            $show = $Page->show(); // 分页显示输出

        }
        foreach ($newsList as $key => $value) {
            $newsList[$key]['countMessage'] = count($value['message']);
        }

*/
        //dump ($newsList);

//var_dump($NewsList->getLastSql());
        $newsResult = $this->queryNews($catId);
        $this->assign('newsList', $newsResult['newsList']);
        $this->assign('page', $newsResult['pageShow']); // 赋值分页输出
        $this->display();
    }

    private function queryNews($catId="" ,$pre_page = "10"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['title'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['catid'] = $catId;
        }


        $NewsList = D('News');
        $NewsCount = M('News');
        $dbPre = C('DB_PREFIX');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $orderStr = "inputtime desc , id desc";

        $count = $NewsCount->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数

        $newsList = $NewsList -> relation(true) -> where($where) -> order($orderStr)->limit(  $Page->firstRow,$Page->listRows )->select();

        foreach ($newsList as $key => $value) {
            $newsList[$key]['countMessage'] = count($value['message']);
        }

        $show = $Page->show(); // 分页显示输出

        $resultArr['newsList'] = $newsList;
        $resultArr['pageShow'] = $show;

        return $resultArr;
    }

    public function ajaxSearchNews(){
        $keyword = "";


        $newsResult = $this->queryNews();

        $this->assign('newsList', $newsResult['newsList']);

        $newsTabelHtml   = $this->fetch("Tpl/Admin/default/News/news-table.inc.html");

        $ajaxResult = array("newsTabelHtml"=>$newsTabelHtml ,'pagination'=> $newsResult['pageShow']);

        $this->success($ajaxResult);
    }

    /**
     * 新建新闻页面
     */
    public function create()
    {
        $this->getCategories();
        $this->getNews();

        // THUMBSIZE START

        $thumbsize = M('admin_panel') -> find();
        $thumbsize = $thumbsize['thumbsize'];
        $thumbsize = unserialize($thumbsize);

        $this->assign('thumbsize',$thumbsize);

        // THUMBSIZE END

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

        // THUMBSIZE START

        $thumbsize = M('admin_panel') -> find();
        $thumbsize = $thumbsize['thumbsize'];
        $thumbsize = unserialize($thumbsize);

        $this->assign('thumbsize',$thumbsize);

        // THUMBSIZE END
  
        $this->assign('categoryInfo', $categoryInfo);
        $this->assign('news', $News);
//        $this->assign('newsData', $newsData);
        $this->display();

    }

    public function copy()
    {

        $this->getCategories();
        /*
         * function Create by ICE @ 2013-06-21
         * 资讯编辑并复制页面显示
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

        $news_pinyin = Pinyin($news_title);

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
        $data['pinyin'] = $news_pinyin;

        
        $is_change = $this->_post('is_change');
        if ($is_change){
        	$thumbDate = $this->getThumbInfo();
       	 	$data = array_merge($data,$thumbDate);
        }
         $News -> relation('newsdata') -> save($data);


//        if ($updateRes) {
            $this->success();
//        } else {
//            $this->error('编辑失败');
//        }
    }

    private function copyRowNewToDb($newsId){

        $newNewsId = 0;

        if(empty($newsId)){}


        $NewsObj = D('News');
//        $NewsData = M('news_data');
        $News = $NewsObj -> relation(true) -> where("`id` = '$newsId'")->find();

        if(!empty($News)){
            $News['id'] = '';
            $newNewsId = $NewsObj->relation('newsdata') -> add($News);
        }

        return $newNewsId ;
    }
    public function copyAct()
    {

        $oldNewsId = $this->_post('id');
        $newsId = $this->copyRowNewToDb($oldNewsId);

        if($newsId < 1){
                $this->error("复制失败,请重新尝试!");
        }

        /*
         * function Create by ICE @ 2013-06-21
         * 资讯复制操作
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

        $news_pinyin = Pinyin($news_title);

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
        $data['pinyin'] = $news_pinyin;


        $is_change = $this->_post('is_change');
        if ($is_change){
        	$thumbDate = $this->getThumbInfo();
       	 	$data = array_merge($data,$thumbDate);
        }
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
        $data['pinyin'] = Pinyin( $this->_post("news_title"));
        //cut pic
        $thumbDate = $this->getThumbInfo();
        $data = array_merge($data,$thumbDate);
        
        
        /*
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
        */
        
//         print_r($data);
//         die("hello");

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
     * cut pic
     * @return array $data
     */
    private function getThumbInfo($type=0){
    	
    	$data = array();
    	
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
    	 
    	return $data;
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
        $ret = M('NewsSetting')->find();
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

        $ret = M('NewsSetting')->find();
        $templateModel =  M('NewsSetting');
        $ret[$name] = $checkedBit;
        $templateModel->save($ret);
        $this->success();
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

    public function comment() {
    /*
     * ICE 2103-07-19 管理新闻评论
     */
        $id = $this->_get('id');
        $D_news = D('News');
        $newsContent = $D_news -> relation(true) -> where("`id` = $id") -> find();

        $DB_message = D('Messageboard');
        $messageList = $DB_message -> relation(true) -> where("`AID` = $id AND `type` = 2") -> select();
        //dump ($messageList);
        $this->assign('messageList',$messageList);
        $this->assign('newsContent',$newsContent); 
        $this->display();
    }


}
