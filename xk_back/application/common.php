<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

if( ! function_exists('generate_code') )
{
    function generate_code($length = 6) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }

}


 
function trimArray($Input){
    if (!is_array($Input))
        return trim($Input);
    return array_map('trimArray', $Input);
}

function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){ 
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
        $indexKeyIsNull            = (is_null($indexKey))?true :false; 
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
        $result                         = array(); 
        foreach((array)$input as $key=>$row){ 
            if($columnKeyIsNumber){ 
                $tmp= array_slice($row, $columnKey, 1); 
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
            }else{ 
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
            } 
            if(!$indexKeyIsNull){ 
                if($indexKeyIsNumber){ 
                  $key = array_slice($row, $indexKey, 1); 
                  $key = (is_array($key) && !empty($key))?current($key):null; 
                  $key = is_null($key)?0:$key; 
                }else{ 
                  $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                } 
            } 
            $result[$key] = $tmp; 
        } 
        return $result; 
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}


// 应用公共文件
function GetPasswordMd5($password,$salt){
    return md5($salt.md5($password.$salt).$salt);
}
function ClickSort($a,$b){
    if ($a[1]==$b[1]) return 0;
    return ($a[1]>$b[1])?-1:1;
}
function UpdateCache($type,$data){
    $Cache = new \WebCache\WebCacheLib();
    $prefix = \think\Config::get("database")['prefix'];
    $brandsql = \think\Db::name('brand')->where("{$prefix}brand.id = {$prefix}goods.brandid and {$prefix}brand.available = 1")
        ->fetchsql()->count();
    $brandverify = "($brandsql) > 0";
    switch ($type){
        case "article_list":
            $typeid = is_numeric($data)?$data:$data->typeid;
            $Articles = \app\index\model\Article::all(["typeid"=>$typeid,"deleted"=>0]);
            $ArticleList = [];
            foreach ($Articles as $Article){
                $ArticleList[] = [$Article->id,$Article->title,$Article->describe,$Article->updatetime];
            }
            rsort($ArticleList);
            $Cache::Set("article_list_type".$typeid,$ArticleList);
            break;
        case "article":
            $type = \app\index\model\ArticleType::get($data->typeid);
            $data->typename = $type->name;
            $Cache::set("article_cache_".$data->id,$data->toArray());
            break;
        case "help_list":
            $categoryid = is_numeric($data)?$data:$data->categoryid;
            $Helps = \think\Db::name("help")->where(['categoryid'=>$categoryid])->order("sort asc")->select();
            $HelpList = [];
            foreach ($Helps as $Help){
                $HelpList[] = [$Help['id'],$Help['title'],$Help['body']];
            }
            $Cache::Set("help_list_type".$categoryid,$HelpList);
            break;
        case "article_click":
            $Articles = \app\index\model\Article::all(["typeid"=>$data,"deleted"=>0]);
            $clicks = [];
            foreach ($Articles as $article){
                $clicks[$article['id']] = $article['click'];
            }
            $Cache::Set("articles_type{$data}_click",$clicks);
            break;
        case "banner_list":
            $BannerList = \think\Db::name("banner")->where(['typeid'=>$data])->order("sort asc,id desc")->select();
            $Cache::Set("banner_list_$data",$BannerList);
            break;
        case "page":
            $page = \app\index\model\PageList::get($data);
            if (!$page) return;
            if($page->Page == null){
                $page->Page = ['body'=>''];
            }
            $Cache::Set("page_".$data,$page->toArray());
            break;
        case "case":
            $Cache::set("case_cache_".$data->id,$data->toArray());
            break;
        case "case_list":
            $Cases = \app\index\model\Cases::all(
                function($q){
                    $q->order("sort asc,id desc");
                }
            );
            $CaseList = [];
            foreach ($Cases as $Case){
                $CaseList[] = [$Case->id,$Case->title,$Case->cover,$Case->read_count,$Case->status];
            }
            rsort($CaseList);
            $Cache::Set("case_list",$CaseList);
            break;
        case "category":
            $categorys = \think\Db::name("category")->select();
            foreach ($categorys as $key=>$category){
                $attribute_lsit = \think\Db::name("category_attribute")->where('categoryid',$category['id'])->order('id')->select();
                $attribute = [];
                foreach ($attribute_lsit as $attribute_tmp){
                    $attribute[]=['id'=>$attribute_tmp['id'],'name'=>$attribute_tmp['name'],'value'=>explode("#",$attribute_tmp['value'])];
                }
                $categorys[$key]['attribute'] = $attribute;
            }
            $Cache::Set("category",$categorys);
            break;
        case "category2":
            $categorys = \think\Db::name("category2")->select();
            $Cache::Set("category2",$categorys);
            break;
        case "brand":
            $brandList = \think\Db::name("brand")->order("sort asc,id desc")->select();
            foreach ($brandList as &$brand){
                $category = explode(',',$brand['category']);
                $brand['category'] = [];
                if(sizeof($category)>0){
                    foreach ($category as $c){
                        if(HasCategory($c))
                            $brand['category'][] = $c;
                    }
                }
            }
            $Cache::Set("brand",$brandList);
            break;
        case "goods":
            $goods = \think\Db::name("goods")->where("id = $data")->find();
            if(!$goods)return false;
            $attributes = \think\Db::name("goods_attribute")->where("goodsid",$goods['id'])->select();
            $goods['attribute'] = $attributes;
            $Cache::Set("goods_cache_".$data,$goods);
            return true;
            break;
        case "goods_list":
            $categoryid = intval($data);
            if(!HasCategory($categoryid))return false;
            $goodsList = \think\Db::name("goods")->where("categoryid = $categoryid and available = 1 and brandavailable = 1")->order("sort asc,id desc")->select();
            foreach ($goodsList as &$goods){
                $attributes = \think\Db::name("goods_attribute")->where("goodsid",$goods['id'])->select();
                $attributeList = [];
                foreach ($attributes as $attribute){
                    $attributeList[$attribute['attributeid']] = explode(",",$attribute['value']);
                }
                $goods['attribute'] = $attributeList;
            }
            $Cache::Set("goodslist_type{$categoryid}_cache",$goodsList);
            return true;
            break;
        case "goods_alllist":
            $Cache = new \WebCache\WebCacheLib();
            $CategoryList = $Cache::Get("category");
            foreach ($CategoryList as $category){
                $categoryid = $category['id'];
                $goodsList = \think\Db::name("goods")->where("categoryid = $categoryid and available = 1 and brandavailable = 1")->order("sort asc,id desc")->select();
                foreach ($goodsList as &$goods){
                    $attributes = \think\Db::name("goods_attribute")->where("goodsid",$goods['id'])->select();
                    $attributeList = [];
                    foreach ($attributes as $attribute){
                        $attributeList[$attribute['attributeid']] = explode(",",$attribute['value']);
                    }
                    $goods['attribute'] = $attributeList;
                }
                $Cache::Set("goodslist_type{$categoryid}_cache",$goodsList);
            }
            break;
        case "goods_allnew":
            $goodsList = \think\Db::name("goods")
                ->where("available = 1 and brandavailable = 1")->order("sort asc,id desc")->limit(0,7)->select();
            $Cache::Set("goodslist_allnew_cache",$goodsList);
            return true;
            break;
        case "goods_mostclick":
            $goodsList = \think\Db::name("goods")->where("available = 1 and brandavailable = 1")->order("click desc,id desc")->limit(0,7)->select();
            $Cache::Set("goods_mostclick_cache",$goodsList);
            return true;
            break;
        case "goods_recommended":
            $goodsList = \think\Db::name("goods")->where("recommended = 1 and available = 1 and brandavailable = 1")->order("sort asc,id desc")->limit(0,7)->select();
            $Cache::Set("goods_recommended_cache",$goodsList);
            return true;
            break;
        case "store":
            $storeList = \think\Db::name("store")->order("sort asc,id desc")->select();
            $Cache::Set("store_cache",$storeList);
            break;
        case "free_design_remain":
            $result = \think\Db::name("freedesign_options")->where("id=1")->find();
            $count = $result['applytotal'];
            $Cache::Set("free_design_remain",['remain'=>$count,'date'=>date('Y-m-d',time())]);
            break;
        case "free_design":
            $successCount = \think\Db::name("freedesign")->where("status=1")->count();
            $Cache::Set('free_design',['success'=>$successCount]);
            break;
        case "free_design_list":
            $result = \think\Db::name("freedesign")->where("status=1")->order("treatedtime desc")->limit(0,30)->select();
            $list = [];
            foreach ($result as $value){
                $list[] = ['phone'=>substr($value['phone'],0,3)."****".substr($value['phone'],7),
                    'region'=>GetRegionName($value['region']),
                    'model'=>GetModelName($value['model']),
                    'area'=>$value['area'],
                    'category'=>$value['category'],
                ];
            }
            $Cache::Set("free_design_list",$list);
            break;
    }
}
function CoreCacheInit(){
    $Cache = new \WebCache\WebCacheLib();
    if(!$Cache::Has("category")){
        UpdateCache("category",null);
    }
    if(!$Cache::Has("category2")){
        UpdateCache("category2",null);
    }
    if(!$Cache::Has("model")){
        $model = \think\Db::name("model")->select();
        $Cache::Set("model",$model);
    }
    if(!$Cache::Has("region")){
        $region = \think\Db::name("region")->select();
        $Cache::Set("region",$region);
    }
    if(!$Cache::Has("brand")){
        UpdateCache("brand",null);
    }
    if(!$Cache::Has("free_design_remain")){
        UpdateCache("free_design_remain",null);
    }else{
        $free_design = $Cache::Get("free_design_remain");
        if($free_design['date']!=date('Y-m-d',time())){
            UpdateCache("free_design_remain",null);
        }
    }
    if(!$Cache::Has("free_design")) {
        UpdateCache("free_design",null);
    }
    if(!$Cache::Has("free_design_list")) {
        UpdateCache("free_design_list",null);
    }
}
function FreeDesignHasRemain(){
    $Cache = new \WebCache\WebCacheLib();
    $free_design = $Cache::Get("free_design_remain");
    if($free_design['remain']<=0)
        return false;
    return true;
}
function FreeDesignComfirm()
{
    $Cache = new \WebCache\WebCacheLib();
    $free_design = $Cache::Get("free_design_remain");
    if($free_design['remain']<=0)
        return false;
    $free_design['remain']--;
    $Cache::Set("free_design_remain",$free_design);
    return true;
}
function GetModelName($id){
    $Cache = new \WebCache\WebCacheLib();
    $list = $Cache::Get("model");
    foreach ($list as $Model)
        if($Model['id']==$id)return $Model['name'];
}
function HasModel($id){
    $Cache = new \WebCache\WebCacheLib();
    $list = $Cache::Get("model");
    foreach ($list as $Model)
        if($Model['id']==$id)return true;
    return false;
}
function GetCategoryName($id){
    $Cache = new \WebCache\WebCacheLib();
    $list = $Cache::Get("category");
    foreach ($list as $Category)
        if($Category['id']==$id)return $Category['name'];
}
function GetRegionName($id){
    $Cache = new \WebCache\WebCacheLib();
    $list = $Cache::Get("region");
    foreach ($list as $Region)
        if($Region['id']==$id)return $Region['region'];
}
function HasCategory($id){
    $Cache = new \WebCache\WebCacheLib();
    $CategoryList = $Cache::Get("category");
    foreach ($CategoryList as $category){
        if($category['id'] == $id)return true;
    }
    return false;
}
function HasRegion($id){
    $Cache = new \WebCache\WebCacheLib();
    $RegionList = $Cache::Get("region");
    foreach ($RegionList as $region){
        if($region['id'] == $id)return true;
    }
    return false;
}
function GetCategory($id){
    $Cache = new \WebCache\WebCacheLib();
    $CategoryList = $Cache::Get("category");
    foreach ($CategoryList as $category){
        if($category['id'] == $id)return $category;
    }
    return null;
}
function GetBrand($id){
    $Cache = new \WebCache\WebCacheLib();
    $BrandList = $Cache::Get("brand");
    foreach ($BrandList as $brand){
        if($brand['id'] == $id)return $brand;
    }
    return ['name'=>'(品牌丢失)','id'=>0];
}
function HasBrand($id){
    $Cache = new \WebCache\WebCacheLib();
    $BrandList = $Cache::Get("brand");
    foreach ($BrandList as $brand){
        if($brand['id'] == $id)return true;
    }
    return false;
}
function GoodsMoveCategory($goodsid,$origin,$to){
    $Cache = new \WebCache\WebCacheLib();
    $oldCache =  $Cache::Get("goodslist_type{$origin}_cache");
    foreach ($oldCache as $key=>$goods){
        if($goods['id'] == $goodsid){
            unset($oldCache[$key]);
            break;
        }
    }
    $Cache::Set("goodslist_type{$origin}_cache",$oldCache);
    UpdateCache("goods_list",$to);
}
function GoodsSortByNew($a,$b){
    if($a['id']==$b['id'])return 0;
    return $a['id']>$b['id']?-1:1;
}
function GoodsSortByPriceLower($a,$b){
    if($a['price']==$b['price'])return 0;
    return $a['price']>$b['price']?1:-1;
}
function GoodsSortByPriceHigher($a,$b){
    if($a['price']==$b['price'])return 0;
    return $a['price']>$b['price']?-1:1;
}
function SendPrice($phone,$price){
    $phone = trim($phone);
    if(!is_numeric($phone)||strlen($phone)!=11)return -1;
    $Cache = new \WebCache\WebCacheLib();
    $appId = config("ucpass.appid");
    $options['accountsid'] = config("ucpass.aid");
    $options['token'] = config("ucpass.token");
    $templateId = 148307;
    $ucpass = new \Ucpaas\UcpaasLib($options);
    $p = "";
    foreach ($price['item'] as $item){
        if(is_numeric($item[2]))
            $p.=$item[0] . ":" . $item[2] ."元，";
        else
            $p.=$item[0] . ":" . $item[2] ."，";
    }
    $p = trim($p,"，") . "。";
    $p .= "," . $price['totals'];
    $result =  json_decode($ucpass->templateSMS($appId,$phone,$templateId,$p),true);
}
function SendVerify($phone){
    $phone = trim($phone);
    if(!is_numeric($phone)||strlen($phone)!=11)return -1;
    $Cache = new \WebCache\WebCacheLib();
    $appId = config("ucpass.appid");
    $options['accountsid'] = config("ucpass.aid");
    $options['token'] = config("ucpass.token");
    $templateId = config("ucpass.verify_sms_id");
    $ucpass = new \Ucpaas\UcpaasLib($options);
    if(!$Cache::Has('verify_phone_'.request()->ip().'_cache')){
        $chars = "0123456789";
        $code = "";
        for ( $i = 0; $i < 8; $i++ )
        {
            $code .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $result =  json_decode($ucpass->templateSMS($appId,$phone,$templateId,$code),true);
        if($result['resp']['respCode']=="000000"){
            \think\Db::name("freedesign_options")->where('id=1')->setInc("smscount");
            $Cache::Set('verify_phone_'.request()->ip().'_cache',[$phone,$code],180);
            return 1;
        }else{
            return -1;
        }
    }else{
        return 0;
    }
}
function VerifyCode($phone,$code){
    $phone = trim($phone);
    if(!is_numeric($phone)||strlen($phone)!=11)return false;
    $Cache = new \WebCache\WebCacheLib();
    if(!$Cache::Has('verify_phone_'.request()->ip().'_cache'))return false;
    $Verify = $Cache::Get('verify_phone_'.request()->ip().'_cache');
    if($Verify[0]!=$phone||$Verify[1]!=$code)return false;
    $Cache::Set('verify_phone_'.request()->ip().'_cache',["",""],1);
    return true;
}
function IsMobile(){
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
}