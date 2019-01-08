<?php
/**
 * File: User.php
 * User: XljBearSoft
 * Date: 2017-07-05
 * Time: 15:08
 */

namespace app\admin\controller;


use think\Controller;
use think\Request;
use think\Db;
class User extends Controller
{
    public function login(){
        if(session('?admin_login'))return $this->redirect("admin/index/index");
        $request = Request::instance();
        $this->assign("error_info","");
        if($request->method()=="GET"){
            return $this->fetch();
        }else{
            $geeTest = new \app\index\controller\Geetest();
            if(!$geeTest->VerifyGeeTest()){
                $this->assign("error_info","验证码验证无效!");
                return $this->fetch();
            }
            $username = trim($request->post('username'));
            $password = $request->post('password');
            if($username==""||$password==""){
                $this->assign("error_info","用户名或密码错误!");
                return $this->fetch();
            }else{
                $result = Db::name('admin')->where('username',$username)->find();
                if(!$result){
                    $this->assign("error_info","用户名或密码错误!");
                    return $this->fetch();
                }else{
                    if(GetPasswordMd5($password,$result['salt'])!=$result['password']){
                        $this->assign("error_info","用户名或密码错误!");
                        return $this->fetch();
                    }else{
                        session('admin_login',true);
                        session('admin_id',$result['id']);
                        session('admin_name',$result['username']);
                        $login_update['lastlogin'] = time();
                        $login_update['lastip'] = $request->ip();
                        Db::name('admin')->where('id',$result['id'])->update($login_update);
                        return $this->redirect('admin/index/index');
                    }
                }
            }
        }
    }
    public function logout(){
        session(null);
        return $this->redirect('admin/user/login');
    }
    public function ie(){
        return $this->fetch("index/ie");
    }
}