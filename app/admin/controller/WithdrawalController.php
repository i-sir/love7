<?php

namespace app\admin\controller;

use think\db\Query;
use think\facade\Db;
use cmf\controller\AdminBaseController;


class WithdrawalController extends AdminBaseController
{

    //    public function initialize()
    //    {
    //        parent::initialize();
    //    }

    /**
     * 首页基础信息
     */
    protected function base_index()
    {
        $this->type_array   = [1 => '支付宝', 2 => '微信'];
        $this->status_array = [1 => '待审核', 2 => '已审核', 3 => '已拒绝'];
        $this->assign('status_list', $this->status_array);

    }

    /**
     * 编辑,添加基础信息
     */
    protected function base_edit()
    {

    }

    /**
     * 提现记录查询
     */
    public function index()
    {
        $this->base_index();


        $MemberWithdrawalModel = new \initmodel\MemberWithdrawalModel();//提现管理
        $MemberRecommendModel  = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)

        $params = $this->request->param();


        $where[] = ['w.id', '>', 0];
        if (isset($params['keyword']) && $params['keyword']) $where[] = ['m.username|m.phone|w.ali_username|w.ali_account', 'like', "%{$params['keyword']}%"];
        if (isset($params['status']) && $params['status']) $where[] = ['w.status', '=', $params['status']];
        if ($params['user_id']) $where[] = ['w.user_id', '=', $params['user_id']];
        $where[] = $this->getBetweenTime($params['beginTime'], $params['endTime'], 'w.create_time');


        $list = $MemberWithdrawalModel
            ->alias("w")
            ->join("member_recommend m", "w.user_id=m.id")
            ->field("w.*,m.nickname,m.avatar,m.phone")
            ->where($where)
            ->order("w.id desc")
            ->paginate(10)
            ->each(function ($item, $key) {
                if ($item['create_time']) $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);


                $item['type_name']   = $this->type_array[$item['type']];
                $item['status_name'] = $this->status_array[$item['status']];


                return $item;
            });


        $list->appends($params);

        // 获取分页显示
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list', $list);

        return $this->fetch();
    }


    /**
     * 修改状态
     */
    public function update_withdrawal()
    {
        // 启动事务
        Db::startTrans();

        $MemberWithdrawalModel = new \initmodel\MemberWithdrawalModel();//提现管理
        $MemberRecommendModel  = new \initmodel\MemberRecommendModel(); //引荐人   (ps:InitModel)


        $params                = $this->request->param();
        $params['update_time'] = time();


        $admin_id_and_name = cmf_get_current_admin_id() . '-' . session('name');//管理员信息


        $withdrawal_info = $MemberWithdrawalModel->where('id', $params['id'])->find();
        if ($withdrawal_info['status'] != 1) $this->error("已处理不能重复处理!");


        $result = $MemberWithdrawalModel->where('id', $params['id'])->strict(false)->update($params);


        if ($result) {
            $remark = "操作人[{$admin_id_and_name}];操作说明[提现驳回:{$params['refuse']}];操作类型[管理员驳回提现申请];";//管理备注
            if ($params['status'] == 3) $MemberRecommendModel->inc_balance($withdrawal_info['user_id'], $withdrawal_info['price'], '提现驳回:' . $params['refuse'], $remark, $withdrawal_info['id'], $withdrawal_info['order_num'], 50);

            // 提交事务
            Db::commit();

            $this->success("处理成功!");
        } else {
            $this->error("处理失败!");
        }
    }


    /**
     * 删除提现记录
     */
    public function delete_withdrawal()
    {
        $MemberWithdrawalModel = new \initmodel\MemberWithdrawalModel();//提现管理
        $params                = $this->request->param();
        $result                = $MemberWithdrawalModel->where('id', $params['id'])->delete();
        if ($result) {
            $this->success("删除成功!");
        } else {
            $this->error("删除失败!");
        }
    }


    public function refuse()
    {
        $MemberWithdrawalModel = new \initmodel\MemberWithdrawalModel();//提现管理
        $id                    = $this->request->param('id');

        $result = $MemberWithdrawalModel->find($id);
        if (empty($result)) {
            $this->error("not found data");
        }
        $toArray = $result->toArray();

        foreach ($toArray as $k => $v) {
            $this->assign($k, $v);
        }
        return $this->fetch();
    }

}