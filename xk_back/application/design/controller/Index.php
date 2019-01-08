<?php
 namespace app\design\controller;
/**
 * File: index.php
 * User: XljBearSoft
 * Date: 2017-08-02
 * Time: 11:32
 */
 use app\index\controller\CoreController;
 use think\console\Input;
 use think\Db;
 use WebCache\WebCacheLib as Cache;
use  think\Request;
 class Index extends CoreController
{
    public function index(){
        $this->redirect('design/index/free');
    }
    public function free(){
        //SendPrice("18616185912");
        $modelList = Cache::Get("model");
        $categoryList = Cache::Get("category");
        $regionList = Cache::Get("region");
        $freeDesignList = Cache::Get("free_design_list");
        $this->assign("freeDesignList",$freeDesignList);
        $this->assign('modelList',$modelList);
        $this->assign('regionList',$regionList);
        $this->assign('categoryList',$categoryList);
        $RandomCaseList = $this->GetRandomCase();
        $this->assign("RandomCaseList",$RandomCaseList);
        $FreeDesignCache = Cache::Get("free_design");
        $FreeDesignRemain = Cache::Get("free_design_remain");
        $this->assign("FreeDesignRemain",$FreeDesignRemain['remain']);
        $this->assign("FreeDesignSuccess",$FreeDesignCache['success']);
        return $this->fetch('free');
    }
    public function sendverifycode(){
        if (!Request::instance()->isAjax())exit();
        if(!FreeDesignHasRemain()){
            $data['result'] = -3;
            return json($data);
        }
        $geeTest = new \app\index\controller\Geetest();
        if(!$geeTest->VerifyGeeTest()){
            $data['result'] = -2;
        }else {
            $phone = trim(input("post.phone"));
            $data['result'] = SendVerify($phone);
        }
        return json($data);
    }
    public function oneminute_sendverifycode(){
         if (!Request::instance()->isAjax())exit();
         $geeTest = new \app\index\controller\Geetest();
         if(!$geeTest->VerifyGeeTest()){
             $data['result'] = -2;
         }else {
             $phone = trim(input("post.phone"));
             $data['result'] = SendVerify($phone);
         }
         return json($data);
     }
     public function oneminute_apply(){
         if (!Request::instance()->isAjax())exit();
         $json = htmlspecialchars_decode(trim(input("post.json")));
         $phone = trim(input("post.phone"));
         $code = trim(input("post.code"));
         $name = trim(input("post.name"));
         $data['status'] = 0;
         if($json=="")return json($data);
         $select = json_decode($json,true);
        if($select==null)return json($data);
        if($phone==""||$code==""||$name==""){
            $data['status'] = -1;
            return json($data);
        }
         if(!VerifyCode($phone,$code)){
             $data['status'] = -2;
             return json($data);
         }
         $result = [];
         $result['item'] = [];
         $row = intval($select['model']);
         $col = intval($select['type']) - 1;
         if(isset($select['brand']['water_type'])){
             $js_col = intval($select['brand']['water_type']) - 1;
             if($js_col<0||$js_col>3)return json($data);
         }
         if($row<0||$row>9)return json($data);
         if($col<0||$col>3)return json($data);
         $area = intval($select['area']);
         if($area<1||$area>8)return json($data);
         foreach ($select['brand'] as $key=>$value){
             switch ($key){
                 case "air":
                     $price = $this->GetPriceInfo("air",$value,$row,$col);
                     $result['item'][] = ["中央空调系统",$price[0],$price[1]];
                     break;
                 case "heat1":
                     $heat2_name = ['奎林','雅克菲'];
                     if(array_key_exists("heat2",$select['brand'])){
                         $price = $this->GetPriceInfo("heat2",$value,$row,$col);
                         $result['item'][] = ["中央供热系统",$price[0] . " + " . $heat2_name[$select['brand']['heat2']-1] ,$price[1]];
                     }else{
                         $price = $this->GetPriceInfo("heat1",$value,$row,$col);
                         $result['item'][] = ["中央供热系统",$price[0],$price[1]];
                     }
                     break;
                 case "water":
                     $js_type = ['前置过滤器','末端直饮机','中央软水机','中央净水机'];
                     $price = $this->GetPriceInfo("water",$value,$row,$js_col);
                     $result['item'][] = ["中央净水系统({$js_type[$js_col]})",$price[0],$price[1]];
                     break;
                 case "wind":
                     $price = $this->GetPriceInfo("wind",$value,$row,$col);
                     $result['item'][] = ["中央新风系统",$price[0],$price[1]];
                     break;
             }
         }
         $price = 0;
         foreach ($result['item'] as $item){
             if(is_numeric($item[2]))
                $price += $item[2];
         }
         $result['totals'] = $price;
         $result['status'] = 1;
         $result_db['name'] = $name;
         $result_db['phone'] = $phone;
         $result_db['timestamp'] = time();
         $tmp = $result;
         $tmp['model'] = $row;
         $tmp['type'] = $col;
         $tmp['area'] = $area;
         $result_db['result'] = json_encode($tmp);
         SendPrice($phone,$result);
         Db::name("oneminute_recode")->insert($result_db);
         return json($result);
     }
    public function apply(){
        $phone = trim(input("post.phone"));
        $sex = intval(trim(input("post.sex")));
        if($sex>1)$sex = 1;
        if($sex<0)$sex = 0;
        $area = intval(trim(input("post.area")));
        $name = trim(input("post.name"));
        $code = trim(input("post.code"));
        $model = intval(trim(input("post.model")));
        $region = intval(trim(input("post.region")));
        $category = isset($_POST['category'])?$_POST['category']:[];
        $categoryStr = "";
        foreach ($category as $c){
            $co = GetCategory(intval($c));
            if($co!=null){
                $categoryStr.=$co['name'] . ",";
            }
        }
        $categoryStr = trim($categoryStr,",");
        if(!HasRegion($region)){
            return $this->errorHtml("地区选择不正确！");
        }
        if(!HasModel($model)){
            return $this->errorHtml("户型参数不正确！");
        }
        if($categoryStr==""){
            return $this->errorHtml("请选择至少一个系统！");
        }
        if($name==""||$area<=3){
            return $this->errorHtml("请输入正确的信息！");
        }
        if(!VerifyCode($phone,$code)){
            return $this->errorHtml("手机验证码不正确！");
        }
        $data['area'] = $area;
        $data['phone'] = $phone;
        $data['name'] = $name;
        $data['sex'] = $sex;
        $data['model'] = $model;
        $data['category'] = $categoryStr;
        $data['timestamp'] = time();
        $data['region'] = $region;
        $hasCout = Db::name("freedesign")->where(['phone'=>$phone,'status'=>0])->count();
        if($hasCout>0){
            return $this->errorHtml("您之前已提交过信息，请耐心等待处理结果！");
        }
        if(!FreeDesignComfirm())return $this->errorHtml("很抱歉，今日的免费名额已结束!请明日再来报名。");
        Db::name("freedesign")->insert($data);
        return $this->okHtml("您的信息已成功登记！请耐心等待，我们将尽快与您取得联系！");
    }
     private function okHtml($msg){
         return "<script>alert('$msg');window.location.href='".url('design/index/free')."'</script>";
     }
    private function errorHtml($msg){
        return "<script>alert('$msg');javascript:history.go(-1);</script>";
    }
    public function oneminute(){
        return $this->fetch('oneminute');
    }
    private function GetRandomCase($count = 6){
        if(!Cache::Has("case_list")){
            UpdateCache("case_list",null);
        }
        $CaseList = Cache::Get("case_list");
        shuffle($CaseList);
        return array_slice($CaseList,0,$count);
    }
    private function GetPriceInfo($system,$brand,$row,$col)
    {
        $price = [
            "air" => [
                "东芝" => [
                    [21600,0,0,0],
                    [27400,0,0,0],
                    [35200,0,0,0],
                    [35200,35200,0,0],
                    [44000,44000,0,0],
                    [54800,54800,66000,66000],
                    [57570,60600,72000,72000],
                    [70110,73800,82000,82000],
                    [83790,88200,98000,98000],
                    [94050,99000,11000,110000],
                ],
                "大金" => [
                    [24600,0,0,0],
                    [31400,0,0,0],
                    [41200,0,0,0],
                    [41200,41200,0,0],
                    [50000,50000,0,0],
                    [62800,62800,70000,70000],
                    [71820,75600,77000,77000],
                    [79515,83700,93000,93000],
                    [99180,104400,116000,116000],
                    [111150,117000,130000,130000],
                ],
                "美的" => [
                    [18600,0,0,0],
                    [23900,0,0,0],
                    [31200,0,0,0],
                    [31200,31200,0,0],
                    [39500,39500,0,0],
                    [47800,47800,59000,59000],
                    [50445,53100,64500,64500],
                    [63698,67050,74500,74500],
                    [71820,75600,84000,84000],
                    [81225,85500,95000,95000],
                ],
                "格里" => [
                    [18600,0,0,0],
                    [23900,0,0,0],
                    [31200,0,0,0],
                    [31200,31200,0,0],
                    [39500,39500,0,0],
                    [47800,47800,59000,59000],
                    [50445,53100,64500,64500],
                    [63698,67050,74500,74500],
                    [71820,75600,84000,84000],
                    [81225,85500,95000,95000],
                ],
            ],
            "wind" => [
                "百朗" => [
                    [9000,0,0,0],
                    [9000,0,0,0],
                    [11000,0,0,0],
                    [11000,0,0,0],
                    [12500,18000,27000,36000],
                    [18000,18000,27000,36000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                ],
                "松下" => [
                    [9000,0,0,0],
                    [9000,0,0,0],
                    [11000,0,0,0],
                    [11000,0,0,0],
                    [12500,18000,27000,36000],
                    [18000,18000,27000,36000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                    [18000,22000,33000,44000],
                ],
            ],
            "water" => [
                "森乐" => [
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                ],
                "蓝飘儿" => [
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [1580,4580,7550,6880],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                    [3880,4580,10850,9580],
                ],
            ],
            "heat1" => [
                "威能" => [
                    [18500,0,0,0],
                    [22100,0,0,0],
                    [25700,0,0,0],
                    [31800,31800,0,0],
                    [38400,38400,0,0],
                    [42000,42000,42000,44000],
                    [49290,51000,54000,56000],
                    [49240,51840,57600,59600],
                    [53865,56700,63000,65000],
                    [61560,64800,72000,74000],
                ],
                "阿里斯顿" => [
                    [17500,0,0,0],
                    [21000,0,0,0],
                    [24700,0,0,0],
                    [29800,29800,0,0],
                    [36400,36400,0,0],
                    [40000,40000,40000,41000],
                    [48450,51000,51000,52000],
                    [46683,49140,54600,55600],
                    [51300,54000,60000,61000],
                    [58995,62100,69000,70000],
                ],
            ],
            "heat2" => [
                "威能" => [
                    [21500,0,0,0],
                    [25500,0,0,0],
                    [29500,0,0,0],
                    [36000,36000,0,0],
                    [43000,43000,0,0],
                    [47000,47000,51000,53000],
                    [51300,54000,58000,60000],
                    [56430,59400,66000,68000],
                    [63270,66600,74000,76000],
                    [70110,73800,82000,84000],
                ],
                "阿里斯顿" => [
                    [20500,0,0,0],
                    [24500,0,0,0],
                    [28500,0,0,0],
                    [34000,34000,0,0],
                    [41000,41000,0,0],
                    [45000,45000,49000,50000],
                    [48450,51000,55000,56000],
                    [53865,56700,63000,64000],
                    [60705,63900,71000,72000],
                    [67545,71100,79000,80000],
                ],
            ]
        ];
        $brandname = array_keys($price[$system])[$brand-1];
        $item_price = $price[$system][$brandname][$row][$col];
        if($item_price==0) $item_price = "无法提供参考价";
        return [array_keys($price[$system])[$brand-1],$item_price];
    }
}