<?php
/**
 * File: Store.php
 * User: XljBearSoft
 * Date: 2017-08-02
 * Time: 9:25
 */

namespace app\index\model;


use think\Model;

class Store extends Model
{
    protected $resultSetType = 'collection';

    protected static function init(){
        Store::event("after_insert",function(){Store::UpdateCache();});
        Store::event("after_update",function(){Store::UpdateCache();});
        Store::event("after_delete",function(){Store::UpdateCache();});
    }
    protected static function UpdateCache(){
        UpdateCache("store",null);
    }
}