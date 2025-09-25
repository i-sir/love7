<?php

namespace api\wxapp\controller;

use initmodel\AssetModel;
use initmodel\MemberModel;

/**
 * @ApiController(
 *     "name"                    =>"Init",
 *     "name_underline"          =>"init",
 *     "controller_name"         =>"Init",
 *     "table_name"              =>"无",
 *     "remark"                  =>"基础接口,封装的接口"
 *     "api_url"                 =>"/api/wxapp/init/index",
 *     "author"                  =>"",
 *     "create_time"             =>"2024-04-24 17:16:22",
 *     "version"                 =>"1.0",
 *     "use"                     => new \api\wxapp\controller\InitController();
 *     "test_environment"        =>"http://love7.ikun:9090/api/wxapp/init/index",
 *     "official_environment"    =>"https://xcxkf186.aubye.com/api/wxapp/init/index",
 * )
 */
class InitController
{
    /**
     * 本模块,用于封装常用方法,复用方法
     */


    /**
     * 给上级发放佣金
     * @param $p_user_id
     * https://xcxkf186.aubye.com/api/wxapp/init/send_invitation_commission?p_user_id=2
     */
    public function send_invitation_commission($p_user_id = 0)
    {
        //邀请佣金
        $balance = cmf_config('invitation_rewards');
        $remark  = "操作人[邀请奖励];操作说明[邀请好友得佣金];操作类型[佣金奖励];";//管理备注
        MemberModel::inc_balance($p_user_id, $balance, '邀请奖励', $remark, 0, cmf_order_sn(), 10);

        return "true";
    }


    /**
     * 开通会员给上级发放佣金
     * @param $order_num
     */
    public function send_vip_commission($order_num = 0, $user_id = 0, $level = 1)
    {
        $MemberVipOrderModel = new \initmodel\MemberVipOrderModel(); //充值会员   (ps:InitModel)
        $MemberModel         = new \initmodel\MemberModel();//用户管理

        $map        = [];
        $map[]      = ['order_num', '=', $order_num];
        $order_info = $MemberVipOrderModel->where($map)->find();
        if (empty($order_info)) return false;
        if ($order_info['amount'] <= 0) return false;
        if ($level > 4) return false;//最多4级

        $level_list = [
            1 => 'one_distribution',
            2 => 'two_distribution',
            3 => 'third_distribution',
            4 => 'fourth_distribution',
        ];

        //查找上级
        $pid = $MemberModel->where('id', '=', $user_id)->value('pid');
        if ($pid) {
            //分销比例(%)
            $distribution = cmf_config($level_list[$level]);
            $commission   = $order_info['amount'] * $distribution / 100;
            if ($commission > 0) {
                $remark = "操作人[下单得佣金];操作说明[下单得佣金];操作类型[下单得佣金];";//管理备注
                AssetModel::incAsset('下单得佣金,给上级发放佣金 [300]', [
                    'operate_type'  => 'commission',//操作类型，balance|point ...
                    'identity_type' => 'member',//身份类型，member| ...
                    'user_id'       => $pid,
                    'price'         => $commission,
                    'order_num'     => $order_num,
                    'order_type'    => 300,
                    'content'       => '邀请奖励',
                    'remark'        => $remark,
                    'order_id'      => $order_info['id'],
                ]);
                $this->send_vip_commission($order_num, $pid, $level + 1);
            }
        }




        return true;



    }


    //升级
    public function upgrade($user_id = 0)
    {
        $MemberModel         = new \initmodel\MemberModel();//用户管理



        $user_info = $MemberModel->where('id', '=', $user_id)->field('pid,id')->find();
        if (empty($user_info)) return false;

        //团队人数
        $team_size=cmf_config('team_size');


        $team_number = $this->getAllChildIds($user_id);
        if ($team_number >= $team_size) {
            //升级会员
            $MemberModel->where('id', '=', $user_id)->update(['is_captain' => 1,'update_time'=>time()]);
        }



        $this->upgrade($user_info['pid']);


    }

    /**
     * 获取所有子级ID（递归方法）
     * @param int    $pid      父级ID
     * @param array &$childIds 用于存储结果的数组
     * @return array
     */
    public function getAllChildIds($pid, &$childIds = [])
    {
        $MemberModel = new \initmodel\MemberModel();


        // 查询直接子级
        $map      = [];
        $map[]    = ['pid', '=', $pid];
        $map[]    = ['vip_id', '<>', 1];
        $children = $MemberModel->where($map)->column('id');

        if (!empty($children)) {
            foreach ($children as $childId) {
                $childIds[] = $childId;
                // 递归查询子级的子级
                $this->getAllChildIds($childId, $childIds);
            }
        }

        return $childIds;
    }



}