<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/21
 * Time: 16:37
 */

namespace app\admin\model;


use think\Model;

/**
 * 积分规则模型
 * @author huanghao
 * Class IntegralRule
 * @package app\admin\model
 */
class IntegralRule extends Model
{
    protected $resultSetType = 'collection';

    /**
     * 获取某分类下的房型列表
     */
    public  static  function getCateList($cate_id){
        $list = self::where('cate_id', $cate_id)->select();
        return $list ? $list->toArray() : [];
    }
}