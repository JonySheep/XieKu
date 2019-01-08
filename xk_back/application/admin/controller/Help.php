<?php
namespace app\admin\controller;
use \app\index\model\Help as HelpModel;
use think\Db;
use WebCache\WebCacheLib as Cache;
class Help extends Admin
{
    public function index($id){
        if(!HasCategory($id)){
            return CreateInfoPage("很抱歉...","该系统分类不存在或已被删除!","关闭","javascript:CloseTab();");
        }
        $whereCondition['categoryid'] = $id;
        $HelpCount =  Db::name("help")->where($whereCondition)->count();
        $HelpList = Db::name("help")->where($whereCondition)->order("sort asc,id desc")->select();
        $CategoryType = GetCategory($id);
        $this->assign('categoryid',$id);
        $this->assign('typename',$CategoryType);
        $this->assign('helplist',$HelpList);
        $this->assign('HelpCount',$HelpCount);
        return $this->fetch();
    }
    public function add($type){
        $this->assign('typeid',$type);
        return $this->fetch('form');
    }
    public function edit($type,$id){
        $help = HelpModel::get($id);
        if(!$help) {
            return CreateInfoPage("很抱歉...","该文档不存在或已被删除!","返回",url('admin/help/index',['id'=>$type]));
        }
        $this->assign('typeid',$type);
        $this->assign('help',$help);
        $this->assign('editmode',true);
        return $this->fetch('form');
    }
    public function delete($typeid,$id){
        $help = HelpModel::get($id);
        if(!$help){
            return CreateInfoPage("很抱歉...","该文档不存在或已被删除!","返回",url('admin/help/index',['id'=>$typeid]));
        }else{
            $help->delete();
            return CreateInfoPage("操作成功","文档已成功删除!","返回",url('admin/help/index',['id'=>$typeid]));
        }
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $help_data['title'] = trim(input("post.title"));
        if($help_data['title']==""){
            return CreateInfoPage("发生错误...","请输入文档标题!","返回","javascript:window.history.back();");
        }
        $help_data['sort'] = intval(input("post.sort"));
        $help_data['body'] = htmlspecialchars_decode(input("post.editorValue"));
        $type = intval(input("post.type"));
        $help_data['categoryid'] = $type;
        if(input("?post.edit_id")){
            $help_id = intval(input("post.edit_id"));
            $help = HelpModel::get($help_id);
            if(!$help){
                return CreateInfoPage("很抱歉...","该文档不存在或已被删除!","返回",url('admin/help/index',['id'=>$type]));
            }
            $help->save($help_data);
            return CreateInfoPage("操作成功","文档信息修改成功!","返回",url('admin/help/index',['id'=>$type]));
        }else{
            $newHelp = new HelpModel();
            $newHelp->save($help_data);
            return CreateInfoPage("操作成功","文档已成功添加!","返回",url('admin/help/index',['id'=>$type]));
        }
    }
    public function trash(){
        return "回收站";
    }
}