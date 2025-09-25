<?php

namespace app\admin\controller;


/**
 * @adminMenuRoot(
 *     'name'   =>'Member',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'cogs',
 *     'remark' =>'会员管理',
 * )
 */

use think\App;
use think\facade\Db;
use cmf\controller\AdminBaseController;

class MemberController extends AdminBaseController
{

    //    public function initialize()
    //    {
    //        //会员管理
    //        parent::initialize();
    //    }


    /**
     * 首页基础信息
     */
    protected function base_index()
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人  (ps:InitModel)
        $this->assign("recommend_list", $MemberRecommendModel->field('id,nickname,phone,invite_code')->order('id desc')->select());


        $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级  (ps:InitModel)
        $p_vip_list     = $MemberVipModel->where('pid', 0)->select();
        $vip_ids        = array_column($p_vip_list->toArray(), 'name', 'id');
        $vip_list       = $MemberVipModel->order('list_order,id desc')->where('pid', '<>', 0)->select()
            ->each(function ($item, $key) use ($vip_ids) {
                if ($item['pid']) {
                    $item['name'] = $vip_ids[$item['pid']] . ' - ' . $item['name'];
                }
                return $item;
            });
        $this->assign("vip_list", $vip_list);


        //是否引荐人后台
        if ($this->admin_info['recommend_id']) $this->assign('admin_recommend_id', $this->admin_info['recommend_id']);


