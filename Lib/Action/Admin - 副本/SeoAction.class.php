<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-27
 * Time: 上午9:52
 * 
 */

class SeoAction extends AdminBaseAction{

    private $lang_code = array();

    public function __construct(){
        parent::__construct();

        $this->lang_code = array('zh-cn' => "简体中文",'en'=>"English");
    }



    /**
     * SEO       管理
     */
    public function index(){
//        $seo_db = M('Seo');
//        $seo = $seo_db->find();

        $langC = empty($_REQUEST['lang']) ? C('DEFAULT_LANG'):$_REQUEST['lang'];
//echo $langC;
        $seo = $this->getSeoByLang($langC);

        $this->assign('seo',$seo);
        $this->assign('lang_code',$this->lang_code);
        $this->assign('langC',$langC);
        $this->display();
    }


    private function getSeoByLang($lang='zh-en'){
        $seo = "";
        $seo_db = M('Seo');
        $where = array();
        $where['lang'] = $lang;

        $seo_db = M('Seo');

        $seo = $seo_db->where($where)->find();
//var_dump($seo_db->getLastSql());
        return $seo;
    }

    public function commit(){

// print_r($_POST);
// die("aa");
        $seo_db = M('seo');
        $id = $this->_post("id");

        $ret = null;

        if (empty ($id)) {
            $ret =  $seo_db -> add($_POST);
        } else {
            $ret = $seo_db ->where("`id` = $id") ->save($_POST);
        }

        if ( ! $ret){
            $this->error($seo_db->getDbError());
        }
        $this->success('插入成功！');

    }

    public function makeSiteMap() {
    /*
     * ICE 2013-07-12 生成网站地图
     * 读取module表 获得需要读入的模块 然后循环找出需要读取的库 连接后读取所有数据
     * 目前读取新闻 单页 产品所有的地址
     */

       $changefreq = 'daily'; //更新频率
       $priority = '1.0'; //权重

       $DB_module = M('module');
       $moduleList = $DB_module -> where("`sitemapswitch` = 1") -> select();

       foreach ($moduleList as $value) {
            //按循环连接DB
            $dbname = $value['dbtable'];
            $DB = M($dbname);
            $dataList = $DB -> select();

            $moduleName = $value['module']; //module中的module名
            //dump ($moduleName);
            //data是写入的数据 temp只是临时字符串
            foreach ($dataList as $dataArray) {
                    $tempArray['loc'] = 'http://'.$_SERVER['SERVER_NAME'].__APP__.'/'.$moduleName.'/show/id/'.$dataArray['id']; //拼网址
                    $tempArray['lastmod'] = $dataArray['create_date'];
                    $tempArray['changefreq'] = $changefreq;
                    $tempArray['priority'] = $priority;
                    $data[] = $tempArray;
            }
        }

        $res = xml_encode($data,'urlset','url','','');
        //dump ($res);

        //写入到根目录
        @$fp = fopen("sitemap.xml","w");
        if(!$fp){
            echo "文件写入失败";
            exit();
        } else {
            fwrite($fp,$res);
            fclose($fp);
            echo '<br />写入成功';
        }
    }



}