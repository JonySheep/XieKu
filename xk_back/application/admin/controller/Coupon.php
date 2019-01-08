<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Request;
use think\Validate; 
use app\admin\model\Coupon as CouponModel;

/**
 * 优惠券管理
 */
class Coupon extends Admin
{
	/**is_valid
	 * [index description]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-18T09:53:56+0800
	 * @return   [type]                   [description]
	 */
 	function index(Request $request)
 	{	
 		//当前所在页面
 		$this->assign('NowPage',$nowPage = $request->param('nowPage')?:1); 

 		//搜索内容
 		$this->assign('Keyword',$keyword = $request->param('search')?:'');

 		$pageLimit = 15; 		
 		$couList = CouponModel::where([ 'is_valid'=>1 ])->limit(($nowPage-1)*$pageLimit,15)->select();
 		$PageCount = count($couList) / $pageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($nowPage>$PageCount)$nowPage = $PageCount;

 	 	$this->assign('couList',$couList);
 	 	$this->assign('PageCount',$PageCount);

 		return view();
 	}


 	/**
 	 * [addCoupon 添加/修改优惠券]
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-19T11:28:16+0800
 	 */
 	function alterCoupon(Request $request)
 	{	
 		//修改 1 添加 0
 		$this->assign('editmode',$isUpdate = $request->param('coupon_id')?1:0);
 		
 		//现有的优惠券信息
 		$this->assign('data',$isUpdate ? CouponModel::find( $request->param('coupon_id') ) : []);
 	 
 		//如果有参数提交
 		if( $request->isPost() )
 		{			 
 			//验证参数
 			$validate = Validate::make(
 				[
 					'face_money'	=> 'require|number',
 					'top_money'		=> 'require|number',
 					'end_time'		=> 'require|date',
 				 	'number'		=> 'require|number',
 				 	'back_img'		=> 'require'
 				],
 				[
 					'face_money.require'	=> '面值必填',
 					'face_money.number'		=> '填写数字',
 					'top_money.require'		=> '满减额度必须',
 					'end_time.require'		=> '活动有效期必须',
 					'number.require'		=> '优惠券数量必须',
 					'back_img.require'		=> '优惠券背景图必须'
 				] 				 
 			);
 			if( !$validate->check( $request->param() ) )
 				return $this->error( $validate->getError() );
 		 	
 		 	//数据整理
 		 	$insertData = $request->only(['face_money','top_money','end_time','number','back_img']);
 		 	$insertData['remain_number']	= $request->param('number');
 		 	$insertData['end_time']			= strtotime($request->param('end_time'));

 		 	//修改or写入操作
 		 	try {
 		 		if( $isUpdate )
 		 		{	
 		 			CouponModel::where(array( 'id'=>$request->param('coupon_id') ))->update( array_unique($insertData) );
 		 		}
 		 		else
 		 		{
 		 			CouponModel::create($insertData);	
 		 		}
 		 		return $this->success('操作成功',url('admin/Coupon/index'));
 		 	} catch (\Exception $e) {
 		 		return $this->error($e->getMessage());
 		 	}
 		}

 		return view();
 	}

 	/**
 	 * [delete 删除优惠券]
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-19T17:27:14+0800
 	 * @param    Request                  $request [description]
 	 * @return   [type]                            [description]
 	 */
 	function delete(Request $request)
 	{	
 		CouponModel::where([ 'id'=>$request->param('id') ])->setField('is_valid',0);

 		return $this->renderSuccess('操作成功');
 	}

 	/**
 	 * [upload 优惠券背景图上传]
 	 * @Author   yangxiaogang
 	 * @DateTime 2018-07-20T10:44:41+0800
 	 * @return   [type]                   [description]
 	 */
 	public function upload(){
        $file = request()->file('banner_file');
        if($file==null){
            return json(['result'=>-1]);
        }else{
            $rootdir = ROOT_PATH . 'public' . DS . 'data/images/coupon';
            $info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,bmp'])->rule('uniqid')
                ->move($rootdir);
            if($info){
                /*if(session('?upload_banner_old'))@unlink(session('upload_banner_old'));
                session('upload_banner_old',$rootdir.'/'.$info->getSaveName());*/
                return json(['result'=>1,'src'=>"/data/images/coupon/".$info->getSaveName()]);
            }else{
                return json(['result'=>0,'msg'=>$file->getError()]);
            }
        }
    }

}