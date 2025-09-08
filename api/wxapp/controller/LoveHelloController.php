<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveHello",
 *     "name_underline"          =>"love_hello",
 *     "controller_name"         =>"LoveHello",
 *     "table_name"              =>"love_hello",
 *     "remark"                  =>"打招呼"
 *     "api_url"                 =>"/api/wxapp/love_hello/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-27 16:24:10",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveHelloController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_hello/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_hello/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveHelloController extends AuthController
{


    public function initialize()
    {
        //打招呼

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_hello/index
     * https://hl212.wxselling.com/api/wxapp/love_hello/index
     */
    public function index()
    {
        $LoveHelloInit  = new \init\LoveHelloInit();//打招呼   (ps:InitController)
        $LoveHelloModel = new \initmodel\LoveHelloModel(); //打招呼   (ps:InitModel)

        $result = [];

        $this->success('打招呼-接口请求成功', $result);
    }


    /**
     * 打招呼 列表
     * @OA\Post(
     *     tags={"打招呼"},
     *     path="/wxapp/love_hello/find_love_hello_list",
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
     *     @OA\Parameter(
     *         name="is_put",
     *         in="query",
     *         description="我收到的",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="is_send",
     *         in="query",
     *         description="我发起的",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_hello/find_love_hello_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_hello/find_love_hello_list
     *   api:  /wxapp/love_hello/find_love_hello_list
     *   remark_name: 打招呼 列表
     *
     */
    public function find_love_hello_list()
    {
        $this->checkAuth();

        $LoveHelloInit  = new \init\LoveHelloInit();//打招呼   (ps:InitController)
        $LoveHelloModel = new \initmodel\LoveHelloModel(); //打招呼   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;
        $params["is_put"]  = $params["is_put"] ?? true;

        //查询条件
        $where = [];
        if ($params["is_put"]) $where[] = ['to_user_id', '=', $this->user_id];
        if ($params["is_send"]) $where[] = ['user_id', '=', $this->user_id];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveHelloInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 打招呼 详情
     * @OA\Post(
     *     tags={"打招呼"},
     *     path="/wxapp/love_hello/find_love_hello",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_hello/find_love_hello
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_hello/find_love_hello
     *   api:  /wxapp/love_hello/find_love_hello
     *   remark_name: 打招呼 详情
     *
     */
    public function find_love_hello()
    {
        $LoveHelloInit  = new \init\LoveHelloInit();//打招呼    (ps:InitController)
        $LoveHelloModel = new \initmodel\LoveHelloModel(); //打招呼   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveHelloInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 打招呼  添加
     * @OA\Post(
     *     tags={"打招呼"},
     *     path="/wxapp/love_hello/add_love_hello",
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
     *         name="to_user_id",
     *         in="query",
     *         description="接收人",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="招呼内容",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_hello/add_love_hello
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_hello/add_love_hello
     *   api:  /wxapp/love_hello/add_love_hello
     *   remark_name: 打招呼  添加
     *
     */
    public function add_love_hello()
    {
        $this->checkAuth();

        $LoveHelloInit  = new \init\LoveHelloInit();//打招呼    (ps:InitController)
        $LoveHelloModel = new \initmodel\LoveHelloModel(); //打招呼   (ps:InitModel)

        //每日打招呼次数
        $daily_greetings_frequency = cmf_config('daily_greetings_frequency');


        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        $map        = [];
        $map[]      = ['user_id', '=', $this->user_id];
        $map[]      = ['to_user_id', '=', $params['to_user_id']];
        $map[]      = ['create_time', 'between', [strtotime('today'), strtotime('tomorrow') - 1]];
        $hello_info = $LoveHelloModel->where($map)->count();
        if ($hello_info >= $daily_greetings_frequency) $this->error("与此用户今日打招呼已超过{$daily_greetings_frequency}次，无法打招呼");

        //提交更新
        $result = $LoveHelloInit->api_edit_post($params);
        if (empty($result)) $this->error("失败请重试");


        $this->success('发送成功');
    }


    /**
     * 打招呼 删除
     * @OA\Post(
     *     tags={"打招呼"},
     *     path="/wxapp/love_hello/delete_love_hello",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_hello/delete_love_hello
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_hello/delete_love_hello
     *   api:  /wxapp/love_hello/delete_love_hello
     *   remark_name: 打招呼 删除
     *
     */
    public function delete_love_hello()
    {
        $LoveHelloInit  = new \init\LoveHelloInit();//打招呼    (ps:InitController)
        $LoveHelloModel = new \initmodel\LoveHelloModel(); //打招呼   (ps:InitModel)

        //参数
        $params = $this->request->param();

        //删除数据
        $result = $LoveHelloInit->delete_post($params["id"]);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功");
    }


}
