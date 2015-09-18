<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetTuziAction extends BaseAction
{

    public function __construct()
    {
        parent::__construct();

    }

    //节目频道列表
    public function channel_list(){
        $allpage = 10 ;
        $topID = 123 ;
        $id = $_GET['id'];

        import('ORG.Pinyin');
        $py = new PinYin();


        for($pageNo = 1 ; $pageNo < $allpage ;  $pageNo++ ){
            echo $pageNo."";

            $str = $this-> poststr($id , $pageNo );
            if($id==2){ //film
                $cid = 1969;
            }elseif($id==3){ //tv
                $cid = 1970;
            }elseif($id==4) {  //
                $cid = 1972;
            }elseif($id==5) {
                $cid = 1974;  //jilu
            }elseif($id==6) {
                $cid = 1971;  //zongyi
            }
            $tvlist = json_decode($str,1);

            foreach ($tvlist['data']['list'] as $v) {

                if( $v['name'] &&  $v['vid']   ){
                    $mtime = time();
                    $v['name'] = str_replace("'","", $v['name']);
                    $v['name'] = str_replace('"',"", $v['name']);
                    $v['name'] = str_replace('“',"", $v['name']);
                    $v['name'] = str_replace('”',"", $v['name']);
                    $v['name'] = str_replace('\\',"", $v['name']);
                    $v['name'] = str_replace('：',":", $v['name']);
                    $title = $v['name'];

                    $rowss = M('page')->where("title = '$title' and cid = $cid")->field("id,title")->find();

                    if ($rowss['id']) {
                        $id = $rowss['id'];
                        $pagetype=M('pagetype')->where("aid = $id and type = 3")->find();
                        if($pagetype){
                            $vid = $v['vid'];
                            $savedata['mtime'] = $mtime;
                            M()->query("update  `tp_pagetype` set `mkey`='$vid',`mtime`='$mtime' where `aid` = '$id' and  type=3  ");
                        }
                        else{
                            $vid = $v['vid'];
                            $savedata['mtime'] = $mtime;
                            $savedata['type'] = 3;
                            $savedata['aid'] = $id;
                            M()->query("insert into `tp_pagetype` ( `mkey` , `mtime` ,`type` ,`aid`,`sort` ) value ( '$vid' , '$mtime'  , 3  , '$id',7)  ");
                        }
                    }else{
                        $v['name'] = addslashes($v['name']);
                        $where['pic'] = $v['cover'];
                        $where['desc'] = $v['desc'];
                        $where['verify'] = 1;
                        $where['cid'] = $cid;
                        $where['title'] = $v['name'];
                        $where['pingy'] =  $py->getFirstPY($v['name']);
                        $where['year'] = substr($v['showtime'],0,4);
                        $where['uuid'] = $v['vid'];
                        $where['allnum'] = 1;
                        $where['act'] = $v['starring'];
                        $where['director'] = $v['director'];
                        $addpage = M('page')->add($where);
                        if ($addpage) {
                            $type['aid'] = $addpage;
                            $type['type'] = 3;
                            $type['mkey'] = $v['vid'];
                            $type['mtime'] = $mtime;
                            $type['sort'] = 7;
                            $addpagetype = M('pagetype')->add($type);
                        }
                    }

                }


            }
        }
    }

   public function poststr($a,$b){

        $post_data =
            array(
                'method=core.video.list',
                'category='.$a,
                'page='.$b,
                'apptoken=7f6811184f578ab84c09b84cc36d2d5e',
                'token=95e9660c7843b020ddf71cc91a26a115',
                'sort=utime',
                'extend=name%2Ccategory%2Ccover%2Cdirector%2Cstarring%2Cdesc%2Cdbscore%2Cisend%2Ctnum%2Cunum%2Cnutime%2Cshowtime%2Cresolution%2Cactor%2Csource&__ifrom=openresty_lua',
            );

        $post_data = implode('&',$post_data);

        $url='http://api.16tree.com:8085/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
        return  $result;





    }
}