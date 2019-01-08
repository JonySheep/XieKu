<?php
/**
 * File: Brand.php
 * User: XljBearSoft
 * Date: 2017-07-05
 * Time: 15:01
 */
namespace app\admin\controller;

use app\index\model\Brand as BrandModel;
use WebCache\WebCacheLib as Cache;


class Brand extends Admin
{
    public function index()
    {
        $BrandList = BrandModel::all(function($q){$q->order("sort asc,id desc");});
        //$BrandList = Db::name("brand")->order("sort asc,id desc")->select();
        $this->assign("brandlist",$BrandList);
        return $this->fetch();
    }
    public function edit($id){
        $brand = BrandModel::get($id);
        if(!$brand) {
            return CreateInfoPage("很抱歉...","该品牌不存在或已被删除!","返回",url('admin/brand/index'));
        }
        $categorys = [];
        $category = explode(",",$brand->category);
        if(sizeof($category)>0){
            foreach ($category as $c){
                if(HasCategory($c))
                    $categorys[] = $c;
            }
        }
        $categoryList = Cache::Get("category2");
        $this->assign('categoryList',$categoryList);
        $this->assign('categorys',$categorys);
        $this->assign('brand',$brand);
        $this->assign('editmode',true);
        return $this->fetch('form');
    }
    public function update(){
        if(request()->method()!="POST")return;
        $brand_data['name'] = trim(input("post.name"));
        if($brand_data['name']==""){
            return CreateInfoPage("发生错误...","品牌名称不能为空!","返回","javascript:window.history.back();");
        }
        $brand_data['logo'] = trim(input("post.logo_address"));
        $brand_data['phrase'] = trim(input("post.phrase"));
        $brand_data['sort'] = intval(input("post.sort"));
        $brand_data['available'] = input("?post.available")||$brand_data['logo']==''?0:1;
        $brand_data['category'] = trim(input('post.category'));
        session("upload_logo_old",null);
        if(input("?post.edit_id")){
            $brand_id = intval(input("post.edit_id"));
            $brand = BrandModel::get($brand_id);
            if(!$brand){
                return CreateInfoPage("很抱歉...","该品牌不存在或已被删除!","返回",url('admin/brand/index'));
            }
            $rootdir = ROOT_PATH . 'public' . DS;
            if($brand->logo!=$brand_data['logo'])@unlink($rootdir.$brand->logo);
            $brand->save($brand_data);
            return CreateInfoPage("操作成功","{$brand['name']}品牌信息修改成功!","返回",url('admin/brand/index'));
        }else{
            $newBrand = new BrandModel();
            $newBrand->save($brand_data);
            return CreateInfoPage("操作成功","{$brand_data['name']}品牌已成功添加!","返回",url('admin/brand/index'));
        }
    }
    public function delete($id){
        $brand = BrandModel::get($id);
        if(!$brand){
            return CreateInfoPage("很抱歉...","该品牌不存在或已被删除!","返回",url('admin/brand/index'));
        }else{
            $rootdir = ROOT_PATH . 'public' . DS;
            @unlink($rootdir.$brand->logo);
            $brand->delete();
            return CreateInfoPage("操作成功","{$brand['name']}品牌已成功删除!","返回",url('admin/brand/index'));
        }
    }
    public function add(){
        $categoryList = Cache::Get("category2");
        $this->assign('categoryList',$categoryList);
        return $this->fetch('form');
    }
    public function upload(){
        $file = request()->file('logo_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/brand';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?upload_logo_old'))@unlink(session('upload_logo_old'));
                session('upload_logo_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/brand/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}