<?php

/**
 * File: Adminer.php
 * User: XljBearSoft
 * Date: 2017-07-17
 * Time: 14:56
 */
namespace app\index\model;
use think\Cache;
use think\Model;

class Users extends Model
{
    protected static function init(){
        Banner::event("after_insert",function($banner){Banner::UpdateCache($banner);});
        Banner::event("after_update",function($banner){Banner::UpdateCache($banner);});
        Banner::event("after_delete",function($banner){Banner::UpdateCache($banner);});
    }
    protected static function UpdateCache($banner){
        $BannerList = Banner::all(function($q) use ($banner){$q->where(['typeid'=>$banner->typeid])->order("sort asc,id desc");});
        Cache::set("banner_list_".$banner->typeid,serialize($BannerList),0);
    }


    public static function userLogin($user){
        $user_ = Users::get(function ($q)use ($user){
            $q->where(['openid'=>$user['openId']]);
        });

        if($user_){
            if  ($user_['status'] == 0) {
                return false;
            }
            return  $user_;
        }else{
            $data = [
                'nickName'=>$user['nickName'],
                'avatarUrl'=> self::download($user['avatarUrl']),
                'sex'=>$user['gender'],
                'city'=>$user['city'],
                'province'=>$user['province'],
                'country'=>$user['country'],
                'integral'=>0,
                'mobile'=>'',
                'openid'=>$user['openId']
            ];

            $insert_id =  Users::insert($data,false,true);
            $data['id'] = $insert_id;
            return $data;
        }
    }

    public static  function download($url, $path = '/data/images/user/')
    {
        $path_ = ROOT_PATH . 'public' . DS . 'data/images/user/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $time = time().rand(100,999);
        $filename = pathinfo($url, PATHINFO_BASENAME);
        $resource = fopen($path_ . $time.$filename.'.jpg', 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $path . $time .$filename.'.jpg';
    }

}