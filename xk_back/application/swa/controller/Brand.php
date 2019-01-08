<?php
/**
 * File: Brand.php
 * User: XljBearSoft
 * Date: 2017-07-24
 * Time: 16:16
 */

namespace app\swa\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;

class Brand extends CoreController
{
    public function index(){
        $brandList = Cache::Get('brand');
        $this->assign('brandList',$brandList);
        $categoryList = Cache::Get('category2');
        $this->assign('categoryList',$categoryList);
        $brandhtml = $this->fetch();
        $this->assign('type_link',url("swa/brand/index"));
        $this->assign('type_name',"合作品牌");
        $this->assign('subpage_name',"合作品牌");
        $this->assign('page_name',"合作品牌");
        $this->assign('page_content',$brandhtml);
        return $this->fetch('Public/frame');
    }
}