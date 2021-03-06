<?php
/**
 * File: Context.php
 * User: XljBearSoft
 * Date: 2017-07-18
 * Time: 17:52
 */

namespace app\swa\controller;

use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;

class Context extends CoreController
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
        $this->assign('type_link',url("swa/Context/view",['id'=>$id]));
        $this->assign('page_content',$page['Page']['body']);
        return $this->fetch('Public/frame');
    }
}