<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 16:45
 */

namespace app\admin\controller;
use  app\admin\model\Logistics as LogisticsModel;
use think\Db;
use think\Request;

/**
 * 物流公司管理
 * @author yangle
 * Class Administrator
 * @package app\admin\controller
 */
class Logistics extends Admin
{
    /**
     * 公司列表
     */
    public function index(){
        $request = Request::instance();
        //当前所在页面
        $this->assign('NowPage',$nowPage = $request->param('nowPage')?:1);
        //搜索内容
        $this->assign('Keyword',$keyword = $request->param('search')?:'');
        $pageLimit = 15;
        $map = [];
        if($keyword) $map['name'] = ['like', '%'.$keyword.'%'];
        $list = LogisticsModel::where($map)->limit(($nowPage-1)*$pageLimit, $pageLimit)->select();
        $list = $list ? $list->toArray():[];
        $count = LogisticsModel::where($map)->count();
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

        return view();
    }

    /**
     * 新增公司
     */
    public function add(){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $res = LogisticsModel::create($data)->allowField(true);
            if ($res) {
                return CreateInfoPage("新增成功","新增成功!","返回",url('index'));
            } else {
                return CreateInfoPage("新增失败","新增失败!","返回",url('index'));
            }
        }
        return view();
    }

    /**
     * 修改
     * @param $id
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function edit($id){
        $info = LogisticsModel::get($id)->toArray();
        if ($this->request->isPost()) {
            $data = input();
            $logisticsModel = new LogisticsModel();
            $res = $logisticsModel->isUpdate(true)->save($data);
            if ($res) {
                return CreateInfoPage("编辑成功","编辑成功!","返回",url('index'));
            } else {
                return CreateInfoPage("编辑失败","编辑失败!","返回",url('index'));
            }
        }
        $this->assign('info', $info);
        return view();
    }

    /**
     * 删除物流公司
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id){
        LogisticsModel::where('id', $id)->delete();
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}