<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 17:39
 */
namespace app\admin\validate;

use think\Db;
use think\Validate;
/**
 * 管理员验证器
 * @author huanghao
 * Class Admin
 */
class Admin extends Validate
{
    protected $rule = [
        'username|用户名' => 'require|checkRepeat',
        'password|密码' => 'require|length:6,16',
        'shop_id|商铺' => 'require',
    ];

    protected $message = [
        'username.require' => '请输入用户名',
        'username.checkRepeat' => '用户名已存在',
        'password.require' => '请输入密码',
        'password.length' => '密码必须是6-16位',
        'shop_id.require' => '请给管理员设置店铺',
    ];


    protected $scene = [
        'add' => ['user_name', 'password', 'shop_id'],
        'edit' => ['password', 'shop_id'],
    ];

    /**
     * 用户名验证重复
     * @param $value
     * @return bool
     */
    protected function checkRepeat($value)
    {
        $info = Db::name('Admin')->where('username',$value)->value('id');
        if ($info) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检测商铺id是否重复
     */
    protected function checkShopId($value){
        $info = Db::name('Admin')->where('shop_id',$value)->value('id');
        if ($info) {
            return false;
        } else {
            return true;
        }
    }


}