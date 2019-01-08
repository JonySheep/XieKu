<?php
namespace app\api\controller;

use app\index\controller\CoreController;
use think\Request;
use think\Validate;
use app\admin\model\Coupon as CouponModel;
use app\admin\model\UsersCoupon;
use app\admin\model\UsersModel;

/**
 * 优惠券
 */
class Coupon extends CoreController
{
		
	/**
	 * [getCoupon 获取优惠券]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T17:34:59+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function  getCoupon(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'users_id'	=> 'require',
				'coupon_id'	=> 'require'
			],
			[
				'users_id.require'	=> '用户ID必须',
				'coupon_id.require'	=> '优惠券ID必须'
			]
		);
		if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() );

		//优惠券验证
		$couponData = CouponModel::find( $request->param('coupon_id') );
		if( ! $couponData )
			return $this->renderError('该优惠券不存在');
		if( ! $couponData->is_valid === 0 )
			return $this->renderError('该优惠券已被下架');
		if( strtotime($couponData->end_time) < time() )
			return $this->renderError('该优惠券已过期，不可领取');

		//领取情况验证
		$usersCoupon = UsersCoupon::where($request->only([ 'users_id', 'coupon_id' ]))->order('create_time','desc')->find();
		if( $usersCoupon )
		{	
			//如果该优惠券本月有被领取
			if( date('m',strtotime($usersCoupon->create_time)) === date('m',time()) )
				return $this->renderError('该优惠券您本月已领取，不可重复领取');
		}

		//入库
		try {
			if( ! UsersCoupon::create( $request->only(['users_id','coupon_id']) ) )
			{
				return $this->renderError('领取失败，请重试');
			}else{
				CouponModel::where([ 'id'=>$request->param('coupon_id') ])->setDec('remain_number');
				return $this->renderSuccess([ 'rsf'=>'操作成功' ]);
			}
			
		} catch (\Exception $e) {
			return $this->renderError($e->getMessage());
		}
	}

	/**
	 * [couponList 优惠券列表]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-20T13:20:14+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function getCouponList(Request $request)
	{	
		$list = CouponModel::all(function($query){
			$query->where([ 'is_valid'=>1, 'end_time'=>['>',time()]  ]);
		});

		$couponIds = [];
		if( $usersId = $request->param('users_id') )
		{
			$couponIds = UsersCoupon::where([ 'users_id'=>$usersId ])->column('coupon_id');
		}

		foreach ($list as $key => &$value) 
		{
			$value->is_use =  in_array($value->id, $couponIds) ? 1 : 0;
		}
		unset($value);
		return $this->renderSuccess($list);
	}
	
}