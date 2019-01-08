<?php
/**
 * Created by PhpStorm.
 * User: XljBearSoft
 * Date: 2017/9/3
 * Time: 15:28
 */

namespace app\admin\controller;


use think\Db;
use think\Request;
use WebCache\WebCacheLib as Cache;

class Design extends Admin
{
    public function free_options()
    {
        if (Request::instance()->isPost()){
            if(!input('?post.limit'))$this->redirect('admin/design/free_options');
            $limit = intval(input('post.limit'));
            if($limit<0)$limit=0;
            $data['applytotal'] = $limit;
            Db::name("freedesign_options")->where(['id'=>1])->update($data);
            UpdateCache("free_design_remain",null);
            $this->redirect('admin/design/free_options');
        }else{
            $alltreatedCount = Db::name("freedesign")->count();
            $untreatedCount = Db::name("freedesign")->where(['status'=>0])->count();
            $freedesigntotal = Db::name("freedesign_options")->where(['id'=>1])->find()['applytotal'];
            $freedesignremain = Cache::Get('free_design_remain')['remain'];
            $treatedCount = $alltreatedCount - $untreatedCount;
            if($alltreatedCount==0)
            $untreatedP = 0;
            else
            $untreatedP = $untreatedCount / $alltreatedCount * 100;
            $treatedP = 100 - $untreatedP;
            $this->assign("untreatedP",$untreatedP);
            $this->assign("treatedP",$treatedP);
            $this->assign("alltreatedCount",$alltreatedCount);
            $this->assign("untreatedCount",$untreatedCount);
            $this->assign("treatedCount",$treatedCount);
            $this->assign("freedesignremain",$freedesignremain);
            $this->assign("freedesigntotal",$freedesigntotal);
            return $this->fetch('options');
        }
    }
    public function delete_recode()
    {
        if (!Request::instance()->isAjax())exit();
        $id = intval(trim(input("post.id")));
        Db::name("freedesign")->delete($id);
        return 1;
    }
    public function treated_recode()
    {
        if (!Request::instance()->isAjax())exit();
        $id = intval(trim(input("post.id")));
        $note = trim(input("post.note"));
        if(Db::name("freedesign")->where(['id'=>$id])->count()<1){
            return json(['result'=>-1]);
        }
        Db::name("freedesign")->where(['id'=>$id])->update(['note'=>$note,'status'=>1,'treatedtime'=>time()]);
        UpdateCache("free_design",null);
        UpdateCache("free_design_list",null);
        return json(['result'=>1]);
    }
    public function free_recodes()
    {
        if (!Request::instance()->isAjax()){
            $untreatedCount = Db::name("freedesign")->where(['status'=>0])->count();
            $PageLimit = 15;
            $PageCount = $untreatedCount / $PageLimit;
            if(is_float($PageCount)||$PageCount == 0){
                $PageCount = intval($PageCount) + 1;
            }
            $untreatedList = Db::name("freedesign")->where(['status'=>0])->limit(0,$PageLimit)->select();
            $this->assign("untreatedCount",$untreatedCount);
            $this->assign('untreatedPageCount',$PageCount);
            $this->assign("untreatedList",$untreatedList);
            $this->assign("untreatedNowPage",1);



            $treatedCount = Db::name("freedesign")->where(['status'=>1])->count();
            $PageCount = $treatedCount / $PageLimit;
            if(is_float($PageCount)||$PageCount == 0){
                $PageCount = intval($PageCount) + 1;
            }
            $treatedList = Db::name("freedesign")->where(['status'=>1])->limit(0,$PageLimit)->select();
            $this->assign("treatedCount",$treatedCount);
            $this->assign('treatedPageCount',$PageCount);
            $this->assign("treatedList",$treatedList);
            $this->assign("treatedNowPage",1);
            return $this->fetch('free_list');
        }else{
            $type = input("post.type");
            switch ($type){
                case "untreated_list":
                    $page = intval(input("post.page"));
                    $keyword = trim(input("post.keyword"));
                    $whereCondition['status'] = 0;
                    if($keyword!=""){
                        $whereCondition['name'] = ['like',"%$keyword%"];
                    }
                    $untreatedCount = Db::name("freedesign")->where($whereCondition)->count();
                    $PageLimit = 2;
                    $PageCount = $untreatedCount / $PageLimit;
                    if(is_float($PageCount)||$PageCount == 0){
                        $PageCount = intval($PageCount) + 1;
                    }
                    if($page<1)$page = 15;
                    if($page>$PageCount)$page = $PageCount;
                    $untreatedList = Db::name("freedesign")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->select();
                    $this->assign("untreatedCount",$untreatedCount);
                    $this->assign('untreatedPageCount',$PageCount);
                    $this->assign("untreatedList",$untreatedList);
                    $this->assign("untreatedNowPage",$page);
                    return $this->fetch("untreatedList");
                    break;
                case "treated_list":
                    $page = intval(input("post.page"));
                    $keyword = trim(input("post.keyword"));
                    $whereCondition['status'] = 1;
                    if($keyword!=""){
                        $whereCondition['name'] = ['like',"%$keyword%"];
                    }
                    $treatedCount = Db::name("freedesign")->where($whereCondition)->count();
                    $PageLimit = 15;
                    $PageCount = $treatedCount / $PageLimit;
                    if(is_float($PageCount)||$PageCount == 0){
                        $PageCount = intval($PageCount) + 1;
                    }
                    if($page<1)$page = 1;
                    if($page>$PageCount)$page = $PageCount;
                    $treatedList = Db::name("freedesign")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->select();
                    $this->assign("treatedCount",$treatedCount);
                    $this->assign('treatedPageCount',$PageCount);
                    $this->assign("treatedList",$treatedList);
                    $this->assign("treatedNowPage",$page);
                    return $this->fetch("treatedList");
                    break;
            }
        }
    }
    public function oneminute_recodes(){
        $page = input('?post.page')?intval(input('post.page')):1;
        $keyword = input('?post.search')?trim(input('post.search')):"";
        if($page<1)$page = 1;
        $whereCondition = [];
        if($keyword!=""){
            $whereCondition['phone'] = ['like',"%$keyword%"];
        }
        $Count =  Db::name("oneminute_recode")->where($whereCondition)->count();
        $PageLimit = 15;
        $PageCount = $Count / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $typeList = ["平层","复式","三层别墅","四层别墅"];
        $modelList = ["一房一厅","二室一厅","二室二厅","三室一厅","三室二厅","四室二厅","四室三厅","五室二厅","五室三厅"];
        $areaList = ["70平方米以下","70-90平方米","80-120平方米","90-130平方米","110-150平方米","130-180平方米","180-250平方米","250以上平方米"];
        $List = Db::name("oneminute_recode")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("timestamp desc")->select();
        foreach ($List as &$item){
            $item['result'] = json_decode($item['result'],true);
            $item['result']['model'] = $modelList[$item['result']['model']];
            $item['result']['type'] = $typeList[$item['result']['type']];
            $item['result']['area'] = $areaList[$item['result']['area']-1];
            $item['result']['result'] = "";
            foreach ($item['result']['item'] as $items){
                if(is_numeric($items[2]))
                    $item['result']['result'] .= "$items[0]($items[1]) - <span style=\"color:red\">$items[2]</span>元" . "<br>";
                else
                    $item['result']['result'] .= "$items[0]($items[1]) - <span style=\"color:blue\">$items[2]</span>" . "<br>";
            }
            unset($item['result']['item']);
        }
        $this->assign('list',$List);
        $this->assign('Count',$Count);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign('Keyword',$keyword);
        return $this->fetch('oneminute_list');
    }
}