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
use app\index\model\Address as AddressModel;
use app\index\model\Users as UsersModel;
use  app\admin\model\Logistics as LogisticsModel;
use app\admin\model\UsersCoupon as UsersCouponModel;
use think\Db;
use think\Exception;
use app\index\model\Share as ShareModel;
use  app\admin\model\UsersActivityConversion as ActOrderModel;
use app\admin\model\Activity as ActivityModel;

/**
 * 订单接口
 * Class Order
 * @package app\m\controller
 */
class Order extends CoreController
{
    /**
     * 创建订单
     * @throws
     */
     public function createOrder()
    {
        //创建订单
        $post_data = [
            'goodsID' => input('goodsID',0,'intval'), //商品id
            'number' => input('goods_number',0,'intval'), //商品数量
            'openid' => input('openid','','trim'), //用户open_id
            'share_token' => input('share_token','','trim'),//分享token
        ];
        if (!$this->validate($post_data,'Order.add')) {
            return $this->renderError('参数错误');
        }
        //商品信息
        $goods_info = GoodsModel::get($post_data['goodsID']);
        $userModel = new UsersModel();
        $uid = $userModel->where([ 'openid' => $post_data['openid']])->value('id');
        if (!$uid) {
            return $this->renderError('参数错误');
        }
        //判断库存
        if ($goods_info['stock'] < $post_data['number']) {
            return $this->renderError('库存不足');
        }
        //判断是否是分享
        $share_goods_id = ShareModel::where('token',$post_data['share_token'])->value('goods_id');
        if ($share_goods_id != $post_data['goodsID']) {
            unset($post_data['share_token']);
        }
        unset($post_data['openid']);
        $post_data_c = [
            'uid' => $uid,
            'order_number' => 'XY-'.$uid.rand(100000,999999),//订单号
            'goods_title' => $goods_info['title'],
            'goods_images' => $goods_info['cover1'],
            'goods_ingegory' => $goods_info['price'],
            'total_integory' => $goods_info['price'] * $post_data['number'],
            'intregral' => $goods_info['price'] * $post_data['number'],
            'create_time'=>time(),
            'update_time' => time(),
            'status' => 0,
        ];
        $goods_list = [];
        $goods_list[] = [
            'goods_title' => $goods_info['title'],
            'goods_images' => $goods_info['cover1'],
            'goods_number' => $post_data['number'],
            'goods_ingegory' => $goods_info['price'],
            'total_integory' => $goods_info['price'] * $post_data['number'],
        ];
        $post_data = array_merge($post_data, $post_data_c);
        $orderModel = new OrderModel();
        if ($orderModel->isUpdate(false)->save($post_data)) {
            $return = [
                'id' => $orderModel->getLastInsID(),
                'order_number' => $post_data['order_number'],
                'intregral' => $post_data_c['intregral'],
                'goods_list' => $goods_list,
            ];
            return $this->renderSuccess($return);
        } else {
            return $this->renderError('订单生成失败');
        }
    }

