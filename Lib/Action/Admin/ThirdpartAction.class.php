<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-24
 * Time: 上午10:16
 * 
 */

class ThirdpartAction extends  BaseAction{

    public function __construct(){
        parent::__construct();
    }

//**************深度清理app列表***********//
    public function cleanapi(){
        $token = $_GET['token'];
        if($token!=md5('znds')){
            echo json_encode(array('error'=>"notoken"));
            exit;
        }
        $list = M('clean')->select();
        echo json_encode(array("data"=>$list));
    }

//**************屏保*******************//
    public function screensaverapi(){
        $token = I('token');
        if($token!=md5('znds')){
            echo json_encode(array('error'=>"notoken"));
            exit;
        }
        $list = M('pic')->select();
        $date = M('pic')->order("date desc")->field("date")->find();
        $result['list'] = $list;
        $result = array_merge($date,$result);
        echo json_encode(array("data"=>$result));
    }
    
 //***************替换符号 ： :*************************
    
//    public function replace(){
//        $data = M('page')->where("title LIKE '%“%'")->field("title,id")->select();
//        foreach($data as $val){
//             $val['title'] = str_replace('“'," ", $val['title']);
//             $val['title'] = str_replace('”'," ", $val['title']);
//            $where['title'] = $val['title'];
//            $save = M('page')->where("id = ".$val['id'])->save($where);
//        }
//        
//     
//    }

}