        //是否红娘后台
        if ($this->admin_info['matchmaker_id']) $this->assign('admin_matchmaker_id', $this->admin_info['matchmaker_id']);

    }

    /**
     * 编辑,添加基础信息
     */
    protected function base_edit()
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人  (ps:InitModel)
        $this->assign("recommend_list", $MemberRecommendModel->field('id,nickname,phone,invite_code')->order('id desc')->select());

        $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级  (ps:InitModel)
        $p_vip_list     = $MemberVipModel->where('pid', 0)->select();
        $vip_ids        = array_column($p_vip_list->toArray(), 'name', 'id');
        $vip_list       = $MemberVipModel->order('list_order,id desc')->where('pid', '<>', 0)->select()
            ->each(function ($item, $key) use ($vip_ids) {
                if ($item['pid']) {
                    $item['name'] = $vip_ids[$item['pid']] . ' - ' . $item['name'];
                }
                return $item;
            });
        $this->assign("vip_list", $vip_list);


        //是否引荐人后台
        if ($this->admin_info['recommend_id']) $this->assign('admin_recommend_id', $this->admin_info['recommend_id']);


        //是否红娘后台
        if ($this->admin_info['matchmaker_id']) $this->assign('admin_matchmaker_id', $this->admin_info['matchmaker_id']);

    }


    public function getArea()
    {
        if (cache('admin_region_list')) {
            $area = cache('admin_region_list');
        } else {
            $area = Db::name('region')->where('parent_id', '=', 10000000)->field('id,name,code')->select()->each(function ($item, $key) {
                $item['cityList'] = Db::name("region")->where(['parent_id' => $item['id']])->field('id,name,code')->select()->each(function ($item1, $key) {
                    $item1['areaList'] = Db::name("region")->where(['parent_id' => $item1['id']])->field('id,name,code')->select()->each(function ($item2, $key) {
                        return $item2;
                    });
                    return $item1;
                });
                return $item;
            });
            cache("admin_region_list", $area);
        }
        $this->success('list', '', $area);
    }

    /**
     * 展示
     * @adminMenu(
     *     'name'   => 'Member',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '会员管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $this->base_index();
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        $this->assign("excel", $params);//导出使用


        $where = [];
        if ($params["keyword"]) $where[] = ["nickname|phone", "like", "%{$params["keyword"]}%"];
        if ($params["work_address"]) $where[] = ["work_province|work_city|work_county", "like", "%{$params["work_address"]}%"];
        if ($params["recommend_id"]) $where[] = ["pid", "=", $params["recommend_id"]];
        if ($params["vip_id"]) $where[] = ["vip_id", "=", $params["vip_id"]];
        if ($params["gender"]) $where[] = ["gender", "=", $params["gender"]];
        if ($this->admin_info['recommend_id']) $where[] = ["pid", "=", $this->admin_info['recommend_id']];


        //导出数据
        if ($params["is_export"]) $this->export_excel($where, $params);


        $params['InterfaceType'] = 'admin';//身份类型,后台
        $params['field']         = '*';//所有字段
        $result                  = $MemberInit->get_list_paginate($where, $params);

        $this->assign("list", $result);
        $this->assign('page', $result->render());//单独提取分页出来


        return $this->fetch();
    }


    //编辑详情
    public function edit()
    {
        $this->base_edit();
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        $params['InterfaceType'] = 'admin';//身份类型,后台
        $params['field']         = '*';//所有字段

        $where   = [];
        $where[] = ['id', '=', $params['id']];


        $result = $MemberInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }


        /**
         *  下拉数据
         */
        $config_list = Db::name('base_config')->where('group_id', 'in', [200, 300])->select();
        foreach ($config_list as $k => $v) {
            $value  = unserialize($v['value']);
            $config = explode('/', $value);//自定义表格
            $this->assign("{$v['name']}_list", $config);
        }


        return $this->fetch();
    }


    //提交编辑
    public function edit_post()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理


        $result = $MemberInit->edit_post($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功", 'index');
    }


    //提交编辑
    public function edit_post_two()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        //宠物
        if ($params['pet']) {
            $pet           = array_keys($params['pet']);//提取key
            $params['pet'] = $this->setParams($pet);
        }

        //兴趣爱好
        if ($params['hobby']) {
            $hobby           = array_keys($params['hobby']);//提取key
            $params['hobby'] = $this->setParams($hobby);
        }

        //标签
        if ($params['tag']) {
            $tag           = array_keys($params['tag']);//提取key
            $params['tag'] = $this->setParams($tag);
        }

        //省市区基本信息
        $domicile_province_info = Db::name('region')->where('code', '=', $params['domicile_province_code'])->find();
        $domicile_city_info     = Db::name('region')->where('code', '=', $params['domicile_city_code'])->find();
        $domicile_county_info   = Db::name('region')->where('code', '=', $params['domicile_county_code'])->find();
        //name
        $params['domicile_province'] = $domicile_province_info['name'];
        $params['domicile_city']     = $domicile_city_info['name'];
        $params['domicile_county']   = $domicile_county_info['name'];

        //省市区基本信息
        $work_province_info = Db::name('region')->where('code', '=', $params['work_province_code'])->find();
        $work_city_info     = Db::name('region')->where('code', '=', $params['work_city_code'])->find();
        $work_county_info   = Db::name('region')->where('code', '=', $params['work_county_code'])->find();
        //name
        $params['work_province'] = $work_province_info['name'];
        $params['work_city']     = $work_city_info['name'];
        $params['work_county']   = $work_county_info['name'];


        if ($params['images']) $params['images'] = $this->setParams($params['images']);


        $result = $MemberInit->edit_post_two($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功");
    }


    //提交编辑
    public function audit_post()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理


        $result = $MemberInit->edit_post_two($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功");
    }


    //添加
    public function add()
    {
        $this->base_edit();


        /**
         *  下拉数据
         */
        $config_list = Db::name('base_config')->where('group_id', 'in', [200, 300])->select();
        foreach ($config_list as $k => $v) {
            $value  = unserialize($v['value']);
            $config = explode('/', $value);//自定义表格
            $this->assign("{$v['name']}_list", $config);
        }

        $this->assign("status", 2);


        return $this->fetch();
    }


    //导入
    public function member_import()
    {
        return $this->fetch();
    }


    //添加提交
    public function add_post()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理


        $result = $MemberInit->edit_post($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功", 'index');
    }


    //查看详情
    public function find()
    {
        $this->base_edit();

        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        $params['InterfaceType'] = 'admin';//身份类型,后台
        $params['field']         = '*';//所有字段

        $where   = [];
        $where[] = ['id', '=', $params['id']];


        $result = $MemberInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }


        /**
         *  下拉数据
         */
        $config_list = Db::name('base_config')->where('group_id', 'in', [200, 300])->select();
        foreach ($config_list as $k => $v) {
            $value  = unserialize($v['value']);
            $config = explode('/', $value);//自定义表格
            $this->assign("{$v['name']}_list", $config);
        }


        return $this->fetch();
    }


    //查看详情
    public function update_vip()
    {
        $this->base_edit();


        $params        = $this->request->param();
        $MemberInit    = new \init\MemberInit();//会员管理
        $MemberVipInit = new \init\MemberVipInit();//用户等级    (ps:InitController)


        $params['InterfaceType'] = 'admin';//身份类型,后台
        $params['field']         = '*';//所有字段

        $where   = [];
        $where[] = ['id', '=', $params['id']];


        $result = $MemberInit->get_find($where, $params);
        if (empty($result)) $this->error("暂无数据");

        $toArray = $result->toArray();
        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }


        return $this->fetch();
    }

    public function vip_post()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        if ($params['end_time']) $params['end_time'] = strtotime($params['end_time']);


        $result = $MemberInit->edit_post_two($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功");
    }

    //删除
    public function delete()
    {
        $id         = $this->request->param('id/a');
        $MemberInit = new \init\MemberInit();//会员管理

        if (empty($id)) $id = $this->request->param('ids/a');


        $result = $MemberInit->delete_post($id);
        if (empty($result)) $this->error('失败请重试');


        $this->success("删除成功");
    }


    //更新排序
    public function list_order_post()
    {
        $params     = $this->request->param('list_order/a');
        $MemberInit = new \init\MemberInit();//会员管理


        $result = $MemberInit->list_order_post($params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功");
    }

    //更改状态
    public function batch_post()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理


        $id = $this->request->param("id/a");
        if (empty($id)) $id = $this->request->param("ids/a");

        $result = $MemberInit->batch_post($id, $params);
        if (empty($result)) $this->error('失败请重试');


        $this->success("保存成功", 'index');
    }

    /******************************************   余额操作 & 积分操作  ********************************************************/
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
        $MemberModel       = new \initmodel\MemberModel();//用户管理
        $params            = $this->request->param();
        $member            = $MemberModel->where('id', '=', $params['id'])->find();
        $admin_id_and_name = cmf_get_current_admin_id() . '-' . session('name');//管理员信息


        if ($params['operate_type'] == 1) {
            //余额

            if ($params['type'] == 1) {
                if (empty($params['content'])) $params['content'] = '管理员添加';
                //增加
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[增加用户余额];";//管理备注
                $MemberModel->inc_balance($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }

            if ($params['type'] == 2) {
                if (empty($params['content'])) $params['content'] = '管理员扣除';
                //扣除
                if ($member['balance'] < $params['price']) $this->error('请输入正确金额');
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[扣除用户余额];";//管理备注
                $MemberModel->dec_balance($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }
        }


        if ($params['operate_type'] == 2) {
            //积分

            if ($params['type'] == 1) {
                if (empty($params['content'])) $params['content'] = '管理员添加';
                //增加
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[增加用户积分];";//管理备注
                $MemberModel->inc_point($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
            }

            if ($params['type'] == 2) {
                if (empty($params['content'])) $params['content'] = '管理员扣除';
                //扣除
                if ($member['point'] < $params['price']) $this->error('请输入正确金额');
                $remark = "操作人[{$admin_id_and_name}];操作说明[{$params['content']}];操作类型[扣除用户积分];";//管理备注
                $MemberModel->dec_point($params['id'], $params['price'], $params['content'], $remark, 0, cmf_order_sn(6), 100);
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
        if ($params['type'] == 1) $name = 'member_balance';
        if ($params['type'] == 2) $name = 'member_point';
        if (empty($name)) $name = 'member_balance';

        if ($name == 'member_balance') {
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


        //导出数据
        if ($params["is_export"]) $this->export_excel_use($map, $params, $name);


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


    public function children00()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        $where = [];
        if ($params['pid']) {
            $where[] = ['pid', '=', $params['pid']];
            $this->assign("pid", $params['pid']);
        }

        $params['InterfaceType'] = 'admin';//身份类型,后台
        $result                  = $MemberInit->get_list_paginate($where, $params);


        $this->assign("list", $result);
        $this->assign('page', $result->render());//单独提取分页出来


        return $this->fetch();
    }


    /**
     * 导出数据 export_excel_use ,积分-余额导出
     * @param array $where 条件
     */
    public function export_excel_use($where = [], $params = [], $name)
    {
        $type   = [1 => '收入', 2 => '支出'];
        $result = Db::name($name)
            ->where($where)
            ->order('id desc')
            ->select();

        $result = $result->toArray();

        foreach ($result as $k => &$item) {
            //用户信息
            $item['user_info'] = Db::name('member')->find($item['user_id']);

            //导出基本信息
            $item["create_time"] = date("Y-m-d H:i:s", $item["create_time"]);
            $item["update_time"] = date("Y-m-d H:i:s", $item["update_time"]);
            $item['type_name']   = $type[$item['type']];

            //用户信息
            $user_info        = $item['user_info'];
            $item['userInfo'] = "(ID:{$user_info['id']}) {$user_info['nickname']}  {$user_info['phone']}";
        }

        $headArrValue = [
            ["rowName" => "ID", "rowVal" => "id", "width" => 10],
            ["rowName" => "用户信息", "rowVal" => "userInfo", "width" => 30],
            ["rowName" => "类型", "rowVal" => "type_name", "width" => 10],
            ["rowName" => "说明", "rowVal" => "content", "width" => 10],
            ["rowName" => "变得值", "rowVal" => "price", "width" => 10],
            ["rowName" => "变动前", "rowVal" => "before", "width" => 10],
            ["rowName" => "变动后", "rowVal" => "after", "width" => 10],
            ["rowName" => "创建时间", "rowVal" => "create_time", "width" => 30],
        ];


        //副标题 纵单元格
        //        $subtitle = [
        //            ["rowName" => "列1", "acrossCells" => 2],
        //            ["rowName" => "列2", "acrossCells" => 2],
        //        ];

        $Excel = new ExcelController();
        $Excel->excelExports($result, $headArrValue, ["fileName" => "操作记录"]);
    }


    /**
     * 导出数据--用户导出
     * @param array $where 条件
     */
    public function export_excel($where = [], $params = [])
    {
        $MemberInit = new \init\MemberInit();//会员管理

        $params['InterfaceType'] = 'admin';//身份类型,后台
        $result                  = $MemberInit->get_list($where, $params);

        $result = $result->toArray();
        foreach ($result as $k => &$item) {
            $item["update_time"] = date("Y-m-d H:i:s", $item["update_time"]);

            //订单号过长问题
            if ($item["identity_number"]) $item["identity_number"] = $item["identity_number"] . "\t";
            if ($item["number_bank"]) $item["number_bank"] = $item["number_bank"] . "\t";
            if ($item["order_num"]) $item["order_num"] = $item["order_num"] . "\t";


            //图片链接 可用默认浏览器打开   后面为展示链接名字 --单独,多图特殊处理一下
            if ($item["image"]) $item["image"] = '=HYPERLINK("' . cmf_get_asset_url($item['image']) . '","图片.png")';


            //用户信息

            $item['userInfo'] = "(ID:{$item['id']}) {$item['nickname']}";
        }

        $headArrValue = [
            ["rowName" => "ID", "rowVal" => "id", "width" => 10],
            ["rowName" => "用户信息", "rowVal" => "userInfo", "width" => 30],
            ["rowName" => "手机号", "rowVal" => "phone", "width" => 20],
            ["rowName" => "身份类型", "rowVal" => "identity_name", "width" => 20],
            ["rowName" => "身份证号", "rowVal" => "identity_number", "width" => 30],
            ["rowName" => "积分", "rowVal" => "balance", "width" => 20],
            ["rowName" => "性别", "rowVal" => "gender", "width" => 20],
            ["rowName" => "年龄", "rowVal" => "age", "width" => 20],
            ["rowName" => "学历", "rowVal" => "educational", "width" => 30],
            ["rowName" => "开户行", "rowVal" => "opening_bank", "width" => 30],
            ["rowName" => "银行卡号", "rowVal" => "number_bank", "width" => 30],
            ["rowName" => "创建时间", "rowVal" => "create_time", "width" => 30],
        ];

        //副标题 纵单元格
        //        $subtitle = [
        //            ["rowName" => "列1", "acrossCells" => count($headArrValue)/2],
        //            ["rowName" => "列2", "acrossCells" => count($headArrValue)/2],
        //        ];

        $Excel = new ExcelController();
        $Excel->excelExports($result, $headArrValue, ["fileName" => "用户管理"]);
    }


    //查询下级列表
    public function children()
    {
        $params     = $this->request->param();
        $MemberInit = new \init\MemberInit();//会员管理

        $where = [];
        if ($params['pid']) {
            $where[] = ['pid', '=', $params['pid']];
            $this->assign("pid", $params['pid']);
        }

        $params['InterfaceType'] = 'admin';//身份类型,后台
        $result                  = $MemberInit->get_list_paginate($where, $params);


        $this->assign("list", $result);
        $this->assign('page', $result->render());//单独提取分页出来


        return $this->fetch();
    }


    //会员关系图
    public function children_tree()
    {
        return $this->fetch();
    }


    //会员关系图 用户数据
    public function get_user_list()
    {
        $MemberModel    = new \initmodel\MemberModel();//用户管理
        $MemberVipModel = new \initmodel\MemberVipModel(); //用户等级   (ps:InitModel)

        $params = $this->request->param();

        //条件
        $map = [];
        if (empty($params['nickname']) && empty($params['phone'])) $map[] = ['pid', '=', $params['pid'] ?? 0];
        if (isset($params['nickname']) && $params['nickname']) $map[] = ['nickname', 'like', "%{$params['nickname']}%"];
        if (isset($params['phone']) && $params['phone']) $map[] = ['phone', '=', $params['phone']];

        $result = $MemberModel->where($map)
            ->field('id,nickname,avatar,phone,create_time,vip_id,is_captain')
            ->order('id')
            ->select()
            ->each(function ($item, $key) use ($MemberModel, $MemberVipModel) {

                $item['vip_name'] = $MemberVipModel->where('id', $item['vip_id'])->value('name');
                if ($item['is_captain'] == 1) $item['vip_name'] .= '[团队长]';


                //判断是否有子级
                $item['isLeaf'] = true;
                if ($MemberModel->where('pid', $item['id'])->count()) $item['isLeaf'] = false;

                return $item;
            });


        $this->success("请求成功", '', $result);
    }


}
