<?php

class ProductAction extends AdminBaseAction {



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

	public function index(){

		$this->getCategories();

        // $this->getProducts();

        //获得参数 $this->_get['categoryId'];


        $longlong = M('product')->where("product_name = '龙龙直播'")->count();
        $vst = M('product')->where("product_name = 'VST直播'")->count();
        //ICE 当前URL参数中获得categoryId 则显示当前ID下内容 如未获得 则显示顶级ID下内容 

        $catId = $this->_get('categoryId');

        $catId = $catId + 0;

        $queryResult = $this->queryProduct($catId);

        $this->assign('longlong',$longlong);
        $this->assign('vst',$vst);
        $this->assign('products',$queryResult['productList']);
        $this->assign('page',$queryResult['pageShow']);// 赋值分页输出

		$this->display();



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



        $Product = D('Product');

        $pre_page = $pre_page; //每一页显示多少条数据

        //ICE 分页

        import('ORG.Util.Page'); // 导入分页类



        $orderStr = "create_date desc , id desc";



        $count = $Product -> where($where) -> count();// 查询满足要求的总记录数

        $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数

        $productList = $Product ->relation(true) -> where($where) -> order($orderStr) ->limit($Page->firstRow.','.$Page->listRows) -> select();





        //向数组中插入留言条数

        foreach ($productList as $key => $value) {

            $productList[$key]['countMessage'] = count($value['message']);

        }



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

        // dump ($thumbsize);



        // THUMBSIZE END





       // $arrayTemp =array(0 => array(

       //                             'width' => 320,

       //                             'height' => 240,

       //                          ),

       //                   1 => array(

       //                             'width' => 640,

       //                             'height' => 480,

       //                         ),

       //                   2 => array(

       //                             'width' => 800,

       //                             'height' => 820,

       //                         )



       //                 );



       // echo serialize($arrayTemp);



		$this->display();

		//$this->assign("categories",);

	}



    public function deleteProducts(){

        $product = D('Product');

        $ids = join($this->productIds,',');

        $product->delete($ids);

        if ($product->getError()){

            $this->error($product->getError());

        }

        $this->success('');

    }

    /**

     * 新增产品

     */

    public function add(){





        $id = $this->_post("id");

        $copy = $this->_post("copy");







        $type = (empty($copy))?0:'1';



        if(!empty($type) && $type > 0){

            $id = $this->copyRowNewToDb($id);

        }



        $product = D('product');





        $data = array();



        $data['product_name'] = $this->_post('product_name');

        // $data['product_id'] = $this->_post('product_id');

        $data['param1'] = $this->_post('param1');

        $data['param2'] = $this->_post('param2');

        $data['param3'] = $this->_post('param3');

        $data['param4'] = $this->_post('param4');

        $data['pay1'] = $this->_post('pay1');

        $data['pay2'] = $this->_post('pay2');



        $data['product_id'] =  $this->_post('product_id');

        $data['category_id'] =  $this->_post('category_id');

        $data['content'] =  $this->_post('content');

        $data['arguements'] =  $this->_post('arguements');



        $data['memo'] =  $this->_post('memo');

        $data['keywords'] =  $this->_post('keywords');

        $data['brand'] =  $this->_post('brand');

        $data['color'] =  $this->_post('color');

        $data['version'] =  $this->_post('version');

        $data['member_price'] =  $this->_post('member_price');

        $data['mark'] =  $this->_post('mark');

        $data['pinyin'] = Pinyin($this->_post('product_name'));



        if ($this->_post('ishot')) {

            $data['ishot'] = 1;

        }

        else {

            $data['ishot'] = 0;

        }

        if ($this->_post('status')) {

            $data['status'] = 1;

        }

        else {

            $data['status'] = 0;

        }

        $data['type'] =  $this->_post('type');

        $inputtime = time();

        $createDate = $this->_post("create_date");

        if ( isset ( $createDate )) {

            $inputtime = $createDate;

        }

        //ICE 转换时间格式

        $time = strtotime($this->_post("create_date"));

        $data['create_date'] = date('Y-m-d h:i:s',$time);



        $data['image_ratio'] = $this->_post('ratio');

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

            $ratioArray = split('_',$ratio);

            $picture = $this->_post('picture');

            $count = count( explode ("/",$picture) );

            $picture = explode("/",$picture);

            $picture = $picture [ $count - 1];

            $picture = 'uploads/'.date(Ymd).'/'.$picture;



            $file = thumb(APP_PATH.$picture, $w, $h,0,0,$x,$y);



            import('ORG.Util.Image');

            $realW =  $ratioArray[0];

            $realH =  $ratioArray[1];



            Image::thumb($file,$file,'',intval($realW),intval($realH),false);



            $fileCount = count(explode("/",$file));

            $file = explode("/",$file);

            $file = $file [ $fileCount - 1];



            $data['thumb'] =  'uploads/'.date(Ymd).'/'.$file;

            $date['images'] = $picture;

//            $data['pic_ratio'] = $ratio;

        }

