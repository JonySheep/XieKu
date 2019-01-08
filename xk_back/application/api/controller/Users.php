<?php
namespace app\api\controller;

use app\admin\model\FeedBack;
use app\index\controller\CoreController;
use think\Request;
use think\Validate;
use app\admin\model\Users as UsersModel;
use app\admin\model\UsersCoupon;
use app\admin\model\Coupon;
use app\index\model\Users as User;
use app\weixin\WXBizDataCrypt;
use app\admin\model\Integral;
use app\admin\model\IntegralRule;
use app\index\model\WeiXin;
use app\admin\model\UsersSite;
use app\admin\model\City;
use WebCache\WebCacheLib as Cache;
use app\admin\model\Activity as ActivityModel;




/**
 * 用户行为控制器
 */
class Users extends CoreController
{	
	private  $appId 	  = 'wx830f32fd1326dc7a';

	/**
	 * 绑定手机号
	 */
	function setTel(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'openId' => 'require',
				'tel'	 => 'require|regex:/^1[34578][0-9]{9}$/',
			],
			[
				'openId.require'	=> '必须先授权认证',
				'tel.require'		=> '手机号码必须'
			]
		);		
		if( ! $validate->check( $request->param()) )
			return $this->renderError($validate->getError());
	 
		try {
			if( !UsersModel::where( [ 'openid'=>$request->param('openId') ] )->setField('mobile',$request->param('tel')) )
			{
				return $this->renderError('修改的手机号不许是原手机号');
			}else{
				//送积分
				$users_id = UsersModel::where([ 'openid'=>$request->param('openId') ])->value('id');
				$integral = IntegralRule::where('identification','binding_phone')->value('integral');
				$inserIntegral = [ 'users_id'=>$users_id, 'content'=>'手机绑定','integral'=>$integral, 'left_users_id'=>$users_id ];
				if( ! Integral::where([ 'users_id'=>$users_id, 'content'=>'手机绑定'])->find() )
				{ 	
					UsersModel::where([ 'openid'=>$request->param('openId') ])->setInc('integral',$integral);
					Integral::create($inserIntegral);
				}
				return $this->renderSuccess([ 'rsf'=>'操作成功' ]);
			}			
		} catch (\Exception $e) {
			return $this->renderError($e->getMessage());
		}
	}
	
	/**
	 * [getUsersCoupon 我的优惠券]
	 * @author yangxiaogang 2018-07-21
	 * @return [type] [description]
	 */
	function getUsersCoupon(Request $request)
	{
		//参数验证
		$validate = Validate::make(
			[
				'openid' => 'require',
			],
			[
				'openid.require'	=> '必须先授权认证',
			]
		);
		if( ! $validate->check($request->param()) )
			return $this->renderError($validate->getError());

		//获取数据
		$usersId = UsersModel::where($request->only(['openid']))->value('id');
		$usersCouponList = UsersCoupon::all(function($query)use($usersId){
			$query->where([ 'users_id'=>$usersId ,'is_use' => 0])->order('create_time','desc');
		});
		$userCouponModel = new UsersCoupon();
        $usersCouponList = $userCouponModel->alias('u')
            ->field('u.*')
            ->join('coupon c','u.coupon_id = c.id','left')
            ->where(['u.users_id'=>$usersId ,'u.is_use' => 0,'c.is_valid'=>1,'end_time'=>['egt',time()]])
            ->order('create_time')
            ->select();
		$couponIds = i_array_column(collection($usersCouponList)->toArray(), 'coupon_id');
		$couponList = Coupon::all(function($query)use ( $couponIds ){
			$query->whereIn('id',$couponIds);
		});
		$couponList = array_combine( i_array_column($couponList,'id'),$couponList );
		foreach ($usersCouponList as &$value) {
		 	$value['info'] = $couponList[$value->coupon_id];
		}
		unset($value);
	 
		return $this->renderSuccess($usersCouponList);
	}

    /**
     * 获取可使用优惠券
     * @author huanghao
     */
    public function  getCoupon(Request $request){
        //参数验证
        $validate = Validate::make(
            [
                'openid' => 'require',
                'order_money' => 'require',
            ],
            [
                'openid.require'	=> '必须先授权认证',
                'order_money.require'	=> '需要订单金额参数',
            ]
        );
        if( ! $validate->check($request->param()) )
            return $this->renderError($validate->getError());

        //获取数据
        $usersId = UsersModel::where($request->only(['openid']))->value('id');
        $order_money = $request->only(['order_money'])['order_money'];
        $where = [
            'users_id' => $usersId,
            'is_use' => 0,
            'is_valid' => 1,
            'top_money' => ['ELT',$order_money]
        ];

        $usersCouponList = UsersCoupon::field('C.*, UC.is_use,UC.id as user_coupon_id')->alias('UC')
            ->where($where)->join('swa_coupon C','C.id = UC.coupon_id','LEFT')
            ->order('C.face_money','desc')->select();
        foreach ($usersCouponList as &$v) {
            $v['end_time'] = date('Y-m-d',$v['end_time']);
        }
        return $this->renderSuccess($usersCouponList);
    }


	/**
	 * [register 用户注册]
	 * @author yangxiaogang 2018-07-22
	 * @return [type] [description]
	 */
	function usersRegister(Request $request)
	{			 
		//参数验证
		$validate = Validate::make(
			[	
				'openId'	=> 'require',
				'avatarUrl' => 'require',
				'gender'		=> 'require',
				//'city'		=> 'require',
				//'province'	=> 'require',
				//'country'	=> 'require',
				'nickName'  => 'require'
			],
			[	
				'openId.require'	=> 'openid必须',
				'avatarUrl.require'	=> '头像必须',
				'gender.require'	=> '性别必须',
				//'city'				=> '城市地址必须'
			]
		);
		if( ! $validate->check($request->param()) )
			return $this->renderError($validate->getError());

		//入库
		$data = User::userLogin( $request->param() );
		$data['avatarUrl'] = request()->domain().$data['avatarUrl'];
		return $this->renderSuccess($data);

	}

	/**
	 * [getTel 获取用户授权手机号码]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-23T13:16:43+0800
	 * @return   [type]                   [description]
	 */
	function getTel(Request $request)
	{
		$validate = Validate::make(
			[
				'sessionKey'	=> 'require',
				'encryptedData'	=> 'require',
				'iv'			=> 'require'
			] 	
		);
		if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 

	 
		//luoji
		$pc = new WXBizDataCrypt($this->appId, $request->param('sessionKey'));
		$errCode = $pc->decryptData($request->param('encryptedData'), $request->param('iv'), $data );
	 
		if( ! ($errCode === 0) )
			return $this->renderError('操作异常！'.$errCode);
	 
		return $this->renderSuccess(json_decode($data,true));
	}

	/**
	 * [getUsersTel 获取用户手机号]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-23T14:59:51+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function getUsersTel(Request $request)
	{
		$validate = Validate::make(
			[
				'open_id'	=> 'require',			 	
			] 	
		);
		if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 

	 	$tel = UsersModel::where([ 'openid'=>$request->param('open_id') ])->value('mobile');
		return $this->renderSuccess([ 'rsf'=>$tel ]);
	}


	/**
	 * [getUsersIntegral 获取我的积分]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-23T15:44:29+0800
	 * @param    Request                  $request [description]
	 * @return   [type]                            [description]
	 */
	function getUsersIntegral(Request $request)
	{
		$data = UsersModel::where( $request->only(['openid']) )->find(); 
	 
		if( ! $request->param('openid') )
		{	
			$data = $data->toArray();
			foreach ($data as $key => $value) {				 
				$data[$key]	= '';
			}		 			 	
		}
		return $this->renderSuccess($data);
	}

	/**
	 * [getUserIntegralIncome 获取用户收之支]
	 * @Author   yangxiaogang
	 * @DateTime 2018-07-23T15:57:32+0800
	 * @return   [type]                   [description]
	 */
	function getUserIntegralIncome(Request $request)
	{
		$validate = Validate::make(
			[
				'openid'	=> 'require',			 	
			] 	
		);
		if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 

		//获取积分数据
		$income = array(); 
		$expenditure = array(); //收入支出
		$usersId = UsersModel::where($request->only(['openid']))->value('id');
		$integrals = UsersModel::where($request->only(['openid']))->value('integral');
		$integralList = Integral::where([ 'users_id'=>$usersId ])->order('id desc')->select();

		//获取leftUsersId的用户信息
		$leftUsersIds = i_array_column(collection($integralList)->toArray(),'left_users_id');		
		$usersInfo = UsersModel::where([ 'id'=> ['in',$leftUsersIds] ])->field('id,nickName,avatarUrl')->select();
		$usersInfo = array_combine(i_array_column(collection($usersInfo)->toArray(),'id'), collection($usersInfo)->toArray());
        //获取活动信息
        $activityIds = i_array_column(collection($integralList)->toArray(),'activity_id');
        $activityInfo = ActivityModel::where([ 'id'=> ['in',$activityIds] ])->field('id,activity_name,activity_img')->select();
        $activityInfo = array_combine(i_array_column(collection($activityInfo)->toArray(),'id'), collection($activityInfo)->toArray());

		foreach ($integralList as $key => $value) {
		    $value->explain = json_decode($value->explain,1);
			if( $value->integral >= 0 )
			{	
				$value->left_users_id && $value->left_users_info = isset($usersInfo[$value->left_users_id])?$usersInfo[$value->left_users_id]:'';//GIN
				$income[] = $value;
			}else{
                $value->activity_id && $value->activity_info = isset($activityInfo[$value->activity_id])?$activityInfo[$value->activity_id]:'';//GIN
                $expenditure[] = $value;//GIN
			}
		}
		return $this->renderSuccess([ 'integral'=>$integrals,'income'=>$income,'expenditure'=>$expenditure ]);
	}	

	/**
     * [getWeixin 获取用户授权信息]
     * @Author   yangxiaogang
     * @DateTime 2018-07-23T19:18:38+0800
     * @param    Request                  $request [description]
     * @return   [type]                            [description]
     */
    function getWeixin(Request $request)
    {
    	$validate = Validate::make(
    		[	
    			'code'	=> 'require'
    		],
    		[
    			'code.require'	=> 'CODE必须！'
    		]
    	);
    	if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 
		$data = WeiXin::get_session_key($request->param('code'));
 		return $this->renderSuccess($data);
    }

    /**
     * 获取用户地址
     * @Author   yangxiaogang
     * @DateTime 2018-08-08T14:47:06+0800
     * @return   [type]                   [description]
     */
    function getUsersSite(Request $request)
    {
    	//参数验证
    	$validate = Validate::make(
    		[	
    			'users_id'	=> 'require'
    		],
    		[
    			'users_id.require'	=> 'UID必须！'
    		]
    	);
    	if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 

		$site = UsersSite::where([ 'users_id'=>trim($request->param('users_id')), 'is_default'=>1 ])->find();

		//获取用户省市区
		$site['city'] =	$this->getPCR($site->city_id);
		 	
 		return $this->renderSuccess($site);
    }

    /**
     * 获取用户地址列表
     * @Author   yangxiaogang
     * @DateTime 2018-08-08T17:07:04+0800
     * @return   [type]                   [description]
     */
    function getAddressList(Request $request)
    {
    	//参数验证
    	$validate = Validate::make(
    		[	
    			'users_id'	=> 'require'
    		],
    		[
    			'users_id.require'	=> 'UID必须！'
    		]
    	);
    	if( ! $validate->check( $request->param() ) )
			return $this->renderError( $validate->getError() ); 

		$siteList = UsersSite::where([ 'users_id'=>trim($request->param('users_id')) ])->select();

		 	
 		return $this->renderSuccess($siteList);
    }

    /**
     * 获取省市区
     * @Author   yangxiaogang
     * @DateTime 2018-08-10T13:52:50+0800
     * @return   [type]                   [description]
     */
    function getCity(Request $request)
    {	
    	if( Cache::get('city') )
    		return $this->renderSuccess( Cache::get('city') );
    	//获取省市区列表
    	$list = City::where([])->select();

    	//省市区数据组装
    	$cityList = [];
    	foreach ($list as $key => $value) 
    	{	
    		//省
    		if( $value->pid === 1 )
    		{	
       			$pid = $value->id;
    			//获取市
    			$city = [];
    			array_walk($list, function($val)use ($pid,&$city,$list){    			 
    				if($val->pid === $pid )
    				{
    					//区
	    				$area = [];
	    				$pid = $val->id;
	    				array_walk($list, function($v)use( $pid,&$area){
	    					$v->pid === $pid && $area[] = $v;
	    				});
	    				$val->next = $area;
	    				$city[] = $val;
    				} 
       			});
    			$value->next = $city;
    			$cityList[] = $value;
    		}
    	}
    	Cache::set('city',$cityList);
    	return $this->renderSuccess($cityList);
    }

    /**
     * 添加用户地址
     * @Author   yangxiaogang
     * @DateTime 2018-08-15T15:41:39+0800
     */
    function addCity(Request $request)
    {
    	//参数验证
    	$validate = Validate::make(
    		[	
    			'users_id'		=> 'require',
    			'users_name'	=> 'require',
    			'users_tel'		=> 'require',
    			'city_id'		=> 'require',
    			'details'		=> 'require',
    			'postcode'		=> 'require',
    			'is_default'	=> 'require'
    		],
    		[
    			'users_id.request'		=> '请先授权登录',
    			'users_name.require'	=> '填写收货人姓名',
    			'users_tel.require'		=> '填写收货人手机号码',
    			'city_id.require'		=> '请选择省市区',
    			'details.require'		=> '请填写详细地址',
    			'postcode.require'		=> '填写邮编',
    			'is_default.require'	=> 'aaaaaaaaa'
    		]	
    	);
    	$postParam = trimArray( $request->only(['users_id','users_name','users_tel','city_id','details','postcode','is_default']) ); 
    	if( ! $validate->check( $postParam ) )
			return $this->renderError( $validate->getError() ); 
 		
		//验证用户id
		if( ! UsersModel::find($postParam['users_id']) )
			return $this->renderError('用户不存在');


		//入库
		$data = UsersSite::create($postParam);
		//return $this->renderSuccess();
		if( $postParam['is_default'] )
		{	
			$usersSiteIds = UsersSite::where([ 'users_id'=>$data->users_id ])->column('id');
			$usersSiteIds = array_diff($usersSiteIds,[ $data->id ]);	
			UsersSite::isDefaultSet($usersSiteIds);
		} 

		return $this->renderSuccess($data);
    }


    /**
     * 意见反馈
     * @author huanghao
     * time 2018/10/10
     */
    public function feedBack(){
        $openId = input('openid', '','htmlspecialchars');
        $content = input('content', '', 'htmlspecialchars');
        if(empty($openId) || empty($content)) {
            return $this->renderError('参数错误');
        }
        $uid = getUidByOpenid($openId);
        $data = ['uid' => $uid, 'content' => $content];
        $model = new FeedBack();
        if($model->allowField(true)->save($data)){
            return $this->renderSuccess('');
        }else{
            return $this->renderError('操作失败');
        }

    }

    //todo 添加取消收藏的方法




}