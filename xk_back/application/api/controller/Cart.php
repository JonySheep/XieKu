<?php
/**
 * File: Page.php
 * User: Admin
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\api\controller;


use app\index\controller\CoreController;
use app\index\model\Order as OrderModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\Users as UsersModel;
use app\index\model\Cart as CartModel;
use think\Db;
use think\Exception;
use app\index\model\Share as ShareModel;
/**
 * 购物车接口
 * Class Order
 * @package app\m\controller
 */
class Cart extends CoreController
{
    protected $model = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = new CartModel();
    }

    /**
     * 创建购物车
     * @param string $openid
     * @param string $goods_id
     * @param string $goods_num
     * @return \think\response\Json
     * @throws Exception
     */
    public function createCart($openid = '', $goods_id = '', $goods_num = '')
    {
        if (!$openid || !$goods_id || !$goods_num) {
            return $this->renderError('参数错误');
        }
        $uid = getUidByOpenid($openid);
        if (!$uid) {
            return $this->renderError('参数错误');
        }
        $data = input();
        $data['uid'] = $uid;
        $id = $this->model->where(['uid'=>$uid,'goods_id'=>$goods_id])->value('id');
        if ($id){
            $res = $this->model->where(['uid'=>$uid,'goods_id'=>$goods_id])->setInc('goods_num',$goods_num);
        } else {
            $res = $this->model->allowField(true)->save($data);
        }
        if ($res) {
            return $this->renderSuccess('');
        } else {
            return $this->renderError('添加购物车失败');
        }
    }

    /**
     * 购物车列表
     * @param string $openid
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartList($openid = '')
    {
        if (!$openid) {
            return $this->renderError('参数错误');
        }
        $uid = getUidByOpenid($openid);
        $list = $this->model
            ->alias('c')
            ->field('c.id, g.id as goods_id, g.title as goods_name, g.cover1 as image, c.goods_num, g.price, g.stock, 0 as status ')
            ->join('goods g', 'c.goods_id = g.id', 'LEFT')
            ->where('uid', $uid)
            ->order('c.id desc')
            ->select();
        return $this->renderSuccess($list);
    }

    /**
     * 购物车+1
     * @param string $id
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function setIntCart($id = '')
    {
        if (!$id) {
            return $this->renderError('参数错误');
        }
        if ($this->model->where('id', $id)->setInc('goods_num')) {
            return $this->renderSuccess('');
        } else {
            return $this->renderError('操作失败');
        }
    }

    /**
     * 购物车-1
     * @param string $id
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function setDecCart($id = '')
    {
        if (!$id) {
            return $this->renderError('参数错误');
        }
        if ($this->model->where('id', $id)->setDec('goods_num')) {
            return $this->renderSuccess('');
        } else {
            return $this->renderError('操作失败') ;
        }
    }

    /**
     * 购物车提交订单
     * @param string $cart_json
     * @param string $openid
     * @param string $cart_ids
     * @param string $share_token
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function cartToOrder($cart_json = '', $openid = '', $cart_ids = '', $share_token = '')
    {
        if (!$cart_json || !$openid || !$cart_ids) {
            return $this->renderError('参数错误');
        }
        $cart_json = stripslashes(html_entity_decode($cart_json));
        //商品信息
        $uid = getUidByOpenid($openid);
        if (!$uid) {
            return $this->renderError('参数错误');
        }
        $goods_list = [];
        $cart_list = json_decode($cart_json, true);
        $count = count($cart_list);
        $share_goods_id = ShareModel::where('token',$share_token)->value('goods_id');//分享商品id
        if ($count == 1) {
            //单个商品
            $goods_info = GoodsModel::get($cart_list[0]['goods_id']);
            if (!$goods_info) {
                return $this->renderError('参数有误');
            }
            $post_data_c = [
                'uid' => $uid,
                'goodsID' => $goods_info['id'],
                'number' => $cart_list[0]['goods_num'],
                'order_number' => 'XY-' . $uid . rand(100000, 999999),//订单号
                'goods_title' => $goods_info['title'],
                'goods_images' => $goods_info['cover1'],
                'goods_ingegory' => $goods_info['price'],
                'total_integory' => $goods_info['price'] * $cart_list[0]['goods_num'],
                'intregral' => $goods_info['price'] * $cart_list[0]['goods_num'],
                'create_time' => time(),
                'update_time' => time(),
                'status' => 0,
            ];
            //判断是否是分享
            if ($share_goods_id == $goods_info['id']) {
                $post_data_c['share_token'] = $share_token;
            }
            $goods_list[] = [
                'goods_title' => $goods_info['title'],
                'goods_images' => $goods_info['cover1'],
                'goods_number' => $cart_list[0]['goods_num'],
                'goods_ingegory' => $goods_info['price'],
                'total_integory' => $goods_info['price'] * $cart_list[0]['goods_num'],
            ];
        } else {
            //多个商品
            $intregral = 0;
            $goods_number = 0;
            $share_tokens = '';
            foreach ($cart_list as $v) {
                $goods_info = GoodsModel::get( $v['goods_id']);
                if (!$goods_info) {
                    return $this->renderError('参数有误');
                }
                $intregral += $goods_info['price'] * $v['goods_num'];
                $goods_number += $v['goods_num'];
                $goods_list[] = [
                    'goods_title' => $goods_info['title'],
                    'goods_images' => $goods_info['cover1'],
                    'goods_number' => $v['goods_num'],
                    'goods_ingegory' => $goods_info['price'],
                    'total_integory' => $goods_info['price'] * $v['goods_num'],
                ];
                //判断是否是分享
                if ($share_goods_id == $v['goods_id']) {
                    $share_tokens = $share_token;
                }
            }
            $post_data_c = [
                'uid' => $uid,
                'goodsID' => 0,
                'number' => $goods_number,
                'order_number' => 'XY-' . $uid . rand(100000, 999999),//订单号
                'intregral' => $intregral,
                'goods_info' => $cart_json,
                'create_time' => time(),
                'update_time' => time(),
                'status' => 0,
                'share_token' => $share_tokens,
            ];
        }


        $orderModel = new OrderModel();
        Db::startTrans();
        try {
            if (!$orderModel->isUpdate(false)->save($post_data_c)) {
                throw new Exception('订单创建失败');
            }
            $return = [
                'id' => $orderModel->getLastInsID(),
                'order_number' => $post_data_c['order_number'],
                'intregral' => $post_data_c['intregral'],
                'goods_list' => $goods_list,
            ];
            //提交后删除购物车
            $this->model->where('id','in',$cart_ids)->delete();
            //减少商品库存
            if ($count == 1) {
                if (!GoodsModel::where('id',$cart_list[0]['goods_id'])->setDec('stock',$cart_list[0]['goods_num'])) {
                    throw new Exception('减少库存失败');
                }
                $stock = GoodsModel::where('id',$cart_list[0]['goods_id'])->value('stock');
                if ($stock < 0 ) throw new Exception('库存不足');
            } else {
                foreach ($cart_list as $k => $v) {
                    if (!GoodsModel::where('id',$v['goods_id'])->setDec('stock',$v['goods_num'])) {
                        throw new Exception('减少库存失败');
                    }
                    $stock = GoodsModel::where('id',$v['goods_id'])->value('stock');
                    if ($stock < 0 ) throw new Exception('库存不足');
                }
            }
            Db::commit();
            return $this->renderSuccess($return);
        } catch (Exception $e) {
            Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除购物车
     * @param string $ids
     * @return \think\response\Json
     */
    public function delCart($ids = '')
    {
        if (!$ids) {
            return $this->renderError('参数错误');
        }
        $res = CartModel::where('id','in',$ids)->delete();
        if ($res) {
            return $this->renderSuccess('删除成功');
        } else {
            return $this->renderError('删除失败');
        }
    }

    /**
     * 获取购物车商品数量
     * @param string $openid
     * @return \think\response\Json
     */
    public function getCartNumber($openid = '')
    {
        if (!$openid) {
            return $this->renderError('参数错误');
        }
        return $this->renderSuccess(CartModel::where('uid',getUidByOpenid($openid))->sum('goods_num'));
    }
}