<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveLike",
 *     "name_underline"          =>"love_like",
 *     "controller_name"         =>"LoveLike",
 *     "table_name"              =>"love_like",
 *     "remark"                  =>"点赞&收藏"
 *     "api_url"                 =>"/api/wxapp/love_like/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-24 09:38:05",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveLikeController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_like/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_like/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveLikeController extends AuthController
{


    public function initialize()
    {
        //点赞&收藏

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_like/index
     * https://hl212.wxselling.com/api/wxapp/love_like/index
     */
    public function index()
    {
        $LoveLikeInit  = new \init\LoveLikeInit();//点赞&收藏   (ps:InitController)
        $LoveLikeModel = new \initmodel\LoveLikeModel(); //点赞&收藏   (ps:InitModel)

        $result = [];

        $this->success('点赞&收藏-接口请求成功', $result);
    }


    /**
     * 收藏&取消收藏
     * @OA\Post(
     *     tags={"收藏管理"},
     *     path="/wxapp/love_like/edit_like",
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
     *    @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         description="关联id",
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
     *         description="类型:1广场,2广场评论",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_like/edit_like
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_like/edit_like
     *   api:  /wxapp/love_like/edit_like
     *   remark_name: 收藏&取消收藏
     *
     */
    public function edit_like()
    {
        $this->checkAuth();
        $LoveLikeInit = new \init\LoveLikeInit();//点赞&收藏   (ps:InitController)


        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;


        //检测是否已收藏,如果收藏了取消,如果未收藏则添加
        $where   = [];
        $where[] = ['user_id', '=', $this->user_id];
        $where[] = ['pid', '=', $params['pid']];
        $where[] = ['type', '=', $params['type']];
        $is_like = $LoveLikeInit->get_find($where);
        if ($is_like) {
            $update['delete_time'] = time();
            $LoveLikeInit->edit_post($update, $where);
            $this->success("取消点赞");
        } else {
            $LoveLikeInit->edit_post($params);
            $this->success("点赞成功");
        }
    }


    /**
     * 收藏列表 (不用)
     * @OA\Post(
     *     tags={"收藏管理"},
     *     path="/wxapp/love_like/find_like_list",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_like/find_like_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_like/find_like_list
     *   api:  /wxapp/love_like/find_like_list
     *   remark_name: 收藏列表 (不用)
     *
     */
    public function find_like_list()
    {
        $this->checkAuth();

        $LoveLikeInit = new \init\LoveLikeInit();//点赞&收藏   (ps:InitController)


        $where   = [];
        $where[] = ['l.user_id', '=', $this->user_id];


        $params['user_id'] = $this->user_id;//用于是否购买
        $params['order']   = 'l.id desc';
        $params['field']   = 'l.id as l_id,m.*';
        $result            = $LoveLikeInit->get_join_list($where, $params);


        if (empty($result)) $this->error("暂无数据");

        $this->success("收藏记录", $result);
    }


}
