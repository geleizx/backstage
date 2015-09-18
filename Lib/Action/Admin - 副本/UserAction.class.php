<?php

/**
 * 新闻咨询Action
 * @author Gavin
 *
 */
class UserAction extends BaseAction
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->assign("css_dir", __APP__ . "/Public/css");
        $this->assign("js_dir", __APP__ . "/Public/js");
        $this->assign("images_dir", __APP__ . "/Public/images");
    }
    /**
     *
     */
    public function verify(){
        import('ORG.Util.Image');
        Image::buildImageVerify();
    }
    /**
     * 登陆页面
     */
    public function login()
    {
    /*
     * ICE 2013-07-05
     * 实现自动记录登陆信息功能
     * 显示登陆页面前 先读取SESSEION信息 然后写入页面
     */
        $userInfo = cookie('user');
        //获得COOKIE中存储的用户名
        //dump ($userInfo);
        $this -> assign('userInfo',$userInfo);
        $this -> display();
    }

    /**
     * 登陆验证
     */
    public function userLogin()
    {

        $refer = $this->_get('refer');

        $username = $this->_post('username');
        $password = $this->_post('password');
        $remberme = $this->_post('rember_me');


        $password = md5(md5($password));

        $memberModel = D('Member');

        $member = $memberModel->where(array('username' => $username))->find();

        $member['remberme'] = $remberme ;
        if (!empty($member)) {
            if ($member['password'] == $password) {
                if ($member['groupid'] > 1) {

                    $member['lastdate'] = time();
                    $member['lastip'] = getip();
                    //记录IP和上次登录时间
                    $memberModel->save($member);
                    $this->cookieAndSession($member);
                    $this->success(array('refer' => $refer));
                    //有权限


                } else {
                    //
                    $this->error('用户无管理权限');
                }
            } else {
                $this->error('用户不存在或密码不正确');
            }
        } else {
            $this->error('用户不存在或密码不正确');
        }
    }

    private function cookieAndSession($user)
    {
        session('user', $user);
        cookie('user', array('userid' => $user['id'], 'username' => $user['username'], 'logintime' => time() , 'remberme' => $user['remberme'] ,'avatar' => $user['avatar'] ));
    }

    /**
     * logout
     */
    public function changePassword () {
    /*
     * ICE 2013-06-26
     * 修改密码 返回修改结果 success or false
     * 
     */
        $DB_member = M('member');
        $postOldPassword = $this->_post('pw'); //旧密码
        $postNewPassword = $this->_post('npw'); //新密码
        $postVNewPassword = $this->_post('vpw'); //再次输入的新密码

        $userid = $this->_session('user');
        $userid = $userid['userid'];
        $userid = $userid+0;
        // session中存储的用户编号
        $oldFingerMark = $DB_member -> where("`userid` = $userid") -> field("`password`") -> find();
        $oldFingerMark = $oldFingerMark['password'];
        $newFingerMark = md5(md5($postOldPassword));
        if ($oldFingerMark != $newFingerMark) {
            $this->error('密码错误');
        }
        else if ($postNewPassword != $postVNewPassword) {
            $this->error('两次输入的新密码不一样');
        }
        else {
            //写库操作
            $data['password'] = md5(md5($postNewPassword));
            $res = $DB_member -> where("`userid` = $userid") -> save($data);
            echo $DB_member -> getLastSql();
            if ($res) {
                $this->success();
            }
            else {
                $this->error('修改失败');
            }
        }

        //$this->show('changePassword');

    }
    public function logout()
    {
        session('user', null);
        cookie('user', null);
    }

    public function getUserInfo(){
        $username = $this->_get('username');
        $user = M('Member')->where(array('username'=>$username))->find();
        $this->success($user);
    }


}
