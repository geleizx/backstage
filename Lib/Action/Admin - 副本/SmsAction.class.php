<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-23
 * Time: 下午3:03
 *
 */

class SmsAction extends AdminBaseAction
{


    public function __construct(){
        parent::__construct();
    }


    private $ret;

    public function getRet()
    {

        return $this->ret;
    }



    public function test(){
        import("ORG.Wechat");

        $options = array(
            'token'=>'novell' //填写你设定的key
        );

        $weixin = new Wechat($options);
        $weixin->getRevContent();

    }


    /**
     *
     */
    public function index(){
        $this->display();
    }

    public function create(){
        $this->display();
    }


    public function  sms(){

        $uid = 'YU10';		//用户账号
        $pwd = '000000';		//密码
        $mobile	 = '18698812279';	//号码
        $content = '张总，你妈喊你吃饭了！';		//内容
        //即时发送
        $res = $this->sendSMS($uid,$pwd,$mobile,$content);
        echo $res;

    }


    /*
    $time = '2010-05-27 12:11';
    $res = sendSMS($uid,$pwd,$mobile,$content,$time);
    echo $res;
    */
    private function sendSMS($uid, $pwd, $mobile, $content, $time = '', $mid = '')
    {
        $http = 'http://vip.52ao.com/hk/submit';
        $data = array
        (
            'Sp_name' => $uid, //用户账号
            'Sp_pwd' => strtolower(md5($pwd)), //MD5位32密码
            'Mobile' => $mobile, //号码
            'Msg_Content' => $content, //内容
            'Msg_Encode' =>'UTF8',
            //'time' => $time, //定时发送
            'mid' => $mid //子扩展号
        );
        $re = $this->postSMS($http, $data); //POST方式提交
        if (trim($re) == '100') {
            return "发送成功!";
        } else {
            return "发送失败! 状态：" . $re;
        }
    }

    private function postSMS($url, $data = '')
    {
        $row = parse_url($url);
        $host = $row['host'];
        $port = $row['port'] ? $row['port'] : 80;
        $file = $row['path'];
        while (list($k, $v) = each($data)) {
            $post .= rawurlencode($k) . "=" . $v . "&"; //转URL标准码
        }


        $ch = curl_init();

// 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, 'http://vip.52ao.com/hk/submit?'.$post);
        curl_setopt($ch, CURLOPT_HEADER, 0);

// 抓取URL并把它传递给浏览器
        curl_exec($ch);

// 关闭cURL资源，并且释放系统资源
        curl_close($ch);



//        $post = substr($post, 0, -1);
//        $len = strlen($post);
//        $fp = @fsockopen($host, $port, $errno, $errstr, 10);
//        if (!$fp) {
//            return "$errstr ($errno)\n";
//        } else {
//            $receive = '';
//            $out = "POST $file HTTP/1.1\r\n";
//            $out .= "Host: $host\r\n";
//            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
//            $out .= "Connection: Close\r\n";
//            $out .= "Content-Length: $len\r\n\r\n";
//            $out .= $post;
//            fwrite($fp, $out);
//            while (!feof($fp)) {
//                $receive .= fgets($fp, 128);
//            }
//            fclose($fp);
//            $receive = explode("\r\n\r\n", $receive);
//            unset($receive[0]);
//            return implode("", $receive);
//        }
    }


}