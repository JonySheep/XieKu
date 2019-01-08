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
use app\index\model\Share as ShareModel;
use app\admin\model\Integral as IntegralModel;
/**
 * 支付接口
 * Class Order
 * @package app\m\controller
 */
class Pay extends CoreController
{
    protected $config = '';

    /**
     * 预支付请求接口(POST)
     * @param string $openid openid
     * @param string $body 商品简单描述
     * @param string $order_sn 订单编号
     * @param string $total_fee 金额
     * @return \think\response\Json
     */
    public function prepay($openid = '', $order_sn = '', $total_fee = ''){
        if (!$openid || !$order_sn || !$total_fee) {
            return $this->renderError('参数错误');
        }
        $config = config('wx_pay');
        //统一下单参数构造
        $unifiedorder = array(
            'appid'			=> $config['appid'],
            'body'			=> '斜厍--支付',
            'mch_id'		=> $config['pay_mchid'],
            'nonce_str'		=> getNonceStr(),
            'notify_url'	=> 'https://'.$_SERVER['HTTP_HOST'].'/index.php/api/Pay/notify',
            'openid'		=> $openid,
            'out_trade_no'	=> $order_sn.'_'.rand(1000,9999),
//            'spbill_create_ip'	=> '192.168.0.38',
            'spbill_create_ip'	=> getIP(),
            //'total_fee'		=> $total_fee * 100,
            'total_fee' => 1,
            'trade_type'	=> 'JSAPI'
        );
        $unifiedorder['sign'] = makeSign($unifiedorder);
        //请求数据
        $xmldata = array2xml($unifiedorder);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = http_request($url, $xmldata);
        if(!$res){
            return $this->renderError("Can't connect the server");
        }
        $content = xml2array($res);
        if($content['RETURN_CODE'] == 'FAIL'){
            return $this->renderError($content['RETURN_MSG']);
        }
        return $this->renderSuccess($content);
    }

    /**
     * 进行支付接口(POST)
     * @param string $prepay_id 预支付ID(调用prepay()方法之后的返回数据中获取)
     * @return  json的数据
     */
    public function pay($prepay_id = ''){
        $config = config('wx_pay');
        if (!$prepay_id) {
            return $this->renderError('参数错误');
        }

        $data = array(
            'appId'		=> $config['appid'],
            'timeStamp'	=> time(),
            'nonceStr'	=> getNonceStr(),
            'package'	=> 'prepay_id='.$prepay_id,
            'signType'	=> 'MD5'
        );
        ksort($data);
        $data['paySign'] = makeSign($data);

        return $this->renderSuccess($data);
    }

    //微信支付异步回调
    public function notify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        //file_put_contents(ROOT_PATH.'/log.txt',$xml,FILE_APPEND);
        //将服务器返回的XML数据转化为数组
        $data = $this->xmlToArray($xml);
        // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        ksort($data);
        $sign = makeSign($data);

        // 判断签名是否正确  判断支付状态
        if ( ($sign===$data_sign) && ($data['return_code']=='SUCCESS') ) {
            $result = $data;
            //获取服务器返回的数据
            $order_sn = $data['out_trade_no'];			//订单单号
            $openid = $data['openid'];					//付款人openID
            $total_fee = $data['total_fee'];			//付款金额
            $transaction_id = $data['transaction_id']; 	//微信支付流水号

            //更新订单
            $order_sn = substr($order_sn,0,11);
            $order_status = OrderModel::where('order_number',$order_sn)->value('status');
            if ($order_status == 0) {
                OrderModel::update(['status'=>1,'pay_time'=>time(),'transaction_id'=>$transaction_id],['order_number'=>$order_sn]);
                $order_info = OrderModel::where('order_number',$order_sn)->find();
                UsersModel::where('id',$order_info['uid'])->setInc('consumption_money',$total_fee/100);
                //是否返积分
                $share_token = OrderModel::where('order_number',$order_sn)->value('share_token');
                if ($share_token) {
                    $share_info = ShareModel::where('token',$share_token)->field('uid, integral, goods_id')->find();
                    UsersModel::where('id',$share_info['uid'])->setInc('integral',$share_info['integral']);
                    ShareModel::where('token',$share_token)->setInc('buy_number');
                    //积分记录
                    $integral_info = [
                        'users_id' => $share_info['uid'],
                        'content' => '分享商品',
                        'integral' => $share_info['integral'],
                        'create_time' => time(),
                        'update_time' => time(),
                        'left_users_id' => $order_info['uid'],
                        'activity_id' => 0,
                        'explain' => json_encode(['goods_id'=>$share_info['goods_id'],'goods_img'=>GoodsModel::where('id',$share_info['goods_id'])->value('cover1')]),
                    ];
                    IntegralModel::insert($integral_info);
                }
            }
            echo 'SUCCESS';die;
        }else{
            $result = false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }



    function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
}