        //$data['attachment'] = str_replace("/uploads","uploads",$this->_post('attachment'));

//        if(!file_exists($data['attachment'])){

//            $data['attachment'] = './uploads/attachment/'.$this->_post('attachment_name');

//        }

//        if(!file_exists($data['attachment'])){

//

//        }

        if(!file_exists($this->_post('attachment_name'))){

            $data['attachment'] = './uploads/attachment/'.$this->_post('attachment_name');

        }

        if(!file_exists($this->_post('attachment_name2'))){

            $data['attachment2'] = './uploads/attachment/'.$this->_post('attachment_name2');

        }

        if(!file_exists($this->_post('attachment_name3'))){

            $data['attachment3'] = './uploads/attachment/'.$this->_post('attachment_name3');

        }

        if(!file_exists($this->_post('attachment_name4'))){

            $data['attachment4'] = './uploads/attachment/'.$this->_post('attachment_name4');

        }



        //ICE 附件地址

        $data['create_date'] = $inputtime;



        $ret = null;

        //ICE 自基础修改 获得ID 则编辑 未获得则新建





        $imagesParam = $_REQUEST['images'];



        if(!empty($imagesParam) && is_array($imagesParam)){



            $thumbs = "";

            $image_ratio = "";

            $images = "";

            $attachment = "";



            $imagesParam = array_unique($imagesParam);



            foreach($imagesParam as $key=>$image){

                $imageList = unserialize(base64_decode($image));



                (empty($thumbs))?$thumbs .= $imageList['thumb'] : $thumbs .= ",".$imageList['thumb'];

                (empty($image_ratio)) ? $image_ratio .= $imageList['image_ratio'] :  $image_ratio .= ",".$imageList['image_ratio'];

                (empty($images)) ? $images .= $imageList['images']: $images .= ",".$imageList['images'];

                (empty($attachment)) ?  $attachment .= $imageList['attachment'] : $attachment .= ",".$imageList['attachment'];

            }

        }



        if(!empty($thumbs)){

            $data['thumb'] = $thumbs;

        }

        if(!empty($image_ratio)){

            $data['image_ratio'] = $image_ratio;

        }

        if(!empty($images)){

            $data['images'] = $images;

        }

        if(!empty($attachment)){

//            $data['attachment'] = $attachment;

        }





        //没有ID就新建

        if ( empty ( $id )) {

            $ret =  $product->add($data);

        } else {

        //获得ID就修改

            $ret = $product -> where("`id` = $id") ->save($data);

        }



        //echo $product->getLastSql();

        if ( ! $ret){

            $this->error($product->getDbError());

        }

        $this->success($product->getLastSql());

        

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

            $picture = 'uploads/'.date(Ymd).'/'.$picture;



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





        $product_model = M('product');

        $product = $product_model->where(array( 'id' => $product_id ) )->find();



        if(!empty($product)){

            $product['id'] = '';

            $productId = $product_model->add($product);

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

        $product_model = M('product');

        $category_model = M('Category');

        $product = $product_model->where(array( 'id' => $this->id ) )->find();

        $category = $category_model->where( array( 'catid' => $product['category_id']) )->find();

        $product['brandList'] = explode(",",$category['brand']);

        $product['colorList'] = explode(",",$category['color']);

        $product['versionList'] = explode(",",$category['version']);

        $product['thumbList'] = explode(",",$product['images']);

 

        $this->assign('product',$product);

        $this->assign('category',$category);



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



    public function saveTemplate(){

        $name = $this->_get('name');

        $checked = $this->_get('checked');



        $checkedBit = 0;

        if ($checked == "true"){

            $checkedBit = 1;

        }



        $ret = M('ProductSetting')->find();

        $templateModel =  M('ProductSetting');

        $ret[$name] = $checkedBit;

        $templateModel->save($ret);

        $this->success();

    }

    public function editCategory() {

    /*

     *  修改产品分类 跳转至分类管理页面

     */

        $this->redirect('Category/managment', array('moduleId' => 2), 0 );

    }

    public function comment() {

    /*

     * ICE 2103-07-19 管理产品评论

     */

        $id = $this->_get('id');

        $MDB_product = M('product');

        $productContent = $MDB_product -> where("`id` = $id") -> find();



        $DB_message = D('Messageboard');

        $messageList = $DB_message -> relation(true) -> where("`AID` = $id AND `type` = 1") -> select();

        //dump ($messageList);

        $this->assign('messageList',$messageList);

        $this->assign('productContent',$productContent); 

        $this->display();

    }
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
                $data['product_name']= $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                $data['category_id'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                $data['param1'] = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                $data['param2'] = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                $data['type'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
                $data['status'] =1;
                $data['create_date'] =date("Y-m-d H:i:s",time());
                M('Product')->add($data);
            }
            $this->success('导入成功！');
        }else
        {
            $this->error("请选择上传的文件");
        }
    }
}

