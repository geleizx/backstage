<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetVstAction extends BaseAction
{

    public function __construct()
    {
        parent::__construct();

    }
    //vst uuid查询详细信息
    public function channel_list_info(){
        $no = I('no');
        if($no==1){$cid=1969;}elseif($no==2){$cid=1970;}elseif($no==3){$cid=1972;}elseif($no==4){$cid=1971;}elseif($no==5){$cid=1974;}elseif($no==6){$cid=1973;}
        $info = M('page')->where("cid = $cid")->field("uuid,id")->select();
        foreach($info as $v){
            $url ="http://cdn.91vst.com/api/videoinfo.action?uuid=" . $v['uuid'];
            $content = file_get_contents($url);
            $result = json_decode($content,1);
            $where['desc'] = $result['data']['content'];
            $where['director'] = $result['data']['director'];
            $updatepagetype = M('page')->where("id =".$v['id'])->save($where);
        }

    }
    //节目频道列表
        public function channel_list()
    {
        import('ORG.Pinyin');
        $py = new PinYin();
        $no = I('id');
        $allpage[1] = 337 ;
        $topID[1] = 1 ;   //电影

        $allpage[2] = 143 ;
        $topID[2] = 2 ;  //电视剧

        $allpage[3] = 81 ;
        $topID[3] = 3 ;    //动漫

        $allpage[4] = 70 ;
        $topID[4] = 4 ;   //综艺

        $allpage[5] = 80 ;
        $topID[5] = 5 ; //纪录片

        $allpage[6] = 28 ;
        $topID[6] = 8 ;   //少儿


        if($no==1){$cid=1969;}elseif($no==2){$cid=1970;}elseif($no==3){$cid=1972;}elseif($no==4){$cid=1971;}elseif($no==5){$cid=1974;}elseif($no==6){$cid=1973;}
        $allpage = $allpage[$no];
        $topID = $topID[$no];
        for ($pageNo = 1; $pageNo < $allpage; $pageNo++) {
            echo $pageNo . "";
            $url = "http://cdn.91vst.com/api/mvlist.action?pageNo=" . $pageNo . "&count=60&topID=" . $topID . "&sort=0&area=all&quality=all&type=all&year=all";
            $str = file_get_contents($url);
            $tvlist = json_decode($str, 1);

            foreach ($tvlist['video'] as $v) {

                if ($v['title'] && $v['uuid'] && $v['pic']) {
                    $mtime = time();
                    $v['title'] = str_replace("'","", $v['title']);
                    $v['title'] = str_replace('"',"", $v['title']);
                    $v['title'] = str_replace('“',"", $v['title']);
                    $v['title'] = str_replace('”',"", $v['title']);
                    $v['title'] = str_replace('\\',"", $v['title']);
                    $v['title'] = str_replace('：',":", $v['title']);
                    $title = $v['title'];
                    $rowss = M('page')->where("title = '$title' and cid = $cid")->field("allnum,id")->find();
                    $id = $rowss['id'];
                    if (empty($rowss)) {
                        $v['title'] = addslashes($v['title']);
                        $where['cat'] =  $v['cat'];
                        $where['pic'] =  $v['pic'];
                        $where['verify'] = 1;
                        $where['cid'] = $cid;
                        $where['title'] = $v['title'];
                        $where['area'] =  $v['area'];
                        $where['year'] =  $v['year'];
                        $where['uuid'] =  $v['uuid'];
                        $where['act'] =  $v['act'];
                        $where['allnum'] =  1;
                        $where['pingy'] =  $py->getFirstPY($v['title']);
                        $addpage= M('page')->add($where);
                        if ($addpage)
                        {
                            $type['aid'] = $addpage;
                            $type['type'] = 1;
                            $type['mkey'] =$v['uuid'];
                            $type['mtime'] = $mtime;
                            $type['sort'] = 5;
                            $addpagetype = M('pagetype')->add($type);
                            //
                            $url ="http://cdn.91vst.com/api/videoinfo.action?uuid=" . $v['uuid'];
                            $content = file_get_contents($url);
                            $result = json_decode($content,1);
                            // $where['desc'] = $result['data']['content'];
                            $where['director'] = $result['data']['director'];
                            $updatepagetype = M('page')->where("id = $addpage ")->save($where);
                        }
                    } else {
                        $savedata['mkey'] = $v['uuid'];
                        $savedata['mtime'] = $mtime;
                        $updatepage = M('pagetype')->where("aid = $id and type = 1")->save($savedata);
                        //exit;
                    }
                }


            }


        }
    }

}