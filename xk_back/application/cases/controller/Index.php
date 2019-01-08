<?php
/**
 * File: index.php
 * User: XljBearSoft
 * Date: 2017-07-28
 * Time: 18:58
 */
namespace app\cases\controller;
use app\index\model\Cases;
use WebCache\WebCacheLib as Cache;
use app\index\controller\CoreController;
use think\Db;
class Index extends CoreController
{
    public function index(){
        $page = input('?get.page')?intval(input('get.page')):1;
        $region = input('?get.region')?intval(input('get.region')):0;
        $model = input('?get.model')?intval(input('get.model')):0;
        $category = input('?get.category')?intval(input('get.category')):0;
        $region = $region<0?0:$region;
        $model = $model<0?0:$model;
        $category = $region<0?0:$category;

        if($page<1)$page = 1;
        $whereCondition = [];
        if($region!=0){
            $whereCondition['region'] = $region;
        }
        if($model!=0){
            $whereCondition['model'] = $model;
        }
        if($category!=0){
            $whereCondition['category'] = $category;
        }
        $this->assign("model",$model);
        $this->assign("region",$region);
        $this->assign("category",$category);
        $CaseCount =  Db::name("cases")->where($whereCondition)->count();
        $PageLimit = 6;
        $PageCount = $CaseCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $CaseList = Db::name("cases")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        foreach ($CaseList as &$Case){
            $Case['click'] = $this->GetClick($Case['id']);
        }
        $this->assign("caseList",$CaseList);
        $modelList = Cache::Get("model");
        $regionList = Cache::Get("region");
        $categoryList = Cache::Get("category");

        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign("modelList",$modelList);
        $this->assign("regionList",$regionList);
        $this->assign("categoryList",$categoryList);
        return $this->fetch();
    }
    public function view($id){
        if(!Cache::Has("case_cache_$id")){
            $case = Cases::get($id);
            if($case == null) {
                return "文章已被删除!";
            }
            UpdateCache("case",$case);
        }else{
            $case = Cache::Get("case_cache_$id");
        }
        $case['click'] = $this->CaseClick($case['id']);
        $this->assign("case",$case);
        $RandomCaseList = $this->GetRandomCase();
        $this->assign("RandomCaseList",$RandomCaseList);
        return $this->fetch('view');
    }
    private function GetRandomCase($count = 4){
        if(!Cache::Has("case_list")){
            UpdateCache("case_list",null);
        }
        $CaseList = Cache::Get("case_list");
        shuffle($CaseList);
        return array_slice($CaseList,0,$count);
    }
    private function GetClick($id){
        $key = "case_click";
        if(Cache::Has($key)) {
            $clickArray = Cache::Get($key);
            if(!isset($clickArray[$id])){
                $case = Cases::get($id);
                $click = $case->read_count;
                $clickArray[$id] = $click;
                Cache::Set($key,$clickArray);
            }
        }else{
            $case = Cases::get($id);
            $click = $case->read_count;
            $clickArray[$id] = $click;
            Cache::Set($key,$clickArray);
        }
        return $clickArray[$id];
    }
    private function CaseClick($id){
        $key = "case_click";
        if(Cache::Has($key)){
            $clickArray = Cache::Get($key);
            if(isset($clickArray[$id])){
                $clickArray[$id]++;
            }else{
                $case = Cases::get($id);
                if($case == null)return null;
                $click = $case->read_count +1;
                $clickArray[$id] = $click;
            }
            Cache::Set($key,$clickArray);
            if($clickArray[$id] % 20==0){
                $case = Cases::get($id);
                if($case==null)return null;
                $data['read_count'] = $clickArray[$id];
                $case->save($data);
            }
        }else{
            $case = Cases::get($id);
            if($case == null)return null;
            $click = $case->read_count +1;
            $clickArray[$id] = $click;
            Cache::Set($key,$clickArray);
        }
        return $clickArray[$id];
    }
}