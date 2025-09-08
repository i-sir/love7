<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"MemberRecommend",
 *     "name_underline"          =>"member_recommend",
 *     "controller_name"         =>"MemberRecommend",
 *     "table_name"              =>"member_recommend",
 *     "remark"                  =>"引荐人"
 *     "api_url"                 =>"/api/wxapp/member_recommend/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-21 14:46:59",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\MemberRecommendController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/member_recommend/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/member_recommend/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class MemberRecommendController extends AuthController
{


    public function initialize()
    {
        //引荐人

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/member_recommend/index
     * https://xcxkf186.aubye.com/api/wxapp/member_recommend/index
     */
    public function index()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)

        $result = [];

        $this->success('引荐人-接口请求成功', $result);
    }


    /**
     * 引荐人 列表
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/find_member_recommend_list",
     *
     *
     *
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/find_member_recommend_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/find_member_recommend_list
     *   api:  /wxapp/member_recommend/find_member_recommend_list
     *   remark_name: 引荐人  列表
     *
     */
    public function find_member_recommend_list()
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人  (ps:InitModel)

        $map   = [];
        $map[] = ['status', '=', 2];
        $map[] = ['is_manage', '=', 2];
        //提交更新
        $list = $MemberRecommendModel->where($map)->select();
        foreach ($list as $key => $value) {
            $result[] = [
                'value' => $value['invite_code'],
                'label' => $value['invite_code'],
            ];
        }


        $this->success('请求成功', $result);
    }


    /**
     * 引荐人 申请
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/add_member_recommend",
     *
     *
     *
     *    @OA\Parameter(
     *         name="nickname",
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
     *
     *    @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="手机号",
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
     *         name="gender",
     *         in="query",
     *         description="性别 文字",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="native",
     *         in="query",
     *         description="籍贯 文字",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="license_images",
     *         in="query",
     *         description="营业执照 数组",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/add_member_recommend
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/add_member_recommend
     *   api:  /wxapp/member_recommend/add_member_recommend
     *   remark_name: 引荐人  申请
     *
     */
    public function add_member_recommend()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人    (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $Qr                   = new \init\QrInit();

        $app_logo = cmf_config('app_logo');

        //参数
        $params           = $this->request->param();
        $params["avatar"] = $app_logo;


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where            = [];
        $where[]          = ['phone', '=', $params['phone']];
        $member_recommend = $MemberRecommendModel->where($where)->find();
        if ($member_recommend) $this->error("该手机号已存在");


        //生成邀请码
        $max_id                = $MemberRecommendModel->max('id') + 1;
        $max_id                = str_pad($max_id, 3, '0', STR_PAD_LEFT);
        $params['invite_code'] = $max_id . $params['nickname'];


        //提交更新
        $result = $MemberRecommendInit->api_edit_post($params);
        if (empty($result)) $this->error("失败请重试");


        $this->success('申请成功等待审核');
    }


    /**
     * 引荐人 登录
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/pass_login",
     *
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/pass_login
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/pass_login
     *   api:  /wxapp/member_recommend/pass_login
     *   remark_name: 引荐人  登录
     *
     */
    public function pass_login()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人    (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)

        $params = $this->request->param();


        $map    = [];
        $map[]  = ['account_name', '=', $params['account_name']];
        $result = $MemberRecommendModel->where($map)->find();
        if (empty($result)) $this->error("账号或密码错误");


        //更新openid
        if ($params['openid']) $MemberRecommendModel->where($map)->update(['openid' => $params['openid']]);

        //检测密码是否正确
        if (!cmf_compare_password($params['pass'], $result['pass'])) $this->error("账号或密码错误");


        //备份记录token
        $map        = [];
        $map[]      = ['user_id', '=', $result['id']];
        $map[]      = ['device_type', '=', 'recommend'];
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
        $token        = cmf_generate_user_token($result['id'], 'recommend');
        $findUserInfo = $this->getUserInfoByToken($token);

        if (empty($findUserInfo)) $this->error('非法操作');


        $this->success('登录成功', $findUserInfo);
    }


    /**
     * 开通管理费
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/add_order",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/add_order
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/add_order
     *   api:  /wxapp/member_recommend/add_order
     *   remark_name: 开通管理费
     *
     */
    public function add_order()
    {
        $this->checkAuth();

        $MemberRecommendOrderInit  = new \init\MemberRecommendOrderInit();//管理费   (ps:InitController)
        $MemberRecommendOrderModel = new \initmodel\MemberRecommendOrderModel(); //管理费   (ps:InitModel)


        $params = $this->request->param();


        //引荐人管理费用
        $referrer_management_expenses = cmf_config('referrer_management_expenses');

        //引荐人管理天数
        $referrer_effective_days = cmf_config('referrer_effective_days');

        if ($this->user_info['admin_time'] > time()) $this->error('您已开通了该服务');

        $params['openid']    = $this->openid;
        $params['user_id']   = $this->user_id;
        $order_num           = $this->get_only_num('member_recommend_order');
        $params['order_num'] = $order_num;
        $params['amount']    = $referrer_management_expenses;
        $params['day']       = $referrer_effective_days;
        $params['end_time']  = time() + ($referrer_effective_days * 86400);

        $result = $MemberRecommendOrderInit->api_edit_post($params);
        if (empty($result)) $this->error('失败请重试');

        $this->success('请支付', ['order_num' => $order_num, 'order_type' => 10]);
    }


    /**
     * 账户(余额)变动明细
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/find_balance_list",
     *
     *
     *     @OA\Parameter(
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
     *     @OA\Parameter(
     *         name="begin_time",
     *         in="query",
     *         description="2023-04-05",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="end_time",
     *         in="query",
     *         description="2023-04-05",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/find_balance_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/find_balance_list
     *   api: /wxapp/member_recommend/find_balance_list
     *   remark_name: 账户(余额)变动明细
     *
     */
    public function find_balance_list()
    {
        $this->checkAuth();

        $params  = $this->request->param();
        $where   = [];
        $where[] = ['user_id', '=', $this->user_id];
        $where[] = $this->getBetweenTime($params['begin_time'], $params['end_time']);

        $result = Db::name("member_recommend_balance")
            ->where($where)
            ->order("id desc")
            ->paginate($params['page_size'])
            ->each(function ($item, $key) {
                if ($item['type'] == 2) {
                    $item['price'] = -$item['price'];
                } else {
                    $item['price'] = '+' . $item['price'];
                }
                return $item;
            });

        $this->success("请求成功！", $result);
    }


    /**
     * 获客海报&分享&推广二维码
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"引荐人"},
     *     path="/wxapp/member_recommend/poster",
     *
     *
     *     @OA\Parameter(
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/member_recommend/poster
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/member_recommend/poster
     *   api: /wxapp/member_recommend/poster
     *   remark_name: 获客海报&分享&推广二维码
     *
     */
    public function poster()
    {
        $this->checkAuth();

        $Qr               = new \init\QrInit();
        $PublicController = new PublicController();
        $MemberModel      = new \initmodel\MemberModel();//用户管理

        //跳转地址
        $qr_code_redirect_address = cmf_config('qr_code_redirect_address');

        //二维码介绍
        $poster_image_introduction = cmf_config('poster_image_introduction');

        //分销+二维码图
        $image = $this->user_info['invite_image'];
        if (empty($image)) {
            $qr_image = $Qr->get_qr($qr_code_redirect_address . $this->user_info['invite_code']);
            $image    = $Qr->applet_share($qr_image, $poster_image_introduction);
        }


        $this->success('请求成功', cmf_get_asset_url($image));
    }
}
