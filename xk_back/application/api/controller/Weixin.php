<?php
namespace app\api\controller;

use app\index\controller\CoreController;
use app\index\model\WeiXin as Chat;
use app\index\model\Users as User;
use think\Request;

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Weixin extends CoreController
{
    public function index()
    {
        $code = $_POST['code'];
        $rs = Chat::get_session_key($code); 
        if( ! isset($rs['session_key']) )
            return $this->renderError($rs['message']);

        $this->showMessage(1, $rs['session_key']);

    }

    public function login()
    {
        $errCode = Chat::decryptData($_POST['session_key'], $_POST['encryptedData'], $_POST['iv'], $data);
        if ($errCode == 0) {
            $rs = User::userLogin(json_decode($data,1));
            if ($rs == false) {
                $this->showMessage(2, '用户被禁止登录');
            }
            $this->showMessage(1, $rs);
        } else {
            $this->showMessage(2, $errCode);
        }
    }

    /**
     * 获取微信凭证AccessToken
     * @author huanghao
     */
    public function getAccessToken(){
        $access_token = Chat::getAccessToken();
        if($access_token){
            $this->showMessage(1, ['access_token' => $access_token], '获取成功');
        }else{
            $this->showMessage(2, null, '获取失败');
        }
    }

    /**
     * 获取微信分享所需数据
     * @author huanghao
     * @return string
     */
    public function getShareData()
    {
        $data =  Chat::getConfigs();
        return $data;
    }

}