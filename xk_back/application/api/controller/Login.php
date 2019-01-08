<?php
namespace app\api\controller;
use app\index\controller\CoreController;
use app\index\model\System;
use app\index\model\WeiXin as Chat;

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Login extends CoreController
{
    public function index(){
var_dump($_POST);exit();

        $errCode = Chat::decryptData($_POST['session_key'],$_POST['encryptedData'], $_POST['iv'], $data );
        if ($errCode == 0) {
            echo 'ok';
//            $user_model = GaoQiao_User::getInstance();
            //注册 登录  返回 uid  和token
//            $rs =  $user_model->loginSmallProgram($data);
//            $user = $user_model->find(['id'=>$rs['uid']]);
//            api_message(0,['uid'=>$rs['uid'],'token'=>$rs['token'],'uinfo'=>$user]);
        } else {
//            api_message(1,$errCode);
            echo 'no';
        }
    }

    /**
     * 增加浏览量
     * @author huanghao
     */
    public function PageViews(){
        $systemModel = new System();
        if($systemModel->addPageViews()){
            return $this->renderSuccess(['操作成功']);
        }else{
            return $this->renderError('操作失败');
        }
    }
}