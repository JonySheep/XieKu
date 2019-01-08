<?php
namespace app\m\controller;


use app\index\controller\CoreController;

class Swa extends CoreController
{
    public function index(){
        $this->redirect('m/page/view',['id'=>1]);
    }

}