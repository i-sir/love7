<?php

namespace api\wxapp\controller;

/**
 * @ApiController(
 *     "name"                    =>"ShopGoods",
 *     "name_underline"          =>"shop_goods",
 *     "controller_name"         =>"ShopGoods",
 *     "table_name"              =>"shop_goods",
 *     "remark"                  =>"商品管理"
 *     "api_url"                 =>"/api/wxapp/shop_goods/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-08-26 10:46:44",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\ShopGoodsController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/shop_goods/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/shop_goods/index",
 * )
 */


use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;


error_reporting(0);


class ShopGoodsController extends AuthController
{


    public function initialize()
    {
        //商品管理

        parent::initialize();
    }


    /**
     * 默认接口
     * /api/wxapp/shop_goods/index
     * https://xcxkf186.aubye.com/api/wxapp/shop_goods/index
     */
    public function index()
    {
        $ShopGoodsInit  = new \init\ShopGoodsInit();//商品管理   (ps:InitController)
        $ShopGoodsModel = new \initmodel\ShopGoodsModel(); //商品管理   (ps:InitModel)

        $result = [];

        $this->success('商品管理-接口请求成功', $result);
    }


    /**
     * 商品管理 列表
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/find_shop_goods_list",
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
     *
     *     @OA\Parameter(
     *         name="***",
     *         in="query",
     *         description="选择区域",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="class_id",
     *         in="query",
     *         description="商家类型",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="star",
     *         in="query",
     *         description="true 好评优先",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="shop_id",
     *         in="query",
     *         description="店铺id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *     @OA\Parameter(
     *         name="is_me",
     *         in="query",
     *         description="true 自己上传",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *     @OA\Parameter(
     *         name="not_ids",
     *         in="query",
     *         description="过滤商品id  数组",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/find_shop_goods_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/find_shop_goods_list
     *   api:  /wxapp/shop_goods/find_shop_goods_list
     *   remark_name: 商品管理 列表
     *
     */
    public function find_shop_goods_list()
    {
        $this->checkAuth();

        $ShopGoodsInit  = new \init\ShopGoodsInit();//商品管理   (ps:InitController)
        $ShopGoodsModel = new \initmodel\ShopGoodsModel(); //商品管理   (ps:InitModel)
        $ShopModel      = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where = [];
        if (empty($params['is_me'])) $where[] = ['is_show', '=', 1];//上架商品
        if ($params["keyword"]) $where[] = ["goods_name", "like", "%{$params['keyword']}%"];
        if ($params["shop_id"]) $where[] = ["shop_id", "=", $params["shop_id"]];
        if ($params["not_ids"]) $where[] = ["not_ids", "not in", $params["not_ids"]];
        if ($params["status"]) $where[] = ["status", "=", $params["status"]];
        if ($params["is_me"]) $where[] = ["user_id", "=", $params["user_id"]];

        //好评排序
        if ($params['star']) $params['order'] = 'star desc';

        //店铺类型
        if ($params["class_id"]) {
            $map       = [];
            $map[]     = ['class_id', '=', $params['class_id']];
            $shop_list = $ShopModel->where($map)->select();
            $shop_ids  = array_column($shop_list, 'id');
            $where[]   = ["shop_id", "in", $shop_ids];
        }


        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $ShopGoodsInit->get_list_paginate($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 分类管理
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/find_class_list",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/find_class_list
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/find_class_list
     *   api:  /wxapp/shop_goods/find_class_list
     *   remark_name: 分类管理
     *
     */
    public function find_class_list()
    {
        $ShopClassInit  = new \init\ShopClassInit();//店铺类型    (ps:InitController)
        $ShopClassModel = new \initmodel\ShopClassModel(); //店铺类型   (ps:InitModel)
        $params         = $this->request->param();

        //查询条件
        $where = [];
        if ($params["keyword"]) $where[] = ["name", "like", "%{$params["keyword"]}%"];


        //查询数据
        $result = $ShopClassInit->get_list($where, $params);
        if (empty($result)) $this->error("暂无信息!");

        $this->success("请求成功!", $result);
    }


    /**
     * 商品管理 详情
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/find_shop_goods",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/find_shop_goods
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/find_shop_goods
     *   api:  /wxapp/shop_goods/find_shop_goods
     *   remark_name: 商品管理 详情
     *
     */
    public function find_shop_goods()
    {
        $ShopGoodsInit  = new \init\ShopGoodsInit();//商品管理    (ps:InitController)
        $ShopGoodsModel = new \initmodel\ShopGoodsModel(); //商品管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params["user_id"] = $this->user_id;

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "api";//接口类型
        $result                  = $ShopGoodsInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $this->success("详情数据", $result);
    }


    /**
     * 商品管理 编辑&添加
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/edit_shop_goods",
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
     *
     *    @OA\Parameter(
     *         name="goods_name",
     *         in="query",
     *         description="商品名称",
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
     *         name="images",
     *         in="query",
     *         description="图集  数组",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *
     *
     *
     *    @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="价格",
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
     *         description="id空添加,存在编辑",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/edit_shop_goods
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/edit_shop_goods
     *   api:  /wxapp/shop_goods/edit_shop_goods
     *   remark_name: 商品管理 编辑&添加
     *
     */
    public function edit_shop_goods()
    {
        $this->checkAuth();

        $ShopGoodsInit  = new \init\ShopGoodsInit();//商品管理    (ps:InitController)
        $ShopGoodsModel = new \initmodel\ShopGoodsModel(); //商品管理   (ps:InitModel)

        //参数
        $params            = $this->request->param();
        $params['status']  = 1;//编辑就审核
        $params["user_id"] = $this->user_id;
        $params["shop_id"] = $this->user_info['shop_id'];


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交更新
        $result = $ShopGoodsInit->api_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");


        if (empty($params["id"])) $msg = "添加成功";
        if (!empty($params["id"])) $msg = "编辑成功";
        $this->success($msg);
    }


    /**
     * 商品管理 删除
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/delete_shop_goods",
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
     *     @OA\Response(response="200", description="An example resource"),
     *     @OA\Response(response="default", description="An example resource")
     * )
     *
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/delete_shop_goods
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/delete_shop_goods
     *   api:  /wxapp/shop_goods/delete_shop_goods
     *   remark_name: 商品管理 删除
     *
     */
    public function delete_shop_goods()
    {
        $ShopGoodsInit  = new \init\ShopGoodsInit();//商品管理    (ps:InitController)
        $ShopGoodsModel = new \initmodel\ShopGoodsModel(); //商品管理   (ps:InitModel)

        //参数
        $params = $this->request->param();

        //删除数据
        $result = $ShopGoodsInit->delete_post($params["id"]);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功");
    }


    /**
     * 商品管理 上级,下架
     * @OA\Post(
     *     tags={"商品管理"},
     *     path="/wxapp/shop_goods/show",
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
     *    @OA\Parameter(
     *         name="is_show",
     *         in="query",
     *         description="上架:1是,2否",
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
     *   test_environment: http://love7.ikun:9090/api/wxapp/shop_goods/show
     *   official_environment: https://xcxkf186.aubye.com/api/wxapp/shop_goods/show
     *   api:  /wxapp/shop_goods/show
     *   remark_name: 商品管理 上级,下架
     *
     */
    public function show()
    {
        $this->checkAuth();

        $ShopGoodsInit = new \init\ShopGoodsInit();//商品管理    (ps:InitController)

        //参数
        $params = $this->request->param();


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //上下架
        $goods_info = $ShopGoodsInit->get_find($where);
        if ($goods_info['is_show'] == 1) $params['is_show'] = 2;
        else $params['is_show'] = 1;


        //提交更新
        $result = $ShopGoodsInit->api_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");


        $this->success('操作成功');
    }

}
