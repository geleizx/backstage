<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 嘉懿
 * Date: 13-7-6
 * Time: 下午2:48
 * To change this template use File | Settings | File Templates.
 */

class SystemAction extends AdminBaseAction{
    public function admin() {
    /*
     * ICE 2013-07-09 列出管理员列表 并显示信息
     * @param
     */
//   		$DB_member = M("member");
//   		$adminList = $DB_member -> select();
   		//dump ($adminList);

        $resultArr = $this->queryAdmin();
   		$this->assign('adminList',$resultArr['adminList']);
   		$this->assign('page',$resultArr['pageShow']);
        $this->display();
        
    }


    private function queryAdmin($pre_page = "10"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['username'] = array("LIKE","%$keyword%");
            $where['_logic'] = 'OR';
            $where['nickname'] = array("LIKE","%$keyword%");
        }

        $DB_member = M("member");
        $adminList = $DB_member ->where($where)->select();

        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类


        $count = $DB_member->where($where) ->count(); // 查询满足要求的总记录数
        $Page = new Page($count, $pre_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $adminList = $DB_member->where($where)-> limit($Page->firstRow . ',' . $Page->listRows)->select();

//var_dump($DB_member->getLastSql());
        $resultArr['adminList'] = $adminList;
        $resultArr['pageShow'] = $Page->show();

        return $resultArr;
    }

    public function ajaxSearchAdmin(){
        $keyword = "";


        $queryResult = $this->queryAdmin();

        $this->assign('adminList',$queryResult['adminList']);
        $tabelHtml   = $this->fetch("Tpl/Admin/default/System/admin-table.inc.html");

        $ajaxResult = array("tabelHtml"=>$tabelHtml ,'pagination'=> $queryResult['pageShow']);

        $this->success($ajaxResult);
    }

    public function adminAdd() {
    /*
     * ICE  2013-07-09 新建管理员
     * @param
     */
    	$this->display();
    }
    public function adminAddAct() {
    /*
     * ICE  2013-07-09 新建管理员操作
     * @param  avatar , account , password , nickname , email , mobile , ratio
     */


        $DB_member = M('member');
        $data['username'] = $this->_post('account');
        $data['password'] = md5(md5($this->_post('password')));
        $data['nickname'] = $this->_post('nickname');
        $data['email'] = $this->_post('email');
        $data['mobile'] = $this->_post('mobile');
        $data['avatar'] = $this->_post('avatar');
        $data['groupid'] = '10'; //为培训演示 临时赋值
        $data['regdate'] = strtotime(date('Y-m-d'));

        $res = $DB_member->add($data);
        if ($res) {
            $this->success('添加成功');
        }
        else {
            $this->error('添加失败');
        }
    }
    public function adminEdit() {
    /*
     * ICE  2013-07-09 编辑管理员账户信息
     * @param get id
     */
        $id = $this->_get('id');
        $DB_member = M("member");
        $adminList = $DB_member -> where("`userid` = $id") -> find();
        //dump ($adminList);
        $this->assign('adminList',$adminList);
        $this->display();
    }
    public function adminEditAct() {
    /*
     * ICE  2013-07-09 编辑管理员账户信息
     * @param get id
     */
        $id = $this->_get('id');
        $DB_member = M("member");
        $data['username'] = $this->_post('account');
        $data['nickname'] = $this->_post('nickname');
        $data['email'] = $this->_post('email');
        $data['mobile'] = $this->_post('mobile');

        if ($this->_post('password') != '************') {
         $data['password'] = md5(md5($this->_post('password')));
        }
        //密码没变就不改
        
        $data['avatar'] = $this->_post('avatar');
        $res = $DB_member -> where("`userid` = $id") -> save($data);
        if ($res) {
            $this->success('编辑成功',U('System/admin'));
            exit();
        }
        else {
            $this->error('编辑失败');
        }
    }
    public function setting() {
        $config = D('Config')->find();
        $this->assign('config',$config);
    	$this->display();

    }

    public function  admin_setting(){
    /*
     * 2013-07-15 系统参数设置页面
     */
        $DB_admin_panel = M('admin_panel');
        $settingList = $DB_admin_panel -> where("`id` = 1") -> find();

        $navList = $this->getNavData();
//print_r($navList);
        $this->assign('settingList',$settingList);
        $this->assign('navList',$navList);
        $this->assign('langList',C('lang_list'));
        $this->display();
    }


