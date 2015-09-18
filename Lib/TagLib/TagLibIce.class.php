<?php
    class TagLibIce extends TagLib{
    protected $tags=array(
      //文章列表
      'articlelist'=>array('attr'=>'category,rows,debug,item,titlelong,contentlong,dotswitch,timeformat,multipage,uparam,key,ishot','level'=>1), // category 分类号 rows 输出行数 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false uparam U方法的参数
      //产品列表
      'productlist'=>array('attr'=>'category,rows,debug,item,titlelong,contentlong,dotswitch,timeformat,multipage,uparam,key,ishot,offset','level'=>1), // category 分类号 rows 输出行数 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false uparam U方法的参数     //页面
      //单页列表
      'singlepagelist'=>array('attr'=>'category,rows,debug,item,titlelong,contentlong,timeformat,multipage,uparam','level'=>1), // category 分类号 rows 输出行数 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false uparam U方法的参数     //页面
      //单页
      'singlepage'=>array('attr'=>'id,debug,item,titlelong,contentlong,dotswitch,timeformat','level'=>1), // id 编号 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false 
      //文章
      'article'=>array('attr'=>'id,debug,item,titlelong,contentlong,dotswitch,timeformat','level'=>1), // id 编号 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false
      //产品
      'product'=>array('attr'=>'category,rows,debug,item,titlelong,timeformat,multipage','level'=>1), // id 编号 debug 调试开关 item 循环变量名 默认为 vo titlelong 标题长度 timeformat 时间格式 默认为'Y-m-d' multipage 分页开关 默认为false
      //留言列表标签  
      'feedbacklist'=>array('attr'=>'baseid,verify,commit,debug','level'=>1), //baseid 留言分类 verify 是否以verify作为筛选条件 commit 是否以回复作为筛选条件 debug 调试开关 
      //读取分类树
      'categorylist'=>array('attr'=>'baseid,debug,uparam,currentid,activeclass','close'=>0), //闭合标签
      //智能地图标签
      'baidumap'=>array('attr'=>'id,width,height,css,mark,debug','close'=>0), //闭合标签 id 地图id  width 宽px height 高px css添加css样式 mark 是否显示信息 debug 调试开关 
      //顶部导航调用标签
      'headernav'=>array('attr'=>'deep,activeclass,debug','close'=>0), // deep 0 所有 1顶级 2 二级 ... (默认显示顶级分类 deep = 1)  activeclass 当前活动 debug 调试开关 
      //万能标签
      'ithink'   =>  array('attr'=>'model,where,order,num,id,page,pagesize,query,flag,field,cache,relation','level'=>3),  //万能的输出标签

    );
    
    //////////////////////////////////////////////////////////////////////

    public function _articlelist($attr,$content){
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'articlelist');
      //重置所有属性
      $topcategoryid = is_numeric($tag['topcategoryid'])?$tag['topcategoryid']:$this->autoBuildVar($tag['topcategoryid']);
      $rows =!empty($tag['rows'])?$tag['rows']:'';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';
      $multipage =!empty($tag['multipage'])?$tag['multipage']:'false';
      $uparam =!empty($tag['uparam'])?$tag['uparam']:'News/show?lang='.getCurrentLanguage().'&id=';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:'false';
      $ishot =!empty($tag['ishot'])?$tag['ishot']:'false';
      $key =!empty($tag['key'])?$tag['key']:'i';
      //$topcategoryid = $this->autoBuildVar($topcategoryid);
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$RDB_news = D("News");';

      //查询所有子分类号
      $innerHtml .= '$DB_category = M(\'category\');';
      $innerHtml .= '$categoryData = $DB_category -> select();';
      $innerHtml .= '$allIds = getAllChild($categoryData,'.$topcategoryid.',\'catid\',\'parentid\');';
      $innerHtml .= '$allIds .= '.$topcategoryid.';'; 

      //是否只显示推荐的文章
      if ($ishot == 'true') {
        $innerHtml .= '$hotArtlice = \' AND ishot = 1\';';
      }

      if ($multipage == 'true') {
        //分页的查询

        $innerHtml .= 'import(\'ORG.Util.Page\'); '; //加载分页类
        $innerHtml .= '$count = $RDB_news -> where("`catid` IN ($allIds) AND `status` = 1 $hotArtlice") -> count();';// 查询满足要求的总记录数
        $innerHtml .= '$Page = new Page($count,'.$rows.');';// 实例化分页类 传入总记录数和每页显示的记录数
        $innerHtml .= '$articleList = $RDB_news -> relation(true) -> where("`catid` IN ($allIds) AND `status` = 1 $hotArtlice") -> order(\'id desc\') -> limit($Page->firstRow.",".$Page->listRows) -> select();';
        
        //识别当前语言
        $language = getCurrentLanguage();
        //按语言设置分页模板
        switch ($language) {
          case 'zh-cn':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'条资讯\');
              $Page->setConfig(\'prev\',\'上一条\');
              $Page->setConfig(\'next\',\'下一条\');
              $Page->setConfig(\'first\',\'首页\');
              $Page->setConfig(\'last\',\'尾页\');
              $Page->setConfig(\'pageName\',\'页\');
            ';
            break;
          case 'en':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'rows\');
              $Page->setConfig(\'prev\',\'Prev\');
              $Page->setConfig(\'next\',\'Next\');
              $Page->setConfig(\'first\',\'FirstPage\');
              $Page->setConfig(\'last\',\'LastPage\');
              $Page->setConfig(\'pageName\',\'Pages\');
            ';
            break;

          case 'ja':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'行ニュース\');
              $Page->setConfig(\'prev\',\'前のニュース\');
              $Page->setConfig(\'next\',\'次のニュース\');
              $Page->setConfig(\'first\',\'先頭ページ\');
              $Page->setConfig(\'last\',\'最終ページ\');
              $Page->setConfig(\'pageName\',\'行\');
            ';
            break;

          case 'ko':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'행 소식\');
              $Page->setConfig(\'prev\',\'이전 뉴스\');
              $Page->setConfig(\'next\',\'다음 뉴스\');
              $Page->setConfig(\'first\',\'첫 페이지\');
              $Page->setConfig(\'last\',\'마지막 페이지\');
              $Page->setConfig(\'pageName\',\'페이지\');
            ';
            break;

          case 'ru':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'строках новостей\');
              $Page->setConfig(\'prev\',\'Предыдущая новость\');
              $Page->setConfig(\'next\',\'следующая новость\');
              $Page->setConfig(\'first\',\'Первая страница\');
              $Page->setConfig(\'last\',\'последняя страница\');
              $Page->setConfig(\'pageName\',\'Страницы\');
            ';
            break;
          
          default:
            break;
        }

        $innerHtml .= '$multipageStr ="<div class=\"multipage\" >".$Page->show()."</div>";'; 

      }
      else {
        //不分页的查询
        $innerHtml .= '$articleList = $RDB_news -> relation(true) -> order(\'id desc\') -> where("`catid` IN ($allIds) AND `status` = 1 $hotArtlice")';
          
          if($rows){
            $innerHtml .= '->limit(\'0,'.$rows.'\')';
          }

        $innerHtml .= '-> select();';      
      }


      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($articleList as $value) {
          $value[\'inputtime\'] = date(\''.$timeformat.'\',$value[\'inputtime\']);';
          if ($titlelong) {
            $innerHtml .= '$value[\'title\'] = msubstr($value[\'title\'],0,'.$titlelong.',\'utf-8\','.$dotswitch.');';
          }
          if ($contentlong) {
             $innerHtml .= '$value[\'newsdata\'][\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'newsdata\'][\'content\'])),0,'.$contentlong.',\'utf-8\','.$dotswitch.');';
          }
          else {
            $innerHtml .= '$value[\'newsdata\'][\'content\'] = htmlspecialchars_decode($value[\'newsdata\'][\'content\']);';         
          }  
          $innerHtml .= '$value[\'url\'] = U(\''.$uparam.'\'.$value[\'id\'].\'&category=\'.$value[\'catid\']);'; 
          $innerHtml .= '$tempList[] = $value;
        }
        $articleList = $tempList;
        ';
      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump($articleList);dump($RDB_news ->getLastSql());';
      }


      //开始组装输出字符串
      $innerHtml.= 'echo \'<ul>\';';
      $innerHtml.='foreach($articleList as $'.$key.'=>$'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      $innerHtml.= '<?php echo \'</ul>\'; ?>';
      

      
      //如果分页 输出分页部分代码
      if ($multipage == 'true') {
        $innerHtml .= '<?php echo $multipageStr; ?>';
      }

      return $innerHtml;
    }

    //////////////////////////////////////////////////////////////////////

    public function _productlist($attr,$content){
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'productlist');
      //重置所有属性
      $topcategoryid = is_numeric($tag['topcategoryid'])?$tag['topcategoryid']:$this->autoBuildVar($tag['topcategoryid']);
      $rows =!empty($tag['rows'])?$tag['rows']:'';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';
      $multipage =!empty($tag['multipage'])?$tag['multipage']:'false';
      $uparam =!empty($tag['uparam'])?$tag['uparam']:'Product/show?lang='.getCurrentLanguage().'&id=';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:'false';
      $ishot =!empty($tag['ishot'])?$tag['ishot']:'false';
      $offset =!empty($tag['offset'])?$tag['offset']-1:0; //数据库查询偏移量 从1开始
      $mod =!empty($tag['mod'])?$tag['mod']:2;
      $key =!empty($tag['key'])?$tag['key']:'i';
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_product = M("Product");';

      //查询所有子分类号
      $innerHtml .= '$DB_category = M(\'category\');';
      $innerHtml .= '$categoryData = $DB_category -> select();';
      $innerHtml .= '$allIds = getAllChild($categoryData,'.$topcategoryid.',\'catid\',\'parentid\');';
      $innerHtml .= '$allIds .= '.$topcategoryid.';';
      
      //是否只显示推荐的产品
      if ($ishot == 'true') {
        $innerHtml .= '$hotProducts = \' AND ishot = 1\';';
      }      

      if ($multipage == 'true') {
        //分页的查询
        $innerHtml .= 'import(\'ORG.Util.Page\'); '; //加载分页类
        $innerHtml .= '$count = $DB_product -> where("`category_id` IN ($allIds) AND `status` = 1 $hotProducts") -> count();';// 查询满足要求的总记录数
        $innerHtml .= '$Page = new Page($count,'.$rows.');';// 实例化分页类 传入总记录数和每页显示的记录数
        $innerHtml .= '$productList = $DB_product -> where("`category_id` IN ($allIds) AND `status` = 1 $hotProducts") -> limit($Page->firstRow.",".$Page->listRows) -> select();';

        //识别当前语言
        $language = getCurrentLanguage();
        //按语言设置分页模板
        switch ($language) {
          case 'zh-cn':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'条资讯\');
              $Page->setConfig(\'prev\',\'上一条\');
              $Page->setConfig(\'next\',\'下一条\');
              $Page->setConfig(\'first\',\'首页\');
              $Page->setConfig(\'last\',\'尾页\');
              $Page->setConfig(\'pageName\',\'页\');
            ';
            break;
          case 'en':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'rows\');
              $Page->setConfig(\'prev\',\'Prev\');
              $Page->setConfig(\'next\',\'Next\');
              $Page->setConfig(\'first\',\'FirstPage\');
              $Page->setConfig(\'last\',\'LastPage\');
              $Page->setConfig(\'pageName\',\'Pages\');
            ';
            break;

          case 'ja':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'行製品\');
              $Page->setConfig(\'prev\',\'前の製品\');
              $Page->setConfig(\'next\',\'次の製品\');
              $Page->setConfig(\'first\',\'先頭ページ\');
              $Page->setConfig(\'last\',\'最終ページ\');
              $Page->setConfig(\'pageName\',\'行\');
            ';
            break;

          case 'ko':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'행 제품\');
              $Page->setConfig(\'prev\',\'이전 제품\');
              $Page->setConfig(\'next\',\'다음 제품\');
              $Page->setConfig(\'first\',\'첫 페이지\');
              $Page->setConfig(\'last\',\'마지막 페이지\');
              $Page->setConfig(\'pageName\',\'페이지\');
            ';
            break;

          case 'ru':
            $innerHtml .= '
              $Page->setConfig(\'header\',\'строках продукт\');
              $Page->setConfig(\'prev\',\'Предыдущая продукт\');
              $Page->setConfig(\'next\',\'следующая продукт\');
              $Page->setConfig(\'first\',\'Первая страница\');
              $Page->setConfig(\'last\',\'последняя страница\');
              $Page->setConfig(\'pageName\',\'Страницы\');
            ';
            break;
          
          default:
            break;
        }

        $innerHtml .= '$multipageStr ="<div class=\"multipage\" >".$Page->show()."</div>";'; 

      }
      else {
        //不分页的查询
        $innerHtml .= '$productList = $DB_product -> where("`category_id` IN ($allIds) AND `status` = 1 $hotProducts")';
          
          if($rows){
            $innerHtml .= '->limit(\''.$offset.','.$rows.'\')';
          }

        $innerHtml .= '-> select();';      
      }


      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($productList as $value) {
          $value[\'create_date\'] = date(\''.$timeformat.'\',strtotime($value[\'create_date\']));';
        if ($titlelong) {
          $innerHtml .= '$value[\'product_name\'] = msubstr($value[\'product_name\'],0,'.$titlelong.',\'utf-8\','.$dotswitch.');';
        }
        if ($contentlong) {
          $innerHtml .= '$value[\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'content\'])),0,'.$contentlong.',\'utf-8\','.$dotswitch.');';
        }
        else {
          $innerHtml .= '$value[\'content\'] = htmlspecialchars_decode($value[\'content\']);';
        }  
        $innerHtml .= '$value[\'url\'] = U(\''.$uparam.'\'.$value[\'id\'].\'&category=\'.$value[\'category_id\']);'; 
        $innerHtml .= '$tempList[] = $value;
        }
        $productList = $tempList;
        ';
      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump('.$allIds.');dump($DB_product ->getLastSql());';
      }


      //开始组装输出字符串
      $innerHtml.= 'echo \'<ul>\';';
      $innerHtml.= 'foreach($productList as $'.$key.'=>$'.$item.'):';
      $innerHtml.= '$mod = ($'.$key.' % '.$mod.' );?>';
      $innerHtml.= $this->tpl->parse($content);            
      $innerHtml.= '<?php endforeach; ?>';

      //如果分页 输出分页部分代码
      if ($multipage == 'true') {
        $innerHtml .= '<?php echo $multipageStr;?>';
      }

      $innerHtml.= '<?php echo \'</ul>\'; ?>';
      
      return $innerHtml;
    }

    //////////////////////////////////////////////////////////////////////

    public function _singlepagelist($attr,$content){
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'singlepagelist');
      //重置所有属性
      $category =!empty($tag['category'])?$tag['category']:'0';
      $rows =!empty($tag['rows'])?$tag['rows']:'';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';
      $multipage =!empty($tag['multipage'])?$tag['multipage']:'false';
      $uparam =!empty($tag['uparam'])?$tag['uparam']:'Page/show?id=';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:'false';      
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_page = M("Page");';

      //查询所有子分类号
      $DB_category = M('category');
      $categoryData = $DB_category -> select();
      $allIds = getAllChild($categoryData,$category,'catid','parentid');
      $allIds .= $category; 


      if ($multipage == 'true') {
        //分页的查询

        $innerHtml .= 'import(\'ORG.Util.Page\'); '; //加载分页类
        $innerHtml .= '$count = $DB_page -> where(\'`cat_id` IN ('.$allIds.')\') -> count();';// 查询满足要求的总记录数
        $innerHtml .= '$Page = new Page($count,'.$rows.');';// 实例化分页类 传入总记录数和每页显示的记录数
        $innerHtml .= '$singlepagelist = $DB_page -> where(\'`cat_id` IN ('.$allIds.')\') -> limit($Page->firstRow.",".$Page->listRows) -> select();';

        $innerHtml .= '$multipageStr ="<div class=\"multipage\" >".$Page->show()."</div>";'; 

      }
      else {
        //不分页的查询
        $innerHtml .= '$singlepagelist = $DB_page -> where(\'`cat_id` IN ('.$allIds.')\')';
          
          if($rows){
            $innerHtml .= '->limit(\'0,'.$rows.'\')';
          }

        $innerHtml .= '-> select();';      
      }


      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($singlepagelist as $value) {
          $value[\'create_date\'] = date(\''.$timeformat.'\',strtotime($value[\'create_date\']));';
        if ($titlelong) {
          $innerHtml .= '$value[\'title\'] = msubstr($value[\'title\'],0,'.$titlelong.',\'utf-8\',\''.$dotswitch.'\');';
        }
        if ($contentlong) {
          $innerHtml .= '$value[\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'content\'])),0,'.$contentlong.',\'utf-8\',\''.$dotswitch.'\');';
        }
        else {
          $innerHtml .= '$value[\'content\'] = htmlspecialchars_decode($value[\'content\']);';
        }  
        $innerHtml .= '$value[\'url\'] = U(\''.$uparam.'\'.$value[\'id\']);'; 
        $innerHtml .= '$tempList[] = $value;
        }
        $singlepagelist = $tempList;
        ';
      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump($singlepagelist);dump($DB_page ->getLastSql());';
      }


      //开始组装输出字符串
      $innerHtml.='foreach($singlepagelist as $key=>$'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      

      
      //如果分页 输出分页部分代码
      if ($multipage == 'true') {
        $innerHtml .= '<?php echo $multipageStr ?>';
      }
      
      return $innerHtml;
    }

    //////////////////////////////////////////////////////////////////////

    public function _singlepage($attr,$content){
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'singlepage');
      //重置所有属性
      $id =!empty($tag['id'])?$tag['id']:'0';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:'false';
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';
      
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_page = M("page");';

        
      $innerHtml .= '$singlepage[] = $DB_page -> where(\'`id` = '.$id.'\') -> find();';
               
      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($singlepage as $value) { ';
          if ($titlelong) {
            $innerHtml .= '$value[\'title\'] = msubstr($value[\'title\'],0,'.$titlelong.',\'utf-8\',\''.$dotswitch.'\');';
          }
          if ($contentlong) {
            $innerHtml .= '$value[\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'content\'])),0,'.$contentlong.',\'utf-8\',\''.$dotswitch.'\');';
          } 
          else {
            $innerHtml .= '$value[\'content\'] = htmlspecialchars_decode($value[\'content\']);';
          }        
          $innerHtml .= '$tempList[] = $value;
        }
        $singlepage = $tempList;
        ';
      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump($singlepage);dump($DB_page ->getLastSql());';
      }


      //开始组装输出字符串

      $innerHtml.='foreach($singlepage as $key=>$'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      
      
      return $innerHtml;
    }

    //////////////////////////////////////////////////////////////////////

    public function _article($attr,$content){

      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'article');
      //重置所有属性
      $id =!empty($tag['id'])?$tag['id']:'0';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:'false';
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';

      $id = $this->autoBuildVar($id);
      
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$RDB_news = D("News");';

        
      $innerHtml .= '$article[] = $RDB_news -> relation(true) -> where("`id` = '.$id.'") -> find();';
               
      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($article as $value) { ';
          if ($titlelong) {
            $innerHtml .= '$value[\'title\'] = msubstr($value[\'title\'],0,'.$titlelong.',\'utf-8\',\''.$dotswitch.'\');';
          }
          if ($contentlong) {
            $innerHtml .= '$value[\'newsdata\'][\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'newsdata\'][\'content\'])),0,'.$contentlong.',\'utf-8\',\''.$dotswitch.'\');';
          }
          else {
            $innerHtml .= '$value[\'newsdata\'][\'content\'] = htmlspecialchars_decode($value[\'newsdata\'][\'content\']);';         
          }         
          $innerHtml .= '$tempList[] = $value;
        }
        $article = $tempList;
        ';
      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump($article);dump($RDB_news ->getLastSql());';
      }


      //开始组装输出字符串

      $innerHtml.='foreach($article as $key=>$'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      
      
      //$str = var_dump($tag);
      return $innerHtml;
    }


    //////////////////////////////////////////////////////////////////////

    public function _product($attr,$content){

    //'attr'=>'id,debug,item,titlelong,contentlong,timeformat'
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'product');
      //重置所有属性
      $productid =!empty($tag['productid'])?$tag['productid']:'0';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $titlelong =!empty($tag['titlelong'])?$tag['titlelong']:'';
      $contentlong =!empty($tag['contentlong'])?$tag['contentlong']:'';
      $dotswitch =!empty($tag['dotswitch'])?$tag['dotswitch']:false;
      $timeformat =!empty($tag['timeformat'])?$tag['timeformat']:'Y-m-d';

      //自动构建变量
      $productid = $this->autoBuildVar($productid);
      //开始查询数据库
      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_product = M("product");
                     $DB_category = M("category");
      ';

        
      $innerHtml .= '$product[] = $DB_product -> where("`id` = '.$productid.'") -> find();';
               
      //结果数组重新处理时间格式与标题长度
      $innerHtml .= '
        $tempList = array();
        foreach ($product as $value) { 
          $catid = $value[\'category_id\'];
          $category = $DB_category -> where("`catid` = $catid") -> field(\'catname\') -> find();
          $value[\'catname\'] = $category[\'catname\'];
          ';
          if ($titlelong) {
            $innerHtml .= '$value[\'product_name\'] = msubstr($value[\'product_name\'],0,'.$titlelong.',\'utf-8\','.$dotswitch.');';
          }
          if ($contentlong) {
            $innerHtml .= '$value[\'content\'] = msubstr(strip_tags(htmlspecialchars_decode($value[\'content\'])),0,'.$contentlong.',\'utf-8\','.$dotswitch.');';
          }
          else {
            $innerHtml .= '$value[\'content\'] = htmlspecialchars_decode($value[\'content\']);';
          }         
          $innerHtml .= '$tempList[] = $value;
        }
        $product = $tempList;
        ';

      
      //调试开关
      if($debug == 'true') {
        $innerHtml.='dump($productid); dump($DB_product ->getLastSql());';
      }


      //开始组装输出字符串

      $innerHtml.='foreach($product as $key=>$'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      
      
      return $innerHtml;
    }


    //////////////////////////////////////////////////////////////////////

    public function _categorylist($attr,$content){

      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'categorylist');

      //重置所有属性
      $baseid =!empty($tag['baseid'])?$tag['baseid']:'0';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      $uparam =!empty($tag['uparam'])?$tag['uparam']:'';
      $activeclass =!empty($tag['activeclass'])?$tag['activeclass']:'active';
      $currentid =!empty($tag['currentid'])?$tag['currentid']:'';

      $baseid     = $this->autoBuildVar($baseid);
      $currentid  = $this->autoBuildVar($currentid);
      //开始查询数据库
    
      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_category = M(\'category\');';
      $innerHtml .= '$categoryArray = $DB_category -> order("catid asc") ->select();';
      $innerHtml .= '$DB_module = M(\'module\');';
      $innerHtml .= '$moduleArray = $DB_module->select();';
      $innerHtml .= '$language = getCurrentLanguage();';

      //调入一个标签指定的函数并执行  
      $innerHtml .= '$result =  buildCategoryTreeByUl($categoryArray,'.$baseid.',$moduleArray,$language);';

      //调试开关
      if($debug == 'true') {
        $innerHtml.= 'dump($attr);dump($DB_category ->getLastSql());';
      }
      
      $innerHtml .= 'echo $result ?>';
      return $innerHtml;
    }


    //////////////////////////////////////////////////////////////////////

    public function _headernav($attr,$content){

      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'headernav');

      //重置所有属性
      $deep =!empty($tag['deep'])?$tag['deep']:'1';
      $activeclass =!empty($tag['activeclass'])?$tag['activeclass']:'active';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';
      


      // 获得当前语言
      $language = I('lang');
      if (empty($language)) {
        $DB_adminPanel = M('admin_panel');
        $res = $DB_adminPanel -> field('def_lang') -> find();
        $language = $res['def_lang'];
      }
      // 如果标签中传入language名 则重置lang为传入language
      $language =!empty($tag['language'])?$tag['language']:$language;

      //开始查询数据库
    
      $innerHtml .= '<?php

      $DB_category =  M(\'category\');
      $DB_module = M(\'module\');
      $moduleArray = $DB_module->select();
      $categoryArray = $DB_category -> select();
      $navCategoryList = $DB_category -> where("`status` = 2") ->order(\'listorder ASC\') -> select();

      echo \'<ul>\';
      echo \'<li><a href="\'.U(\'Index/index?lang='.$language.'\').\'">\'.L("index_name").\'</a></li>\';
      foreach($navCategoryList as $value) {
        if(getLanguage($categoryArray,$value[\'catid\']) == \''.$language.'\') {
          //取出模块名
          foreach ($moduleArray as $module) {
            if ($module[\'id\'] == $value[\'module\']) {
              $value[\'moduleName\'] = $module[\'module\'];
            }
          }
          $uparam = $value[\'moduleName\'].\'/\'.$value[\'alias\'].\'?lang='.$language.'\';
          echo \'<li><a href="\'.U($uparam).\'" >\'.$value[\'catname\'].\'</a></li>\';

      ';
      //显示所有级别分类
      $innerHtml .= '$language = getCurrentLanguage();';
      if($deep == 'all') {
        $innerHtml .=  '
        echo buildCategoryTreeByUl($categoryArray,$value[\'catid\'],$moduleArray,$language);
        ';
      }
      //按输入等级显示分类
      elseif ($deep > 1) {
        $innerHtml .=  '
        $deep = '.$deep.';
        echo buildCategoryTreeByUl($categoryArray,$value[\'catid\'],$moduleArray,$language,1,\'active\',$deep);
        ';
      }

      $innerHtml .= '

        } 
      }
      echo \'</ul>\';




      ?>';
      

      //调试开关
      if($debug == 'true') {
        $innerHtml.= '<?php dump($attr);dump($DB_category ->getLastSql()); ?>';
      }
      return $innerHtml;
    }


    //////////////////////////////////////////////////////////////////////

    public function _feedbacklist($attr,$content){

    //'attr'=>'id,debug,item,titlelong,contentlong,timeformat'
      
      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'feedbacklist');

      //重置所有属性
      //判断输入baseid类型
      $baseid = is_numeric($tag['baseid'])?$tag['baseid']:$this->autoBuildVar($tag['baseid']);
      $verify =!empty($tag['verify'])?$tag['verify']:'true';
      $commit =!empty($tag['commit'])?$tag['commit']:'false';
      $rows =!empty($tag['rows'])?$tag['rows']:NULL;
      $item =!empty($tag['item'])?$tag['item']:'vo';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';


// $innerHtml .= 'import(\'ORG.Util.Page\'); '; //加载分页类
//         $innerHtml .= '$count = $DB_product -> where("`category_id` IN ($allIds) AND `status` = 1") -> count();';// 查询满足要求的总记录数
//         $innerHtml .= '$Page = new Page($count,'.$rows.');';// 实例化分页类 传入总记录数和每页显示的记录数
//         $innerHtml .= '$productList = $DB_product -> where("`category_id` IN ($allIds) AND `status` = 1") -> limit($Page->firstRow.",".$Page->listRows) -> select();';

//               
      
      //开始查询数据库
      if ($verify == 'true') {
        $verifySql = ' AND `verify` = 1';
      }
      if($rows) {
        $rowsCode = '
          import(\'ORG.Util.Page\');
          $count = $RDB_message -> relation(true) -> where("1=1 '.$verifySql.'") -> count();
          $Page = new Page($count,'.$rows.');

        ';
        $rowsSql = '-> limit($Page->firstRow.",".$Page->listRows) ';
        $rowsPages .= '$multipageStr ="<div class=\"multipage\" >".$Page->show()."</div>";'; 
      }

      $innerHtml .= '<?php
      //逻辑部分
      $RDB_message = D(\'Messageboard\');
      
      ';

      
        $innerHtml .= $rowsCode;        
        $innerHtml .= '$feedbackList = $RDB_message -> relation(true) -> where("1=1 '.$verifySql.'") '.$rowsSql.' -> select();';
        $innerHtml .= $rowsPages;
        if ($commit == 'true') {
          $innerHtml .= '
            foreach($feedbackList as $value) {
              //如果value 不为空 写入新数组
              if (isset($value[\'respond\'])) {
                $tempList[] = $value;
              }
            }
            $feedbackList = $tempList;
          ';
        }

      $innerHtml .= '?>';

      //调试开关
      if($debug == 'true') {
        $innerHtml.='<?php dump($feedbackList); dump($RDB_message ->getLastSql()); ?>';
      }


      //开始组装输出字符串

      $innerHtml.='<?php foreach($feedbackList as $'.$item.'): ?>';
      $innerHtml.=$this->tpl->parse($content);            
      $innerHtml.='<?php endforeach; ?>';
      $innerHtml.='<?php echo $multipageStr ?>';
      
      
      return $innerHtml;
    }


    //////////////////////////////////////////////////////////////////////

    public function _baidumap($attr,$content){

      $innerHtml = '';
      $tag = $this->parseXmlAttr($attr,'categorylist');

      // 重置所有属性
      
      // 如果没有指定id 则查询数据库中
      if ($tag['id']) {
        $id = $tag['id'];
      } 
      else {
        $DB_adminMap = M('admin_map');
        $id = $DB_adminMap -> field('id') ->order('id desc') -> find();
        $id = $id['id'];
      }
      $width =!empty($tag['width'])?$tag['width']:'500px';
      $height =!empty($tag['height'])?$tag['width']:'500px';
      $css =!empty($tag['css'])?$tag['css']:'';
      $mark =!empty($tag['mark'])?$tag['mark']:'true';
      $debug =!empty($tag['debug'])?$tag['debug']:'false';

      $baidumapkey = C('BAIDUMAP_KEY');

      $innerHtml .= '<?php ';
      $innerHtml .= '$DB_adminMap = M(\'admin_map\');';
      $innerHtml .= '$mapInfo = $DB_adminMap -> where("`id` = '.$id.'") -> find(); ?>';
      $innerHtml .= '
        <!--load css-->
        <link rel="stylesheet" href="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.css" />
        <!-- map container -->
        <div id="allmap" style="width:'.$width.';height:'.$height.';zoom:1;position:relative;overflow: hidden;'.$css.'"> 
            <div id="tieson_ai_map" style="height:100%;-webkit-transition: all 0.5s ease-in-out;transition: all 0.5s ease-in-out;"></div>
        </div>
        <!-- script -->
        <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.5&ak=80b4a463a00116f2c045ea97a618a956"></script>
        <script type="text/javascript" src="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.js"></script>
        <script type="text/javascript">
            var map = new BMap.Map(\'tieson_ai_map\');
            var poi = new BMap.Point(<?php echo $mapInfo[\'radius\'] ?>);
            map.centerAndZoom(poi, 16);
            map.enableScrollWheelZoom();

            var content = \'<div style="margin:0;line-height:20px;padding:2px;">\' +
                            \'<!-- <img src="../img/baidu.jpg" alt="" style="float:right;zoom:1;overflow:hidden;width:100px;height:100px;margin-left:3px;"/> -->\' +
                            \'地址：<?php echo $mapInfo[\'address\'] ?><br/>电话：<?php echo $mapInfo[\'telephone\'] ?><br/>简介：<?php echo $mapInfo[\'about\'] ?>\' +
                          \'</div>\';

            //创建检索信息窗口对象
            var searchInfoWindow = null;
          searchInfoWindow = new BMapLib.SearchInfoWindow(map, content, {
              title  : "<?php echo $mapInfo[\'name\'] ?>",      //标题
              width  : 290,             //宽度
              height : 105,              //高度
              panel  : "panel",         //检索结果面板
              enableAutoPan : true,     //自动平移
              searchTypes   :[
                BMAPLIB_TAB_SEARCH,   //周边检索
                BMAPLIB_TAB_TO_HERE,  //到这里去
                BMAPLIB_TAB_FROM_HERE //从这里出发
              ]
            });
            var marker = new BMap.Marker(poi); //创建marker对象
            marker.enableDragging(); //marker可拖拽
            marker.addEventListener("click", function(e){
              searchInfoWindow.open(marker);
            })
            map.addOverlay(marker); //在地图中添加marker';
        if ($mark == 'true') {
            $innerHtml .= 'searchInfoWindow.open(marker); //在marker上打开检索信息串口';
        }

        
        $innerHtml .= '
        </script>
      ';
      //调试开关
      if($debug == 'true') {
        $innerHtml.= '<?php dump($mapInfo);dump($DB_category ->getLastSql()); ?>';
      }
      return $innerHtml;
    }

 
    public function _ithink($attr,$content)
    {
         $html='';         
         $tag       = $this->parseXmlAttr($attr,'ithink'); //将参数解析到字符串         
         $model     =!empty($tag['model'])?$tag['model']:'';     
         $order     =!empty($tag['order'])?$tag['order']:'';
         $num       =!empty($tag['num'])?$tag['num']:'';
         $id        =!empty($tag['id'])?$tag['id']:'vo';
         $where     =!empty($tag['where'])?$tag['where']:''; //使where支持 条件判断,添加不等于的判断
         $relation  =!empty($tag['relation'])?$tag['relation']:'';         
         
         $this->comparison['noteq']= '<>'; //替换SQL字符
         $this->comparison['sqleq']= '=';

         $where     =$this->parseCondition($where); //解析查询语句

         $page=false;

         if(!empty($tag['page'])) {$page=$tag['page'];}

         $relation == 'true'?$relationStr = '-> relation(true)':$relationStr='';

         $pagesize  =!empty($tag['pagesize'])?$tag['pagesize']:'10';
         //是否用缓存,默认是false
         $cache     =!empty($tag['cache'])?$tag['cache']:false;
         $query     =!empty($tag['query'])?$tag['query']:'';
         $field     =!empty($tag['field'])?$tag['field']:'';
         $debug     =!empty($tag['debug'])?$tag['debug']:false;
         //使query 支持条件判断
         $query     =$this->parseCondition($query); 
         //if($where!='')  $where.=' and '.$flag; 
         if($where!='')  $where.= $flag; 
         
         //开始查询数据库
         $html.='<?php $m=D("'.$model.'");';
         //如果使用了query,将忽略使用where,num,order,page,field,cache 等,使用query无法实现分页
         if($query){  
             if($cache!=false){
                $html.='$cache_key="key_".md5("'.$query.'");';
                $html.='if(!$ret=S($cache_key)){ $ret=$m'.$relationStr.' ->query("'.$query.'");S($cache_key,$ret);}';
             }else{
                $html.='$ret=$m '.$relationStr.' ->query("'.$query.'");';
             }                         
         }

         //如果使用了分页,缓存也不生效
         if($page && !$query){
               $html.='import(\'ORG.Util.Page\'); ';    //这里改成你的Page类           
               $html.='$count=$m '.$relationStr.' ->where("'.$where.'")->count();';
               $html.='$p = new Page ( $count, '.$pagesize.' );';
               //如果使用了分页，num将不起作用
               $html.='$ret=$m '.$relationStr.' ->field("'.$field.'")->where("'.$where.'")->limit($p->firstRow.",".$p->listRows)->order("'.$order.'")->select();';
               $html.='$cutInfo ="<div class=cutinfo >". $p->show ()."</div>";'; 
         }
         //如果没有使用分页，并且没有 query
         if(!$page && !$query){    
              //有缓存
              if($cache!=false){
                  //包含缓存判断
                  $html.='$cache_key="key_".md5($m '.$relationStr.' ->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$num.'")->select(false));';
                  $html.='if(!$ret=S($cache_key)){ $ret=$m'.$relationStr.' ->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$num.'")->select(); S($cache_key,$ret,'.$cache.'); }';
              }else{
                  //没有缓存
                  $html.='$ret=$m '.$relationStr.' ->field("'.$field.'")->where("'.$where.'")->order("'.$order.'")->limit("'.$num.'")->select();';
              }
              
         }        
         if($debug!=false){
                 $html.='dump($ret);dump($m'.$relationStr.' ->getLastSql());';
         }

         //查询数据库后 开始
         $html.='foreach($ret as $key=>$'.$id.'): ?>';
         $html.=$this->tpl->parse($content);            
         $html.='<?php endforeach; ?>';        
         if($page)    $html.='<?php echo $cutInfo;?>'; 
         return $html;
         
    }

  }




?>