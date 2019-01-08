<?php
/**
 * File: Article.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 14:56
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use app\index\model\ArticleType;
use think\Db;
use app\index\model\Article as ArticleModel;
use WebCache\WebCacheLib as Cache;
class Article extends CoreController
{
    public function index($type){
        $type_name = ArticleType::get($type);
        if($type_name==null)return "分类不存在!";
        $page = input('?get.page')?intval(input('get.page')):1;
        if($page<1)$page=1;
        $whereCondition['typeid'] = $type;
        $whereCondition['deleted'] = 0;
        $ArticleCount =  Db::name("article")->where($whereCondition)->count();
        $PageLimit = 6;
        $PageCount = $ArticleCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0) {
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page=$PageCount;
        $ArticleList = Db::name("article")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        $this->assign('type_link',url("m/article/index",['type'=>$type]));
        $this->assign('article_typename',$type_name->name);
        $this->assign('articlelist',$ArticleList);
        $this->assign('ArticleCount',$ArticleCount);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign('typeid',$type);
        return $this->fetch('list');
    }
    public function view($id){
        if(!Cache::Has("article_cache_$id")){
            $article = ArticleModel::get($id);
            if($article == null) {
                return "文章已被删除!";
            }
            UpdateCache("article",$article);
        }else{
            $article = Cache::Get("article_cache_$id");
        }
        $typeid = $article['typeid'];
        $this->ArticleClick($typeid,$id);
        $Next = null;
        $Before = null;
        $key = "article_list_type{$typeid}";
        if(!Cache::Has($key)){
            UpdateCache("article_list",$article);
        }
        $ArticleCacheList = Cache::Get($key);
        $result = $this->GetAroundArticle($ArticleCacheList,$article['id']);
        $Before = $result[0];
        $Next = $result[1];
        $this->assign("Next",$Next);
        $this->assign("Before",$Before);
        $this->assign('type_link',url("m/article/index",['type'=>$typeid]));
        $this->assign("typeid",$typeid);
        $this->assign("article_typename",$article['typename']);
        $this->assign("article",$article);
        return $this->fetch('view');
    }
    private function GetAroundArticle($ArticleCacheList,$id){
        $Next = null;
        $Before = null;
        foreach ($ArticleCacheList as $key=>$Article){
            if($Article[0]==$id){
                if($key>0){
                    $Next = $ArticleCacheList[$key-1];
                }
                if($key+1<sizeof($ArticleCacheList)){
                    $Before = $ArticleCacheList[$key+1];
                }
                break;
            }
        }
        return [$Before,$Next];
    }
    private function ArticleClick($typeid,$id){
        $key = "articles_type{$typeid}_click";
        if(!Cache::Has($key)){
            UpdateCache("article_click",$typeid);
        }
        $clickArray = Cache::Get($key);
        if(isset($clickArray[$id])){
            $clickArray[$id]++;
        }else{
            $article = ArticleModel::get($id);
            if($article == null)return null;
            $click = $article->click +1;
            $clickArray[$id] = $click;
        }
        Cache::Set($key,$clickArray);
        if($clickArray[$id] % 20==0){
            $article = ArticleModel::get($id);
            if($article==null)return null;
            $data['click'] = $clickArray[$id];
            $article->save($data);
        }
        return $clickArray[$id];
    }
}