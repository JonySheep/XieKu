<?php
/**
 * File: Goods.php
 * User: XljBearSoft
 * Date: 2017-07-29
 * Time: 20:12
 */

namespace app\admin\controller;



use app\index\model\GoodsAttribute;
use think\Db;
use WebCache\WebCacheLib as Cache;
use app\index\model\Goods as GoodsModel;
use app\admin\model\IntegralRule as IntegralRuleModel;
class Goods extends Admin
{
    public function index(){
        $page = input('?post.page')?intval(input('post.page')):1;
        $keyword = input('?post.search')?trim(input('post.search')):"";
        if($page<1)$page = 1;
        $whereCondition = [];
        if($keyword!=""){
            $whereCondition['title'] = ['like',"%$keyword%"];
        }
        $GoodsM = new GoodsModel();
        $GoodsCount =  $GoodsM->where($whereCondition)->count();
        $PageLimit = 15;
        $PageCount = $GoodsCount / $PageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($page>$PageCount)$page = $PageCount;
        $GoodsList = $GoodsM->limit(($page - 1)*$PageLimit,$PageLimit)->where($whereCondition)->order("sort asc,id desc")->select();
        $this->assign("GoodsList",$GoodsList);
        $this->assign("PageCount",$PageCount);
        $this->assign("NowPage",$page);
        $this->assign("GoodsCount",$GoodsCount);
        $this->assign("Keyword",$keyword);
        return $this->fetch();
    }
    public function edit($id){
        $goods = GoodsModel::get($id);
        if(!$goods){
            return CreateInfoPage("很抱歉...","该商品不存在或已被删除!","返回",url('admin/goods/index'));
        }
        $category = Cache::Get("category");
        //2018.10.9 huanghao所加开始
        $roomList = IntegralRuleModel::getCateList($goods['categoryid']);  //房型列表
        $this->assign('roomList',$roomList);
        // 2018.10.9 huanghao所加 结束
        $brand = Cache::Get("brand");
        $this->assign('goods',$goods);
        $this->assign('categoryList',$category);
        $this->assign('categoryList_json',json_encode($category));
        $this->assign('brandList',$brand);
        $this->assign('editmode',true);
        $coverList = [];
        for($i=1;$i<=5;$i++){
            if($goods["cover$i"]!=null){
                $coverList[] = $goods["cover$i"];
            }
        }
        $this->assign('coverList',$coverList);
        $Attributes = $goods->Attribute;

        $AttributeList = [];
        if($Attributes!=null){
            foreach ($Attributes as $key=>$Attribute){
                $AttributeList[] = explode(',',$Attribute->value);
            }
        }
        $specList = [];
        if($goods->spec!=""){
            $specLine = explode("\n",$goods->spec);
            foreach ($specLine as $spec){
                $specList[] = explode("|#|",$spec);
            }
        }
        $this->assign('specList',$specList);
        $attribute_json = json_encode($AttributeList);
        $this->assign('attribute_json',$attribute_json);
        return $this->fetch('form');
    }

    /**
     * 获取房型列表
     * @author huanghao
     * @param $cate_id
     * @return mixed
     */
    public function getRoomList($cate_id)
    {
        $roomList = IntegralRuleModel::getCateList($cate_id);
        return json_encode($roomList);
    }


