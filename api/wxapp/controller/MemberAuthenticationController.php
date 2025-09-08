<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"MemberAuthentication",
 *     "name_underline"          =>"member_authentication",
 *     "controller_name"         =>"MemberAuthentication",
 *     "table_name"              =>"member_authentication",
 *     "remark"                  =>"认证管理"
 *     "api_url"                 =>"/api/wxapp/member_authentication/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-22 14:55:19",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\MemberAuthenticationController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/member_authentication/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/member_authentication/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class MemberAuthenticationController extends AuthController
{


    public function initialize()
    {
        //认证管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/member_authentication/index
     * https://xcxkf186.aubye.com/api/wxapp/member_authentication/index
     */
    public function index()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)

        $result = [];

        $this->success('认证管理-接口请求成功', $result);
    }


    /**
     * 自己认证列表
     * @OA\Post(
     *     tags={"认证管理"},
     *     path="/wxapp/member_authentication/find_authentication_list",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_authentication/find_authentication_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_authentication/find_authentication_list
     *   api:  /wxapp/member_authentication/find_authentication_list
     *   remark_name: 自己认证列表
     *
     */
    public function find_authentication_list()
    {
        $this->checkAuth();
        $MemberAuthenticationModel    = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $MemberAuthenticationListInit = new \init\MemberAuthenticationListInit();//认证列表   (ps:InitController)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        $result = $MemberAuthenticationListInit->get_list();

        $authentication_list =[];
        foreach ($result as $key => &$value) {
            $value['status'] = 0;//未提交

            $map                   = [];
            $map[]                 = ['user_id', '=', $this->user_id];
            $map[]                 = ['type', '=', $value['type']];
            $member_authentication = $MemberAuthenticationModel->where($map)->find();
            if ($member_authentication) {
                $value['status'] = $member_authentication['status'];
                if ($member_authentication['is_admin'] == 1) $value['url'] = null;
                $authentication_list[]=$value;
            }

            //图片
            $value['true_image'] = cmf_get_asset_url($value['image']);
            if ($member_authentication['status'] == 2) $value['true_image'] = cmf_get_asset_url($value['select_image']);
        }


        $this->success("请求成功!", $authentication_list);
    }


    /**
     * 认证管理 详情
     * @OA\Post(
     *     tags={"认证管理"},
     *     path="/wxapp/member_authentication/find_authentication",
     *
     *
     *
     *    @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id 或类型二选一",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
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
     *    @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="认证类型:1手机认证,2单身认证,3身份认证,4购房认证,5购车认证,6收入认证,7学历认证,8健康认证,9单位认证",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_authentication/find_authentication
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_authentication/find_authentication
     *   api:  /wxapp/member_authentication/find_authentication
     *   remark_name: 认证管理 详情
     *
     */
    public function find_authentication()
    {
        $this->checkAuth();
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理    (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["user_id", "=", $params["user_id"]];
        if ($params['id']) $where[] = ["id", "=", $params["id"]];
        if ($params['type']) $where[] = ["type", "=", $params["type"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $MemberAuthenticationInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 认证管理 添加
     * @OA\Post(
     *     tags={"认证管理"},
     *     path="/wxapp/member_authentication/add_authentication",
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
     *         name="content",
     *         in="query",
     *         description="认证内容  数组格式,自定义",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="认证类型:1手机认证,2单身认证,3身份认证,4购房认证,5购车认证,6收入认证,7学历认证,8健康认证,9单位认证",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_authentication/add_authentication
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_authentication/add_authentication
     *   api:  /wxapp/member_authentication/add_authentication
     *   remark_name: 认证管理  添加
     *
     */
    public function add_authentication()
    {
        $this->checkAuth();
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理    (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        $map                 = [];
        $map[]               = ['user_id', '=', $this->user_id];
        $map[]               = ['type', '=', $params['type']];
        $authentication_info = $MemberAuthenticationModel->where($map)->find();
        if ($authentication_info) $this->error('请勿重复提交');


        //提交更新
        $result = $MemberAuthenticationInit->api_edit_post($params);
        if (empty($result)) $this->error("失败请重试");


        $this->success("提交成功,等待审核");
    }


}
