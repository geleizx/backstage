<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetBeeAction extends BaseAction
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
        //1.电影  2.电视剧 4综艺 5 动漫少儿 9 记录
        $fileList = M('page')->field("title")->select();
        foreach ($fileList as $key => $value) {
            $uri = "http://www.beevideo.tv/videoplus/other/search.action?key=" . $value['title'] . "&type=3";
            $content = file_get_contents("$uri");
            $resutl = json_decode($content, 1);

            foreach ($resutl['videolist'] as $k => $val) {
                if ($val['channelId'] && $val['imgUrl'] && $val['videoId'] && $value['title'] == $val['videoName']) {
                    $mtime = time();
                    $value['title'] = str_replace("'","", $value['title']);
                    $value['title'] = str_replace('"',"", $value['title']);
                    $value['title'] = str_replace('“',"", $value['title']);
                    $value['title'] = str_replace('”',"", $value['title']);
                    $value['title'] = str_replace('\\',"", $value['title']);
                    $value['title'] = str_replace('：',":", $value['title']);
                
                    $title = $value['title'];
                    $channelId = $val['channelId'];
                    if ($channelId == 1) {
                        $cid = 1969;
                    } elseif ($channelId == 2) {
                        $cid = 1970;
                    } elseif ($channelId == 4) {
                        $cid = 1971;
                    } elseif ($channelId == 5) {
                        $cid = 1970;
                    } elseif ($channelId == 9) {
                        $cid = 1974;
                    }
                    $rowss = M('page')->where(array("title"=>$title,"cid"=>$cid))->field("id")->find();
                    if ($rowss) {
                        $id = $rowss['id'];
                        $pagetype = M('pagetype')->where(array("aid" => $id,"type" => 8))->find();
                        if ($pagetype) {
                            $videoId = $val['videoId'];
                            //$savedata['mtime'] = $mtime;
                            //$updatepage = M('pagetype')->where(array("aid" => $id,"type" => 8))->save($savedata);
                            M()->query("update  `tp_pagetype` set `mkey`='$videoId',`mtime`='$mtime' where `aid` = '$id' and  type=8  ");
                        } else{
                            $videoId = $val['videoId'];
                            $savedata['mtime'] = $mtime;
                            $savedata['type'] = 8;
                            $savedata['aid'] = $id;
                            $savedata['sort'] = 6;
                            M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid`,`sort` ) value ( '$videoId' , '$mtime'  ,8  , '$id',6)  ");
                        }
                    }
                }
            }

        }
    }
}