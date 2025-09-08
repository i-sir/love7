<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"LoveSign",
 *     "name_underline"          =>"love_sign",
 *     "controller_name"         =>"LoveSign",
 *     "table_name"              =>"love_sign",
 *     "remark"                  =>"签到"
 *     "api_url"                 =>"/api/wxapp/love_sign/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-22 10:55:41",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\LoveSignController();
 *     "test_environment"        =>"http://love0212.ikun/api/wxapp/love_sign/index",
 *     "official_environment"    =>"https://hl212.wxselling.com/api/wxapp/love_sign/index",
 * )
 */


use initmodel\MemberModel;
use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class LoveSignController extends AuthController
{


    public function initialize()
    {
        //签到

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/love_sign/index
     * https://hl212.wxselling.com/api/wxapp/love_sign/index
     */
    public function index()
    {
        $LoveSignInit  = new \init\LoveSignInit();//签到   (ps:InitController)
        $LoveSignModel = new \initmodel\LoveSignModel(); //签到   (ps:InitModel)

        $result = [];

        $this->success('签到-接口请求成功', $result);
    }


    /**
     * 日期列表
     * @OA\Post(
     *     tags={"签到"},
     *     path="/wxapp/love_sign/find_date_list",
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
     *         name="yea_month",
     *         in="query",
     *         description="年月 2024-08   默认当月",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_sign/find_date_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_sign/find_date_list
     *   api:  /wxapp/love_sign/find_date_list
     *   remark_name: 日期列表
     *
     */
    public function find_date_list()
    {
        $this->checkAuth();
        $LoveSignInit  = new \init\LoveSignInit();//签到   (ps:InitController)
        $LoveSignModel = new \initmodel\LoveSignModel(); //签到   (ps:InitModel)

        $params = $this->request->param();

        if (empty($params['yea_month'])) $params['yea_month'] = date('Y-m');
        // 确定该月的第一天
        $start_date = "{$params['yea_month']}-01";
        // 确定该月的天数
        $days_in_month = date('t', strtotime($start_date));

        // 定义英文星期与中文星期的映射
        $weekdays_map = [
            'Monday'    => '星期一',
            'Tuesday'   => '星期二',
            'Wednesday' => '星期三',
            'Thursday'  => '星期四',
            'Friday'    => '星期五',
            'Saturday'  => '星期六',
            'Sunday'    => '星期日',
        ];

        $result = [];
        for ($day = 1; $day <= $days_in_month; $day++) {
            $current_date = "{$params['yea_month']}-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $weekday_en   = date('l', strtotime($current_date)); // 获取周几的英文名称
            $weekday_cn   = $weekdays_map[$weekday_en]; // 将英文名称映射为中文名称


            $is_sign = false;
            $map     = [];
            $map[]   = ['user_id', '=', $this->user_id];
            $map[]   = ['sign_date', '=', $current_date];
            $sign    = $LoveSignModel->where($map)->find();
            if ($sign) $is_sign = true;


            $result[] = [
                'date'    => $current_date,
                'day'     => $day,
                'weekday' => $weekday_cn,
                'is_sing' => $is_sign,
            ];
        }

        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 已签到日期列表
     * @OA\Post(
     *     tags={"签到"},
     *     path="/wxapp/love_sign/find_already_date_list",
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
     *         name="yea_month",
     *         in="query",
     *         description="年月 2024-08   默认当月",
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
     *   test_environment: http://love0212.ikun/api/wxapp/love_sign/find_already_date_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_sign/find_already_date_list
     *   api:  /wxapp/love_sign/find_already_date_list
     *   remark_name: 已签到日期列表
     *
     */
    public function find_already_date_list()
    {
        $this->checkAuth();

        $LoveSignModel = new \initmodel\LoveSignModel(); //签到   (ps:InitModel)

        $map   = [];
        $map[] = ['user_id', '=', $this->user_id];
        $list  = $LoveSignModel->where($map)->select();
        if (empty($list)) $this->error("暂无信息!");
        $this->success("请求成功!", $list->column('sign_date'));
    }


    /**
     * 签到
     * @OA\Post(
     *     tags={"签到"},
     *     path="/wxapp/love_sign/add_sign",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/love_sign/add_sign
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_sign/add_sign
     *   api:  /wxapp/love_sign/add_sign
     *   remark_name: 签到
     *
     */
    public function add_sign()
    {
        $this->checkAuth();
        $LoveSignInit  = new \init\LoveSignInit();//签到    (ps:InitController)
        $LoveSignModel = new \initmodel\LoveSignModel(); //签到   (ps:InitModel)

        //签到奖励
        $sign_balance = cmf_config('sign_balance');

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;
        $today             = date('Y-m-d'); // 当前日期
        $order_num         = $this->get_only_num('love_sign');


        // 检查今天是否已经签到
        $existingRecord = $LoveSignModel->where(['user_id' => $this->user_id, 'sign_date' => $today])->find();
        if ($existingRecord) $this->error('今天已经签到过了！');


        // 获取最后一次签到记录
        $lastSignIn = $LoveSignModel->where(['user_id' => $this->user_id])->order('sign_date DESC')->find();

        if ($lastSignIn) {
            $lastDate = $lastSignIn['sign_date'];
            $daysDiff = (strtotime($today) - strtotime($lastDate)) / (60 * 60 * 24);

            if ($daysDiff == 1) {
                // 连续签到
                $consecutiveDays = $lastSignIn['consecutive_days'] + 1;
            } else {
                // 中断，重新开始
                $consecutiveDays = 1;
            }
        } else {
            // 第一次签到
            $consecutiveDays = 1;
        }

        // 使用模型插入新的签到记录
        $sign_id = $LoveSignModel->strict(false)->insert([
            'user_id'          => $this->user_id,
            'sign_date'        => $today,
            'order_num'        => $order_num,
            'consecutive_days' => $consecutiveDays,
            'balance'          => $sign_balance,
            'create_time'      => time(),
        ], true);

        //签到奖励
        $remark = "操作人[签到奖励];操作说明[签到获得积分{$today}];操作类型[签到奖励];";//管理备注
        MemberModel::inc_balance($this->user_id, $sign_balance, '签到', $remark, $sign_id, $order_num, 300);

        $this->success('签到成功');
    }


    /**
     * 连续签到天数等信息
     * @OA\Post(
     *     tags={"签到"},
     *     path="/wxapp/love_sign/get_consecutive_days",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/love_sign/get_consecutive_days
     *   official_environment: https://hl212.wxselling.com/api/wxapp/love_sign/get_consecutive_days
     *   api:  /wxapp/love_sign/get_consecutive_days
     *   remark_name: 连续签到天数等信息
     *
     */
    public function get_consecutive_days()
    {
        $this->checkAuth();
        $LoveSignInit  = new \init\LoveSignInit();//签到    (ps:InitController)
        $LoveSignModel = new \initmodel\LoveSignModel(); //签到   (ps:InitModel)

        $params = $this->request->param();

        $map   = [];
        $map[] = ['user_id', '=', $this->user_id];


        //自己连续签到天数
        $latestSignIn = $LoveSignModel->where($map)->order('sign_date desc,id desc')->find();

        $yesterday    = date('Y-m-d', strtotime('-1 day'));
        $today        = date('Y-m-d');
        //如果昨天和今天都没签到,那么天数为0
        $consecutive_days = 0;
        //检测昨天,或者今天有没有签到
        if ($latestSignIn['sign_date'] == $yesterday || $latestSignIn['sign_date'] == $today) $consecutive_days = $latestSignIn['consecutive_days'];


        //累计签到人数
        $result['total_sign_in'] = $LoveSignModel->count();

        //今日签到人数
        $result['today_sign_in'] = $LoveSignModel->where('sign_date', '=', $today)->count();

        //昨日签到人数
        $result['yesterday_sign_in'] = $LoveSignModel->where('sign_date', '=', $yesterday)->count();


        //我连续签到天数
        $result['me_consecutive_days'] = $consecutive_days;


        //积累签到总积分
        $result['total_balance'] = $LoveSignModel->where($map)->sum('balance');


        $this->success('请求成功!', $result);
    }

}
