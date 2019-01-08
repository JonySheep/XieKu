<?php
namespace app\admin\model;


/**
 * 管理员用户表
 */
class Admin extends Base
{
	protected $name = "admin";
    protected $autoWriteTimestamp = true;
    protected $resultSetType = 'collection';

    /**
     * 获取管理员列表（超级管理员除外）
     * @author huanghao
     * @param $map
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
	public static function getList($map){
	    $list = self::where($map)->select();
	    return $list ? $list->toArray() : [] ;
    }

    /**
     * 获取管理员详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getInfo($id){
	    $info = self::find($id);
        return $info ? $info->toArray() : [] ;
    }



}