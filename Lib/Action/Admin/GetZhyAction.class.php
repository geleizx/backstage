<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Caffrey
 * Date: 13-4-24
 * Time: 上午10:16
 *
 */

class GetZhyAction extends BaseAction
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
        $allpage[1] = ceil(2901/300) ;
        $parentCatgId[1] = '00050000000000000000000000019596' ;   //电影

        $allpage[2] = ceil(2010/300) ;
        $parentCatgId[2] = '00050000000000000000000000019614' ;  //电视剧

        $allpage[3] =  ceil(1238/300) ;
        $parentCatgId[3] = '00050000000000000000000000019633' ;    //动漫

        $allpage[4] = ceil(1069/300) ;
        $parentCatgId[4] = '00050000000000000000000000019627' ;   //综艺

        $allpage[5] =  ceil(505/300) ;
        $parentCatgId[5] = '00050000000000000000000000019645' ; //纪录片

        $allpage[6] = ceil(618/300) ;
        $parentCatgId[6] = '000508620939077472006' ;   //少儿


        if($no==1){$cid=1969;}elseif($no==2){$cid=1970;}elseif($no==3){$cid=1972;}elseif($no==4){$cid=1971;}elseif($no==5){$cid=1974;}elseif($no==6){$cid=1973;}
        $allpage = $allpage[$no];
        $parentCatgId = $parentCatgId[$no];
        for ($pageNo = 1; $pageNo < $allpage; $pageNo++) {
            echo $pageNo . "";
            $url = "http://s.epg.ott.cibntv.net/epg/web/v40/program!getCommonMovieList.action?parentCatgId=".$parentCatgId."&templateId=00080000000000000000000000000050&pageNumber=".$pageNo."&pageSize=300";
            $str = file_get_contents($url);
            $tvlist = json_decode($str, 1);

            foreach ($tvlist['programList'] as $v) {

                if ($v['id'] && $v['name'] && $v['image']) {
                    $mtime = time();
                    $v['name'] = str_replace("'","", $v['name']);
                    $v['name'] = str_replace('"',"", $v['name']);
                    $v['name'] = str_replace('“',"", $v['name']);
                    $v['name'] = str_replace('”',"", $v['name']);
                    $v['name'] = str_replace('\\',"", $v['name']);
                    $v['name'] = str_replace('：',":", $v['name']);

                    $title = $v['name'];
                    $getInfoUrl = "http://s.epg.ott.cibntv.net/epg/web/v40/program!getMovieDetail.action?programSeriesId=".$v['id']."&templateId=00080000000000000000000000000050";
                    $strInfo = file_get_contents($getInfoUrl);
                    $info = json_decode($strInfo, 1);
                    $rowss = M('page')->where("title = '$title' and cid = $cid")->field("allnum,id")->find();
                    $id = $rowss['id'];
                    if (empty($rowss)) {
                        $v['name'] = addslashes($v['name']);
                        $where['cat'] =  $info['class'];
                        $where['pic'] =  $v['image'];
                        $where['verify'] = 1;
                        $where['cid'] = $cid;
                        $where['title'] = $v['name'];
                        $where['area'] =  $info['zone'];
                        $where['year'] =  $v['releaseDate'];
                        $where['uuid'] =  $info['sources'][0]['videoid'];
                        $where['act'] =  $v['actor'];
                        $where['director'] =  $v['director'];
                        $where['allnum'] =  1;
                        $where['pingy'] =  $py->getFirstPY($v['name']);
                        $addpage= M('page')->add($where);
                        if ($addpage)
                        {
                            $type['aid'] = $addpage;
                            $type['type'] = 9;
                            $type['mkey'] =  $info['sources'][0]['videoid'];
                            $type['mtime'] = $mtime;
                            $type['sort'] = 9;
                            $addpagetype = M('pagetype')->add($type);
                            //
                        }
                    } else {
                        $savedata['mkey'] =  $info['sources'][0]['videoid'];
                        $savedata['mtime'] = $mtime;
                        $updatepage = M('pagetype')->where("aid = $id and type = 9")->save($savedata);
                        //exit;
                    }
                }


            }


        }
    }

}