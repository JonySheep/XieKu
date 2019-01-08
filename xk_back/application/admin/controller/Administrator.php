<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 16:45
 */

namespace app\admin\controller;
use  app\admin\model\Admin as adminModel;
use think\Db;
use think\Request;

use app\admin\model\Adminer;

/**
 * 管理员控制器
 * @author huanghao
 * Class Administrator
 * @package app\admin\controller
 */
class Administrator extends Admin
{
    /**
     * 管理员列表
     */
    public function index(){
        $request = Request::instance();
        //当前所在页面
        $this->assign('NowPage',$nowPage = $request->param('nowPage')?:1);
        //搜索内容
        $this->assign('Keyword',$keyword = $request->param('search')?:'');
        $pageLimit = 15;
        if(session('admin_id') != 1){  //如果不是超级管理员
            $adminInfo = Adminer::get(session('admin_id'));
            $map['shop_id'] = $adminInfo['shop_id'];
        }
        $map['a.id'] = ['gt' ,1];
        $map['is_delete'] = 0;
//        halt($map);
        if($keyword) $map['username'] = ['like', '%'.$keyword.'%'];
        $list = adminModel::where($map)
            ->alias('a')
            ->field('a.*, s.title as store_name')
            ->join('store s','a.shop_id = s.id','LEFT')
            ->limit(($nowPage-1)*$pageLimit, $pageLimit)->select();
        $list = $list ? $list->toArray():[];
        $count = adminModel::where($map)->alias('a')
            ->field('a.*, s.title as store_name')
            ->join('store s','a.shop_id = s.id','LEFT')
            ->count();
//        halt($map);
        $PageCount = $count / $pageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($nowPage>$PageCount)$nowPage = $PageCount;

        $this->assign('list', $list);
        $this->assign('NowPage', $nowPage);
        $this->assign('Keyword', $keyword);
        $this->assign('PageCount',$PageCount);
        $this->assign('adminId',session('admin_id'));

        return view();
    }

    /**
     * 新增管理员
     */
    public function add(){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $request = Request::instance();
            $data['lastlogin'] = time();
            $data['lastip'] = $request->ip();
            $data['salt'] = random(3);
            // 验证
            $result = $this->validate($data, 'Admin');
            $data['password'] = md5($data['salt'].md5($data['password'].$data['salt']).$data['salt']);
            // 验证失败 输出错误信息
            if (true !== $result) return CreateInfoPage("新增失败",$result,"返回",url('add'));;
            $user = adminModel::create($data)->allowField(true);
            if ($user) {
                $shop_id = $user->toArray()['id'];
                Db::name('store')->where('id', $data['shop_id'])->setField('uid', $shop_id);
                return CreateInfoPage("新增成功","新增成功!","返回",url('index'));
            } else {
                return CreateInfoPage("新增失败","新增失败!","返回",url('index'));
            }
        }
//        $storeList = Db::name("store")->where('uid', 0)->order("sort asc,id desc")->select();
        $storeList = Db::name("store")->order("sort asc,id desc")->select();
        $this->assign('storeList', $storeList);
        return view();
    }

    /**
     * 修改管理员
     * @param $id
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function edit($id){
        $info = adminModel::get($id)->toArray();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $request = Request::instance();
            $data['lastlogin'] = time();
            $data['lastip'] = $request->ip();
            // 验证
            $result = $this->validate($data, 'Admin.edit');
            if($data['password'] != '**********'){
                $data['password'] = md5($info['salt'].md5($data['password'].$info['salt']).$info['salt']);
            }else{
                unset($data['password']);
            }
            // 验证失败 输出错误信息
            if (true !== $result) return CreateInfoPage("修改失败",$result,"返回",url('edit?id='.$id));
            $user = adminModel::where('id', $id)->update($data);
            if ($user) {
                Db::name('store')->where('id', $info['shop_id'])->setField('uid', 0);  //还原之前的商铺管理者id
                Db::name('store')->where('id', $data['shop_id'])->setField('uid', $info['id']); //修改新的商铺管理者id
                return CreateInfoPage("修改成功","修改成功!","返回",url('index'));
            } else {
                return CreateInfoPage("修改失败","修改失败!","返回",url('index'));
            }
        }

//        $storeList = Db::name("store")->where('uid = 0 or uid = '.$info['id'])->order("sort asc,id desc")->select();
        $storeList = Db::name("store")->order("sort asc,id desc")->select();
        $this->assign('storeList', $storeList);
        $this->assign('info', $info);
        return view();
    }

    /**
     * 删除管理员
     * @param $id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function delete($id){
        $info = adminModel::get($id)->toArray();
        Db::name('store')->where('id', $info['shop_id'])->setField('uid', 0);  //还原之前的商铺管理者id
        adminModel::where('id', $id)->update(['is_delete' => 1, 'shop_id' => 0]);
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}