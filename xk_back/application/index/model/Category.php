<?php
/**
 * File: Category.php
 * User: XljBearSoft
 * Date: 2017-07-29
 * Time: 19:15
 */

namespace app\index\model;


use think\Model;

class Category extends Model
{
    public function Attribute(){
        return $this->hasMany("CategoryAttribute","categoryid");
    }
}