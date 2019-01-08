<?php
/**
 * File: Page.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;

class Page extends CoreController
{
    public function view($id){
        if(!Cache::Has("page_".$id)){
            UpdateCache("page",$id);
        }
        $page = Cache::Get("page_".$id);
        if($page == null){
            return "error";
        }
        $this->assign('subpage_name',$page['name']);
        $this->assign('type_name',$page['name']);
        $this->assign('type_link',url("m/page/view",['id'=>$id]));
        $this->assign('page_content',$page['Page']['body']);
        return $this->fetch();
    }
}