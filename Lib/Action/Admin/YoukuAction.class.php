<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class YoukuAction extends Action
{

    public function __construct()
    {
        parent::__construct();
    }

    //节目频道列表
    public function channel_list()
    {
        $pid = "40bf5dff3a73f6e7";
        $url = "http://test.cibn.api.ott.youku.com/ycew/channel_list?pid=".$pid;
        $data = file_get_contents("$url");
        $info = json_decode($data,1);
        $result = $info['results']['data'];
        foreach($result as $k=>$val){
            $filterId[] =$val['filter_id'];
        }
      var_dump($result);

    }

    //节目频道视频列表
    public function channel_video_list( ){
        $filterId = array(15,16,17,18);
        import('ORG.Pinyin');
        $py = new PinYin();
        $pid = "40bf5dff3a73f6e7";
        $guid = "e9ae9a191aa66e78a77811a1e5715154";
        $pg = 1;
        $pz = 900000;
        for($i=0;$i<count($filterId);$i++){
            $url  = "http://test.cibn.api.ott.youku.com/ycew/channel_video_list?pid=".$pid."&guid=".$guid."&filter_id=".$filterId[$i]."&pg=".$pg."&pz=".$pz;
            $data  = file_get_contents("$url");
            $result  = json_decode($data,1);

            foreach($result['results'] as $key=>$value){

                $condition["title"] = array("eq",$value['showname']);
                $relationToPage =  M('page')->where($condition)->find();
                if($relationToPage){
                    $where['aid'] = $relationToPage['id'];
                    $where['type'] = 6;
                    $where['mkey'] = $value['showid'];
                    $where['mtime'] = time();
                    //$relationToPageTypeAdd =  M('pagetype')->add($where);

                }else{
                    var_dump($value['showname']);
                    var_dump($value);
                }
//                 $where['desc'] = $value['middle_stripe'];
//                 $where['mark'] = $value['stripe_bottom'];
//                 $where['pic'] = $value['show_vthumburl_hd'];
//                 $where['cid'] = $value['cid'];
//                 $where['title'] = $value['showname'];
//                 $area  =implode(",", $value['area']);
//                 $where['area'] = $area;
//                 $where['pingy'] = $py->getFirstPY($value['showname']);


//               $add = M('appsoulist')->add($where);



//                if($add){
//                    $type['aid'] = $add;
//                    $type['type'] = 6;
//                    $type['mkey'] = $value['showid'];
//                    $type['mtime'] = time();
//                    $addType = M('appsoutype')->add($type);
//                }
            }
        }


    }
}
