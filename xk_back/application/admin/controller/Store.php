<?php
/**
 * File: Store.php
 * User: XljBearSoft
 * Date: 2017-08-02
 * Time: 9:18
 */

namespace app\admin\controller;

use think\Db;
use WebCache\WebCacheLib as Cache;
use app\index\model\Store as StoreModel;
class Store extends Admin
{
    public function index(){
        $page = input('?post.page')?intval(input('post.page')):1;
        $keyword = input('?post.search')?trim(input('post.search')):"";
        if($page<1)$page = 1;
        $whereCondition = [];
        if($keyword!=""){
            $whereCondition['title'] = ['like',"%$keyword%"];
        }
        $StoreCount =  Db::name("store")->where($whereCondition)->count();
        $PageLimit = 15;
        $PageCount = $StoreCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $StoreList = Db::name("store")->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        $this->assign('StoreList',$StoreList);
        $this->assign('StoreCount',$StoreCount);
        $this->assign('NowPage',$page);
        $this->assign('PageCount',$PageCount);
        $this->assign('Keyword',$keyword);
        return $this->fetch();
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $covers = isset($_POST['cover'])?$_POST['cover']:[];
        $cover_sort = isset($_POST['cover_sort'])?$_POST['cover_sort']:[];
        if(sizeof($covers)==0){
            return CreateInfoPage("发生错误...","请至少上传一张门店预览图!","返回","javascript:window.history.back();");
        }
        for($i=0;$i<sizeof($covers);$i++){
            $covers[$i] = trim(htmlspecialchars($covers[$i]));
            $cover_sort[$i] = intval($cover_sort[$i]);
        }
        array_multisort($cover_sort,SORT_ASC,$covers);
        $cover_size = sizeof($covers)<3?sizeof($covers):3;
        for ($i=0;$i<$cover_size;$i++){
            $store_data['cover'.($i+1)] = $covers[$i];
        }
        $store_data['title'] = trim(input("post.title"));
        if($store_data['title']==""){
            return CreateInfoPage("发生错误...","请输入门店名称!","返回","javascript:window.history.back();");
        }
        $store_data['qrcode'] = trim(input("post.qrcode"));
        if($store_data['qrcode']==""){
            return CreateInfoPage("发生错误...","请先上传门店二维码!","返回","javascript:window.history.back();");
        }
        $store_data['sort'] = intval(input("post.sort"));
        $store_data['address'] = trim(input("post.address"));
        $address_number = explode(',',input('address_number'));
        $store_data['lng'] = $address_number[0];
        $store_data['lat'] = $address_number[1];
        $store_data['telephone'] = trim(input('post.telephone'));
        $store_data['regionid'] = intval(input("post.region"));
        session("upload_qrcode_old",null);
        if(input("?post.edit_id")){
            $store_id = intval(input("post.edit_id"));
            $store = StoreModel::get($store_id);
            if(!$store){
                return CreateInfoPage("很抱歉...","该门店不存在或已被删除!","返回",url('admin/store/index'));
            }
            $rootdir = ROOT_PATH . 'public' . DS;
            if($store->qrcode!=$store_data['qrcode'])@unlink($rootdir.$store->qrcode);
            $store->save($store_data);
            return CreateInfoPage("操作成功","门店信息修改成功!","返回",url('admin/store/index'));
        }else{
            $newArticle = new StoreModel();
            $newArticle->save($store_data);
            return CreateInfoPage("操作成功","门店已成功添加!","返回",url('admin/store/index'));
        }
    }
    public function edit($id){
        $store = StoreModel::get($id);
        if(!$store) {
            return CreateInfoPage("很抱歉...","该门店不存在或已被删除!","返回",url('admin/store/index'));
        }
        $store['address_number'] = empty($store['lng']) ? '':implode(',',[$store['lng'],$store['lat']]);
        $this->assign('store',$store);
        $this->assign('editmode',true);
        $coverList = [];
        for($i=1;$i<=3;$i++){
            if($store["cover$i"]!=null){
                $coverList[] = $store["cover$i"];
            }
        }
        $this->assign('coverList',$coverList);
        $regionList =  Cache::Get('region');
        $this->assign('regionList',$regionList);
        return $this->fetch('form');
    }
    public function add(){
        $regionList =  Cache::Get('region');
        $this->assign('regionList',$regionList);
        return $this->fetch('form');
    }
    public function delete($id){
        $store = StoreModel::get($id);
        if(!$store){
            return CreateInfoPage("很抱歉...","该门店不存在或已被删除!","返回",url('admin/store/index'));
        }else{
            $rootdir = ROOT_PATH . 'public' . DS;
            if(file_exists($rootdir.$store->cover1))@unlink($rootdir.$store->cover1);
            if(file_exists($rootdir.$store->cover1))@unlink($rootdir.$store->cover2);
            if(file_exists($rootdir.$store->cover1))@unlink($rootdir.$store->cover3);
            @unlink($rootdir.$store->qrcode);
            $store->delete();
            return CreateInfoPage("操作成功","门店已成功删除!","返回",url('admin/store/index'));
        }
    }
    public function coverupload(){
        $file = request()->file('cover_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/store';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                return json(['result'=>1,'src'=>"/data/images/store/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
    public function qrcodeupload(){
        $file = request()->file('qrcode_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/store';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                if(session('?upload_qrcode_old'))@unlink(session('upload_qrcode_old'));
                session('upload_qrcode_old',$rootdir.'/'.$info->getSaveName());
                return json(['result'=>1,'src'=>"/data/images/store/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}