<?php
/**
 * File: Brand.php
 * User: XljBearSoft
 * Date: 2017-07-12
 * Time: 15:44
 */

namespace app\index\model;


use think\Db;
use think\Model;

class Brand extends Model
{
    protected static function init(){
        Brand::event("after_insert",function($brand){Brand::UpdateCache($brand);});
        Brand::event("after_update",function($brand){Brand::UpdateCache($brand);});
        Brand::event("after_delete",function($brand){Brand::Deleted($brand);});
    }
    protected static function Deleted($brand){
        Db::name('goods')->where('brandid',$brand->id)->update(['brandavailable'=>0]);
        UpdateCache("brand",null);
        UpdateCache("goods_alllist",null);
        UpdateCache("goods_allnew",null);
    }
    protected static function UpdateCache($brand){
        Db::name('goods')->where('brandid',$brand->id)->update(['brandavailable'=>$brand->available]);
        UpdateCache("brand",null);
        UpdateCache("goods_alllist",null);
        UpdateCache("goods_allnew",null);
    }

}