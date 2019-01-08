<?php

/**
 * File: Page.php
 * User: XljBearSoft
 * Date: 2017-07-14
 * Time: 17:43
 */
namespace app\index\model;
use think\Model;
use WebCache\WebCacheLib as Cache;
class Page extends Model
{
    protected static function init(){
        Page::event("after_insert",function($page){Page::UpdateCache($page);});
        Page::event("after_update",function($page){Page::UpdateCache($page);});
        Page::event("after_delete",function($page){Page::Deleted($page);});
    }
    protected static function Deleted($page){
        Cache::Clear("page_".$page->id);
    }
    protected static function UpdateCache($page){
        UpdateCache("page",$page->id);
    }
}