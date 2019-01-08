<?php
/**
 * File: Article.php
 * User: XljBearSoft
 * Date: 2017-07-24
 * Time: 13:35
 */

namespace app\index\model;


use think\Model;
use WebCache\WebCacheLib as Cache;

class Article extends Model
{
    protected static function init(){
        Article::event("after_insert",function($article){Article::UpdateCache($article);});
        Article::event("after_update",function($article){Article::UpdateCache($article);});
        Article::event("after_delete",function($article){Article::Deleted($article);});
    }
    public function article_type(){
        return $this->hasOne("ArticleType","did","typeid");
    }
    protected static function Deleted($article){
        echo $article->id;
        Cache::Clear("article_cache_".$article->id);
        var_dump(Cache::Has("article_cache_".$article->id));
        UpdateCache("article_list",$article);
    }
    protected static function UpdateCache($article){
        UpdateCache("article",$article);
        UpdateCache("article_list",$article);
    }
}