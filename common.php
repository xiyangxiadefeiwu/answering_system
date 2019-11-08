<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小寒 <y_qianhan@163.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**打印数据
 * @param $data
 * @param string $flag
 */
function alert($data,$flag=''){
    if($flag==1){
        echo '<pre>';
        print_r($data);
    }else{
        echo '<pre>';
        print_r($data);
        die;
    }

}

/**
 * 字符截取 支持UTF8/GBK
 */
function strcut($string, $length, $dot = '...') { //$string 为截取的原文字信息   $length截取长度   $dot为填充符
    $charset = 'utf-8';
    if (strlen($string) <= $length) {
        return $string;
    }
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    if (strtolower($charset) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }
            if($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut.= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

/**
 * @param $string    要加密/解密的字符串
 * @param $operation    类型，E 加密；D 解密
 * @param string $key   密钥
 * @return mixed|string
 *
 * 第一种加密解密方法
 * 使用方式：encrypt($password,'E','www.vanli.com');
 */
function encrypt($string,$operation,$key=''){
    $key=md5($key);
    $key_length=strlen($key);
    $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string;
    $string_length=strlen($string);
    $rndkey=$box=array();
    $result='';
    for($i=0;$i<=255;$i++){
        $rndkey[$i]=ord($key[$i%$key_length]);
        $box[$i]=$i;
    }
    for($j=$i=0;$i<256;$i++){
        $j=($j+$box[$i]+$rndkey[$i])%256;
        $tmp=$box[$i];
        $box[$i]=$box[$j];
        $box[$j]=$tmp;
    }
    for($a=$j=$i=0;$i<$string_length;$i++){
        $a=($a+1)%256;
        $j=($j+$box[$a])%256;
        $tmp=$box[$a];
        $box[$a]=$box[$j];
        $box[$j]=$tmp;
        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
    }
    if($operation=='D'){
        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){
            return substr($result,8);
        }else{
            return'';
        }
    }else{
        return str_replace('=','',base64_encode($result));
    }
}

//>>获取网站标题
function getWebsiteTitle($website_address){
    try {
        //设置超时参数
        $opts=array(
            "http"=>array(
                "method"=>"GET",
                "timeout"=>3
            ),
        );
////创建数据流上下文
        $context = stream_context_create($opts);
        $data = file_get_contents($website_address,false,$context);
    } catch (Exception $e) {
        $data='';
    }
    if($data){
        $title = '';
        preg_match('/<TITLE>([\w\W]*?)<\/TITLE>/si', $data, $matches);
        if (!empty($matches[1])) {
            $title = $matches[1];
        }
    }else{
        $title='';
    }
    //$title = iconv("gb2312", "utf-8//IGNORE",$title);
    return $title;
}

/**
 * @param $string    要加密/解密的字符串
 * @param string $operation   类型，ENCODE 加密；DECODE 解密
 * @param string $key    密匙
 * @param int $expiry    有效期
 * @return string
 *
 * 第二种加密解密方法
 * 使用方式：
 * 加密 ： authcode('字符串', 'ENCODE');
 * 解密 ： authcode('加密后的字符串', 'DECODE');
 */
function authcode($string, $operation = 'DECODE', $key = 'encrypt', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    //解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
        ) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * @param $data  要加密的字符串
 * @param $key   密钥
 * @return string
 *
 * 第三种加密解密方法
 * 使用方式：$encode = encrypt_1($string);
 */
function encrypt_1($data, $key = 'encrypt')
{
    $key = md5($key);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);

}

/**
 * @param $data    要解密的字符串
 * @param $key     密钥
 * @return string
 *
 * 第三种加密解密方法
 * 使用方式 ：$decode = decrypt_1($encode);
 */
function decrypt_1($data, $key = 'encrypt'){
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

function getIp() {
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    return $res;
}

function browse_info() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } else if (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } else if (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } else if (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } else if (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return $br;
    } else {
        return 'unknow';
    }
}

