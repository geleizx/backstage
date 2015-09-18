<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetYoukuAction extends BaseAction
{

    public function __construct()
    {
        parent::__construct();

    }

    //节目频道列表
    public function channel_list()
    {
        $no = I('id');

        $filterId[1]  = 33; //电影
        $filterId[2] = 34; //电视剧
        $filterId[3] = 32; //综艺
        $filterId[4] = 35; //动漫
        $filterId[5] = 46; //纪录片
        import('ORG.Pinyin');
        $py = new PinYin();
        $pid = "f55500e53e3a3a89";
        $guid = "e9ae9a191aa66e78a77811a1e5715154";
        $pg = 1;
        $pz =20;
        if($no==1){$cid=1969;}elseif($no==2){$cid=1970;}elseif($no==3){$cid=1971;}elseif($no==4){$cid=1972;}elseif($no==5){$cid=1974;}
        $url = "http://cibn.api.3g.cp31.ott.cibntv.net/ycew/channel_video_list?pid=" . $pid . "&guid=" . $guid . "&filter_id=" . $filterId[$no] . "&pg=" . $pg . "&pz=" . $pz;
        $data = file_get_contents("$url");
        $tvlist = json_decode($data, 1);
        $pagesize =ceil($tvlist['total'] / $pz);

        for ($i = 0; $i < $pagesize; $i++) {

            foreach ($tvlist['results'] as $v) {

                if ($v['showname'] && $v['showid'] && $v['show_vthumburl_hd']) {
                    $mtime = time();
                    $v['showname'] = str_replace("'","", $v['showname']);
                    $v['showname'] = str_replace('"',"", $v['showname']);
                    $v['showname'] = str_replace('“',"", $v['showname']);
                    $v['showname'] = str_replace('”',"", $v['showname']);
                    $v['showname'] = str_replace('\\',"", $v['showname']);
                    $v['showname'] = str_replace('：',":", $v['showname']);
                    $title = $v['showname'];
                    $rowss = M('page')->where("title = '$title' and cid = $cid")->field("id")->find();

                   if($rowss['id']){
                       $id = $rowss['id'];

                       $pagetype=M('pagetype')->where("aid = $id and type = 6")->find();
                       if($pagetype){
                           $showid = $v['showid'];
                           $save['mtime'] = $mtime;
                           $updatepage = M('pagetype')->where("aid = $id and type = 6")->save($save);
                           M()->query("update  `tp_pagetype` set `mkey`='$showid',`mtime`='$mtime' where `aid` = '$id' and  type=6  ");
                       }else{
                           $showid = $v['showid'];
                           $savedata['mtime'] = $mtime;
                           $savedata['type'] = 6;
                           $savedata['aid'] = $id;
                           //$updatepage = M('pagetype')->add($savedata);
                           M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid`,'sort' ) value ( '$showid' , '$mtime'  , 6  , '$id',2)  ");
                       }

                   }else{
                       $v['showname'] = addslashes($v['showname']);
                       $where['pic'] = $v['show_vthumburl_hd'];
                       $where['verify'] = 1;
                       $where['cid'] = $cid;
                       $where['title'] = $v['showname'];
                       $where['uuid'] = $v['showid'];
                       $where['allnum'] = 1;
                       $area = implode(",",$v['area']);
                       $where['area'] = $area;
                       $where['pingy'] =  $py->getFirstPY($v['showname']);
                       $addpage = M('page')->add($where);
                       if ($addpage) {
                           $type['aid'] = $addpage;
                           $type['type'] = 6;
                           $type['mkey'] = $v['showid'];
                           $type['mtime'] = $mtime;
                           $type['sort'] = 2;
                            $addpagetype = M('pagetype')->add($type);
                       }
                   }
                    }

            }
        }
    }
    public function channel_sou(){
        $cid = I('id');
        $filmList = M('page')->where("cid = $cid")->field("title")->limit(100)->select();
        foreach($filmList as $key=>$value){
            $title = $value['title'];
            $uri  = "http://cibn.api.3g.cp31.ott.cibntv.net/ycew/cibn/showsearch?pid=f55500e53e3a3a89&keyword=".$title."&searchtype=1&guid=e9ae9a191aa66e78a77811a1e5715154&pg=1&pz=5";
            var_dump($uri);
            $result = file_get_contents($uri);
            $info = json_decode($result,1);
           var_dump($info);

        }
    }


}