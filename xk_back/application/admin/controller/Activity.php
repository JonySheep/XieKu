<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use app\admin\model\Activity as ActivityModel;
use think\Request;
use think\Validate;
use think\Db;
use app\admin\model\ActivityBanner;



/**
 * 活动管理
 */
class Activity extends Admin
{

	/**
	 * 活动列表
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-30T18:49:00+0800
	 * @return   [type]                   [description]
	 */
	function activityIndex()
	{
		$list = ActivityModel::all(function($query){
					$query->where([ 'is_valid'=>1 ]);
				});
		$this->assign('list',$list);
		return view();
	}

	/**
	 * 新增活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-30T18:54:43+0800
	 * @param    Request                  $request [description]
	 */
	function addActivity(Request $request)
	{	
		if( $request->isPost() )
		{	
			//参数验证
			$validate = Validate::make(
				[
					'activity_name'		=> 'require',
					'places'			=> 'require|number',
					'price'				=> 'require|number',
					'integral'			=> 'require|number',
					'editorValue'		=> 'require',
					'start_time'		=> 'require',
					'end_time'			=> 'require',
					'city_particulars'	=> 'require',
					'activity_img'		=> 'require'
				]
			);
			$postParam = trimArray($request->only(['activity_name','places','price','integral','editorValue','start_time','end_time','city_particulars','activity_img']));
			if( !$validate->check($request->param()) )
				return $this->renderError($validate->getError());

			if( !$request->param('id') )
			{
				if( ActivityModel::where([ 'activity_name'=>$postParam['activity_name'] ])->find() )
					return $this->renderError('不可重复添加活动');
			}else{
				//判断活动是否开始
			}

			//入库参数组装
			$data = [];
			$postParam['particulars'] = $postParam['editorValue'];
			unset($postParam['editorValue']);
			$postParam['residue_places']	= $postParam['places'];
			$postParam['start_time']	= strtotime($postParam['start_time']);
			$postParam['end_time']	= strtotime($postParam['end_time']);

			if( $request->param('id') )
			{	
				if($postParam['activity_img'] == 'true')
				{
					 unset($postParam['activity_img']);
				} 
				ActivityModel::where($request->only(['id']))->update($postParam);
			}else{
				ActivityModel::create($postParam);
			}

			return $this->renderSuccess('操作成功');
		}

		if( $request->param('id') )
		{	
			$data = ActivityModel::find($request->param('id'));
			$this->assign('data',$data);	
		} 
		return view();
	}

	/**
	 * 删除活动
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-31T10:49:53+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function deleteActivity(Request $request)
	{
		$validate = Validate::make(
			[
				'id'	=> 'require'
			]
		);
		if( !$validate->check($request->param()) )
			return $this->renderError($validate->getError());

		$data = Db::name('activity')->where($request->only(['id']))->find();   
	 
		@unlink(ROOT_PATH . 'public' . DS .$data['activity_img']);
		ActivityModel::where($request->only(['id']))->delete();

		return $this->renderSuccess('操作成功');
	}

	/**
	 * 活动banner 
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T10:27:36+0800
	 * @return   [type]                   [description]
	 */
	function activityBanner(Request $request)
	{	
		$list = ActivityBanner::all(function($query){
					$query->where(['is_valid'=>1])->order('weight','desc');
				});

		$this->assign('list',$list);
		return view();
	}

	/**
	 * 新增活动banner
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T11:19:15+0800
	 */
	function addActivityBanner(Request $request)
	{	
		if( $request->isPost() )
		{	
			//参数验证
			$validate = Validate::make(
				[
					'activity_banner_name'	=> 'require',
					'activity_img'			=> 'require',
					'weight'				=> 'require'
				]
			);
			$postParam = trimArray($request->only(['activity_banner_name','activity_img','weight']));
			if( !$validate->check( $postParam ) )
				return $this->renderError($validate->getError());

			ActivityBanner::create($postParam);

			return $this->renderSuccess([ 'rsf'=>'操作成功' ]);
		}

		return view();
	}

	/**
	 * 删除banner
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T13:10:14+0800
	 * @return   [type]                   [description]
	 */
	function deleteActivityBanner(Request $request)
	{
		$validate = Validate::make(
			[
				'id'	=> 'require'
			]
		);
		$postParam = $request->only([ 'id' ]);
		if( !$validate->check($postParam) )
			return $this->renderError($validate->getError());

		$data = Db::name('activity_banner')->where($request->only(['id']))->find();   
	 
		unlink(ROOT_PATH . 'public' . DS .$data['activity_img']);
		ActivityBanner::where($request->only(['id']))->delete();
		return $this->renderSuccess([ 'rsf'=>'操作成功' ]);

	}

}	