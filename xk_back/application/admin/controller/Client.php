<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 17:18
 */

namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\model\Users as usersModel;
use app\admin\model\Admin as AdminUserModel;
use app\admin\model\Order as OrderModel;
use think\Db;
use think\Request;

/**
 * 前台用户控制器
 * @author huanghao
 * Class Partner
 * @package app\admin\controller
 */
class Client  extends Admin
{
    /**
     * 用户列表
     */
    public function index(){
        $request = Request::instance();
        //当前所在页面
        $this->assign('NowPage',$nowPage = $request->param('nowPage')?:1);
        //搜索内容
        $this->assign('Keyword',$keyword = $request->param(
            'search')?:'');
        $pageLimit = 15;
        $map = [];
        if($keyword) $map['mobile'] = ['like', '%'.$keyword.'%'];
        $admin_id = session('admin_id');
        if ($admin_id > 1) {
            $shop_id = AdminUserModel::where('id',$admin_id)->value('shop_id');
            $uids = OrderModel::where('store_id',$shop_id)->group('uid')->column('uid');
            if ($uids) $map['id'] = ['in',$uids];
        }
        $map['del_status'] = 0;
        $list = usersModel::where($map)->limit(($nowPage-1)*$pageLimit, $pageLimit)->select();
        $list = $list ? $list->toArray():[];
        $count = usersModel::where($map)->count();
        $PageCount = $count / $pageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($nowPage>$PageCount)$nowPage = $PageCount;

        $this->assign('list', $list);
        $this->assign('NowPage', $nowPage);
        $this->assign('Keyword', $keyword);
        $this->assign('PageCount',$PageCount);

        return view();
    }

    /**
     * 修改用户状态
     * @param $uid
     * @param $status
     * @return mixed
     */
    public function set_status($uid ,$status){
        usersModel::where('id', $uid)->setField('status', $status);
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }

    /**
     * 删除会员
     * @param int $uid
     * @return mixed
     */
    //todo 这个是干嘛的？？？
    public function delete($uid = 0)
    {
        usersModel::where('id', $uid)->setField('del_status', 1);
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}