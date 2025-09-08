<?php

namespace api\wxapp\controller;

use plugins\weipay\lib\PayController;
use think\facade\Db;
use think\facade\Log;

class OrderPayController extends AuthController
{

    public function initialize()
    {
        parent::initialize();//初始化方法
    }


    /**
     * 微信公众号支付
     * @OA\Post(
     *     tags={"订单支付"},
     *     path="/wxapp/order_pay/wx_pay_mp",
     *
     *
     * 	   @OA\Parameter(
     *         name="openid",
     *         in="query",
     *         description="openid",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     * 	   @OA\Parameter(
     *         name="order_num",
     *         in="query",
     *         description="order_num",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/order_pay/wx_pay_mp
     *   official_environment:  https://hl212.wxselling.com/api/wxapp/order_pay/wx_pay_mp
     *   api: /wxapp/order_pay/wx_pay_mp
     *   remark_name: 微信公众号支付
     *
     */
    public function wx_pay_mp()
    {
        //$this->checkAuth();
        $params = $this->request->param();
        $openid = $this->openid;

        $Pay                       = new PayController();
        $OrderPayModel             = new \initmodel\OrderPayModel();
        $MemberRecommendOrderModel = new \initmodel\MemberRecommendOrderModel(); //管理费   (ps:InitModel)
        $ShopManageOrderModel      = new \initmodel\ShopManageOrderModel(); //管理费订单管理   (ps:InitModel)
        $MemberVipOrderModel       = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)
        $MemberRechargeOrderModel  = new \initmodel\MemberRechargeOrderModel(); //充值订单   (ps:InitModel)

        $map   = [];
        $map[] = ['order_num', '=', $params['order_num']];


        //查询引荐人订单信息
        if ($params['order_type'] == 10) $order_info = $MemberRecommendOrderModel->where($map)->find();

        //查询店铺订单信息
        if ($params['order_type'] == 20) $order_info = $ShopManageOrderModel->where($map)->find();

        //用户开通vip
        if ($params['order_type'] == 30) $order_info = $MemberVipOrderModel->where($map)->find();

        //充值
        if ($params['order_type'] == 40) $order_info = $MemberRechargeOrderModel->where($map)->find();


        if (empty($order_info)) $this->error('订单不存在');
        if ($order_info['amount'] < 0.01) $this->error('订单错误');


        //订单金额&&订单号
        $amount    = $order_info['amount'] ?? 0.01;
        $order_num = $order_info['order_num'] ?? cmf_order_sn(6);

        //$amount = 0.01;

        //支付记录插入一条记录
        $pay_num = $OrderPayModel->add($openid, $order_num, $amount, $params['order_type'], 1, $order_info['id']);
        $result  = $Pay->wx_pay_mp($pay_num, $amount, $openid);


        if ($result['code'] != 1) {
            if (strstr($result['msg'], '此商家的收款功能已被限制')) $this->error('支付失败,请联系客服!错误码:pay_limit');
            $this->error($result['msg']);
        }


        //将订单号,支付单号返回给前端
        $result['data']['order_num'] = $order_num;
        $result['data']['pay_num']   = $pay_num;


