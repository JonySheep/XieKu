<?php
/**
 * File: Page.php
 * User: XljBearSoft
 * Date: 2017-07-14
 * Time: 17:34
 */
namespace app\admin\controller;
use app\index\model\PageList;
class Page extends Admin
{
    public function edit($id){
        $Page = PageList::get($id);
        if(!$Page)return CreateInfoPage("发生错误...","该单页模型不存在!","关闭","javascript:CloseTab();");
        $this->assign("page",$Page->toArray());
        $this->assign("content",$Page->Page==null?"":$Page->Page->body);
        return $this->fetch('form');
    }
    public function modify(){
        $page_id = intval(input("post.pageid"));
        $body = htmlspecialchars_decode(input("post.editorValue"));
        $page = PageList::get($page_id);
        if(!$page_id)return CreateInfoPage("发生错误...","该单页模型不存在!","关闭","javascript:CloseTab();");
        if($page->Page==null){
            $Page = new \app\index\model\Page();
            $Page->pageid = $page->id;
            $Page->body = $body;
            $Page->save();
        }else{
            $page->Page->body = $body;
            $page->Page->save();
        }
        return CreateInfoPage("操作成功","{$page->name}单页内容修改成功!","返回",url('admin/page/edit',['id'=>$page->id]));
    }
}
