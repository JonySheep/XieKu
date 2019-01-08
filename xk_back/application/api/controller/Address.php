<?php
/**
 * File: Article.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 14:56
 */

namespace app\api\controller;


use app\index\controller\CoreController;
use app\index\model\Address  as ModelAddress;
use think\Db;
class Address extends CoreController
{
    public function addList(){
        if(!isset($_GET['openid'])){
            return $this->renderError('用户不存在');
        }
        $uid = getUidByOpenid($_GET['openid']) ;
        $addressList = Db::name("address")->where(['uid'=>$uid])->order('id desc')->select();
        return $this->renderSuccess($addressList);
    }

    public function delete(){

        if(!isset($_POST['openid'])){
            return $this->renderError('用户不存在');
        }
        if(!isset($_POST['id'])){
            return $this->renderError('地址不存在');
        }
        $uid = getUidByOpenid($_POST['openid']);
        $address =  ModelAddress::get($_POST['id']);
        if($address['uid'] != $uid){
            return $this->renderError('地址不存在');
        }
        $address->delete();
        return $this->renderSuccess('删除成功');

    }

    public function choose(){
        if(!isset($_POST['openid'])){
            return $this->renderError('用户不存在');
        }
        $uid = getUidByOpenid($_POST['openid']);
        if (!$uid) {
            return $this->renderError('用户不存在');
        }
        if(!isset($_POST['id'])){
            return $this->renderError('地址不存在');
        }
        $address =  ModelAddress::get($_POST['id']);
        if($address['uid'] != $uid){
            return $this->renderError('地址不存在');
        }
        ModelAddress::update(['is_default'=>0],['uid'=>$uid]);
        ModelAddress::update(['is_default'=>1],['uid'=>$uid,'id'=>$_POST['id']]);
        return $this->renderSuccess('设置成功');
    }


    public function edit(){
        if(!isset($_POST['openid'])){
            return $this->renderError('用户不存在');
        }
        $uid = getUidByOpenid($_POST['openid']);
        if (!$uid) {
            return $this->renderError('用户不存在');
        }
        if(!isset($_POST['id'])){
            return $this->renderError('地址不存在');
        }
        $address =  ModelAddress::get($_POST['id']);

        if($address['uid']!= $uid ){
            return $this->renderError('地址不存在');
        }

        return $this->renderSuccess($address);
    }

    public function update(){
        if(!isset($_POST['openid'])|| !$_POST['openid']){
            return $this->renderError('数据错误');
        }
        if(!isset($_POST['id'])|| !$_POST['id']){
            return $this->renderError('数据错误');
        }

        if(!isset($_POST['fullname'])|| !$_POST['fullname']){
            return $this->renderError('联系人不能为空');
        }
        if(!isset($_POST['mobile'])|| !$_POST['mobile']){
            return $this->renderError('联系方式不能为空');
        }
        if(!isset($_POST['address'])|| !$_POST['address']){
            return $this->renderError('地址不能为空');
        }
        if($_POST['is_default']){
            ModelAddress::update(['is_default'=>0],['uid'=>$_POST['uid']]);
        }
        $uid = getUidByOpenid($_POST['openid']);
        $address = ModelAddress::where('uid',$uid)->field('id')->select();
        if (!$address) {
            $is_default = 1;
        } else {
            $is_default = $_POST['is_default']?1:0;
        }
        ModelAddress::update([
            'fullname'=>$_POST['fullname'],
            'mobile'=>$_POST['mobile'],
            'address'=>$_POST['address'],
            'is_default'=>$is_default,
            'postal' => $_POST['postal'],
        ],['id'=>$_POST['id']]);
        return $this->renderSuccess('已保存');
    }

    public function orderChoose(){
        if(!isset($_GET['openid']) || !$_GET['openid']){
            return $this->renderError('参数错误');
        }
        if(!isset($_GET['address_id']) || !$_GET['address_id']){
            $uid = getUidByOpenid($_GET['openid']);
            $rs =  Db::name("address")->where(['uid'=>$uid,'is_default'=>1])->order('id desc')->select();
            $address = $rs?$rs[0]:[];
        }else{
            $address =   ModelAddress::get($_GET['address_id']);
            $address = $address ? : [];
        }
        return $this->renderSuccess($address);
    }

    /**
     * 添加收货地址
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(){
        $id = input('id',0);
        $openid = input('openid','','trim');
        if (!$openid) {
            return $this->renderError('数据错误');
        }

        if(!isset($_POST['fullname'])|| !$_POST['fullname']){
            return $this->renderError('联系人不能为空');
        }
        if(!isset($_POST['mobile'])|| !$_POST['mobile']){
            return $this->renderError('联系方式不能为空');
        }
        if(!isset($_POST['address'])|| !$_POST['address']){
            return $this->renderError('地址不能为空');
        }
        $uid = getUidByOpenid($_POST['openid']);
        if (!$uid) {
            return $this->renderError('未查询到用户');
        }
        $address = ModelAddress::where('uid',$uid)->field('id')->select();
        if (!$address) {
            $is_default = 1;
        } else {
            $is_default = $_POST['is_default']?1:0;
        }
        if($_POST['is_default'] == 1){
            ModelAddress::update(['is_default'=>0],['uid'=>$uid]);
        }
        if ($id) {
            ModelAddress::update([
                'fullname'=>$_POST['fullname'],
                'mobile'=>$_POST['mobile'],
                'address'=>$_POST['address'],
                'is_default'=>$is_default,
                'postal' => $_POST['postal'],
            ],['id'=>$_POST['id']]);
        } else {
            ModelAddress::insert([
                'uid'=>$uid,
                'fullname'=>$_POST['fullname'],
                'mobile'=>$_POST['mobile'],
                'address'=>$_POST['address'],
                'is_default'=>$is_default,
                'postal' => $_POST['postal']
            ]);
        }
        return $this->renderSuccess('已保存');
    }

}