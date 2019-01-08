<?php

/**
 * File: Adminer.php
 * User: XljBearSoft
 * Date: 2017-07-17
 * Time: 14:56
 */
namespace app\index\model;
use think\Cache;
use think\Model;

class Banner extends Model
{
    protected static function init(){
        Banner::event("after_insert",function($banner){Banner::UpdateCache($banner);});
        Banner::event("after_update",function($banner){Banner::UpdateCache($banner);});
        Banner::event("after_delete",function($banner){Banner::UpdateCache($banner);});
    }
    protected static function UpdateCache($banner){
        $BannerList = Banner::all(function($q) use ($banner){$q->where(['typeid'=>$banner->typeid])->order("sort asc,id desc");});
        Cache::set("banner_list_".$banner->typeid,serialize($BannerList),0);
    }
}