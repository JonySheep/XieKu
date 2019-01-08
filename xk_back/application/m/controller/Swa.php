<?php
/**
 * File: Swa.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:48
 */

namespace app\m\controller;


use app\index\controller\CoreController;

class Swa extends CoreController
{
    public function index(){
        $this->redirect('m/page/view',['id'=>1]);
    }
}