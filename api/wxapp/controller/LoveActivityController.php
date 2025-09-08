<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveActivity",
 *     "name_underline"          =>"love_activity",
 *     "controller_name"         =>"LoveActivity",
 *     "table_name"              =>"love_activity",
 *     "remark"                  =>"活动管理"
 *     "api_url"                 =>"/api/wxapp/love_activity/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-22 10:03:06",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveActivityController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_activity/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_activity/index",
 * )
 */


use initmodel\MemberModel;
use think\db\Raw;
use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveActivityController extends AuthController
{


    public function initialize()
    {
        //活动管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_activity/index
     * https://hl212.wxselling.com/api/wxapp/love_activity/index
     */
    public function index()
    {
        $LoveActivityInit  = new \init\LoveActivityInit();//活动管理   (ps:InitController)
        $LoveActivityModel = new \initmodel\LoveActivityModel(); //活动管理   (ps:InitModel)

        $result = [];

        $this->success('活动管理-接口请求成功', $result);
    }


    /**
     * 活动管理 列表
     * @OA\Post(
     *     tags={"活动管理"},
     *     path="/wxapp/love_activity/find_activity_list",
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
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型:1商家活动,2平台活动",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态:1未开始,2进行中,3已结束",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_activity/find_activity_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_activity/find_activity_list
     *   api:  /wxapp/love_activity/find_activity_list
     *   remark_name: 活动管理 列表
     *
     */
    public function find_activity_list()
    {
        $LoveActivityInit  = new \init\LoveActivityInit();//活动管理   (ps:InitController)
        $LoveActivityModel = new \initmodel\LoveActivityModel(); //活动管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where = [];
        if ($params["keyword"]) $where[] = ["name|address", "like", "%{$params['keyword']}%"];
        if ($params["type"]) $where[] = ["type", "=", $params["type"]];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];
        if (empty($params['status'])) {
            $customOrderStr = "2,1,3";
            // 构建 FIELD() 函数的 SQL 表达式
            $exp             = new Raw("FIELD(status, $customOrderStr)");
            $params['order'] = $exp;
        }


        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveActivityInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 活动管理 详情
     * @OA\Post(
     *     tags={"活动管理"},
     *     path="/wxapp/love_activity/find_activity",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_activity/find_activity
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_activity/find_activity
     *   api:  /wxapp/love_activity/find_activity
     *   remark_name: 活动管理 详情
     *
     */
    public function find_activity()
    {
        $LoveActivityInit  = new \init\LoveActivityInit();//活动管理    (ps:InitController)
        $LoveActivityModel = new \initmodel\LoveActivityModel(); //活动管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $LoveActivityInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 报名
     * @OA\Post(
     *     tags={"报名管理"},
     *     path="/wxapp/love_activity/attend_activity",
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
     *
     *    @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="电话",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="activity_id",
     *         in="query",
     *         description="活动",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/love_activity/attend_activity
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_activity/attend_activity
     *   api:  /wxapp/love_activity/attend_activity
     *   remark_name: 报名
     *
     */
    public function attend_activity()
    {
        $this->checkAuth();
        $LoveActivityLogInit  = new \init\LoveActivityLogInit();//报名管理    (ps:InitController)
        $LoveActivityModel    = new \initmodel\LoveActivityModel(); //活动管理   (ps:InitModel)
        $LoveActivityLogModel = new \initmodel\LoveActivityLogModel(); //报名记录管理  (ps:InitModel)


        //参数
        $params              = $this->request->param();
        $params["user_id"]   = $this->user_id;
        $params["phone"]     = $this->user_info['phone'];
        $params["username"]  = $this->user_info['nickname'];
        $params["order_num"] = $this->get_only_num('love_activity_log');


        //检测活动是否存在
        $activity_info = $LoveActivityModel->where('id', '=', $params['activity_id'])->find();
        if (empty($activity_info)) $this->error('活动信息错误!');


        //检测是否已将参加
        $map         = [];
        $map[]       = ['user_id', '=', $this->user_id];
        $map[]       = ['activity_id', '=', $params['activity_id']];
        $map[]       = ['status', 'in', [1, 3]];
        $is_activity = $LoveActivityLogModel->where($map)->count();
        if ($is_activity) $this->error('请勿重复报名!');


        //提交
        $result = $LoveActivityLogInit->api_edit_post($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success('您的报名申请已提交!');
    }


    /**
     * 已报名列表
     * @OA\Post(
     *     tags={"报名管理"},
     *     path="/wxapp/love_activity/attend_activity_list",
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
     *         name="status",
     *         in="query",
     *         description="状态:1未开始,2进行中,3已结束",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_activity/attend_activity_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_activity/attend_activity_list
     *   api:  /wxapp/love_activity/attend_activity_list
     *   remark_name: 已报名列表
     *
     */
    public function attend_activity_list()
    {
        $this->checkAuth();

        $LoveActivityLogInit = new \init\LoveActivityLogInit();//报名管理    (ps:InitController)
        $params              = $this->request->param();

        $map   = [];
        $map[] = ['l.user_id', '=', $this->user_id];
        if ($params['status']) $map[] = ['l.status', '=', $params['status']];


        //条件
        $params['field'] = 'l.*,a.name,a.image,a.begin_time,a.end_time,a.status as activity_status';
        $params['order'] = 'l.id desc';


        $result = $LoveActivityLogInit->get_join_list($map, $params);
        if (empty($result)) $this->error('暂无数据!');

        $this->success('请求成功!', $result);
    }

}