        $this->success('请求成功', $result['data']);
    }


    /**
     * 微信小程序支付
     * @OA\Post(
     *     tags={"订单支付"},
     *     path="/wxapp/order_pay/wx_pay_mini",
     *
     *
     * 	   @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *    @OA\Parameter(
     *         name="order_type",
     *         in="query",
     *         description="10引荐人管理费订单  20店铺管理费订单   30用户开通vip  40充值订单",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     * 	   @OA\Parameter(
     *         name="order_num",
     *         in="query",
     *         description="order_num",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/order_pay/wx_pay_mini
     *   official_environment: https://hl212.wxselling.com/api/wxapp/order_pay/wx_pay_mini
     *   api: /wxapp/order_pay/wx_pay_mini
     *   remark_name: 微信小程序支付
     *
     */
    public function wx_pay_mini()
    {
        $this->checkAuth();

        $params = $this->request->param();
        $openid = $this->openid;

        $Pay                       = new PayController();
        $OrderPayModel             = new \initmodel\OrderPayModel();
        $MemberRecommendOrderModel = new \initmodel\MemberRecommendOrderModel(); //管理费   (ps:InitModel)
        $ShopManageOrderModel      = new \initmodel\ShopManageOrderModel(); //管理费订单管理   (ps:InitModel)
        $MemberVipOrderModel       = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)
        $MemberRechargeOrderModel  = new \initmodel\MemberRechargeOrderModel(); //充值订单   (ps:InitModel)

        $map   = [];
        $map[] = ['order_num', '=', $params['order_num']];


        //查询引荐人订单信息
        if ($params['order_type'] == 10) $order = $MemberRecommendOrderModel->where($map)->find();

        //查询店铺订单信息
        if ($params['order_type'] == 20) $order = $ShopManageOrderModel->where($map)->find();

        //用户开通vip
        if ($params['order_type'] == 30) $order = $MemberVipOrderModel->where($map)->find();

        //充值
        if ($params['order_type'] == 40) $order = $MemberRechargeOrderModel->where($map)->find();


        //订单金额&&订单号
        $amount    = $order['amount'] ?? 0.01;
        $order_num = $order['order_num'] ?? cmf_order_sn(6);

        //支付记录插入一条记录
        $pay_num = $OrderPayModel->add($openid, $order_num, $amount, $params['order_type']);
        $res     = $Pay->wx_pay_mini($pay_num, $amount, $openid);


        if ($res['code'] != 1) $this->error($res['msg']);
        $this->success('请求成功', $res['data']);
    }


    // 测试用 https://hl212.wxselling.com/api/wxapp/order_pay/wx_pay_mini2
    public function wx_pay_mini2()
    {

        $params = $this->request->param();
        $openid = $this->openid;

        $Pay = new PayController();


        $map   = [];
        $map[] = ['order_num', '=', $params['order_num']];


        //查询订单信息
        if ($params['order_type'] == 1) {
            $order = null;
        }


        //订单金额&&订单号
        $amount = $order['amount'] ?? 0.01;

        //支付记录插入一条记录
        $pay_num = cmf_order_sn(6);
        $openid  = 'o46yr4hMXOae1P0ZAfMa9Z9HtL3Y';
        $res     = $Pay->wx_pay_mini($pay_num, $amount, $openid);


        if ($res['code'] != 1) $this->error($res['msg']);
        $this->success('请求成功', $res['data']);
    }


    /**
     * 微信订单退款 测试
     *
     * @return void
     *
     *   test_environment: http://love0212.ikun/api/wxapp/order_pay/wx_pay_refund_test
     *   official_environment: https://hl212.wxselling.com/api/wxapp/order_pay/wx_pay_refund_test
     *   api: /wxapp/order_pay/wx_pay_refund_test
     *
     *   order_num 订单号
     *   amount 退款金额
     */
    public function wx_pay_refund_test()
    {
        $params = $this->request->param();
        //给用户退款
        $Pay           = new PayController();
        $OrderPayModel = new \initmodel\OrderPayModel();

        $map       = [];
        $map[]     = ['order_num', '=', $params['order_num']];//实际订单号
        $map[]     = ['status', '=', 2];//已支付
        $pay_info  = $OrderPayModel->where($map)->find();//支付记录表
        $amount    = $pay_info['amount'];//支付金额&全部退款
        $order_num = $pay_info['pay_num'];//支付单号


        $refund_result = $Pay->wx_pay_refund($order_num, $amount);
        $refund_result = json_decode($refund_result['data'], true);


        if (!isset($refund_result['amount'])) $this->error($refund_result['message']);

        $this->success('请求成功', $refund_result);
    }


}