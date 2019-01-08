<?php
/**
 * File: Banner.php
 * User: XljBearSoft
 * Date: 2017-07-17
 * Time: 15:32
 */

namespace app\admin\controller;

use app\index\model\Banner as BannerModel;
class Banner extends Admin
{
    public function index($type){
        $bannerList = BannerModel::all(function($q) use ($type) {$q->where(['typeid'=>$type])->order("sort asc,id desc");});
        $this->assign('bannerlist',$bannerList);
        $this->assign('banner_type_id',$type);
        return $this->fetch();
    }
    public function add($type){
        $this->assign('banner_type_id',$type);
        return $this->fetch('form');
    }
    public function delete($type,$id){
        $banner = BannerModel::get($id);
        if(!$banner){
            return CreateInfoPage("很抱歉...","该Banner不存在或已被删除!","返回",url('admin/banner/index',['type'=>$type]));
        }else{
            $rootdir = ROOT_PATH . 'Public' . DS;
            @unlink($rootdir.$banner->banneraddress);
            $banner->delete();
            return CreateInfoPage("操作成功","Banner已成功删除!","返回",url('admin/banner/index',['type'=>$type]));
        }
    }
    public function update()
    {
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $banner_data['banneraddress'] = trim(input("post.banneraddress"));
        if($banner_data['banneraddress']==""){
            return CreateInfoPage("发生错误...","请先上传Banner图像!","返回","javascript:window.history.back();");
        }
        $banner_data['link'] = trim(input("post.link"));
        $banner_data['sort'] = intval(input("post.sort"));
        $banner_data['typeid'] = intval(input("post.type"));
        $type = intval(input("post.type"));
        session("upload_banner_old",null);
        if(input("?post.edit_id")){
            $banner_id = intval(input("post.edit_id"));
            $banner = BannerModel::get($banner_id);
            if(!$banner){
                return CreateInfoPage("很抱歉...","该Banner不存在或已被删除!","返回",url('admin/banner/index',['type'=>$type]));
            }
            $rootdir = ROOT_PATH . 'public' . DS;
            if($banner->banneraddress!=$banner_data['banneraddress'])@unlink($rootdir.$banner->banneraddress);
            $banner->save($banner_data);
            return CreateInfoPage("操作成功","Banner信息修改成功!","返回",url('admin/banner/index',['type'=>$type]));
        }else{
            $newBanner = new BannerModel();
            $newBanner->save($banner_data);
            return CreateInfoPage("操作成功","Banner已成功添加!","返回",url('admin/banner/index',['type'=>$type]));
        }
    }
    public function edit($type,$id){
        $banner = BannerModel::get($id);
        if(!$banner) {
            return CreateInfoPage("很抱歉...","该Banner不存在或已被删除!","返回",url('admin/banner/index',['type'=>$type]));
        }
        $this->assign('banner_type_id',$type);
        $this->assign('banner',$banner);
        $this->assign('editmode',true);
        return $this->fetch('form');
    }
    public function upload(){
        $file = request()->file('banner_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/banner';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?upload_banner_old'))@unlink(session('upload_banner_old'));
                session('upload_banner_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/banner/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}