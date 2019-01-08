<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 活动banner表
 */
class ActivityBanner extends Base
{
	protected $name = 'activity_banner';


	protected $autoWriteTimestamp = true;

	
	/**
	 * 获取图片
	 * @Author   yangxiaogang
	 * @DateTime 2018-08-01T13:19:54+0800
	 * @return   [type]                   [description]
	 */
	function getActivityImgAttr($activity_img)
	{
		return $activity_img ? request()->domain().$activity_img : '';
	}

}