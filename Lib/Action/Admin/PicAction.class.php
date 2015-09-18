<?php

class PicAction extends AdminBaseAction {



    private $productIds;

    private $categoryId;

    private $id ;

    public function __construct(){

        parent::__construct();

        $this->productIds = $this->_post('productIds');

        $this->categoryId = $this->_get('categoryId');

        $this->id = $this->_get('id');

        //获得模板设置

        $DB_page_setting = M('product_setting');

        $pageSwitch = $DB_page_setting -> find();

        $this->assign('pageSwitch',$pageSwitch);



    }

    public function index () {

        $DB_feedback_category = M('category');
        $categoryId = $this->_get("categoryid");
        $categoryList = $DB_feedback_category -> select();

        $this->assign("categoryList",$categoryList);  //输出分类

        $queryResult = $this->queryPic($categoryId);

        $this->assign("page",$queryResult['pageShow']);// 分页显示输出
        $this->assign("picList",$queryResult['picList']); //输入留言列表
        $this->display();
    }

    private function queryPic($catId="" ,$pre_page = "15"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['pic_name'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['categoryid'] = $catId;
        }

        $DB_pic = M('pic');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $DB_pic -> where($where) -> count();// 查询满足要求的总记录数
        $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $messageList = $DB_pic  -> where($where) -> order("sort asc") -> limit($Page->firstRow,$Page->listRows) -> select();


        $resultArr['picList'] = $messageList;
        $resultArr['pageShow'] = $Page->show();

        return $resultArr;
    }

