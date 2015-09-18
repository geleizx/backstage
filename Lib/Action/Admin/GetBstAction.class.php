<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetBstAction extends BaseAction
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
        $t = I("id");
        if( empty($t)){$t=1;}
        if($t == 1 ){
            $allpage = intval(2668/100)+1 ;
            $topID = "movie" ;
            $cid = 1969 ;
        }elseif($t == 2 ){
            $allpage = intval(732/100)+1 ;
            $topID = "tv" ;
            $cid = 1970 ;
        }elseif($t == 3 ){
            $allpage = intval(647/100)+1 ;
            $topID = "variety" ;   //zongyi
            $cid = 1971 ;
        }elseif($t == 4 ){
            $allpage = intval(389/100)+1 ;
            $topID = "cartoon" ;
            $cid = 1972 ;
        }

        for($pageNo = 1 ; $pageNo <$allpage ;  $pageNo++ ) {
            echo $pageNo . "";

            $url = "http://ott-api.fun.tv/api/v3/retrive?mtype=" . $topID . "&region=all&tag=all&rdate=all&order=daynum&statdate=all&pg=" . $pageNo . "&pz=100&pv=101&show_tb=1";
            $str = file_get_contents($url);
            $tvlist = json_decode($str, 1);
            foreach ($tvlist['data'] as $v) {
                if ($v['name_cn'] && $v['mediaid']) {
                    $mtime = time();
                    $v['name_cn'] = str_replace("'","", $v['name_cn']);
                    $v['name_cn'] = str_replace('"',"", $v['name_cn']);
                    $v['name_cn'] = str_replace("“","", $v['name_cn']);
                    $v['name_cn'] = str_replace("”","", $v['name_cn']);
                    $v['name_cn'] = str_replace('\\',"", $v['name_cn']);
                    $v['name_cn'] = str_replace('：',":", $v['name_cn']);

                    $title =  $v['name_cn'];
                    $rowss = M('page')->where("title ='$title' and cid = $cid")->field("id")->find();
                    if ($rowss) {
                        $id = $rowss['id'];
                        $pagetype = M('pagetype')->where(array("aid"=>$rowss['id'],"type"=>2))->find();
                        if (empty($pagetype)) {
                           $mediaid = $v['mediaid'];
                            $savedata['mtime'] = $mtime;
                            $savedata['type'] =2;
                            $savedata['aid'] = $rowss['id'];
                            $savedata['sort'] = 4;
                             M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid`,`sort` ) value ( '$mediaid' , '$mtime'  , 2  , '$id',4)  ");
                       }else{
                            $mediaid = $v['mediaid'];
                            $savedata['mtime'] = $mtime;
                            M()->query("update  `tp_pagetype` set `mkey`='$mediaid',`mtime`='$mtime' where `aid` = '$id' and  type=2  ");
                        }
                    }
                    else {
                        $v['name_cn'] = addslashes($v['name_cn']);
                        $cat = implode(",",$v['tags']);
                        $where['cat'] = $cat;
                        $where['pic'] = $v['p_pic'];
                        $where['verify'] = 1;
                        $where['cid'] = $cid;
                        $where['title'] = $v['name_cn'];
                        $area  =implode(",",$v['countries']);
                        $where['area'] = $area;
                        $where['year'] = substr($v['releasedate'],0,4);
                        $where['uuid'] = $v['mediaid'];
                        $where['allnum'] = 1;
                        $where['pingy'] =  $py->getFirstPY($v['name_cn']);
                        $addpage = M('page')->add($where);
                        if ($addpage) {
                            $type['aid'] = $addpage;
                            $type['type'] = 2;
                            $type['mkey'] = $v['mediaid'];
                            $type['mtime'] = $mtime;
                            $type['sort'] = 4;
                            $addpagetype = M('pagetype')->add($type);
                        }

                    }
                }
            }

        }
        }
}