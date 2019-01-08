<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 用户表
 */
class Users extends Base
{
	protected $name = 'users';
    protected $autoWriteTimestamp = true;
    protected $resultSetType = 'collection';

	/**
	 * [coupon 关联优惠券表]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-18T11:28:28+0800
	 * @return   [type]                   [description]
	 */
	function coupon()
	{
		return $this->belongsToMany('Coupon','swa_users_coupon');
	}

	/**
	 * [getAvatarUrlAttr 获取用户头像]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-25T14:11:48+0800
	 * @param    [type]                   $avatarUrl [description]
	 * @return   [type]                              [description]
	 */
	function getAvatarUrlAttr($avatarUrl)
	{
		return $avatarUrl ? request()->domain().$avatarUrl : '';
	}

}