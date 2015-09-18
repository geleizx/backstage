<?php
/**
 * Created by PhpStorm.
 * User: mackcyl
 * Date: 13-11-29
 * Time: 下午4:09
 *
 * rss 显示扩展
 *
 * 使用方式为 {:W('rss')}
 *
 */

class RssWidget extends Widget{

    public function render($data){
        $iThinkinfo['name'] = "iThink 专注企业站文化";
        $iThinkinfo['description'] ="您正在查看的源, 包含频繁更新的内容!订阅后,该源会添加到 常见源列表";

        $Newslist = D('News')->order('id')->limit(10)->select();//查询news表的记录

        import("@.Tool.RSSTool");//加载RSSTool.class.php类文件,我放在前台项目Lib/ORG目录中。

        $RSS = new RSSTool($iThinkinfo['name'],U('/News/'),$iThinkinfo['description'],'');//初始化类，给RSS加上标题及描述信息，具体参数看构造器__construct

        foreach($Newslist as $list){ //遍历$Bloglist
            $RSS->AddItem($list['title'],U('News/'.$list['id']),$list['newsdata']['content'],date("Y-m-d",$list['inputtime']));
        }
        $rss = $RSS->Fetch(); //输出日记列表,不需要模板。
        return $rss;
    }
}