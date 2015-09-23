<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetTencentAction extends BaseAction
{

    public function __construct()
    {
        parent::__construct();

    }

    //节目频道列表
    public function channel_list()
    {


        import('ORG.Pinyin');
        $py = new PinYin();
        $type = $_GET['id'];
        //1代表只搜索电影、2代表只搜索电视剧   3少儿  4 动漫 9 纪录片 10综艺
        if ($type == 1) {
            $cid = 1969;
        } elseif ($type == 2) {
            $cid = 1970;
        } elseif ($type == 3) {
            $cid = 1973;
        } elseif ($type == 4) {
            $cid = 1972;
        } elseif ($type == 9) {
            $cid = 1974;
        } elseif ($type == 10) {
            $cid = 1971;
        }
        $result = M('page')->where("cid = $cid")->field("title")->limit(10)->select();

        foreach ($result as $v) {
            $url = 'http://tv.video.qq.com/i-tvbin/qtv_video/search/get_search_video?page_size=10&page_num=0&format=json&search_type=' . $type . '&key=' . $v['title'] . '&Q-UA=PT%3DSNMAPP';
            $str = file_get_contents($url);
            $vodlist = json_decode($str, 1);
            $vodInfo = $vodlist['data']['list'];
            foreach($vodInfo as $k=>$value){
                if ($value['cover_id'] && $value['title']&&$value['year']) {
                    $mtime = time();
                    $value['title'] = str_replace("'","", $value['title']);
                    $value['title'] = str_replace('"',"", $value['title']);
                    $value['title'] = str_replace('“',"", $value['title']);
                    $value['title'] = str_replace('”',"", $value['title']);
                    $value['title'] = str_replace('\\',"", $value['title']);
                    $value['title'] = str_replace('：',":", $value['title']);

                    $title = $value['title'];
                    $year =  explode("(",$value['year']);
                    $year = substr($year[1],0,4);
                    $rowss = M('page')->where("title = '$title' and cid= $cid")->field("id")->find();
                    if ($rowss) {
                        $id = $rowss['id'];
                        $pagetype = M('pagetype')->where("aid = $id and type = 7")->field("id")->find();
                        if ($pagetype) {
                            $cover_id = $value['cover_id'];
                            $savedata['mtime'] = $mtime;
                            M()->query("update  `tp_pagetype` set `mkey`='$cover_id',`mtime`='$mtime' where `aid` = '$id' and  type=7  ");
                            //$updatepage = M('pagetype')->where("aid = $id and type = 7")->save($savedata);
                        } else {
                            $cover_id = $value['cover_id'];
                            $savedata['mtime'] = $mtime;
                            $savedata['type'] = 7;
                            $savedata['aid'] = $id;
                            $savedata['sort'] = 1;
                            M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid` ) value ( '$cover_id' , '$mtime'  , 7  , '$id')  ");
                            //$updatepage = M('pagetype')->add($savedata);
                        }
                    }
                }
            }

        }
    }
}
