<?php
use think\Db;
/**
 * 获取指定长度的随机字母数字组合的字符串
 *
 * @param int $type 字符类型 1数字字母混合 2纯数字 3纯字母
 * @param  int $length
 * @return string
 */
function random($type = 1, $length = 6)
{
    $pool = '';
    switch ($type) {
        case 1:
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 2:
            $pool = '0123456789';
            break;
        case 3:
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
    }


    return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

function getUidByOpenid($openid = '')
{
    return Db::name('Users')->where('openid',$openid)->value('id');
}

//curl请求
function http_request($url,$data = null,$headers=array())
{
    $curl = curl_init();
    if( count($headers) >= 1 ){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($curl, CURLOPT_URL, $url);


    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);


    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}



function xml2array($xml){
    $p = xml_parser_create();
    xml_parse_into_struct($p, $xml, $vals, $index);
    xml_parser_free($p);
    $data = "";
    foreach ($index as $key=>$value) {
        if($key == 'xml' || $key == 'XML') continue;
        $tag = $vals[$value[0]]['tag'];
        $value = $vals[$value[0]]['value'];
        $data[$tag] = $value;
    }
    return $data;
}

function array2xml($data){
    if(!is_array($data) || count($data) <= 0){
        return false;
    }
    $xml = "<xml>";
    foreach ($data as $key=>$val){
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;
}

function getNonceStr()
{
    $result = '';
    $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
    for ($i=0;$i<32;$i++){
        $result .= $str[rand(0,48)];
    }
    return $result;
}

function getIP(){
    global $ip;
    if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow IP";
    return $ip;
}


function makeSign($data)
{
    $stringA = '';
    $config = config('wx_pay');
    foreach ($data as $key=>$value){
        if(!$value) continue;
        if($stringA) $stringA .= '&'.$key."=".$value;
        else $stringA = $key."=".$value;
    }
    $wx_key = $config['wx_key'];//申请支付后有给予一个商户账号和密码，登陆后自己设置的key
    $stringSignTemp = $stringA.'&key='.$wx_key;
    return strtoupper(md5($stringSignTemp));
}


/**
 * 根据经纬度算距离，返回结果单位是公里，先纬度，后经度
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @return float|int
 */
 function GetDistance($lat1, $lng1, $lat2, $lng2)
{
    $EARTH_RADIUS = 6378.137;

    $radLat1 = rad($lat1);
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = rad($lng1) - rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 10000) / 10000;

    return $s;
}
 function rad($d)
{
    return $d * M_PI / 180.0;
}

/**
 * 返回数组中指定的一列
 * @param $input            需要取出数组列的多维数组（或结果集）
 * @param $columnKey        需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。 也可以是NULL，此时将返回整个数组（配合index_key参数来重置数组键的时候，非常管用）
 * @param null $indexKey    作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 * @return array            返回值
 */
function new_array_column($input, $columnKey, $indexKey = null)
{
    if (!function_exists('array_column')) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array)$input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    } else {
        return array_column($input, $columnKey, $indexKey);
    }
}
