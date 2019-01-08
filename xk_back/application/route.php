<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    "[admin]"=>[
        'ie' => 'admin/index/ie',
        'index' =>  'admin/index/index',
        'login'   => 'admin/user/login',
        'brand/:id'  => ['admin/brand/brandedit',[],['id' => '\d+']],
        'brand/delete/:id'  => ['admin/brand/delete',[],['id' => '\d+']],
        'page/:id'  => ['admin/page/edit',[],['id' => '\d+']],
        'index_page'   => 'admin/index/index_page',
        'logout'    => 'admin/user/logout',
        'banner/:type/add' => ['admin/banner/add',[],['type' => '\d+']],
        'banner/edit/:type/:id' => ['admin/banner/edit',[],['type' => '\d+','id' => '\d+']],
        'banner/delete/:type/:id' => ['admin/banner/delete',[],['type' => '\d+','id' => '\d+']],
        'banner/:type' => ['admin/banner/index',[],['type' => '\d+']],
        /*'article/add/:type' => ['admin/article/add',[],['type' => '\d+']],
        'article/:type/:page' => ['admin/article/index',[],['type' => '\d+','page' => '\d+']],
        'article/:type' => ['admin/article/index',[],['type' => '\d+']],*/
        'help/delete/:typeid/:id'  => ['admin/help/delete',[],['typeid' => '\d+','id' => '\d+']],
        'help/:id'  => ['admin/help/index',[],['id' => '\d+']],
    ],
    "[swa]"=>[
        'index'=>'swa/index/index',
        'p/:id'=>['swa/context/view',[],['id' => '\d+']],
        'service/help/:typeid'=>['swa/service/help',['typeid' => '\d+']],
        'service/:id'=>['swa/service/view',[],['id' => '\d+']],
        'article/t/:type'=>['swa/article/index',[],['type' => '\d+']],
        'article/:id'=>['swa/article/view',['id' => '\d+']],
    ],
    "[cases]"=>[
        'index'=>'cases/index/index',
        '/:id'=>['cases/index/view',[],['id' => '\d+']],
    ],
    "[mall]"=>[
        'index'=>'mall/index/index',
        'category'=>'mall/index/category',
        'category/:t'=>['mall/index/category',[],['t' => '\d+']],
        'product/:id'=>['mall/index/product',[],['id' => '\d+']],
        'brand/:t'=>['mall/index/brand',[],['t' => '\d+']],
    ],
    "[store]"=>[
        'index'=>'store/index/index',
    ],
    "[design]"=>[
        'free/apply'=>'design/index/apply',
        'free/vfcode'=>'design/index/sendverifycode',
        'free'=>'design/index/free',
        'oneminute'=>'design/index/oneminute',
    ],
];
