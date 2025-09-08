<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"MemberMatchmaker",
    *     "name_underline"   =>"member_matchmaker",
    *     "table_name"       =>"member_matchmaker",
    *     "model_name"       =>"MemberMatchmakerModel",
    *     "remark"           =>"红娘管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-27 10:52:52",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\MemberMatchmakerModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class MemberMatchmakerModel extends Model{

	protected $name = 'member_matchmaker';//红娘管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
