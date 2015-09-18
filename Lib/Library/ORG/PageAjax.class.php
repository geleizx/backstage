<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mackcyl
 * Date: 13-9-11
 * Time: 上午11:51
 * 针对ajax翻页请求扩展类, 继承Page类
 */
import('ORG.Util.Page'); // 导入分页类

class PageAjax extends Page{
    public $jSFunction;

    protected $config  =    array('header'=>'条记录','prev'=>'<','next'=>'>','first'=>'第一页','last'=>'最后一页','pageName'=>'页','theme'=>'    %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% ');


    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        parent::__construct($totalRows,$listRows,$parameter,$url);
    }

    public function setJsFunction($jsFunction = ""){
        $this->jSFunction = (empty($jsFunction))?"ajaxPage":$jsFunction;
    }

    public function getJsFunction(){
        if(empty($this->jSFunction)){
            $this->jSFunction = "ajaxPage";
        }
        return $this->jSFunction;
    }


     /**
     * AJAX分页显示输出
     *  public
     */
    public function ajaxShow() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $href = $this->constructAjaxPageUrl($upRow,$url);
//            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a>";
            $upPage     =   "<a href='".$href."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $href = $this->constructAjaxPageUrl($downRow,$url);
            $downPage   =   "<a href='".$href."'>".$this->config['next']."</a>";
//            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".$this->constructAjaxPageUrl($preRow,$url)."' >上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".$this->constructAjaxPageUrl(1,$url)."' >".$this->config['first']."</a>";
//            $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."' >上".$this->rollPage."页</a>";
//            $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".$this->constructAjaxPageUrl($nextRow,$url)."' >下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".$this->constructAjaxPageUrl($theEndRow,$url)."' >".$this->config['last']."</a>";
//            $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' >下".$this->rollPage."页</a>";
//            $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".$this->constructAjaxPageUrl($page,$url)."'>".$page."</a>";
//                    $linkPage .= "<a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= '<a href="javascript:void(0)" class="on">'.$page.'</a>';
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<a href='".str_replace('__PAGE__',$upRow,$url)."'>".$this->config['prev']."</a>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<a href='".str_replace('__PAGE__',$downRow,$url)."'>".$this->config['next']."</a>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            $preRow     =   $this->nowPage-$this->rollPage;
            $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."' >上".$this->rollPage."页</a>";
            $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' >下".$this->rollPage."页</a>";
            $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".str_replace('__PAGE__',$page,$url)."'>".$page."</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= '<a href="javascript:void(0)" class="on">'.$page.'</a>';

//                    $linkPage .= "<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr     =   str_replace(

            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%pageName%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$this->config['pageName']),$this->config['theme']);
        return $pageStr;
    }

    private function constructAjaxPageUrl($Row,$url){
        $hrefStr = '';
        $hrefStr .= "javascript:".$this->getJsFunction() ."(\"".str_replace('__PAGE__',$Row,$url)."\")";
        return $hrefStr ;
    }
}