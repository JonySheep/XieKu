<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/10
 * Time: 17:10
 */

namespace app\admin\controller;
use app\admin\model\FeedBack as FeedBackModel;
/**
 * 反馈建议控制器
 * @author huanghao
 * Class FeedBack
 * @package app\admin\controller
 */
class FeedBack extends Admin
{
    public function index(){
        $list = FeedBackModel::alias('FB')->field('FB.*, U.nickName as name')
            ->join('swa_users U','U.id = FB.uid')
            ->select();

        if($list){
            foreach ($list as $key => &$item){
                $item['content_str'] = mb_substr($item['content'],0, 30);
            }
            $list = $list->toArray();
        }

        $this->assign('list', $list);
        return view();
    }

    /**
     * 查看详情
     * @param $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($id){
        FeedBackModel::where('id', $id)->setField('status', 1);
        $info = FeedBackModel::alias('FB')->field('FB.*, U.nickName as name')
            ->join('swa_users U','U.id = FB.uid')
            ->find()->toArray();
        $this->assign('info', $info);
        return view();
    }

    //todo 看一下有没有相关联的也要删掉
    public function delete($id){
        $feedback = FeedBackModel::get($id)->toArray();
        if(!$feedback){
            return CreateInfoPage("很抱歉...","该反馈不存在或已被删除!","返回",url('admin/feedback/index'));
        }else{
            $feedback->delete();
            return CreateInfoPage("操作成功","反馈已成功删除!","返回",url('admin/feedback/index'));
        }
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}