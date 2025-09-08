<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberRecommendOrder",
    *     "name_underline"   =>"member_recommend_order",
    *     "table_name"       =>"member_recommend_order",
    *     "model_name"       =>"MemberRecommendOrderModel",
    *     "remark"           =>"管理费",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-24 15:07:02",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberRecommendOrderModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberRecommendOrderModel extends Model{

	protected $name = 'member_recommend_order';//管理费

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
