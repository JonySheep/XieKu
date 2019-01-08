<?php
/**
 * File: Page.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\api\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
use app\index\model\Page as PageModel;
class Page extends CoreController
{
    public function view($id){
        /*if(!Cache::Has("page_".$id)){
            UpdateCache("page",$id);
        }
        $page = Cache::Get("page_".$id);
        if($page == null){
            return "error";
        }
        $this->assign('subpage_name',$page['name']);

        $this->assign('type_link',url("m/page/view",['id'=>$id]));*/
        $page = PageModel::get($id);
        $this->assign('page_content',$page['body']);
        $this->assign('type_name',$id == 5?'常见问题':'关于我们');
        return $this->fetch();
    }
}