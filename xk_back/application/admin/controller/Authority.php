<?php
namespace app\admin\controller;

use app\admin\controller\Admin; 
use think\Request;
use think\Validate;
use app\admin\model\AdminModel;
use app\admin\model\Menu;

/**
 * 权限管理模块
 */
class Authority extends Admin
{
	protected $controller;

	protected $action;

	function __construct()
	{	
		parent::__construct();

		$this->controller = request()->controller();
	 
		$this->action   = request()->action();
	}


	/**
	 * 添加菜单管理模块
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-27T10:15:15+0800
	 */
 	function addAuthority(Request $request)
 	{		
 		if( $request->isPost() )
 		{
 			//参数验证
 			$validate = Validate::make(
 				[
 					'model_name'	=> 'require'
 				]
 			);
 			//获取有效请求参数
 			$postParam = trimArray($request->only(['model_name']));
 			if( ! $validate->check( $postParam ) )
				return $this->renderError( $validate->getError() );

			if( AdminModel::where( $postParam )->find() )
				return $this->renderError( '改模块已存在，不可重复插入' );

			AdminModel::create($postParam);

 			return $this->renderSuccess('操作成功');
 		}
 		return view();
 	}

 	/**
 	 * 菜单模块列表
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-27T14:57:50+0800
 	 * @return   [type]                   [description]
 	 */
 	function menuModel(Request $request)
 	{	
 		$adminModelList = AdminModel::all();
 		$this->assign('list',$adminModelList);
		return view();
 	}

 	/**
 	 * 删除管理菜单项
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-27T15:07:24+0800
 	 * @return   [type]                   [description]
 	 */
 	function deleteMenuModel(Request $request)
 	{	
 		//参数验证
 		$validate = Validate::make(
 			[
 				'id'	=> 'require'
 			]
 		);
 		$postParam = $request->only(['id']);
 		if( ! $validate->check($postParam) )
 			return $this->renderError($validate->getError());
 		
 		if( AdminModel::where($postParam)->delete() )
 			return $this->renderSuccess('操作成功');

 		return $this->renderError('操作异常，请重试');

 	}

 	/**
 	 * 添加菜单
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-30T10:47:43+0800
 	 */
 	function addMenuAdmin(Request $request)
 	{	
 		if( $request->isPost() )
 		{
 			//参数验证
	 		$validate = Validate::make(
	 			[
	 				'parent_id'	=> 'require',
	 				'menu_name'	=> 'require',
	 				'controller'=> 'require',
	 				'action'	=> 'require'
	 			],
	 			[
	 				'parent_id.require'	=> '模块分类必须选择',
	 				'menu_name.require'	=> '菜单名必须'
	 			]
	 		);
	 		if( ! $validate->check($request->param()) )
	 			return $this->renderError($validate->getError());

	 	 
	 		if( Menu::where($request->only(['controller','action']))->find() )
	 			return $this->renderError('不可重复插入');
	  
	 		$this->addMenu($request->param('parent_id'),$request->param('is_show'),$request->param('menu_name'),$request->param('controller'),$request->param('action'));
	 		return $this->renderSuccess('操作成功');
 		}

 		//获取管理模块
 		$adminModel = AdminModel::all(function($query){
 			$query->order('weight','desc');
 		});
 		$this->assign('model',$adminModel);

 	 	//获取admin下面的所有控制器
 		$dirArr = $this->controllrtArr( APP_PATH.'admin/controller' );
 		$this->assign('controller',$dirArr);
 		

 		return view();

 	}

 	/**
 	 * 获取控制器的所有方法
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-30T15:50:48+0800
 	 * @param    Request                  $request [description]
 	 * @return   [type]                            [description]
 	 */
 	function getAdminAction(Request $request)
 	{
 		return $this->renderSuccess( $this->getAction( $request->param('controller') ) );
 	}

 	/**
 	 * 菜单列表
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-30T18:23:23+0800
 	 * @return   [type]                   [description]
 	 */
 	function menuIndex(Request $request)
 	{
 		$validate = Validate::make(
 			[
 				'parent_id'	=> 'require'
 			]
 		);
 		if( ! $validate->check($request->param()) )
 			return $this->renderError($validate->getError());

 		$list = Menu::where($request->only(['parent_id']))->select();
 		$this->assign('list',$list);
 		
 		return view(); 	 
 	}

 	/**
 	 * 删除菜单
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-30T18:42:39+0800
 	 * @return   [type]                   [description]
 	 */
 	function deleteMenu(Request $request)
 	{
 		//参数验证
 		$validate = Validate::make(
 			[
 				'id'	=> 'require'
 			]
 		);
 		$postParam = $request->only(['id']);
 		if( ! $validate->check($postParam) )
 			return $this->renderError($validate->getError());
 		 
 		if( Menu::where($postParam)->delete() )
 			return $this->renderSuccess('操作成功');

 		return $this->renderError('操作异常，请重试');

 	}

 }