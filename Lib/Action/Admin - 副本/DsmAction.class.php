<?php


if (empty($_SESSION[access_token]))
{
$s = json_decode(file_get_contents("http://open.moretv.com.cn/authorize?appid=bf566f7456117820b397d26fa258c318"), 1);
$authorize_code = $s[authorize_code];
$key = md5("bf566f7456117820b397d26fa258c318_e6ecbf7200c7421719778e4748dfbaa8_".$authorize_code);
$sl = json_decode(file_get_contents("http://open.moretv.com.cn/get_access_token?authorize_code=".$authorize_code."&key=".$key), 1);
$access_token = $sl[access_token];
$_SESSION[access_token] = $access_token;
}

else{
    $access_token = $_SESSION[access_token];
}
$url = "http://open.moretv.com.cn/live?access_token=" . $access_token;
$str = file_get_contents($url);
$tvlist = json_decode($str, 1);
if( $tvlist[status] == 106 ){
    $s = json_decode(file_get_contents("http://open.moretv.com.cn/authorize?appid=bf566f7456117820b397d26fa258c318"),1);
    $authorize_code = $s[authorize_code];
    $key = md5("bf566f7456117820b397d26fa258c318_e6ecbf7200c7421719778e4748dfbaa8_".$authorize_code);
    $sl = json_decode(file_get_contents("http://open.moretv.com.cn/get_access_token?authorize_code=".$authorize_code."&key=".$key),1);
    $access_token = $sl[access_token];
    $_SESSION[access_token] = $access_token  ;
    $url = "http://open.moretv.com.cn/position/".$contentType."?access_token=".$access_token;
    $str = file_get_contents($url);
//    $tvlist = json_decode($str,1);
}


$open = fopen("dsm.txt", "a");
fwrite($open,$str);
fclose($open);
exit;

