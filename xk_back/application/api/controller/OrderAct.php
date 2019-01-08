<?php
/**
 * File: Page.php
 * User: Admin
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\api\controller;


use app\index\controller\CoreController;
use think\Db;
use think\Exception;
use app\admin\model\Activity as ActivityModel;
use app\index\model\Users as UsersModel;
use  app\admin\model\UsersActivityConversion as OrderModel;
use app\index\model\Address as AddressModel;
use app\admin\model\Integral as IntegralModel;

/**
 * 活动订单接口
 * Class Order
 * @package app\m\controller
 */
class OrderAct extends CoreController
{
    /**
     * 创建订单
     * @throws
     */
     public function createOrder()
    {
        //创建订单
        $post_data = [
            'activity_id' => input('activity_id',0,'intval'), //商品id
            'openid' => input('openid','','trim'), //用户open_id
        ];
        if (!$post_data['openid'] || $post_data['activity_id'] == 0) {
            return $this->renderError('参数错误');
        }
        //商品信息
        $goods_info = ActivityModel::get($post_data['activity_id']);
        if ($goods_info['residue_places'] <= 0) {
            return $this->renderError('非常遗憾！活动参与人数已满');
        }
        $uid = getUidByOpenid($post_data['openid']);
        if (!$uid) {
            return $this->renderError('未找到用户');
        }
        unset($post_data['openid']);
        $post_data_c = [
            'users_id' => $uid,
            'order_number' => 'JF-'.$uid.rand(100000,999999),//订单号
            'goods_title' => $goods_info['activity_name'],
            'goods_images' => $goods_info['activity_img'],
            'goods_ingegory' => $goods_info['integral'],
            'intregral' => $goods_info['integral'] * 1,
            'create_time'=>time(),
            'update_time' => time(),
            'status' => 0,
        ];
        $goods_list = [];
        $goods_list[] = [
            'goods_title' => $goods_info['activity_name'],
            'goods_images' => $goods_info['activity_img'],
            'goods_number' => 1,
            'goods_ingegory' => $goods_info['integral'],
            'total_integory' => $goods_info['integral'] * 1,
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
     * 提交订单
     * @throws \think\exception\DbException
     */
    public function payOrder()
    {
        $id = input('id',0,'intval');
        if ($id <= 0) {
            return $this->renderError('参数错误');
        }
        $post_data = [
            'users_site_id' => input('users_site_id','','intval'),//地址id
            'remark' => input('remark','','trim'),//备注
            'shipping_way' => input('shipping_way','','intval'), //配送方式
        ];
        if ($post_data['users_site_id'] == 0 || empty($post_data['shipping_way'])) {
            return $this->renderError('参数错误');
        }
        $order_info = OrderModel::get($id);
        //活动信息
        $act_info = ActivityModel::get($order_info['activity_id']);
        if ($act_info['residue_places'] <= 0) {
            return $this->renderError('非常遗憾！活动参与人数已满');
        }
        if ( time() > strtotime($act_info['end_time'])) {
            return $this->renderError('非常遗憾！活动已结束');
        }
        //判断用户积分是否够支付
        $user_integral = UsersModel::where('id',$order_info['users_id'])->value('integral');
        if ($user_integral < $order_info['intregral']) {
            return $this->renderError('积分不足');
        }
        $address_info = AddressModel::get($post_data['users_site_id']);
        $post_data_c = [
            'address_detail' => $address_info['address'],
            'address_name'  => $address_info['fullname'],
            'address_tel' => $address_info['mobile'],
            'status'    => 1,
        ];
        $post_data = array_merge($post_data, $post_data_c);
        Db::startTrans();
        try {
            //减少用户积分
            $res = UsersModel::where('id',$order_info['users_id'])->setDec('integral',(int)$order_info['intregral']);
            if (!$res) {
                throw new Exception('扣除积分失败');
            }
            if (!OrderModel::update($post_data,['id'=>$id])) {
                throw new Exception('订单提交失败');
            }
            //生成积分记录
            $integral_info = [
                'users_id' => $order_info['users_id'],
                'content' => '参与活动',
                'integral' => -$order_info['intregral'],
                'create_time' => time(),
                'update_time' => time(),
                'left_users_id' => 0,
                'activity_id' => $order_info['activity_id'],
                'explain' => json_encode(['activity_img'=>$act_info['activity_img'],'order_number'=>$order_info['order_number']]),
            ];
            if (!IntegralModel::insert($integral_info)) {
                throw new Exception('积分提交失败');
            }
            //减少活动剩余人数
            ActivityModel::where('id',$order_info['activity_id'])->setDec('residue_places',$order_info['goods_number']);
            Db::commit();
            return $this->renderSuccess('积分付款成功');
        } catch(Exception $e) {
            Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 查询物流
     * @param string $order_number
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLogistics($order_number = '')
    {
        if (!$order_number) {
            return $this->renderError('参数错误');
        }
        $order_info = OrderModel::where('order_number',$order_number)->field('logistics_id as com,logistics_number as order_sn')->find();
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
        $send_time = OrderModel::where('logistics_number',$order_sn)->value('send_time');
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
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function goPayOrder($id = '')
    {
        if (!$id) {
            return $this->renderError('参数错误');
        }
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
        ];
        return $this->renderSuccess($return);
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
        $order_info = OrderModel::where('id',$id)->field('intregral,reduce_integory,shipping_way,transaction_id,refund_sn')->find();
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
        }
        if ($order_info['shipping_way'] != 1) {
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
     * @return \think\response\Json
     */
    public function sureOrder($id = '')
    {
        if (!$id) {
            return $this->renderError('参数错误');
        }
        $orderModel = new OrderModel();
        if ($orderModel->save(['status'=>5],['id'=>$id])){
            return $this->renderSuccess(['message'=>'操作成功']);
        } else {
            return $this->renderError('操作失败');
        }
    }
}