<?php

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-07-21
 * Time: 16:39
 */
namespace app\mall\controller;
use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
class Index extends CoreController
{
    public function index(){
//        if(!Cache::Has("banner_list_2")){
//            UpdateCache("banner_list",2);
//        }
//        if(!Cache::Has("goodslist_allnew_cache")){
//            UpdateCache("goods_allnew",null);
//        }
//        if(!Cache::Has("goods_recommended_cache")){
//            UpdateCache("goods_recommended",null);
//        }
        $BannerList =   \think\Db::name("banner")->where(['typeid'=>2])->order("sort asc,id desc")->select(); //Cache::Get("banner_list_2");

        $NewGoodsList  = \think\Db::name("goods")
            ->where("available = 1 and brandavailable = 1")->order("sort asc,id desc")->limit(0,7)->select();//  Cache::Get("goodslist_allnew_cache");
        $RecommendedGoodsList =   \think\Db::name("goods")->where("recommended = 1 and available = 1 and brandavailable = 1")->order("sort asc,id desc")->limit(0,7)->select();//  Cache::Get("goods_recommended_cache");
        $this->assign("RecommendedGoodsList",$RecommendedGoodsList);
        $this->assign("NewGoodsList",$NewGoodsList);
        $this->assign("bannerlist",$BannerList);
        return $this->fetch();
    }
    public function category($t = 1,$k=""){
        $type = $t;
        $keyword = $k;
        $attributes_bs = input("?get.attr")?input("get.attr"):"";
        $page = input("?get.pg")?intval(input("get.pg")):1;
        $brandid = input("?get.bd")?intval((input("get.bd"))):"";
        if($keyword==""||input("?get.kw")){
            $keyword = input("?get.kw")?input("get.kw"):"";
        }
        if($brandid<0||!HasBrand($brandid))$brandid=0;
        $keyword = trim($keyword);
        $attributes_bs = trim($attributes_bs);
        $attributes_str = base64_decode($attributes_bs);
        $attributes = [];
        if($attributes_str!=""){
            $attributes = json_decode($attributes_str,true);
            if($attributes==null)$attributes=[];
        }

        $attributes_json = json_encode($attributes,JSON_FORCE_OBJECT);
        if($attributes_json=="null"){
            $attributes_json="[]";
        }

        $this->assign("keyword",$keyword);
        $this->assign("attributes_json",$attributes_json);
        $this->assign("attributes_base64",base64_encode($attributes_json));
        $categoryList = Cache::Get("category");
        if(!HasCategory($type))$type = 1;
        $category = GetCategory($type);
        $this->assign("NowBrand",$brandid);
        $this->assign("NowPage",$page);
        $this->assign("nowCategoryId",$type);
        $this->assign("nowCategory",$category);
        $this->assign("categoryList",$categoryList);
        $this->assign("attributeList",$category['attribute']);
        if(!Cache::Has("goodslist_type".$type."_cache"))
            UpdateCache("goods_list",$type);
        $GoodsList = Cache::Get("goodslist_type".$type."_cache");
        $brandList = [];
        foreach ($GoodsList as $key=>$Goods){
            if($keyword!="")if(mb_strpos($Goods['title'],$keyword,0,"utf-8")===false)unset($GoodsList[$key]);
            foreach ($attributes as $attr_key=>$attr){
                if(isset($Goods['attribute'][$attr_key])&&$Goods['attribute'][$attr_key][0]!='0'&&!in_array($attr,$Goods['attribute'][$attr_key])&&$attr!="0"){
                    unset($GoodsList[$key]);
                }
            }
            if(isset($GoodsList[$key])){
                if(!isset($brandList[$GoodsList[$key]['brandid']])){
                    $brandList[$GoodsList[$key]['brandid']] = GetBrand($GoodsList[$key]['brandid']);
                }
            }
            if($Goods['brandid']!=$brandid&&$brandid!=0)unset($GoodsList[$key]);
        }
        $GoodsCount = sizeof($GoodsList);
        $PageLimit = 16;
        $PageCount = $GoodsCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page=$PageCount;
        $GoodsList = array_slice($GoodsList,($page - 1)*$PageLimit,$PageLimit);
        $this->assign('GoodsCount',$GoodsCount);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign("BrandList",$brandList);
        $this->assign("GoodsList",$GoodsList);
        return $this->fetch('list');
    }
    public function product($id){
        if(!Cache::Has("goods_cache_".$id)){
            if(!UpdateCache("goods",$id))return "商品不存在或已被删除!";
        }
        $goods = Cache::Get("goods_cache_".$id);
        $coverList = [];
        for($i=1;$i<=5;$i++){
            if($goods["cover$i"]!=null){
                $coverList[] = $goods["cover$i"];
            }
        }

        $this->assign('cover_list',$coverList);
        $specList = [];
        if($goods['spec']!=""){
            $specLine = explode("\n",$goods['spec']);
            foreach ($specLine as $spec){
                $specList[] = explode("|#|",$spec);
            }
        }
        $this->assign('spec_list',$specList);
        $this->assign('goods',$goods);
        return $this->fetch('product');
    }
    public function brand($t){
        if(!HasCategory($t))$t = 1;
        $nowCategory = GetCategory($t);
        $categoryList = Cache::Get("category");
        $this->assign('nowCategory',$nowCategory);
        $this->assign('categoryList',$categoryList);
        if(!Cache::Has("goodslist_type".$t."_cache"))
            UpdateCache("goods_list",$t);
        $GoodsList = Cache::Get("goodslist_type".$t."_cache");
        $brandList = [];
        foreach ($GoodsList as $key=>$Goods){
            if(!isset($brandList[$GoodsList[$key]['brandid']])){
                $brandList[$GoodsList[$key]['brandid']] = GetBrand($GoodsList[$key]['brandid']);
            }
        }
        $this->assign('brandList',$brandList);
        return $this->fetch();
    }
}