<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-24
 * Time: 上午10:16
 * 
 */

class AppsouAction  extends Action
{

    public function __construct()
    {
        parent::__construct();
    }

    public function top()
    {
        $version = I('versioncode');
        //请求直播推荐接口
        $conf = file_get_contents("http://sou.dangbei.com/Admin/Interface/recommendedChannelData");
        $configstr = json_decode($conf, 1);
        if ($version > 5) {

            $time =  date("Y-m-d H:i:s",strtotime('-1 days'));
            $needtime = strtotime($time);

            $list = M()->table("tp_pagesou b,tp_page a")->where("a.id = b.aid and b.mtime > $needtime  and verify=1")->group("b.aid")->order("count(b.aid) desc")->limit(8)->select();

            // $list = postmem($sqlb, "arr");
            if($list==''){
                $list = M()->table("tp_page")->order("year desc")->limit(8)->select();
            }
            foreach ($list as $v) {
                if (strstr($v['title'], "性爱") || strstr($v['title'], "党") || strstr($v['title'], "共产")) {
                    continue;
                }
                $outlisttype[] = $v;
            }
            $newJson = json_encode(
                array_merge(
                    array('hotlive' => $configstr),
                    array('hotsou' => $outlisttype)
                )
            );
            echo $newJson;
        } else {
            //大家都在搜
            $list = M('page')->order("allnum desc")->limit(8)->select();

            foreach ($list as $v) {
                if (strstr($v['title'], "性爱") || strstr($v['title'], "党") || strstr($v['title'], "共产")) {
                    continue;
                }
                $outlisttype[] = $v;
            }

            echo json_encode($outlisttype);
        }

    }

