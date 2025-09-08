<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveNotice",
 *     "name_underline"          =>"love_notice",
 *     "controller_name"         =>"LoveNotice",
 *     "table_name"              =>"love_notice",
 *     "remark"                  =>"系统通知"
 *     "api_url"                 =>"/api/wxapp/love_notice/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-28 09:53:30",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveNoticeController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/love_notice/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/love_notice/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveNoticeController extends AuthController
{


    public function initialize()
    {
        //系统通知

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_notice/index
     * https://xcxkf186.aubye.com/api/wxapp/love_notice/index
     */
    public function index()
    {
        $LoveNoticeInit  = new \init\LoveNoticeInit();//系统通知   (ps:InitController)
        $LoveNoticeModel = new \initmodel\LoveNoticeModel(); //系统通知   (ps:InitModel)

        $result = [];

        $this->success('系统通知-接口请求成功', $result);
    }


    /**
     * 系统通知 列表
     * @OA\Post(
     *     tags={"系统通知"},
     *     path="/wxapp/love_notice/find_love_notice_list",
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_notice/find_love_notice_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_notice/find_love_notice_list
     *   api:  /wxapp/love_notice/find_love_notice_list
     *   remark_name: 系统通知 列表
     *
     */
    public function find_love_notice_list()
    {
        $this->checkAuth();
        $LoveNoticeInit  = new \init\LoveNoticeInit();//系统通知   (ps:InitController)
        $LoveNoticeModel = new \initmodel\LoveNoticeModel(); //系统通知   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params['user_id'] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        $where[] = ['', 'EXP', Db::raw("FIND_IN_SET({$params['user_id']},send_user_id)")];


        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveNoticeInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 系统通知 详情
     * @OA\Post(
     *     tags={"系统通知"},
     *     path="/wxapp/love_notice/find_love_notice",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_notice/find_love_notice
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_notice/find_love_notice
     *   api:  /wxapp/love_notice/find_love_notice
     *   remark_name: 系统通知 详情
     *
     */
    public function find_love_notice()
    {
        $LoveNoticeInit  = new \init\LoveNoticeInit();//系统通知    (ps:InitController)
        $LoveNoticeModel = new \initmodel\LoveNoticeModel(); //系统通知   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveNoticeInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 审核
     * @OA\Post(
     *     tags={"系统通知"},
     *     path="/wxapp/love_notice/examine",
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
     *    @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="2通过,3拒绝",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_notice/examine
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_notice/examine
     *   api:  /wxapp/love_notice/examine
     *   remark_name: 审核
     *
     */
    public function examine()
    {
        $this->checkAuth();

        $LoveNoticeInit  = new \init\LoveNoticeInit();//系统通知    (ps:InitController)
        $LoveNoticeModel = new \initmodel\LoveNoticeModel(); //系统通知   (ps:InitModel)

        //参数
        $params = $this->request->param();


        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];


        if ($params['status'] == 2) $params['pass_time'] = time();
        if ($params['status'] == 3) $params['refuse_time'] = time();


        $notice_ifno = $LoveNoticeInit->get_find($where);
        if (empty($notice_ifno)) $this->error("暂无数据");
        if ($notice_ifno["status"] != 1 || $notice_ifno['to_user_id'] != $this->user_id) $this->error("非法操作");


        $result = $LoveNoticeInit->api_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("操作成功");
    }

}
