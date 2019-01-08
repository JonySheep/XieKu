<?php
namespace app\api\controller;
use app\index\controller\CoreController;

use app\index\model\Banner as BannerModel;

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Index extends CoreController
{
    public function index(){
        $type = $_GET['type']?:1;
        $bannerList = BannerModel::all(function($q) use ($type) {$q->where(['typeid'=>$type])->order("sort asc,id desc");});

        var_dump($bannerList);exit;
        $rs = userLike::get($like['id']);
        $rs->delete();

    }
}