<?php
/**
 * File: Info.php
 * User: XljBearSoft
 * Date: 2017-07-06
 * Time: 15:43
 */

namespace app\admin\controller;


use think\Controller;

class Info extends Controller
{
    public function Info_page($title,$info,$button,$back){
        $this->assign("title",$title);
        $this->assign("info",$info);
        $this->assign("button",$button);
        $this->assign("back",$back);
        return $this->fetch("info/info");
    }
}