    private function createCategory($lang_list = ""){
        $moduleObj = D("module");
        $categoryObj = D("Category");

        $moduleArr = $moduleObj->select();

        $langArr = C('lang_list');

        foreach($moduleArr as $key=>$module){
            foreach($lang_list as $key=>$lang){
                $category = array(
                    'module' =>$module['id'],
                    'catname' =>$langArr[$lang],
                    'setting' =>$lang,
                );

                $categoryObj ->add($category);
            }

        }
    }



    private   function getNavData(){
        $module = D('Menu');
//        $cond['disabled'] = array("eq","0");
        $cond['parentid'] = array("eq","0");
        $ret = $module->relation(false)->where($cond)->order('listorder')->select();

        return $ret;
    }

    /**
     *设置/更新模块开启/关闭
     */
    public function setNavDisabled(){

        $navid = $this->_post('navId');
        $navDisabled = $this->_post('navDisabled');
        $meunModelObj = M('menu');

        $DisabledMenu['disabled'] = $navDisabled;
        $DisabledMenu['id'] = $navid;

//var_dump($DisabledMenu);

        $res = $meunModelObj->save($DisabledMenu);


        if($res) {
            $this->success(1);
        }
        else {
            $this->error();
        }

    }


    public function adminSettingAct () {
    /*
     * 2013-07-15 系统参数设置操作
     */

        $post = $this->_post();

        $def_lang  = $this->_post('def_lang');
        $lang_list = $this->_post('lang_list');


        $this->createCategory($lang_list);


        $data['def_lang']  = $def_lang;

//      serialize ---------   将数组格式化成有序的字符串
//      unserialize   ----- 将数组还原成数组
        $data['lang_list'] = serialize($lang_list);


        $data['custom'] = $this->_post('custom');
        $data['customcontacter'] = $this->_post('customcontacter');
        $data['customphone'] = $this->_post('customphone');
        $data['agent'] = $this->_post('agent');
        $data['agentphone'] = $this->_post('agentphone');
        $data['domain'] = $this->_post('domain');

        $data['motion_value'] = $motion_value = $this->_post('motion_value');
        $data['motion_type'] = $motion_type = $this->_post('motion_type');

        //计算到期时间
        
        $startTime = $this->_post('motionstart');
        $data['motionstart'] = strtotime($startTime);
        import('ORG.Util.Date');// 导入日期类
        $Date = new Date($startTime);
        $endDate = $Date -> dateAdd($motion_value,$motion_type) ;
        $endTime = strtotime($endDate);
        $data['motionend'] = $endTime;


        $data['spaceSizeValue'] = $this->_post('spaceSizeValue');
        $data['spaceSizeType'] = $this->_post('spaceSizeType');

        $data['siteid'] = $this->_post('siteid');
        $data['key'] = $this->_post('key');
        $data['pop3'] = $this->_post('pop3');
        $data['smtp'] = $this->_post('smtp');
        $data['mailuser'] = $this->_post('mailuser');
        $data['mailpassword'] = $this->_post('mailpassword');
        if ($this->_post('trustservice')) {
            $data['trustservice'] = 1;
        }
        else {
            $data['trustservice'] = 0;
        }


        $DB_admin_panel = M("admin_panel");
        $res = $DB_admin_panel -> where("`id` = 1") -> save($data);

        //echo $DB_admin_panel -> getLastSql();
        //dump($res);

        if($res) {
            $this->success();
        }
        else {
            $this->error();
        }



    }

    public function save_setting(){
        $model = D('Config');
        $config = $model->find();

        $skin = $this->_post('skin');
        $config['skin'] =  $skin;
        $model->save($config);
        $this->success('');

    }

    public function delAdminAct () {
    /*
     * ICE 2013-07-16 删除管理员操作
     * @param ajax post 'id'
     */

        $admin = D('member');
        $ids = join($this->_post('ids') , ',');

        $admin ->delete($ids);
        if ($admin->getError()) {
            $this->error('删除失败');
        }else{
            $this->success('删除成功');
        }
    }


}