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

        //ICE 当前URL参数中获得categoryId 则显示当前ID下内容 如未获得 则显示顶级ID下内容 
        $catId = $this->_get('categoryId');
        $catId = $catId + 0;

        $Product = D('Product');
        $pre_page = 10;//每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page');// 导入分页类

        if($catId) { 
        /*
         * ICE 如果获得分类ID 则显示该分类下的所有信息
         */
            $count = $Product -> where("`category_id` = $catId") -> count();// 查询满足要求的总记录数
            $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
            $productList = $Product ->relation(true) -> where("`category_id` = $catId") -> order('create_date desc') ->limit($Page->firstRow.','.$Page->listRows) -> select();
            $this->assign('products',$productList);
            $show = $Page->show();// 分页显示输出
        }
        else {
        /*
         * ICE 如果未获得分类ID 则显示所有分类下的所有信息
         */
//            $Category = D('category');
//            $topCatagory = $Category -> where("`module` = 2  AND `parentid` = 0 ") -> field('catid') -> select();
//            //ICE 拼装一个查询条件字符串 有时间的时候 应该整理到常用函数
//            foreach ($topCatagory as $value) {
//                $catString .= $value['catid'].',';
//            }
//            $catString = rtrim($catString,',');

            $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数

   //         $count = $Product -> where("`category_id` IN ($catString)") -> count(); // 查询满足要求的总记录数\
            $count = $Product -> count(); // 查询满足要求的总记录数
  //          $productList = $Product ->relation(true) -> where("`category_id` IN ($catString)") -> order('create_date desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
            $productList = $Product ->relation(true)  -> order('create_date desc') -> limit($Page->firstRow.','.$Page->listRows) -> select();
            $show = $Page->show();// 分页显示输出
        }   
        $this->assign('products',$productList);
        $this->assign('page',$show);// 赋值分页输出
		$this->display();

	}
	
	/**
	 * 新建产品页面
	 */
	public function create(){
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
        $product = D('product');

        $data = array();

        $data['product_name'] = $this->_post('product_name');
        $data['product_id'] = $this->_post('product_id');
        $data['category_id'] =  $this->_post('category_id');
        $data['content'] =  $this->_post('content');
        $data['arguements'] =  $this->_post('arguements');

        $data['memo'] =  $this->_post('memo');
        $data['keywords'] =  $this->_post('keywords');
        $data['brand'] =  $this->_post('brand');
        $data['market_price'] =  $this->_post('market_price');
        $data['member_price'] =  $this->_post('member_price');
        $data['mark'] =  $this->_post('mark');

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

        $data['amount'] =  $this->_post('amount');

        $data['image_ratio'] = $this->_post('ratio');


        $inputtime = time();

        $createDate = $this->_post("create_date");

        if ( isset ( $createDate )) {
            $inputtime = $createDate;
        }
        //ICE 转换时间格式
        $time = strtotime($this->_post("create_date"));
        $data['create_date'] = date('Y-m-d h:i:s',$time);

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
            $data['pic_ratio'] = $ratio;
        }
        //$data['attachment'] = str_replace("/uploads","uploads",$this->_post('attachment'));
        $data['attachment'] = './uploads/attachment/'.$this->_post('attachment_name');
        //ICE 附件地址
        $data['create_date'] = $inputtime;

        $ret = null;
        //ICE 自基础修改 获得ID 则编辑 未获得则新建

        $id = $this->_post("id");


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
        $product = $product_model->where(array( 'id' => $this->id ) )->find();
        $category_model = M('Category');
        $category = $category_model->where( array( 'catid' => $product['category_id']) )->find();
        $this->assign('product',$product);
        $this->assign('category',$category);
        $this->display();

    }
    public function teamplate(){
        $this->display();
    }
    public function editCategory() {
    /*
     *  修改产品分类 跳转至分类管理页面
     */
        $this->redirect('Category/managment', array('moduleId' => 2), 0 );
    }
    public function comment() {
        $this->display();
    }
}
