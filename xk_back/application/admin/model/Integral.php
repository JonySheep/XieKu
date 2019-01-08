<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 积分赠送说明
 */
class Integral extends Base
{
	protected $name = 'integral';

	protected $autoWriteTimestamp = true;

	/**
	 * [getCreateTimeAttr 获取时间]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-23T19:47:30+0800
	 * @param    [type]                   $create_time [description]
	 * @return   [type]                                [description]
	 */
	function getCreateTimeAttr($create_time)
	{
		return date('Y-m-d',$create_time);
	}
}