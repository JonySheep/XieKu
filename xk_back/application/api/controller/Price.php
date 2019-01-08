<?php
/**
 * File: Price.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 16:24
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
class Price extends CoreController
{
    public function index(){
        $modelList = Cache::Get("model");
        $regionList = Cache::Get("region");
        $categoryList = Cache::Get("category");
        $this->assign('categoryList',$categoryList);
        $this->assign("modelList",$modelList);
        $this->assign("regionList",$regionList);
        return $this->fetch();
    }
}