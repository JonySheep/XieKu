<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 商品数据模型
 */
class Goods extends Base
{
	protected $name = 'goods';

	/**
	 * [getCover1Attr 获取封面图]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $cover1 [description]
	 * @return [type]         [description]
	 */
	function getCover1Attr($cover1)
	{
		return $cover1 ? request()->domain().$cover1 : '';
	}

	/**
	 * [getCover1Attr 获取封面图]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $cover1 [description]
	 * @return [type]         [description]
	 */
	function getCover2Attr($cover2)
	{
		return $cover2 ? request()->domain().$cover2 : '';
	}

	/**
	 * [getCover1Attr 获取封面图]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $cover1 [description]
	 * @return [type]         [description]
	 */
	function getCover3Attr($cover3)
	{
		return $cover3 ? request()->domain().$cover3 : '';
	}

	/**
	 * [getCover1Attr 获取封面图]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $cover1 [description]
	 * @return [type]         [description]
	 */
	function getCover4Attr($cover4)
	{
		return $cover4 ? request()->domain().$cover4 : '';
	}

	/**
	 * [getCover1Attr 获取封面图]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $cover1 [description]
	 * @return [type]         [description]
	 */
	function getCover5Attr($cover5)
	{
		return $cover5 ? request()->domain().$cover5 : '';
	}
}