<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveMate",
 *     "name_underline"          =>"love_mate",
 *     "controller_name"         =>"LoveMate",
 *     "table_name"              =>"love_mate",
 *     "remark"                  =>"牵线管理"
 *     "api_url"                 =>"/api/wxapp/love_mate/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-27 18:37:31",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveMateController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/love_mate/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/love_mate/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveMateController extends AuthController
{


    public function initialize()
    {
        //牵线管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_mate/index
     * https://xcxkf186.aubye.com/api/wxapp/love_mate/index
     */
    public function index()
    {
        $LoveMateInit  = new \init\LoveMateInit();//牵线管理   (ps:InitController)
        $LoveMateModel = new \initmodel\LoveMateModel(); //牵线管理   (ps:InitModel)

        $result = [];

        $this->success('牵线管理-接口请求成功', $result);
    }


    /**
     * 牵线管理  添加
     * @OA\Post(
     *     tags={"牵线管理"},
     *     path="/wxapp/love_mate/add_love_mate",
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
     *
     *
     *    @OA\Parameter(
     *         name="is_new",
     *         in="query",
     *         description="true 换红娘",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/love_mate/add_love_mate
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/love_mate/add_love_mate
     *   api:  /wxapp/love_mate/add_love_mate
     *   remark_name: 牵线管理  添加
     *
     */
    public function add_love_mate()
    {
        $this->checkAuth();

        $LoveMateInit          = new \init\LoveMateInit();//牵线管理    (ps:InitController)
        $LoveMateModel         = new \initmodel\LoveMateModel(); //牵线管理   (ps:InitModel)
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)


        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        if ($params['to_user_id'] == $params['user_id']) $this->error('参数错误');

        //关联红娘
        $matchmaker_id   = $this->user_info['matchmaker_id'];
        $matchmaker_info = $MemberMatchmakerModel->where('id', '=', $matchmaker_id)->find();

        //默认之前红娘,如果下线,不存在  随机分配一个
        if (empty($params['is_new'])) {
            if (empty($matchmaker_info)) $this->error(['msg' => "红娘已下线,重新匹配红娘", 'code' => 1000]);
            if ($matchmaker_info['status'] == 2) $this->error(['msg' => "红娘已下线,重新匹配红娘", 'code' => 1000]);
        } else {
            if (empty($matchmaker_info) || $matchmaker_info['status'] == 2) {
                //随机一个红娘
                $matchmaker_id = $MemberMatchmakerModel->where('status', '=', 1)->orderRaw('RAND()')->value('id');
            }
        }

        //红娘
        $params['matchmaker_id'] = $matchmaker_id;

        //是否重复提交
        $map       = [];
        $map[]     = ['user_id', '=', $this->user_id];
        $map[]     = ['to_user_id', '=', $params['to_user_id']];
        $mate_info = $LoveMateModel->where($map)->find();
        if (!empty($mate_info)) $this->error("您已提交过");

        $map2       = [];
        $map2[]     = ['to_user_id', '=', $this->user_id];
        $map2[]     = ['user_id', '=', $params['to_user_id']];
        $mate_info2 = $LoveMateModel->where($map)->find();
        if (!empty($mate_info2)) $this->error("对方已提交过");


        //提交更新
        $result = $LoveMateInit->api_edit_post($params);
        if (empty($result)) $this->error("失败请重试");


        $this->success('操作成功');
    }


}
