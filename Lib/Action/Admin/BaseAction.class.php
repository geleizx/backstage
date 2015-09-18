<?php
/**
 * Action 基类
 * @author Gavin
 * @version 1.0
 */
class BaseAction extends Action
{


    public function __construct()
    {

        parent::__construct();

        $this->assign("css_dir", __APP__ . "/Public/css");
        $this->assign("js_dir", __APP__ . "/Public/js");
        $this->assign("images_dir", __APP__ . "/Public/images");

    }

    //memcache缓存
    public function postmem( $sql , $type = "one" ){
        global $dsql;
        $mem = new Memcache;
        $mem->connect("127.0.0.1" , 11211); //连接Memcache服务器
        //$mem->connect("10.168.240.114" , 11111); //连接Memcache服务器
        $key = md5($sql);
        if(($k  = $mem->get($key))){
            return $k;
        } else {
            if($type=="one"){
                $rows = M()->query("$sql");
                $mem->set('key',$rows, 0, 180000);
            }else{
                $dsql->SetQuery( $sql );
                $dsql->Execute();
                while($rowa = $this->GetArray())
                {
                    $row[]=$rowa;
                }
            }
            return $rows;
        }
    }


    //清除所有memcache缓存
    public function clean(){
        $mem = new Memcache;
        $mem->connect("127.0.0.1" , 11211); //连接Memcache服务器
        $mem->flush();
        $mem->close();
    }

    //清理文件缓存
    public function del_cache() {
        header("Content-type: text/html; charset=utf-8");
        //清文件缓存
        $dirs = array('./Runtime/');
        @mkdir('Runtime',0777,true);
        //清理缓存
        foreach($dirs as $value) {
            $this->rmdirr($value);
        }
        $this->success('系统缓存清除成功！');
        //echo '<div style="color:red;">系统缓存清除成功！</div>';
    }


    public function rmdirr($dirname) {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        $dir = dir($dirname);
        if($dir){
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                //递归
                $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
            }
        }
        $dir->close();
        return rmdir($dirname);
    }
}
