<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 17:11
 */

namespace app\admin\model;


use think\Model;

class Order extends Model
{
    protected $autoWriteTimestamp = true;
    protected $resultSetType = 'collection';
}