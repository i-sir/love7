<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveEmotion",
 *     "name_underline"          =>"love_emotion",
 *     "controller_name"         =>"LoveEmotion",
 *     "table_name"              =>"love_emotion",
 *     "remark"                  =>"情感测试"
 *     "api_url"                 =>"/api/wxapp/love_emotion/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-21 18:15:46",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveEmotionController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_emotion/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_emotion/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveEmotionController extends AuthController
{


    public function initialize()
    {
        //情感测试

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_emotion/index
     * https://hl212.wxselling.com/api/wxapp/love_emotion/index
     */
    public function index()
    {
        $LoveEmotionInit  = new \init\LoveEmotionInit();//情感测试   (ps:InitController)
        $LoveEmotionModel = new \initmodel\LoveEmotionModel(); //情感测试   (ps:InitModel)

        $result = [];

        $this->success('情感测试-接口请求成功', $result);
    }


    /**
     * 情感测试 列表
     * @OA\Post(
     *     tags={"情感测试"},
     *     path="/wxapp/love_emotion/find_love_emotion_list",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_emotion/find_love_emotion_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_emotion/find_love_emotion_list
     *   api:  /wxapp/love_emotion/find_love_emotion_list
     *   remark_name: 情感测试 列表
     *
     */
    public function find_love_emotion_list()
    {
        $LoveEmotionInit  = new \init\LoveEmotionInit();//情感测试   (ps:InitController)
        $LoveEmotionModel = new \initmodel\LoveEmotionModel(); //情感测试   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        if ($params["keyword"]) $where[] = ["title", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveEmotionInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 情感测试 详情
     * @OA\Post(
     *     tags={"情感测试"},
     *     path="/wxapp/love_emotion/find_love_emotion",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_emotion/find_love_emotion
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_emotion/find_love_emotion
     *   api:  /wxapp/love_emotion/find_love_emotion
     *   remark_name: 情感测试 详情
     *
     */
    public function find_love_emotion()
    {
        $LoveEmotionInit  = new \init\LoveEmotionInit();//情感测试    (ps:InitController)
        $LoveEmotionModel = new \initmodel\LoveEmotionModel(); //情感测试   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveEmotionInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


}
