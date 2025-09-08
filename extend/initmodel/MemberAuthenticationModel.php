<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberAuthentication",
    *     "name_underline"   =>"member_authentication",
    *     "table_name"       =>"member_authentication",
    *     "model_name"       =>"MemberAuthenticationModel",
    *     "remark"           =>"认证管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-22 14:55:19",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberAuthenticationModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberAuthenticationModel extends Model{

	protected $name = 'member_authentication';//认证管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
