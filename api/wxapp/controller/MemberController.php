<?php
// +----------------------------------------------------------------------
// | 会员中心
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
namespace api\wxapp\controller;

use initmodel\MemberModel;
use think\facade\Db;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:*');


class MemberController extends AuthController
{
    public function initialize()
    {
        parent::initialize();//初始化方法
    }

    /**
     * 测试用
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member/index
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/index
     *   api: /wxapp/member/index
     *   remark_name: 测试用
     *
     */
    public function index()
    {
        $MemberInit = new \init\MemberInit();//用户管理

        $map                     = [];
        $map[]                   = ['id', '>', 99999];
        $params['InterfaceType'] = 'api';
        $result                  = $MemberInit->get_list_paginate($map, $params);

        $this->success('请求成功');
    }


    /**
     * 查询会员信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/find_member",
     *
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member/find_member
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/find_member
     *   api: /wxapp/member/find_member
     *   remark_name: 查询会员信息
     *
     */
    public function find_member()
    {
        $this->checkAuth();
        //查询会员信息
        $result = $this->getUserInfoByToken($this->token);

        $this->success("请求成功!", $result);
    }


    /**
     * 登录注册用户
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/pass_login",
     *
     *
     *
     *     @OA\Parameter(
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
     *     @OA\Parameter(
     *         name="sms_code",
     *         in="query",
     *         description="验证码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="p_invite_code",
     *         in="query",
     *         description="上级邀请码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
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
     *   test_environment: http://love0212.ikun/api/wxapp/member/pass_login
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/pass_login
     *   api: /wxapp/member/pass_login
     *   remark_name: 查询会员信息
     *
     */
    public function pass_login()
    {
        $MemberInit                = new \init\MemberInit();//用户管理
        $MemberModel               = new \initmodel\MemberModel();//用户管理
        $MemberRecommendModel      = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)

        $params = $this->request->param();


        $map         = [];
        $map[]       = ['phone', '=', $params['phone']];
        $member_info = $MemberModel->where($map)->find();


        //检测验证码是否正确
        $result = cmf_check_verification_code($params['phone'], $params['sms_code']);
        //if ($result) $this->error($result);


        if (empty($member_info)) {
            //注册
            $member_info['id'] = $MemberModel->strict(false)->insert([
                'openid'      => $params['openid'],
                'phone'       => $params['phone'],
                'status'      => 2,//默认通过
                'vip_id'      => 1,//默认初级会员
                'end_time'    => 4102415999,
                'create_time' => time(),
                'ip'          => get_client_ip(),
                'login_city'  => $this->get_ip_to_city(),
            ], true);

            //手机号认证
            $MemberAuthenticationModel->strict(false)->insert([
                'status'      => 2,
                'user_id'     => $member_info['id'],
                'content'     => serialize(['phone' => $params['phone']]),
                'type'        => 1,
                'create_time' => time(),
                'pass_time'   => time(),
            ]);
        } else {
            //数据库已存在用户,更新用户登录信息
            $update['openid']      = $params['openid'];
            $update['update_time'] = time();
            $update['login_time']  = time();
            $update['ip']          = get_client_ip();
            $update['login_city']  = $this->get_ip_to_city();

            $MemberModel->where('id', '=', $member_info['id'])->strict(false)->update($update);
        }

        //备份记录token
        $map        = [];
        $map[]      = ['user_id', '=', $member_info['id']];
        $map[]      = ['device_type', '=', 'member'];
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
        $token        = cmf_generate_user_token($member_info['id'], 'member');
        $findUserInfo = $this->getUserInfoByToken($token);

        if (empty($findUserInfo)) $this->error('非法操作');