    /**
     * 确认订单
     * @throws \think\exception\DbException
     */
    public function updateOrder()
    {
        $id = input('id',0,'intval');
        if ($id <= 0) {
            return $this->renderError('参数错误');
        }
        $order_info = OrderModel::get($id);
        if (!$order_info) {
            return $this->renderError('订单信息错误');
        }
        $post_data = [
            'addrsss' => input('address','','intval'),//地址id
            'remarks' => input('remarks','','trim'),//备注
            'shipping_way' => input('shipping_way','','intval'), //配送方式
            //'reduce_integory' => input('reduce_integory',0,'intval'), //优惠金额
            'pay_integory' => input('pay_integory','','intval'), //支付方式 1线上支付 2到店付款
            'store_id' => input('store_id','','intval'),//店铺id
            'coupon_id' => input('coupon_id','','intval'),//优惠券id
        ];
        if (!$this->validate($post_data,'Order.update')) {
            return $this->renderError('参数错误');
        }
        if ($post_data['pay_integory'] == 2) {
            if (!$post_data['store_id']) {
                return $this->renderError('请选择店铺');
            }
        }
        $address_info = AddressModel::get($post_data['addrsss']);
        $post_data_c = [
            'address_detail' => $address_info['address'],
            'address_name'  => $address_info['fullname'],
            'address_tel' => $address_info['mobile'],
        ];
        $post_data = array_merge($post_data, $post_data_c);
        Db::startTrans();
        try {

            $return = [
                'order_sn' => $order_info['order_number'],
                'total_fee' => $order_info['intregral'],
                'pay_integory' => $post_data['pay_integory'],
            ];
            if (!empty($post_data['coupon_id'])){
                $usersCouponModel = new UsersCouponModel();
                $map['uc.is_use'] = 0;
                $map['uc.id'] = $post_data['coupon_id'];

                $coupon = $usersCouponModel
                    ->alias('uc')
                    ->field('c.face_money, c.top_money')
                    ->join('Coupon c','uc.coupon_id = c.id','left')
                    ->where($map)
                    ->find();
                if (!$coupon) {
                    throw new Exception('该优惠券已使用，请刷新后重试');
                }
                if ($coupon['top_money'] != 0 && $order_info['intregral'] < $coupon['top_money']) {
                    throw new Exception('优惠券错误，请刷新后重试');
                }
                $post_data['reduce_integory'] = $coupon['face_money'];
                $post_data['intregral'] = $order_info['intregral'] - $coupon['face_money'];
                $return['total_fee'] -= $coupon['face_money'];
            }
            if (!OrderModel::update($post_data,['id'=>$id])) {
                throw new Exception('确认订单失败');
            }
            //改变优惠券状态
            if (!UsersCouponModel::update(['is_use' => 1],['id'=>$post_data['coupon_id']])) {
                throw new Exception('优惠券使用失败');
            }
            //增加商品销量并减少库存
            if ($order_info['goodsID']) {
                GoodsModel::where('id',$order_info['goodsID'])->setInc('sale',$order_info['number']);
                GoodsModel::where('id',$order_info['goodsID'])->setDec('stock',$order_info['number']);
            } else {
                $goods_info = json_decode($order_info['goods_info'],1);
                foreach ($goods_info as $k => $v) {
                    GoodsModel::where('id',$v['goods_id'])->setInc('sale',$v['goods_num']);
                    GoodsModel::where('id',$v['goods_id'])->setDec('stock',$v['goods_num']);
                }
            }
            Db::commit();
            return $this->renderSuccess($return);
        } catch(Exception $e) {
            Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderList()
    {
        $status = input('status',-1,'intval');
        $uid = input('openid','','trim');
        if (!$uid) {
            return $this->renderError('参数错误');
        }
        if ($status == -1) {
            return $this->renderError('参数错误');
        }
        $userModel = new UsersModel();
        $uid = $userModel->where('openid',$uid)->value('id');
        if (!$uid) {
            return $this->renderError('未查询到用户');
        }
        $map = [
            'uid' => $uid,
            'status' => $status,
        ];
        $list = OrderModel::where($map)
            ->field('id,order_number,create_time,create_time as time,goods_images,goods_title,intregral,number,pay_integory,total_integory,status,logistics_id,logistics_number,concat(1) as order_type, goods_info')
            ->order('id desc')
            ->select();
        foreach ($list as $k => &$v) {
            if ($v['pay_integory'] == 1) {
                $v['pay_integory'] = '线上支付';
            } else {
                $v['pay_integory'] = '到店付款';
            }
            if ($v['status'] < 5) {
                unset($v['update_time']);
            }
            $goods_list = [];
            if (isset($v['goods_info']) && $v['goods_info'] != '') {
                $v['goods_info'] = json_decode($v['goods_info'],true);
                foreach ($v['goods_info'] as $kg => $vg) {
                    $goods_info = GoodsModel::get($vg['goods_id']);
                    $goods_list[] = [
                        'goods_title' => $goods_info['title'],
                        'goods_images' => $goods_info['cover1'],
                        'goods_number' => $vg['goods_num'],
                        'goods_price' => $goods_info['price'] * $vg['goods_num'],
                    ];
                }
            }
            $v['goods_info'] = $goods_list;
        }
        $map_act = [
            'users_id' => $uid,
            'status' => $status,
        ];
        //,UNIX_TIMESTAMP(create_time) as time
        $act_list = ActOrderModel::where($map_act)
            ->field('id,order_number,create_time,create_time as time,goods_images,goods_title,intregral,goods_number as number,concat(\'\') as pay_integory,intregral as total_integory,status,logistics_id,logistics_number,concat(2) as order_type')
            ->order('id desc')
            ->select();
        $list = array_merge($list,$act_list);
        $list_key = new_array_column($list,'time');
        array_multisort($list_key,SORT_DESC,$list);
        return $this->renderSuccess($list);
    }

    /**
     * 取消订单
     * @param string $id
     * @param string $order_type
     * @return \think\response\Json
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function cancelOrder($id = '', $order_type = '')
    {
        if (!$id || ($order_type != 1 && $order_type != 2)) {
            return $this->renderError('参数错误');
        }
        if ($order_type == 1) {
            //增加商品库存并减少销量
            $order_info = OrderModel::get($id);
            if (!$order_info) {
                return $this->renderError('未查询到订单');
            }
            if ($order_info['goodsID']) {
                GoodsModel::where('id',$order_info['goodsID'])->setInc('stock',$order_info['number']);
                GoodsModel::where('id',$order_info['goodsID'])->setDec('sale',$order_info['number']);
            } else {
                $goods_info = json_decode($order_info['goods_info'],1);
                foreach ($goods_info as $k => $v) {
                    GoodsModel::where('id',$v['goods_id'])->setInc('stock',$v['goods_num']);
                    GoodsModel::where('id',$v['goods_id'])->setDec('sale',$v['goods_num']);
                }
            }
            Db::startTrans();
            try {
                $orderModel = new OrderModel();
                if (!$orderModel->save(['status'=>9],['id'=>$id])){
                    throw new Exception('更改订单状态失败');
                }
                //如果是待发货状态 1  取消订单需要返回用户money
                if ($order_info['status'] == 1) {
                    $refund = $this->refund($id);
                    if ($refund['code'] != 1) {
                        throw new Exception($refund['message']);
                    }
                }
                //如果是分享订单  减少分享人积分
                if ($order_info['share_token']) {
                    $share_info = ShareModel::where('token',$order_info['share_token'])->field('uid, integral')->find();
                    $res = UsersModel::where('id',$share_info['uid'])->setDec('integral',$share_info['integral']);
                    if (!$res) {
                        throw new Exception('减少用户积分失败');
                    }
                    ShareModel::where('token',$order_info['share_token'])->setDec('buy_number');
                }
                Db::commit();
                return $this->renderSuccess(['message'=>'取消成功']);
            } catch (Exception $e) {
                Db::rollback();
                return $this->renderError($e->getMessage());
            }
        } else {
            //增加活动剩余人数
            $order_info = ActOrderModel::get($id);
            if (!$order_info) {
                return $this->renderError('未查询到订单');
            }
            ActivityModel::where('id',$order_info['activity_id'])->setInc('residue_places',$order_info['goods_number']);
            try {
                //是否支付
                if ($order_info['status'] == 1) {
                    //返回积分
                    $res = UsersModel::where('id',$order_info['users_id'])->setInc('integral',(int)$order_info['intregral']);
                    if (!$res) {
                        throw new Exception('扣除积分失败');
                    }
                }
                $orderModel = new ActOrderModel();
                if (!$orderModel->save(['status'=>9],['id'=>$id])){
                    throw new Exception('更改订单状态失败');
                }
                Db::commit();
                return $this->renderSuccess(['message'=>'取消成功']);
            } catch (Exception $e) {
                Db::rollback();
                return $this->renderError('取消失败');
            }
        }

    }

    /**
     * 查询物流
     * @param string $order_number
     * @param string $order_type
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLogistics($order_number = '', $order_type = '')
    {
        if (!$order_number || !$order_type) {
            return $this->renderError('参数错误');
        }
        if ($order_type == 1) {
            $order_info = OrderModel::where('order_number',$order_number)->field('logistics_id as com,logistics_number as order_sn')->find();
        } else {
            $order_info = ActOrderModel::where('order_number',$order_number)->field('logistics_id as com,logistics_number as order_sn')->find();
        }

        $com = $order_info['com'];
        $order_sn = $order_info['order_sn'];
        //通过订单号查询物流进度
        //参数设置
        $post_data = array();
        $post_data["customer"] = '1DBA65AFDAB5675D034736D183846176';
        $key= 'btUMLsJE6861' ;
        $post_data["param"] = '{"com":"'.$com.'","num":"'.$order_sn.'"}';

        $url='http://poll.kuaidi100.com/poll/query.do';
        $url='http://www.kuaidi100.com/query?type='.$com.'&postid='.$order_sn;
        $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        $data = str_replace("\"",'"',$result );
        $data = json_decode($data,true);
        if ($data['status'] != 200) {
            return $this->renderError($data['message']);
        }
        $company = LogisticsModel::where('number',$com)->value('name');
        if ($order_type == 1) {
            $send_time = OrderModel::where('logistics_number',$order_sn)->value('send_time');
        } else {
            $send_time = ActOrderModel::where('logistics_number',$order_sn)->value('send_time');
        }

        $return = [
            'company' => $company,
            'send_time' => date('Y-m-d H:i:s',$send_time),
            'order_sn' => $order_sn,
            'list' => $data['data'],
        ];
        return $this->renderSuccess($return);
    }

    /**
     * 订单--去支付
     * @param string $id
     * @param string $order_type
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function goPayOrder($id = '',$order_type = '')
    {
        if (!$id || !$order_type) {
            return $this->renderError('参数错误');
        }
        if ($order_type == 1) {
            $order_info = OrderModel::get($id);
            $goods_list = [];
            if ($order_info['goodsID']) {
                $goods_info = GoodsModel::get($order_info['goodsID']);
                $goods_list[] = [
                    'goods_title' => $goods_info['title'],
                    'goods_images' => $goods_info['cover1'],
                    'goods_number' => $order_info['number'],
                    'goods_ingegory' => $goods_info['price'],
                    'total_integory' => $goods_info['price'] * $order_info['number'],
                ];
            } else {
                $goods_json = json_decode($order_info['goods_info'],true);
                foreach ($goods_json as $k => $v) {
                    $goods_info = GoodsModel::get( $v['goods_id']);
                    $goods_list[] = [
                        'goods_title' => $goods_info['title'],
                        'goods_images' => $goods_info['cover1'],
                        'goods_number' => $v['goods_num'],
                        'goods_ingegory' => $goods_info['price'],
                        'total_integory' => $goods_info['price'] * $v['goods_num'],
                    ];
                }
            }

            $return = [
                'id' => $id,
                'order_number' => $order_info['order_number'],
                'intregral' => $order_info['intregral'],
                'goods_list' => $goods_list,
                'order_type' => $order_type,
            ];
            return $this->renderSuccess($return);
        } else {
            $order_info = ActOrderModel::get($id);
            $goods_info = ActivityModel::get($order_info['activity_id']);
            $goods_list = [];
            $goods_list[] = [
                'goods_title' => $goods_info['activity_name'],
                'goods_images' => $goods_info['activity_img'],
                'goods_number' => 1,
                'goods_ingegory' => $goods_info['integral'],
                'total_integory' => $goods_info['integral'] * 1,
            ];
            $return = [
                'id' => $id,
                'order_number' => $order_info['order_number'],
                'intregral' => $order_info['intregral'],
                'goods_list' => $goods_list,
                'order_type' => $order_type,
            ];
            return $this->renderSuccess($return);
        }

    }

    /**
     * 退款
     * @param string $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refund($id = '')
    {
        $order_info = OrderModel::where('id',$id)->field('intregral,reduce_integory,pay_integory,transaction_id,refund_sn')->find();
        if (!$order_info) {
            return ['code'=>0,'message'=>'订单不存在'];
        }
        if ($order_info['transaction_id'] != '') {
            //退款单号
            if (!$order_info['refund_sn']) {
                $order_info['refund_sn'] = 'B'.rand(100000,999999);
                OrderModel::update(['refund_sn'=>$order_info['refund_sn']],['id'=>$id]);
            }
            $config = config('wx_pay');
            $refund_data = array(
                'appid'			=> $config['appid'],
                'mch_id'		=> $config['pay_mchid'],
                'nonce_str'		=> getNonceStr(),
                'transaction_id'=> $order_info['transaction_id'],
                'out_refund_no' => $order_info['refund_sn'],
                'refund_fee'    => 1,
                'total_fee'     => 1,
               /* 'refund_fee'    => $order_info['intregral'] * 100,
                'total_fee'     => $order_info['intregral'] * 100,*/
            );
            ksort($refund_data);
            $refund_data['sign'] = makeSign($refund_data);
            $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
            //请求数据
            $xmldata = array2xml($refund_data);
            $res = $this->curl_post_refund($url, $xmldata);
            if (!$res) {
                return ['code'=>0,'message'=>"can't connect to service"];
            }
            $content = xml2array($res);
            if($content['RETURN_CODE'] == 'FAIL' || $content['RESULT_CODE'] == 'FAIL'){
                return ['code'=>0,'message'=>$content['ERR_CODE_DES']];
            }
            if ($content['RESULT_CODE'] == 'SUCCESS') {
                return ['code'=>1,'message'=>'ok'];
            }
        } else if ($order_info['pay_integory'] != 1) {
            return ['code'=>2,'message'=>'该订单是线下支付,请联系商家取消订单'];
        }
    }

    private function curl_post_refund($url = '', $data = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);


        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        //证书
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,1);//证书检查
        curl_setopt($curl,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($curl,CURLOPT_SSLCERT,ROOT_PATH.'/application/weixin/apiclient_cert.pem');
        curl_setopt($curl,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($curl,CURLOPT_SSLKEY,ROOT_PATH.'/application/weixin/apiclient_key.pem');

        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * 确认收货
     * @param string $id
     * @param string $order_type
     * @return \think\response\Json
     */
    public function sureOrder($id = '', $order_type = '')
    {
        if (!$id || !$order_type) {
            return $this->renderError('参数错误');
        }
        if ($order_type == 1) {
            $orderModel = new OrderModel();
        } else {
            $orderModel = new ActOrderModel();
        }
        if ($orderModel->save(['status'=>5],['id'=>$id])){
            return $this->renderSuccess(['message'=>'操作成功']);
        } else {
            return $this->renderError('操作失败');
        }
    }
}