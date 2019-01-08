<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 10:25
 */

namespace app\api\validate;


use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'goodsID' => 'require', //商品id
        'number' => 'require', //商品数量
        'uid' => 'require', //用户uid
        'goods_attribute' => 'require',//商品属性

        'address' => 'require',
        'shipping_way' => 'require',
        'pay_integory' => 'require',
        'store_id' => 'require',
    ];

    protected $message = [
        'goodsID.require' => '缺少商品id', //商品id
        'number.require' => '缺少商品数量', //商品数量
        'uid.require' => '缺少uid', //用户uid
        'goods_attribute.require' => '缺少商品属性',//商品属性

        'address.require' => '缺少地址id',
        'shipping_way.require' => '缺少配送方式',
        'pay_integeory.require' => '缺少支付方式',
        'store_id.require' => '缺少店铺id',
    ];

    protected $scene = [
        'add' => ['goodsID', 'number', 'uid'],
        'update' => ['address', 'shipping_way', 'pay_integeory', ],
    ];
}