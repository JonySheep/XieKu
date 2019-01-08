<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/26
 * Time: 10:38
 */

namespace app\index\model;
use think\Model;


/**
 * 系统模型
 * @author huanghao
 * Class PageViews
 * @package app\index\model
 */
class System extends Model
{
    /**
     * 增加浏览量
     */
    public function addPageViews(){
        return self::where('id', 1)->setInc('value');
    }

    /**
     * 获取浏览量
     */
    public function getPageViews(){
        return self::where('id', 1)->value('value');
    }
}