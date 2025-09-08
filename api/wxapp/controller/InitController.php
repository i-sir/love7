<?php

namespace api\wxapp\controller;

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

}