<?php
namespace app\index\controller;

use WebCache\WebCacheLib as Cache;

class Index extends CoreController
{
    public function index()
    {
        if(IsMobile())$this->redirect('m/index/index');
        if(!Cache::Has("banner_list_1")){
            UpdateCache("banner_list",1);
        }
        if(!Cache::Has("article_list_type1")){
            UpdateCache("article_list",1);
        }
        $BannerList = Cache::Get("banner_list_1");
        $ArticleList = Cache::Get("article_list_type1");
        $this->assign("articlelist",array_slice($ArticleList,0,5));
        $this->assign("bannerlist",$BannerList);
        return $this->fetch();
    }
}