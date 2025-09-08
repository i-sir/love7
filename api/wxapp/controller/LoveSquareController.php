<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveSquare",
 *     "name_underline"          =>"love_square",
 *     "controller_name"         =>"LoveSquare",
 *     "table_name"              =>"love_square",
 *     "remark"                  =>"广场管理"
 *     "api_url"                 =>"/api/wxapp/love_square/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-22 15:29:20",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveSquareController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/love_square/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/love_square/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveSquareController extends AuthController
{


    public function initialize()
    {
        //广场管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_square/index
     * https://xcxkf186.aubye.com/api/wxapp/love_square/index
     */
    public function index()
    {
        $LoveSquareInit  = new \init\LoveSquareInit();//广场管理   (ps:InitController)
        $LoveSquareModel = new \initmodel\LoveSquareModel(); //广场管理   (ps:InitModel)

        $result = [];

        $this->success('广场管理-接口请求成功', $result);
    }


    /**
     * 广场管理 列表
     * @OA\Post(
     *     tags={"广场管理"},
     *     path="/wxapp/love_square/find_love_square_list",
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
     *         name="user_id",
     *         in="query",
     *         description="个人动态",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="is_me",
     *         in="query",
     *         description="true  查自己 个人动态",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_square/find_love_square_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_square/find_love_square_list
     *   api:  /wxapp/love_square/find_love_square_list
     *   remark_name: 广场管理 列表
     *
     */
    public function find_love_square_list()
    {
        $LoveSquareInit  = new \init\LoveSquareInit();//广场管理   (ps:InitController)
        $LoveSquareModel = new \initmodel\LoveSquareModel(); //广场管理   (ps:InitModel)

        //参数
        $params = $this->request->param();
        $params['like_user_id'] = $this->user_id;


        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        if ($params["keyword"]) $where[] = ["title", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];
        if ($params["user_id"]) $where[] = ["user_id", "=", $params["user_id"]];
        if ($params['is_me']) $where[] = ["user_id", "=", $this->user_id];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveSquareInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 广场管理 详情
     * @OA\Post(
     *     tags={"广场管理"},
     *     path="/wxapp/love_square/find_love_square",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_square/find_love_square
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_square/find_love_square
     *   api:  /wxapp/love_square/find_love_square
     *   remark_name: 广场管理 详情
     *
     */
    public function find_love_square()
    {
        $LoveSquareInit  = new \init\LoveSquareInit();//广场管理    (ps:InitController)
        $LoveSquareModel = new \initmodel\LoveSquareModel(); //广场管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveSquareInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 广场管理 编辑&添加
     * @OA\Post(
     *     tags={"广场管理"},
     *     path="/wxapp/love_square/edit_love_square",
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
     *         name="title",
     *         in="query",
     *         description="标题",
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
     *         description="图集",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_square/edit_love_square
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_square/edit_love_square
     *   api:  /wxapp/love_square/edit_love_square
     *   remark_name: 广场管理 编辑&添加
     *
     */
    public function edit_love_square()
    {
        $this->checkAuth();
        $LoveSquareInit  = new \init\LoveSquareInit();//广场管理    (ps:InitController)
        $LoveSquareModel = new \initmodel\LoveSquareModel(); //广场管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params['status']  = 1;//审核
        $params["user_id"] = $this->user_id;


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交更新
        $result = $LoveSquareInit->api_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");


        if (empty($params["id"])) $msg = "添加成功";
        if (!empty($params["id"])) $msg = "编辑成功";
        $this->success($msg);
    }


    /**
     * 广场管理 删除
     * @OA\Post(
     *     tags={"广场管理"},
     *     path="/wxapp/love_square/delete_love_square",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_square/delete_love_square
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_square/delete_love_square
     *   api:  /wxapp/love_square/delete_love_square
     *   remark_name: 广场管理 删除
     *
     */
    public function delete_love_square()
    {
        $LoveSquareInit  = new \init\LoveSquareInit();//广场管理    (ps:InitController)
        $LoveSquareModel = new \initmodel\LoveSquareModel(); //广场管理   (ps:InitModel)

        //参数
        $params = $this->request->param();

        //删除数据
        $result = $LoveSquareInit->delete_post($params["id"]);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功");
    }


}
