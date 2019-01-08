<?php
/**
 * File: Cases.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 15:34
 */

namespace app\m\controller;


use app\index\controller\CoreController;
use WebCache\WebCacheLib as Cache;
use think\Db;
use app\index\model\Cases as CasesModel;
class Cases extends CoreController
{
    public function index(){
        $page = input('?get.page')?intval(input('get.page')):1;
        if($page<1)$page = 1;
        $CaseCount =  Db::name("cases")->count();
        $PageLimit = 6;
        $PageCount = $CaseCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $CaseList = Db::name("cases")->limit(($page - 1)*$PageLimit,$PageLimit)->order("sort asc,id desc")->select();
        $this->assign("caseList",$CaseList);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        return $this->fetch();
    }
    public function view($id){
        if(!Cache::Has("case_cache_$id")){
            $case = CasesModel::get($id);
            if($case == null) {
                return "文章已被删除!";
            }
            UpdateCache("case",$case);
        }else{
            $case = Cache::Get("case_cache_$id");
        }
        $case['click'] = $this->CaseClick($case['id']);
        $this->assign("case",$case);
        return $this->fetch('view');
    }
    private function CaseClick($id){
        $key = "case_click";
        if(Cache::Has($key)){
            $clickArray = Cache::Get($key);
            if(isset($clickArray[$id])){
                $clickArray[$id]++;
            }else{
                $case = CasesModel::get($id);
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
            $case = CasesModel::get($id);
            if($case == null)return null;
            $click = $case->read_count +1;
            $clickArray[$id] = $click;
            Cache::Set($key,$clickArray);
        }
        return $clickArray[$id];
    }
}