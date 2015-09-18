<?php

$url = "http://customization.91vst.com:88/api3.0/downloadtvdb.action";

echo gzdecode( curlpoststr($url) );

$open=fopen("log.txt","a" );
fwrite($open,gzdecode( curlpoststr($url) ));
fclose($open);
exit;




function curlpoststr( $uri   ){
    $ch = curl_init ();
    $this_header = array(
        "content-type: application/x-www-form-urlencoded;
        charset=UTF-8"
    );
    $headers['name'] = 'dangbei';
    $headers['key'] = 'DD7QXWR3EWZEEF7Z939Z';
    $headerArr = array();
    foreach( $headers as $n => $v ) {
        $headerArr[] = $n .':' . $v;
    }
    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    curl_setopt ( $ch, CURLOPT_URL, $uri );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER , $headerArr );  //构造IP
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    $return = curl_exec ( $ch );
    curl_close ( $ch );
    return $return ;
}

if (!function_exists('gzdecode')) {
    function gzdecode ($data) {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) // Filename
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16) // Comment
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2) // CRC at end of file
            $headerlen += 2;
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE)
            $unpacked = $data;
        return $unpacked;
    }
}