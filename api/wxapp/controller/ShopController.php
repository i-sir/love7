<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"Shop",
 *     "name_underline"          =>"shop",
 *     "controller_name"         =>"Shop",
 *     "table_name"              =>"shop",
 *     "remark"                  =>"店铺管理"
 *     "api_url"                 =>"/api/wxapp/shop/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-26 09:27:36",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\ShopController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/shop/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/shop/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class ShopController extends AuthController
{


    public function initialize()
    {
        //店铺管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/shop/index
     * https://xcxkf186.aubye.com/api/wxapp/shop/index
     */
    public function index()
    {
        $ShopInit  = new \init\ShopInit();//店铺管理   (ps:InitController)
        $ShopModel = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)

        $result = [];

        $this->success('店铺管理-接口请求成功', $result);
    }


    /**
     * 店铺类型 列表
     * @OA\Post(
     *     tags={"店铺管理"},
     *     path="/wxapp/shop/find_class_list",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop/find_class_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop/find_class_list
     *   api:  /wxapp/shop/find_class_list
     *   remark_name: 店铺类型 列表
     *
     */
    public function find_class_list()
    {
        $ShopClassInit  = new \init\ShopClassInit();//店铺类型   (ps:InitController)
        $ShopClassModel = new \initmodel\ShopClassModel(); //店铺类型   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        if ($params["keyword"]) $where[] = ["name", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $ShopClassInit->get_list($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 店铺管理 列表
     * @OA\Post(
     *     tags={"店铺管理"},
     *     path="/wxapp/shop/find_shop_list",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop/find_shop_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop/find_shop_list
     *   api:  /wxapp/shop/find_shop_list
     *   remark_name: 店铺管理 列表
     *
     */
    public function find_shop_list()
    {
        //$this->checkAuth();

        $ShopInit  = new \init\ShopInit();//店铺管理   (ps:InitController)
        $ShopModel = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where = [];
        if ($params["keyword"]) $where[] = ["name", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];
        $where[] = ['status', '=', 2];
        $where[] = ['end_time', '>', time()];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $params['is_api']        = true;
        $result                  = $ShopInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 店铺管理 详情
     * @OA\Post(
     *     tags={"店铺管理"},
     *     path="/wxapp/shop/find_shop",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop/find_shop
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop/find_shop
     *   api:  /wxapp/shop/find_shop
     *   remark_name: 店铺管理 详情
     *
     */
    public function find_shop()
    {
        $ShopInit  = new \init\ShopInit();//店铺管理    (ps:InitController)
        $ShopModel = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)

        //参数
        $params = $this->request->param();


        //查询条件
        $where = [];
        if (empty($params['id'])) $where[] = ["user_id", "=", $this->user_id];
        if ($params['id']) $where[] = ["id", "=", $params['id']];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $params['is_api']        = true;
        $result                  = $ShopInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 店铺管理 编辑&添加
     * @OA\Post(
     *     tags={"店铺管理"},
     *     path="/wxapp/shop/edit_shop",
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
     *
     *
     *    @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="店铺名字",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="class_id",
     *         in="query",
     *         description="分类id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="logo_image",
     *         in="query",
     *         description="logo",
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
     *    @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="负责人",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
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
     *         name="date",
     *         in="query",
     *         description="营业日期",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="营业时间",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="avg_price",
     *         in="query",
     *         description="人均消费",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="地址信息",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="经度",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="纬度",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="lnglat",
     *         in="query",
     *         description="经纬度",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="province_id",
     *         in="query",
     *         description="省id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="city_id",
     *         in="query",
     *         description="市id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="county_id",
     *         in="query",
     *         description="区id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="province",
     *         in="query",
     *         description="省name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="市name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="county",
     *         in="query",
     *         description="区name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="province_code",
     *         in="query",
     *         description="省code",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="city_code",
     *         in="query",
     *         description="市code",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="county_code",
     *         in="query",
     *         description="区code",
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
     *         name="id",
     *         in="query",
     *         description="id空添加,存在编辑",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop/edit_shop
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop/edit_shop
     *   api:  /wxapp/shop/edit_shop
     *   remark_name: 店铺管理 编辑&添加
     *
     */
    public function edit_shop()
    {
        $this->checkAuth();

        $ShopInit  = new \init\ShopInit();//店铺管理    (ps:InitController)
        $ShopModel = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;
        $params["status"]  = 1;


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where     = [];
        $where[]   = ['user_id', '=', $params['user_id']];
        $shop_info = $ShopModel->where($where)->find();


        $map = [];
        if ($shop_info) $map[] = ['id', '=', $shop_info['id']];

        //提交更新
        $result = $ShopInit->api_edit_post($params, $map);
        if (empty($result)) $this->error("失败请重试");


        $this->success('提交成功,等待审核');
    }


    /**
     * 管理费,缴纳
     * @OA\Post(
     *     tags={"店铺管理"},
     *     path="/wxapp/shop/add_order",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop/add_order
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop/add_order
     *   api:  /wxapp/shop/add_order
     *   remark_name: 管理费,缴纳
     *
     */
    public function add_order()
    {
        $this->checkAuth();

        $ShopManageOrderModel = new \initmodel\ShopManageOrderModel(); //管理费订单管理   (ps:InitModel)
        $ShopManageOrderInit  = new \init\ShopManageOrderInit();//管理费订单管理   (ps:InitController)


        $params = $this->request->param();


        //费用
        $shop_management_expense = cmf_config('shop_management_expense');

        //天数
        $shop_effective_days = cmf_config('shop_effective_days');


        if ($this->user_info['end_time'] > time()) $this->error('您已开通了该服务');


        $params['openid']    = $this->openid;
        $params['user_id']   = $this->user_id;
        $params['shop_id']   = $this->user_info['shop_id'];
        $order_num           = $this->get_only_num('shop_manage_order');
        $params['order_num'] = $order_num;
        $params['amount']    = $shop_management_expense;
        $params['day']       = $shop_effective_days;
        $params['end_time']  = time() + ($shop_effective_days * 86400);


        $result = $ShopManageOrderInit->api_edit_post($params);
        if (empty($result)) $this->error('失败请重试');

        $this->success('请支付', ['order_num' => $order_num, 'order_type' => 20]);


    }

}
