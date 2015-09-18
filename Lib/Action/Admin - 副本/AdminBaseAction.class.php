<?php
/**
 * Action 基类
 * @author Gavin
 * @version 1.0
 */
class AdminBaseAction extends Action {

	
	private $pageNum = 1;
	
	public function __construct(){
        parent::__construct();

        
        Load('extend');//调取扩展函数库


        $this->assign("css_dir", __APP__."/Public/css");
        $this->assign("js_dir",__APP__."/Public/js");
        $this->assign("images_dir",__APP__."/Public/images");

        $config = D('Config')->find();
        $this->assign('tilte',$config['title']);
        $this->assign('skin', $config['skin']);

        /*ICE  2013-07-06
         *获得面包屑导航所需值
         */
        
        $moduleName = MODULE_NAME;
        $actionName =  ACTION_NAME;
        $DB_menu = M('menu');
        $resModuleName = $DB_menu -> where("`module` = '$moduleName'") -> field('name') -> find();
        $crumbNav['module'] = $resModuleName['name'];  // 模块名

        $resActionName = $DB_menu -> where("`module` = '$moduleName' AND `action` = '$actionName'") -> field('actionName,name') -> find();
        $crumbNav['action'] = $resActionName['actionName']; //操作名
        $crumbNav['module'] = $resActionName['name']; //操作名

        $this-> assign('crumbNav',$crumbNav);

//        print_r($crumbNav);

        // dump ($crumbNav);
        //验证登陆
        if (! $this->validateLogin()){
            //
            $this->redirect('User/login');
        }
		$this->pageNum =  $this->_get('p') ? $this->_get('p') : 1;
        $nav = $this->getNavData();
		$this->assign("nav",$nav);
	}
    /**
     * 验证是否登陆
     */
    private function validateLogin(){
        $user = $this->_session('user');
        if ( !$user ){
            $cookie_user = cookie('user');
            $rember_me = $cookie_user['remberme'];
            $username = $cookie_user['username'];
            if ( 'on' == $rember_me){
                $memberModel = M('User');
                $member = $memberModel->where(array('username' => $username))->find();
                $member['remberme'] = 'on' ;
                $this->cookieAndSession($member);
                return true;
            }
            return false;
        } else {
             if ( $user['username'] == 'admin' ){
                $this->assign('superAdmin',1);
             }
            return true;
        }


    }

	protected function getPage($model,$pageSize=10,$where='',$relation=false){
		import('ORG.Util.Page');// 导入分页类
		$count = $model->where($where)->count();// 查询满足要求的总记录数
		$Page = new Page($count,$pageSize);// 实例化分页类 传入总记录数和每页显示的记录数
		$pageObject = $Page->fetch();

        $list = null;

        if ($relation){
            $list = $model->relation(true)->where($where)->page($this->pageNum.",$pageSize")->select();
        }else{
            $list = $model->where($where)->page($this->pageNum.",$pageSize")->select();
        }

		
		$view = Think::instance('View');
		$view->assign("page_object",$pageObject);

        Think::instance("view");
		
		$pageDiv = $view->fetch("Tpl/Admin/default/Page/page.inc.html");
		$this->assign("pageDiv",$pageDiv);
		return $list;
	}
	
	private function getNavData(){
		$module = D('Menu');
		$cond['disabled'] = array("eq","0");
        $cond['parentid'] = array("eq","0");
        $ret = $module->relation(true)->where($cond)->order('listorder')->select();
		return $ret;
	}

    private function cookieAndSession($user)
    {
        session('user', $user);
        cookie('user', array('userid' => $user['id'], 'username' => $user['username'], 'logintime' => time() , 'remberme' => $user['remberme'] ));
    }

}
