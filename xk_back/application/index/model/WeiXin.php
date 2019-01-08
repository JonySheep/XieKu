<?php
/**
 * File: Article.php
 * User: XljBearSoft
 * Date: 2017-07-24
 * Time: 13:35
 */

namespace app\index\model;


use think\Db;
use think\Model;
use think\Session;

class WeiXin extends Model
{
    private static $App_id ='wx830f32fd1326dc7a';
    private static $AppSecret ='2a5ec1425e1ed6445e337282fedfbfdf';

    const   SESSION_KEY ='smallprogram:session:key:';
    const   SP_SP_SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session?';


    public static function get_session_key($code)
    {
        $data = [
            'appid'=>self::$App_id,
            'secret'=>self::$AppSecret,
            'js_code'=>$code,
            'grant_type'=>'authorization_code',
        ];
        //$url = self::SP_SP_SESSION_URL.http_build_query($data);
        $content = http_request(self::SP_SP_SESSION_URL,$data);
        //$content =  file_get_contents($url);
        $result = json_decode($content, true);

        return $result;
    }

    public static function app_id(){
        return self::$App_id;
    }


    public static function decryptData($sessionKey,$encryptedData, $iv, &$data ){

        if (strlen($sessionKey) != 24) {
            return -41001;
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return -41003;
        }
        if( $dataObj->watermark->appid != self::$App_id )
        {
            return  -41003;
        }
        $data = $result;
        return 0;
    }

    /**
     * 获取微信全局凭证access_token
     * @author huanghao
     */
    public static function getAccessToken(){
        $find = Db::name('weixin_access_token')->find();
        if($find['expires_in'] > time()){
            return $find['access_token'];
        }
        $data = [
            'appid'=>self::$App_id,
            'secret'=>self::$AppSecret,
            'grant_type'=>'client_credential',
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/token?';
        $content =  file_get_contents($url.http_build_query($data));
        $result = json_decode($content, true);
        if($result['access_token']){
            $save = [
                'access_token' => $result['access_token'],
                'expires_in' => time()+$result['expires_in'],
            ];
            Db::name('weixin_access_token')->where('id', 1)->update($save);
            return $save['access_token'];
        }else{
            return false;
        }

    }

    /**
     * 获取微信jsapi config数据
     */
    public static function getConfigs(){
        $access_token = self::getAccessToken();
        if(!$access_token) return false;
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?';
        $data = [
            'access_token' => $access_token,
            'type' => 'jsapi'
        ];
        $content =  file_get_contents($url.http_build_query($data));
        $result = json_decode($content, true);
        $jsapi_ticket = $result['ticket'];  //jsapi的临时票据
        $time = time();
        $random_str = random();  //随机字符串
        $signData = [
            'noncestr' => $random_str,
            'jsapi_ticket' => $jsapi_ticket,
            'timestamp' => $time,
            'url' => request()->domain(),
        ];
        ksort($signData);
        $sign = sha1(http_build_query($signData));  //签名
        $configData = [
            'debug' => 'true',   //是否开启调试
            'appId' => self::app_id(),   //appid
            'timestamp' => $time,   //时间戳
            'nonceStr' => $random_str,  //随机字符串
            'signature' => $sign,   //签名
        ];
        return $configData;

    }
}