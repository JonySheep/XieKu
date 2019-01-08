<?php
/**
 * File: Category_attr.php
 * User: XljBearSoft
 * Date: 2017-08-11
 * Time: 15:01
 */

namespace app\admin\controller;

use app\index\model\Category;
use think\Db;
use WebCache\WebCacheLib as Cache;
class Categoryattr extends  Admin
{
    public function index(){
        $CategoryList = Cache::Get("category");
        $this->assign("CategoryList",$CategoryList);
        return $this->fetch();
    }
    public function edit($id){
        if(!HasCategory($id))
            return CreateInfoPage("发生错误...","无效的CategoryID!","返回",url('admin/categoryattr/index'));
        $this->assign("category",GetCategory($id));
        return $this->fetch("form");
    }
    public function update(){
        if(request()->method()!="POST")return;
        $cid = intval(input("post.edit_id"));
        $category = Category::get($cid);
        if(!$category){
            return CreateInfoPage("很抱歉...","该系统属性不存在!","返回",url('admin/categoryattr/index'));
        }
        $category_data['cover'] = trim(input("post.cover_address"));
        $category_data['introduce'] = htmlspecialchars_decode(input("post.editorValue"));
        $category->save($category_data);
        UpdateCache("category",null);
        return CreateInfoPage("操作成功","系统属性更新成功!","返回",url('admin/categoryattr/index'));
    }
    public function upload(){
        $file = request()->file('cover_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/category';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?upload_c_cover_old'))@unlink(session('upload_c_cover_old'));
                session('upload_c_cover_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/category/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}