<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 用户地址表
 */
class UsersSite extends Base
{
	protected $name = 'users_site';


	protected $autoWriteTimeStamp = true;



	//设置默认地址
	static function isDefaultSet($usersSiteIds)
	{
		self::where([ 'id'=>['in',$usersSiteIds] ])->setField('is_default',0);
	}

}