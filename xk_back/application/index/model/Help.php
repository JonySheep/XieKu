<?php

namespace app\index\model;


use think\Model;
use WebCache\WebCacheLib as Cache;

class Help extends Model
{
    protected static function init(){
        Help::event("after_insert",function($help){Help::UpdateCache($help);});
        Help::event("after_update",function($help){Help::UpdateCache($help);});
        Help::event("after_delete",function($help){Help::UpdateCache($help);});
    }
    protected static function UpdateCache($help){
        UpdateCache("help_list",$help);
    }
}