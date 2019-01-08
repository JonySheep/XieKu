<?php
namespace app\api\controller;
use app\index\controller\CoreController;
use app\index\model\Goods as GoodsModel;
use app\index\model\UsersLike as userLike;
use app\index\model\Store as Store;
use app\index\model\CategoryAttribute as Attr;
use think\Db;


/**
 * File: Index.php
 * User: XljBearSoft
 * Date: 2017-08-07
 * Time: 11:38
 */
class Goods extends CoreController
{
    public function index(){
        $goods = GoodsModel::all(function ($q){
            $q->order("sale desc,sort asc , id desc");
        });

        $this->showMessage(1,$goods);

    }

    public function  detail(){
        $id = $_GET['id'];
        $goods = GoodsModel::get($id);
        $data = [];
        if($goods->Attribute){
            foreach ($goods->Attribute as $value){
                $attr = Attr::get($value['attributeid']);
                $value['children'] = explode('#',$attr['value']);
                $value['name'] = $attr['name'];
                $value['choose'] =0;
                $data[] = $value;
            }
            $goods['attribute']  = $data;

        }
        if ($goods['sale'] == '') {
            $goods['sale'] = 0;
        }
        $goods['detail'] = str_replace('/data/images/upload','http://'.$_SERVER['HTTP_HOST'].'/data/images/upload',$goods['detail'] );
        $goods['share_token'] = input('share_token','','trim');
        if  ($goods['spec'] != '') {
            $spec = explode("\n",$goods['spec']);
            foreach ($spec as $k => &$v) {
                $attr = explode('|#|',$v);
                $v = [
                    'name' => $attr[0],
                    'value' => $attr[1]
                ];
            }
            $goods['spec'] = $spec;
        }

        $this->showMessage(1,$goods);
    }

    public function addLike(){
        $data = $_POST;

        $goods = GoodsModel::get(function ($q)use ($data){
            $q->where(['id'=>$data['id']])->order("sale desc,sort asc , id desc");
        });

        if(!$goods){
            $this->showMessage(2,'商品不存在或已下架');
        }
        $like = userLike::get(function ($q)use ($data){
            $q->where(['uid'=>$data['uid'],'goods_id'=>$data['id']])->order(" id desc");
        });

        if($like){
            $rs = userLike::get($like['id']);
            $rs->delete();
            $this->showMessage(1,'取消关注');
        }else{
            userLike::insert(['uid'=>$data['uid'],'goods_id'=>$data['id']]);
            $this->showMessage(1,'已关注');
        }

    }

    public function checkStock(){
        if(!isset($_GET['id'])|| !$_GET['id']){
            $this->showMessage(2,'','商品不存在或已下架');
        }
        $goods = GoodsModel::get($_GET['id']);

        if(!$goods['available']){
            $this->showMessage(2,'','商品不存在或已下架');
        }

        if($goods['stock']<$_GET['number']){
            $this->showMessage(2,'','商品库存不足');
        }
        $this->showMessage(1,'ok','');


    }

    /**
     * 商家列表，按距离排序
     * @throws \think\exception\DbException
     */
    public function storeList(){
        $longitude = input('longitude', '' ,'htmlspecialchars');  //经度
        $latitude = input('latitude', '' ,'htmlspecialchars');  //纬度
        if(empty($longitude) || empty($latitude)){
            $this->showMessage(2,'','参数错误');
        }
        $store = Store::all();
        $store = $store ? $store->toArray() : [];
        foreach ($store as $key => &$item){
            if($item['lat'] && $item['lng']){
                $item['distance'] = GetDistance($item['lat'], $item['lng'], $latitude, $longitude);
                $item['distance'] = ((int)($item['distance']*10))/10;
                $item['distance'] = $item['distance'].'千米';
            }else{
                $item['distance'] = '未知';
            }
            $distance[$key] = $item['distance'];
        }
        array_multisort($distance, SORT_ASC, $store);

        foreach ($store as $k => $value){
            if($k >= 20)  unset($store[$k]);
        }
        $this->showMessage(1,$store);
    }



    public function storeLocation(){
        $store = Store::get($_GET['id']);
        $store->save(['lat'=>$_GET['lat'],'lng'=>$_GET['lng']]);

        $this->showMessage(1,$store);
    }

}