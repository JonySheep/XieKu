<?php
namespace app\store\controller;
use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-02
 * Time: 2:46
 */
class Index extends CoreController
{
    public function index(){
        $regionid = input('?get.r')?intval(input('get.r')):0;
        $keyword = input('?get.k')?trim(input('get.k')):"";
        if(!HasRegion($regionid))$regionid = 0;
        $regionList = Cache::Get('region');
        if(!Cache::Has('store_cache'))UpdateCache('store',null);
        $storeList = Cache::Get('store_cache');
        foreach ($storeList as $key=>$store){
            if(($store['regionid']!=$regionid&&$regionid!=0)||($keyword!=''&&mb_strpos($store['title'],$keyword)===false)){
                unset($storeList[$key]);
            }
            if(isset($storeList[$key])){
                $coverList  = [];
                for($i=0;$i<3;$i++){
                    if($store['cover'.($i+1)]!=null){
                        $coverList[] = $store['cover'.($i+1)];
                    }
                }
                $storeList[$key]['coverlist'] = $coverList;
            }
        }
        $this->assign('regionid',$regionid);
        $this->assign('keyword',$keyword);
        $this->assign('storeList',$storeList);
        $this->assign('regionList',$regionList);
        return $this->fetch();
    }
}