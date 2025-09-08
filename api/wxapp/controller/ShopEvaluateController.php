<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"ShopEvaluate",
 *     "name_underline"          =>"shop_evaluate",
 *     "controller_name"         =>"ShopEvaluate",
 *     "table_name"              =>"shop_evaluate",
 *     "remark"                  =>"店铺评价"
 *     "api_url"                 =>"/api/wxapp/shop_evaluate/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-26 10:56:55",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\ShopEvaluateController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/shop_evaluate/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/shop_evaluate/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class ShopEvaluateController extends AuthController
{


    public function initialize()
    {
        //店铺评价

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/shop_evaluate/index
     * https://hl212.wxselling.com/api/wxapp/shop_evaluate/index
     */
    public function index()
    {
        $ShopEvaluateInit  = new \init\ShopEvaluateInit();//店铺评价   (ps:InitController)
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        $result = [];

        $this->success('店铺评价-接口请求成功', $result);
    }


    /**
     * 店铺评价 列表
     * @OA\Post(
     *     tags={"店铺评价"},
     *     path="/wxapp/shop_evaluate/find_shop_evaluate_list",
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
     *         name="shop_id",
     *         in="query",
     *         description="shop_id",
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
     *   test_environment: http://love0212.ikun/api/wxapp/shop_evaluate/find_shop_evaluate_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/shop_evaluate/find_shop_evaluate_list
     *   api:  /wxapp/shop_evaluate/find_shop_evaluate_list
     *   remark_name: 店铺评价 列表
     *
     */
    public function find_shop_evaluate_list()
    {
        $ShopEvaluateInit  = new \init\ShopEvaluateInit();//店铺评价   (ps:InitController)
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        if ($params["keyword"]) $where[] = ["user_id|shop_id|star|evaluate", "like", "%{$params['keyword']}%"];
        if ($params["shop_id"]) $where[] = ["shop_id", "=", $params["shop_id"]];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $ShopEvaluateInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 店铺评价 详情
     * @OA\Post(
     *     tags={"店铺评价"},
     *     path="/wxapp/shop_evaluate/find_shop_evaluate",
     *
     *
     *
     *    @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
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
     *   test_environment: http://love0212.ikun/api/wxapp/shop_evaluate/find_shop_evaluate
     *   official_environment: https://hl212.wxselling.com/api/wxapp/shop_evaluate/find_shop_evaluate
     *   api:  /wxapp/shop_evaluate/find_shop_evaluate
     *   remark_name: 店铺评价 详情
     *
     */
    public function find_shop_evaluate()
    {
        $ShopEvaluateInit  = new \init\ShopEvaluateInit();//店铺评价    (ps:InitController)
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $ShopEvaluateInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 店铺评价 平均分
     * @OA\Post(
     *     tags={"店铺评价"},
     *     path="/wxapp/shop_evaluate/find_avg",
     *
     *
     *
     *    @OA\Parameter(
     *         name="shop_id",
     *         in="query",
     *         description="shop_id",
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
     *   test_environment: http://love0212.ikun/api/wxapp/shop_evaluate/find_avg
     *   official_environment: https://hl212.wxselling.com/api/wxapp/shop_evaluate/find_avg
     *   api:  /wxapp/shop_evaluate/find_avg
     *   remark_name: 店铺评价 平均分
     *
     */
    public function find_avg()
    {
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        //参数
        $params = $this->request->param();


        //查询条件
        $where   = [];
        $where[] = ["shop_id", "=", $params["shop_id"]];


        $result = $ShopEvaluateModel->where($where)->avg('star');
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", round($result, 2));
    }


    /**
     * 店铺评价  添加
     * @OA\Post(
     *     tags={"店铺评价"},
     *     path="/wxapp/shop_evaluate/add_shop_evaluate",
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
     *         name="shop_id",
     *         in="query",
     *         description="shop_id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="star",
     *         in="query",
     *         description="星级",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="evaluate",
     *         in="query",
     *         description="评价内容",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="图集  数组",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/shop_evaluate/add_shop_evaluate
     *   official_environment: https://hl212.wxselling.com/api/wxapp/shop_evaluate/add_shop_evaluate
     *   api:  /wxapp/shop_evaluate/add_shop_evaluate
     *   remark_name: 店铺评价  添加
     *
     */
    public function add_shop_evaluate()
    {
        $ShopEvaluateInit  = new \init\ShopEvaluateInit();//店铺评价    (ps:InitController)
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交更新
        $result = $ShopEvaluateInit->api_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");


        $this->success('评价成功');
    }


}
