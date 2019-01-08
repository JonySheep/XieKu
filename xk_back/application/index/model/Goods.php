<?php
/**
 * Created by PhpStorm.
 * User: XljBearSoft
 * Date: 2017/7/31
 * Time: 6:13
 */

namespace app\index\model;


use think\Model;
use WebCache\WebCacheLib as Cache;

class Goods extends Model
{
    protected static function init(){
        Goods::event("after_insert",function($goods){Goods::UpdateCache($goods);});
        Goods::event("after_update",function($goods){Goods::UpdateCache($goods);});
        Goods::event("after_delete",function($goods){Goods::Deleted($goods);});
    }
    public function Attribute(){
        return $this->hasMany("GoodsAttribute","goodsid");
    }
    protected static function Deleted($goods){
        Cache::Clear("goods_cache_".$goods->id);
        UpdateCache("goods_list",$goods->categoryid);
        UpdateCache("goods_allnew",null);
        UpdateCache("goods_recommended",null);
    }
    protected static function UpdateCache($goods){
        UpdateCache("goods",$goods->id);
        UpdateCache("goods_allnew",null);
        UpdateCache("goods_recommended",null);
    }
}