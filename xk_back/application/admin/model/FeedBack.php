<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/10
 * Time: 16:45
 */

namespace app\admin\model;


use think\Model;

class FeedBack extends Model
{
    /**
     * 反馈建议模型
     * @author huanghao
     */

    protected $autoWriteTimestamp = 1;
    protected $resultSetType = 'collection';
}