        $this->success('成功', $findUserInfo);
    }


    /**
     * 更新会员信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/update_member",
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
     *         name="nickname",
     *         in="query",
     *         description="昵称",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
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
     *     @OA\Parameter(
     *         name="avatar",
     *         in="query",
     *         description="头像",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *      @OA\Parameter(
     *         name="used_pass",
     *         in="query",
     *         description="旧密码,如需要传,不需要请勿传",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="pass",
     *         in="query",
     *         description="更改密码,如需要传,不需要请勿传",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member/update_member
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/update_member
     *   api: /wxapp/member/update_member
     *   remark_name: 更新会员信息
     *
     */
    public function update_member()
    {
        $this->checkAuth();

        $MemberModel          = new \initmodel\MemberModel();//用户管理
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)


        $params                = $this->request->param();
        $params['update_time'] = time();
        if ($params['nickname'] && $params['birth']) $params['status'] = 1;//审核


        $member = $this->getUserInfoByToken($this->token);
        if (empty($member)) $this->error("该会员不存在!");
        if ($member['pid']) unset($params['pid']);

        //绑定上级
        if ($params['p_invite_code']) {
            //检测是否存在
            $is_recommend = $MemberRecommendModel->where('invite_code', '=', $params['p_invite_code'])->find();
            if (!$is_recommend) $this->error("该邀请码不存在!");
        }
        if ($params['p_invite_code'] && empty($member['pid'])) {
            $recommend_info = $MemberRecommendModel->where('invite_code', $params['p_invite_code'])->find();
            if ($recommend_info) $params['pid'] = $recommend_info['id'];
        }

        //修改密码
        if ($params['pass']) {
            if (!cmf_compare_password($params['used_pass'], $member['pass'])) $this->error('旧密码错误');
            $params['pass'] = cmf_password($params['pass']);
        }

        //相册
        if ($params['images']) $params['images'] = $this->setImages($params['images']);
        //视频
        if ($params['video']) $params['video'] = $this->setImages($params['video']);
        //兴趣爱好
        if ($params['hobby']) $params['hobby'] = $this->setImages($params['hobby']);
        //宠物
        if ($params['pet']) $params['pet'] = $this->setImages($params['pet']);
        //标签
        if ($params['tag']) $params['tag'] = $this->setImages($params['tag']);

        $result = $MemberModel->where('id', $member['id'])->strict(false)->update($params);
        if ($result) {
            $result = $this->getUserInfoByToken($this->token);
            $this->success("保存成功!", $result);
        } else {
            $this->error("保存失败!");
        }
    }


    /**
     * 账户(余额)变动明细
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/find_balance_list",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member/find_balance_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/find_balance_list
     *   api: /wxapp/member/find_balance_list
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

        $result = Db::name("member_balance")
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
     * 查询用户信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/find_user",
     *
     *
     *
     *     @OA\Parameter(
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
     *   test_environment: http://love0212.ikun/api/wxapp/member/find_user
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/find_user
     *   api: /wxapp/member/find_user
     *   remark_name: 查询用户信息
     *
     */
    public function find_user()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//用户管理


        $map    = [];
        $map[]  = ['id', '=', $params['id']];
        $result = $MemberInit->get_find($map, ['field' => '*']);
        if (empty($result)) $this->error("暂无数据！");


        $this->success("请求成功！", $result);
    }


    /**
     * 用户列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/find_user_list",
     *
     *
     *
     *     @OA\Parameter(
     *         name="is_index",
     *         in="query",
     *         description="true  首页推荐",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="education",
     *         in="query",
     *         description="学历",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="income",
     *         in="query",
     *         description="收入",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="birth",
     *         in="query",
     *         description="出生",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="is_index",
     *         in="query",
     *         description="true 首页推荐",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *
     *     @OA\Parameter(
     *         name="keyword_id",
     *         in="query",
     *         description="会员编号",
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
     *   test_environment: http://love0212.ikun/api/wxapp/member/find_user_list
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/find_user_list
     *   api: /wxapp/member/find_user_list
     *   remark_name: 用户列表
     *
     */
    public function find_user_list()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//用户管理


        $map   = [];
        $map[] = ['id', '>', 0];
        $map[] = ['is_show', '=', 1];
        $map[] = ['status', '=', 2];

        if ($params['education']) $map[] = ['education', '=', $params['education']];
        if ($params['is_index'] && empty($params['keyword_id'])) $map[] = ['is_index', '=', 1];
        if ($params['income']) $map[] = ['income', '=', $params['income']];
        if ($params['birth']) $map[] = ['birth', 'like', "%{$params['birth']}"];
        if ($params['keyword']) $map[] = ['nickname|id', 'like', "%{$params['keyword']}"];
        //单身专区
        if ($params['index_type'] == 1) $map[] = ['marriage_status', 'in', ['未婚', '短婚未育']];
        //离异专区
        if ($params['index_type'] == 2) $map[] = ['marriage_status', 'in', ['离婚', '丧偶']];
        //本地专区
        if ($params['index_type'] == 3) $map[] = ['work_city_code', '=', 320400];//常州市
        //编号搜索
        if ($params['keyword_id']) $map[] = ['keyword_id', '=', $params['keyword_id']];


        //性别取反
        if ($this->user_info['identity_type'] == 'member') $map[] = ['gender', '<>', $this->user_info['gender']];

        //引荐人只查看自己下级  查看所有
        //if ($this->user_info['identity_type'] == 'recommend') $map[] = ['pid', '=', $this->user_id];


        //刷新时间,id排序
        $params['order'] = 'ranking,top_time desc,id desc';
        $params['field'] = '*';


        $result = $MemberInit->get_list_paginate($map, $params);
        if (empty($result)) $this->error("暂无数据！");


        $this->success("请求成功！", $result);
    }


    /**
     * 更新排名
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/update_ranking",
     *
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member/update_ranking
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/update_ranking
     *   api: /wxapp/member/update_ranking
     *   remark_name: 更新排名
     *
     */
    public function update_ranking()
    {
        $this->checkAuth();

        $MemberModel = new \initmodel\MemberModel();//用户管理


        //刷新置顶费用
        $refresh_top = cmf_config('refresh_top');
        if ($this->user_info['balance'] < $refresh_top) $this->error("余额不足");

        //更新排名
        $MemberModel->where('id', '=', $this->user_id)->update([
            'top_time' => time(),
            'ranking'  => 1,
        ]);

        //扣费
        $remark = "操作人[{$this->user_id}-{$this->user_info['nickname']}];操作说明[置顶:{$refresh_top}];操作类型[置顶扣除费用];";//管理备注
        MemberModel::dec_balance($this->user_id, $refresh_top, '置顶', $remark, 0, cmf_order_sn(), 200);


        $this->success("操作成功！", $this->getUserInfoByToken($this->token));
    }


    /**
     * 查询权限
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @OA\Post(
     *     tags={"会员中心模块"},
     *     path="/wxapp/member/get_auth",
     *
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
     *
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love0212.ikun/api/wxapp/member/get_auth
     *   official_environment: https://hl212.wxselling.com/api/wxapp/member/get_auth
     *   api: /wxapp/member/get_auth
     *   remark_name: 查询权限
     *
     */
    public function get_auth()
    {
        $result['is_avatar']    = false; // 头像
        $result['is_profile']   = false; // 个人信息,基础信息
        $result['is_otherfile'] = false; // 其他信息
        $result['is_lovers']    = false; // 红娘
        $result['is_talk']      = false; // 打招呼
        $result['is_phone']     = false; // 手机号
        $result['is_one']       = false; // 一对一

        //用户
        if ($this->user_id && $this->user_info['identity_type'] == 'member' && $this->user_info['vip_id']) {
            $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)
            $vip_info       = $MemberVipModel->where('id', '=', $this->user_info['vip_id'])->find();
            if ($vip_info['pid']) {
                $p_vip_info = $MemberVipModel->where('id', '=', $vip_info['pid'])->find();
                if ($p_vip_info['is_avatar'] == 1) $result['is_avatar'] = true;
                if ($p_vip_info['is_otherfile'] == 1) $result['is_otherfile'] = true;
                if ($p_vip_info['is_lovers'] == 1) $result['is_lovers'] = true;
                if ($p_vip_info['is_talk'] == 1) $result['is_talk'] = true;
                if ($p_vip_info['is_phone'] == 1) $result['is_phone'] = true;
                if ($p_vip_info['is_one'] == 1) $result['is_one'] = true;
            }
        }


        //        if ($this->user_id && $this->user_info['identity_type'] == 'member') {
        //            //2：注册就送初级会员（永久免费），平台内所有用户，头像为高清状态。同时屏蔽同性用户的信息，只保留异性用户。异性用户的个人基本信息和择偶要求，相册，动态，视频，均为屏蔽状态。增项类：红娘服务，打招呼，一对一，查看联系，均被限制使用。
        //
        //            //3：普通会员，平台内所有用户，头像为高清状态，展示用户的个人基本信息和择偶要求。同时屏蔽同性用户的信息，只保留异性用户。异性用户的相册，动态，视频，均为屏蔽状态。增项类：红娘服务，打招呼，一对一，查看联系，均被限制使用。
        //            if ($this->user_info['vip_id'] == 2) {
        //                $result['is_avatar']  = true; // 头像
        //                $result['is_profile'] = true;//个人信息,基础信息
        //            }
        //
        //            //4：白银会员，平台内所有用户，头像为高清状态，展示用户的个人基本信息和择偶要求。同时屏蔽同性用户的信息，只保留异性用户。异性用户相册，动态，视频，均为高清状态。开通增项类：红娘服务和打招呼。一对一和查看联系，均被限制。
        //            if ($this->user_info['vip_id'] == 3) {
        //                $result['is_avatar']    = true; // 头像
        //                $result['is_profile']   = true;//个人信息,基础信息
        //                $result['is_otherfile'] = true;//其他信息
        //                $result['is_lovers']    = true;//红娘
        //            }
        //            //5：黄金会员，平台内所有用户，头像为高清状态，展示用户的基本信息相册和择偶要求。同时屏蔽同性用户的信息，只保留异性用户。异性用户相册，动态，视频，均为高清状态。开通增项类：红娘服务，打招呼，一对一。查看联系为限制。
        //            if ($this->user_info['vip_id'] == 4) {
        //                $result['is_avatar']    = true; // 头像
        //                $result['is_profile']   = true;//个人信息,基础信息
        //                $result['is_otherfile'] = true;//其他信息
        //                $result['is_lovers']    = true;//红娘
        //                $result['is_talk']      = true;//打招呼
        //                $result['is_one']       = true;//一对一
        //            }
        //            //6：特别会员，开通上述所有会员功能和增项类权限
        //            if ($this->user_info['vip_id'] == 5) {
        //                $result['is_avatar']    = true; // 头像
        //                $result['is_profile']   = true;//个人信息,基础信息
        //                $result['is_otherfile'] = true;//其他信息
        //                $result['is_lovers']    = true;//红娘
        //                $result['is_talk']      = true;//打招呼
        //                $result['is_phone']     = true;//手机号
        //                $result['is_one']       = true;//一对一
        //            }
        //        }

        //引荐人
        if ($this->user_id && $this->user_info['identity_type'] == 'recommend') {
            //2：当引荐人没有充值管理费时，引荐人手机端中，展示的首页推荐用户，单身专区，离异专区，本地专区，所有用户的头像，相册，动态，视频，均为蒙层状态。
            $result['is_avatar']  = true; // 头像
            $result['is_profile'] = true; // 个人信息,基础信息


            //引荐人手机端，只展示平台用户的个人基本信息和择偶信息。
            if ($this->user_info['is_manage'] == 2) {
                $result['is_otherfile'] = true; // 其他信息
                $result['is_phone']     = true; // 手机号
            }


            //$result['is_profile'] = true; // 其他信息
        }

        //红娘全部开放
        if ($this->user_id && $this->user_info['identity_type'] == 'matchmaker') {
            $result['is_avatar']    = true; // 头像
            $result['is_profile']   = true; // 个人信息,基础信息
            $result['is_otherfile'] = true; // 其他信息
            $result['is_lovers']    = true; // 红娘
            $result['is_talk']      = true; // 打招呼
            $result['is_phone']     = true; // 手机号
            $result['is_one']       = true; // 一对一
        }


        $this->success("请求成功！", $result);
    }

}