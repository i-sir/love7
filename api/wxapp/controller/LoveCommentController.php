<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveComment",
 *     "name_underline"          =>"love_comment",
 *     "controller_name"         =>"LoveComment",
 *     "table_name"              =>"love_comment",
 *     "remark"                  =>"评论管理"
 *     "api_url"                 =>"/api/wxapp/love_comment/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-24 09:38:18",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveCommentController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_comment/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_comment/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveCommentController extends AuthController
{


    public function initialize()
    {
        //评论管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_comment/index
     * https://hl212.wxselling.com/api/wxapp/love_comment/index
     */
    public function index()
    {
        $LoveCommentInit  = new \init\LoveCommentInit();//评论管理   (ps:InitController)
        $LoveCommentModel = new \initmodel\LoveCommentModel(); //评论管理   (ps:InitModel)

        $result = [];

        $this->success('评论管理-接口请求成功', $result);
    }


    /**
     * 评论列表
     * @OA\Post(
     *     tags={"论坛管理"},
     *     path="/wxapp/love_comment/find_comment_list",
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
     *         name="pid",
     *         in="query",
     *         description="文章id",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_comment/find_comment_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_comment/find_comment_list
     *   api:  /wxapp/love_comment/add_comment
     *   remark_name: 评论列表
     *
     *
     */
    public function find_comment_list()
    {
        $LoveCommentModel = new \initmodel\LoveCommentModel(); //评论管理   (ps:InitModel)
        $MemberModel      = new \initmodel\MemberModel(); //用户管理   (ps:InitModel)
        $LoveLikeModel    = new \initmodel\LoveLikeModel(); //点赞&amp;收藏  (ps:InitModel)

        $params            = $this->request->param();
        $params['user_id'] = $this->user_id;

        $pid = $params['pid'];//文章id

        $map   = [];
        $map[] = ['c.pid', '=', $params['pid']];
        $map[] = ['c.parent_id', '=', 0];


        $result = $LoveCommentModel
            ->alias('c')
            ->join('member m', 'c.user_id=m.id')
            ->where($map)
            ->field('c.*,m.nickname,m.avatar')
            ->order('id desc')
            ->paginate(10)
            ->each(function ($item) use ($pid, $params, $LoveCommentModel, $MemberModel, $LoveLikeModel) {
                //是否点赞
                if ($params['user_id']) {
                    $map2            = [];
                    $map2[]          = ['user_id', '=', $params['user_id']];
                    $map2[]          = ['pid', '=', $item['id']];
                    $map2[]          = ['type', '=', 2];//类型:1广场,2广场评论
                    $is_like         = $LoveLikeModel->where($map2)->find();
                    $item['is_like'] = $is_like ? true : false;
                }
                //点赞数量
                $map5               = [];
                $map5[]             = ['pid', '=', $item['id']];
                $map5[]             = ['type', '=', 2];//类型:1广场,2广场评论
                $item['like_count'] = $LoveLikeModel->where($map5)->count();


                //一级评论
                $item['avatar'] = cmf_get_asset_url($item['avatar']);
                if ($item['image']) $item['image'] = cmf_get_asset_url($item['image']);
                //一级评论上级人,如作者,一般不用
                //                if ($item['answer_user_id']) {
                //                    $member                 = $MemberModel->where('id', '=', $item['answer_user_id'])->find();
                //                    $item['reply_username'] = $member['nickname'];
                //                    $item['reply_avatar']   = cmf_get_asset_url($member['avatar']);
                //                }


                //二级,多级评论
                $map                = [];
                $map[]              = ['c.pid', '=', $pid];//文章id
                $map[]              = ['c.parent_id', '=', $item['id']];//上级id
                $child_list         = $LoveCommentModel
                    ->alias('c')
                    ->join('member m', 'c.user_id=m.id')
                    ->field('c.*,m.nickname,m.avatar')
                    ->where($map)
                    ->select()
                    ->each(function ($citem) use ($params, $LoveLikeModel) {
                        //自己的头像
                        $citem['avatar'] = cmf_get_asset_url($citem['avatar']);

                        //回复的上级人头像,昵称
                        if ($citem['answer_user_id']) {
                            $cmember                 = Db::name('member')->where('id', '=', $citem['answer_user_id'])->find();
                            $citem['reply_username'] = $cmember['nickname'];
                            $citem['reply_avatar']   = cmf_get_asset_url($cmember['avatar']);
                        }

                        //是否点赞
                        if ($params['user_id']) {
                            $map3             = [];
                            $map3[]           = ['user_id', '=', $params['user_id']];
                            $map3[]           = ['pid', '=', $citem['id']];
                            $map3[]           = ['type', '=', 2];//类型:1广场,2广场评论
                            $is_like          = $LoveLikeModel->where($map3)->find();
                            $citem['is_like'] = $is_like ? true : false;
                        }
                        //点赞数量
                        $map4                = [];
                        $map4[]              = ['pid', '=', $citem['id']];
                        $map4[]              = ['type', '=', 2];//类型:1广场,2广场评论
                        $citem['like_count'] = $LoveLikeModel->where($map4)->count();

                        return $citem;
                    });
                $item['child_list'] = $child_list;


                return $item;
            });


        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 添加评论
     * @OA\Post(
     *     tags={"论坛管理"},
     *     path="/wxapp/love_comment/add_comment",
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
     *         name="pid",
     *         in="query",
     *         description="文章id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="上级id 一级传0",
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
     *         name="content",
     *         in="query",
     *         description="评论内容",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/love_comment/add_comment
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_comment/add_comment
     *   api:  /wxapp/love_comment/add_comment
     *   remark_name: 添加评论
     *
     */
    public function add_comment()
    {
        $this->checkAuth();

        $LoveCommentInit  = new \init\LoveCommentInit();//评论管理   (ps:InitController)
        $LoveCommentModel = new \initmodel\LoveCommentModel(); //评论管理   (ps:InitModel)


        $params            = $this->request->param();
        $params['user_id'] = $this->user_id;


        //如果三级,或四级,修改为评论一级的评论
        if ($params['parent_id']) {
            $comment = $LoveCommentModel->where('id', '=', $params['parent_id'])->find();
            if ($comment['parent_id'] != 0) $params['parent_id'] = $comment['parent_id'];
            $params['answer_user_id'] = $comment['user_id'];//评论的上级用户id
            $params['send_user_id']   = $comment['user_id'];//通知人
        }


        $result = $LoveCommentInit->api_edit_post($params);
        if (empty($result)) $this->error('失败请重试');

        $comment_info               = $LoveCommentModel->where('id', '=', $result)->find();
        $comment_info['avatar']     = cmf_get_asset_url($this->user_info['avatar']);
        $comment_info['nickname']   = $this->user_info['nickname'];
        $comment_info['child_list'] = [];


        $this->success('评论成功', $comment_info);
    }


    /**
     * 删除评论
     * @OA\Post(
     *     tags={"论坛管理"},
     *     path="/wxapp/love_comment/delete",
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
     *
     *
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/love_comment/delete
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_comment/delete
     *   api:  /wxapp/love_comment/delete
     *   remark_name: 删除评论
     *
     */
    public function delete()
    {
        $this->checkAuth();

        $LoveCommentInit  = new \init\LoveCommentInit();//评论管理   (ps:InitController)
        $LoveCommentModel = new \initmodel\LoveCommentModel(); //评论管理   (ps:InitModel)


        $params = $this->request->param();

        $result = $LoveCommentInit->delete_post($params['id']);
        if (empty($result)) $this->error('失败请重试');
        $this->success('删除成功');
    }

}
