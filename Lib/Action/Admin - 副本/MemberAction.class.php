<?php
/*
 * Created by ICE 2013-10-16
 * 后台会员管理部分 
 */

class MemberAction extends AdminBaseAction{

    private $modelNamesC = array(
                                    '0' => 'Category',
                                    '1' => 'Product',
                                    '2' => 'Page',
                                    '3' => 'News',
                                );
    private $modelCodeC = array(
                                    'Category' => '0',
                                    'Product' => '1',
                                    'Page'=>'2',
                                    'News'=>'3',
                                );

    public function __construct(){
        parent::__construct();
        $this->adIds =  $this->_post('adIds');
    }

    public function index(){

        $groupId = I('groupId',0);
        $memberModel = new MemberModel('member');
        $memberList = $memberModel->getMemberList($groupId);
        $this->assign("memberList",$memberList);

        $this->display();
    }

    public function ajaxSearch(){
        $keyword = I('keyword');
        $where['username|nickname|email'] = array("LIKE","%$keyword%");
        $memberModel = new MemberModel('member');
        $memberList = $memberModel->getMemberList(0,$where);
        $this->assign("memberList",$memberList);

        $pageTabelHtml   = $this->fetch("Tpl/Admin/default/Member/member-table.inc.html");

        $ajaxResult = array("pageTabelHtml"=>$pageTabelHtml ,'pagination'=> '');

        $this->success($ajaxResult);
    }

    public function group(){
        $groupList = $this->getGroupList();
        $this->assign('groupList',$groupList);

        $this->display();
    }

    public function groupConfig(){
        $id = I('id',0);
        $resultArr = $this->getGroupInfoById($id);

        $this->assign('groupObj',$resultArr['groupObj']);
        $this->assign('roleObj',$resultArr['roleObj']);
        $this->assign('cat',count($resultArr['roleObj'][0]));
        $this->assign('product',count($resultArr['roleObj'][1]));
        $this->assign('page',count($resultArr['roleObj'][2]));
        $this->assign('news',count($resultArr['roleObj'][3]));

        $this->display("Tpl/Admin/Member/creategroup.html");
    }

    /**
     * 删除新闻
     */
    public function delete()
    {

        $DB_page = M('Member');
        $ids = join($this->_post('PageIds'), ',');
        $res = $DB_page-> where("`userid` IN ($ids)") -> delete();
        if ($res) {
            $this->success('删除成功');
        }
        else {
            $this->error('删除失败');
        }
    }


    public function copy() {
        $this->display();
    }
    public function create() {
        $groupList = $this->getGroupList();
        $this->assign("groupList",$groupList);
        $this->display();
    }


    public function edit() {
        $id = I('id');

        $memberObj = M('Member')->where('userid='.$id)->find();
//        $memberObj = M('Member_detail')->where('userid='.$id)->find();

        $groupList = $this->getGroupList();

        $this->assign("groupList",$groupList);
        $this->assign('adminList',$memberObj);
        $this->display();
    }

    public function update(){
        $memberModel = new MemberModel('Member');
        $data['username'] = $this->_post('account');
        $data['userid'] = I('userid');
        $data['password'] = md5(md5($this->_post('password')));
        $data['nickname'] = $this->_post('nickname');
        $data['email'] = $this->_post('email');
        $data['mobile'] = $this->_post('mobile');
        $data['vip'] = 1; //临时使用, 当前vip等>0 时, 表示为前台用户;
        $data['avatar'] = $this->_post('avatar');
        $data['groupid'] = I("groupid");
        $data['post'] = I("post");
        $data['sex'] = I("sex");
        $data['identity'] = I("identity");
        $data['emergency'] = I("emergency");
        $data['university'] = I("university");
        $data['bedroom'] = I("bedroom");
        $data['major'] = I("major");
        $data['entrance_time'] = I("entrance_time");
        $data['company_name'] = I("company_name");
        $data['company_address'] = I("company_address");
        $data['company_type'] = I("company_type");
        $data['company_call'] = I("company_call");
        $data['lastdate'] = strtotime(date('Y-m-d'));
        $userid= I('userid');
        $jifen = $memberModel->where("userid = $userid")->find();
        if(empty($jifen['regdate'])){
            $data['regdate'] = strtotime(date('Y-m-d'));
        }
        $jifen_before = I('integral');
        if($jifen_before){
            $memberjifenModel = M("member_jifen");
            $jifen['userid'] = I('userid');
            $jifen['lasttime'] =  strtotime(date('Y-m-d h:i:s'));
            $jifen['integral'] =  $jifen_before;
            $jifen_add = $memberjifenModel->add($jifen);
        }
        $all_jifen = $jifen['all_integral'];
        $jifen_after = $all_jifen + $jifen_before;

        $data['integral'] = $jifen_before;
        $data['all_integral'] = $jifen_after;

        $res = $memberModel->update($data);
        if(!$res){
            $this->error($memberModel->getError());
        }else{
            $this->success($res['userid']?'更新成功':'新增成功', U('index'));
        }
    }

    public function  ajaxListGroup(){
        $groupList = $this->getGroupList();
        $this->success($groupList);
    }


