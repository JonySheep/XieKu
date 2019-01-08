<?php
/**
 * File: Geetest.php
 * User: XljBearSoft
 * Date: 2017-07-05
 * Time: 15:54
 */

namespace app\index\controller;

use think\Request;
use geetest\GeetestLib;
class Geetest
{
    public function init()
    {
        $GtSdk = new GeetestLib(config('geetest.CAPTCHA_ID'), config('geetest.PRIVATE_KEY'));
        $request = Request::instance();
        $data = array(
            "client_type" => "web",
            "ip_address" => $request->ip()
        );
        $status = $GtSdk->pre_process($data, 1);
        session('gtserver',$status);
        echo $GtSdk->get_response_str();
    }
    public function VerifyGeeTest(){
        if(!isset($_POST['geetest_challenge'])||!isset($_POST['geetest_validate'])||!isset($_POST['geetest_seccode']))return false;
        $GtSdk = new GeetestLib(config('geetest.CAPTCHA_ID'), config('geetest.PRIVATE_KEY'));
        $request = Request::instance();
        $data = array(
            "client_type" => "web",
            "ip_address" => $request->ip()
        );
        if (session('gtserver') == 1) {   //服务器正常
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
            if ($result) {
                return true;
            } else{
                return false;
            }
        }else{  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                return true;
            }else{
                return false;
            }
        }
    }
}