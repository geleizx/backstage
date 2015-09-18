<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-24
 * Time: 上午10:16
 * 
 */

class InterfaceAction extends Action{

    public function __construct(){
        parent::__construct();
    }
    //推荐直播频道接口
    public function recommendedChannelData(){
        $categoryModel = D('Category');
        $product = M('product');
        $relation = false;
        $where['status'] =array("eq",2);
        $categoryModel = D('Category');
        $list = $categoryModel->relation($relation)->where($where)->field("catid,catname,image,status")->limit(0,8)->select();
        //$sqls = $categoryModel->getLastSql();
        foreach($list as $k=>$val){
            $subContent = $product->where("category_id = ".$list[$k]['catid']." and status=1")->field("product_name,type,param1,param2,appid")->order("sort asc")->select();
            $list[$k]['app'] = $subContent;
        }
        //$BaseAction =A("Admin/Base");
        //$list = $BaseAction->postmem($sqls,$type = "read");
        $jsonArr = json_encode($list);
        echo $jsonArr;
    }
    //直播频道接口
    public function requestChannelName(){
        $category = M('category');
        $catid = $_GET['catid'];
        $screen =$_GET['screen'];
        if($catid ==99||$screen==1){
            $catList = $category->where("parentid=0 and status !=0 and  module=2 and screening=1")->field("catid,catname,screening")->order("listorder asc")->select();
        }else{
            $catList = $category->where("parentid=0 and status !=0 and  module=2 and screening = 0")->field("catid,catname,screening")->order("listorder asc")->select();
            array_splice($catList,2,0, array("2"=>array('catid' => "99",'catname' =>  '地方')));
        }

        //主分类json
        $channel = '{"channel": '.json_encode($catList).'}';
        if(!$catid){
            $catid= $catList[0]['catid'];
        }
        $data = $this->requestChannel($catid);

        foreach($catList as $k=>$v){
            $url = "http://sou.dangbei.com/Admin/Interface/requestChannelName?catid=".$v['catid']."&screen=".$v['screening'];
            $catList[$k]['url'] = $url;
        }

        $newJson = json_encode(
            array_merge(
                array('channel' => $catList),
                array('channelList' => $data)
            )
        );

        echo $newJson;
    }

    public function requestChannel($catid){
        $category = M('category');
        $list = $this->_getNav($catid);
//        $jsonArr = '{"data":'.json_encode($list).'}';
        return $list;
    }



    private function _getNav($catid)
    {

        $film = file_get_contents("http://appsou.dangbei.com/api/config.php");
        $result = json_decode($film,1);
        $category = M('category');
        $product = M('product');

        if($catid ==99){  //地方城市列表
            $navs =$category->where("parentid=0 and status !=0 and  module=2 and screening=1")->field("catid,catname,image")->order("listorder asc")->select();

            $catid = $navs[0]['catid'];
            $navs = $category->where("parentid=$catid and status !=0 and  module=2 ")->field("catid,catname,image")->order("listorder asc")->select();

        }else{
            $navs = $category->where("parentid=$catid and status !=0 and module=2")->field("catid,catname,image")->order("listorder asc")->select();
            if(!$navs){
                $catname = $category->where("catid = $catid and module=2 and status !=0")->field("catname")->find();
                $catname = $catname['catname'];
                $selectZjPage = $category->where("catname = '$catname' and module = 19")->find();

                $navs = M('category')->where("parentid = $selectZjPage[catid] and  status !=0 and module = 19")->field("catid,catname,image,albumid")->order("listorder asc")->select();

                foreach ($navs as $key=> $val) {
                    $navs[$key]['catid'] = $val['albumid'];
                }

            }
        }
            if($navs){
                foreach($navs as $key=>$val){
                    $subContent = $product->where("category_id = ".$navs[$key]['catid']." and status!=0")->field("product_name,param1,param2,appid")->order("sort asc")->select();

//                    foreach($subContent as $k=>$v){
//                        $subContent[$k]['appinfo'] =  $this->getappInfo($subContent[$k]['appid'],$result);
//                    }
                    $navs[$key]['app'] = $subContent;

                }
            }
        return $navs;

    }
    //播放源信息获取
//        public function getappInfo($id,$result){
//            foreach($result as $k=>$v){
//                $appList[$v['appid']] = $v;
//            }
//           return $appList[$id];
//        }
}