<?php
namespace app\index\controller;

use think\Request;
use think\Controller;
use WebCache\WebCacheLib as Cache;
use app\admin\model\City;


class CoreController extends Controller
{
    public function __construct(){
        parent::__construct();
        CoreCacheInit();
    }


    public function showMessage($code = 1,$data=[],$errno =''){

        echo json_encode(['code'=>$code,'data'=>$data,'err'=>$errno]);exit();
    }


    /**
     * [renderError 错误输出]
     * @Author   yangxiaogang
     * @DateTime 2018-07-19T15:21:25+0800
     * @return   [type]                   [description]
     */
    function renderError($msg='',$code = 400)
    {
    	return json(array( 'code'=>$code, 'data'=>(object)array(), 'err'=>$msg ));
    }


     /**
     * [renderError 正确输出]
     * @Author   yangxiaogang
     * @DateTime 2018-07-19T15:21:25+0800
     * @return   [type]                   [description]
     */
    function renderSuccess($data,$code = 200)
    {
    	return json(array( 'code'=>$code, 'data'=>$data, 'err'=>'' ));
    }

    function getWechatUserPhone()
    {

    }

    /**
     * 获取用户省市区
     * @Author   yangxiaogang
     * @DateTime 2018-08-16T15:39:24+0800
     * @return   [type]                   [description]
     */
    function getPCR($cityId)
    {   
        //区
        $range = City::find($cityId);
        //市区
        $city  = City::find($range->pid);
        //省
        $pro   = City::find($city->pid);

        return $pro->name.'-'.$city->name.'-'.$range->name;
    }


}