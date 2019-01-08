<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/21
 * Time: 11:53
 */

namespace app\admin\controller;
use app\admin\model\IntegralRule as IntegralRuleModel;

/**
 * 积分控制器
 * @author huanghao
 * Class Integration
 * @package app\admin\controller
 */
class Integration extends Admin
{

    /**
     * 积分规则
     */
    public function index(){
        $list = IntegralRuleModel::where('cate_id',0)->select()->toArray();
        $this->assign('list', $list);
        return view();
    }

    /**
     * 设置积分
     * @param $id
     * @param $integral
     * @return mixed
     */
    public function set_integral($id, $integral){
        IntegralRuleModel::where('id', $id)->setField('integral', $integral);
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}