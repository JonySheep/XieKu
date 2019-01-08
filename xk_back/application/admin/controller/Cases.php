<?php
/**
 * File: Cases.php
 * User: XljBearSoft
 * Date: 2017-07-28
 * Time: 15:42
 */

namespace app\admin\controller;


use WebCache\WebCacheLib as Cache;
use app\index\model\Cases as CaseModel;
use think\Db;
class Cases extends Admin
{
    public function index(){
        $page = input('?post.page')?intval(input('post.page')):1;
        $keyword = input('?post.search')?trim(input('post.search')):"";
        if($page<1)$page = 1;
        $whereCondition = [];
        if($keyword!=""){
            $whereCondition['title'] = ['like',"%$keyword%"];
        }
        $CaseCount =  Db::name("cases")->where($whereCondition)->count();
        $PageLimit = 15;
        $PageCount = $CaseCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $CaseList = Db::name("cases")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        $this->assign("CaseList",$CaseList);
        $this->assign("PageCount",$PageCount);
        $this->assign("NowPage",$page);
        $this->assign("CaseCount",$CaseCount);
        $this->assign("Keyword",$keyword);
        return $this->fetch();
    }
    public function add(){
        $modelList = Cache::Get("model");
        $regionList = Cache::Get("region");
        $categoryList = Cache::Get("category");
        $this->assign("modelList",$modelList);
        $this->assign("regionList",$regionList);
        $this->assign("categoryList",$categoryList);
        return $this->fetch('form');
    }
    public function edit($id){
        $case = CaseModel::get($id);
        if(!$case) {
            return CreateInfoPage("很抱歉...","该案例不存在或已被删除!","返回",url('admin/cases/index'));
        }
        $case->introduce = str_replace("<br>","\n",$case->introduce);
        $case->details = str_replace("<br>","\n",$case->details);
        $case->feedback = str_replace("<br>","\n",$case->feedback);
        $this->assign('case',$case);
        $this->assign("editmode",true);
        $modelList = Cache::Get("model");
        $regionList = Cache::Get("region");
        $categoryList = Cache::Get("category");
        $this->assign("modelList",$modelList);
        $this->assign("regionList",$regionList);
        $this->assign("categoryList",$categoryList);
        return $this->fetch('form');
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $case_data['cover'] = trim(input("post.coveraddress"));
        if($case_data['cover']==""){
            return CreateInfoPage("发生错误...","请先上传封面图像!","返回","javascript:window.history.back();");
        }
        $case_data['title'] = trim(input("post.title"));
        if($case_data['title']==""){
            return CreateInfoPage("发生错误...","请输入案例标题!","返回","javascript:window.history.back();");
        }
        $case_data['introduce'] = trim(input("post.introduce"));
        $case_data['details'] = trim(input("post.details"));
        $case_data['introduce'] = str_replace("\n","<br>",$case_data['introduce']);
        $case_data['details'] = str_replace("\n","<br>",$case_data['details']);
        $case_data['category'] = intval(input("post.category"));
        $case_data['model'] = intval(input("post.model"));
        $case_data['region'] = intval(input("post.region"));
        $case_data['status'] = intval(input("post.status"));

        $case_data['plan'] = htmlspecialchars_decode(input("post.plan"));
        $case_data['construction_details'] = htmlspecialchars_decode(input("post.construction_details"));
        $case_data['feedback'] = trim(input("post.feedback"));
        $case_data['feedback'] = str_replace("\n","<br>",$case_data['feedback']);

        $case_data['sort'] = intval(input("post.sort"));
        session("case_cover_file_old",null);
        if(input("?post.edit_id")){
            $case_id = intval(input("post.edit_id"));
            $case = CaseModel::get($case_id);
            if(!$case){
                return CreateInfoPage("很抱歉...","该案例不存在或已被删除!","返回",url('admin/cases/index'));
            }
            $rootdir = ROOT_PATH . 'public' . DS;
            if($case->cover!=$case_data['cover'])@unlink($rootdir.$case->cover);
            $case->save($case_data);
            return CreateInfoPage("操作成功","案例信息修改成功!","返回",url('admin/cases/index'));
        }else{
            $newArticle = new CaseModel();
            $newArticle->save($case_data);
            return CreateInfoPage("操作成功","案例已成功添加!","返回",url('admin/cases/index'));
        }
    }
    public function delete($id){
        $case = CaseModel::get($id);
        if(!$case){
            return CreateInfoPage("很抱歉...","该文章不存在或已被删除!","返回",url('admin/cases/index'));
        }else{
            $rootdir = ROOT_PATH . 'public' . DS;
            @unlink($rootdir.$case->cover);
            $case->delete();
            return CreateInfoPage("操作成功","案例已成功删除!","返回",url('admin/cases/index'));
        }
    }
    public function upload(){
        $file = request()->file('case_cover_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/case';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?case_cover_file_old'))@unlink(session('case_cover_file_old'));
                session('case_cover_file_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/case/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}