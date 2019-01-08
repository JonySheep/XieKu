<?php
/**
 * File: Page.php
 * User: Admin
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\api\controller;


use app\index\controller\CoreController;
use app\admin\model\System as SystemModel;


/**
 * 配置
 * Class Settings
 * @package app\m\controller
 */
class Settings extends CoreController
{
    protected $model = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = new SystemModel();
    }

    /**
     * 客服电话
     * @return \think\response\Json
     */
    public function getMobile()
    {
        $mobile = SystemModel::where('name','mobile')->value('value');
        return $this->renderSuccess(['mobile'=>$mobile]);
    }
}