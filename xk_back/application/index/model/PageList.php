<?php

/**
 * File: Page.php
 * User: XljBearSoft
 * Date: 2017-07-14
 * Time: 17:41
 */
namespace app\index\model;
use think\Model;

class PageList extends Model
{
    protected $name = "pagelist";
    public function Page(){
        return $this->hasOne("page","pageid");
    }
}