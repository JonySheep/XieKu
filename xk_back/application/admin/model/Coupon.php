<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 优惠券数据模型
 */
class Coupon extends Base
{

	protected $name = 'coupon';

	protected $autoWriteTimestamp = true;

	/**
	 * [users 对应关联]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T09:26:06+0800
	 * @return   [type]                   [description]
	 */
	function users()
	{
		return $this->belongsToMany('Users','swa_users_coupon');
	}

	/**
	 * [getBackImgAttr 获取图片]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T16:14:18+0800
	 * @return   [type]                   [description]
	 */
	function getBackImgAttr($back_img)
	{
		return $back_img ? request()->domain().$back_img : '';
	}

	/**
	 * [getEndTimeAttr 获取有效期时间]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T16:55:49+0800
	 * @param    [type]                   $end_time [description]
	 * @return   [type]                             [description]
	 */
	function getEndTimeAttr($end_time)
	{
		return date('Y-m-d',$end_time);
	}

	/**
	 * [getCreateTimeAttr 入库时间]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T16:56:30+0800
	 * @param    [type]                   $create_time [description]
	 * @return   [type]                                [description]
	 */
	function getCreateTimeAttr($create_time)
	{
		return date('Y-m-d H:i:s',$create_time);
	}

	/**
	 * [getCreateTimeAttr 更新时间]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-19T16:56:30+0800
	 * @param    [type]                   $create_time [description]
	 * @return   [type]                                [description]
	 */
	function getUpdateTimeAttr($update_time)
	{
		return date('Y-m-d H:i:s',$update_time);
	}


}