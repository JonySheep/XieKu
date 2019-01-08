<?php
/**
 * File: Brand.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 14:39
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
class Brand extends CoreController
{
    public function  index(){
        $brandList = Cache::Get('brand');
        $this->assign('brandList',$brandList);
        $categoryList = Cache::Get('category2');
        $this->assign('categoryList',$categoryList);

        $this->assign('type_link',url("m/brand/index"));
        return $this->fetch();
    }
}