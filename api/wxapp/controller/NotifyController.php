<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2021/12/17
 * Time: 11:08
 */

namespace api\wxapp\controller;

use initmodel\MemberModel;
use plugins\weipay\lib\PayController;
use think\facade\Db;
use think\facade\Log;

class NotifyController extends AuthController
{


    public function initialize()
    {
        parent::initialize();//初始化方法


        //获取初始化信息
        $plugin_config        = cmf_get_option('weipay');
        $this->wx_system_type = $plugin_config['wx_system_type'];//默认 读配置可手动修改
        if ($this->wx_system_type == 'wx_mini') {//wx_mini:小程序
            $appid     = $plugin_config['wx_mini_app_id'];
            $appsecret = $plugin_config['wx_mini_app_secret'];
        } else {//wx_mp:公众号
            $appid     = $plugin_config['wx_mp_app_id'];
            $appsecret = $plugin_config['wx_mp_app_secret'];
        }
        $this->wx_config = [
            //微信基本信息
            'token'             => $plugin_config['wx_token'],
            'wx_mini_appid'     => $plugin_config['wx_mini_app_id'],//小程序 appid
            'wx_mini_appsecret' => $plugin_config['wx_mini_app_secret'],//小程序 secret
            'wx_mp_appid'       => $plugin_config['wx_mp_app_id'],//公众号 appid
            'wx_mp_appsecret'   => $plugin_config['wx_mp_app_secret'],//公众号 secret
            'appid'             => $appid,//读取默认 appid
            'appsecret'         => $appsecret,//读取默认 secret
            'encodingaeskey'    => $plugin_config['wx_encodingaeskey'],
            // 配置商户支付参数
            'mch_id'            => $plugin_config['wx_mch_id'],
            'mch_key'           => $plugin_config['wx_v2_mch_secret_key'],
            // 配置商户支付双向证书目录 （p12 | key,cert 二选一，两者都配置时p12优先）
            //	'ssl_p12'        => __DIR__ . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . '1332187001_20181030_cert.p12',
            'ssl_key'           => './upload/' . $plugin_config['wx_mch_secret_cert'],
            'ssl_cer'           => './upload/' . $plugin_config['wx_mch_public_cert_path'],
            // 配置缓存目录，需要拥有写权限
            'cache_path'        => './wx_cache_path',
            'wx_system_type'    => $this->wx_system_type,//wx_mini:小程序 wx_mp:公众号
            'wx_notify_url'     => cmf_get_domain() . $plugin_config['wx_notify_url'],//微信支付回调地址
        ];
    }


    /**
     * 微信支付回调-微信回调用
     * api: /wxapp/notify/wxPayNotify
     */
    public function wxPayNotify()
    {
        $OrderPayModel = new \initmodel\OrderPayModel();//支付记录表

        $wechat = new \WeChat\Pay($this->wx_config);
        // 4. 获取通知参数
        $result = $wechat->getNotify();
        Log::write($result, '微信回调用-wx_pay_notify');


        if ($result['return_code'] === 'SUCCESS' && $result['result_code'] === 'SUCCESS') {
            Log::write("微信回调用-wx_pay_notify:支付成功,修改订单状态;支付单号[{$result['out_trade_no']}]");
            //Log::write($result);

            $pay_num        = $result['out_trade_no'];
            $pay_amount     = round($result['total_fee'] / 100, 2);//支付金额(元)
            $transaction_id = $result['transaction_id'];


            /** 处理订单状态等操作 **/
            $this->processOrder($pay_num);//微信官方支付回调


            /** 更改支付记录,状态 */
            $result['time']          = time();
            $pay_update['pay_time']  = time();
            $pay_update['trade_num'] = $transaction_id ?? '9880' . cmf_order_sn(8);
            $pay_update['status']    = 2;
            $pay_update['notify']    = serialize($result);
            $OrderPayModel->where('pay_num', '=', $pay_num)->strict(false)->update($pay_update);


            // 返回接收成功的回复
            ob_clean();
            echo $wechat->getNotifySuccessReply();

        } else {
            Log::write('event_type:' . $result);
        }
    }