   /* 热门榜单*/
    public function hottop()
    {

        $version = I('version');
        $cid = I('topId');
        if ($version == '') {
            echo json_encode(array("error" => "noversioin"));
        }
        if ($cid == '') {
            $cid = 1969;
        }
        if ($version > 6) {

            $time = date("Y-m-d H:i:s", strtotime('-1 days'));
            $needtime = strtotime($time);

            $list = M()->table("tp_pagesou b,tp_page a")->where("a.cid = $cid and a.id = b.aid and b.mtime > $needtime and verify=1")->group("b.aid")->order("count(b.aid) desc")->limit(20)->select();

            if (empty($list)) {
                $list = M()->table("tp_page")->where("cid = $cid")->order("year desc")->limit(20)->select();
            }
            foreach ($list as $v) {
                if (strstr($v['title'], "性爱") || strstr($v['title'], "党") || strstr($v['title'], "共产")) {
                    continue;
                }
                $outlisttype[] = $v;
            }
            $i = 1;

            foreach ($outlisttype as $v) {
                if (strstr($v['title'], "性爱") || strstr($v['title'], "党") || strstr($v['title'], "共产")) {
                    continue;
                }

                $sqlc = M('pagetype')->where("aid = " . $v['id'])->order("sort asc")->select();
                $outlist[$i]['aid'] = $v['id'];
                $outlist[$i]['pic'] = $v['pic'];
                $outlist[$i]['title'] = $v['title'];
                $outlist[$i]['pingy'] = $v['pingy'];
                $outlist[$i]['act'] = $v['act'];
                $outlist[$i]['topId'] = $v['cid'];
                if ($v['year'] > 0) {
                    $outlist[$i]['year'] = $v['year'];
                } else {
                    $outlist[$i]['year'] = "未知";
                }
                $outlist[$i]['topId'] = $v['cid'];

                foreach ($sqlc as $k => $vc) {
                    if ($vc['type'] == 2) {
                        continue;
                    }
                    if ($vc['type'] == 7) {
                        $outlist[$i]['uuid7'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        $type = 2324;
                        $outlist[$i]['appid7'] = $type;
                    } elseif ($vc['type'] == 6) {
                        $outlist[$i]['uuid6'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        $type = 1420;
                        $outlist[$i]['appid6'] = $type;
                    } elseif ($vc['type'] == 5) {
                        $outlist[$i]['uuid5'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        $type = 658;
                        $outlist[$i]['appid5'] = $type;
                    } elseif ($vc['type'] == 2) {
                        $outlist[$i]['uuid2'] = "http://ott-api.fun.tv/api/v3/subject/" . $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        $type = 944;
                        $outlist[$i]['appid2'] = $type;
                    } elseif ($vc['type'] == 1) {
                        $outlist[$i]['uuid1'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        $type = 14;
                        $outlist[$i]['appid1'] = $type;
                    } elseif ($vc['type'] == 8) {
                        $outlist[$i]['uuid8'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                        $type = 1057;
                        $outlist[$i]['appid8'] = $type;
                    } elseif ($vc['type'] == 3) {
                        $outlist[$i]['uuid3'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        $type = 11;
                        $outlist[$i]['appid3'] = $type;
                    } elseif ($vc['type'] == 4) {
                        $outlist[$i]['uuid4'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        $type = 13;
                        $outlist[$i]['appid4'] = $type;
                    }elseif ($vc['type'] == 9&&$version>9) {
                        $outlist[$i]['uuid9'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                        if (empty($outlist[$i]['uuid8'])) $outlist[$i]['uuid8'] = "";
                        $type = 1720;
                        $outlist[$i]['appid9'] = $type;
                    }

                    $outlist[$i]['appid'][] = $type;
                }

                if (count($outlist[$i]['appid']) == 0) {
                    $outlist[$i] = array();
                    array_pop($outlist);
                } else {
                    $i++;
                }

            }

            $configstr = array(
                "0" => array("title" => "热门电影", "topId" => 1969),
                "1" => array("title" => "热门电视", "topId" => 1970),
                "2" => array("title" => "热门综艺", "topId" => 1971),
                "3" => array("title" => "热门动漫", "topId" => 1972)
            );

            $newJson = json_encode(
                array_merge(
                    array('hotlive' => $configstr),
                    array('hottop' => $outlist)
                )
            );
            echo $newJson;
        }

    }
    //点播字段采集
    public function soukey()
    {

        $sokey =  I('soukey');
        $topId =  I('aid');
        $mtime = time();
        if ($topId) {
            $data['aid'] = $topId;
            $data['mtime'] = $mtime;
            $data['soukey'] = $sokey;
            $dsql = M('pagesou')->add($data);
            if ($dsql) {
                $sqls = M('page')->where("id = $topId")->setInc("allnum",1);
            }
        }
    }
    //直播字段采集
    public function livekey()
    {
        $sokey =  I('livekey');
        $topId =  I('aid');
        $mtime = time();
        if ($topId) {
            $data['aid'] = $topId;
            $data['mtime'] = $mtime;
            $data['livekey'] = $sokey;
            $dsql = M('pagelive')->add($data);
            if ($dsql) {
                $sqls = M('category')->where("catid = $topId")->setInc("hits",1);
            }
        }
    }
//0全部; 1电影;2电视剧;3动漫;4综艺;5纪录片;6V视点;7体育;8少儿;120明星;121微电影;122搞笑;123其他;124公开课
    //影视快搜查询
    public function  souzm()
    {
        $sokey = addslashes(I('sokey'));
        $cId = addslashes(I('topId'));
        $page = addslashes(I('page'));    //用户查询页数
        $version = intval(I('version'));

        if ($version > 2) {
            $pagesize = 10;
        } else {
            $pagesize = 100;
        }
        if($cId==1){
            $topId = 1969;
        }elseif($cId==2){
            $topId = 1970;
        }elseif($cId==3){
            $topId = 1972;
        }elseif($cId==4){
            $topId = 1971   ;
        }elseif($cId==5){
            $topId = 1974;
        }elseif($cId==6){
            $topId = 1973;
        }

        $page = (isset($page) && $page >= 1) ? intval($page) : 1; //默认为第一页
        $offset = ($page - 1) * $pagesize;    //偏移量
        $file = "cache/soukeym9_" . $sokey . "_" . $page . "_" . $topId . "_" . $offset . "_" . $pagesize . ".txt";
        if (file_exists($file)) {
            echo file_get_contents($file);
            exit;
        } else {

            $i = 1;

            //分页开始
            if ($cId) {
                $data['cid'] = $topId;
            } else {
                $data['cid'] = array("in", "1969,1970,1971,1972,1973,1974");
            }
            $where['pingy'] = array("like", "%" . $sokey . "%");
            $where['title'] = array("like", "%" . $sokey . "%");
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $map['cid'] = $data['cid'];

            $row =  M('page')->where($map)->select();
            $total = count($row);
//            $total=$row['count'];
            $pagenum = ceil($total / $pagesize);

            if ($page > $pagenum) {
                $page = $pagenum;
            }
            $offset = ($page - 1) * $pagesize;
            //分页结束
            $list = M('page')->where($map)->order('allnum desc')->limit($offset, $pagesize)->select();

            foreach ($list as $v) {
                $outlisttype[] = $v;
            }

            foreach ($outlisttype as $v) {
                if (strstr($v['title'], "性爱") || strstr($v['title'], "党") || strstr($v['title'], "共产")) {
                    continue;
                }
                $v['title'] = str_replace("“","", $v['title']);
                $v['title'] = str_replace("”","", $v['title']);
                if (empty($v['cid']) || $v['cid'] == 0) {
                    $v['cid'] = 1969;
                }
                $sqlc = M('pagetype')->where("aid = " . $v['id'])->order("sort asc")->select();

                $outlist[$i]['aid'] = $v['id'];
                $outlist[$i]['pic'] = $v['pic'];
                $outlist[$i]['title'] = $v['title'];
                $outlist[$i]['pingy'] = $v['pingy'];
                $outlist[$i]['act'] = $v['act'];
                $outlist[$i]['topId'] = $v['cid'];
                if ($v['year'] > 0) {
                    $outlist[$i]['year'] = $v['year'];
                } else {
                    $outlist[$i]['year'] = "未知";
                }
                $outlist[$i]['topId'] = $v['cid'];
                foreach ($sqlc as $k=>$vc) {
                    if($vc['type']==2){
                      continue;   
                    }    
                    if ($vc['type'] == 7) {
                        $outlist[$i]['uuid7'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        $type = 2324;
                        $outlist[$i]['appid7'] = $type;
                    }elseif ($vc['type'] == 6) {
                        $outlist[$i]['uuid6'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        $type = 1420;
                        $outlist[$i]['appid6'] = $type;
                    }elseif ($vc['type'] == 5) {
                        $outlist[$i]['uuid5'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        $type = 658;
                        $outlist[$i]['appid5'] = $type;
                    } elseif ($vc['type'] == 2) {
                        $outlist[$i]['uuid2'] = "http://ott-api.fun.tv/api/v3/subject/" . $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        $type = 944;
                        $outlist[$i]['appid2'] = $type;
                    }
                    elseif ($vc['type'] == 1) {
                        $outlist[$i]['uuid1'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        $type = 14;
                        $outlist[$i]['appid1'] = $type;
                    } elseif ($vc['type'] == 8) {
                        $outlist[$i]['uuid8'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                        $type = 1057;
                        $outlist[$i]['appid8'] = $type;
                    } elseif ($vc['type'] == 3) {
                        $outlist[$i]['uuid3'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        $type = 11;
                        $outlist[$i]['appid3'] = $type;
                    } elseif ($vc['type'] == 4) {
                        $outlist[$i]['uuid4'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        $type = 13;
                        $outlist[$i]['appid4'] = $type;
                    }elseif ($vc['type'] == 9&&$version>9) {
                        $outlist[$i]['uuid9'] = $vc['mkey'];
                        if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                        if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                        if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                        if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                        if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                        if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                        if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                        if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                        if (empty($outlist[$i]['uuid8'])) $outlist[$i]['uuid8'] = "";
                        $type = 1720;
                        $outlist[$i]['appid9'] = $type;
                    }
                    $outlist[$i]['appid'][] =$type ;

                }
                 if( count( $outlist[$i]['appid'] ) == 0) {
                     $outlist[$i]=array();
                     array_pop($outlist);
                }else {
                    $i++;
                }
            }

            $outlist['total'] = $total;
            $str = json_encode($outlist);
            file_put_contents($file, $str);
            echo $str;

        }
    }


    public function  sou()
    {
        $sokey = addslashes(I('sokey'));
        $cId = addslashes(I('topId'));
        $page = addslashes(I('page'));    //用户查询页数
        $version = intval(I('version'));
        $chanel = addslashes(I('chanel'));
        if ($version > 2) {
            $pagesize = 10;
        } else {
            $pagesize = 100;
        }
        if($cId==1){
            $topId = 1969;
        }elseif($cId==2){
            $topId = 1970;
        }elseif($cId==3){
            $topId = 1972;
        }elseif($cId==4){
            $topId = 1971   ;
        }elseif($cId==5){
            $topId = 1974;
        }elseif($cId==8){
            $topId = 1973;
        }

//  1电影;2电视剧;3动漫 4综艺;5纪录片; 6少儿

        $page = (isset($page) && $page >= 1) ? intval($page) : 1; //默认为第一页


        $offset = ($page - 1) * $pagesize;    //偏移量
//$total = $dsql->GetTotalRow();

        $file = "cache/soukeym9_" . $sokey . "_" . $page . "_" . $topId . "_" . $offset . "_" . $pagesize . ".txt";
        if (file_exists($file)) {

            echo file_get_contents($file);
            exit;
        } else {

            $i = 1;

            //分页开始
            if ($cId) {
                $data['cid'] = $topId;
            } else {
                $data['cid'] = array("in", "1969,1970,1971,1972,1973,1974");
            }

                $where['pingy'] = array("like", "%" . $sokey . "%");
                $where['title'] = array("like", "%" . $sokey . "%");
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $map['cid'] = $data['cid'];
                $row =  M('page')->where($map)->select();

            $total = count($row);
//            $total=$row['count'];
            $pagenum = ceil($total / $pagesize);

            if ($page > $pagenum) {
                $page = $pagenum;
            }
            $offset = ($page - 1) * $pagesize;
            //分页结束

                $list = M('page')->where($map)->order('allnum desc')->limit($offset, $pagesize)->select();


            foreach ($list as $v) {
                $outlisttype[] = $v;
            }

            foreach ($outlisttype as $v) {
                if (strstr($v[title], "性爱") || strstr($v[title], "党") || strstr($v[title], "共产")) {
                    continue;
                }
                if (empty($v['cid']) || $v['cid'] == 0) {
                    $v['cid'] = 1969;
                }

                $condition['aid'] = array("eq", $v['id']);
                $sqlc = M('pagetype')->where($condition)->order("sort asc")->select();

//                $listc =  postmem( $sqlc , "arr");
                $outlist[$i]['aid'] = $v['id'];
                $outlist[$i]['pic'] = $v['pic'];
                $outlist[$i]['title'] = $v['title'];
                $outlist[$i]['pingy'] = $v['pingy'];
                $outlist[$i]['act'] = $v['act'];
                $outlist[$i]['topId'] = $v['cid'];
                if ($v['year'] > 0) {
                    $outlist[$i]['year'] = $v['year'];
                } else {
                    $outlist[$i]['year'] = "未知";
                }
                $outlist[$i]['topId'] = $v['cid'];

                foreach ($sqlc as $vc) {
                    if($chanel=='jinruixian') {
                        if ( $vc['type'] == 1  ||  $vc['type'] == 3  ||  $vc['type'] == 4 || $vc['type'] == 8  ) {
                            continue;
                        }
                    }
                    if($vc['type']==2){
                      continue;   
                    }    
                                    
                        if ($vc['type'] == 1) {
                            $outlist[$i]['uuid'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            $type = 14;
                            $outlist[$i]['appid1'] = $type;
                        } elseif ($vc['type'] == 2) {         /*  百视通*/
                            $outlist[$i]['uuid2'] = "http://ott-api.fun.tv/api/v3/subject/" . $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            $type = 944;
                            $outlist[$i]['appid2'] = $type;
                        } elseif ($vc['type'] == 3) {
                            $outlist[$i]['uuid3'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            $type = 11;
                            $outlist[$i]['appid3'] = $type;
                        } elseif ($vc['type'] == 4) {
                            $outlist[$i]['uuid4'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            $type = 13;
                            $outlist[$i]['appid4'] = $type;
                        } elseif ($vc['type'] == 5) {                                      /* 华数*/
                            $outlist[$i]['uuid5'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                            $type = 658;
                            $outlist[$i]['appid5'] = $type;
                        } elseif ($vc['type'] == 6) {      /* 优酷CIBN*/
                            $outlist[$i]['uuid6'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                            if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                            $type = 1420;
                            $outlist[$i]['appid6'] = $type;
                        } elseif ($vc['type'] == 7) {             /*  腾讯*/
                            $outlist[$i]['uuid7'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                            if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                            if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                            $type = 2324;
                            $outlist[$i]['appid7'] = $type;
                        } elseif ($vc['type'] == 8) {
                            $outlist[$i]['uuid8'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) {$outlist[$i]['uuid1'] = "";}
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                            if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                            if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                            if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                            $type = 1057;
                            $outlist[$i]['appid8'] = $type;
                        }elseif ($vc['type'] == 9&&$version>9) {
                            $outlist[$i]['uuid9'] = $vc['mkey'];
                            if (empty($outlist[$i]['uuid'])) $outlist[$i]['uuid'] = "";
                            if (empty($outlist[$i]['uuid1'])) $outlist[$i]['uuid1'] = "";
                            if (empty($outlist[$i]['uuid2'])) $outlist[$i]['uuid2'] = "";
                            if (empty($outlist[$i]['uuid3'])) $outlist[$i]['uuid3'] = "";
                            if (empty($outlist[$i]['uuid4'])) $outlist[$i]['uuid4'] = "";
                            if (empty($outlist[$i]['uuid5'])) $outlist[$i]['uuid5'] = "";
                            if (empty($outlist[$i]['uuid6'])) $outlist[$i]['uuid6'] = "";
                            if (empty($outlist[$i]['uuid7'])) $outlist[$i]['uuid7'] = "";
                            if (empty($outlist[$i]['uuid8'])) $outlist[$i]['uuid8'] = "";
                            $type = 1720;
                            $outlist[$i]['appid9'] = $type;
                        }

                    $outlist[$i]['appid'][] = $type;

                }
                if( count( $outlist[$i]['appid'] ) == 0) {
                     $outlist[$i]=array();
                     array_pop($outlist);
                }else {
                    $i++;
                }
            }
            $str = json_encode($outlist);
            file_put_contents($file, $str);
            echo $str;

        }
    }

    //点播 直播点击数统计
    public function clicknum(){

        $data = I('data');
        $where['content'] = $data;
        if($data){
            $insert = M('json')->add($where);
        }
        if($insert){
            echo json_encode(array("result"=>1));
        }else{
            echo json_encode(array("result"=>0));
        }

    }


}
