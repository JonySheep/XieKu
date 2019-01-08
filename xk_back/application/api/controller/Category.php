<?php
namespace app\api\controller;
use app\index\controller\CoreController;

use app\index\model\Brand as BrandModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\Category as CategoryModel;
use WebCache\WebCacheLib as Cache;
/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Category extends CoreController
{
    public function index(){

        $CategoryList = CategoryModel::all();


        foreach ($CategoryList as $k=>$category){
            $BrandList = BrandModel::all(function($q) use ($category){$q->where(['category'=>$category['id']])->order("sort asc,id desc");});

            foreach ($BrandList as $key =>$brand){
                $goods = GoodsModel::all(function ($q) use ($brand){
                   $q->where(['brandid'=>$brand['id']])->order("sort asc,id desc");
                });
                $BrandList[$key]['goods'] = $goods;
            }
            $CategoryList[$k]['brands'] = $BrandList;

        }
        $this->showMessage(1,$CategoryList);
    }

    public function products(){
        $CategoryList = CategoryModel::all();
        foreach ($CategoryList as $k=>$category){
            $goods = GoodsModel::all(function ($q) use ($category){
                $q->where(['categoryid'=>$category['id']])->order("sort asc,id desc")->limit(5);
            });
            $CategoryList[$k]['goods'] = $goods;
        }
        $this->showMessage(1,$CategoryList);
    }
}