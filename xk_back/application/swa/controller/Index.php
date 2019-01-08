<?php
/**
 * File: index.php
 * User: XljBearSoft
 * Date: 2017-07-18
 * Time: 17:31
 */
namespace app\swa\controller;
use app\index\controller\CoreController;

class index extends CoreController
{
    public function index(){
        $this->redirect('swa/context/view','id=1');
    }
}