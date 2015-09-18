<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetDsmAction extends BaseAction
{

    public function __construct()
    {
        parent::__construct();

    }

    //节目频道列表
    public function channel_list()
    {
        $allpage[1] = 335;
        $contentType[1] = "movie";
        $cid[1] = 1969;

        $allpage[2] = 147;
        $contentType[2] = "tv";
        $cid[2] = 1970;

        $allpage[3] = 88;
        $contentType[3] = "zongyi";
        $cid[3] = 1971;

        $allpage[4] = 70;
        $contentType[4] = "kids";
        $cid[4] = 1973;

        $allpage[5] = 3;
        $contentType[5] = "comic";
        $cid[5] = 1972;

        $allpage[6] = 4;
        $contentType[6] = "jilu";
        $cid[6] = 1974;

        import('ORG.Pinyin');
        $py = new PinYin();

        if (empty($_SESSION[access_token])) {
            $s = json_decode(file_get_contents("http://open.moretv.com.cn/authorize?appid=bf566f7456117820b397d26fa258c318"), 1);
            $authorize_code = $s[authorize_code];
            $key = md5("bf566f7456117820b397d26fa258c318_e6ecbf7200c7421719778e4748dfbaa8_" . $authorize_code);
            $sl = json_decode(file_get_contents("http://open.moretv.com.cn/get_access_token?authorize_code=" . $authorize_code . "&key=" . $key), 1);
            $access_token = $sl[access_token];
            $_SESSION[access_token] = $access_token;
        } else {
            $access_token = $_SESSION[access_token];
        }


        $id = $_GET['id'];
        $allpage = $allpage[$id] ;

        for ($pageNo = 1; $pageNo < $allpage; $pageNo++) {

            $no = $pageNo;
            $cid = $cid[$no];
            $contentType = $contentType[$no];


            $url = "http://open.moretv.com.cn/position/" . $contentType . "?access_token=" . $access_token;
            $str = file_get_contents($url);
            $tvlist = json_decode($str, 1);
            if ($tvlist[status] == 106) {
                $s = json_decode(file_get_contents("http://open.moretv.com.cn/authorize?appid=bf566f7456117820b397d26fa258c318"), 1);
                $authorize_code = $s[authorize_code];
                $key = md5("bf566f7456117820b397d26fa258c318_e6ecbf7200c7421719778e4748dfbaa8_" . $authorize_code);
                $sl = json_decode(file_get_contents("http://open.moretv.com.cn/get_access_token?authorize_code=" . $authorize_code . "&key=" . $key), 1);
                $access_token = $sl[access_token];
                $_SESSION[access_token] = $access_token;
                $url = "http://open.moretv.com.cn/position/" . $contentType . "?access_token=" . $access_token;
                $str = file_get_contents($url);
                $tvlist = json_decode($str, 1);
            }

            foreach ($tvlist['position']['positionItems'] as $v) {
                if ($v['item_title'] && $v['link_data'] && $v['item_icon1']) {

                    $mtime = time();
                    $v['item_title'] = str_replace("'","", $v['item_title']);
                    $v['item_title'] = str_replace('"',"", $v['item_title']);
                    $v['item_title'] = str_replace('“',"", $v['item_title']);
                    $v['item_title'] = str_replace('”',"", $v['item_title']);
                    $v['item_title'] = str_replace('\\',"", $v['item_title']);
                    $v['item_title'] = str_replace('：',":", $v['item_title']);

                    $title = $v['item_title'];
                    $rowss =  M('page')->where("title = '$title' and cid = $cid")->field("id")->find();
                    if ($rowss['id']) {
                        $id = $rowss['id'];
                        $pagetype=M('pagetype')->where("aid = $id and type = 4")->find();
                        if($pagetype){
                            $link_data =$v['link_data'];
                            $savedata['mtime'] = $mtime;
                           // $updatepage = M('pagetype')->where("aid = $id and type = 4")->save($savedata);
                            M()->query("update  `tp_pagetype` set `mkey`='$link_data',`mtime`='$mtime' where `aid` = '$id' and  type=4  ");
                        }
                    }else{
                        $v['item_title'] = addslashes($v['item_title']);
                        $item_tag = implode(",",$v['item_tag']);
                        $where['cat'] =  $item_tag;
                        $where['pic'] =  $v['item_icon1'];
                        $where['verify'] = 1;
                        $where['cid'] = $cid;
                        $where['title'] =  $v['item_title'];
                        $where['area'] =  $v['item_area'];
                        $where['year'] =  $v['item_year'];
                        $where['uuid'] =  $v['link_data'];
                        $item_cast = implode(",",$v['item_cast']);
                        $where['act'] =  $item_cast;
                        $item_director =  implode(",",$v['item_director']);
                        $where['director'] = $item_director;
                        $where['desc'] =  $v['description'];
                        $where['pingy'] =  $py->getFirstPY($v['item_title']);
                        $where['allnum'] =  1;

                        $addpage= M('page')->add($where);
                        if ($addpage)
                        {
                            $type['aid'] = $addpage;
                            $type['type'] = 4;
                            $type['mkey'] =$v['link_data'];
                            $type['mtime'] = $mtime;
                            $type['sort'] = 8;
                            $addpagetype = M('pagetype')->add($type);
                        }
                    }
                    // exit;


                }
            }
        }
    }
}



