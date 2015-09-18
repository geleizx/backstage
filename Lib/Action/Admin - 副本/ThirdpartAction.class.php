<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-24
 * Time: 上午10:16
 * 
 */

class ThirdpartAction extends AdminBaseAction{

    public function __construct(){
        parent::__construct();
    }


    public function index(){

        import("ORG.ThinkSDK.ThinkOauth");
        $sdk  = ThinkOauth::getInstance('sina');

        redirect($sdk->getRequestCodeURL());

    }


    public function callback($type = null, $code = null){
        (empty($type) || empty($code)) && $this->error('参数错误');

        //加载ThinkOauth类并实例化一个对象
        import("ORG.ThinkSDK.ThinkOauth");
        $sns  = ThinkOauth::getInstance($type);

        //腾讯微博需传递的额外参数
        $extend = null;
        if($type == 'tencent'){
            $extend = array('openid' => $this->_get('openid'), 'openkey' => $this->_get('openkey'));
        }

        //请妥善保管这里获取到的Token信息，方便以后API调用
        //调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
        //如： $qq = ThinkOauth::getInstance('qq', $token);
        $token = $sns->getAccessToken($code , $extend);

        $third = M('Thirdpart');
        $data = array( 'type' => $type , 'access_token' => $token['access_token'],'expires_in'=>$token['expires_in']);

        $third->add($data);

        $third->save();

        $this->redirect('Index/index');


    }

    public function sendNewMessage( $message,$type="sina"){
        import("ORG.ThinkSDK.ThinkOauth");
        $thirdpart = M('Thirdpart');
        $ret = $thirdpart->where(array('type'=>$type))->find();
        $token = $ret['access_token'];
        $sns  = ThinkOauth::getInstance($type,$token);
        $ret = $sns->call('statuses/update','access_token='.$token.'&status='.$message,'POST');
        return $ret['id'];
    }


}