<?php

namespace app\admin\controller;


/**
 * @adminMenuRoot(
 *     "name"                =>"MemberRecommend",
 *     "name_underline"      =>"member_recommend",
 *     "controller_name"     =>"MemberRecommend",
 *     "table_name"          =>"member_recommend",
 *     "action"              =>"default",
 *     "parent"              =>"",
 *     "display"             => true,
 *     "order"               => 10000,
 *     "icon"                =>"none",
 *     "remark"              =>"引荐人",
 *     "author"              =>"",
 *     "create_time"         =>"2024-08-21 14:46:59",
 *     "version"             =>"1.0",
 *     "use"                 => new \app\admin\controller\MemberRecommendController();
 * )
 */


use think\facade\Db;
use cmf\controller\AdminBaseController;


class MemberRecommendController extends AdminBaseController
{


    //    public function initialize()
    //    {
    //        //引荐人
    //        parent::initialize();
    //    }


    /**
     * 展示
     * @adminMenu(
     *     'name'             => 'MemberRecommend',
     *     'name_underline'   => 'member_recommend',
     *     'parent'           => 'index',
     *     'display'          => true,
     *     'hasView'          => true,
     *     'order'            => 10000,
     *     'icon'             => '',
     *     'remark'           => '引荐人',
     *     'param'            => ''
     * )
     */
    public function index()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人    (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //查询条件
        $where = [];
        if ($params["keyword"]) $where[] = ["nickname|phone|account_name", "like", "%{$params["keyword"]}%"];
        if ($params["test"]) $where[] = ["test", "=", $params["test"]];
        //if($params["status"]) $where[]=["status","=", $params["status"]];
        //$where[]=["type","=", 1];


        $params["InterfaceType"] = "admin";//接口类型


        //导出数据
        if ($params["is_export"]) $this->export_excel($where, $params);

        //查询数据
        $result = $MemberRecommendInit->get_list_paginate($where, $params);

        //数据渲染
        $this->assign("list", $result);
        $this->assign("page", $result->render());//单独提取分页出来

        return $this->fetch();
    }

    //编辑详情
    public function edit()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人  (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberRecommendInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        //数据格式转数组
        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }

