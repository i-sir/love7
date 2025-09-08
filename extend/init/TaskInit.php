<?php

namespace init;

use think\facade\Db;
use think\facade\Log;
use think\facade\Cache;

/**
 * 定时任务
 */
class TaskInit
{


    /**
     * 更新vip状态
     */
    public function operation_vip()
    {
        $MemberModel = new \initmodel\MemberModel();//用户管理

        //操作vip   vip_time vip到期时间
        //$MemberModel->where('vip_time', '<', time())->update(['is_vip' => 0]);
        echo("更新vip状态,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }

    /**
     * 处理引荐人管理时间
     */
    public function operation_recommend()
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)

        //正常
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '>', time()];
        $MemberRecommendModel->where($map)->strict(false)->update([
            'is_manage' => 2
        ]);

        //已过期
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '<', time()];
        $MemberRecommendModel->where($map)->strict(false)->update([
            'is_manage' => 3
        ]);


        echo("处理引荐人管理时间,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }


    /**
     * 处理店铺管理时间
     */
    public function operation_shop()
    {
        $ShopManageOrderModel = new \initmodel\ShopManageOrderModel(); //管理费订单管理   (ps:InitModel)

        //正常
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '>', time()];
        $ShopManageOrderModel->where($map)->strict(false)->update([
            'is_manage' => 2
        ]);

        //已过期
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '<', time()];
        $ShopManageOrderModel->where($map)->strict(false)->update([
            'is_manage' => 3
        ]);


        echo("处理店铺管理时间,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }


    /**
     * 处理红娘管理时间
     */
    public function operation_matchmaker()
    {
        $MemberMatchmakerModel = new \initmodel\MemberMatchmakerModel(); //红娘管理   (ps:InitModel)


        //正常
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '>', time()];
        $MemberMatchmakerModel->where($map)->strict(false)->update([
            'is_manage' => 2
        ]);

        //已过期
        $map   = [];
        $map[] = ['end_time', '>', 0];
        $map[] = ['end_time', '<', time()];
        $MemberMatchmakerModel->where($map)->strict(false)->update([
            'is_manage' => 3
        ]);


        echo("处理红娘管理时间,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }


    /**
     * 处理店铺评分
     */
    public function operation_star()
    {
        $ShopModel         = new \initmodel\ShopModel(); //店铺管理   (ps:InitModel)
        $ShopEvaluateModel = new \initmodel\ShopEvaluateModel(); //店铺评价   (ps:InitModel)

        $shop_list = $ShopModel->select();
        foreach ($shop_list as $k => $v) {
            $map   = [];
            $map[] = ['shop_id', '=', $v['id']];
            $star  = $ShopEvaluateModel->where($map)->avg('star');

            if ($star) $ShopModel->where('id', '=', $v['id'])->update(['star' => $star]);
        }


        echo("处理店铺评分,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }


    /**
     * 刷新排名
     */
    public function operation_ranking()
    {
        $MemberModel = new \initmodel\MemberModel();//用户管理

        // 获取所有用户的排名
        $rankings = $MemberModel
            ->field('id,top_time')
            ->order('top_time desc,id desc')
            ->select();

        // 遍历排名结果，逐个更新数据库中的 ranking 字段
        foreach ($rankings as $index => $ranking) {
            $rank = $index + 1;
            $MemberModel
                ->where('id', $ranking['id'])
                ->update(['ranking' => $rank]);
        }


        echo("刷新排名,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }


    /**
     * 将公众号的official_openid存入member表中
     */
    public function update_official_openid()
    {
        $gzh_list = Db::name('member_gzh')->select();
        foreach ($gzh_list as $k => $v) {
            Db::name('member')->where('unionid', '=', $v['unionid'])->update(['official_openid' => $v['openid']]);
        }

        echo("将公众号的official_openid存入member表中,执行成功\n" . cmf_random_string(80) . "\n" . date('Y-m-d H:i:s') . "\n");
    }

}