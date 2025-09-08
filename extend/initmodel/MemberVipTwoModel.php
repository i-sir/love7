<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberVipTwo",
    *     "name_underline"   =>"member_vip_two",
    *     "table_name"       =>"member_vip",
    *     "model_name"       =>"MemberVipTwoModel",
    *     "remark"           =>"用户等级",
    *     "author"           =>"",
    *     "create_time"      =>"2025-03-13 17:04:37",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberVipTwoModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberVipTwoModel extends Model{

	protected $name = 'member_vip';//用户等级

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
