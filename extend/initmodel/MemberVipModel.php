<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberVip",
    *     "name_underline"   =>"member_vip",
    *     "table_name"       =>"member_vip",
    *     "model_name"       =>"MemberVipModel",
    *     "remark"           =>"用户等级",
    *     "author"           =>"",
    *     "create_time"      =>"2025-03-13 16:59:13",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberVipModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberVipModel extends Model{

	protected $name = 'member_vip';//用户等级

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
