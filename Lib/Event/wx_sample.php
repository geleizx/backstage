<?php
/**
 * wechat php test
 */

//define your token
define("TOKEN", "novell");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);


            $resultStr = "";
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if (!empty($keyword)) {
                $msgType = "text";

                $contentStr = "你好，您有什么要求？";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);

                if ($keyword == "电话") {
                    $contentStr = "天向互联电话： 0411-88888888";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);

			}
                if ($keyword == "地址") {

                    //http://api.map.baidu.com/staticimage?center=121.645678,38.927698&width=400&height=300&zoom=17&markers=121.645678,38.927698
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[location]]></MsgType>
                            <Location_X>121.645678</Location_X>
                            <Location_Y>38.927698</Location_Y>
                            <Scale>20</Scale>
                            <Label><![CDATA[天向互联地址]]></Label>
                            </xml>";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);

                }


               echo $resultStr;
            } else {
                echo "Input something...";
            }

        } else {
            echo "";
            exit;
        }
    }
}

?>