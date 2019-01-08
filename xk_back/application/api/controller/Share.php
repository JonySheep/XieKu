<?php
/**
 * File: Page.php
 * User: Admin
 * Date: 2017-08-07
 * Time: 11:44
 */

namespace app\api\controller;


use app\admin\model\IntegralRule;
use app\index\controller\CoreController;
use app\index\model\Category as CategoryModel;
use app\index\model\Goods as GoodsModel;
use app\index\model\CategoryAttribute as CategoryAttributeModel;
use app\index\model\Share as ShareModel;
/**
 * 订单接口
 * Class Order
 * @package app\m\controller
 */
class Share extends CoreController
{
    /**
     * 分享页面--菜单
     * @throws
     */
     public function shareCategory()
    {
        $cate = CategoryModel::field('id,name')->select();

        foreach ($cate as $k => &$v) {
            $v['sub_menu'] = CategoryAttributeModel::where('categoryid',$v['id'])->field('name as sunb_name')->select();
        }
        return $this->renderSuccess($cate);
    }

    /**
     * 分享页面--内容列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function shareList()
    {
        $id = input('id',0,'intval');
        if ($id < 0) {
            return $this->renderError('参数错误');
        }
        if ($id == 0) {
            $map = [];
        } else {
            $map['g.categoryid'] = $id;
        }
        $map['g.buy_room_id'] = ['neq',0];
        $goodsModel = new GoodsModel();
        $list = $goodsModel
            ->alias('g')
            ->field('g.id, g.title, g.cover1, g.price, g.sale, i.integral, i.title as room_name')
            ->join('IntegralRule i','g.buy_room_id = i.id','LEFT')
            ->where($map)
            ->order('id desc')
            ->select();
        foreach ($list as $k => &$v) {
            if (empty($v['integral'])) {
                $v['integral'] = 0;
            }
            if (empty($v['sale'])) {
                $v['sale'] = 0;
            }
            if (empty($v['room_name'])) {
                $v['room_name'] = '';
            }
        }

        return $this->renderSuccess($list);
    }

    /**
     * @param string $openid
     * @param string $goods_id
     * @return \think\response\Json
     */
    public function getShareUrl($openid = '', $goods_id = '')
    {
        if (!$openid || !$goods_id) {
            return $this->renderError('参数错误');
        }
        $uid = getUidByOpenid($openid);
        if (!$uid) {
            return $this->renderError('用户错误');
        }
        $integral_id = GoodsModel::where('id',$goods_id)->value('buy_room_id');
        $integral = IntegralRule::where('id',$integral_id)->value('integral');
        if (!$integral) $integral = 0;
        $share_token = MD5(time().$uid.$goods_id);
        $data = [
            'uid' => $uid,
            'goods_id' => $goods_id,
            'integral' => $integral,
            'token' => $share_token,
        ];
        $shareModel = new ShareModel();
        if ($shareModel->save($data)) {
            return $this->renderSuccess(['goods_id'=>$goods_id,'share_token'=>$share_token]);
        } else {
            return $this->renderError('分享失败');
        }
    }
}