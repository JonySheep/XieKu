<?php
/**
 * File: Service.php
 * User: XljBearSoft
 * Date: 2017-07-28
 * Time: 15:00
 */

namespace app\swa\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;

class Service extends CoreController
{
    public function index(){
        $categoryList = Cache::Get("category");
        $this->assign('categoryList',$categoryList);
        $this->redirect('swa/service/view',['id'=>3]);
    }
    public function view($id){
        if(!Cache::Has("page_".$id)){
            UpdateCache("page",$id);
        }
        $page = Cache::Get("page_".$id);
        if($page == null){
            return "error";
        }
        $categoryList = Cache::Get("category");
        $this->assign('categoryList',$categoryList);
        $this->assign('subpage_name',$page['name']);
        $this->assign('type_name',$page['name']);
        $this->assign('type_link',url("swa/service/view",['id'=>$id]));
        $this->assign('page_content',$page['Page']['body']);
        return $this->fetch();
    }
    public function help($typeid){
        if(!HasCategory($typeid))return "error";
        if(!Cache::Has("help_list_type".$typeid)){
            UpdateCache("help_list",$typeid);
        }
        $categoryList = Cache::Get("category");
        $this->assign('categoryList',$categoryList);
        $HelpList = Cache::Get("help_list_type".$typeid);
        $this->assign('HelpList',$HelpList);
        $HelpHtml = $this->fetch('list');
        $cname = GetCategoryName($typeid)."系统帮助文档";
        $this->assign('subpage_name',$cname);
        $this->assign('type_name',$cname);
        $this->assign('type_link',url("swa/service/help",['typeid'=>$typeid]));
        $this->assign('page_content',$HelpHtml);
        return $this->fetch('view');
    }
}