<?php
namespace app\api\controller;

use app\index\controller\CoreController;
use app\admin\model\Activity as ActivityModel;
use think\Request;
use think\Validate;
use app\admin\model\ActivityBanner;
use app\admin\model\UsersActivityCollect;
use app\admin\model\Users;
use app\admin\model\UsersActivityConversion;
use app\admin\model\Integral;

/**
 * 活动控制器
 */
class Activity extends CoreController
{
	/**
	 * 获取活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T09:16:30+0800
	 * @return   [type]                   [description]
	 */
	function getActivity(Request $request)
	{	
		//参数验证
		$validate = Validate::make(
			[
				'genre'	=> 'require|in:1,2,3'   	//1 进行中 2 即将开始 3 已结束
			]
		);
		if( ! $validate->check($request->param()) )
			return $this->renderError($validate->getError());
		//条件
		$where = [ 'is_valid'=>1 ]; 

		switch ($genre = $request->param('genre')) {
			case 1:
				$where['start_time']	= ['<=',time()];
				$where['end_time']		= ['>=',time()];
				break;
			case 2:
				$where['start_time']	= ['between',[time(),time()+86400]]; 	//提前一天视为即将开始的活动
				break;
			default:
				$where['end_time']	= ['<=',time()];
				break;
		}		 	
	 
		$list = ActivityModel::where($where)->select();

		foreach ($list as $key => &$value) {
			//剩余时间 如果是正在进行
			$value->residueTime = $genre == 1 ? strtotime($value->end_time) - time() : ( $genre == 2 ? strtotime($value->start_time) - time() : '已结束' );
			 
		}
		unset($value);

		return $this->renderSuccess($list);
		 	
	}

	/**
	 * 获取活动banner
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T13:14:20+0800
	 * @return   [type]                   [description]
	 */
	function getActivityBanner(Request $request)
	{
		$list = ActivityBanner::all(function($query){
			$query->order('weight','desc');
		});

		return $this->renderSuccess( i_array_column( collection( $list )->toArray(), 'activity_img') );
	}

	/**
	 * 获取单个活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-03T09:56:13+0800
	 * @return   [type]                   [description]
	 */
	function getActivityFind(Request $request)
	{	
		//参数验证
		$canshu = array( 'id' => 'require', 'users_id'	=> 'require' );
		$validate = Validate::make( $canshu	);
		$postParam = trimArray( $request->only([ 'id','users_id' ]) );
		
		if( !$validate->check($postParam,$canshu) )
			return $this->renderError($validate->getError());
	
		$data = ActivityModel::find($postParam['id']);

		//是否收藏
		$isCollect = UsersActivityCollect::where([ 'activity_id'=>$postParam['id'],'users_id'=>$request->param('users_id') ])->find() ? 1 : 0;
		$data['isCollect'] = $isCollect;


		return $this->renderSuccess($data);
	}

