<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetHsAction extends BaseAction
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
        //1电影;2电视剧
        $id = $_GET['id'];
        if ($id == 1) {
            $cid = 1969;
        } elseif ($id == 2) {
            $id=3;
            $cid = 1970;
        }
        $result = M('page')->where("cid = $cid")->field("title")->order("id desc")->select();
        foreach ($result as $v) {
            $encode = mb_detect_encoding($v['title'], array('ASCII', 'UTF-8', 'GB2312', 'GBK'));
            if (!$encode == 'UTF-8') {
                $v['title'] = iconv('UTF-8', $encode, $v['title']);
            }
            $string = $id . "|" . $v['title'] . "|" . "mypassword";
            $code = md5($string);
            $uri = "http://bs3-api.sdk.wasu.tv/XmlData/Search?keyword=" . $v['title'] . "&type=" . $id . "&code=" . $code;
            $str = file_get_contents($uri);
            $vodlist = json_decode($str, 1);

            foreach ($vodlist['result'] as $val) {

                if ($val['title'] && $val['id'] && $val['picUrl'] && $val['title'] == $v['title']) {
                    $mtime = time();
                    $v['title'] = str_replace("'","", $v['title']);
                    $v['title'] = str_replace('"',"", $v['title']);
                    $v['title'] = str_replace('“',"", $v['title']);
                    $v['title'] = str_replace('”',"", $v['title']);
                    $v['title'] = str_replace('\\',"", $v['title']);
                    $v['title'] = str_replace('：',":", $v['title']);
                    $title = $v['title'];
                    $rowss = M('page')->where("title = '$title' and cid = $cid")->field("id,act,director")->find();
                    if(empty($rowss['director'])){
                        $dir['director'] = $val['director'];
                        $uppaged = M('page')->where("title = '$title' and cid = $cid")->save($dir);
                    }
                    if(empty($rowss['act'])){
                        $act['act'] = $val['actors'];
                        $uppagea = M('page')->where("title = '$title' and cid = $cid")->save($act);
                    }
                    if ($rowss['id']) {
                        $id = $rowss['id'];
                        $pagetype = M('pagetype')->where("aid = $id and type = 5")->find();
                        if ($pagetype) {
                            $mkey = $val['id'];
                            $savedata['mtime'] = $mtime;
                            M()->query("update  `tp_pagetype` set `mkey`='$mkey',`mtime`='$mtime' where `aid` = '$id' and  type=5  ");
                           // $updatepage = M('pagetype')->where("aid = $id and type = 5")->save($savedata);
                        }else{
                            $mkey = $val['id'];
                            M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid`,`sort` ) value ( '$mkey' , '$mtime'  , 5  , '$id',3)  ");
                        }

                    }
                }
            }


        }
    }

    public function channel_list_recommend(){
        //1电影;2电视剧
        $id = I('id');
        if($id==1){
            $tag = 'movie';
            $cid = 1969;
        }elseif($id==2){
            $tag = 'series';
            $cid = 1970;
        }elseif($id==3){
            $tag = 'children';
            $cid = 1973;
        }elseif($id==4){
            $tag = 'record';
            $cid = 1974;
        }elseif($id==5){
            $tag = 'variety';
            $cid = 1971;
        }
        import('ORG.Pinyin');
        $py = new PinYin();
        $url = "http://bs3-api.sdk.wasu.tv/XmlData/Recommend?tag=".$tag;
        $str = file_get_contents($url);
        $tvlist = json_decode($str,1);
        foreach($tvlist['data'] as $v ) {
            if ($v['title'] && $v['id'] && $v['picUrl']) {
                $mtime = time();
                $v['title'] = str_replace("'","", $v['title']);
                $v['title'] = str_replace('"',"", $v['title']);
                $v['title'] = str_replace('\\',"", $v['title']);
                $title = $v['title'];
                $rowss = M('page')->where("title = '$title' and cid = $cid")->field("allnum,id")->find();

                if ($rowss['id']) {
                    $savedata['mkey'] = $v['id'];
                    $savedata['mtime'] = $mtime;
                    $updatepage = M('pagetype')->where("aid =" . $rowss['id'] . "and type = 5")->save($savedata);
                    //exit;
                }else{
                    $v['title'] = addslashes($v['title']);
                    $where['pic'] = $v['picUrl'];
                    $where['verify'] = 1;
                    $where['cid'] = $cid;
                    $where['title'] = $v['title'];
                    $where['year'] = $v['year'];
                    $where['uuid'] = $v['id'];
                    $where['act'] = $v['actors'];
                    $where['director'] = $v['director'];
                    $where['allnum'] = 1;
                    $where['pingy'] = $py->getFirstPY($v['title']);
                    $addpage = M('page')->add($where);
                    if ($addpage) {
                        $type['aid'] = $addpage;
                        $type['type'] = 5;
                        $type['mkey'] = $v['id'];
                        $type['mtime'] = $mtime;
                        $type['sort'] = 3;
                        $addpagetype = M('pagetype')->add($type);
                    }
                }
            }
        }
        }
}