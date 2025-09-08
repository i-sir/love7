<?php

namespace app\admin\controller;


/**
 * @adminMenuRoot(
 *     "name"                =>"MemberAuthentication",
 *     "name_underline"      =>"member_authentication",
 *     "controller_name"     =>"MemberAuthentication",
 *     "table_name"          =>"member_authentication",
 *     "action"              =>"default",
 *     "parent"              =>"",
 *     "display"             => true,
 *     "order"               => 10000,
 *     "icon"                =>"none",
 *     "remark"              =>"认证管理",
 *     "author"              =>"",
 *     "create_time"         =>"2024-08-22 14:55:19",
 *     "version"             =>"1.0",
 *     "use"                 => new \app\admin\controller\MemberAuthenticationController();
 * )
 */


use think\facade\Db;
use cmf\controller\AdminBaseController;


class MemberAuthenticationController extends AdminBaseController
{


    public function initialize()
    {
        //认证管理
        parent::initialize();
    }



    /**
     * 首页基础信息
     */
    protected function base_index()
    {
        //是否引荐人后台
        if ($this->admin_info['recommend_id']) $this->assign('admin_recommend_id', $this->admin_info['recommend_id']);
    }

    /**
     * 编辑,添加基础信息
     */
    protected function base_edit()
    {
        //是否引荐人后台
        if ($this->admin_info['recommend_id']) $this->assign('admin_recommend_id', $this->admin_info['recommend_id']);
    }


    /**
     * 展示
     * @adminMenu(
     *     'name'             => 'MemberAuthentication',
     *     'name_underline'   => 'member_authentication',
     *     'parent'           => 'index',
     *     'display'          => true,
     *     'hasView'          => true,
     *     'order'            => 10000,
     *     'icon'             => '',
     *     'remark'           => '认证管理',
     *     'param'            => ''
     * )
     */
    public function index()
    {
        $this->base_index();
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理    (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //查询条件
        $where = [];
        if ($params["keyword"]) $where[] = ["status|user_id", "like", "%{$params["keyword"]}%"];
        if ($params["user_id"]) $where[] = ["user_id", "=", $params["user_id"]];

        if ($params["test"]) $where[] = ["test", "=", $params["test"]];
        //if($params["status"]) $where[]=["status","=", $params["status"]];
        $where[] = ["is_admin", "=", 2];


        $params["InterfaceType"] = "admin";//接口类型
        $params["order"]         = "type";//接口类型


        //导出数据
        if ($params["is_export"]) $this->export_excel($where, $params);

        //查询数据
        $result = $MemberAuthenticationInit->get_list_paginate($where, $params);

        //数据渲染
        $this->assign("list", $result);
        $this->assign("page", $result->render());//单独提取分页出来

        return $this->fetch();
    }


    //权限详情
    public function auth_list()
    {
        $MemberAuthenticationModel    = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $MemberAuthenticationListInit = new \init\MemberAuthenticationListInit();//认证列表    (ps:InitController)
        $MemberModel                  = new \initmodel\MemberModel();//用户管理

        $params = $this->request->param();


        $result = $MemberAuthenticationListInit->get_list();
        foreach ($result as $key => &$value) {
            $value['status']  = 0;//未提交
            $value['checked'] = '';

            $map                   = [];
            $map[]                 = ['user_id', '=', $params['user_id']];
            $map[]                 = ['type', '=', $value['type']];
            $member_authentication = $MemberAuthenticationModel->where($map)->find();
            if ($member_authentication) {
                $value['status'] = $member_authentication['status'];
                if ($value['status'] == 2 || $member_authentication['is_admin'] == 1) $value['checked'] = 'checked';
            }
        }

        $this->assign("list", $result);

        //查询富文本内容
        $authentication_images = $MemberModel->where('id', '=', $params['user_id'])->value('authentication_images');
        if ($authentication_images) {
            $this->assign("authentication_images", $this->getParams($authentication_images));
        }

        return $this->fetch();
    }

    //提交信息
    public function auth_post()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $MemberModel               = new \initmodel\MemberModel();//用户管理


        $params = $this->request->param();


        $MemberAuthenticationModel->where('user_id', '=', $params['user_id'])->update(['delete_time' => time()]);

        $ids_keys = array_keys($params['ids']);
        foreach ($ids_keys as $key => $value) {
            $map     = [];
            $map[]   = ['user_id', '=', $params['user_id']];
            $map[]   = ['type', '=', $value];
            $is_auth = $MemberAuthenticationModel->withTrashed()->where($map)->find();
            if (empty($is_auth)) {
                //手动点亮
                $MemberAuthenticationModel->strict(false)->insert([
                    'status'      => 2,
                    'is_admin'    => 1,
                    'user_id'     => $params['user_id'],
                    'type'        => $value,
                    'create_time' => time(),
                ]);
            } else {
                $MemberAuthenticationModel->withTrashed()->where($map)->update(['delete_time' => 0]);
            }
        }


        //富文本更新用户里面
        $MemberModel->where('id', '=', $params['user_id'])->update([
            'authentication_images' => $this->setParams($params['authentication_images']),
            'update_time'           => time(),
        ]);

        $this->success("保存成功", "index{$this->params_url}");
    }


    //编辑详情
    public function edit()
    {
        $this->base_edit();
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理  (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberAuthenticationInit->get_find($where, $params);
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
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交数据
        $result = $MemberAuthenticationInit->admin_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //提交(副本,无任何操作) 编辑&添加
    public function edit_post_two()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        //提交数据
        $result = $MemberAuthenticationInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //驳回
    public function refuse()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理  (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberAuthenticationInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        //数据格式转数组
        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }

        return $this->fetch();
    }


    //驳回,更改状态
    public function audit_post()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        if ($params['status'] == 2) $params['pass_time'] = time();
        if ($params['status'] == 3) $params['refuse_time'] = time();

        //提交数据
        $result = $MemberAuthenticationInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("操作成功");
    }


    //添加
    public function add()
    {
        $this->base_edit();

        return $this->fetch();
    }


    //添加提交
    public function add_post()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //插入数据
        $result = $MemberAuthenticationInit->admin_edit_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //查看详情
    public function find()
    {
        $this->base_edit();

        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理    (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $MemberAuthenticationInit->get_find($where, $params);
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
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        if ($params["id"]) $id = $params["id"];
        if (empty($params["id"])) $id = $this->request->param("ids/a");

        //删除数据
        $result = $MemberAuthenticationInit->delete_post($id);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功", "index{$this->params_url}");
    }


    //批量操作
    public function batch_post()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param();

        $id = $this->request->param("id/a");
        if (empty($id)) $id = $this->request->param("ids/a");

        //提交编辑
        $result = $MemberAuthenticationInit->batch_post($id, $params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //更新排序
    public function list_order_post()
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)
        $params                    = $this->request->param("list_order/a");

        //提交更新
        $result = $MemberAuthenticationInit->list_order_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    /**
     * 导出数据
     * @param array $where 条件
     */
    public function export_excel($where = [], $params = [])
    {
        $MemberAuthenticationInit  = new \init\MemberAuthenticationInit();//认证管理   (ps:InitController)
        $MemberAuthenticationModel = new \initmodel\MemberAuthenticationModel(); //认证管理   (ps:InitModel)


        $result = $MemberAuthenticationInit->get_list($where, $params);

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


}
