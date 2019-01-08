<?php
/**
 * File: Cases.php
 * User: XljBearSoft
 * Date: 2017-07-28
 * Time: 17:30
 */

namespace app\index\model;


use think\Model;

class Cases extends Model
{
    protected static function init(){
        Cases::event("after_insert",function($case){Cases::UpdateCache($case);});
        Cases::event("after_update",function($case){Cases::UpdateCache($case);});
        Cases::event("after_delete",function($case){Cases::Deleted($case);});
    }
    protected static function Deleted($case){
        Cache::Clear("case_cache_".$case->id);
        UpdateCache("case_list",$case);
    }
    protected static function UpdateCache($case){
        UpdateCache("case",$case);
        UpdateCache("case_list",$case);
    }
}