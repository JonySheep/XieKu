<?php
namespace app\admin\model;


use app\admin\model\Base;

/**
 * 活动商品表
 */
class Activity extends Base
{
	protected $name = "activity";

	protected $autoWriteTimestamp = true;

	/**	
	 * 获取封面图片
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-30T19:36:39+0800
	 * @return   [type]                   [description]
	 */
	function getActivityImgAttr($activity_img)
	{
		return $activity_img ? request()->domain().$activity_img : '';
	}

	/**
	 * 获取时间
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-30T19:45:56+0800
	 * @return   [type]                   [description]
	 */
	function getStartTimeAttr($start_time)
	{
		return date('Y-m-d',$start_time);
	}

	/**
	 * 获取活动结束时间
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-30T19:46:32+0800
	 * @return   [type]                   [description]
	 */
	function getEndTimeAttr($end_time)
	{
		return date('Y-m-d',$end_time);
	}
 		
	/**
	 *  
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-31T13:39:36+0800
	 * @return   [type]                   [description]
	 */
 	function getParticularsAttr($particulars)
 	{	

 		$particulars = htmlspecialchars_decode($particulars);
 		return str_replace('/data/images/upload',request()->domain().'/data/images/upload',$particulars );
 	}



}