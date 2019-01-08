<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/21
 * Time: 11:53
 */

namespace app\admin\controller;
use app\admin\model\System as SystemModel;
/**
 * 客服电话
 * @author huanghao
 * Class Integration
 * @package app\admin\controller
 */
class Settings extends Admin
{

    /**
     * @return \think\response\View
     */
    public function tel(){
        if ($this->request->isPost()) {
            $mobile = input('mobile');
            $names = SystemModel::where('')->column('name');
            if (!isset($names['mobile'])) {
                SystemModel::create(['name'=>'mobile']);
            }
            if (SystemModel::where('name','mobile')->update(['value'=>$mobile])) {
                return CreateInfoPage("编辑成功","编辑成功!","返回",url('tel'));
            } else {
                return CreateInfoPage("编辑失败","编辑失败!","返回",url('tel'));
            }
        } else {
            $mobile = SystemModel::where('name','mobile')->value('value');
            $this->assign('mobile', $mobile);
            return view();
        }

    }
}