    public function delete($id){
        $goods = GoodsModel::get($id);
        if(!$goods){
            return CreateInfoPage("很抱歉...","该商品不存在或已被删除!","返回",url('admin/goods/index'));
        }else{
            $rootdir = ROOT_PATH . 'public' . DS;
            if(file_exists($rootdir.$goods->cover1))@unlink($rootdir.$goods->cover1);
            if(file_exists($rootdir.$goods->cover1))@unlink($rootdir.$goods->cover2);
            if(file_exists($rootdir.$goods->cover1))@unlink($rootdir.$goods->cover3);
            if(file_exists($rootdir.$goods->cover1))@unlink($rootdir.$goods->cover4);
            if(file_exists($rootdir.$goods->cover1))@unlink($rootdir.$goods->cover5);
            $Attributes = $goods->Attribute;
            if($Attributes!=null){
                foreach ($Attributes as $Attribute){
                    $Attribute->delete();
                }
            }
            $goods->delete();
            return CreateInfoPage("操作成功","商品已成功删除!","返回",url('admin/goods/index'));
        }
    }
    public function update(){
        if(request()->method()!="POST")return CreateInfoPage("发生错误...","无效的操作!","关闭","javascript:CloseTab();");
        $title = trim(input("post.title"));
        if($title==""){
            return CreateInfoPage("发生错误...","请输入商品标题!","返回","javascript:window.history.back();");
        }
        $goods_data['title'] = $title;
        $covers = isset($_POST['cover'])?$_POST['cover']:[];
        $cover_sort = isset($_POST['cover_sort'])?$_POST['cover_sort']:[];
        if(sizeof($covers)==0){
            return CreateInfoPage("发生错误...","请至少上传一张商品预览图!","返回","javascript:window.history.back();");
        }
        for($i=0;$i<sizeof($covers);$i++){
            $covers[$i] = trim(htmlspecialchars($covers[$i]));
            $cover_sort[$i] = intval($cover_sort[$i]);
        }
        array_multisort($cover_sort,SORT_ASC,$covers);
        $cover_size = sizeof($covers)<5?sizeof($covers):5;
        for ($i=0;$i<$cover_size;$i++){
            $goods_data['cover'.($i+1)] = $covers[$i];
        }
        $model = trim(input("post.model"));
        $goods_data['model'] = $model;
        $price = intval(input("post.price"));
        $goods_data['price'] = $price;
        //$integral = intval(input("post.integral"));
        $buy_room_id = intval(input("post.buy_room_id"));  //所属房型id 2018.10.9 huanghao所加开始
        $goods_data['buy_room_id'] = $buy_room_id;    //所属房型id 2018.10.9 huanghao所加开始
        //$goods_data['integral'] = $integral;
        $stock =   intval(input("post.stock"));
        $goods_data['stock'] = $stock;


        $brandid = intval(input("post.brandid"));
        if(!HasBrand($brandid)){
            return CreateInfoPage("发生错误...","商品品牌信息不正确!","返回","javascript:window.history.back();");
        }

        $goods_data['brandid'] = $brandid;
        $categoryid = intval(input("post.categoryid"));
        if(!HasCategory($categoryid)){
            return CreateInfoPage("发生错误...","商品分类信息不正确!","返回","javascript:window.history.back();");
        }
        $goods_data['categoryid'] = $categoryid;
        $spec_name = isset($_POST['spec_name'])?$_POST['spec_name']:[];
        $spec_value = isset($_POST['spec'])?$_POST['spec']:[];
        $spec_str = "";
        $spec = [];
        for($i=0;$i<sizeof($spec_name);$i++){
            $spec_name[$i] = trim(htmlspecialchars($spec_name[$i]));
            $spec_value[$i] = trim(htmlspecialchars($spec_value[$i]));
            if($spec_name[$i]!=""&&$spec_value[$i]!=""){
                $spec[] = [$spec_name[$i],$spec_value[$i]];
                $spec_str.= $spec_name[$i]."|#|" . $spec_value[$i]."\n";
            }
        }
        $spec_str = trim($spec_str,"\n");
        $goods_data['spec'] = $spec_str;
        $CategoryAttribute = [];
        $category_val = isset($_POST['category_val'])?$_POST['category_val']:[];
        $attribute_id = isset($_POST['attribute_id'])?$_POST['attribute_id']:[];
        for($i=0;$i<sizeof($category_val);$i++){
            $category_val[$i] = trim(htmlspecialchars($category_val[$i]));
            $attribute_id[$i] = intval($attribute_id[$i]);
            if($category_val[$i]==""){
                $CategoryAttribute[] = 0;
            }else{
                $CategoryAttribute[] = explode(",",$category_val[$i]);
            }
        }
        $goods_data['introduce'] = trim(input("post.introduce"));;
        $goods_data['detail'] = htmlspecialchars_decode(input("post.detail"));;
        $goods_data['customized'] = input("?post.customized")?1:0;
        $goods_data['recommended'] = input("?post.recommended")?1:0;
        $goods_data['available'] = input("?post.available")?0:1;
        $goods_data['sort'] = intval(input("post.sort"));

        if(input("?post.edit_id")){
            $gid = intval(input("post.edit_id"));
            $Goods = GoodsModel::get($gid);
            if($Goods==null){
                return CreateInfoPage("很抱歉...","该商品不存在或已被删除!","返回",url('admin/goods/index'));
            }
            $MoveFlag = false;
            if($Goods->categoryid!=$categoryid){
                $OriginId = $Goods->categoryid;
                $MoveFlag = true;
                $AttributeList = $Goods->Attribute;
                if($AttributeList!=null){
                    foreach ($AttributeList as $Attribute){
                        $Attribute->delete();
                    }
                }
            }
            if($Goods->brandid!=$goods_data['brandid']){
                $brandList = Cache::Get("brand");
                foreach ($brandList as $brand){
                    if($brand['id'] == $goods_data['brandid']){
                        $goods_data['brandavailable'] = $brand['available'];
                        break;
                    }
                }
            }
            $Goods->save($goods_data);
            $attribute_data = [];
            $AttributeModel = new GoodsAttribute();
            foreach ($CategoryAttribute as $key=>$Attribute){
                $attribute_data = ['goodsid'=>$gid,'attributeid'=>$attribute_id[$key],'value'=>is_array($Attribute)?implode(",",$Attribute):0];
                $where = ['goodsid'=>$gid,'attributeid'=>$attribute_id[$key]];
                if($AttributeModel->where($where)->count()>0){
                    $AttributeModel->where($where)->update($attribute_data);
                }else{
                    $AttributeModel->where($where)->insert($attribute_data);
                }
            }
            if($MoveFlag){
                GoodsMoveCategory($Goods->id,$OriginId,$categoryid);
            }else{
                UpdateCache("goods_list",$categoryid);
            }
            return CreateInfoPage("操作成功","商品信息已成功修改!","返回",url('admin/goods/index'));
        }else{
            $brandList = Cache::Get("brand");
            foreach ($brandList as $brand){
                if($brand['id'] == $goods_data['brandid']){
                    $goods_data['brandavailable'] = $brand['available'];
                    break;
                }
            }
            $Goods = new GoodsModel();
            $Goods->insert($goods_data);
            $gid = $Goods->getLastInsID();
            $attribute_data = [];
            foreach ($CategoryAttribute as $key=>$Attribute){
                $attribute_data[] = ['goodsid'=>$gid,'attributeid'=>$attribute_id[$key],'value'=>is_array($Attribute)?implode(",",$Attribute):0];
            }
            $GoodsAttribute = new GoodsAttribute();
            $GoodsAttribute->saveAll($attribute_data);
            UpdateCache("goods_list",$categoryid);
            UpdateCache("goods_allnew",null);
            UpdateCache("goods_recommended",null);
            return CreateInfoPage("操作成功","商品信息已成功添加!","返回",url('admin/goods/index'));
        }
    }
    public function add(){
        $category = Cache::Get("category");
        $brand = Cache::Get("brand");

        $roomList = IntegralRuleModel::getCateList(1);  //房型列表  2018.10.9 huanghao所加
        $this->assign('roomList',$roomList);  //房型列表  2018.10.9 huanghao所加
        $this->assign('categoryList',$category);
        $this->assign('categoryList_json',json_encode($category));
        $this->assign('brandList',$brand);
        return $this->fetch('form');
    }
    public function upload(){
        $file = request()->file('cover_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/goods';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                return json(['result'=>1,'src'=>"/data/images/goods/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }
}