        return $this->fetch();
    }

    //二维码
    public function qr()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人  (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $Qr                   = new \init\QrInit();

        $qr_code_redirect_address = cmf_config('qr_code_redirect_address');

        $params = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //生成新的二维码
        $user_info = $MemberRecommendModel->where($where)->find();

        //分销+二维码图
        $qr_image = $Qr->get_qr($qr_code_redirect_address . $user_info['invite_code']);
        //$image    = $Qr->applet_share($qr_image);//邀请好友卡片

        $this->assign('qr_image', $qr_image);
        $this->assign('invite_code', $user_info['invite_code']);


        return $this->fetch();
    }

    //编辑详情
    public function pass()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人  (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberRecommendInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        //数据格式转数组
        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }

        return $this->fetch();
    }


    //编辑详情
    public function refuse()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人  (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberRecommendInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        //数据格式转数组
        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }

        return $this->fetch();
    }


    //提交编辑
    public function edit_post()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交数据
        $result = $MemberRecommendInit->admin_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //提交(副本,无任何操作) 编辑&添加
    public function edit_post_two()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        //提交数据
        $result = $MemberRecommendInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //更改状态操作
    public function audit_post()
    {
        // 启动事务
        Db::startTrans();

        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        //审核通过添加后台账号
        if ($params['status'] == 2) {
            if (empty($params['account_name'])) $this->error('请填写账号');
            if (empty($params['pass'])) $this->error('请填写密码');
            $params['pass'] = cmf_password($params['pass']);

            $is_user = Db::name('user')->where('user_login', $params['account_name'])->find();
            if ($is_user) $this->error('账号已存在');

            $map       = [];
            $map[]     = ['recommend_id', '=', $params['id']];
            $user_info = Db::name('user')->where($map)->find();
            if (empty($user_info)) {
                $user_id = Db::name('user')->strict(false)->insert([
                    'user_login'   => $params['account_name'],
                    'user_pass'    => $params['pass'],
                    'user_email'   => $params['account_name'],
                    'recommend_id' => $params['id'],
                    'create_time'  => time(),
                ], true);

                Db::name('role_user')->strict(false)->insert([
                    'user_id' => $user_id,
                    'role_id' => 2,
                ]);
            }


        }

        //提交数据
        $result = $MemberRecommendInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");


        Db::commit();


        $this->success("操作成功");
    }


    //添加
    public function add()
    {
        return $this->fetch();
    }


    //添加提交
    public function add_post()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //插入数据
        $result = $MemberRecommendInit->admin_edit_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //查看详情
    public function find()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人    (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberRecommendInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        //数据格式转数组
        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }

        return $this->fetch();
    }


    //删除
    public function delete()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        if ($params["id"]) $id = $params["id"];
        if (empty($params["id"])) $id = $this->request->param("ids/a");

        //删除数据
        $result = $MemberRecommendInit->delete_post($id);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功", "index{$this->params_url}");
    }


    //批量操作
    public function batch_post()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param();

        $id = $this->request->param("id/a");
        if (empty($id)) $id = $this->request->param("ids/a");

        //提交编辑
        $result = $MemberRecommendInit->batch_post($id, $params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //更新排序
    public function list_order_post()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $params               = $this->request->param("list_order/a");

        //提交更新
        $result = $MemberRecommendInit->list_order_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    /**
     * 导出数据
     * @param array $where 条件
     */
    public function export_excel($where = [], $params = [])
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)


        $result = $MemberRecommendInit->get_list($where, $params);

        $result = $result->toArray();
        foreach ($result as $k => &$item) {

            //订单号过长问题
            if ($item["order_num"]) $item["order_num"] = $item["order_num"] . "\t";

            //图片链接 可用默认浏览器打开   后面为展示链接名字 --单独,多图特殊处理一下
            if ($item["image"]) $item["image"] = '=HYPERLINK("' . cmf_get_asset_url($item['image']) . '","图片.png")';


            //用户信息
            $user_info        = $item['user_info'];
            $item['userInfo'] = "(ID:{$user_info['id']}) {$user_info['nickname']}  {$user_info['phone']}";


            //背景颜色
            if ($item['unit'] == '测试8') $item['BackgroundColor'] = 'red';
        }

        $headArrValue = [
            ["rowName" => "ID", "rowVal" => "id", "width" => 10],
            ["rowName" => "用户信息", "rowVal" => "userInfo", "width" => 30],
            ["rowName" => "名字", "rowVal" => "name", "width" => 20],
            ["rowName" => "年龄", "rowVal" => "age", "width" => 20],
            ["rowName" => "测试", "rowVal" => "test", "width" => 20],
            ["rowName" => "创建时间", "rowVal" => "create_time", "width" => 30],
        ];


        //副标题 纵单元格
        //        $subtitle = [
        //            ["rowName" => "列1", "acrossCells" => count($headArrValue)/2],
        //            ["rowName" => "列2", "acrossCells" => count($headArrValue)/2],
        //        ];

        $Excel = new ExcelController();
        $Excel->excelExports($result, $headArrValue, ["fileName" => "导出"]);
    }


    //操作 积分 或余额
    public function operate()
    {
        $params = $this->request->param();
        foreach ($params as $k => $v) {
            $this->assign($k, $v);
        }
        return $this->fetch();
    }

    //提交
    public function operate_post()
    {
        $MemberRecommendInit  = new \init\MemberRecommendInit();//引荐人   (ps:InitController)
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)

        $params            = $this->request->param();
        $member            = $MemberRecommendModel->where('id', '=', $params['id'])->find();
        $admin_id_and_name = cmf_get_current_admin_id() . '-' . session('name');//管理员信息


        if ($params['operate_type'] == 1) {
            //余额

            if ($params['type'] == 1) {
                if (empty($params['content'])) $params['content'] = '管理员添加';
                //增加
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[增加引荐人余额];";//管理备注
                $MemberRecommendModel->inc_balance($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }

            if ($params['type'] == 2) {
                if (empty($params['content'])) $params['content'] = '管理员扣除';
                //扣除
                if ($member['balance'] < $params['price']) $this->error('请输入正确金额');
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[扣除引荐人余额];";//管理备注
                $MemberRecommendModel->dec_balance($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }
        }


        if ($params['operate_type'] == 2) {
            //积分

            if ($params['type'] == 1) {
                if (empty($params['content'])) $params['content'] = '管理员添加';
                //增加
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[增加引荐人积分];";//管理备注
                $MemberRecommendModel->inc_point($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }

            if ($params['type'] == 2) {
                if (empty($params['content'])) $params['content'] = '管理员扣除';
                //扣除
                if ($member['point'] < $params['price']) $this->error('请输入正确金额');
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[扣除引荐人积分];";//管理备注
                $MemberRecommendModel->dec_point($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }
        }

        $this->success('操作成功');
    }


    //编辑详情
    public function log()
    {
        $params = $this->request->param();
        foreach ($params as $k => $v) {
            $this->assign($k, $v);
        }

        //数据库
        if ($params['type'] == 1) $name = 'member_recommend_balance';
        if ($params['type'] == 2) $name = 'member_point';
        if (empty($name)) $name = 'member_recommend_balance';

        if ($name == 'member_recommend_balance') {
            $this->assign('type', 1);
            $this->assign('type1', 'class="active"');
        }
        if ($name == 'member_point') {
            $this->assign('type', 2);
            $this->assign('type2', 'class="active"');
        }


        //筛选条件
        $map   = [];
        $map[] = ["user_id", "=", $params["user_id"]];
        $map[] = $this->getBetweenTime($params['beginTime'], $params['endTime']);
        if ($params['keyword']) $map[] = ["content", "like", "%{$params['keyword']}%"];


        $type   = [1 => '收入', 2 => '支出'];
        $result = Db::name($name)
            ->where($map)
            ->order('id desc')
            ->paginate(['list_rows' => 15, 'query' => $params])
            ->each(function ($item, $key) use ($type) {

                $item['type_name'] = $type[$item['type']];

                return $item;
            });


        $this->assign("list", $result);
        $this->assign('page', $result->render());//单独提取分页出来

        return $this->fetch();
    }


}
