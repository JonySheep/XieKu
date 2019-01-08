<?php
namespace app\api\controller;

use think\Request;
use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
use think\Validate;
use app\admin\model\Brand as BrandModel;
use app\admin\model\Category;
use app\admin\model\Goods;

class Brand extends CoreController
{
    public function  index(){
        $brandList = Cache::Get('brand');
        $this->assign('brandList',$brandList);

        $this->showMessage(1,$brandList);
    }

    /**
     * [getBrand 获取品牌]
     * @author yangxiaogang 2018-07-22
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    function getBrand(Request $request)
    {	
    	//空调分类
    	$cateList  = Category::all();
        
        //结果组装
    	foreach ($cateList as $k => &$v) 
    	{    
            $category = $this->getSort($v->id);
            foreach ($category as $key => $val) {
                $category[$key]['logo'] =  request()->domain().$val['logo'];
            }
            $v->category = $category;
            unset($v);
    	}  
    	
    	return $this->renderSuccess($cateList);   
    }

    /**
     * [getGoods 获取商品]
     * @author yangxiaogang 2018-07-22
     * @param Request $request
     * @return \think\response\Json [type] [description]
     * @throws \think\exception\DbException
     */
    function getGoods(Request $request)
    {
    	$validate = Validate::make(
    		[
    			'brand_id'	=>  "require",
    		],
    		[
    			'brand_id.require' => '商品分类ID必须'
    		]
    	);
    	if( ! $validate->check( $request->param() ) )
    		return $this->renderError($validate->getError());
    	//获取商品
    	$goodsList = Goods::all(function($query)use($request){
    	    $sort = $request->param('sort') ? : 0;
    	    $sort_type = ['sort','id','price','sale'];
    	    $sort = $sort_type[$sort];
    		$query->where([
    			'available'	=> 1,
    			'brandid'	=> $request->param('brand_id'),
                'title'     => ['like','%'.$request->param('keywords').'%']
    		])->order($sort,$request->param('asc') == 1 ? 'asc' : 'desc');
    	});

    	return $this->renderSuccess($goodsList);
    }

    function getSort($t)
    {
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
        return $brandList;
    }








}