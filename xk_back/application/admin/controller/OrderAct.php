<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 16:45
 */

namespace app\admin\controller;
use  app\admin\model\UsersActivityConversion as OrderModel;
use  app\admin\model\Logistics as LogisticsModel;
use think\Request;

/**
 * 活动订单管理
 * @author yangle
 * Class Administrator
 * @package app\admin\controller
 */
class OrderAct extends Admin
{
    /**
     * 订单列表
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $request = Request::instance();
        //当前所在页面
        $nowPage = $request->param('nowPage')?:1;
        //搜索内容
        $keyword = $request->param('search')?:'';
        $whereCondition = [];
        if ($keyword) {
            $whereCondition['order_number'] = ['like','%'.$keyword.'%'];
        }
        $pageLimit = 15;
        $uid = $request->param('uid') ? : '';
        if ($uid) {
            $whereCondition['O.users_id'] = $uid;
        }
        $activity_id = $request->param('activity_id') ? : '';
        if ($activity_id) {
            $whereCondition['O.activity_id'] = $activity_id;
        }
        $orderModel = new OrderModel();
        $list = $orderModel
            ->alias('O')
            ->field('O.*, u.nickName, a.activity_name')
            ->join('users u','O.users_id = u.id','left')
            ->join('activity a','O.activity_id = a.id','left')
            ->where($whereCondition)
            ->limit(($nowPage - 1)*$pageLimit,$pageLimit)
            ->order("id desc")
            ->select();

        foreach ($list as $k => &$v) {
            switch ($v['status']) {
                case '0':
                    $v['status'] = '待支付';
                    break;
                case '1':
                    $v['status'] = '待发货';
                    break;
                case '2':
                    $v['status'] = '待收货';
                    break;
                case '5':
                    $v['status'] = '已完成';
                    break;
                case '6':
                    $v['status'] = '售后';
                    break;
                case '9':
                    $v['status'] = '已取消';
                    break;
            }
        }
        $count = $orderModel
            ->alias('O')
            ->join('users u','O.users_id = u.id','left')
            ->where($whereCondition)
            ->count();

        $PageCount = $count / $pageLimit;
        if(is_float($PageCount)||$PageCount == 0){
            $PageCount = intval($PageCount) + 1;
        }
        if($nowPage>$PageCount)$nowPage = $PageCount;
        $this->assign('OrderList',$list);
        $this->assign('OrderCount',$count);
        $this->assign('NowPage', $nowPage);
        $this->assign('Keyword', $keyword);
        $this->assign('PageCount',$PageCount);
        $this->assign('uid',$uid);
        $this->assign('activity_id',$activity_id);
        return view();
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($id = '')
    {
        if (!$id) {
            return CreateInfoPage("发生错误...","该订单不存在或已被删除!","返回",url('index'));
        }
        $orderModel = new OrderModel();
        $info = $orderModel
            ->alias('o')
            ->field('o.*, u.nickName, a.activity_name, a.activity_img')
            ->join('users u','o.users_id = u.id','left')
            ->join('activity a','o.activity_id = a.id','left')
            ->where('o.id',$id)
            ->find();
        switch ($info['status']) {
            case '0':
                $info['status'] = '待支付';
                break;
            case '1':
                $info['status'] = '待发货';
                break;
            case '2':
                $info['status'] = '待收货';
                break;
            case '5':
                $info['status'] = '已完成';
                break;
            case '6':
                $info['status'] = '售后';
                break;
            case '9':
                $info['status'] = '已取消';
                break;
        }
        $this->assign('info',$info);
        return $this->fetch('form');
    }

    public function edit($id = '')
    {
        if (!$id) {
            return CreateInfoPage("发生错误...","该订单不存在或已被删除!","返回",url('index'));
        }
        $info = OrderModel::get($id);
        if ($this->request->isPost()) {
            $data = input();
            $orderModel = new OrderModel();
            if (!isset($data['status']) || $data['status'] == $info['status']) {
                return CreateInfoPage("发生错误...","订单状态没有改变!","返回",url('edit',['id'=>$id]));
            } else if ($data['status'] == 2) {
                if (empty($data['logistics_id']) || empty($data['logistics_number']) ) {
                    return CreateInfoPage("缺少数据...","请完善物流公司信息!","返回",url('edit',['id'=>$id]));
                }
                $data['send_time'] = time();
            } else {
                unset($data['logistics_id'],$data['logistics_number']);
            }
            $res = $orderModel->isUpdate(true)->save($data,['id'=>$id]);
            if ($res) {
                return CreateInfoPage("编辑成功","编辑成功!","返回",url('index'));
            } else {
                return CreateInfoPage("编辑失败","编辑失败!","返回",url('index'));
            }
        } else {
            switch ($info['status']) {
                default: $status = '待支付';break;
                case '1': $status = '待发货';break;
                case '2': $status = '待收货';break;
                case '5': $status = '已完成';break;
                case '6': $status = '售后';break;
                case '9': $status = '已取消';break;
            }
            $this->assign('status',$status);
            $this->assign('info',$info);
            $logistics = logisticsModel::select()->toArray();
            $this->assign('logistics',$logistics);
            return view();
        }
    }

    /**
     * 物流信息
     * @param string $order_sn
     * @param string $com
     * @return mixed|\think\response\View
     */
    public function logistics($order_sn = '', $com = '')
    {
        if (!$order_sn) {
            return CreateInfoPage("发生错误...","没有物流信息!","返回",url('index'));
        }
        //通过订单号查询物流进度
        //参数设置
        $post_data = array();
        $post_data["customer"] = '1DBA65AFDAB5675D034736D183846176';
        $key= 'btUMLsJE6861' ;
        $post_data["param"] = '{"com":"'.$com.'","num":"'.$order_sn.'"}';

        $url='http://poll.kuaidi100.com/poll/query.do';
        $url='http://www.kuaidi100.com/query?type='.$com.'&postid='.$order_sn;
        $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }
        $post_data=substr($o,0,-1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);
        $data = str_replace("\"",'"',$result );
        $data = json_decode($data,true);
        if ($data['status'] != 200) {
            return CreateInfoPage("发生错误...",$data['message'],"返回",url('index'));
        }
        $company = LogisticsModel::where('number',$com)->value('name');
        $send_time = OrderModel::where('logistics_number',$order_sn)->value('send_time');
        switch ($data['state']) {
            case '1' :
                $data['state'] = '已揽收';
                break;
            case '2' :
                $data['state'] = '疑难';
                break;
            case '3' :
                $data['state'] = '已签收';
                break;
            default : $data['state'] = '在途中';
            break;
        }
        $this->assign('data',$data);
        $this->assign('company',$company);
        $this->assign('send_time',$send_time);
        return view();
    }

    public function payCode($id)
    {
        $order_info = OrderModel::get($id);
        $order_sn = $order_info['order_number'];
        $total_fee = $order_info['intregral'];
        $config = config('wx_pay');
        //统一下单参数构造
        $unifiedorder = array(
            'appid'			=> $config['appid'],
            'body'			=> '斜厍--订单支付',
            'mch_id'		=> $config['pay_mchid'],
            'nonce_str'		=> getNonceStr(),
            'notify_url'	=> 'https://'.$_SERVER['HTTP_HOST'].'/index.php/api/Pay/notify',
            'out_trade_no'	=> $order_sn.'_'.rand(1000,9999),
//            'spbill_create_ip'	=> '192.168.0.38',
            'spbill_create_ip'	=> getIP(),
            'total_fee'		=> $total_fee * 100,
            'trade_type'	=> 'NATIVE'
        );
        $unifiedorder['sign'] = makeSign($unifiedorder);
        //请求数据
        $xmldata = array2xml($unifiedorder);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//订单支付
        $res = http_request($url, $xmldata);
        if(!$res){
            return $this->renderError("Can't connect the server");
        }
        $content = xml2array($res);
        if($content['RETURN_CODE'] == 'FAIL'){
            return $this->renderError($content['RETURN_MSG']);
        }
        $code_url = urlencode($content['CODE_URL']);
        $this->assign('code_url',$code_url);
        return view('payCode');
    }

    public function qrCode($code_url = ''){
        require_once ROOT_PATH.'/application/weixin/phpqrcode.php';
        $qrCode = new \QRcode();
        $code_url = urldecode($code_url);
        if(substr($code_url, 0, 6) == "weixin"){
            $qrCode->png($code_url,false,'L',10);
        }
    }

    //todo 看一下有没有什么关联的也要删掉
    public function delete($id){
        $order = OrderModel::get($id)->toArray();
        if(!$order){
            return CreateInfoPage("很抱歉...","该订单不存在或已被删除!","返回",url('admin/order/index'));
        }else{
            $order->delete();
            return CreateInfoPage("操作成功","反馈已成功删除!","返回",url('admin/order/index'));
        }
        return CreateInfoPage("操作成功","操作成功!","返回",url('index'));
    }
}