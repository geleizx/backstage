<?php
class IndexAction extends AdminBaseAction{

    private $menu_db ;

    private $setting;

    public  function __construct(){
        parent::__construct();
        $this->menu_db = D('Menu');

        $adminPanelModel = M("admin_panel");
        $this->setting = $adminPanelModel->limit(1)->select();

    }


    function send_post($url, $post_data) {

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    public function  index(){


        $post_data = array(
            'name' => 'dangbei',
            'key' => 'DD7QXWR3EWZEEF7Z939Z'
        );
        $data = $this->send_post("http://customization.91vst.com/api3.0/downloadtvdb.action",$post_data);
        $result= $this->writelog($data);
	    $this->display();
	}

    function writelog($str)
    {
        $open=fopen("log.txt","a" );
        fwrite($open,$str);
        fclose($open);
    }


    /**
	 * 首页导航信息
	 */
	private function getNavData(){
		$module = M('Module');
		$cond['disabled'] = array("eq","0");
		$ret = $module->where($cond)->order('listorder')->select();
		return $ret;
	}

	/**
	 * Just Test 
	 */
	public function testNavData(){
		print_r($this->getNavData() );
		echo "OK";
	
	}

    public function quickMenu(){
        $QuickMenu = M('menu');
        $quickMenu = $QuickMenu -> where("`parentid` = 0 AND `isquickmenu` = 1 AND `isbuy` = 1 AND `parentid` = 0" ) -> order("`listorder` ASC") -> select();
        $this->success($quickMenu);
    }

    public function quickMenuDetail(){
        $id = $this->_get('id');
        $QuickMenu = M('menu');
        $quickMenu = $QuickMenu -> where(array('id' => $id)) -> find();
        $this->success($quickMenu);

    }
    public function hiddenQuickMenu () {
    /*
     * ICE 2013-07-06 隐藏一个快捷导航块
     * @param AJAX POST id
     */
        $id = $this->_post('id');
        $QuickMenu = M('menu');
        $data['isquickmenu'] =  0;
        $res = $QuickMenu -> where("`id` = '$id'") -> save($data);
        if ($res) {
            $this->success('删除成功');
        }
        else {
            $this->error('删除失败'); 
        }
    }

    /**
     *
     */
    public function  pv(){
        $token_auth = $this->setting['key'];
        $site_id = $this->setting['siteid'];
        $date = date('Y-m-d');
        $api = 'http://stat.tieson.cn/index.php?module=API&method=Actions.get&idSite='.$site_id.'&period=day&date='.$date.'&format=JSON&token_auth='.$token_auth;
         $json = file_get_contents($api);
         $stat_info = json_decode($json);
        $this->success($stat_info);
    }

    public function  online(){
        $token_auth = $this->setting['key'];
        $site_id = $this->setting['siteid'];
        $date = date('Y-m-d');
        $api = 'http://stat.tieson.cn/index.php?module=API&method=Live.getCounters&idSite='.$site_id.'&lastMinutes=30&format=JSON&token_auth='.$token_auth;
        $json = file_get_contents($api);
        $stat_info = json_decode($json);
        $this->success($stat_info);
    }

    public function stat_graph(){
        $token_auth = $this->setting['key'];
        $site_id = $this->setting['siteid'];
        $api = 'http://stat.tieson.cn/index.php?module=API&method=VisitsSummary.get&idSite='.$site_id.'&period=day&date=previous15&format=JSON&token_auth='.$token_auth;
        $json = file_get_contents($api);
        $stat_info = json_decode($json);

        $date_array = array();
        $page_view_array = array();
        $uv_array = array();

        foreach (  $stat_info as $date => $value  ){
            array_push( $date_array , $date);
            $value = (array) $value;
            if ( count( $value ) != 0){
                array_push($uv_array , $value['nb_uniq_visitors']);
            }else{
                array_push($uv_array , 0 );
            }
        }

        $api_pv = 'http://stat.tieson.cn/index.php?module=API&method=Actions.get&idSite='.$site_id.'&period=day&date=previous15&format=JSON&token_auth='.$token_auth;
        $json = file_get_contents($api_pv);
        $stat_info = json_decode($json);
        foreach (  $stat_info as $date => $value  ){
            $value = (array) $value;
            if ( count( $value ) != 0){
                array_push($page_view_array , $value['nb_pageviews']);
            }else{
                array_push($page_view_array , 0 );
            }
        }

        $info = array( 'date' => $date_array, 'pv' => $page_view_array ,'uv' => $uv_array);
        $this->success($info);
    }
    public function addModList(){
        $DB_menu = M('menu');
        $addModList = $DB_menu -> where("`isquickmenu` = 0 AND `quickmenu` = 1") -> order("isbuy DESC")-> select();
        $this->assign('addModList',$addModList);
        $this->display('addModList');
    }
    public function quickAdd () {
    /*
     * ICE 2013-07-12 添加一个桌面快捷方式
     * @param AJAX POST "id"
     */
        $id = $this->_post('id');
        $DB_menu = M('menu');
        $data['isquickmenu'] = '1';
        $quickAdd = $DB_menu -> where("`id` = $id") -> save($data);
        //dump($quickAdd);
        if ($quickAdd) {
            $data = $DB_menu -> where("`id` = $id") -> find();
            $this->ajaxReturn($data,'操作成功',1);
        }
        else {
            $this->error();
        }
    }

    public function checkQuickListIsNull(){
        $QuickMenu = M('menu');
        $quickMenu = $QuickMenu -> where(" quickmenu = 1 AND isbuy = 1 AND  isquickmenu =0 " ) -> order("`listorder` ASC") -> select();


        if (  $quickMenu ){
            $this->ajaxReturn(array('isNull'=>0));
        } else{
            $this->ajaxReturn(array('isNull'=>1));
        }
    }


    public function SortQuickMenu(){

        $before = $this->_get('before');
        $after = $this->_get('after');

        $afterArray = $after;

//var_dump($afterArray);

//        $QuickMenuModel = M('menu');

//        $QuickMenu = $QuickMenuModel -> where("`parentid` = 0 AND `isquickmenu` = 1 AND `isbuy` = 1 AND `parentid` = 0" ) -> order("`quick_menu_order` ASC") -> select();

//var_dump($QuickMenu);

//fix ITHINK-165
        foreach($afterArray as $sortKey=>$menuIDStr){
            $menuIdArr = (empty($menuIDStr)) ? "" : explode("_",$menuIDStr);

            if(isset($menuIdArr['1']) && !empty($menuIdArr['1'])){
                $QuickMenuModelT = M('menu');
                $menuId = $menuIdArr['1'];

                $QuickMenu['quick_menu_order'] = $sortKey;
                $QuickMenu['id'] = $menuId;

                $QuickMenuModelT->save($QuickMenu);

//var_dump($QuickMenuModelT->getLastSql());
            }
        }

       /* for ($i = 0 ; $i < count($afterArray) ; $i++){

            if ($afterArray[$i] != $QuickMenu[i]['quick_menu_order']){

                $QuickMenuModelT = M('menu');
                $QuickMenu[$i]['quick_menu_order'] = $afterArray[$i] ;
                $QuickMenuModelT->save( $QuickMenu[$i]);
            }
        }*/

//        foreach ( $QuickMenu as $value){
//
//        }
        $this->success(1);


    }


    public function quickSearch(){
        $keywords = $this->_get('term');
//        $QuickMenu = M('menu');
//        $map['name'] = array('like','%'.$keywords.'%');

        $newsModel = M('News');
        $map['title'] = array('like','%'.$keywords.'%');
        $map['pinyin'] = array('like','%'.$keywords.'%');
        $map['_logic'] = 'OR';

        $news = $newsModel->where($map)->select();

        $productModel = M('Product');
        $map['title'] = array('like','%'.$keywords.'%');
        $map['pinyin'] = array('like','%'.$keywords.'%');
        $map['_logic'] = 'OR';

        $products = $productModel->where($map)->select();

        $pageModel = M('Page');
        $map['title'] = array('like','%'.$keywords.'%');
        $map['pinyin'] = array('like','%'.$keywords.'%');
        $map['_logic'] = 'OR';

        $pages = $pageModel->where($map)->select();


        $array = array();

        //News
        for ($i = 0 ; $i < count($news) ;$i++){
            $data = array('id' => $news[$i]['id'] , 'label'=>  $news[$i]['title'] , 'value'=>  'News/edit/id/'.$news[$i]['id'].'.html');
            array_push($array,$data);
        }

        //Pages
        for ($i = 0 ; $i < count($pages) ;$i++){
            $data = array('id' => $pages[$i]['id'] , 'label'=>  $pages[$i]['title'] , 'value'=>  'Page/edit/id/'.$pages[$i]['id'].'.html');
            array_push($array,$data);
        }


        //Products
        for ($i = 0 ; $i < count($products) ;$i++){
            $data = array('id' => $products[$i]['id'] , 'label'=>  $products[$i]['product_name'] , 'value'=>  'Product/edit/id/'.$products[$i]['id'].'.html');
            array_push($array,$data);
        }

        $this->ajaxReturn($array);

    }
}
