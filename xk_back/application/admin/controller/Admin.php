<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Menu;

/**
 * 我特么也不知道这玩意咋取这个名字
 */
class Admin extends Controller
{
    public function __construct(){
        parent::__construct();
        if(!session('?admin_login'))$this->redirect("admin/user/login");
        CoreCacheInit();
        //return $this->redirect("admin/index");
    }


    /**
     * [imgUp 图片上传公共类]
     * @Author   yangxiaogang
     * @DateTime 2018-07-20T10:02:30+0800
     * @param    [type]                   $file [description]
     * @return   [type]                         [description]
     */
    protected function imgUp($file)
    {   
        //参数验证
        if( empty($file) )
            return array('code'=>0, 'msg'=>'文件有吗？卧槽！' );

        //文件移动
        $rootdir = ROOT_PATH . 'public' . DS . 'data/images/coupon';
        $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')->move($rootdir);
        if( ! $info )
            return array('code'=>0, 'msg'=>$info->getError() );

        return array('code'=>1, 'msg'=>$info->getSaveName() );
       
    }



    /**
     * [renderError 错误输出]
     * @Author   yangxiaogang
     * @DateTime 2018-07-19T15:21:25+0800
     * @return   [type]                   [description]
     */
    function renderError($msg='',$code = 400)
    {
    	return json(array( 'code'=>$code, 'data'=>(object)array(), 'msg'=>$msg ));
    }


     /**
     * [renderError 正确输出]
     * @Author   yangxiaogang
     * @DateTime 2018-07-19T15:21:25+0800
     * @return   [type]                   [description]
     */
    function renderSuccess($data,$code = 200)
    {
    	return json(array( 'code'=>$code, 'data'=>(object)$data, 'msg'=>'' ));
    }

    /**
     * 添加菜单项目
     * @Author   yangxiaogang
     * @DateTime 2018-07-27T16:24:58+0800
     * @param    [type]                   $parentId   [菜单分类项id]
     * @param    [type]                   $menuId     [父级菜单id]
     * @param    [type]                   $menuName   [节点名称]
     * @param    [type]                   $controller [控制器名称]
     * @param    [type]                   $action     [方法名称]
     */
    function addMenu($parentId,$isShow,$menuName,$controller,$action)
    {   
        $menuData = [
            'parent_id' => $parentId ?: 0,
            'is_show'   => $isShow,
            'menu_name' => $menuName,
            'controller'=> $controller ?: '',
            'action'    => $action ?: ''
        ];
        Menu::create($menuData);
        return true;
    }

    /**
     * 返回指定模块的所有控制器
     * @Author   yangxiaogang
     * @DateTime 2018-07-30T14:24:19+0800
     * @param    [type]                   $dirName [description]
     * @return   [type]                            [description]
     */
    function controllrtArr($dirName)
    {
        $dirArr = scandir($dirName);
        foreach ($dirArr as $key => &$val) {
            $val = explode('.', $val);
        }
        unset($val);
        $data = array_filter( i_array_column( $dirArr,'0')  );
        return array_values($data);
    }

    /**
     * 读取控制器里面的所有方法
     * @Author   yangxiaogang
     * @DateTime 2018-07-30T14:27:13+0800
     * @return   [type]                   [description]
     */
    function getAction($class)
    {
        $namespace = 'app\admin\controller\\';
        $className = $namespace.$class;
        $ref    = new \ReflectionClass($className);
        $ref    = $ref->getMethods();       
        $data = []; //返回结果存放
        foreach ($ref as $key => $value) {
             
            if( $value->class === $className )
            {   
                if( $value->name !== '__construct' )
                {
                    $data[] = $value->name;
                }
            }
        }
        return $data;
    }

}