<?php

namespace app\admin\controller;


/**
 * @adminMenuRoot(
 *     "name"                =>"Statistics",
 *     "name_underline"      =>"statistics",
 *     "controller_name"     =>"Statistics",
 *     "table_name"          =>"statistics",
 *     "action"              =>"default",
 *     "parent"              =>"",
 *     "display"             => true,
 *     "order"               => 10000,
 *     "icon"                =>"none",
 *     "remark"              =>"统计管理",
 *     "author"              =>"",
 *     "create_time"         =>"2024-08-29 09:48:44",
 *     "version"             =>"1.0",
 *     "use"                 => new \app\admin\controller\StatisticsController();
 * )
 */


use think\facade\Db;
use cmf\controller\AdminBaseController;


class StatisticsController extends AdminBaseController
{


    //    public function initialize()
    //    {
    //        //统计管理
    //        parent::initialize();
    //    }


    /**
     * 展示
     * @adminMenu(
     *     'name'             => 'Statistics',
     *     'name_underline'   => 'statistics',
     *     'parent'           => 'index',
     *     'display'          => true,
     *     'hasView'          => true,
     *     'order'            => 10000,
     *     'icon'             => '',
     *     'remark'           => '统计管理',
     *     'param'            => ''
     * )
     */
    public function index()
    {
        $MemberModel           = new \initmodel\MemberModel();//用户管理
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)
        $MemberRecommendModel  = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)
        $ShopModel             = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)
        $MemberVipModel        = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)


        $params = $this->request->param();


        //平台用户
        $result['member_total'] = $MemberModel->count();

        //引荐人
        $result['recommend_total'] = $MemberRecommendModel->count();

        //店铺
        $result['shop_total'] = $ShopModel->count();

        //红娘
        $result['matchmaker_total'] = $MemberMatchmakerModel->count();


        foreach ($result as $k => $v) {
            $this->assign($k, $v);
        }

        /** 计算男女比例 **/
        // 获取男性用户数量
        $maleCount = $MemberModel->where('gender', '男')->count();
        // 获取女性用户数量
        $femaleCount = $MemberModel->where('gender', '女')->count();
        // 计算总人数
        $totalCount = $maleCount + $femaleCount;
        // 计算男女比例
        if ($totalCount > 0) {
            $maleRatio   = $maleCount / $totalCount * 100;
            $femaleRatio = $femaleCount / $totalCount * 100;
        } else {
            // 如果没有数据，默认比例为0
            $maleRatio   = 0;
            $femaleRatio = 0;
        }
        $proportion_data = json_encode([
            ['value' => round($maleRatio, 2), 'name' => '男'],
            ['value' => round($femaleRatio, 2), 'name' => '女'],
        ]);
        $this->assign('proportion_data', $proportion_data);

        /** 会员比例 **/
        $p_vip_list = $MemberVipModel->where('pid', 0)->select();
        $vip_ids    = array_column($p_vip_list->toArray(), 'name', 'id');
        $vip_list   = $MemberVipModel->where('pid', '<>', 0)->order('list_order,id desc')->select();
        $vip_data   = [];
        foreach ($vip_list as $k => &$v) {
            $map        = [];
            $map[]      = ['vip_id', '=', $v['id']];
            $map[]      = ['end_time', '>', time()];
            $vip_data[] = [
                'name'  => $v['name'],
                'value' => $MemberModel->where($map)->count(),
            ];

            $v['name'] = $vip_ids[$v['pid']] . ' - ' . $v['name'];
        }
        $this->assign('vip_data', json_encode($vip_data));

        /** 店铺充值比例 **/
        // 统计有效店铺数量（end_time 大于当前时间）
        $shopCount = $ShopModel
            ->where('end_time', '>', time())
            ->count();

        // 统计普通店铺数量（end_time 小于或等于当前时间）
        $regularCount = $ShopModel
            ->where('end_time', '<=', time())
            ->count();

        // 计算总人数
        $totalCount = $shopCount + $regularCount;
        // 计算比例
        if ($totalCount > 0) {
            $shopRatio    = $shopCount / $totalCount * 100;
            $regularRatio = $regularCount / $totalCount * 100;
        } else {
            // 如果没有数据，默认比例为0
            $shopRatio    = 0;
            $regularRatio = 0;
        }
        $shop_data = json_encode([
            ['value' => round($shopRatio, 2), 'name' => '充值'],
            ['value' => round($regularRatio, 2), 'name' => '未充值'],
        ]);
        $this->assign('shop_data', $shop_data);


        /**用户注册柱状图**/
        // 初始化日期范围数组
        $startDate  = strtotime('-1 month');
        $endDate    = strtotime('now');
        $day_list   = [];
        $count_list = [];

        // 初始化每日注册量
        while ($startDate <= $endDate) {
            $date         = date('Y-m-d', $startDate);
            $startDate    = strtotime('+1 day', $startDate);
            $day_list[]   = $date; //日期
            $count_list[] = $MemberModel->where('create_time', 'between', [strtotime($date . ' 00:00:00'), strtotime($date . ' 23:59:59')])->count();
        }
        $xAxis_data  = json_encode([
            'type'     => 'category',
            'data'     => $day_list,
            'axisTick' => ['alignWithLabel' => true],
        ]);
        $series_data = json_encode([
            'name'     => '用户注册增长',
            'type'     => 'bar',
            'barWidth' => '60%',
            'data'     => $count_list,
        ]);

        $this->assign('xAxis_data', $xAxis_data);
        $this->assign('series_data', $series_data);

        return $this->fetch();
    }

    //编辑详情
    public function edit()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理  (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $StatisticsInit->get_find($where, $params);
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
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();


        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];


        //提交数据
        $result = $StatisticsInit->admin_edit_post($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //提交(副本,无任何操作) 编辑&添加
    public function edit_post_two()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        //提交数据
        $result = $StatisticsInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //驳回
    public function refuse()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理  (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $StatisticsInit->get_find($where, $params);
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
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //更改数据条件 && 或$params中存在id本字段可以忽略
        $where = [];
        if ($params['id']) $where[] = ['id', '=', $params['id']];

        //提交数据
        $result = $StatisticsInit->edit_post_two($params, $where);
        if (empty($result)) $this->error("失败请重试");

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
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //插入数据
        $result = $StatisticsInit->admin_edit_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //查看详情
    public function find()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理    (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        //查询条件
        $where   = [];
        $where[] = ["id", "=", $params["id"]];

        //查询数据
        $params["InterfaceType"] = "admin";//接口类型
        $result                  = $StatisticsInit->get_find($where, $params);
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
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        if ($params["id"]) $id = $params["id"];
        if (empty($params["id"])) $id = $this->request->param("ids/a");

        //删除数据
        $result = $StatisticsInit->delete_post($id);
        if (empty($result)) $this->error("失败请重试");

        $this->success("删除成功", "index{$this->params_url}");
    }


    //批量操作
    public function batch_post()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param();

        $id = $this->request->param("id/a");
        if (empty($id)) $id = $this->request->param("ids/a");

        //提交编辑
        $result = $StatisticsInit->batch_post($id, $params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    //更新排序
    public function list_order_post()
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)
        $params          = $this->request->param("list_order/a");

        //提交更新
        $result = $StatisticsInit->list_order_post($params);
        if (empty($result)) $this->error("失败请重试");

        $this->success("保存成功", "index{$this->params_url}");
    }


    /**
     * 导出数据
     * @param array $where 条件
     */
    public function export_excel($where = [], $params = [])
    {
        $StatisticsInit  = new \init\StatisticsInit();//统计管理   (ps:InitController)
        $StatisticsModel = new \initmodel\StatisticsModel(); //统计管理   (ps:InitModel)


        $result = $StatisticsInit->get_list($where, $params);

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
