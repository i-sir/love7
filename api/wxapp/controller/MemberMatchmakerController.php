<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"MemberMatchmaker",
 *     "name_underline"          =>"member_matchmaker",
 *     "controller_name"         =>"MemberMatchmaker",
 *     "table_name"              =>"member_matchmaker",
 *     "remark"                  =>"红娘管理"
 *     "api_url"                 =>"/api/wxapp/member_matchmaker/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-27 10:52:52",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\MemberMatchmakerController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/member_matchmaker/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/member_matchmaker/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class MemberMatchmakerController extends AuthController
{


    public function initialize()
    {
        //红娘管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/member_matchmaker/index
     * https://hl212.wxselling.com/api/wxapp/member_matchmaker/index
     */
    public function index()
    {
        $MemberMatchmakerInit  = new \init\MemberMatchmakerInit();//红娘管理   (ps:InitController)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)

        $result = [];

        $this->success('红娘管理-接口请求成功', $result);
    }


    /**
     * 红娘管理 列表
     * @OA\Post(
     *     tags={"红娘管理"},
     *     path="/wxapp/member_matchmaker/find_member_matchmaker_list",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member_matchmaker/find_member_matchmaker_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_matchmaker/find_member_matchmaker_list
     *   api:  /wxapp/member_matchmaker/find_member_matchmaker_list
     *   remark_name: 红娘管理 列表
     *
     */
    public function find_member_matchmaker_list()
    {
        $this->checkAuth();
        $MemberMatchmakerInit  = new \init\MemberMatchmakerInit();//红娘管理   (ps:InitController)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        //普通用户白银会员才可以使用该功能
        if ($this->user_info['identity_type'] == 'member' && $this->user_info['vip_id'] < 3) {
            $this->error(['mas' => '身份不支持', 'code' => 200]);
        }


        //查询条件
        $where   = [];
        $where[] = ['id', '>', 0];
        $where[] = ['is_manage', '=', 2];
        if ($params["keyword"]) $where[] = ["nickname|phone", "like", "%{$params['keyword']}%"];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $MemberMatchmakerInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 红娘管理 详情
     * @OA\Post(
     *     tags={"红娘管理"},
     *     path="/wxapp/member_matchmaker/find_member_matchmaker",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member_matchmaker/find_member_matchmaker
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_matchmaker/find_member_matchmaker
     *   api:  /wxapp/member_matchmaker/find_member_matchmaker
     *   remark_name: 红娘管理 详情
     *
     */
    public function find_member_matchmaker()
    {
        $MemberMatchmakerInit  = new \init\MemberMatchmakerInit();//红娘管理    (ps:InitController)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $MemberMatchmakerInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 找她牵线 (绑定)
     * @OA\Post(
     *     tags={"红娘管理"},
     *     path="/wxapp/member_matchmaker/binding",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member_matchmaker/binding
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_matchmaker/binding
     *   api:  /wxapp/member_matchmaker/binding
     *   remark_name: 找她牵线 (绑定)
     *
     */
    public function binding()
    {
        $this->checkAuth();
        $MemberMatchmakerInit  = new \init\MemberMatchmakerInit();//红娘管理    (ps:InitController)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)
        $MemberModel           = new \initmodel\MemberModel();//用户管理

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        $result = $MemberModel->where('id', '=', $this->user_id)->update([
            'matchmaker_id' => $params['id'],
            'update_time'   => time(),
        ]);
        if (empty($result)) $this->error("暂无数据");

        $this->success("绑定成功");
    }



    /**
     * 红娘登录
     * @OA\Post(
     *     tags={"红娘管理"},
     *     path="/wxapp/member_matchmaker/pass_login",
     *
     *
     *
     *    @OA\Parameter(
     *         name="account_name",
     *         in="query",
     *         description="账号",
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
     *         name="pass",
     *         in="query",
     *         description="密码",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member_matchmaker/pass_login
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member_matchmaker/pass_login
     *   api:  /wxapp/member_matchmaker/pass_login
     *   remark_name: 红娘登录
     *
     */
    public function pass_login()
    {
        $MemberMatchmakerInit  = new \init\MemberMatchmakerInit();//红娘管理    (ps:InitController)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)


        $params = $this->request->param();


        $map    = [];
        $map[]  = ['account_name', '=', $params['account_name']];
        $result = $MemberMatchmakerModel->where($map)->find();
        if (empty($result)) $this->error("账号或密码错误");


        //更新openid
        if ($params['openid']) $MemberMatchmakerModel->where($map)->update(['openid' => $params['openid']]);

        //检测密码是否正确
        if (!cmf_compare_password($params['pass'], $result['pass'])) $this->error("账号或密码错误");


        //备份记录token
        $map        = [];
        $map[]      = ['user_id', '=', $result['id']];
        $map[]      = ['device_type', '=', 'matchmaker'];
        $token_info = Db::name("user_token")->where($map)->find();
        unset($token_info['id']);
        $token_info['login_time'] = time();
        $token_info['ip']         = get_client_ip();
        $token_info['login_city'] = $this->get_ip_to_city();
        Db::name("user_token_log")->strict(false)->insert($token_info);


        //删除之前token
        Db::name('user_token')->where($map)->update([
            'expire_time' => time()//让token过期
        ]);

        //登录成功生成token
        $token        = cmf_generate_user_token($result['id'], 'matchmaker');
        $findUserInfo = $this->getUserInfoByToken($token);

        if (empty($findUserInfo)) $this->error('非法操作');


        $this->success('登录成功', $findUserInfo);
    }



}
