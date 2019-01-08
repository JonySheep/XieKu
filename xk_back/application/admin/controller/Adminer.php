<?php
/**
 * File: Adminer.php
 * User: XljBearSoft
 * Date: 2017-09-20
 * Time: 16:02
 */

namespace app\admin\controller;


use think\Db;
use think\Request;

class Adminer extends Admin
{
    function resetpwd(){
        return $this->fetch('form');
    }
    function update(){
        if (!Request::instance()->isPost())exit();
        $admin_name = trim(input('post.admin_n'));
        $old_password = input('post.old_p');
        $new_password = input('post.new_p');
        $new_password2 = input('post.new_p2');
        $admin_id = session("admin_id");
        if($admin_name==""||$old_password==""||$new_password==""||$new_password2==""){
            return CreateInfoPage("发生错误...","信息填写不完整!","返回","javascript:window.history.back();");
        }
        if(strlen($new_password)<6){
            return CreateInfoPage("发生错误...","密码长度需至少6位!","返回","javascript:window.history.back();");
        }
        if($new_password!=$new_password2){
            return CreateInfoPage("发生错误...","重复密码错误!","返回","javascript:window.history.back();");
        }
        $admin_info = Db::name("admin")->where(['id'=>$admin_id])->find();
        $data['password'] = GetPasswordMd5($new_password,$admin_info['salt']);
        if(GetPasswordMd5($old_password,$admin_info['salt'])!=$admin_info['password']){
            return CreateInfoPage("发生错误...","旧密码错误!","返回","javascript:window.history.back();");
        }
        if($admin_info['username'] != $admin_name){
            $data['username'] = $admin_name;
            if(Db::name("admin")->where(['username'=>$admin_name])->count()>0){
                return CreateInfoPage("发生错误...","该用户名已存在!","返回","javascript:window.history.back();");
            }
        }
        Db::name("admin")->where(['id'=>$admin_id])->update($data);
        session(null);
        return CreateInfoPage("修改成功","信息已完成修改,请重新登录!","登录",url('admin/user/login'));
    }
}