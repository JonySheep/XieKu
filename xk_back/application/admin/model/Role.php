<?php
namespace app\admin\model;

use app\admin\model\Base;

/**
 * 管理员角色表
 */
class Role extends Base
{
	protected $name = 'role';

	protected $autoWriteTimestamp = true;
	
}