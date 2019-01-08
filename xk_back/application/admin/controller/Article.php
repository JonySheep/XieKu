<?php
/**
 * File: Article.php
 * User: XljBearSoft
 * Date: 2017-07-24
 * Time: 13:35
 */

namespace app\admin\controller;
use \app\index\model\Article as ArticleModel;
use app\index\model\ArticleType;
use think\Db;

class Article extends Admin
{
    public function index($type){
        $page = input('?post.page')?intval(input('post.page')):1;
        $keyword = input('?post.search')?trim(input('post.search')):"";
        if($page<1)$page = 1;
        $whereCondition['typeid'] = $type;
        $whereCondition['deleted'] = 0;
        if($keyword!=""){
            $whereCondition['title'] = ['like',"%$keyword%"];
        }
        $ArticleCount =  Db::name("article")->where($whereCondition)->count();
        $PageLimit = 15;
        $PageCount = $ArticleCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $ArticleList = Db::name("article")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        $ArticleType = ArticleType::get($type);
        if($ArticleType == null)return;
        $this->assign('typeid',$type);
        $this->assign('typename',$ArticleType->name);
        $this->assign('articlelist',$ArticleList);
        $this->assign('ArticleCount',$ArticleCount);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign('Keyword',$keyword);
        return $this->fetch();
    }
    public function add($type){
        $this->assign('typeid',$type);
        return $this->fetch('form');
    }
    public function edit($type,$id){
        $article = ArticleModel::get($id);
        if(!$article) {
            return CreateInfoPage("很抱歉...","该文章不存在或已被删除!","返回",url('admin/article/index',['type'=>$type]));
        }
        $this->assign('typeid',$type);
        $this->assign('article',$article);
        $this->assign('editmode',true);
        return $this->fetch('form');
    }
    public function delete($type,$id){
        $article = ArticleModel::get($id);
        if(!$article){
            return CreateInfoPage("很抱歉...","该文章不存在或已被删除!","返回",url('admin/article/index',['type'=>$type]));
        }else{
            $rootdir = ROOT_PATH . 'public' . DS;
            @unlink($rootdir.$article->cover);
            $article->delete();
            return CreateInfoPage("操作成功","文章已成功删除!","返回",url('admin/article/index',['type'=>$type]));
        }
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $article_data['cover'] = trim(input("post.coveraddress"));
        if($article_data['cover']==""){
            return CreateInfoPage("发生错误...","请先上传封面图像!","返回","javascript:window.history.back();");
        }
        $article_data['title'] = trim(input("post.title"));
        if($article_data['title']==""){
            return CreateInfoPage("发生错误...","请输入文章标题!","返回","javascript:window.history.back();");
        }
        $article_data['user'] = trim(input("post.user"));
        if($article_data['user']=="")$article_data['user'] = session('admin_name');
        $article_data['sort'] = intval(input("post.sort"));
        $article_data['body'] = htmlspecialchars_decode(input("post.editorValue"));
        $article_data['describe'] = strip_tags($article_data['body']);
        $article_data['describe'] = htmlspecialchars_decode($article_data['describe']);
        $article_data['describe'] = str_replace("&nbsp;","",$article_data['describe']);
        $article_data['describe'] = str_replace("　","",$article_data['describe']);
        if(mb_strlen($article_data['describe'],"utf-8")>150){
            $article_data['describe'] = mb_substr($article_data['describe'],0,150,"utf-8")."...";
        }
        $article_data['updatetime'] = trim(input("post.updatetime"));
        if($article_data['updatetime']==""){
            $article_data['updatetime'] = date("Y-m-d",time());
        }
        $type = intval(input("post.type"));
        $article_data['typeid'] = $type;
        session("upload_article_cover_old",null);
        if(input("?post.edit_id")){
            $article_id = intval(input("post.edit_id"));
            $article = ArticleModel::get($article_id);
            if(!$article){
                return CreateInfoPage("很抱歉...","该文章不存在或已被删除!","返回",url('admin/article/index',['type'=>$type]));
            }
            $rootdir = ROOT_PATH . 'public' . DS;
            if($article->cover!=$article_data['cover'])@unlink($rootdir.$article->cover);
            $article->save($article_data);
            return CreateInfoPage("操作成功","文章信息修改成功!","返回",url('admin/article/index',['type'=>$type]));
        }else{
            $newArticle = new ArticleModel();
            $article_data['publishtime'] = date("Y-m-d",time());
            $newArticle->save($article_data);
            return CreateInfoPage("操作成功","文章已成功添加!","返回",url('admin/article/index',['type'=>$type]));
        }
    }
    public function upload(){
        $file = request()->file('cover_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/article';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?upload_article_cover_old'))@unlink(session('upload_article_cover_old'));
                session('upload_article_cover_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/article/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
    public function trash(){
        return "回收站";
    }
}