    private function findModule($moduleId = "")
    {
        $moduleObj = M('module');
        $module = $moduleObj->where(array('id' => $moduleId))->find();

        $moduleStr = $module['name'];
        return $moduleStr;
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
        $Page  = new Page($count,100);// 实例化分页类 传入总记录数和每页显示的记录数
//        $Page->setConfig('header','item');
//        $Page->setConfig('prev','prev');
//        $Page->setConfig('next','next');
//        $Page->setConfig('fitst','first');
//        $Page->setConfig('last','last');
        $Page->setConfig('theme',"  %upPage% %first% %prePage% %linkPage% %nextPage% %end% %downPage% ");
        $show = $Page->show();// 分页显示输出
        $category = $categoryModel->where($where)->order("listorder  desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        return $category;
    }

    private function queryProduct($catId="" ,$pre_page = "25"){
        $resultArr = array();
        $keyword = $this->_post('keyword');
        $where = array();

        if(!empty($keyword)){
            $where['product_name'] = array("LIKE","%$keyword%");
        }

        if(!empty($catId)){
            $where['category_id'] = $catId;
        }
        $where['status'] = 1;
        $Product = D('Product');

        $pre_page = $pre_page; //每一页显示多少条数据

        //ICE 分页

        import('ORG.Util.Page'); // 导入分页类



        $orderStr = "  sort asc";



        $count = $Product -> where($where) -> count();// 查询满足要求的总记录数

        $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数

        $productList = $Product ->relation(true) -> where($where) -> order($orderStr) ->limit($Page->firstRow.','.$Page->listRows) -> select();

        $show = $Page->show(); // 分页显示输出

        $resultArr['productList'] = $productList;

        $resultArr['pageShow'] = $show;
        return $resultArr;

    }



    public function ajaxSearchProduct(){

        $keyword = "";





        $productResult = $this->queryProduct();



        $this->assign('products', $productResult['productList']);



        $productTabelHtml   = $this->fetch("Tpl/Admin/default/Product/product-table.inc.html");



        $ajaxResult = array("productTabelHtml"=>$productTabelHtml ,'pagination'=> $productResult['pageShow']);



        $this->success($ajaxResult);

    }



	

	/**

	 * 新建产品页面

	 */

	public function create(){

        // THUMBSIZE START

        $thumbsize = M('admin_panel') -> find();
        $thumbsize = $thumbsize['thumbsize'];
        $thumbsize = unserialize($thumbsize);
        $this->assign('thumbsize',$thumbsize);
		$this->display();

		//$this->assign("categories",);

	}



    public function deletePic(){
        $DB_pic = M('pic');
        $ids = join($this->_post('picIds'), ',');
        $res = $DB_pic-> where("`id` IN ($ids)") -> delete();
        if ($res) {
            $this->success('删除成功');
        }
        else {
            $this->error('删除失败');
        }

    }

    /**

     * 新增图片

     */

    public function add(){
        $id = $this->_post("id");

        $copy = $this->_post("copy");

        $type = (empty($copy))?0:'1';
        if(!empty($type) && $type > 0){
            $id = $this->copyRowNewToDb($id);
        }


        $pic = D('Pic');
        $data = array();
        $data['pic_name'] = $this->_post('pic_name');
        $data['sort'] =  $this->_post('sort');



        $data['type'] =  $this->_post('type');
        $inputtime = time();

        //ICE 转换时间格式
        $time = strtotime($this->_post("create_date"));
        $data['create_date'] = $time;

        $picture = $this->_post('picture');
        if(!$id){
            $count = count( explode ("/",$picture) );
            $picture = explode("/",$picture);
            $picture = $picture [ $count - 1];
            $picture = __APP__.'/uploads/'.date(Ymd).'/'.$picture;
        }
            $data['images'] = $picture;

        //ICE 附件地址

        $data['date'] = $inputtime;



        $ret = null;

        $imagesParam = $_REQUEST['images'];

        //没有ID就新建

        if ( empty ( $id )) {

            $ret =  $pic->add($data);

        } else {

        //获得ID就修改

            $ret = $pic -> where("`id` = $id") ->save($data);

        }

        //echo $product->getLastSql();
        if ( ! $ret){
            $this->error( M('pic')->getDbError());
        }else{
            $info =$id?'修改成功':'添加成功';
            $this->success($info,U('Pic/index'));
        }

    }





    public function ajaxProductImageAdd(){

        $imageArr = array();

        $imageArr['image_ratio'] = $this->_post('ratio');

        $x = $this->_post('x');

        $y = $this->_post('y');

        $x1 = $this->_post('x2');

        $y1 = $this->_post('y2');



        $w = $this->_post('w');

        $h = $this->_post('h');

        //pic name

        //ok

        if ( $x1 && $y1 && $w && $h){

            $ratio = $this->_post('ratio');

            $ratioArray = explode('_',$ratio);

            $picture = $this->_post('picture');

            $count = count( explode ("/",$picture) );

            $picture = explode("/",$picture);

            $picture = $picture [ $count - 1];

            $picture = __APP__.'/uploads/'.date(Ymd).'/'.$picture;



            $file = thumb(APP_PATH.$picture, $w, $h,0,0,$x,$y);



            import('ORG.Util.Image');

            $realW =  $ratioArray[0];

            $realH =  $ratioArray[1];



            Image::thumb($file,$file,'',intval($realW),intval($realH),false);



            $fileCount = count(explode("/",$file));

            $file = explode("/",$file);

            $file = $file [ $fileCount - 1];

            $imageArr['thumb'] =  'uploads/'.date(Ymd).'/'.$file;

            $imageArr['images'] = $picture;

//            $data['pic_ratio'] = $ratio;

        }

        //$data['attachment'] = str_replace("/uploads","uploads",$this->_post('attachment'));

        $imageArr['attachment'] = './uploads/attachment/'.$this->_post('attachment_name');



        $imageParam = base64_encode(serialize($imageArr));

        $this->success($imageParam);





        //ICE 附件地址

    }



    private function copyRowNewToDb($product_id){

        $productId = 0;
        if(empty($product_id)){}

        $pic_model = M('pic');

        $pic = $pic_model->where(array( 'id' => $product_id ) )->find();



        if(!empty($pic)){

            $pic['id'] = '';

            $product  = $pic_model->add($pic);

        }
        return $productId ;

    }





	/**

	 * 获得分类信息

	 *

	 */

	private function getCategories(){

		$category = M('Category');

		$where['module'] = array('eq',2);

		$where['parentid'] = array('eq',0);

		$categories = $category->where($where)->select();

		foreach ( $categories as $n =>$val  ){

			$child_where['parentid'] = $val['catid'];

			$categories[$n]['child'] = $category->where($child_where)->select();

		}

		$this->assign("categories",$categories);

	}

	

	/**

	 *

	 * 读取所有产品

	 */

	private function getProducts(){

	

		$where = array();

		if($this->categoryId){

			$where['category_id'] = $this->categoryId;

		}

		$productModel = D('Product');

		//$list = parent::getPage($productModel,10,$where,true);

        $list = $productModel -> relation(true) -> where($where) -> order('create_date desc') -> select();

		$this->assign('products',$list);

	}

    /**

     *       修改产品

     */

    public function edit(){

        $pic_model = M('pic');

        $category_model = M('Category');

        $pic = $pic_model->where(array( 'id' => $this->id ) )->find();

        $this->assign('pic',$pic);

        // THUMBSIZE START


        $thumbsize = M('admin_panel') -> find();

        $thumbsize = $thumbsize['thumbsize'];

        $thumbsize = unserialize($thumbsize);



        $this->assign('thumbsize',$thumbsize);



        // THUMBSIZE END



        $this->display();



    }

    public function copy(){

        $product_model = M('product');

        $product = $product_model->where(array( 'id' => $this->id ) )->find();

        $category_model = M('Category');

        $category = $category_model->where( array( 'catid' => $product['category_id']) )->find();

        $product['brandList'] = explode(",",$category['brand']);

        $product['colorList'] = explode(",",$category['color']);

        $product['versionList'] = explode(",",$category['version']);

        $product['thumbList'] = explode(",",$product['images']);

        $this->assign('product',$product);

        $this->assign('category',$category);

        $this->display();



    }

     public function teamplate()

    {

        $ret = M('ProductSetting')->find();

        $this->assign('template',$ret);

        $this->display();



    }

    public function verifyPicAct () {
        //审核消息
        $id = $this->_post('id');

        //获得传入ID

        $picModel = M('pic');
        $verify = $picModel -> where("`id` = $id") -> field('type') -> find();


        if ($verify['type'] == 1) {
            $data['type'] = 0 ;
        }
        else {
            $data['type'] = 1 ;
        }
        $res = $picModel -> where("`id` = $id") -> save($data);

        $status = $data['type'];

        if ($res) {
            $this->success($status);
        }
        else {
            $this->error('操作失败');
        }

    }






}

