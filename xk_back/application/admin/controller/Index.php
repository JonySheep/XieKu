<?php
namespace app\admin\controller;

use app\index\model\ArticleType;
use app\index\model\PageList;
use app\index\model\System;
use think\Controller;
use WebCache\WebCacheLib as Cache;
use app\admin\model\Order as orderModel;
use app\admin\model\UsersActivityConversion as UsersActivityConversionModel;
use app\admin\model\Adminer;
class Index extends Admin
{
    public function index()
    {
        $categoryList = Cache::Get("category");
        $this->assign('categoryList',$categoryList);
        $PageList = PageList::all();
        $ArticleList = ArticleType::all();
        consoleLog($PageList);
        $systemModel = new System();
        $map = [];
        $viewCount = $systemModel->getPageViews();  //浏览量
        if(session('admin_id') != 1) {  //如果不是超级管理员
            $adminInfo = Adminer::get(session('admin_id'));
            $map['store_id'] = $adminInfo['shop_id'];
        }
        $orderCount = orderModel::where($map)->count();  //订单总量
        $activityOrderCount = UsersActivityConversionModel::count();  //活动订单总量
        $this->assign("ArticleList",$ArticleList);

        $this->assign("PageList",$PageList);
        $this->assign("viewCount",$viewCount);
        $this->assign("orderCount",$orderCount);
        $this->assign("activityOrderCount",$activityOrderCount);
        return $this->fetch();
    }
    public function ie(){
        return $this->fetch();
    }
    public function index_page(){
        
    }
}