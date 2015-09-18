<?php
/**
 * Action 基类
 * @author Gavin
 * @version 1.0
 */
class BaseAction extends Action{

	

	
	public function __construct(){

        parent::__construct();

        $this->assign("css_dir", __APP__."/Public/css");
        $this->assign("js_dir",__APP__."/Public/js");
        $this->assign("images_dir",__APP__."/Public/images");

        $this->assign('tilte','Tieson iThink');


		
	}



}
