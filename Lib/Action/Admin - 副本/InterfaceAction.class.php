<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-24
 * Time: 上午10:16
 * 
 */

class InterfaceAction extends Action{
    private function _getNav($catid = 0)
    {
        $category = M('category');
        $product = M('product');
        $navs = $category->where("parentid=$catid and status !=0")->field("catid,catname,image,picture,listorder,status")->order("listorder asc")->select();
        if($navs){
            foreach ($navs as $key => $value)
            {
                $subNavs = $this->_getNav($value['catid']);//如果有子菜单
                if ($subNavs)
                {
                    $navs[$key]['sub'] = $subNavs;
                    foreach($subNavs as $k=>$val){
                        $subContent = $product->where("category_id = ".$subNavs[$k]['catid']." and status=1")->field("product_name,status,ishot,type,param1,param2")->order("id asc")->select();
                        $navs[$key]['sub'][$k]['subApp'] = $subContent;
                    }

                }
            }
        }
        return $navs;
    }
    public function __construct(){
        parent::__construct();
    }
    //请求数据
    public function requestDataReturn(){
        $list = $this->_getNav($catid = 0);
        $jsonArr = '{"data":'.json_encode($list).',"status":1,"message":""}';
       echo $jsonArr;
    }


}