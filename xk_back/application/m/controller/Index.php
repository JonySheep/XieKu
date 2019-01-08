<?php
namespace app\m\controller;
use app\index\controller\CoreController;

/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Index extends CoreController
{
    public function index(){
        return $this->fetch();
    }
}