	/**
	 * 增加活动浏览量
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-03T10:04:42+0800
	 */
	function addActivityNumnber(Request $request)
	{	
		//参数验证
		$validate = Validate::make(
			[
				'id'	=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		ActivityModel::where($postParam)->setInc('pv');
		return $this->renderSuccess(['rsf'=>'操作成功']);

	}

	/**
	 * 活动收藏
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-04T16:26:30+0800
	 * @return   [type]                   [description]
	 */
	function activityCollect(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'activity_id'	=> 'require',
				'open_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'activity_id','open_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		//用户id
		$usersId = Users::where([ 'openid'=>$postParam['open_id'] ])->value('id');
		$insterData = [ 'users_id'=>$usersId,'activity_id'=>$postParam['activity_id'] ];
	 
		if( UsersActivityCollect::where($insterData)->find() )
		{	
			UsersActivityCollect::where($insterData)->delete();
			ActivityModel::where([ 'id'=>$postParam['activity_id'] ])->setDec('cn');
			return $this->renderSuccess([ 'rsf'=>'取消收藏成功' ]);
		}
		UsersActivityCollect::create($insterData);
		ActivityModel::where([ 'id'=>$postParam['activity_id'] ])->setInc('cn');
		return $this->renderSuccess([ 'rsf'=>'收藏成功' ]);
	}

	/**
	 * 活动商品兑换
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-04T18:06:01+0800
	 * @return   [type]                   [description]
	 */
	function activityConversion(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'activity_id'	=> 'require',
				'users_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'activity_id','users_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		$activityData = ActivityModel::find($postParam['activity_id']);
		$usersData 	  = Users::find($postParam['users_id']);
		if( !$activityData )
			return $this->renderError('活动不存在，请重新确认');
		if( strtotime($activityData->start_time) > time() )
			return $this->renderError('活动未开始，不能兑换');
		if( strtotime($activityData->end_time) < time() )
			return $this->renderError('活动已结束');

		if( $usersData->integral < $activityData->integral) 
			return $this->renderError('积分不足');
		//订单编号
		$postParam['indent_number']	= generate_code(7);

		$data = UsersActivityConversion::create($postParam);	//写入兑换记录 
		/*Users::where([ 'id'=>$postParam['users_id'] ])->setDec('integral',$activityData->integral);  	//减去积分*/
		//记录日志
		/*$data = Integral::create([
					'users_id'		=> $postParam['users_id'],
					'content'		=> '参加活动',
					'integral'		=> $activityData->integral,
					'explain'		=> $activityData->activity_img,
					'activity_id'	=> $postParam['activity_id']
				]);*/
		return $this->renderSuccess($data);
	}


	/**
	 * 我的活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-04T18:43:05+0800
	 * @return   [type]                   [description]
	 */
	function myActivity(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'users_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'users_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		$list = UsersActivityConversion::where($postParam)->select();
		$activityIds = i_array_column( collection($list)->toArray(), 'activity_id');
		$list = ActivityModel::where([ 'id'=>['in',$activityIds] ])->select();

		return $this->renderSuccess($list);
	}


	/**
	 * 我的活动收藏
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-05T17:39:30+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function myActivityCollect(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'users_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'users_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		$activityIds = UsersActivityCollect::where([ 'users_id'=>$postParam['users_id'] ])->column('activity_id');

		$activityList = ActivityModel::where([ 'id'=>['in',$activityIds] ])->select();
		return $this->renderSuccess($activityList);
	}

	/**
	 * 获取兑换的活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-16T14:41:15+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function getCActivity(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'activity_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'activity_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		
		$uac = UsersActivityConversion::find($postParam['activity_id']);

		$request->setParam('id',$uac->activity_id);
		$request->setParam('users_id',$uac->users_id);	

		return $this->getActivityFind($request);
	}

	/**
	 * 获取订单详情
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-23T10:53:27+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function getIndentDetails(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'activity_id'		=> 'require'
			]
		);
		$postParam = trimArray( $request->only([ 'activity_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		
		$uac = UsersActivityConversion::find($postParam['activity_id']);
 		
 		return $this->renderSuccess($uac);
	}

	/**
	 * 提交订单
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-23T11:21:02+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function sublimeIndent(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[	
				'users_activity_conversion_id'	=> 'require', 	//	订单ID
				'remark'		=> 'require',
				'users_site_id'		=> 'require',
			],
			[	'users_activity_conversion_id'	=> '订单不存在，请检查',
				'remark.require'	=> '订单备注必填',

			]
		);
		$postParam = trimArray( $request->only([ 'remark','users_site_id','users_activity_conversion_id' ]) );
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());
		 
		//写入订单
		UsersActivityConversion::where([ 'id'=>$postParam['users_activity_conversion_id']  ])->update([
			'remark'	=> $postParam['remark'],
			'users_site_id'	=> $postParam['users_site_id']
		]);

		//插入日志
		$logParam = UsersActivityConversion::find($postParam['users_activity_conversion_id']);
		//记录日志
		/*$data = Integral::create([
					'users_id'		=> $logParam->users_id,
					'content'		=> '参加活动',
					'integral'		=> $logParam->integral,
					'explain'		=> $activityData->activity_img,
					'activity_id'	=> $postParam['activity_id']
				]);*/

		//减去积分
		/*Users::where([ 'id'=>$postParam['users_id'] ])->setDec('integral',$activityData->integral);  	//减去积分*/

	 	return $this->renderSuccess($postParam);
	}


}