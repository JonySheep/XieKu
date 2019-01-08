<?php
namespace app\admin\model;

use app\admin\model\Base;


/**
 * 品牌
 */
class Brand extends Base
{
	protected $name = 'brand';

	/**
	 * [getLogoAttr 获取logo]
	 * @author yangxiaogang 2018-07-22
	 * @param  [type] $logo [description]
	 * @return [type]       [description]
	 */
	function getLogoAttr($logo)
	{
		return $logo ? request()->domain().$logo : '';
	}
}