    public function teamplate() {
        $this->display();
    }
    public function createGroup(){
        $this->display();
    }
    public function groupindex(){
        $this->display();
    }


    public function groupAdd(){

        $editId = I('editId',0);

        $group['name']=I('name','');
        $group['point']=I('point','0');
        $group['sort']=I('sort','0');
        $group['description']=I('description','');
        $group['allowdownload']=I('allowdownload','0');
        $group['allowsendmessage']=I('allowsendmessage','0');
        $group['disabled']=I('disabled','0');
        $roles=$_REQUEST['role'];

        if($editId > 0){
            $groupId = $editId;
            $group['id'] = $groupId;
            $this->updateGroup($group);

            if($groupId > 0){
                $result =  $this->insertRoleByGroupId($roles,$groupId);

                if($result > 0){
                    $this->success('编辑成功');
                }
            }
            $this->error('编辑失败');
        }else{
            $groupId = $this->insertGroup($group);

            if($groupId > 0){
                $result =  $this->insertRoleByGroupId($roles,$groupId);

                if($result > 0){
                    $this->success('添加成功');
                }
            }
            $this->error('添加失败');
        }

    }

    public function ajaxDelGroup(){
        $groupId = I('group_id','0');
        if($groupId > 0){
            $result = $this->removeGroup($groupId);
            if($result['error']){
                $this->error($result['message']);
            }
            $this->success($result['message']);
        }
        $this->error('用户组未找到');

    }

    private function getGroupList(){
        $groupModel = M('member_group');
        $groupList = $groupModel->select();
        return $groupList;
    }

    private function insertGroup($group){
        $groupModel = M('member_group');

        $groupId = $groupModel->add($group);

        return $groupId;
    }

    private function updateGroup($group){
        $groupModel = M('member_group');

        $groupId = $groupModel->save($group);

        return $groupId;
    }

    private function insertRoleByGroupId($roles , $groupId){

        $roleModel = M('group_role');
        try{
            foreach($roles as $key=>$roleTypes){
                $model_name = $this->modelNamesC[$key];
                foreach($roleTypes as $v){
                    $roleObj['model_name'] = $model_name;
                    $roleObj['group_id'] = $groupId;
                    $roleObj['role_id'] = $v;
                    $roleModel->add($roleObj);
                }
            }
            return 1;
        }catch (Exception $e){
            return 0;
        }

    }
    public function verifyMemberAct () {
        //审核消息
        $id = $this->_post('userid');

        //获得传入ID

        $memberModel = M('member');
        $verify = $memberModel -> where("`userid` = $id") -> field('verify') -> find();
        //echo $DB_messageboard -> getLastSql();
        //dump ($verify);
        if ($verify['verify'] == 1) {
            $data['verify'] = 0 ;
        }
        else {
            $data['verify'] = 1 ;
        }
        $res = $memberModel -> where("`userid` = $id") -> save($data);
        // echo $DB_messageboard -> getLastSql();

        $status = $data['verify'];

        if ($res) {
            $this->success($status);
        }
        else {
            $this->error('操作失败');
        }

    }
    private function removeGroup($groupId){

        $error = 0 ;
        $groupModel = M('member_group');
        $memberModel = M('member');
        $groupMember = $memberModel->where('groupid='.$groupId)->select();
        if(!empty($groupMember)){
            $error = 1;
            $errorMessage = '请先解除该用户组用户关系才进行删除 , 共 '.count($groupMember)." 个会员在此用户组下; ";
            return array('error'=>$error,'message'=>$errorMessage);
        }
        if($groupModel->where('id='.$groupId)->delete()){
            $roleModel = M('group_role');
            $result = $roleModel->where('group_id='.$groupId)->delete();
            if($result){
                return array('error'=>$error,'message'=>'操作成功');
            }
        }

        return array('error'=>1,'message'=>'操作失败');

    }


    private function getGroupInfoById($groupId){
        $groupModel = new GroupModel('member_group');

        $groupObj = $groupModel->relation(true)->find($groupId);

        $roles = $groupObj['roles'];

        $roleArr = array();

        foreach($roles as $key=>$role){
            $model_name = $role['model_name'];
            $codeKey = $this->modelCodeC[$model_name];
            $role_id = $role['role_id'];
            $roleActionObj = $this->getRoleContentByRoleId($role_id ,$model_name);
            $role['role_name']=$roleActionObj['role_name'];

            if(!empty($roleActionObj)){
                $roleArr[$codeKey][] = $role;
            }

        }

        return array('groupObj'=>$groupObj , 'roleObj'=>$roleArr);

    }

    private function getRoleContentByRoleId($roleId,$modelName){
        $model = M($modelName);

        $obj =  $model->find($roleId);
        $codeKey = $this->modelCodeC[$modelName];
        if(!empty($obj)){
            switch($codeKey){
                case 0: //分类
                    $obj['role_name'] = $obj['catname'];
                    break;
                case 1: //产品
                    $obj['role_name'] = $obj['product_name'];
                    break;
                case 2: //页面
                    $obj['role_name'] = $obj['title'];
                    break;
                case 3: //文章
                    $obj['role_name'] = $obj['title'];
                    break;

            }

            return $obj;
        }

        return "";

    }


}