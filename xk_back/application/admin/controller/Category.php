<?php
/**
 * File: Category.php
 * User: XljBearSoft
 * Date: 2017-07-12
 * Time: 10:20
 */

namespace app\admin\controller;


use app\index\model\CategoryAttribute;
use think\Db;
use WebCache\WebCacheLib as Cache;
use app\admin\model\IntegralRule as IntegralRuleModel;
class Category extends Admin
{
    public function index(){
        $CategoryList = Cache::Get("category");
        $this->assign("CategoryList",$CategoryList);
        return $this->fetch();
    }
    public function edit($id){
        if(!HasCategory($id))
            return CreateInfoPage("发生错误...","无效的CategoryID!","返回",url('admin/category/index'));
        $this->assign("category",GetCategory($id));
        return $this->fetch("form");
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $categoryid = intval(input("post.edit_id"));
        if(!HasCategory($categoryid))
            return CreateInfoPage("发生错误...","无效的CategoryID!","返回",url('admin/category/index'));
        $attrs = isset($_POST['attr'])?$_POST['attr']:[];
        $vals = isset($_POST['val'])?$_POST['val']:[];
        $status = isset($_POST['attribute_status'])?$_POST['attribute_status']:[];
        $id = isset($_POST['attribute_id'])?$_POST['attribute_id']:[];
        for($i=0;$i<sizeof($attrs);$i++){
            $attrs[$i] = trim(htmlspecialchars($attrs[$i]));
            $vals[$i] = trim(htmlspecialchars($vals[$i]));
            $vals[$i] = trim($vals[$i],"#");
            $status[$i] = trim(htmlspecialchars($status[$i]));
            $id[$i] = intval($id[$i]);
            if($attrs[$i]==''||$vals[$i]=='')continue;
            $Attribute = ['categoryid'=>$categoryid,'name'=>$attrs[$i],'value'=>$vals[$i]];
            switch ($status[$i]){
                case "default":
                    $CA = CategoryAttribute::get($id[$i]);
                    if(!$CA)continue;
                    $CA->save($Attribute);
                    break;
                case "add":
                    $CA = new CategoryAttribute();
                    $CA->save($Attribute);
                    break;
                case "delete":
                    $CA = CategoryAttribute::get($id[$i]);
                    if(!$CA)continue;
                    Db::name('goods_attribute')->where('attributeid',$id[$i])->delete();
                    $CA->delete();
                    break;
            }
        }
        UpdateCache("category",null);
        return CreateInfoPage("操作成功","商品属性更新成功!","返回",url('admin/category/index'));
    }

    /**
     * 房型编辑
     * @author huanghao
     * @param $id int 分类id
     * @return \think\response\View
     */
    public function roomType($id){
        $list = IntegralRuleModel::getCateList($id);
        $this->assign("list",$list);
        $this->assign("category",GetCategory($id));
        return view('room_type');
    }

    /**
     * 房型编辑数据操作
     * @author huanghao
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function roomTypeUpdate(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $categoryid = intval(input("post.edit_id"));
        if(!HasCategory($categoryid))
            return CreateInfoPage("发生错误...","无效的CategoryID!","返回",url('admin/category/index'));
        $title = isset($_POST['title'])?$_POST['title']:[];
        $integral = isset($_POST['integral'])?$_POST['integral']:[];
        $status = isset($_POST['attribute_status'])?$_POST['attribute_status']:[];
        $id = isset($_POST['rule_id'])?$_POST['rule_id']:[];
        for($i=0;$i<sizeof($title);$i++){
            $title[$i] = trim(htmlspecialchars($title[$i]));
            $integral[$i] = trim(htmlspecialchars($integral[$i]));
            $integral[$i] = trim($integral[$i],"#");
            $status[$i] = trim(htmlspecialchars($status[$i]));
            $id[$i] = intval($id[$i]);
            if($title[$i]==''||$integral[$i]=='')continue;
            $Attribute = ['title'=>$title[$i], 'identification' => 'room_type', 'integral'=>$integral[$i], 'cate_id'=>$categoryid,];
            switch ($status[$i]){
                case "default":
                    $CA = IntegralRuleModel::get($id[$i]);
                    if(!$CA)continue;
                    $CA->allowField(true)->save($Attribute);
                    break;
                case "add":
                    $CA = new IntegralRuleModel();
                    $CA->allowField(true)->save($Attribute);
                    break;
                case "delete":
                    $CA = IntegralRuleModel::get($id[$i]);
                    if(!$CA)continue;
                    $CA->delete();
                    break;
            }
        }
        UpdateCache("category",null);
        return CreateInfoPage("操作成功","房型更新成功!","返回",url('admin/category/index'));
    }
}