<?php
/**
 * File: Store.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 17:20
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
class Store extends CoreController
{
    public function index(){
        $regionid = input('?get.r')?intval(input('get.r')):0;
        if(!HasRegion($regionid))$regionid = 0;
        $regionList = Cache::Get('region');
        if(!Cache::Has('store_cache'))UpdateCache('store',null);
        $storeList = Cache::Get('store_cache');
        foreach ($storeList as $key=>$store){
            if(($store['regionid']!=$regionid&&$regionid!=0)){
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
        $this->assign('storeList',$storeList);
        $this->assign('regionList',$regionList);
        return $this->fetch();
    }
}