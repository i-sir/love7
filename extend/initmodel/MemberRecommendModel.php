<?php

namespace initmodel;

/**
 * @AdminModel(
 *     "name"             =>"MemberRecommend",
 *     "name_underline"   =>"member_recommend",
 *     "table_name"       =>"member_recommend",
 *     "model_name"       =>"MemberRecommendModel",
 *     "remark"           =>"引荐人",
 *     "author"           =>"",
 *     "create_time"      =>"2024-08-21 14:46:59",
 *     "version"          =>"1.0",
 *     "use"              => new \initmodel\MemberRecommendModel();
 * )
 */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberRecommendModel extends Model
{

    protected $name = 'member_recommend';//引荐人

    //软删除
    protected $hidden            = ['delete_time'];
    protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;



    /**
     * 新增余额
     * @param $user_id    用户id
     * @param $balance    金额
     * @param $content    展示内容
     * @param $remark     管理员备注
     * @param $order_id   订单id
     * @param $order_num  订单单号
     * @param $order_type 订单类型   100后台操作   50佣金
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function inc_balance($user_id, $balance, $content, $remark, $order_id = 0, $order_num = 0, $order_type = 0)
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)


        $member_info = $MemberRecommendModel->where('id', '=', $user_id)->find();
        if ($balance <= 0) return;

        $log = array(
            'user_id'     => $user_id,
            'type'        => 1,
            'price'       => $balance,
            'before'      => $member_info['balance'],
            'after'       => $member_info['balance'] + $balance,
            'content'     => $content,
            'remark'      => $remark,
            'order_id'    => $order_id,
            'order_type'  => $order_type,
            'create_time' => time(),
            'order_num'   => $order_num,
        );
        //写入明细
        Db::name('member_recommend_balance')->strict(false)->insert($log);
        //更新当前金额
        $MemberRecommendModel->where('id', $user_id)->inc('balance', $balance)->update();
    }


    /**
     * 减少余额
     * @param $user_id    用户id
     * @param $balance    金额
     * @param $content    展示内容
     * @param $remark     管理备注
     * @param $order_id   订单id
     * @param $order_num  订单单号
     * @param $order_type 订单类型 20抵扣订单金额   100后台操作
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function dec_balance($user_id, $balance, $content, $remark, $order_id = 0, $order_num = 0, $order_type = 0)
    {
        $MemberRecommendModel = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)



        $member_info = $MemberRecommendModel->where('id', '=', $user_id)->find();
        if ($balance <= 0) return;

        $log = array(
            'user_id'     => $user_id,
            'type'        => 2,
            'price'       => $balance,
            'before'      => $member_info['balance'],
            'after'       => $member_info['balance'] - $balance,
            'content'     => $content,
            'remark'      => $remark,
            'order_id'    => $order_id,
            'order_type'  => $order_type,
            'create_time' => time(),
            'order_num'   => $order_num,
        );
        //写入明细
        Db::name('member_recommend_balance')->strict(false)->insert($log);
        //更新当前金额
        $MemberRecommendModel->where('id', $user_id)->dec('balance', $balance)->update();
    }

}
