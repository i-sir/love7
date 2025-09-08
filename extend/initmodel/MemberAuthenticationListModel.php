<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberAuthenticationList",
    *     "name_underline"   =>"member_authentication_list",
    *     "table_name"       =>"member_authentication_list",
    *     "model_name"       =>"MemberAuthenticationListModel",
    *     "remark"           =>"认证列表",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-24 16:39:21",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberAuthenticationListModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberAuthenticationListModel extends Model{

	protected $name = 'member_authentication_list';//认证列表

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