    /**
     * /api/wxapp/notify/returnCallback
     *
     */
    public function returnCallback()
    {
        $data = $this->request->param();
        Log::write($data, 'returnCallback');
    }


    /**
     * 支付宝回调
     * /api/wxapp/notify/aliPayNotify
     */
    public function aliPayNotify()
    {
        $Pay = new PayController();

        $pay_data = $Pay->ali_pay_notify();
        if ($pay_data != 1) Log::write($pay_data['msg'], 'wx_pay_notify');

        $data = $pay_data['date'];

        if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {

            Log::write($data, '订单回调');

            $order_num  = $data['out_trade_no'];
            $pay_amount = $data['total_amount'];

            $order_info = Db::name('shop_order')->where('order_num', $order_num)->find();

            Log::write($order_info, 'order_info');
            if (!empty($order_info)) {
                if ($order_info['status'] == 1) {
                    if ($order_info['amount'] != $pay_amount) {
                        Log::write('金额错误', $order_num);
                    } else {
                        //支付完成更改订单状态
                        Log::write('订单支付成功', $order_num);
                    }
                    ob_clean();
                    return $Pay->ali_pay_success();
                } else {
                    Log::write('订单状态错误', $order_num);
                }
            } else {
                Log::write('订单不存在', $order_num);
            }
        } else {
            Log::write('trade_status:' . $data['trade_status']);
        }
    }


    /**
     *
     * 微信支付回调 测试
     *
     *   test_environment: http://love0212.ikun/api/wxapp/notify/wx_pay_notify_test?pay_num=1000
     *   official_environment: https://hl212.wxselling.com/api/wxapp/notify/wx_pay_notify_test?pay_num=1000
     *   api:   /wxapp/notify/wx_pay_notify_test?pay_num=1000
     */
    public function wx_pay_notify_test()
    {
        $OrderPayModel = new \initmodel\OrderPayModel();//支付记录表

        $params  = $this->request->param();
        $pay_num = $params['pay_num'];


        //查询出支付信息,以及关联的订单号
        $pay_info = $OrderPayModel->where('pay_num', $pay_num)->find();


        /**  查询订单条件  */
        $order_num = $pay_info['order_num'];
        $map       = [];
        $map[]     = ['order_num', '=', $order_num];


        /**
         * 更新订单 默认字段
         */
        $updata['update_time'] = time();
        $updata['pay_time']    = time();
        $updata['status']      = 2;


        Log::write('wxPayNotifyTest:pay_info');
        Log::write($pay_info);


        /** 处理订单状态 **/
        $pay_result = $this->processOrder($pay_num);//本地测试支付回调


        //更改支付记录,状态
        $data['time']            = time();
        $pay_update['pay_time']  = time();
        $pay_update['trade_num'] = $transaction_id ?? '9880' . cmf_order_sn(8);
        $pay_update['status']    = 2;
        $pay_update['notify']    = serialize($data);
        $OrderPayModel->where('pay_num', '=', $pay_num)->strict(false)->update($pay_update);


        $this->success('操作成功', $pay_result['order_info']);
    }


