<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"MemberVip",
 *     "name_underline"          =>"member_vip",
 *     "controller_name"         =>"MemberVip",
 *     "table_name"              =>"member_vip",
 *     "remark"                  =>"用户等级"
 *     "api_url"                 =>"/api/wxapp/member_vip/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-26 11:10:17",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\MemberVipController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/member_vip/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/member_vip/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class MemberVipController extends AuthController
{


    public function initialize()
    {
        //用户等级

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/member_vip/index
     * https://hl212.wxselling.com/api/wxapp/member_vip/index
     */
    public function index()
    {
        $MemberVipInit  = new \init\MemberVipInit();//用户等级   (ps:InitController)
        $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)

        $result = [];

        $this->success('用户等级-接口请求成功', $result);
    }


    /**
     * 用户等级 列表
     * @OA\Post(
     *     tags={"用户等级"},
     *     path="/wxapp/member_vip/find_member_vip_list",
     *
     *
     *
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="(选填)关键字搜索",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *
     *
     *
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         description="上级id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *
     *    @OA\Parameter(
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member_vip/find_member_vip_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_vip/find_member_vip_list
     *   api:  /wxapp/member_vip/find_member_vip_list
     *   remark_name: 用户等级 列表
     *
     */
    public function find_member_vip_list()
    {
        $this->checkAuth();
        $MemberVipInit  = new \init\MemberVipInit();//用户等级   (ps:InitController)
        $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;
        $params["gender"]  = $this->user_info['gender'];

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        $where[] = ["pid", "=", $params["pid"] ?? 0];
        if ($params["keyword"]) $where[] = ["name", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $MemberVipInit->get_list($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 开通vip
     * @OA\Post(
     *     tags={"用户等级"},
     *     path="/wxapp/member_vip/add_order",
     *
     *
     *
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="(选填)关键字搜索",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="vip_id",
     *         in="query",
     *         description="vip_id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *
     *    @OA\Parameter(
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member_vip/add_order
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_vip/add_order
     *   api:  /wxapp/member_vip/add_order
     *   remark_name: 开通vip
     *
     */
    public function add_order()
    {
        $this->checkAuth();

        $MemberVipOrderInit  = new \init\MemberVipOrderInit();//充值会员   (ps:InitController)
        $MemberVipOrderModel = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)
        $MemberVipModel      = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)


        $params = $this->request->param();


        $vip_info = $MemberVipModel->where('id', '=', $params['vip_id'])->find();
        if (empty($vip_info)) $this->error("非法操作!");
        $price = $vip_info['price'];
        if ($this->user_info['gender'] == '女') $price = $vip_info['price2'];

        //检测用户是否开通会员
        //if ($this->user_info['end_time']>time()) $this->error('当前已经是会员身份');


        $params['amount']    = $price;
        $params['openid']    = $this->openid;
        $params['user_id']   = $this->user_id;
        $order_num           = $this->get_only_num('member_vip_order');
        $params['order_num'] = $order_num;
        $params['pid']       = $this->user_info['pid'];
        $params['vip_name']  = $vip_info['name'];
        $params['day']       = $vip_info['day'];
        $params['end_time']  = time() + ($vip_info['day'] * 86400);


        $result = $MemberVipOrderInit->api_edit_post($params);
        if (empty($result)) $this->error('失败请重试');

        $this->success('请支付', ['order_num' => $order_num, 'order_type' => 30]);
    }


    /**
     * 开通记录
     * @OA\Post(
     *     tags={"用户等级"},
     *     path="/wxapp/member_vip/find_order_list",
     *
     *
     *
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="(选填)关键字搜索",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member_vip/find_order_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_vip/find_order_list
     *   api:  /wxapp/member_vip/find_order_list
     *   remark_name: 开通记录
     *
     */
    public function find_order_list()
    {
        $this->checkAuth();


        $MemberVipOrderInit  = new \init\MemberVipOrderInit();//充值会员   (ps:InitController)
        $MemberVipOrderModel = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        $where[] = ['status', '>=', 2];
        $where[] = ['user_id', '=', $this->user_id];


        $result = $MemberVipOrderInit->get_list($where, $params);
        if (empty($result)) $this->error("暂无信息!");


        $this->success("请求成功!", $result);
    }


    /**
     * 退款
     * @OA\Post(
     *     tags={"用户等级"},
     *     path="/wxapp/member_vip/refund",
     *
     *
     *
     *     @OA\Parameter(
     *         name="order_num",
     *         in="query",
     *         description="单号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="姓名",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="手机号",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member_vip/refund
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_vip/refund
     *   api:  /wxapp/member_vip/refund
     *   remark_name: 退款
     *
     */
    public function refund()
    {
        $this->checkAuth();

        $MemberVipOrderInit  = new \init\MemberVipOrderInit();//充值会员   (ps:InitController)
        $MemberVipOrderModel = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['status', '=', 2];
        $where[] = ['user_id', '=', $this->user_id];
        $where[] = ['order_num', '=', $params['order_num']];


        $result = $MemberVipOrderModel->where($where)->update([
            'status'      => 3,
            'username'    => $params['username'],
            'phone'       => $params['phone'],
            'refund_time' => time(),
            'update_time' => time(),
        ]);
        if (empty($result)) $this->error("非法操作");


        $this->success("申请成功");
    }


}