    /**
     * 支付成功回调
     * @param $pay_num  支付单号
     */
    public function processOrder($pay_num)
    {
        $OrderPayModel             = new \initmodel\OrderPayModel();//支付记录表
        $MemberRecommendOrderModel = new \initmodel\MemberRecommendOrderModel(); //管理费   (ps:InitModel)
        $MemberRecommendModel      = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $ShopModel                 = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)
        $ShopManageOrderModel      = new \initmodel\ShopManageOrderModel(); //管理费订单管理   (ps:InitModel)
        $MemberVipOrderModel       = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)
        $MemberModel               = new \initmodel\MemberModel();//用户管理
        $MemberRechargeOrderModel  = new \initmodel\MemberRechargeOrderModel(); //充值订单   (ps:InitModel)
        $MemberVipModel            = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)


        /** 查询出支付信息,以及关联的订单号 */
        $pay_info = $OrderPayModel->where('pay_num', $pay_num)->find();
        Log::write('wxPayNotify:pay_info');
        Log::write($pay_info);

        /**  查询订单条件  */
        $order_num = $pay_info['order_num'];
        $map       = [];
        $map[]     = ['order_num', '=', $order_num];


        /** 更新订单 默认字段  */
        $updata['update_time'] = time();
        $updata['pay_time']    = time();
        $updata['is_pay']      = 2;
        $updata['status']      = 2;

        //引荐人管理费 & 类型注意
        if ($pay_info['order_type'] == 10) {
            $result     = $MemberRecommendOrderModel->where($map)->strict(false)->update($updata);//更新订单信息
            $order_info = $MemberRecommendOrderModel->where($map)->find();//查询订单信息

            //更新引荐人到期时间
            $MemberRecommendModel->where('id', '=', $order_info['user_id'])->strict(false)->update([
                'is_manage'   => 2,
                'end_time'    => $order_info['end_time'],
                'update_time' => time(),
            ]);
        }

        //店铺管理费 & 类型注意
        if ($pay_info['order_type'] == 20) {
            $result     = $ShopManageOrderModel->where($map)->strict(false)->update($updata);//更新订单信息
            $order_info = $ShopManageOrderModel->where($map)->find();//查询订单信息

            //更新店铺到期时间
            $ShopModel->where('id', '=', $order_info['shop_id'])->strict(false)->update([
                'is_manage'   => 2,
                'end_time'    => $order_info['end_time'],
                'update_time' => time(),
            ]);
        }

        //开通vip & 类型注意
        if ($pay_info['order_type'] == 30) {
            $result     = $MemberVipOrderModel->where($map)->strict(false)->update($updata);//更新订单信息
            $order_info = $MemberVipOrderModel->where($map)->find();//查询订单信息


            //更新vip到期时间
            $MemberModel->where('id', '=', $order_info['user_id'])->strict(false)->update([
                'is_vip'      => 1,
                'vip_id'      => $order_info['vip_id'],
                'end_time'    => $order_info['end_time'],
                'update_time' => time(),
            ]);


            //给引荐人送佣金
            $vip_info    = $MemberVipModel->where('id', '=', $order_info['vip_id'])->find();
            $member_info = $MemberModel->where('id', '=', $order_info['user_id'])->find();

            if ($vip_info['commission'] && $member_info['pid']) {
                $balance = round($order_info['amount'] * ($vip_info['commission'] / 100), 2);
                //签到奖励
                $remark = "操作人[佣金];操作说明[邀请用户:{$order_info['user_id']},奖励:{$balance}];操作类型[佣金奖励];";//管理备注
                $MemberRecommendModel->inc_balance($member_info['pid'], $balance, '佣金', $remark, $order_info['id'], $order_info['order_num'], 50);
            }
        }


        //充值 & 类型注意
        if ($pay_info['order_type'] == 40) {
            $result     = $MemberRechargeOrderModel->where($map)->strict(false)->update($updata);//更新订单信息
            $order_info = $MemberRechargeOrderModel->where($map)->find();//查询订单信息

            //充值
            $remark = "操作人[充值,支付金额{$order_info['amount']}];操作说明[充值{$order_info['balance']},赠送{$order_info['give_balance']},总金额{$order_info['total_balance']}];操作类型[充值];";//管理备注
            $MemberModel->inc_balance($order_info['user_id'], $order_info['total_balance'], '充值', $remark, $order_info['id'], $order_info['order_num'], 400);
        }


//        Log::write('processOrder:order_info');
//        Log::write($order_info);

        return ['result' => $result, 'order_info' => $order_info];
    }
}